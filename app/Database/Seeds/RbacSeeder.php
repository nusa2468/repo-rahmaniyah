<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ============================================================================
 * SEEDER CORE SYSTEM (AUTHENTICATION & AUTHORIZATION)
 * ============================================================================
 * Menangani pengisian data secara dinamis.
 * Menggunakan ignore(true) agar seeder ini aman dijalankan berulang kali 
 * (Idempotent) tanpa menghasilkan error Duplicate Key.
 */
class RbacSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // --------------------------------------------------------------------
        // 1. DATA ROLES
        // --------------------------------------------------------------------
        $rolesData = [
            ['name' => 'superadmin', 'description' => 'Administrator Utama Yayasan/Pusat dengan hak akses seluruh unit', 'created_at' => $now],
            ['name' => 'admin', 'description' => 'Administrator Unit Sekolah (Kepala Sekolah/Waka)', 'created_at' => $now],
            ['name' => 'operator_akademik', 'description' => 'Staf TU/Operator Kurikulum Sekolah', 'created_at' => $now],
            ['name' => 'staf_keuangan', 'description' => 'Bendahara Sekolah / Administrasi Keuangan', 'created_at' => $now],
            ['name' => 'staf_kepegawaian', 'description' => 'Staf HRD / Administrasi Guru & Karyawan', 'created_at' => $now],
            ['name' => 'staf_sapras', 'description' => 'Staf Inventaris / Sarana Prasarana', 'created_at' => $now],
            ['name' => 'staf_humas', 'description' => 'Staf Hubungan Masyarakat & PSB', 'created_at' => $now],
            ['name' => 'guru', 'description' => 'Tenaga Pendidik (Akses Portal Guru)', 'created_at' => $now],
            ['name' => 'siswa', 'description' => 'Peserta Didik (Akses Portal Siswa)', 'created_at' => $now],
        ];
        
        // Gunakan ignore(true) agar tidak error jika data role sudah ada
        $this->db->table('roles')->ignore(true)->insertBatch($rolesData);


        // --------------------------------------------------------------------
        // 2. DATA PERMISSIONS (A - K)
        // --------------------------------------------------------------------
        $permissionsData = [
            // A. Dashboard
            ['permission_key' => 'dashboard_view', 'description' => 'Melihat Dashboard Utama', 'group_name' => 'A. Dashboard', 'created_at' => $now],
            // B. Pengaturan Umum
            ['permission_key' => 'pengaturan_umum_manage', 'description' => 'Mengelola Identitas Sekolah & Aplikasi', 'group_name' => 'B. Pengaturan Umum', 'created_at' => $now],
            ['permission_key' => 'pengaturan_notifikasi_manage', 'description' => 'Mengelola Integrasi WA/Email Notifikasi', 'group_name' => 'B. Pengaturan Umum', 'created_at' => $now],
            ['permission_key' => 'pengaturan_log_view', 'description' => 'Melihat Log Jejak Audit Sistem', 'group_name' => 'B. Pengaturan Umum', 'created_at' => $now],
            // C. Pengaturan Pengguna
            ['permission_key' => 'pengaturan_pengguna_manage', 'description' => 'CRUD Data Akun Pengguna (Users)', 'group_name' => 'C. Pengaturan Pengguna', 'created_at' => $now],
            ['permission_key' => 'pengaturan_hakakses_manage', 'description' => 'Mengatur Matriks Role & Permission', 'group_name' => 'C. Pengaturan Pengguna', 'created_at' => $now],
            // D. Master Data
            ['permission_key' => 'master_guru_manage', 'description' => 'Mengelola Database Guru (Pendidik)', 'group_name' => 'D. Master Data', 'created_at' => $now],
            ['permission_key' => 'master_siswa_manage', 'description' => 'Mengelola Database Siswa (Peserta Didik)', 'group_name' => 'D. Master Data', 'created_at' => $now],
            ['permission_key' => 'master_karyawan_manage', 'description' => 'Mengelola Database Karyawan (Staf)', 'group_name' => 'D. Master Data', 'created_at' => $now],
            ['permission_key' => 'master_matpel_manage', 'description' => 'Mengelola Daftar Mata Pelajaran', 'group_name' => 'D. Master Data', 'created_at' => $now],
            ['permission_key' => 'master_kelas_manage', 'description' => 'Mengelola Ruang Kelas & Rombel', 'group_name' => 'D. Master Data', 'created_at' => $now],
            ['permission_key' => 'master_tahunajaran_manage', 'description' => 'Mengelola Periode Tahun Ajaran & Semester', 'group_name' => 'D. Master Data', 'created_at' => $now],
            ['permission_key' => 'master_jenjang_manage', 'description' => 'Mengelola Tingkat Pendidikan (Jenjang)', 'group_name' => 'D. Master Data', 'created_at' => $now],
            // E. Akademik
            ['permission_key' => 'akademik_setting_manage', 'description' => 'Konfigurasi Parameter Akademik', 'group_name' => 'E. Akademik', 'created_at' => $now],
            ['permission_key' => 'akademik_jadwal_manage', 'description' => 'Menyusun Jadwal Pelajaran Mingguan', 'group_name' => 'E. Akademik', 'created_at' => $now],
            ['permission_key' => 'akademik_absensi_siswa_manage', 'description' => 'Input & Rekap Absensi Siswa', 'group_name' => 'E. Akademik', 'created_at' => $now],
            ['permission_key' => 'akademik_nilai_manage', 'description' => 'Input & Olah Nilai Mata Pelajaran', 'group_name' => 'E. Akademik', 'created_at' => $now],
            ['permission_key' => 'akademik_rapor_manage', 'description' => 'Proses Cetak & Distribusi Rapor', 'group_name' => 'E. Akademik', 'created_at' => $now],
            ['permission_key' => 'akademik_e_learning_manage', 'description' => 'Mengelola Modul & Tugas E-Learning', 'group_name' => 'E. Akademik', 'created_at' => $now],
            // F. Keuangan
            ['permission_key' => 'keuangan_setting_manage', 'description' => 'Setting Pos Anggaran & Tarif Bayar', 'group_name' => 'F. Keuangan', 'created_at' => $now],
            ['permission_key' => 'keuangan_jenispembayaran_manage', 'description' => 'Kelola Jenis Pembayaran (SPP/Gedung)', 'group_name' => 'F. Keuangan', 'created_at' => $now],
            ['permission_key' => 'keuangan_tagihan_manage' , 'description' => 'Generate Tagihan Siswa Massal', 'group_name' => 'F. Keuangan', 'created_at' => $now],
            ['permission_key' => 'keuangan_pembayaran_manage', 'description' => 'Proses Transaksi Pembayaran Siswa', 'group_name' => 'F. Keuangan', 'created_at' => $now],
            // G. Kepegawaian (HRD)
            ['permission_key' => 'kepegawaian_komponen_gaji_manage', 'description' => 'Setting Tunjangan & Potongan Gaji', 'group_name' => 'G. Kepegawaian (HRD)', 'created_at' => $now],
            ['permission_key' => 'kepegawaian_absensi_guru_manage', 'description' => 'Olah Absensi Pendidik', 'group_name' => 'G. Kepegawaian (HRD)', 'created_at' => $now],
            ['permission_key' => 'kepegawaian_absensi_karyawan_manage', 'description' => 'Olah Absensi Staf/Karyawan', 'group_name' => 'G. Kepegawaian (HRD)', 'created_at' => $now],
            ['permission_key' => 'kepegawaian_penggajian_guru_manage', 'description' => 'Generate Slip Gaji Guru', 'group_name' => 'G. Kepegawaian (HRD)', 'created_at' => $now],
            ['permission_key' => 'kepegawaian_penggajian_karyawan_manage', 'description' => 'Generate Slip Gaji Karyawan', 'group_name' => 'G. Kepegawaian (HRD)', 'created_at' => $now],
            // H. Sarana & Prasarana
            ['permission_key' => 'sapras_manage', 'description' => 'Inventarisasi Aset, Ruang, & Tanah', 'group_name' => 'H. Sarana & Prasarana', 'created_at' => $now],
            // I. Humas & PSB
            ['permission_key' => 'psb_manage', 'description' => 'Mengelola Pendaftaran Siswa Baru', 'group_name' => 'I. Humas & PSB', 'created_at' => $now],
            ['permission_key' => 'humas_berita_manage', 'description' => 'Publikasi Berita, Agenda, & Galeri', 'group_name' => 'I. Humas & PSB', 'created_at' => $now],
            // J. Laporan
            ['permission_key' => 'laporan_akademik_view', 'description' => 'Melihat & Export Laporan Pendidikan', 'group_name' => 'J. Laporan', 'created_at' => $now],
            ['permission_key' => 'laporan_keuangan_view', 'description' => 'Melihat & Export Laporan Transaksi', 'group_name' => 'J. Laporan', 'created_at' => $now],
            ['permission_key' => 'laporan_kepegawaian_view', 'description' => 'Melihat & Export Laporan SDM', 'group_name' => 'J. Laporan', 'created_at' => $now],
            // K. Portal
            ['permission_key' => 'portal_guru_access', 'description' => 'Hak Akses Masuk Dashboard Guru', 'group_name' => 'K. Portal', 'created_at' => $now],
            ['permission_key' => 'portal_siswa_access', 'description' => 'Hak Akses Masuk Dashboard Siswa', 'group_name' => 'K. Portal', 'created_at' => $now],
        ];

        $this->db->table('permissions')->ignore(true)->insertBatch($permissionsData);


        // --------------------------------------------------------------------
        // 3. MAPPING ROLE_PERMISSIONS (Hak Akses Matriks)
        // --------------------------------------------------------------------
        // Tarik ulang ID dari database untuk memastikan mapping presisi
        $roleIds = array_column($this->db->table('roles')->get()->getResultArray(), 'id', 'name');
        $permIds = array_column($this->db->table('permissions')->get()->getResultArray(), 'id', 'permission_key');

        $assign = [];

        // 1. SUPERADMIN (Akses Total)
        foreach ($permIds as $id) {
            $assign[] = ['role_id' => $roleIds['superadmin'], 'permission_id' => $id];
        }

        // 2. ADMIN UNIT
        $adminPerms = ['dashboard_view', 'master_guru_manage', 'master_siswa_manage', 'master_karyawan_manage', 'master_matpel_manage', 'master_kelas_manage', 'akademik_absensi_siswa_manage', 'akademik_nilai_manage', 'akademik_rapor_manage', 'keuangan_pembayaran_manage', 'laporan_akademik_view', 'laporan_keuangan_view', 'humas_berita_manage'];
        foreach ($adminPerms as $key) {
            if (isset($permIds[$key])) $assign[] = ['role_id' => $roleIds['admin'], 'permission_id' => $permIds[$key]];
        }

        // 3. OPERATOR AKADEMIK
        $opPerms = ['dashboard_view', 'master_siswa_manage', 'master_matpel_manage', 'master_kelas_manage', 'akademik_jadwal_manage', 'akademik_absensi_siswa_manage', 'akademik_nilai_manage', 'laporan_akademik_view', 'psb_manage'];
        foreach ($opPerms as $key) {
            if (isset($permIds[$key])) $assign[] = ['role_id' => $roleIds['operator_akademik'], 'permission_id' => $permIds[$key]];
        }

        // 4. STAF KEUANGAN
        $keuPerms = ['dashboard_view', 'keuangan_jenispembayaran_manage', 'keuangan_tagihan_manage', 'keuangan_pembayaran_manage', 'laporan_keuangan_view'];
        foreach ($keuPerms as $key) {
            if (isset($permIds[$key])) $assign[] = ['role_id' => $roleIds['staf_keuangan'], 'permission_id' => $permIds[$key]];
        }

        // 5. GURU
        $guruPerms = ['dashboard_view', 'portal_guru_access', 'akademik_absensi_siswa_manage', 'akademik_nilai_manage', 'akademik_e_learning_manage'];
        foreach ($guruPerms as $key) {
            if (isset($permIds[$key])) $assign[] = ['role_id' => $roleIds['guru'], 'permission_id' => $permIds[$key]];
        }

        // 6. SISWA
        if (isset($permIds['portal_siswa_access'])) {
            $assign[] = ['role_id' => $roleIds['siswa'], 'permission_id' => $permIds['portal_siswa_access']];
        }

        // Masukkan relasi ke tabel pivot (abaikan jika relasi sudah terdaftar sebelumnya)
        if (!empty($assign)) {
            $this->db->table('role_permissions')->ignore(true)->insertBatch($assign);
        }
        
        echo "RBAC Seeder berhasil dijalankan!\n";
    }
}