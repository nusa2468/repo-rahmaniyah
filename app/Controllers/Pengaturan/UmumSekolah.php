<?php

namespace App\Controllers\Pengaturan;

use App\Controllers\BaseController;
use App\Models\SettingsModel;

class UmumSekolah extends BaseController
{
    // __construct() dan method lainnya
    // ...

    public function index(): string
    {
        $settingsModel = new SettingsModel();
        $data = [
            // REVISI: Ubah 'Kelembagaan' menjadi 'Umum Sekolah' agar lebih sesuai rute 'pengaturan/umum'
            'title'          => 'Umum Sekolah - Informasi Sekolah', 
            'current_module' => 'umum', // REVISI: Ubah 'kelembagaan' menjadi 'umum'
            'settings'       => $settingsModel->getSettingsAsArray(),
        ];
        return view('pengaturan/umum/index', $data);
    }
    
    public function update()
    {
        $postData = $this->request->getPost();
        
        // Optimasi: Inisialisasi model di luar loop
        $settingsModel = new SettingsModel(); 
        
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($postData as $key => $value) {
            
            // Logic: Cek apakah key sudah ada (berdasarkan nama input form), jika ya update, jika tidak insert
            $existing = $settingsModel->where('key', $key)->first();

            if ($existing) {
                $settingsModel->update($existing['id'], ['value' => $value]);
            } else {
                $settingsModel->insert(['key' => $key, 'value' => $value]);
            }
        }
        
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('pengaturan/umum')->with('error', 'Terjadi kesalahan pada database saat menyimpan data.');
        }

        // Redirect sukses
        return redirect()->to('pengaturan/umum')->with('success', 'Data informasi sekolah berhasil diperbarui.'); 
    }
}