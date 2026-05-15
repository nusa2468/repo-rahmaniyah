<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreatePegawaiTable extends Migration
{
    public function up()
    {
        // ==========================================
        // 1. TABEL PEGAWAI (INDUK)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [ // Relasi ke tabel Users (Login)
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'kode_jenjang' => [ // Scope Unit Kerja (SD/SMP/SMA/GLOBAL)
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'GLOBAL',
            ],
            
            // --- IDENTITAS DIRI (DAPODIK) ---
            'nama_lengkap' => [
                'type'       => 'VARCHAR',
                'constraint' => '100', // Sesuai Dapodik
            ],
            'gelar_depan' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'gelar_belakang' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'nik' => [ // Nomor Induk Kependudukan (Wajib)
                'type'       => 'VARCHAR',
                'constraint' => '16', 
                'null'       => true,
            ],
            'nuptk' => [ // Nomor Unik Pendidik dan Tenaga Kependidikan
                'type'       => 'VARCHAR',
                'constraint' => '16',
                'null'       => true,
                'comment'    => 'Wajib bagi Guru yang sudah sertifikasi',
            ],
            'nip' => [ // Nomor Induk Pegawai (PNS)
                'type'       => 'VARCHAR',
                'constraint' => '18',
                'null'       => true, 
            ],
            'nipy' => [ // Nomor Induk Pegawai Yayasan (Internal Yayasan)
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true, 
            ],
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'],
                'default'    => 'L',
            ],
            'tempat_lahir' => [
                'type'       => 'VARCHAR',
                'constraint' => '32',
                'null'       => true,
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'nama_ibu_kandung' => [ // Wajib di Dapodik untuk verifikasi data
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'agama' => [
                'type'       => 'VARCHAR',
                'constraint' => '20', // Islam, Kristen, dll
                'null'       => true,
            ],
             'status_perkawinan' => [
                'type'       => 'ENUM',
                'constraint' => ['Kawin', 'Belum Kawin', 'Janda/Duda'],
                'null'       => true,
            ],

            // --- KONTAK & DOMISILI (RINCIAN ALAMAT DAPODIK) ---
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '60',
                'null'       => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
            ],
            'alamat_jalan' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'rt' => [
                'type'       => 'VARCHAR',
                'constraint' => '3',
                'null'       => true,
            ],
            'rw' => [
                'type'       => 'VARCHAR',
                'constraint' => '3',
                'null'       => true,
            ],
            'nama_dusun' => [
                'type'       => 'VARCHAR',
                'constraint' => '60',
                'null'       => true,
            ],
            'desa_kelurahan' => [
                'type'       => 'VARCHAR',
                'constraint' => '60',
                'null'       => true,
            ],
            'kecamatan' => [
                'type'       => 'VARCHAR',
                'constraint' => '60',
                'null'       => true,
            ],
            'kode_pos' => [
                'type'       => 'VARCHAR',
                'constraint' => '5',
                'null'       => true,
            ],

            // --- KEPEGAWAIAN (DAPODIK) ---
            'status_kepegawaian' => [ 
                // Ref Dapodik: PNS, PNS Diperbantukan, GTY/PTY, GTT/PTT, Guru Honor Sekolah, Tenaga Honor Sekolah
                'type'       => 'VARCHAR',
                'constraint' => '50', 
                'default'    => 'GTY/PTY', // Guru/Pegawai Tetap Yayasan
            ],
            'jenis_ptk' => [ 
                // Ref Dapodik: Guru Mapel, Guru Kelas, Kepala Sekolah, Tenaga Administrasi Sekolah, Penjaga Sekolah, dll
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Guru Mapel',
            ],
            'tugas_tambahan' => [
                // e.g. Kepala Sekolah, Wakil Kepala Sekolah, Kepala Perpustakaan
                 'type'       => 'VARCHAR',
                 'constraint' => '50',
                 'null'       => true,
            ],
            'sk_pengangkatan' => [
                'type'       => 'VARCHAR',
                'constraint' => '80',
                'null'       => true,
            ],
            'tmt_pengangkatan' => [ // Tanggal Mulai Tugas
                'type' => 'DATE',
                'null' => true,
            ],
            'sumber_gaji' => [ // e.g. Yayasan, APBD, APBN
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
             'pendidikan_terakhir' => [
                'type'       => 'VARCHAR',
                'constraint' => '20', // S1, S2, D3, SMA
                'null'       => true,
            ],

            // --- SISTEM ---
            'jenis_pegawai' => [ 
                // Simplifikasi untuk logic aplikasi (Gaji/Absen)
                'type'       => 'ENUM',
                'constraint' => ['guru', 'staff', 'penunjang'],
                'default'    => 'staff',
                'comment'    => 'Grouping sederhana untuk filter sistem'
            ],
            'status_aktif' => [ // Status keaktifan di sistem
                'type'       => 'ENUM',
                'constraint' => ['aktif', 'nonaktif', 'cuti', 'pensiun', 'meninggal'],
                'default'    => 'aktif',
            ],
            
            // Foto Profil
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],

            // Timestamps & Soft Deletes
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('nik');   // Penting untuk pencarian NIK
        $this->forge->addKey('nuptk'); // Penting untuk pencarian NUPTK
        $this->forge->addKey(['jenis_pegawai', 'kode_jenjang']); // Index komposit untuk filter sistem
        $this->forge->createTable('pegawai', true);

        // ==========================================
        // 2. TABEL DOKUMEN PEGAWAI (ANAK)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pegawai' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jenis_dokumen' => [ // KTP, IJAZAH, SK_PENGANGKATAN, FOTO_PROFIL, LAINNYA
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_file' => [
                'type'       => 'VARCHAR', // Nama file asli saat diupload
                'constraint' => 255,
            ],
            'file_path' => [
                'type'       => 'VARCHAR', // Nama file tersimpan (random name)
                'constraint' => 255,
            ],
            'tipe_file' => [ // pdf, jpg, png
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'ukuran_file' => [ // dalam KB
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        // Relasi ke tabel pegawai (Cascade delete: jika pegawai dihapus, dokumen ikut terhapus)
        $this->forge->addForeignKey('id_pegawai', 'pegawai', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pegawai_dokumen', true);

        // ==========================================
        // 3. TABEL RIWAYAT PENDIDIKAN (ANAK)
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pegawai' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jenjang' => [ // SD, SMP, SMA, S1, S2, S3, D3
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nama_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'jurusan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tahun_masuk' => [
                'type'       => 'YEAR',
                'null'       => true,
            ],
            'tahun_lulus' => [
                'type'       => 'YEAR',
                'null'       => true,
            ],
            'nilai_akhir' => [ // IPK atau Nilai Rata-rata
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_pegawai', 'pegawai', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('riwayat_pendidikan', true);

        // ==========================================
        // 4. TABEL RIWAYAT KEPEGAWAIAN (ANAK) - NEW
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pegawai' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'jenis_sk' => [ // SK Pengangkatan, Kenaikan Pangkat, Berkala
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Pengangkatan',
            ],
            'no_sk' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tanggal_sk' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tmt_sk' => [ // Terhitung Mulai Tanggal
                'type' => 'DATE',
                'null' => true,
            ],
            'masa_kerja_tahun' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 0,
            ],
            'masa_kerja_bulan' => [
                'type'       => 'INT',
                'constraint' => 2,
                'default'    => 0,
            ],
            'status_kepegawaian' => [
                'type'       => 'VARCHAR',
                'constraint' => 50, 
            ],
            'pangkat_golongan' => [ // Pembina IV/a, Penata III/c
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'jabatan_fungsional' => [ // Guru Madya, Guru Muda
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'is_aktif' => [ // Penanda apakah ini SK yang sedang berlaku
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_pegawai', 'pegawai', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('riwayat_kepegawaian', true);
    }

    public function down()
    {
        // Hapus tabel anak terlebih dahulu untuk menjaga integritas foreign key
        $this->forge->dropTable('riwayat_kepegawaian', true);
        $this->forge->dropTable('riwayat_pendidikan', true);
        $this->forge->dropTable('pegawai_dokumen', true);
        $this->forge->dropTable('pegawai', true);
    }
}