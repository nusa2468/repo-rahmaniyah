<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreatePpdbTable extends Migration
{
    public function up()
    {
        // --- 1. TABEL AFFILIATES (MASTER AGEN) ---
        $this->forge->addField([
            'affiliate_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // UPDATE: Menambahkan kode_jenjang untuk scope unit
            'kode_jenjang' => [
                'type'           => 'VARCHAR',
                'constraint'     => '10',
                'null'           => true,
                'comment'        => 'TK, SD, SMP, SMA, atau GLOBAL',
            ],
            'nama_agen' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
            ],
            'kode_agen' => [
                'type'           => 'VARCHAR',
                'constraint'     => '50',
                'unique'         => true,
            ],
            'no_hp' => [
                'type'           => 'VARCHAR',
                'constraint'     => '20',
                'null'           => true,
            ],
            'email' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => true,
            ],
            'alamat' => [
                'type'           => 'TEXT',
                'null'           => true,
            ],
            'status' => [
                'type'           => 'ENUM',
                'constraint'     => ['Aktif', 'Non-Aktif'],
                'default'        => 'Aktif',
            ],
            'metode_agen' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => true,
                'comment'        => 'Strategi: 4P, Digital, Canvas, Alumni, dll',
            ],
            'target_pendaftar' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'default'        => 0,
            ],
            'fee_per_siswa' => [
                'type'           => 'DECIMAL',
                'constraint'     => '15,2',
                'default'        => 0.00,
            ],
            'nama_bank' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => true,
            ],
            'nomor_rekening' => [
                'type'           => 'VARCHAR',
                'constraint'     => '50',
                'null'           => true,
            ],
            'nama_rekening' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
                'null'           => true,
            ],
            'catatan' => [
                'type'           => 'TEXT',
                'null'           => true,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
            'deleted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('affiliate_id', true);
        $this->forge->createTable('affiliates');

        // --- 2. TABEL PENDAFTAR_BIODATA (DATA SISWA) ---
        $this->forge->addField([
            'pendaftar_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'no_pendaftaran' => [
                'type'           => 'VARCHAR',
                'constraint'     => '25',
                'unique'         => true,
                'null'           => true,
            ],
            'user_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => true,
            ],
            // UPDATE: Menambahkan kode_jenjang
            'kode_jenjang' => [
                'type'           => 'VARCHAR',
                'constraint'     => '10',
                'null'           => true,
            ],
            // UPDATE: Menambahkan tahun_ajaran
            'tahun_ajaran' => [
                'type'           => 'VARCHAR',
                'constraint'     => '10',
                'null'           => true,
                'comment'        => 'e.g. 2025/2026',
            ],
            'nama_lengkap' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
            ],
            'nik' => [
                'type'           => 'VARCHAR',
                'constraint'     => '16',
                'unique'         => true,
            ],
            'nisn' => [
                'type'           => 'VARCHAR',
                'constraint'     => '10',
                'unique'         => true,
            ],
            'jenis_kelamin' => [
                'type'           => 'ENUM',
                'constraint'     => ['L', 'P'],
                'null'           => true,
            ],
            'tempat_lahir' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => true,
            ],
            'tanggal_lahir' => [
                'type'           => 'DATE',
                'null'           => true,
            ],
            'alamat_lengkap' => [
                'type'           => 'TEXT',
                'null'           => true,
            ],
            'no_hp_whatsapp' => [
                'type'           => 'VARCHAR',
                'constraint'     => '20',
                'null'           => true,
            ],
            'asal_sekolah' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
                'null'           => true,
            ],
            'nama_ayah' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
                'null'           => true,
            ],
            'nama_ibu' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
                'null'           => true,
            ],
            'jalur_masuk' => [
                'type'           => 'VARCHAR',
                'constraint'     => '50',
                'default'        => 'Umum',
            ],
            'skor_akhir' => [
                'type'           => 'DECIMAL',
                'constraint'     => '5,2',
                'default'        => 0.00,
            ],
            'status_seleksi' => [
                'type'           => 'ENUM',
                'constraint'     => ['Pending', 'Lolos', 'Gagal'],
                'default'        => 'Pending',
            ],
            // UPDATE: Menambahkan 'Menunggu Verifikasi' sesuai logika Controller
            'status_pembayaran' => [
                'type'           => 'ENUM',
                'constraint'     => ['Belum Bayar', 'Menunggu Verifikasi', 'Lunas', 'Ditolak'],
                'default'        => 'Belum Bayar',
            ],
            'metode_bayar' => [
                'type'           => 'VARCHAR',
                'constraint'     => '100',
                'null'           => true,
            ],
            // UPDATE: Standardisasi nama kolom menjadi bukti_setor
            'bukti_setor' => [
                'type'           => 'VARCHAR',
                'constraint'     => '255',
                'null'           => true,
            ],
            'kode_afiliasi' => [
                'type'           => 'VARCHAR',
                'constraint'     => '50',
                'null'           => true,
            ],
            'nominal_fee' => [
                'type'           => 'DECIMAL',
                'constraint'     => '15,2',
                'default'        => 0.00,
            ],
            'status_fee' => [
                'type'           => 'ENUM',
                'constraint'     => ['Pending', 'Dibayar'],
                'default'        => 'Pending',
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
            'deleted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('pendaftar_id', true);
        $this->forge->addKey('kode_afiliasi'); 
        $this->forge->createTable('pendaftar_biodata');
    }

    public function down()
    {
        $this->forge->dropTable('pendaftar_biodata', true);
        $this->forge->dropTable('affiliates', true);
    }
}