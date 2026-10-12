<?php

namespace App\Controllers;

use Google\Client;
use Google\Service\Calendar;

class GoogleAuthDev extends BaseController
{
    private function client(): Client
    {
        if (ENVIRONMENT !== 'development') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $c = new Client();
        $c->setAuthConfig(WRITEPATH . 'google/client_secret.json');
        $c->setRedirectUri(base_url('google-dev/callback'));
        $c->setScopes([Calendar::CALENDAR_EVENTS]);
        $c->setAccessType('offline');
        $c->setPrompt('consent');
        return $c;
    }

    public function login()
    {
        $c     = $this->client();
        $state = bin2hex(random_bytes(16));
        session()->set('google_dev_state', $state);
        $c->setState($state);
        return redirect()->to($c->createAuthUrl());
    }

    public function callback()
    {
        $code     = (string) $this->request->getGet('code');
        $state    = (string) $this->request->getGet('state');
        $erro     = (string) $this->request->getGet('error');
        $esperado = (string) session()->get('google_dev_state');

        if ($erro !== '' || $code === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'erro' => 'Consentimento não concluído' . ($erro !== '' ? ': ' . $erro : '.'),
                'dica' => 'Acesse /google-dev/login e aceite a permissão.',
            ]);
        }

        if ($esperado === '' || ! hash_equals($esperado, $state)) {
            return $this->response->setStatusCode(403)->setJSON([
                'erro' => 'State inválido ou expirado.',
                'dica' => 'Refaça o fluxo em /google-dev/login.',
            ]);
        }
        session()->remove('google_dev_state');

        try {
            $token = $this->client()->fetchAccessTokenWithAuthCode($code);
        } catch (\Throwable $t) {
            return $this->response->setStatusCode(500)->setJSON(['erro' => 'Falha ao trocar o código: ' . $t->getMessage()]);
        }

        if (isset($token['error'])) {
            return $this->response->setStatusCode(400)->setJSON(['erro' => $token['error_description'] ?? $token['error']]);
        }

        return $this->response->setJSON([
            'refresh_token' => $token['refresh_token'] ?? null,
            'dica'          => 'Copie o refresh_token para GOOGLE_REFRESH_TOKEN no .env (mantenha em segredo) e rode: php spark calendario:token',
        ]);
    }
}
