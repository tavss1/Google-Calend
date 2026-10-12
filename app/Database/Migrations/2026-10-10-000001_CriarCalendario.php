<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarCalendario extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('eventos')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'titulo'         => ['type' => 'VARCHAR', 'constraint' => 200],
                'descricao'      => ['type' => 'TEXT', 'null' => true],
                'tipo'           => ['type' => 'ENUM', 'constraint' => ['interno', 'externo', 'aniversario'], 'default' => 'interno'],
                'local'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'inicio'         => ['type' => 'DATETIME'],
                'fim'            => ['type' => 'DATETIME', 'null' => true],
                'dia_inteiro'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'google_event_id'=> ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'sync_status'    => ['type' => 'ENUM', 'constraint' => ['pendente', 'ok', 'erro'], 'default' => 'pendente'],
                'sync_erro'      => ['type' => 'TEXT', 'null' => true],
                'criado_por'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
                'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('inicio');
            $this->forge->addForeignKey('criado_por', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('eventos', true);
        } elseif (! $this->db->fieldExists('sync_erro', 'eventos')) {
            $this->forge->addColumn('eventos', ['sync_erro' => ['type' => 'TEXT', 'null' => true, 'after' => 'sync_status']]);
        }

        if (! $this->db->tableExists('evento_convidados')) {
            $this->forge->addField([
                'id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'evento_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'email'     => ['type' => 'VARCHAR', 'constraint' => 190],
                'nome'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'externo'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'resposta'  => ['type' => 'ENUM', 'constraint' => ['pendente', 'aceito', 'recusado', 'talvez'], 'default' => 'pendente'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('evento_id', 'eventos', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('evento_convidados', true);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('evento_convidados', true);
        $this->forge->dropTable('eventos', true);
    }
}
