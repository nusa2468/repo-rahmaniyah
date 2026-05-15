<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaprasTables extends Migration
{
    public function up()
    {
        // 1. Tabel Tanah
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'=> ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], 
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'luas'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'sertifikat'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'keterangan'  => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true], // Kolom untuk Soft Deletes
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); // Index untuk Foreign Key
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sapras_tanah', true);

        // 2. Tabel Gedung (Bangunan)
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'=> ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], 
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'tahun'       => ['type' => 'YEAR', 'null' => true],
            'luas'        => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'keterangan'  => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true], // Kolom untuk Soft Deletes
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); // Index untuk Foreign Key
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sapras_gedung', true);

        // 3. Tabel Ruangan
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'=> ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'id_gedung'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'kapasitas'   => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'keterangan'  => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true], // Kolom untuk Soft Deletes
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); // Index untuk Foreign Key
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_gedung', 'sapras_gedung', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sapras_ruangan', true);

        // 4. Tabel Peralatan
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'=> ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'kondisi'     => ['type' => 'ENUM', 'constraint' => ['Baik', 'Rusak Ringan', 'Rusak Berat'], 'default' => 'Baik'],
            'jumlah'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'keterangan'  => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true], // Kolom untuk Soft Deletes
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); // Index untuk Foreign Key
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sapras_peralatan', true);

        // 5. Tabel Inventaris Lain
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'=> ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'nama'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'kondisi'     => ['type' => 'ENUM', 'constraint' => ['Baik', 'Rusak Ringan', 'Rusak Berat'], 'default' => 'Baik'],
            'jumlah'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'keterangan'  => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true], // Kolom untuk Soft Deletes
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); // Index untuk Foreign Key
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sapras_inventaris', true);
    }

    public function down()
    {
        // Hapus tabel dengan urutan terbalik untuk menghindari error Foreign Key
        $this->forge->dropTable('sapras_inventaris', true);
        $this->forge->dropTable('sapras_peralatan', true);
        $this->forge->dropTable('sapras_ruangan', true);
        $this->forge->dropTable('sapras_gedung', true);
        $this->forge->dropTable('sapras_tanah', true);
    }
}