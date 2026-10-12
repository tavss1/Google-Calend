<?php

use App\Libraries\GoogleCalendarSync;
use CodeIgniter\Test\CIUnitTestCase;
use Google\Service\Calendar\Event;

/**
 * @internal
 */
final class GoogleCalendarSyncTest extends CIUnitTestCase
{
    private function novoSync(): GoogleCalendarSync
    {
        return (new ReflectionClass(GoogleCalendarSync::class))->newInstanceWithoutConstructor();
    }

    /**
     * @param array<string, mixed> $evento
     * @param list<string>         $emails
     */
    private function montar(array $evento, array $emails = []): Event
    {
        $metodo = new ReflectionMethod(GoogleCalendarSync::class, 'montar');

        return $metodo->invoke($this->novoSync(), $evento, $emails);
    }

    /**
     * @return array<string, mixed>
     */
    private function eventoBase(array $extra = []): array
    {
        return array_merge([
            'titulo'      => 'Evento Teste',
            'descricao'   => 'Descricao do evento',
            'local'       => 'Sala 1',
            'inicio'      => '2026-12-01 09:00:00',
            'fim'         => '2026-12-01 10:00:00',
            'dia_inteiro' => 0,
        ], $extra);
    }

    public function testHabilitadoRespeitaVariavelDeAmbiente(): void
    {
        $original = $_ENV['GOOGLE_SYNC_ENABLED'] ?? null;

        try {
            $_ENV['GOOGLE_SYNC_ENABLED'] = 'false';
            $this->assertFalse(GoogleCalendarSync::habilitado());

            $_ENV['GOOGLE_SYNC_ENABLED'] = 'true';
            $this->assertTrue(GoogleCalendarSync::habilitado());
        } finally {
            if ($original === null) {
                unset($_ENV['GOOGLE_SYNC_ENABLED']);
            } else {
                $_ENV['GOOGLE_SYNC_ENABLED'] = $original;
            }
        }
    }

    public function testSincronizarSeguroDesligadoNaoFalha(): void
    {
        $original = $_ENV['GOOGLE_SYNC_ENABLED'] ?? null;
        $_ENV['GOOGLE_SYNC_ENABLED'] = 'false';

        try {
            $this->assertNull(GoogleCalendarSync::sincronizarSeguro(123));
        } finally {
            if ($original === null) {
                unset($_ENV['GOOGLE_SYNC_ENABLED']);
            } else {
                $_ENV['GOOGLE_SYNC_ENABLED'] = $original;
            }
        }
    }

    public function testDiaInteiroUsaFimExclusivoSomandoUmDia(): void
    {
        $evento = $this->montar($this->eventoBase([
            'dia_inteiro' => 1,
            'inicio'      => '2026-12-01 00:00:00',
            'fim'         => '2026-12-01 00:00:00',
        ]));

        $this->assertSame('2026-12-01', $evento->getStart()->getDate());
        $this->assertSame('2026-12-02', $evento->getEnd()->getDate());
        $this->assertNull($evento->getStart()->getDateTime());
    }

    public function testDiaInteiroSemFimUsaUmDia(): void
    {
        $evento = $this->montar($this->eventoBase([
            'dia_inteiro' => 1,
            'inicio'      => '2026-12-01 00:00:00',
            'fim'         => null,
        ]));

        $this->assertSame('2026-12-01', $evento->getStart()->getDate());
        $this->assertSame('2026-12-02', $evento->getEnd()->getDate());
    }

    public function testEventoComHoraIncluiTimezoneDeSaoPaulo(): void
    {
        $evento = $this->montar($this->eventoBase());

        $this->assertSame('2026-12-01T09:00:00-03:00', $evento->getStart()->getDateTime());
        $this->assertSame('America/Sao_Paulo', $evento->getStart()->getTimeZone());
        $this->assertSame('2026-12-01T10:00:00-03:00', $evento->getEnd()->getDateTime());
        $this->assertNull($evento->getStart()->getDate());
    }

    public function testEventoSemFimAssumeUmaHora(): void
    {
        $evento = $this->montar($this->eventoBase(['fim' => null]));

        $this->assertSame('2026-12-01T09:00:00-03:00', $evento->getStart()->getDateTime());
        $this->assertSame('2026-12-01T10:00:00-03:00', $evento->getEnd()->getDateTime());
    }

    public function testTituloDescricaoLocalEConvidados(): void
    {
        $evento = $this->montar($this->eventoBase(), ['a@teste.local', 'b@teste.local']);

        $this->assertSame('Evento Teste', $evento->getSummary());
        $this->assertSame('Descricao do evento', $evento->getDescription());
        $this->assertSame('Sala 1', $evento->getLocation());
        $this->assertFalse($evento->getGuestsCanSeeOtherGuests());
        $this->assertFalse($evento->getGuestsCanModify());

        $emails = array_map(static fn ($atendee): string => $atendee->getEmail(), $evento->getAttendees());
        $this->assertSame(['a@teste.local', 'b@teste.local'], $emails);
    }
}
