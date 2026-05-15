<?php

use App\Models\HakAksesModel;

if (!function_exists('has_permission')) {
    /**
     * Cek apakah user yang login memiliki izin untuk akses fitur tertentu.
     * * @param string $permissionName Nama permission (contoh: 'kelembagaan.view')
     * @return bool
     */
    function has_permission($permissionName)
    {
        $session = session();
        $roleId = $session->get('role_id'); // Pastikan ID Role disimpan saat login
        $kodeJenjang = $session->get('kode_jenjang');

        // 1. BYPASS SUPERADMIN
        // Superadmin (Global/Yayasan) selalu punya akses penuh (God Mode)
        if (in_array($kodeJenjang, ['GLOBAL', 'YAYASAN'])) {
            return true;
        }

        // 2. CEK VIA SESSION (OPTIMASI PERFORMA)
        // Idealnya, saat login, daftar permission user disimpan di session 'user_permissions'
        // Format: ['kelembagaan.view', 'sdm.view', ...]
        $userPermissions = $session->get('user_permissions');

        if ($userPermissions && is_array($userPermissions)) {
            return in_array($permissionName, $userPermissions);
        }

        // 3. FALLBACK: QUERY DATABASE (Jika session permission kosong)
        // Ini opsi cadangan jika sistem login belum menyimpan permission ke session
        // Kita gunakan logika "Negative Logic" sederhana untuk Admin Unit sesuai request Anda
        
        // Aturan: Admin Unit boleh akses semua KECUALI yang mengandung 'kelembagaan'
        if ($permissionName === 'masterdata.kelembagaan.view') {
            return false; // Admin Unit dilarang akses ini
        }

        // Default: Boleh (Untuk SDM, Akademik, Kesiswaan)
        return true;
    }
}