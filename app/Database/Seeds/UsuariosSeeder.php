<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $senha = static fn (string $s): string => password_hash($s, PASSWORD_DEFAULT);

        $usuarios = [
            ['nome' => 'Administrador', 'email' => 'admin@dev.local', 'senha' => 'admin123', 'perfil' => 'ADM', 'data_nascimento' => '1990-10-20'],
            ['nome' => 'Maria Souza', 'email' => 'maria@dev.local', 'senha' => 'maria123', 'perfil' => 'USUARIO', 'data_nascimento' => '1995-11-15'],
            ['nome' => 'Bruno R.', 'email' => 'brbruno082003@gmail.com', 'senha' => 'bruno123', 'perfil' => 'ADM', 'data_nascimento' => null],
            ['nome' => 'Bruno Z.', 'email' => 'brunozzt99@outlook.com', 'senha' => 'outlook123', 'perfil' => 'USUARIO', 'data_nascimento' => null],
        ];

        foreach ($usuarios as $u) {
            $existe = $this->db->table('usuarios')->where('email', $u['email'])->countAllResults();
            if ($existe) {
                continue;
            }
            $this->db->table('usuarios')->insert([
                'nome'            => $u['nome'],
                'email'           => $u['email'],
                'senha_hash'      => $senha($u['senha']),
                'perfil'          => $u['perfil'],
                'data_nascimento' => $u['data_nascimento'],
                'created_at'      => $agora,
                'updated_at'      => $agora,
            ]);
        }
    }
}
