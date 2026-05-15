<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Matikan Foreign Key Checks sementara
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        // 2. Kosongkan tabel (Truncate)
        $this->db->table('role_permissions')->truncate(); 
        $this->db->table('permissions')->truncate();

        // 3. Hidupkan kembali Foreign Key Checks
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $now = date('Y-m-d H:i:s');

        // 4. Data Permissions Lengkap
        $permissionsList = [
            // --- A. DASHBOARD ---
            'A. Dashboard & Akses Utama' => [
                'dashboard_view'              => 'Melihat Dashboard Utama',
                'dashboard_akademik_view'     => 'Melihat Dashboard Akademik',
                'dashboard_keuangan_view'     => 'Melihat Dashboard Keuangan',
                'dashboard_kesiswaan_view'    => 'Melihat Dashboard Kesiswaan',
                'dashboard_kepegawaian_view'  => 'Melihat Dashboard Kepegawaian',
                'dashboard_sapras_view'       => 'Melihat Dashboard Sapras',
                'dashboard_pembelajaran_view' => 'Melihat Dashboard Pembelajaran',
            ],

            // --- B. PENGATURAN SISTEM ---
            'B. Pengaturan Sistem' => [
                'pengaturan_sekolah_manage'   => 'Mengelola Identitas & Kelembagaan Sekolah',
                'pengaturan_akademik_manage'  => 'Mengelola Pengaturan Akademik (Tahun Ajaran Aktif, dll)',
                'pengaturan_keuangan_manage'  => 'Mengelola Pengaturan Keuangan',
                'pengaturan_notifikasi_manage'=> 'Mengelola Notifikasi & Integrasi',
                'pengaturan_log_view'         => 'Melihat Log Aktivitas Sistem',
            ],

            // --- C. MANAJEMEN PENGGUNA ---
            'C. Manajemen Pengguna' => [
                'pengaturan_pengguna_manage'  => 'CRUD Data Pengguna (Users)',
                'pengaturan_hakakses_manage'  => 'CRUD Data Role & Permissions',
            ],

            // --- D. MASTER DATA ---
            'D. Master Data' => [
                'master_jenjang_manage'       => 'CRUD Jenjang Sekolah',
                'master_identitas_manage'     => 'Mengelola Identitas Sekolah',
                'master_organisasi_manage'    => 'CRUD Struktur Organisasi',
                'master_jabatan_manage'       => 'CRUD Data Jabatan',
                'master_jurusan_manage'       => 'CRUD Data Jurusan',
                'master_tahunajaran_manage'   => 'CRUD Tahun Ajaran',
                'master_kurikulum_manage'     => 'CRUD Kurikulum',
                'master_matpel_manage'        => 'CRUD Mata Pelajaran',
                'master_kelas_manage'         => 'CRUD Data Kelas',
                'master_pegawai_manage'       => 'CRUD Data Pegawai/Guru',
                'master_siswa_manage'         => 'CRUD Data Siswa',
                'master_jenispembayaran_manage' => 'CRUD Jenis Pembayaran',
                'master_komponen_gaji_manage' => 'CRUD Komponen Gaji',
            ],

            // --- E. PEMBELAJARAN (PERENCANAAN) ---
            'E. Pembelajaran (Perencanaan)' => [
                'pembelajaran_silabus_manage'    => 'Mengelola Silabus',
                'pembelajaran_rpp_manage'        => 'Mengelola RPP / Modul Ajar',
                'pembelajaran_bahan_ajar_manage' => 'Mengelola Bahan Ajar',
                'pembelajaran_bank_soal_manage'  => 'Mengelola Bank Soal',
                'pembelajaran_evaluasi_manage'   => 'Mengelola Evaluasi Belajar',
            ],

            // --- F. AKADEMIK ---
            'F. Akademik' => [
                'akademik_kalender_manage'       => 'Mengelola Kalender Pendidikan',
                'akademik_jadwal_manage'         => 'Mengelola Jadwal Pelajaran',
                'akademik_absensi_siswa_manage'  => 'Mengelola Absensi Siswa',
                'akademik_absensi_otomatis_manage' => 'Proses Absensi Otomatis',
                'akademik_nilai_manage'          => 'Mengelola Penilaian Siswa',
                'akademik_rapor_manage'          => 'Mengelola dan Mencetak Rapor',
                'akademik_kenaikan_kelas_manage' => 'Proses Kenaikan Kelas',
                'akademik_ijazah_manage'         => 'Mengelola Data Ijazah',
            ],

            // --- G. KEUANGAN ---
            'G. Keuangan' => [
                'keuangan_budget_manage'         => 'Mengelola Budgeting & Target',
                'keuangan_tagihan_manage'        => 'Mengelola Tagihan/Utang Siswa',
                'keuangan_pembayaran_manage'     => 'Mencatat Transaksi Pembayaran (Kasir)',
                'keuangan_pengeluaran_manage'    => 'Mencatat Pengeluaran Operasional',
                'keuangan_laporan_view'          => 'Melihat & Export Laporan Keuangan',
            ],

            // --- H. KEPEGAWAIAN ---
            'H. Kepegawaian (HR)' => [
                'kepegawaian_mesin_absensi'      => 'Integrasi Mesin Absensi',
                'kepegawaian_absensi_manage'     => 'Mengelola Absensi Pegawai (Guru & Staff)',
                'kepegawaian_gaji_manage'        => 'Proses Penggajian (Payroll)',
            ],

            // --- I. KESISWAAN ---
            'I. Kesiswaan' => [
                'kesiswaan_ekskul_manage'        => 'Mengelola Ekstrakurikuler',
                'kesiswaan_bk_manage'            => 'Mengelola Konseling & Pelanggaran (BK)',
                'kesiswaan_prestasi_manage'      => 'Mengelola Data Prestasi',
                'kesiswaan_alumni_manage'        => 'Mengelola Data Alumni',
                'kesiswaan_organisasi_manage'    => 'Mengelola Organisasi Siswa (OSIS dll)',
            ],

            // --- J. SARANA PRASARANA ---
            'J. Sarana Prasarana' => [
                'sapras_manage'                  => 'Mengelola Data Aset, Tanah, Gedung, Barang',
            ],

            // --- K. HUMAS & CMS ---
            'K. Humas & CMS' => [
                'cms_berita_manage'              => 'Mengelola Berita Sekolah',
                'cms_pengumuman_manage'          => 'Mengelola Pengumuman',
                'cms_agenda_manage'              => 'Mengelola Agenda Kegiatan',
                'cms_galeri_manage'              => 'Mengelola Album & Foto Galeri',
            ],

            // --- L. PPDB (ADMIN & MANAJEMEN) ---
            'L. PPDB (Penerimaan Siswa Baru)' => [
                'ppdb_view'                      => 'Melihat Data Pendaftar PPDB',
                'ppdb_manage'                    => 'Mengelola (Edit/Hapus) Data Pendaftar',
                'ppdb_verify'                    => 'Melakukan Verifikasi Data Siswa Baru',
                'ppdb_export'                    => 'Export Data PPDB ke Excel/PDF',
                'ppdb_affiliate_manage'          => 'Mengelola Data Agen Afiliasi & Fee',
                'ppdb_affiliate_config'          => 'Konfigurasi Skema Komisi Afiliasi',
            ],

            // --- M. KERJASAMA ---
            'M. Kerjasama' => [
                'kerjasama_manage'               => 'Mengelola Data Mitra & MOU',
            ],

            // --- N. DATABASE ---
            'N. Database Maintenance' => [
                'database_manage'                => 'Akses Backup, Restore, Import, Export Data',
            ],

            // --- O. PORTAL AKSES (FRONTEND) ---
            'O. Akses Portal Khusus' => [
                'portal_guru_access'             => 'Login ke Portal Guru',
                'portal_siswa_access'            => 'Login ke Portal Siswa',
                'portal_affiliate_access'        => 'Login ke Portal Afiliasi',
                'portal_ppdb_access'             => 'Akses ke Portal Pendaftaran (PPDB)',
            ],

            // --- P. E-LEARNING (LMS) ---
            'P. E-Learning (Kelas Maya)' => [
                'elearning_dashboard_view'       => 'Melihat Dashboard E-Learning',
                'elearning_course_manage'        => 'Membuat & Mengelola Kelas (Forum, Materi)',
                'elearning_course_join'          => 'Bergabung ke Kelas (Siswa)',
                'elearning_content_create'       => 'Membuat Materi, Tugas & Postingan',
                'elearning_interaction_manage'   => 'Moderasi Komentar & Pengumuman',
                'elearning_people_manage'        => 'Mengelola Anggota Kelas (Siswa/Guru)',
                'elearning_quiz_manage'          => 'Mengelola Kuis & Bank Soal Kelas',
                'elearning_grades_view'          => 'Melihat Buku Nilai Kelas',
                'elearning_grades_sync'          => 'Sinkronisasi Nilai ke Akademik',
                'elearning_ai_generator'         => 'Menggunakan AI Generator Materi',
                'elearning_assignment_submit'    => 'Mengerjakan & Mengirim Tugas (Siswa)',
            ]
        ];

        // Flatten data untuk insert batch
        $dataToInsert = [];
        foreach ($permissionsList as $groupName => $perms) {
            foreach ($perms as $key => $desc) {
                $dataToInsert[] = [
                    'permission_key' => $key,       // FIX: Menggunakan 'permission_key' bukan 'name'
                    'description'    => $desc,
                    'group_name'     => $groupName, // Menyertakan group_name
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
        }

        // 5. Insert Batch
        $this->db->table('permissions')->insertBatch($dataToInsert);
        
        echo "Permissions seeded successfully (" . count($dataToInsert) . " items).\n";
    }
}