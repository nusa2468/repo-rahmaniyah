<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * DataInstallSeeder
 * Seeder utama untuk inisialisasi sistem ERP Sekolah.
 * Menjalankan urutan: Permissions -> Roles -> Settings -> User Awal -> CMS Content.
 */
class DataInstallSeeder extends Seeder
{
    public function run()
    {
        // 0. BERSIHKAN DATA LAMA (TRUNCATE)
        // Matikan pemeriksaan Foreign Key agar bisa menghapus tabel induk tanpa error
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

        // Daftar tabel core yang akan dibersihkan
        $tablesToTruncate = [
            'role_permissions', // Tabel Pivot
            'permissions',      // Tabel Master Izin
            'users',            // Tabel Pengguna
            'roles',            // Tabel Peran
            'settings',         // Tabel Pengaturan
            'jenjang_sekolah',  // Tabel Master Jenjang
            'tahun_ajaran'      // Tabel Tahun Ajaran
        ];

        foreach ($tablesToTruncate as $table) {
            if ($this->db->tableExists($table)) {
                $this->db->table($table)->truncate();
                echo " - Table '$table' cleaned (truncated).\n";
            }
        }

        // Hidupkan kembali pemeriksaan Foreign Key
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "-------------------------------------------------------\n";

        // 1. Install Hak Akses (Permissions) - Fondasi sistem
        $this->call('PermissionsSeeder');

        // 2. Install Peran (Roles) - Menentukan jenis pengguna
        $this->call('RoleSeeder');
        
        // 3. Install Jenjang Sekolah (Baru)
        $this->call('JenjangSeeder');
        
        // 4. Install Tahun Ajaran (FIXED NAME)
        $this->call('TahunAjaranSeeder');

        // 5. Install Pengaturan Sekolah (Settings) - Identitas Global & Unit
        $this->call('SettingsSeeder');

        // 6. Install User Awal (Superadmin)
        $this->call('InitialSeeder');
        
        // 7. Opsional: Install Data Dummy CMS
        // $this->call('CmsSeeder'); 
    }
}