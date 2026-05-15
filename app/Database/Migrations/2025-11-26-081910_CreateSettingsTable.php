<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTabel extends Migration
{
    public function up()
    {
        // 1. SKEMA TABEL: jenjang_sekolah
        // Berfungsi sebagai master data unit pendidikan (TK, SD, SMP, SMA, GLOBAL)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_jenjang' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'kode_jenjang' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 1,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_jenjang');
        $this->forge->createTable('jenjang_sekolah', true);

        // 2. SKEMA TABEL: settings
        // PERBAIKAN: Mengubah ENUM menjadi VARCHAR agar mendukung TK dan unit masa depan
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'jenjang' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'GLOBAL',
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        
        // Penting: Key unik tetap pada kombinasi jenjang dan key
        $this->forge->addUniqueKey(['jenjang', 'key']);
        
        $this->forge->createTable('settings', true);
    }

    public function down()
    {
        $this->forge->dropTable('settings', true);
        $this->forge->dropTable('jenjang_sekolah', true);
    }
}