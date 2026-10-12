<?php
// app/Controllers/GoogleAuthDev.php
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
        $c->setPrompt('consent'); // garante a emissão do refresh_token
        return $c;
    }

    public function login()
    {
        return redirect()->to($this->client()->createAuthUrl());
    }

    public function callback()
    {
        $token = $this->client()->fetchAccessTokenWithAuthCode($this->request->getGet('code'));
        return $this->response->setJSON(['refresh_token' => $token['refresh_token'] ?? null]);
    }
}