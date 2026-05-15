<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * RoleSeeder
 * Mengelola pembuatan data peran (Roles) dan pemetaan hak akses (Permissions).
 * Versi ini dipastikan bersih dari karakter ilegal untuk menjamin sinkronisasi user.
 */
class RoleSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // 1. Bersihkan Data Lama (Fresh Start)
        $db->disableForeignKeyChecks();
        $db->table('role_permissions')->truncate();
        $db->table('roles')->truncate();
        $db->enableForeignKeyChecks();

        echo "\n[RoleSeeder] Memasukkan Data Roles Baru...\n";

        // 2. Insert Roles Dasar
        $this->seedRoles();

        // 3. Mapping Permissions
        echo "[RoleSeeder] Memetakan Hak Akses ke Masing-masing Role...\n";
        $this->seedRolePermissions();
    }

    private function seedRoles()
    {
        $now = date('Y-m-d H:i:s');
        $rolesData = [
            ['name' => 'superadmin', 'description' => 'Administrator Sistem dengan Hak Penuh (Yayasan/IT)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admin', 'description' => 'Administrator Unit Sekolah (Terbatas per Jenjang)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'operator_akademik', 'description' => 'Staf Tata Usaha / Operator Sekolah bidang Kurikulum', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staf_keuangan', 'description' => 'Bendahara dan Staf Keuangan', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staf_kepegawaian', 'description' => 'Staf HRD/Kepegawaian', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staf_sapras', 'description' => 'Staf Sarana dan Prasarana', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staf_humas', 'description' => 'Staf Hubungan Masyarakat dan Publikasi', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'guru', 'description' => 'Tenaga Pendidik (Guru)', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'siswa', 'description' => 'Peserta Didik (Siswa)', 'created_at' => $now, 'updated_at' => $now],
        ];
        
        $this->db->table('roles')->insertBatch($rolesData);
        echo " [OK] 9 Role berhasil didaftarkan.\n";
    }

    private function seedRolePermissions()
    {
        $db = $this->db;
        $roleIds = array_column($db->table('roles')->get()->getResultArray(), 'id', 'name');
        $permissionIds = array_column($db->table('permissions')->get()->getResultArray(), 'id', 'permission_key');

        if (empty($roleIds) || empty($permissionIds)) {
            echo " [WARN] Data Roles atau Permissions kosong. Sinkronisasi dibatalkan.\n";
            return;
        }

        $assignments = [
            'superadmin' => array_keys($permissionIds),
            'admin' => [
                'dashboard_view', 'master_siswa_manage', 'master_guru_manage', 'master_karyawan_manage',
                'master_kelas_manage', 'master_matpel_manage', 'akademik_jadwal_manage', 
                'akademik_absensi_siswa_manage', 'akademik_nilai_manage', 'akademik_rapor_manage',
                'laporan_akademik_view', 'laporan_keuangan_view', 'psb_manage', 'humas_berita_manage'
            ],
            'operator_akademik' => [
                'dashboard_view', 'master_siswa_manage', 'master_guru_manage', 
                'master_kelas_manage', 'master_matpel_manage', 'master_tahunajaran_manage',
                'akademik_jadwal_manage', 'akademik_absensi_siswa_manage', 'akademik_nilai_manage', 
                'akademik_rapor_manage', 'akademik_e_learning_manage', 'laporan_akademik_view',
                'pengaturan_log_view', 'portal_guru_access', 'portal_siswa_access'
            ],
            'staf_keuangan' => [
                'dashboard_view', 'keuangan_setting_manage', 'keuangan_jenispembayaran_manage', 
                'keuangan_tagihan_manage', 'keuangan_pembayaran_manage', 'laporan_keuangan_view',
            ],
            'guru'  => ['dashboard_view', 'portal_guru_access', 'akademik_absensi_siswa_manage', 'akademik_nilai_manage'],
            'siswa' => ['portal_siswa_access'],
        ];

        $rolePermissions = [];
        foreach ($assignments as $roleName => $permKeys) {
            if (!isset($roleIds[$roleName])) continue;
            
            $roleId = $roleIds[$roleName];
            foreach ($permKeys as $permKey) {
                if (isset($permissionIds[$permKey])) {
                    $rolePermissions[] = [
                        'role_id'       => $roleId,
                        'permission_id' => $permissionIds[$permKey], 
                    ];
                }
            }
        }
        
        if (!empty($rolePermissions)) {
            $db->table('role_permissions')->insertBatch($rolePermissions);
            echo " [OK] Matriks Hak Akses berhasil dipasang.\n";
        }
    }
}