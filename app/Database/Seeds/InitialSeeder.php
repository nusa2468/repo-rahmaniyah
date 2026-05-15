<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use Throwable;

/**
 * ============================================================================
 * INITIAL SEEDER (VERSI FINAL & LOGGING EKSTRIM)
 * ============================================================================
 * Deskripsi: Menjamin ketersediaan akun Superadmin dan Admin Unit.
 * Relasi: Sangat bergantung pada data dari RbacSeeder (tabel roles).
 * Solusi: Menggunakan Upsert (Update if exists) dan pemulihan soft-delete.
 */
class InitialSeeder extends Seeder
{
    public function run()
    {
        // Pastikan berjalan di CLI
        if (php_sapi_name() !== 'cli') {
            return;
        }

        $db = $this->db;
        $now = Time::now()->format('Y-m-d H:i:s');
        $defaultPassword = 'password123';
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

        echo "\n[InitialSeeder] Memulai Sinkronisasi Pengguna Utama...\n";

        // --------------------------------------------------------------------
        // 1. Verifikasi Role (Sinkronisasi dengan RbacSeeder)
        // --------------------------------------------------------------------
        $roleMap = [];
        if ($db->table('roles')->countAllResults() == 0) {
            echo " [CRITICAL] Tabel 'roles' kosong! Pastikan RbacSeeder sudah berjalan.\n";
            return;
        }

        $roles = $db->table('roles')->get()->getResultArray();
        foreach ($roles as $role) {
            $roleMap[strtolower($role['name'])] = $role['id'];
        }

        $idSuperAdmin = $roleMap['superadmin'] ?? null;
        $idAdmin      = $roleMap['admin'] ?? null;

        if (!$idSuperAdmin || !$idAdmin) {
            echo " [CRITICAL] Role 'superadmin' atau 'admin' tidak ditemukan!\n";
            echo " [INFO] Daftar Role yang ada: " . implode(', ', array_keys($roleMap)) . "\n";
            return; 
        }

        // --------------------------------------------------------------------
        // 2. Tentukan Daftar Unit (SD, SMP, SMA)
        // --------------------------------------------------------------------
        $listUnit = ['SD', 'SMP', 'SMA']; // Fallback default
        
        // Cek tabel master jenjang jika sudah di-seed sebelumnya
        if ($db->tableExists('jenjang_sekolah')) {
            $jenjangs = $db->table('jenjang_sekolah')
                           ->whereNotIn('kode_jenjang', ['GLOBAL', 'YAYASAN', 'PUSAT'])
                           ->get()->getResultArray();
                           
            if (!empty($jenjangs)) {
                $listUnit = array_column($jenjangs, 'kode_jenjang');
            }
        }

        // --------------------------------------------------------------------
        // 3. Persiapan Data User (Superadmin & Admin Unit)
        // --------------------------------------------------------------------
        $users = [];
        
        // Akun Superadmin (Yayasan/Pusat)
        $users[] = [
            'id_role'       => $idSuperAdmin,
            'kode_jenjang'  => 'GLOBAL',
            'nama_lengkap'  => 'Super Administrator Yayasan',
            'username'      => 'superadmin',
            'email'         => 'superadmin@sekolah.zb',
            'password_hash' => $hashedPassword,
            'is_active'     => 1,
            'deleted_at'    => null // Pastikan tidak ter-soft delete
        ];

        // Akun Admin Unit (SD, SMP, SMA, dll)
        foreach ($listUnit as $u) {
            $uUpper = strtoupper($u);
            $uLower = strtolower($u);
            $users[] = [
                'id_role'       => $idAdmin,
                'kode_jenjang'  => $uUpper,
                'nama_lengkap'  => "Administrator Unit {$uUpper}",
                'username'      => 'admin_' . $uLower,
                'email'         => "admin.{$uLower}@sekolah.zb",
                'password_hash' => $hashedPassword,
                'is_active'     => 1,
                'deleted_at'    => null
            ];
        }

        // --------------------------------------------------------------------
        // 4. Eksekusi Database (Upsert Logic)
        // --------------------------------------------------------------------
        $db->disableForeignKeyChecks();
        $countSuccess = 0;

        foreach ($users as $userData) {
            try {
                // Cek fisik (termasuk yang di-delete) untuk menghindari duplicate entry error
                $existing = $db->table('users')
                               ->where('username', $userData['username'])
                               ->get()
                               ->getRow();

                if ($existing) {
                    // Paksa Update (Termasuk merestore jika sebelumnya ter-soft delete)
                    $db->table('users')
                       ->where('id', $existing->id)
                       ->update(array_merge($userData, ['updated_at' => $now]));
                    echo " [OK] Updated/Restored : {$userData['username']}\n";
                } else {
                    // Insert Baru
                    $db->table('users')->insert(array_merge($userData, [
                        'created_at' => $now, 
                        'updated_at' => $now
                    ]));
                    echo " [OK] Inserted        : {$userData['username']}\n";
                }
                $countSuccess++;
            } catch (Throwable $e) {
                echo " [ERR] Gagal memproses {$userData['username']}: " . $e->getMessage() . "\n";
            }
        }

        $db->enableForeignKeyChecks();
        echo "[InitialSeeder] Selesai. Total {$countSuccess} akun berhasil disinkronkan.\n\n";
    }
}