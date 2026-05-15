<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * AuthFilter (Global / Admin)
 * Filter utama untuk memproteksi area Admin (/app) dan menangani routing dasar.
 * PERBAIKAN: Menambahkan pengecualian untuk route 'portal/*' agar tidak bentrok dengan sesi Siswa.
 */
class AuthFilter implements FilterInterface 
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $currentUri = trim($request->getUri()->getPath(), '/');

        // ------------------------------------------------------------------------
        // 1. CEK SESI SISWA (Agar tidak nyasar ke Login Admin)
        // ------------------------------------------------------------------------
        if ($session->get('siswa_logged_in')) {
            // Jika siswa iseng buka halaman login admin, register, atau root
            if (in_array($currentUri, ['login', 'register', '']) || str_starts_with($currentUri, 'app')) {
                return redirect()->to(base_url('portal/siswa/dashboard'));
            }
            // Jika siswa akses portalnya sendiri, BIARKAN LEWAT (jangan di-redirect ke login admin)
            if (str_starts_with($currentUri, 'portal')) {
                return; 
            }
        }

        // ------------------------------------------------------------------------
        // 2. EXCEPTION: JANGAN GANGGU ROUTE PORTAL & ASSETS
        // ------------------------------------------------------------------------
        // Route 'portal/*' memiliki auth sendiri (misal: SiswaAuthFilter).
        // Jadi Filter Admin ini harus "minggir" jika URL-nya adalah portal.
        if (str_starts_with($currentUri, 'portal') || str_starts_with($currentUri, 'auth')) {
            return;
        }

        // ------------------------------------------------------------------------
        // 3. LOGIKA AUTH ADMIN / STAFF (Session: isLoggedIn)
        // ------------------------------------------------------------------------
        $isLoggedIn = $session->get('isLoggedIn');
        $roleName   = strtolower($session->get('role_name') ?? ''); 
        
        // Mapping redirect khusus untuk Admin/Staff
        $portalMap = [
            'guru'        => 'portal/guru/dashboard',
            'pendidik'    => 'portal/guru/dashboard',
            'orangtua'    => 'portal/ortu/dashboard',
            'calon-siswa' => 'portal/calon-siswa/dashboard',
        ];

        // A. JIKA SUDAH LOGIN (ADMIN/STAFF)
        if ($isLoggedIn) {
            
            // Cegah masuk ke halaman Login/Register lagi
            if ($currentUri === 'login' || $currentUri === 'register' || $currentUri === '') {
                
                // Cek apakah user punya portal khusus?
                if (array_key_exists($roleName, $portalMap)) {
                    return redirect()->to(base_url($portalMap[$roleName]));
                }

                // Default ke Dashboard Admin
                return redirect()->to(base_url('app/dashboard'));
            }
            
            // Proteksi Area /app sesuai Role (Opsional, logika role bisa diperdalam di sini)
            if (str_starts_with($currentUri, 'app')) {
                // Biarkan lewat, controller yang akan menangani detail permission
            }
            
            return $request;
        }

        // B. JIKA BELUM LOGIN
        // Izinkan akses ke route public (Login, Register, Forgot Password)
        $publicRoutes = ['login', 'register', 'forgot-password'];
        
        if (!in_array($currentUri, $publicRoutes)) {
            // Redirect ke Login Admin
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk untuk melanjutkan.');
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed
    }
}