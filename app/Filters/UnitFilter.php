<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * UnitFilter (School Scoping Middleware)
 * * Tugas: 
 * 1. Memastikan User sudah login.
 * 2. Memastikan User sudah memilih "Unit Aktif" (kode_jenjang) di sesi mereka.
 * 3. Mencegah akses ke data jika sesi jenjang belum di-set.
 */
class UnitFilter implements FilterInterface
{
    /**
     * Dijalankan SEBELUM Controller dieksekusi.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Cek Login Dasar (Safety Net jika AuthFilter belum jalan)
        if (!$session->get('logged_in')) {
            return redirect()->to('/login');
        }

        // 2. Ambil URI saat ini untuk mencegah Infinite Redirect Loop
        $currentPath = $request->getUri()->getPath();

        // Daftar whitelist route yang BOLEH diakses tanpa memilih unit
        // Sesuaikan dengan route pemilihan unit di aplikasi Anda
        $whitelist = [
            'auth/pilih-unit',
            'auth/select-unit',
            'auth/logout',
            'profile/index' // Profil user mungkin boleh diakses tanpa unit
        ];

        // Jika user sedang berada di halaman whitelist, biarkan lewat
        foreach ($whitelist as $path) {
            if (strpos($currentPath, $path) !== false) {
                return;
            }
        }

        // 3. Cek apakah 'kode_jenjang' atau 'active_unit' ada di Session
        // Sesuai dengan SiswaModel Anda yang butuh $kode_jenjang
        if (!$session->has('kode_jenjang') || empty($session->get('kode_jenjang'))) {
            
            // Logika Tambahan: Jika User adalah Super Admin (YAYASAN), mungkin auto-set ke Global
            if ($session->get('role') === 'YAYASAN') {
                // Opsional: Auto set jika yayasan tidak butuh milih
                // $session->set('kode_jenjang', 'YAYASAN'); 
                // return; 
            }

            // LEMPAR KE HALAMAN PILIH UNIT
            return redirect()->to('/auth/pilih-unit')
                             ->with('warning', 'Silakan pilih Unit Sekolah (Jenjang) terlebih dahulu untuk melanjutkan.');
        }

        // 4. (Opsional) Validasi Hak Akses User terhadap Unit tersebut
        // Mencegah user SD memanipulasi session untuk masuk ke SMA
        $userUnits = $session->get('assigned_units') ?? []; 
        $activeUnit = $session->get('kode_jenjang');
        
        // Jika sistem Anda menyimpan daftar unit yang boleh diakses user di session
        if (!empty($userUnits) && !in_array($activeUnit, $userUnits) && $activeUnit !== 'YAYASAN') {
             return redirect()->to('/auth/pilih-unit')->with('error', 'Anda tidak memiliki akses ke unit tersebut.');
        }
    }

    /**
     * Dijalankan SETELAH Controller dieksekusi (Biasanya kosong).
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}