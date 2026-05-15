<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePegawaiDetailTable extends Migration
{
    public function up()
    {
        // =================================================================
        // 1. TABEL KOMPONEN GAJI (Master Data) - DITAMBAHKAN
        // =================================================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'kode_komponen' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true, // Kode unik, misal: GAPOK_SD
            ],
            'kode_jenjang' => [ // Scope Unit (SD/SMP/SMA/GLOBAL)
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'GLOBAL',
            ],
            'nama_komponen' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'tipe' => [ // 1: Pendapatan, 2: Potongan
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'comment'    => '1=Pendapatan, 2=Potongan'
            ],
            'metode_hitung' => [
                'type'       => 'ENUM',
                'constraint' => ['fixed', 'variabel', 'manual'], 
                'default'    => 'fixed',
                'comment'    => 'fixed=tetap, variabel=per kehadiran, manual=input saat generate'
            ],
            'nominal_default' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'is_default' => [ // Apakah otomatis ditambahkan ke pegawai baru?
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'is_aktif' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['kode_jenjang', 'tipe']); // Index untuk filter
        $this->forge->createTable('komponen_gaji', true);

        // =================================================================
        // 2. TABEL ABSENSI PEGAWAI (Unified Log Harian)
        // =================================================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'id_pegawai' => [ // Relasi ke tabel pegawai (gabungan guru & staff)
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            // Snapshot Unit Kerja (Penting untuk filter history jika pegawai pindah unit)
            'kode_jenjang' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            'tanggal' => [
                'type' => 'DATE'
            ],
            'jam_masuk' => [
                'type' => 'TIME',
                'null' => true
            ],
            'jam_keluar' => [
                'type' => 'TIME',
                'null' => true
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['hadir', 'sakit', 'izin', 'alpa', 'terlambat', 'cuti', 'dinas_luar'],
                'default'    => 'hadir'
            ],
            'metode_absen' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'manual',
                'comment'    => 'manual / fingerprint / face_id / mobile_gps',
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'bukti_foto_masuk' => [ // Opsional: Foto selfie/lokasi saat masuk
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'bukti_foto_keluar' => [ // Opsional: Foto selfie/lokasi saat pulang
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'id_user_admin' => [ // Audit trail: Siapa yang input/edit manual
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_pegawai', 'tanggal']); // Index komposit untuk query harian
        $this->forge->addKey('kode_jenjang');
        // Relasi ke tabel pegawai
        $this->forge->addForeignKey('id_pegawai', 'pegawai', 'id', 'CASCADE', 'CASCADE', 'fk_absensi_pegawai');
        $this->forge->createTable('absensi_pegawai', true);

        // =================================================================
        // 3. TABEL GAJI PEGAWAI (Setting Komponen Gaji Individu)
        // =================================================================
        // Tabel ini menyimpan nominal KHUSUS per pegawai jika berbeda dari standar master
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'id_pegawai' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'kode_jenjang' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            'id_komponen' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            // Nominal Setting (Decimal untuk uang)
            'jumlah_set' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'is_active' => [ // Status aktif komponen ini untuk pegawai tsb
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true], // Soft delete slip

        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['id_pegawai', 'id_komponen']); // Index pencarian cepat
        $this->forge->addForeignKey('id_pegawai', 'pegawai', 'id', 'CASCADE', 'CASCADE', 'fk_gaji_pegawai');
        $this->forge->addForeignKey('id_komponen', 'komponen_gaji', 'id', 'CASCADE', 'CASCADE', 'fk_komponen_gaji_pegawai');
        $this->forge->createTable('gaji_pegawai', true);

        // =================================================================
        // 4. TABEL RIWAYAT GAJI (Slip Gaji Bulanan / Transaksi)
        // =================================================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'no_transaksi' => [ // Kode unik slip gaji (misal: SLIP/202501/001)
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'id_pegawai' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true // Set null on delete pegawai (keep history)
            ],
            // Snapshot Data Pegawai (Agar histori tetap valid meski data master berubah)
            'nama_pegawai' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'jabatan_pegawai' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'kode_jenjang' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
            ],
            
            // Periode Gaji
            'bulan' => [
                'type'       => 'VARCHAR',
                'constraint' => 2, // 01 - 12
            ],
            'tahun' => [
                'type'       => 'VARCHAR',
                'constraint' => 4, // 2025
            ],
            
            // Rekapitulasi Keuangan
            'total_pendapatan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'total_potongan' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            'gaji_bersih' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00
            ],
            
            // Status & Meta
            'status_bayar' => [
                'type'       => 'ENUM',
                'constraint' => ['Belum Dibayar', 'Dibayar', 'Ditahan', 'Dibatalkan'],
                'default'    => 'Belum Dibayar'
            ],
            'tanggal_bayar' => [
                'type' => 'DATE',
                'null' => true
            ],
            'metode_bayar' => [ // Transfer / Tunai
                 'type'       => 'VARCHAR',
                 'constraint' => '20',
                 'null'       => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true], // Soft delete slip
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['bulan', 'tahun', 'kode_jenjang']); // Index filter laporan
        $this->forge->addForeignKey('id_pegawai', 'pegawai', 'id', 'SET NULL', 'CASCADE', 'fk_riwayat_gaji_pegawai');
        $this->forge->createTable('riwayat_gaji_pegawai', true);
    }

    public function down()
    {
        // Drop urutan terbalik untuk menghindari error FK constraint
        $this->forge->dropTable('riwayat_gaji_pegawai', true);
        $this->forge->dropTable('gaji_pegawai', true);
        $this->forge->dropTable('absensi_pegawai', true);
        $this->forge->dropTable('komponen_gaji', true);
    }
}