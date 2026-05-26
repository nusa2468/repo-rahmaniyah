<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAkuntansiTables extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // ===================================================================
        // 1. TABEL: AKUNTANSI KATEGORI (Klasifikasi Akun Utama - Multi Tenant)
        // ===================================================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'GLOBAL'], // <--- FIX: Kolom ditambahkan di sini
            'kode_kategori' => ['type' => 'VARCHAR', 'constraint' => 10], 
            'nama_kategori' => ['type' => 'VARCHAR', 'constraint' => 100], 
            'saldo_normal'  => ['type' => 'ENUM', 'constraint' => ['Debit', 'Kredit']],
            'laporan_tujuan'=> ['type' => 'ENUM', 'constraint' => ['Neraca', 'Aktivitas']], // Sesuai ISAK 35
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        
        // Anti-Bocor: Kode kategori tidak boleh bentrok dalam 1 unit yang sama
        $this->forge->addUniqueKey(['kode_jenjang', 'kode_kategori']);
        
        $this->forge->createTable('akuntansi_kategori', true);

        // ===================================================================
        // 2. TABEL: CHART OF ACCOUNTS (COA) -> TERISOLASI PER TENANT/UNIT
        // ===================================================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'GLOBAL'], // Kunci Isolasi Tenant/Unit
            'id_kategori'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kode_akun'     => ['type' => 'VARCHAR', 'constraint' => 20], // Contoh: 1101, 1102
            'nama_akun'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'saldo_awal'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'is_parent'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0], // 1 Jika Header, 0 Jika Akun Transaksi
            'parent_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        
        // Anti-Bocor: Kode akun tidak boleh bentrok dalam 1 unit yang sama
        $this->forge->addUniqueKey(['kode_jenjang', 'kode_akun']);
        
        $this->forge->addForeignKey('id_kategori', 'akuntansi_kategori', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'akuntansi_coa', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('akuntansi_coa', true);

        // ===================================================================
        // 3. TABEL: HEADER JURNAL UMUM -> TERISOLASI PER TENANT/UNIT
        // ===================================================================
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'      => ['type' => 'VARCHAR', 'constraint' => 20], // Kunci Isolasi Tenant/Unit
            'nomor_jurnal'      => ['type' => 'VARCHAR', 'constraint' => 50], // JU-202605-0001
            'tanggal'           => ['type' => 'DATE'],
            'referensi'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true], // No Invoice/SPP
            'deskripsi'         => ['type' => 'TEXT'],
            'total_debit'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'total_kredit'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'sumber_transaksi'  => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Manual'], // Manual, SPP, Gaji, Aset
            'status'            => ['type' => 'ENUM', 'constraint' => ['Draft', 'Posted', 'Void'], 'default' => 'Posted'],
            'created_by'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        
        // Anti-Bocor: Nomor jurnal tidak boleh bentrok dalam 1 unit
        $this->forge->addUniqueKey(['kode_jenjang', 'nomor_jurnal']);
        
        $this->forge->createTable('akuntansi_jurnal', true);

        // ===================================================================
        // 4. TABEL: JURNAL DETAIL (Baris Debit & Kredit)
        // ===================================================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_jurnal'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_coa'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'debit'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'kredit'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'keterangan'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], 
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_jurnal', 'akuntansi_jurnal', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_coa', 'akuntansi_coa', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('akuntansi_jurnal_detail', true);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('akuntansi_jurnal_detail', true);
        $this->forge->dropTable('akuntansi_jurnal', true);
        $this->forge->dropTable('akuntansi_coa', true);
        $this->forge->dropTable('akuntansi_kategori', true);
        $this->db->enableForeignKeyChecks();
    }
}