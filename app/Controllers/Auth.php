<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('id')) {
            return redirect()->to('calendario');
        }
        return view('auth/login');
    }

    public function tentar()
    {
        $email = mb_strtolower(trim((string) $this->request->getPost('email')));
        $senha = (string) $this->request->getPost('senha');

        $usuario = (new UsuarioModel())->where('email', $email)->first();

        if (! $usuario || ! password_verify($senha, $usuario['senha_hash'])) {
            return redirect()->back()->withInput()->with('erro', 'Credenciais inválidas.');
        }

        session()->set([
            'id'     => (int) $usuario['id'],
            'nome'   => $usuario['nome'],
            'email'  => $usuario['email'],
            'perfil' => $usuario['perfil'],
        ]);
        return redirect()->to('calendario');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
