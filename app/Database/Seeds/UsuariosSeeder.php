<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $agora = date('Y-m-d H:i:s');
        $senha = static fn (string $s): string => password_hash($s, PASSWORD_DEFAULT);

        $this->db->table('usuarios')->delete('1=1');
        $this->db->table('usuarios')->insertBatch([
            [
                'nome'            => 'Administrador',
                'email'           => 'admin@dev.local',
                'senha_hash'      => $senha('admin123'),
                'perfil'          => 'ADM',
                'data_nascimento' => '1990-10-20',
                'created_at'      => $agora,
                'updated_at'      => $agora,
            ],
            [
                'nome'            => 'Maria Souza',
                'email'           => 'maria@dev.local',
                'senha_hash'      => $senha('maria123'),
                'perfil'          => 'USUARIO',
                'data_nascimento' => '1995-11-15',
                'created_at'      => $agora,
                'updated_at'      => $agora,
            ],
            [
                'nome'            => 'Bruno R.',
                'email'           => 'brbruno082003@gmail.com',
                'senha_hash'      => $senha('bruno123'),
                'perfil'          => 'ADM',
                'data_nascimento' => null,
                'created_at'      => $agora,
                'updated_at'      => $agora,
            ],
            [
                'nome'            => 'Bruno Z.',
                'email'           => 'brunozzt99@outlook.com',
                'senha_hash'      => $senha('outlook123'),
                'perfil'          => 'USUARIO',
                'data_nascimento' => null,
                'created_at'      => $agora,
                'updated_at'      => $agora,
            ],
        ]);
    }
}
