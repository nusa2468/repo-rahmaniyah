<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrganisasiAndJabatan extends Migration
{
    public function up()
    {
        // ==========================================
        // 1. TABEL JABATAN (Master Data / Referensi)
        // ==========================================
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_jabatan' => ['type' => 'VARCHAR', 'constraint' => 255],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Global'], 
            'level'        => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'atasan'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); 
        $this->forge->createTable('jabatan', true);

        // ==========================================
        // 2. TABEL ORGANISASI (Struktur & Personel)
        // ==========================================
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'parent_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'comment' => 'Untuk hierarki pohon jabatan'],
            'jenis_organisasi' => [
                'type'       => 'ENUM', 
                'constraint' => ['Pendiri', 'Pembina', 'Pengurus', 'Pengawas', 'Sekolah'], 
                'default'    => 'Sekolah'
            ],
            'kode_jenjang'     => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'Global'],
            
            // Relasi ke Master Jabatan (Opsional)
            'jabatan_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            // Nama Jabatan (Langsung/Custom)
            'nama_jabatan'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],

            // Relasi ke Pegawai (Unified Table) -> Menggantikan guru_id & karyawan_id
            'id_pegawai'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            
            // Manual Input (Jika pegawai tidak ada di database)
            'nama_pengampu'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'nip'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            
            'urutan'           => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'status'           => ['type' => 'ENUM', 'constraint' => ['aktif', 'nonaktif'], 'default' => 'aktif'],
            
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->addKey('parent_id');
        $this->forge->addKey('id_pegawai');

        // Menambahkan Foreign Keys
        // Pastikan tabel 'pegawai' dan 'jabatan' sudah ada sebelum migration ini dijalankan
        $this->forge->addForeignKey('id_pegawai', 'pegawai', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('jabatan_id', 'jabatan', 'id', 'SET NULL', 'CASCADE');
        
        // Self Reference untuk Parent ID (Hierarki)
        // $this->forge->addForeignKey('parent_id', 'organisasi', 'id', 'SET NULL', 'CASCADE'); // Opsional, hati-hati saat seed

        $this->forge->createTable('organisasi', true);
    }

    public function down()
    {
        $this->forge->dropTable('organisasi', true);
        $this->forge->dropTable('jabatan', true);
    }
}