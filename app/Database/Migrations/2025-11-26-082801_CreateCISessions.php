<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCISessions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'collation'  => 'utf8mb4_bin'
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false
            ],
            'timestamp' => [ // Diperbarui: Menggunakan INT (Unix Timestamp)
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'default'    => 0, 
            ],
            'data' => [
                'type' => 'BLOB'
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('timestamp');
        $this->forge->createTable('ci_sessions', true); // Ditambahkan 'true' agar IF NOT EXISTS
    }

    public function down()
    {
        $this->forge->dropTable('ci_sessions', true);
    }
}