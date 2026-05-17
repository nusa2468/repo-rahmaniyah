<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DatabaseSeeder
 * Seeder Utama yang memanggil semua seeder modul lainnya secara berurutan.
 * Dilengkapi dengan fitur Clean Truncate untuk reset database total.
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Non-aktifkan foreign key checks untuk memastikan TRUNCATE berjalan lancar.
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        // 2. Daftar Tabel untuk dibersihkan (Urutan: Anak/Detail -> Induk/Master)
        $tables = [
            // Modul E-Learning
            'el_submissions', 'elearning_comments', 'elearning_posts', 'el_quiz_grades',
            'el_questions', 'el_quizzes', 'el_enrollment', 'el_contents', 'el_topics', 'el_courses',
            
            // Modul Pembelajaran
            'pembelajaran_evaluasi_belajar', 'pembelajaran_bank_soal', 'pembelajaran_bahan_ajar', 'pembelajaran_rpp', 'pembelajaran_silabus',
            
            // ==============================================================================
            // PERBAIKAN: Modul Manajemen Aset (Pengganti Modul Sapras Lama)
            // ==============================================================================
            'aset_pemeliharaan', 'aset_peminjaman', 'aset_pengadaan', 'aset_barang', 'aset_lokasi', 'aset_kategori',
            // ==============================================================================
            
            // Modul Keuangan
            'anggaran_unit', 'pengeluaran', 'kategori_anggaran', 'pembayaran', 'tagihan', 'jenis_pembayaran',
            
            // Modul Kepegawaian Unified
            'riwayat_pendidikan', 
            'pegawai_dokumen', 
            'riwayat_kepegawaian',
            'riwayat_gaji_pegawai', 
            'gaji_pegawai', 
            'absensi_pegawai', 
            'pegawai',

            // Modul Kesiswaan (Prestasi/Pelanggaran)
            'siswa_kesiswaan',
            'kesiswaan_prestasi',
            'kesiswaan_alumni',
            'kesiswaan_bk_catatan',
            'kesiswaan_bk_kategori',
            'kesiswaan_organisasi',
            'kesiswaan_ekskul_presensi',
            'kesiswaan_ekskul_anggota',
            'kesiswaan_ekskul',

            // Modul Akademik & Kesiswaan (Transaksional)
            'raport', 
            'nilai_siswa', 
            'peserta_ekskul', 
            'siswa_enrollment', 
            'siswa_akademik', 
            'siswa_keluarga', 
            'siswa_demografi', 
            'osis_pengurus', 'osis_periode', 'osis',
            'kenaikan_kelas', 'absensi_siswa', 'jadwal_pelajaran', 'grup_siswa', 'penugasan_mengajar',
            
            // Entitas Utama
            'pendaftar_biodata', 'siswa', 'orang_tua', 'kelas', 'ekstrakurikuler',
            'jurusan', 'tahun_ajaran', 'organisasi', 'jabatan',
            
            // Master & System & CMS
            'foto', 'album_foto', 'agenda', 'pengumuman', 'berita',
            'settings', 'jenjang_sekolah', 'affiliates', 'alumni', 'kerjasama', 'kalender_pendidikan', 'bk',
            
            // --- UPDATE: Tabel Otorisasi RBAC ---
            'role_permissions', 'users', 'roles', 'permissions',
        ];

        echo "Cleaning up database tables...\n";
        foreach ($tables as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->table($table)->truncate();
                if ($this->db->getPlatform() === 'MySQLi') {
                    $this->db->query("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                }
            }
        }

        // 3. Jalankan Seeder
        $this->call('RbacSeeder');
        $this->call('InitialSeeder');
        $this->call('JenjangSeeder'); 
        $this->call('SettingsSeeder');
        $this->call('OrganisasiAndJabatanSeeder'); 
        $this->call('TahunAjaranSeeder');
        
        // --- SEEDING KURIKULUM & MAPEL ---
        $this->call('KurikulumSeeder');
        $this->call('MataPelajaranSeeder');
        
        $this->call('KalenderPendidikanSeeder');
        $this->call('PegawaiSeeder'); 
        $this->call('PegawaiDetailSeeder'); 
        
        // ==============================================================================
        // AKTIFASI SEEDER ASET
        // ==============================================================================
        $this->call('ManajemenAsetSeeder');
        // ==============================================================================
        
        $this->call('SiswaSeeder'); 
        $this->call('KeuanganSeeder');
        $this->call('AcademicDataSeeder');
        $this->call('KesiswaanMasterDataSeeder'); 
        $this->call('PpdbSeeder');
        $this->call('CmsSeeder'); 
        $this->call('KerjasamaSeeder');

        // ==============================================================================
        // KUNCI PERBAIKAN: MEMATIKAN SEMENTARA SEEDER YANG MERUSAK DATA MAPEL
        // ==============================================================================
        // Modul di bawah ini dicurigai memiliki skrip truncate('mata_pelajaran')
        // yang menghancurkan 162 data Anda dan menggantinya dengan 4 data dummy.
        
        echo "\n[INFO] PembelajaranSeeder & ElearningSeeder DIMATIKAN sementara untuk melindungi data Mapel.\n";
        
        // $this->call('PembelajaranSeeder');
        // $this->call('ElearningSeeder'); 
        
        // ==============================================================================

        // 4. Aktifkan kembali foreign key checks
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
        
        echo "\n>>> SELURUH PROSES SEEDING BERHASIL DISINKRONISASI! <<<\n";
    }
}