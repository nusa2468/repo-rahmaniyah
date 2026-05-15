<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ============================================================================
 * MIGRASI CORE SYSTEM (AUTHENTICATION & AUTHORIZATION)
 * ============================================================================
 * Deskripsi:
 * Hanya mendefinisikan infrastruktur (Skema/Tabel) tanpa menyisipkan data.
 * Data diisi melalui Database Seeder.
 */
class CreateCoreTables extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // --------------------------------------------------------------------
        // 1. TABEL: ROLES
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
            'name'          => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true],
            'description'   => ['type' => 'VARCHAR', 'constraint' => '255'],
            'kode_jenjang'  => [
                'type'       => 'VARCHAR', 
                'constraint' => '20', 
                'null'       => true, 
                'default'    => 'GLOBAL',
                'comment'    => 'Scope Unit: GLOBAL (Semua) atau Kode Spesifik (SD/SMP)'
            ],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('roles', true);

        // --------------------------------------------------------------------
        // 2. TABEL: USERS
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_role'       => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true],
            'kode_jenjang'  => [
                'type'       => 'VARCHAR', 
                'constraint' => '20', 
                'null'       => true, 
                'default'    => 'GLOBAL',
                'comment'    => 'Kunci Isolasi Data: GLOBAL atau Kode Spesifik'
            ],
            'nama_lengkap'  => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => false],
            'username'      => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true],
            'email'         => ['type' => 'VARCHAR', 'constraint' => '255', 'unique' => true, 'null' => true],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'last_login'    => ['type' => 'DATETIME', 'null' => true],
            'foto'          => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_role', 'roles', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('users', true);

        // --------------------------------------------------------------------
        // 3. TABEL: PERMISSIONS
        // --------------------------------------------------------------------
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true, 'auto_increment' => true],
            'permission_key' => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true],
            'description'    => ['type' => 'VARCHAR', 'constraint' => '255'],
            'group_name'     => ['type' => 'VARCHAR', 'constraint' => '100'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('permissions', true);

        // --------------------------------------------------------------------
        // 4. TABEL: ROLE_PERMISSIONS
        // --------------------------------------------------------------------
        $this->forge->addField([
            'role_id'       => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true],
            'permission_id' => ['type' => 'INT', 'constraint' => 5, 'unsigned' => true],
        ]);
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addPrimaryKey(['role_id', 'permission_id']);
        $this->forge->createTable('role_permissions', true);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('role_permissions', true);
        $this->forge->dropTable('permissions', true);
        $this->forge->dropTable('users', true);
        $this->forge->dropTable('roles', true);
        $this->db->enableForeignKeyChecks();
    }
}