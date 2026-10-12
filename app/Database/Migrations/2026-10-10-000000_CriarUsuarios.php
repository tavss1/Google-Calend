<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarUsuarios extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nome'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 190],
            'senha_hash'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'perfil'          => ['type' => 'ENUM', 'constraint' => ['ADM', 'USUARIO'], 'default' => 'USUARIO'],
            'data_nascimento' => ['type' => 'DATE', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('usuarios', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('usuarios', true);
    }
}
