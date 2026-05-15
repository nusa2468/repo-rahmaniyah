<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKerjasamaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'jenjang' => [
                'type'       => 'ENUM',
                'constraint' => ['Global', 'SD', 'SMP', 'SMA'],
                'default'    => 'Global',
            ],
            'nama_mitra' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
            ],
            'logo' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'comment'    => 'Contoh: Manufaktur, IT, Pendidikan',
            ],
            // Detail Profil & Kontak
            'alamat' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'kontak_person' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'no_telp' => [
                'type'       => 'VARCHAR',
                'constraint' => '25',
                'null'       => true,
            ],
            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            // Manajemen MOU / PKS (Legalitas)
            'tgl_mulai' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'tgl_akhir' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'file_mou' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => 'Penyimpanan path dokumen digital (PDF)',
            ],
            // Program Kerjasama
            'program' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Menyimpan pilihan program: PKL, Rekrutmen, dll',
            ],
            // Fitur Baru: Indikator Keberhasilan / KPI
            'target_capaian' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => 'Target KPI atau output yang diharapkan dari kerjasama',
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif'],
                'default'    => 'aktif',
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
        $this->forge->createTable('kerjasama');
    }

    public function down()
    {
        $this->forge->dropTable('kerjasama');
    }
}