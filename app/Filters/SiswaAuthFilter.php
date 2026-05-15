<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SiswaAuthFilter
 * Filter untuk memproteksi halaman Dashboard Siswa.
 * Menggunakan session 'siswa_logged_in' untuk validasi.
 */
class SiswaAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // PERBAIKAN UTAMA:
        // Cek 'siswa_logged_in' (Session khusus siswa)
        // Jangan cek 'isLoggedIn' (itu untuk Admin)
        if (!session()->get('siswa_logged_in')) {
            return redirect()->to(base_url('portal/siswa/login'))->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi
    }
}