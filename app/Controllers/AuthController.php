<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index(): string|RedirectResponse
    {
        if (session()->get('isLoggedIn')) {
            return $this->_redirectBasedOnRole(session()->get('role_name'));
        }
        
        return view('auth/login', [
            'title'  => 'Masuk ke Sistem Informasi Terpadu',
            'error'  => session()->getFlashdata('error'), 
            'errors' => session()->getFlashdata('errors'), 
        ]);
    }

    /**
     * Memproses login pengguna
     * Route: POST /login
     */
    public function login(): RedirectResponse
    {
        helper(['form', 'url']);
        
        $rules = [
            'login'    => 'required|min_length[4]|max_length[100]',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $loginIdentifier = $this->request->getVar('login');
        $password        = $this->request->getVar('password');

        $user = $this->userModel->findUserByLoginIdentifier($loginIdentifier);

        // Verifikasi User dan Password
        if (!$user || !password_verify($password, $user['password_hash'])) { 
            return redirect()->back()->withInput()->with('error', 'Identitas pengguna atau kata sandi Anda salah.');
        }

        // Cek Status Aktif
        if ((int)($user['is_active'] ?? 0) !== 1) {
            return redirect()->back()->withInput()->with('error', 'Akun Anda sedang dinonaktifkan.');
        }

        // Persiapan Data Sesi
        $sessionData = [
            'user_id'       => $user['id'],
            'username'      => $user['username'],
            'nama_lengkap'  => $user['nama_lengkap'],
            'email'         => $user['email'],
            'id_role'       => $user['id_role'],
            'role_name'     => strtolower($user['role_name'] ?? 'umum'),
            'role_display'  => $user['role_name'] ?? 'Pengguna Umum',
            'kode_jenjang'  => strtoupper($user['kode_jenjang'] ?? 'GLOBAL'),
            'isLoggedIn'    => true,
        ];
        
        session()->set($sessionData);
        session()->regenerate(); // Mencegah Session Fixation

        // Update Last Login
        $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        return $this->_redirectBasedOnRole($sessionData['role_name'])
                    ->with('success', 'Selamat datang kembali, ' . $user['nama_lengkap']);
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();
        // Redirect ke Landing Page utama ('/') alih-alih login, sesuai permintaan user sebelumnya
        return redirect()->to(base_url('/'))->with('success', 'Anda telah keluar dari sistem.');
    }

    private function _redirectBasedOnRole(string $role): RedirectResponse
    {
        $role = strtolower($role);
        switch ($role) {
            case 'superadmin':
            case 'admin':
            case 'yayasan':
            case 'pengelola':
                return redirect()->to(base_url('app')); 
            case 'siswa':
                return redirect()->to(base_url('portal/siswa/dashboard'));
            default:
                // Default fallback jika role tidak dikenali, arahkan ke app dashboard umum
                return redirect()->to(base_url('app'));
        }
    }
}