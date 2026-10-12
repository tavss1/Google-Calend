<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Base para os testes do modulo de calendario.
 *
 * Roda contra o grupo de banco "tests" (configurado no phpunit.dist.xml),
 * desliga a sincronizacao com o Google e o filtro CSRF (nao ha browser
 * para obter o token), e limpa as tabelas antes de cada teste.
 */
abstract class CalendarTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $DBGroup     = 'tests';
    protected $namespace   = null;
    protected $migrateOnce = true;
    protected $refresh     = true;

    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['GOOGLE_SYNC_ENABLED']    = 'false';
        $_SERVER['GOOGLE_SYNC_ENABLED'] = 'false';
        putenv('GOOGLE_SYNC_ENABLED=false');

        $filtros = config(\Config\Filters::class);
        $filtros->globals['before'] = array_values(array_filter(
            $filtros->globals['before'],
            static fn ($filtro) => $filtro !== 'csrf',
        ));

        $this->limparTabelas();
    }

    protected function limparTabelas(): void
    {
        $this->db->disableForeignKeyChecks();
        foreach (['evento_convidados', 'eventos', 'usuarios'] as $tabela) {
            $this->db->table($tabela)->emptyTable();
        }
        $this->db->enableForeignKeyChecks();
    }

    protected function criarUsuario(string $perfil = 'ADM', array $extra = []): array
    {
        $agora = date('Y-m-d H:i:s');

        $dados = array_merge([
            'nome'            => 'Usuario ' . $perfil,
            'email'           => strtolower($perfil) . '@teste.local',
            'senha_hash'      => password_hash('senha123', PASSWORD_DEFAULT),
            'perfil'          => $perfil,
            'data_nascimento' => null,
            'created_at'      => $agora,
            'updated_at'      => $agora,
        ], $extra);

        $this->db->table('usuarios')->insert($dados);

        return $dados + ['id' => (int) $this->db->insertID()];
    }

    protected function sessaoDe(array $usuario): array
    {
        return [
            'id'     => (int) $usuario['id'],
            'nome'   => $usuario['nome'],
            'email'  => $usuario['email'],
            'perfil' => $usuario['perfil'],
        ];
    }

    protected function criarEvento(array $extra = []): int
    {
        if (! isset($extra['criado_por'])) {
            $extra['criado_por'] = (int) $this->criarUsuario('ADM', ['email' => 'dono@teste.local'])['id'];
        }

        $dados = array_merge([
            'titulo'      => 'Evento teste',
            'descricao'   => null,
            'tipo'        => 'interno',
            'local'       => null,
            'inicio'      => '2026-10-15 09:00:00',
            'fim'         => '2026-10-15 10:00:00',
            'dia_inteiro' => 0,
            'sync_status' => 'pendente',
        ], $extra);

        $this->db->table('eventos')->insert($dados);

        return (int) $this->db->insertID();
    }

    protected function criarConvidado(int $eventoId, array $extra = []): int
    {
        $dados = array_merge([
            'evento_id' => $eventoId,
            'email'     => 'convidado@teste.local',
            'nome'      => 'Convidado Teste',
            'externo'   => 1,
            'resposta'  => 'pendente',
        ], $extra);

        $this->db->table('evento_convidados')->insert($dados);

        return (int) $this->db->insertID();
    }

    /**
     * Decodifica o corpo JSON de uma resposta de teste.
     *
     * Nao usar TestResponse::getBody() para JSON: ele e redirecionado para o
     * DOMParser e devolve o corpo re-serializado como HTML.
     *
     * @return array<string, mixed>
     */
    protected function jsonDa(\CodeIgniter\Test\TestResponse $resultado): array
    {
        $json  = $resultado->getJSON();
        $dados = is_string($json) ? json_decode($json, true) : null;

        return is_array($dados) ? $dados : [];
    }
}
