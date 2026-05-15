<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CalonSiswaModel;
use App\Models\SettingsModel;

class PendaftaranController extends BaseController
{
    /**
     * Menampilkan halaman formulir pendaftaran.
     */
    public function index()
    {
        $settingsModel = new SettingsModel();
        $data = [
            'title'      => 'Formulir Pendaftaran Siswa Baru',
            'settings'   => $settingsModel->getSettingsAsArray(),
            'validation' => \Config\Services::validation(),
        ];
        return view('pendaftaran/form', $data);
    }

    /**
     * Memproses penyimpanan data pendaftaran.
     */
    public function simpan()
    {
        $model = new CalonSiswaModel();
        $rules = $model->getValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        
        // Generate nomor pendaftaran unik
        $data['nomor_pendaftaran'] = 'PSB' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 5));

        // Handle file uploads
        $files = ['file_kk', 'file_akta', 'file_ijazah'];
        foreach ($files as $file_input) {
            $file = $this->request->getFile($file_input);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move('uploads/psb', $newName);
                $data[$file_input] = $newName;
            }
        }
        
        // Generate password default
        $data['email'] = $this->request->getPost('email');
        $data['password'] = password_hash('password123', PASSWORD_DEFAULT);


        if ($model->save($data)) {
            // Redirect ke halaman sukses
            return redirect()->to('psb/sukses');
        } else {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    /**
     * Menampilkan halaman sukses setelah pendaftaran.
     */
    public function sukses()
    {
        $settingsModel = new SettingsModel();
        $data = [
            'title' => 'Pendaftaran Berhasil',
            'settings' => $settingsModel->getSettingsAsArray(),
        ];
        return view('pendaftaran/sukses', $data);
    }
}

