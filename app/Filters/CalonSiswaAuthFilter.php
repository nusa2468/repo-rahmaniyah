<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CalonSiswaAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Jika user belum login ATAU role-nya bukan 'calon_siswa'
        if (!session()->get('isLoggedIn') || session()->get('role_name') !== 'calon_siswa') {
            // Maka paksa redirect ke halaman login portal calon siswa
            return redirect()->to(base_url('portal/calon-siswa/login'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi
    }
}
