<?php

use Tests\Support\CalendarTestCase;

/**
 * @internal
 */
final class CalendarioAuthTest extends CalendarTestCase
{
    public function testPaginaDeLoginCarrega(): void
    {
        $result = $this->get('login');

        $result->assertOK();
        $this->assertStringContainsString('Entrar', $result->getBody());
    }

    public function testLoginComCredenciaisInvalidasNaoPreencheSessao(): void
    {
        $email = 'auth' . uniqid('', true) . '@teste.local';
        $this->criarUsuario('ADM', ['email' => $email]);

        $result = $this->post('login', ['email' => $email, 'senha' => 'errada']);

        $result->assertRedirect();
        $this->assertArrayNotHasKey('id', $_SESSION);
    }

    public function testLoginComSucessoPreencheSessao(): void
    {
        $email = 'auth' . uniqid('', true) . '@teste.local';
        $user  = $this->criarUsuario('ADM', ['email' => $email]);

        $result = $this->post('login', ['email' => $email, 'senha' => 'senha123']);

        $result->assertRedirect();
        $this->assertStringContainsString('calendario', $result->getRedirectUrl());
        $this->assertSame((int) $user['id'], $_SESSION['id'] ?? null);
        $this->assertSame('ADM', $_SESSION['perfil'] ?? null);
    }

    public function testThrottleBloqueiaAposCincoTentativas(): void
    {
        $email = 'throttle' . uniqid('', true) . '@teste.local';
        $this->criarUsuario('ADM', ['email' => $email]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('login', ['email' => $email, 'senha' => 'errada'])->assertRedirect();
        }

        $result = $this->post('login', ['email' => $email, 'senha' => 'senha123']);

        $result->assertRedirect();
        $this->assertArrayNotHasKey('id', $_SESSION);
    }

    public function testLogoutRedirecionaParaLogin(): void
    {
        $user = $this->criarUsuario('ADM', ['email' => 'logout@teste.local']);

        $result = $this->withSession($this->sessaoDe($user))->post('logout');

        $result->assertRedirect();
        $this->assertStringContainsString('login', $result->getRedirectUrl());
    }

    public function testRotasProtegidasExigemLogin(): void
    {
        $this->get('calendario')->assertRedirect();
        $this->get('calendario/feed?start=2026-10-01&end=2026-11-01')->assertRedirect();
        $this->post('calendario/salvar')->assertRedirect();
        $this->post('calendario/excluir/1')->assertRedirect();
    }
}
