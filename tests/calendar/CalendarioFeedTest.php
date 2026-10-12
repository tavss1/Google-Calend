<?php

use Tests\Support\CalendarTestCase;

/**
 * @internal
 */
final class CalendarioFeedTest extends CalendarTestCase
{
    private array $adm;

    private array $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adm     = $this->sessaoDe($this->criarUsuario('ADM', ['email' => 'adm@teste.local']));
        $this->usuario = $this->sessaoDe($this->criarUsuario('USUARIO', ['email' => 'user@teste.local']));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function feed(array $sessao, string $query = ''): array
    {
        $result = $this->withSession($sessao)->get('calendario/feed' . $query);
        $result->assertOK();

        return $this->jsonDa($result);
    }

    /**
     * @param list<array<string, mixed>> $itens
     *
     * @return array<string, mixed>
     */
    private function achar(array $itens, string $id): array
    {
        foreach ($itens as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        $this->fail('Item ' . $id . ' nao encontrado no feed.');
    }

    public function testVisitanteERedirecionadoParaLogin(): void
    {
        $result = $this->get('calendario/feed?start=2026-10-01&end=2026-11-01');

        $result->assertRedirect();
        $this->assertStringContainsString('login', $result->getRedirectUrl());
    }

    public function testFeedSemPeriodoRetorna400(): void
    {
        $this->withSession($this->adm)->get('calendario/feed')->assertStatus(400);
    }

    public function testAniversariosDeUsuariosRepetemTodoAno(): void
    {
        $maria = $this->criarUsuario('USUARIO', [
            'email'           => 'maria@teste.local',
            'nome'            => 'Maria Souza',
            'data_nascimento' => '1995-11-15',
        ]);

        $itens = $this->feed($this->adm, '?start=2026-01-01&end=2028-01-01');

        $marias = array_values(array_filter(
            $itens,
            static fn (array $e): bool => str_starts_with($e['id'], 'u' . $maria['id'] . '-'),
        ));

        $this->assertCount(2, $marias);
        $this->assertSame('2026-11-15', $marias[0]['start']);
        $this->assertSame('2027-11-15', $marias[1]['start']);
        $this->assertTrue($marias[0]['allDay']);
        $this->assertStringContainsString('Maria Souza', $marias[0]['title']);
        $this->assertSame('aniversario', $marias[0]['extendedProps']['tipo']);
    }

    public function testAniversario29DeFevereiroVira28EmAnoNaoBissexto(): void
    {
        $bissexto = $this->criarUsuario('USUARIO', [
            'email'           => 'bissexto@teste.local',
            'nome'            => 'Bissexto',
            'data_nascimento' => '2000-02-29',
        ]);

        $itens = $this->feed($this->adm, '?start=2027-02-01&end=2029-03-01');

        $starts = array_map(
            static fn (array $e): string => $e['start'],
            array_values(array_filter(
                $itens,
                static fn (array $e): bool => str_starts_with($e['id'], 'u' . $bissexto['id'] . '-'),
            )),
        );

        $this->assertSame(['2027-02-28', '2028-02-29', '2029-02-28'], $starts);
    }

    public function testAniversarioManualVemDosEventos(): void
    {
        $dono = $this->criarUsuario('ADM', ['email' => 'dono2@teste.local']);
        $id   = $this->criarEvento([
            'tipo'        => 'aniversario',
            'titulo'      => 'Aniversario do Instituto',
            'dia_inteiro' => 1,
            'inicio'      => '2020-10-05 00:00:00',
            'fim'         => null,
            'criado_por'  => $dono['id'],
        ]);

        $itens = $this->feed($this->adm, '?start=2026-10-01&end=2026-11-01');

        $item = $this->achar($itens, 'a' . $id . '-2026');
        $this->assertSame('2026-10-05', $item['start']);
        $this->assertSame($id, $item['extendedProps']['eventoId']);
        $this->assertEmpty(array_filter($itens, static fn (array $e): bool => $e['id'] === 'e' . $id));
    }

    public function testDiaInteiroEnviaFimExclusivo(): void
    {
        $id = $this->criarEvento([
            'dia_inteiro' => 1,
            'inicio'      => '2026-10-10 00:00:00',
            'fim'         => '2026-10-12 00:00:00',
        ]);

        $item = $this->achar($this->feed($this->adm, '?start=2026-10-01&end=2026-11-01'), 'e' . $id);

        $this->assertSame('2026-10-10', $item['start']);
        $this->assertSame('2026-10-13', $item['end']);
        $this->assertTrue($item['allDay']);
    }

    public function testEventoComHoraUsaFormatoComT(): void
    {
        $id = $this->criarEvento([
            'inicio' => '2026-10-15 09:00:00',
            'fim'    => '2026-10-15 10:00:00',
        ]);

        $item = $this->achar($this->feed($this->adm, '?start=2026-10-01&end=2026-11-01'), 'e' . $id);

        $this->assertSame('2026-10-15T09:00:00', $item['start']);
        $this->assertSame('2026-10-15T10:00:00', $item['end']);
        $this->assertFalse($item['allDay']);
    }

    public function testConvidadosESyncVisiveisApenasParaAdm(): void
    {
        $dono = $this->criarUsuario('ADM', ['email' => 'dono3@teste.local']);
        $id   = $this->criarEvento(['criado_por' => $dono['id']]);
        $this->criarConvidado($id, ['email' => 'fulano@example.com', 'externo' => 1]);
        $this->criarConvidado($id, ['email' => 'interno@teste.local', 'nome' => 'Interno', 'externo' => 0]);

        $itemAdm = $this->achar($this->feed($this->adm, '?start=2026-10-01&end=2026-11-01'), 'e' . $id);
        $this->assertCount(2, $itemAdm['extendedProps']['convidados']);
        $this->assertSame('pendente', $itemAdm['extendedProps']['sync']);

        $itemUsuario = $this->achar($this->feed($this->usuario, '?start=2026-10-01&end=2026-11-01'), 'e' . $id);
        $this->assertSame([], $itemUsuario['extendedProps']['convidados']);
        $this->assertNull($itemUsuario['extendedProps']['sync']);
    }

    public function testEventoForaDaJanelaNaoAparece(): void
    {
        $id = $this->criarEvento([
            'inicio' => '2026-10-15 09:00:00',
            'fim'    => '2026-10-15 10:00:00',
        ]);

        $itens = $this->feed($this->adm, '?start=2026-11-01&end=2026-12-01');

        $this->assertEmpty(array_filter($itens, static fn (array $e): bool => $e['id'] === 'e' . $id));
    }
}
