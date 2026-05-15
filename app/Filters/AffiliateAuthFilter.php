<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AffiliateAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // FIX: Cek session key yang benar 'mitra_logged_in' (sesuai Controller PortalAffiliated)
        if (!session()->get('mitra_logged_in')) {
            
            // FIX: Redirect ke URL yang benar 'portal/affiliated/login' (bukan afiliasi)
            return redirect()->to(base_url('portal/affiliated/login'))->with('error', 'Silakan login terlebih dahulu.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi
    }
}