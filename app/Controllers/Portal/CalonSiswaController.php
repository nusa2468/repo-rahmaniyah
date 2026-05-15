<?php

// FIX: Namespace diubah agar sesuai dengan lokasi folder baru
namespace App\Controllers\Portal;

use App\Controllers\BaseController;
use App\Models\CalonSiswaModel;

// FIX: Nama kelas disederhanakan
class CalonSiswaController extends BaseController
{
    /**
     * Menampilkan halaman login untuk calon siswa.
     */
    public function login()
    {
        if (session()->get('isLoggedIn') && session()->get('role_name') === 'calon_siswa') {
            return redirect()->to('portal/calon-siswa/dashboard');
        }
        return view('portal/calon_siswa/login', ['title' => 'Portal Calon Siswa - Login']);
    }

    /**
     * Memproses upaya login calon siswa.
     */
    public function attemptLogin()
    {
        helper('form');
        $session = session();
        $model = new CalonSiswaModel();

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $calonSiswa = $model->where('email', $email)->first();

        if (!$calonSiswa || !password_verify($password, $calonSiswa['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau Password salah.');
        }

        // Set session data khusus untuk calon siswa
        $sessionData = [
            'calon_siswa_id'    => $calonSiswa['id'],
            'nama_calon_siswa'  => $calonSiswa['nama_lengkap'],
            'role_name'         => 'calon_siswa', // Menandakan ini adalah sesi calon siswa
            'isLoggedIn'        => TRUE
        ];
        $session->set($sessionData);

        session()->close();

        return redirect()->to('portal/calon-siswa/dashboard');
    }

    /**
     * Menampilkan dashboard calon siswa setelah login berhasil.
     */
    public function dashboard()
    {
        $calonSiswaModel = new CalonSiswaModel();
        $calonSiswa = $calonSiswaModel->find(session()->get('calon_siswa_id'));

        $data = [
            'title'      => 'Dashboard Calon Siswa',
            'calon_siswa' => $calonSiswa,
        ];

        return view('portal/calon_siswa/dashboard', $data);
    }

    /**
     * Memproses logout calon siswa.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('portal/calon-siswa/login');
    }
}
