<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrasi Khusus Data Siswa dan Relasi Terdekatnya.
 * Meliputi: orang_tua, siswa, siswa_demografi, siswa_keluarga, siswa_akademik, siswa_enrollment.
 * UPDATED: Disinkronisasi dengan Standar Dapodik (Field Wajib)
 */
class CreateSiswaTable extends Migration
{
    public function up()
    {
        // FIX: Matikan cek FK sementara agar aman dari error "Table exists" atau constraint issues
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        // ==========================================
        // 1. ORANG TUA TABLE (Portal Users / Induk)
        // ==========================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_lengkap'  => ['type' => 'VARCHAR', 'constraint' => '150'],
            'email'         => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true],
            'password'      => ['type' => 'VARCHAR', 'constraint' => '255'],
            'no_telepon'    => ['type' => 'VARCHAR', 'constraint' => '15'],
            'pekerjaan'     => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'alamat'        => ['type' => 'TEXT', 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['aktif', 'nonaktif'], 'default' => 'aktif'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('orang_tua', true);

        // ==========================================
        // 2. SISWA (Main Table - SINKRONISASI DAPODIK)
        // ==========================================
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'        => ['type' => 'VARCHAR', 'constraint' => '20'],
            'id_jurusan'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'angkatan'            => ['type' => 'YEAR', 'null' => true],
            'tahun_keluar'        => ['type' => 'YEAR', 'null' => true],
            'status'              => ['type' => 'ENUM', 'constraint' => ['AKTIF', 'TERDAFTAR', 'LULUS', 'PINDAH', 'MUTASI', 'DIKELUARKAN', 'MENINGGAL'], 'default' => 'AKTIF'],
            'id_orang_tua_portal' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            
            // --- Identitas Utama (Wajib Dapodik) ---
            'nis'                 => ['type' => 'VARCHAR', 'constraint' => '50', 'unique' => true],
            'nisn'                => ['type' => 'VARCHAR', 'constraint' => '10', 'unique' => true, 'null' => true], // Wajib Dapodik
            'nik'                 => ['type' => 'VARCHAR', 'constraint' => '16', 'unique' => true, 'null' => true], // Wajib Dapodik
            'nama_lengkap'        => ['type' => 'VARCHAR', 'constraint' => '255'],
            'jenis_kelamin'       => ['type' => 'ENUM', 'constraint' => ['L', 'P'], 'default' => 'L'],
            
            // --- Tambahan Field untuk Sync Import Excel ---
            'tempat_lahir'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tanggal_lahir'       => ['type' => 'DATE', 'null' => true],
            'nama_ibu_kandung'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], // Wajib Dapodik
            'agama'               => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'alamat'              => ['type' => 'TEXT', 'null' => true], // Alamat Domisili
            
            // Login & Kontak
            'email'               => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true, 'null' => true],
            'password'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'foto'                => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Pastikan tabel 'jurusan' dan 'jenjang_sekolah' sudah ada di migrasi lain
        $this->forge->addForeignKey('id_jurusan', 'jurusan', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('id_orang_tua_portal', 'orang_tua', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'RESTRICT', 'CASCADE'); 
        $this->forge->addKey('nama_lengkap');
        $this->forge->addKey('status');
        $this->forge->createTable('siswa', true);

        // ==========================================
        // 3. SISWA DEMOGRAFI (Detail Data)
        // ==========================================
        $this->forge->addField([
            'id_siswa'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_panggilan'    => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            // Field ini redundan dengan tabel siswa, tapi bisa disimpan untuk history/detail
            'tempat_lahir'      => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true], 
            'tanggal_lahir'     => ['type' => 'DATE', 'null' => true],
            'agama'             => ['type' => 'ENUM', 'constraint' => ['ISLAM', 'KRITEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KONGHUCU', 'KEPERCAYAAN'], 'null' => true],
            'kewarganegaraan'   => ['type' => 'ENUM', 'constraint' => ['WNI', 'WNA'], 'default' => 'WNI'],
            'no_akta_lahir'     => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'status_anak'       => ['type' => 'ENUM', 'constraint' => ['KANDUNG', 'ANGKAT', 'TIRI'], 'null' => true],
            'nama_ayah'         => ['type' => 'VARCHAR', 'constraint' => '150', 'null' => true],
            'nama_ibu'          => ['type' => 'VARCHAR', 'constraint' => '150', 'null' => true],
            'alamat'            => ['type' => 'TEXT', 'null' => true],
            'rt'                => ['type' => 'VARCHAR', 'constraint' => '5', 'null' => true],
            'rw'                => ['type' => 'VARCHAR', 'constraint' => '5', 'null' => true],
            'dusun'             => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'kelurahan'         => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'kecamatan'         => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'kode_pos'          => ['type' => 'VARCHAR', 'constraint' => '10', 'null' => true],
            'lintang'           => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'bujur'             => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'telepon'           => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'email'             => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'no_kk'             => ['type' => 'VARCHAR', 'constraint' => '16', 'null' => true],
            'jenis_pendaftaran' => ['type' => 'ENUM', 'constraint' => ['SISWA BARU', 'PINDAHAN', 'KEMBALI BERSEKOLAH'], 'default' => 'SISWA BARU'],
            'asal_sekolah'      => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'no_seri_ijazah'    => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'nomor_ijazah'      => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'tanggal_lulus'     => ['type' => 'DATE', 'null' => true],
            'alasan_keluar'     => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'penerimaan_kps'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'no_kps'            => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'penerimaan_kip'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'no_kip'            => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'penerimaan_kks'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'no_kks'            => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_siswa', true); // Primary Key is Foreign Key
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('siswa_demografi', true);

        // ==========================================
        // 4. SISWA KELUARGA
        // ==========================================
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'hubungan'     => ['type' => 'ENUM', 'constraint' => ['AYAH', 'IBU', 'WALI', 'SAUDARA', 'LAINNYA'], 'default' => 'LAINNYA'],
            'nama_lengkap' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'nik'          => ['type' => 'VARCHAR', 'constraint' => '16', 'null' => true],
            'pekerjaan'    => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'pendidikan'   => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'no_telepon'   => ['type' => 'VARCHAR', 'constraint' => '15', 'null' => true],
            'penghasilan'  => ['type' => 'DECIMAL', 'constraint' => '10,2', 'null' => true],
            'alamat'       => ['type' => 'TEXT', 'null' => true],
            'is_wali'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('siswa_keluarga', true);

        // ==========================================
        // 5. SISWA AKADEMIK (Data Masuk & Statis)
        // ==========================================
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jalur_penerimaan'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'ZONASI, PRESTASI, TES, DLL'],
            'nomor_pendaftaran' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'tanggal_diterima'  => ['type' => 'DATE', 'null' => true],
            'program_peminatan' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'comment' => 'IPA, IPS, dll'],
            'sk_yudisium_masuk' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'nilai_masuk'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'catatan_khusus'    => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('siswa_akademik', true);

        // ==========================================
        // 6. SISWA ENROLLMENT (Riwayat Kelas)
        // ==========================================
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_kelas'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_grup_siswa'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'semester'        => ['type' => 'ENUM', 'constraint' => ['GANJIL', 'GENAP']],
            'tanggal_masuk'   => ['type' => 'DATE', 'null' => true],
            'status_akademik' => ['type' => 'ENUM', 'constraint' => ['AKTIF', 'LULUS', 'PINDAH'], 'default' => 'AKTIF'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        
        // Pastikan 'kelas', 'grup_siswa', dan 'tahun_ajaran' sudah ada di migrasi lain
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_grup_siswa', 'grup_siswa', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id', 'RESTRICT', 'CASCADE');
        
        $this->forge->addUniqueKey(['id_siswa', 'id_tahun_ajaran']);
        $this->forge->createTable('siswa_enrollment', true);

        // Kembalikan pemeriksaan FK
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        // Drop urutan: Anak -> Induk
        $this->forge->dropTable('siswa_enrollment', true);
        $this->forge->dropTable('siswa_akademik', true);
        $this->forge->dropTable('siswa_keluarga', true);
        $this->forge->dropTable('siswa_demografi', true);
        
        $this->forge->dropTable('siswa', true);
        $this->forge->dropTable('orang_tua', true);

        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}