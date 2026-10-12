<?php

use CodeIgniter\Test\TestResponse;
use Tests\Support\CalendarTestCase;

/**
 * @internal
 */
final class CalendarioAdmTest extends CalendarTestCase
{
    private array $adm;

    private array $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adm     = $this->sessaoDe($this->criarUsuario('ADM', ['email' => 'adm@teste.local']));
        $this->usuario = $this->sessaoDe($this->criarUsuario('USUARIO', ['email' => 'user@teste.local']));
    }

    private function salvar(array $sessao, array $payload): TestResponse
    {
        return $this->withBodyFormat('json')->withSession($sessao)->post('calendario/salvar', $payload);
    }

    public function testUsuarioComumRecebe403(): void
    {
        $this->salvar($this->usuario, [
            'titulo' => 'Tentativa',
            'tipo'   => 'interno',
            'inicio' => '2026-10-15T09:00',
        ])->assertStatus(403);

        $this->withSession($this->usuario)->get('calendario/usuarios?q=maria')->assertStatus(403);

        $id = $this->criarEvento();
        $this->withSession($this->usuario)->post('calendario/excluir/' . $id)->assertStatus(403);
    }

    public function testSalvarCriaEventoEConvidadosNoMysql(): void
    {
        $maria = $this->criarUsuario('USUARIO', ['email' => 'maria@teste.local', 'nome' => 'Maria Souza']);

        $result = $this->salvar($this->adm, [
            'titulo'      => 'Reuniao Geral',
            'tipo'        => 'interno',
            'inicio'      => '2026-11-05T09:00',
            'fim'         => '2026-11-05T10:00',
            'local'       => 'Sala 1',
            'descricao'   => 'Pauta da reuniao',
            'convidados'  => [
                ['email' => 'fulano@example.com', 'nome' => 'Fulano'],
                ['email' => $maria['email'], 'nome' => 'Maria Souza'],
            ],
        ]);

        $result->assertOK();
        $json = $this->jsonDa($result);
        $this->assertArrayHasKey('id', $json);
        $this->assertNull($json['aviso']);

        $id    = (int) $json['id'];
        $evento = $this->db->table('eventos')->where('id', $id)->get()->getRowArray();

        $this->assertSame('Reuniao Geral', $evento['titulo']);
        $this->assertSame('2026-11-05 09:00:00', $evento['inicio']);
        $this->assertSame('2026-11-05 10:00:00', $evento['fim']);
        $this->assertSame(0, (int) $evento['dia_inteiro']);
        $this->assertSame($this->adm['id'], (int) $evento['criado_por']);
        $this->assertSame('pendente', $evento['sync_status']);

        $convidados = $this->db->table('evento_convidados')
            ->where('evento_id', $id)
            ->get()->getResultArray();

        $this->assertCount(2, $convidados);
        $porEmail = array_column($convidados, null, 'email');
        $this->assertSame(1, (int) $porEmail['fulano@example.com']['externo']);
        $this->assertSame(0, (int) $porEmail['maria@teste.local']['externo']);
        $this->assertSame('Maria Souza', $porEmail['maria@teste.local']['nome']);
    }

    public function testSalvarValidacoesRetornam422(): void
    {
        $casos = [
            'titulo ausente' => ['tipo' => 'interno', 'inicio' => '2026-11-05T09:00'],
            'tipo invalido'  => ['titulo' => 'X', 'tipo' => 'festa', 'inicio' => '2026-11-05T09:00'],
            'email invalido' => ['titulo' => 'X', 'tipo' => 'interno', 'inicio' => '2026-11-05T09:00', 'convidados' => [['email' => 'invalido']]],
            'fim igual'      => ['titulo' => 'X', 'tipo' => 'interno', 'inicio' => '2026-11-05T10:00', 'fim' => '2026-11-05T10:00'],
            'fim antes'      => ['titulo' => 'X', 'tipo' => 'interno', 'inicio' => '2026-11-05T10:00', 'fim' => '2026-11-05T09:00'],
            'inicio ausente' => ['titulo' => 'X', 'tipo' => 'interno'],
        ];

        foreach ($casos as $descricao => $payload) {
            $this->salvar($this->adm, $payload)->assertStatus(422);
        }

        $this->assertSame(0, $this->db->table('eventos')->countAllResults());
    }

    public function testSalvarDiaInteiroNoMesmoDia(): void
    {
        $result = $this->salvar($this->adm, [
            'titulo'      => 'Feriado',
            'tipo'        => 'externo',
            'dia_inteiro' => true,
            'inicio'      => '2026-12-25',
            'fim'         => '2026-12-25',
        ]);

        $result->assertOK();
        $id     = (int) $this->jsonDa($result)['id'];
        $evento = $this->db->table('eventos')->where('id', $id)->get()->getRowArray();

        $this->assertSame(1, (int) $evento['dia_inteiro']);
        $this->assertSame('2026-12-25 00:00:00', $evento['inicio']);
        $this->assertSame('2026-12-25 00:00:00', $evento['fim']);
    }

    public function testEdicaoTrocaConvidadosEPreservaGoogleEventId(): void
    {
        $id = $this->criarEvento([
            'google_event_id' => 'g-id-123',
            'criado_por'      => $this->adm['id'],
        ]);
        $this->criarConvidado($id, ['email' => 'antigo@teste.local']);

        $result = $this->salvar($this->adm, [
            'id'         => $id,
            'titulo'     => 'Evento Editado',
            'tipo'       => 'interno',
            'inicio'     => '2026-10-15T09:00',
            'fim'        => '2026-10-15T11:00',
            'convidados' => [['email' => 'novo@teste.local']],
        ]);

        $result->assertOK();
        $this->assertSame($id, (int) $this->jsonDa($result)['id']);

        $evento = $this->db->table('eventos')->where('id', $id)->get()->getRowArray();
        $this->assertSame('Evento Editado', $evento['titulo']);
        $this->assertSame('g-id-123', $evento['google_event_id']);

        $convidados = $this->db->table('evento_convidados')
            ->where('evento_id', $id)
            ->get()->getResultArray();

        $this->assertCount(1, $convidados);
        $this->assertSame('novo@teste.local', $convidados[0]['email']);
    }

    public function testSalvarAniversarioIgnoraConvidadosEZeraFim(): void
    {
        $result = $this->salvar($this->adm, [
            'titulo'     => 'Aniversario da Cidade',
            'tipo'       => 'aniversario',
            'inicio'     => '2026-12-25',
            'convidados' => [['email' => 'x@teste.local']],
        ]);

        $result->assertOK();
        $id     = (int) $this->jsonDa($result)['id'];
        $evento = $this->db->table('eventos')->where('id', $id)->get()->getRowArray();

        $this->assertSame(1, (int) $evento['dia_inteiro']);
        $this->assertNull($evento['fim']);
        $this->assertSame(0, $this->db->table('evento_convidados')->where('evento_id', $id)->countAllResults());
    }

    public function testExcluirRemoveEventoEConvidados(): void
    {
        $id = $this->criarEvento(['criado_por' => $this->adm['id']]);
        $this->criarConvidado($id);

        $result = $this->withSession($this->adm)->post('calendario/excluir/' . $id);

        $result->assertOK();
        $this->assertTrue($this->jsonDa($result)['ok']);
        $this->assertSame(0, $this->db->table('eventos')->where('id', $id)->countAllResults());
        $this->assertSame(0, $this->db->table('evento_convidados')->where('evento_id', $id)->countAllResults());
    }

    public function testExcluirInexistenteRetorna404(): void
    {
        $this->withSession($this->adm)->post('calendario/excluir/9999')->assertStatus(404);
    }

    public function testAutocompleteUsuarios(): void
    {
        $this->criarUsuario('USUARIO', ['email' => 'maria@teste.local', 'nome' => 'Maria Souza']);

        $result = $this->withSession($this->adm)->get('calendario/usuarios', ['q' => 'mar']);
        $result->assertOK();

        $linhas = $this->jsonDa($result);
        $this->assertCount(1, $linhas);
        $this->assertSame('maria@teste.local', $linhas[0]['email']);
        $this->assertSame('Maria Souza', $linhas[0]['nome']);

        $curto = $this->withSession($this->adm)->get('calendario/usuarios', ['q' => 'm']);
        $curto->assertOK();
        $this->assertSame([], $this->jsonDa($curto));
    }
}
