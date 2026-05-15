<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrasi Data Akademik Sekolah.
 * Meliputi: kelas, grup_siswa, nilai, jadwal, absensi, raport, kenaikan_kelas, kesiswaan.
 * PENTING: Nama file ini (timestamp-nya) HARUS lebih besar/baru dari CreateAcademicMasterData 
 * dan CreateSiswaTable agar dieksekusi setelah tabel master terbentuk.
 */
class CreateAcademicData extends Migration
{
    public function up()
    {
        // FIX 1: Gunakan method bawaan CI4 yang lebih aman daripada raw SQL query
        $this->db->disableForeignKeyChecks();

        // ==========================================
        // 1. KELAS (Struktural)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => [
                'type' => 'VARCHAR', 
                'constraint' => '20', 
                'comment' => 'Relasi ke jenjang_sekolah.kode_jenjang'
            ],
            'nama_kelas' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'tingkat' => ['type' => 'INT', 'constraint' => 2, 'null' => true],
            'id_jurusan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_wali_kelas' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_kurikulum' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], 
            
            'is_aktif' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'kapasitas' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 30,
                'comment'    => 'Batas maksimal siswa dalam kelas'
            ],
            'terisi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'comment'    => 'Jumlah siswa saat ini'
            ],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'angkatan' => ['type' => 'YEAR', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'RESTRICT', 'CASCADE'); 
        
        // FIX: Parameter CI4 adalah (field, table, targetField, ON_UPDATE, ON_DELETE)
        $this->forge->addForeignKey('id_jurusan', 'jurusan', 'id', 'CASCADE', 'SET NULL');
        
        $this->forge->addForeignKey('id_wali_kelas', 'pegawai', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kurikulum', 'kurikulum', 'id', 'RESTRICT', 'RESTRICT'); 
        $this->forge->createTable('kelas', true);
        
        // ==========================================
        // 2. GRUP SISWA (Rombongan Belajar)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Global'],
            'id_kelas' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_grup' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'tahun_ajaran' => ['type' => 'VARCHAR', 'constraint' => '9'],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('grup_siswa', true);

        // ==========================================
        // 3. NILAI SISWA
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            
            // Relasi Utama
            'id_enrollment' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_kelas' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_siswa' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false], 
            'id_mata_pelajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_guru' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false], 
            
            // Atribut
            'semester' => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap']],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'kategori_nilai' => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'PH'],
            
            // Nilai
            'nilai_absensi' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'nilai_tugas' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'nilai_uts' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'nilai_uas' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'nilai_akhir' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'nilai_huruf' => ['type' => 'VARCHAR', 'constraint' => '2', 'null' => true],
            
            // Keterangan
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            
            // System
            'is_deleted_key' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_enrollment', 'siswa_enrollment', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_mata_pelajaran', 'mata_pelajaran', 'id', 'CASCADE', 'CASCADE');
        
        // FIX: ON UPDATE CASCADE, ON DELETE SET NULL
        $this->forge->addForeignKey('id_guru', 'pegawai', 'id', 'CASCADE', 'SET NULL');
        
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['id_siswa', 'id_tahun_ajaran', 'semester']);
        $this->forge->createTable('nilai_siswa', true);

        // ==========================================
        // 4. JADWAL PELAJARAN (FIX 2: Dipindah sebelum Absensi)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20],
            'id_grup_siswa' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_kelas' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_mata_pelajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_guru' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_kurikulum' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_ruangan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'hari' => ['type' => 'VARCHAR', 'constraint' => 10],
            'jam_mulai' => ['type' => 'TIME'],
            'jam_selesai' => ['type' => 'TIME'],
            'is_aktif' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_mata_pelajaran', 'mata_pelajaran', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_guru', 'pegawai', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('kode_jenjang');
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kurikulum', 'kurikulum', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('jadwal_pelajaran', true);

        // ==========================================
        // 5. ABSENSI SISWA (Sekarang bisa langsung relasi ke Jadwal)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'id_jadwal' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_siswa' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal' => ['type' => 'DATE'],
            'status' => ['type' => 'ENUM', 'constraint' => ['hadir', 'sakit', 'izin', 'alpa'], 'default' => 'hadir'],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        
        // FIX: Langsung kaitkan FK disini, tidak butuh query manual ALTER TABLE lagi
        $this->forge->addForeignKey('id_jadwal', 'jadwal_pelajaran', 'id', 'CASCADE', 'SET NULL');
        
        $this->forge->createTable('absensi_siswa', true);

        // ==========================================
        // 6. RAPORT
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_enrollment' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'semester' => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap']],
            'rata_rata' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            
            // Absensi
            'total_sakit' => ['type' => 'INT', 'default' => 0],
            'total_izin' => ['type' => 'INT', 'default' => 0],
            'total_alpa' => ['type' => 'INT', 'default' => 0],
            
            // Catatan
            'catatan_wali_kelas' => ['type' => 'TEXT', 'null' => true],
            'catatan_akademik' => ['type' => 'TEXT', 'null' => true],
            'catatan_karakter' => ['type' => 'TEXT', 'null' => true], 

            // Sikap (Spiritual & Sosial)
            'predikat_spiritual' => ['type' => 'VARCHAR', 'constraint' => '5', 'null' => true], 
            'deskripsi_spiritual' => ['type' => 'TEXT', 'null' => true],
            'predikat_sosial' => ['type' => 'VARCHAR', 'constraint' => '5', 'null' => true],
            'deskripsi_sosial' => ['type' => 'TEXT', 'null' => true],

            // Data Periodik Kesehatan
            'tinggi_badan' => ['type' => 'INT', 'null' => true], 
            'berat_badan' => ['type' => 'INT', 'null' => true],  

            // Keputusan Kenaikan
            'status_kenaikan' => ['type' => 'VARCHAR', 'constraint' => '50', 'null' => true], 

            // Meta Data
            'status_raport' => ['type' => 'ENUM', 'constraint' => ['Draft', 'Published', 'Locked'], 'default' => 'Draft'],
            'tanggal_cetak' => ['type' => 'DATE', 'null' => true],
            'is_locked' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_enrollment', 'siswa_enrollment', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('raport', true);

        // ==========================================
        // 7. KENAIKAN KELAS
        // ==========================================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'id_enrollment_lama' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'comment'        => 'Referensi ke pendaftaran lama'
            ],
            'id_enrollment_baru' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => true,
                'comment'        => 'Referensi ke pendaftaran baru (jika naik/tinggal)'
            ],
            'status_kenaikan' => [
                'type'           => 'ENUM',
                'constraint'     => ['Naik', 'Tinggal', 'Lulus', 'Mutasi', 'Dikeluarkan'],
                'default'        => 'Naik',
            ],
            'tanggal_keputusan' => [
                'type'           => 'DATE',
            ],
            'catatan_guru' => [
                'type'           => 'TEXT',
                'null'           => true,
            ],
            'id_operator' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'null'           => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_enrollment_lama', 'siswa_enrollment', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kenaikan_kelas', true);

        // ==========================================
        // 8. SISWA KESISWAAN (Prestasi/Pelanggaran)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jenis_data' => ['type' => 'ENUM', 'constraint' => ['Prestasi', 'Pelanggaran', 'Ekskul']],
            'tanggal_kejadian' => ['type' => 'DATE'],
            'keterangan' => ['type' => 'TEXT'],
            'poin' => ['type' => 'INT', 'null' => true],
            'level_prestasi' => ['type' => 'ENUM', 'constraint' => ['Sekolah', 'Kabupaten', 'Provinsi', 'Nasional', 'Internasional'], 'null' => true],
            'dicatat_oleh' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'bukti_dokumen' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id', 'CASCADE', 'CASCADE');
        
        // FIX: ON UPDATE CASCADE, ON DELETE SET NULL
        $this->forge->addForeignKey('dicatat_oleh', 'pegawai', 'id', 'CASCADE', 'SET NULL');
        
        $this->forge->createTable('siswa_kesiswaan', true);

        // Aktifkan kembali cek FK
        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        // Drop urutan: Anak -> Induk secara akurat
        $this->forge->dropTable('siswa_kesiswaan', true);
        $this->forge->dropTable('kenaikan_kelas', true);
        $this->forge->dropTable('raport', true);
        
        // FIX: Karena urutan dibalik di "up()", urutan "down()" juga dibalik (Absensi dihapus sebelum Jadwal)
        $this->forge->dropTable('absensi_siswa', true);
        $this->forge->dropTable('jadwal_pelajaran', true); 
        
        $this->forge->dropTable('nilai_siswa', true);
        $this->forge->dropTable('grup_siswa', true);
        $this->forge->dropTable('kelas', true);

        $this->db->enableForeignKeyChecks();
    }
}