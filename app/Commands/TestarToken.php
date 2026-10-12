<?php

namespace App\Commands;

use App\Libraries\GoogleCalendarSync;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Google\Client;
use Google\Service\Calendar;

class TestarToken extends BaseCommand
{
    protected $group       = 'Calendario';
    protected $name        = 'calendario:token';
    protected $description = 'Valida a autenticação com o Google sem expor tokens.';

    public function run(array $params)
    {
        if (! GoogleCalendarSync::habilitado()) {
            CLI::write('GOOGLE_SYNC_ENABLED está desligado.', 'yellow');
            return;
        }

        $secret = WRITEPATH . 'google/client_secret.json';
        if (! is_file($secret)) {
            CLI::error('client_secret.json não encontrado em writable/google/.');
            return;
        }

        $modo    = env('GOOGLE_AUTH_MODE');
        $calId   = env('GOOGLE_CALENDAR_ID', 'primary');
        $token   = env('GOOGLE_REFRESH_TOKEN');

        if ($modo === 'oauth') {
            if (! $token || str_contains($token, '...')) {
                CLI::error('GOOGLE_REFRESH_TOKEN não está preenchido (ainda é placeholder). Rode /google-dev/login.');
                return;
            }
        } else {
            if (! env('GOOGLE_SA_JSON') || ! env('GOOGLE_ORGANIZER_EMAIL')) {
                CLI::error('GOOGLE_SA_JSON e/ou GOOGLE_ORGANIZER_EMAIL ausentes.');
                return;
            }
        }

        $client = new Client();
        $client->setScopes([Calendar::CALENDAR_EVENTS]);

        try {
            if ($modo === 'oauth') {
                $client->setAuthConfig($secret);
                $resposta = $client->fetchAccessTokenWithRefreshToken($token);
                if (isset($resposta['error'])) {
                    CLI::error('FALHA OAuth: ' . ($resposta['error_description'] ?? $resposta['error']));
                    CLI::write('Causa provável: token expirado/revogado (7 dias no modo Testing), escopo, ou usuário fora da lista de teste. Rode /google-dev/login novamente.', 'yellow');
                    return;
                }
                CLI::write('Access token obtido com sucesso (token não exibido).', 'green');
            } else {
                $client->setAuthConfig(json_decode((string) env('GOOGLE_SA_JSON'), true));
                $client->setSubject(env('GOOGLE_ORGANIZER_EMAIL'));
            }

            $service = new Calendar($client);
            $eventos = $service->events->listEvents($calId, ['maxResults' => 1, 'singleEvents' => true]);
            CLI::write('Agenda acessível (events.list respondeu; ' . count($eventos->getItems()) . ' evento(s) na primeira página).', 'green');
            CLI::write('Autenticação OK. Pode rodar: php spark calendario:sync');
        } catch (\Throwable $t) {
            CLI::error('FALHA ao acessar a Agenda: ' . $t->getMessage());
        }
    }
}
