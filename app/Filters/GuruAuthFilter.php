<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;
// Import helper function untuk routing
use function route_to; 

/**
 * Filter ini berfungsi untuk membatasi akses hanya untuk pengguna dengan Role 'guru' ATAU 'admin'
 * untuk Portal Guru.
 */
class GuruAuthFilter implements FilterInterface
{
    /**
     * Jalankan sebelum Controller.
     */
    public function before(RequestInterface $request, $arguments = null): RequestInterface|ResponseInterface|string|RedirectResponse
    {
        $session = session();

        // 1. Cek apakah pengguna sudah login
        if (! $session->get('isLoggedIn')) {
            $session->setFlashdata('error', 'Sesi Anda telah habis. Silakan login kembali.');
            
            // Mengalihkan ke halaman login Guru (Lebih baik gunakan named route jika ada)
            // Fallback: base_url('portal/guru/login')
            return redirect()->to(route_to('portal_guru_login') ?? base_url('portal/guru/login'));
        }

        // 2. Cek apakah role di sesi adalah 'guru' ATAU 'admin'
        $userRole = strtolower($session->get('role_name'));
        
        // Daftar peran yang diizinkan untuk mengakses portal guru
        $allowedRoles = ['guru', 'admin'];
        
        if (!in_array($userRole, $allowedRoles)) {
            log_message('warning', 'Akses Ditolak: User ID ' . ($session->get('user_id') ?? 'UNKNOWN') . ' mencoba mengakses Portal Guru dengan role: ' . $userRole);
            
            // Hancurkan sesi yang memiliki peran salah. 
            $session->destroy();
            
            $session->setFlashdata('error', 'Akses ditolak. Peran Anda tidak diizinkan di portal ini.');
            
            // Redirect ke halaman Admin/Umum/App (Asumsi rute 'app' adalah rute default setelah log out)
            // Fallback: base_url('app')
            return redirect()->to(route_to('app_dashboard') ?? base_url('app')); 
        }

        // 3. KRITIS: Cek apakah guru_id ada di sesi HANYA jika peran adalah GURU
        // Admin (role_name: 'admin') akan melewati pemeriksaan guru_id
        if ($userRole === 'guru' && !$session->get('guru_id')) {
             log_message('error', 'Sesi Guru Korup: guru_id hilang untuk user ID: ' . ($session->get('user_id') ?? 'UNKNOWN'));
             
             // Hancurkan sesi yang korup dan minta login ulang
             $session->destroy();
             $session->setFlashdata('error', 'Data sesi Guru tidak valid. Silakan login ulang.');
             
             // Redirect ke halaman login Guru
             // Fallback: base_url('portal/guru/login')
             return redirect()->to(route_to('portal_guru_login') ?? base_url('portal/guru/login'));
        }
        
        // Jika sudah login, role diizinkan ('guru' atau 'admin'), lanjutkan
        return $request;
    }

    /**
     * Jalankan setelah Controller.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        // Tidak ada operasi post-controller
        return $response;
    }
}