<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * Base Controller menyediakan tempat untuk memuat komponen global
 * dan memastikan fungsionalitas inti tersedia di seluruh Controller.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance dari the main Request object.
     *
     * @var IncomingRequest|CLIRequest
     */
    protected $request;

    /**
     * Array Helper yang akan dimuat secara otomatis pada setiap Controller.
     */
    protected $helpers = ['url', 'session', 'utility', 'form', 'number'];

    /**
     * Data user yang sedang login.
     */
    protected $userData = [];
    
    /**
     * Instance Session.
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * Instance Database.
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Daftar modul yang termasuk dalam grup Portal.
     * Digunakan di Sidebar untuk logika menu aktif.
     */
    protected $portalModules = ['portal', 'guru', 'siswa', 'ppdb'];

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // 1. KONEKSI DATABASE & SESSION
        $this->db = \Config\Database::connect();
        $this->session = \Config\Services::session();

        // 2. MUAT DATA PENGGUNA (Otentikasi Dasar)
        $this->loadCurrentUser();

        // 3. GLOBAL VIEW DATA INJECTION (PENTING!)
        // Ini membuat variabel tersedia di SEMUA View (termasuk Sidebar/Layout)
        // tanpa perlu dikirim manual via return view('...', $data).
        // Ini mengatasi error: Undefined variable $portalModules
        \Config\Services::renderer()->setData([
            'userData'      => $this->userData,
            'portalModules' => $this->portalModules,
            'currentModule' => '', // Default value agar tidak error undefined
        ]);
    }

    /**
     * Memuat data pengguna dari sesi ke properti $this->userData.
     */
    protected function loadCurrentUser()
    {
        // Mengambil data sesi
        $userId = $this->session->get('user_id');
        $isLoggedIn = $this->session->get('isLoggedIn');

        if ($isLoggedIn && $userId) {
            $this->userData = [
                'user_id'        => $userId,
                'username'       => $this->session->get('username'),
                'full_name'      => $this->session->get('nama_lengkap'),
                'role_name'      => $this->session->get('role_name'),
                'role_name_full' => $this->session->get('role_name_full'),
                'guru_id'        => $this->session->get('guru_id'),
                'isLoggedIn'     => true,
            ];
        } else {
            // Set data default Guest jika tidak ada sesi aktif
            $this->userData = [
                'user_id'        => null,
                'username'       => 'Guest',
                'full_name'      => 'Tamu',
                'role_name'      => 'public',
                'role_name_full' => 'Publik',
                'guru_id'        => null,
                'isLoggedIn'     => false,
            ];
        }
    }
}