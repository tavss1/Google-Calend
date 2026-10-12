<?php

namespace App\Libraries;

use App\Models\EventoConvidadoModel;
use App\Models\EventoModel;
use DateTimeImmutable;
use DateTimeZone;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Exception as GoogleException;

class GoogleCalendarSync
{
    private Calendar $service;
    private string $calendarId;
    private string $sendUpdates;

    public static function habilitado(): bool
    {
        return filter_var(env('GOOGLE_SYNC_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN);
    }

    public function __construct()
    {
        $this->calendarId  = env('GOOGLE_CALENDAR_ID', 'primary');
        $this->sendUpdates = env('GOOGLE_SEND_UPDATES', 'all'); // all | externalOnly | none

        $client = new Client();
        $client->setScopes([Calendar::CALENDAR_EVENTS]);

        if (env('GOOGLE_AUTH_MODE') === 'oauth') {
            // TESTE LOCAL: conta pessoal (OAuth + refresh token)
            $client->setAuthConfig(WRITEPATH . 'google/client_secret.json');
            $t = $client->fetchAccessTokenWithRefreshToken(env('GOOGLE_REFRESH_TOKEN'));
            if (isset($t['error'])) {
                throw new \RuntimeException('OAuth: ' . ($t['error_description'] ?? $t['error']));
            }
        } else {
            // PRODUÇÃO: service account + delegação em todo o domínio
            $client->setAuthConfig(json_decode((string) env('GOOGLE_SA_JSON'), true));
            $client->setSubject(env('GOOGLE_ORGANIZER_EMAIL'));
        }

        $this->service = new Calendar($client);
    }

    /** Envia o evento do MySQL para o Google (cria, atualiza ou remove, conforme o caso). */
    public function sincronizar(int $id): void
    {
        $eventos = model(EventoModel::class);
        $e = $eventos->find($id);
        if (! $e) {
            return;
        }

        // Aniversários não vão para o Google (e, se o tipo mudou, remove o que existia).
        if ($e['tipo'] === 'aniversario') {
            if ($e['google_event_id']) {
                $this->remover($e['google_event_id']);
            }
            $eventos->update($id, ['google_event_id' => null, 'sync_status' => 'ok', 'sync_erro' => null]);
            return;
        }

        $emails = array_column(model(EventoConvidadoModel::class)->where('evento_id', $id)->findAll(), 'email');

        $gid = $e['google_event_id']
            ? $this->atualizar($e['google_event_id'], $e, $emails)
            : $this->criar($e, $emails);

        $eventos->update($id, ['google_event_id' => $gid, 'sync_status' => 'ok', 'sync_erro' => null]);
    }

    /** Nunca lança exceção: marca erro no banco e devolve a mensagem (ou null se deu certo/está desligado). */
    public static function sincronizarSeguro(int $id): ?string
    {
        if (! self::habilitado()) {
            return null;
        }
        try {
            (new self())->sincronizar($id);
            return null;
        } catch (\Throwable $t) {
            log_message('error', 'Sync Google (evento {id}): {msg}', ['id' => $id, 'msg' => $t->getMessage()]);
            model(EventoModel::class)->update($id, [
                'sync_status' => 'erro',
                'sync_erro'   => mb_substr($t->getMessage(), 0, 1000),
            ]);
            return $t->getMessage();
        }
    }

    public function criar(array $evento, array $emails): string
    {
        $criado = $this->service->events->insert(
            $this->calendarId,
            $this->montar($evento, $emails),
            ['sendUpdates' => $this->sendUpdates]
        );
        return $criado->getId();
    }

    public function atualizar(string $googleId, array $evento, array $emails): string
    {
        try {
            $this->service->events->patch(
                $this->calendarId,
                $googleId,
                $this->montar($evento, $emails),
                ['sendUpdates' => $this->sendUpdates]
            );
            return $googleId;
        } catch (GoogleException $ex) {
            // Evento apagado manualmente no Google: recria.
            if (in_array($ex->getCode(), [404, 410], true)) {
                return $this->criar($evento, $emails);
            }
            throw $ex;
        }
    }

    public function remover(string $googleId): void
    {
        try {
            $this->service->events->delete($this->calendarId, $googleId, ['sendUpdates' => $this->sendUpdates]);
        } catch (GoogleException $ex) {
            if (! in_array($ex->getCode(), [404, 410], true)) {
                throw $ex;
            }
        }
    }

    private function montar(array $e, array $emails): Event
    {
        $tzNome = 'America/Sao_Paulo';
        $tz     = new DateTimeZone($tzNome);
        $ini    = new DateTimeImmutable($e['inicio'], $tz);
        $fim    = $e['fim'] ? new DateTimeImmutable($e['fim'], $tz) : null;

        $dados = [
            'summary'                 => $e['titulo'],
            'description'             => $e['descricao'] ?? '',
            'location'                => $e['local'] ?? '',
            'attendees'               => array_map(static fn ($m) => ['email' => $m], $emails),
            'guestsCanSeeOtherGuests' => false, // LGPD: convidados não veem os e-mails dos outros
            'guestsCanModify'         => false,
        ];

        if (! empty($e['dia_inteiro'])) {
            // No Google o "end" de dia inteiro é EXCLUSIVO: soma 1 dia ao último dia.
            $dados['start'] = ['date' => $ini->format('Y-m-d')];
            $dados['end']   = ['date' => ($fim ?? $ini)->modify('+1 day')->format('Y-m-d')];
        } else {
            $fim ??= $ini->modify('+1 hour');
            $dados['start'] = ['dateTime' => $ini->format(DATE_ATOM), 'timeZone' => $tzNome];
            $dados['end']   = ['dateTime' => $fim->format(DATE_ATOM), 'timeZone' => $tzNome];
        }

        return new Event($dados);
    }
}
