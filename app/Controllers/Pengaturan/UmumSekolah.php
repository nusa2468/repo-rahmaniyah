<?php

namespace App\Controllers\Pengaturan;

use App\Controllers\BaseController;
use App\Models\SettingsModel;

/**
 * Controller UmumSekolah (Transformasi menjadi Centralized SaaS Settings)
 * Menangani konfigurasi Profil, Odoo Integration, dan Manajemen Lisensi SaaS.
 */
class UmumSekolah extends BaseController
{
    protected $settingsModel;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL'];

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    public function index(): string
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        // Filter untuk memilih unit mana yang mau disetting (Khusus Superadmin/Yayasan)
        $targetJenjang = $this->request->getGet('jenjang') ?? $sessionJenjang;
        
        // Proteksi Keamanan Tenant: Admin SD tidak bisa mengintip/menyeting unit SMP
        if (!$isGlobal && $targetJenjang !== $sessionJenjang) {
            $targetJenjang = $sessionJenjang;
        }

        // Ambil Data Jenjang (Untuk Dropdown Filter Superadmin)
        $daftarUnit = [];
        if ($isGlobal) {
            $db = \Config\Database::connect();
            if ($db->tableExists('jenjang_sekolah')) {
                $query = $db->table('jenjang_sekolah')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get();
                foreach ($query->getResultArray() as $row) {
                    $daftarUnit[strtoupper($row['kode_jenjang'])] = $row['nama_jenjang'];
                }
            }
        }

        // Gunakan fitur baru dari SettingsModel: Ambil data berdasarkan Scope Unit
        $settingsData = $this->settingsModel->getSettingsAsArray($targetJenjang);

        $data = [
            'title'          => 'Konfigurasi Sistem Terpadu',
            'current_module' => 'umum',
            'settings'       => $settingsData,
            'isGlobal'       => $isGlobal,
            'targetJenjang'  => $targetJenjang,
            'daftarUnit'     => $daftarUnit
        ];

        return view('pengaturan/umum/index', $data);
    }
    
    public function update()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        $targetJenjang = $this->request->getPost('target_jenjang') ?? $sessionJenjang;

        // Proteksi Lintas Tenant
        if (!$isGlobal && $targetJenjang !== $sessionJenjang) {
            return redirect()->back()->with('error', 'Akses Ditolak. Anda hanya dapat mengubah pengaturan unit Anda sendiri.');
        }

        // Tangkap array data dari form input (format: name="settings[nama_key]")
        $postedSettings = $this->request->getPost('settings');

        if (!empty($postedSettings) && is_array($postedSettings)) {
            $db = \Config\Database::connect();
            $db->transStart();

            foreach ($postedSettings as $key => $value) {
                // Memanfaatkan fungsi update cerdas (Upsert) dari SettingsModel
                $this->settingsModel->updateSetting($targetJenjang, $key, $value);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->to(base_url('app/pengaturan/umum?jenjang=' . $targetJenjang))->with('error', 'Terjadi kesalahan pada database saat menyimpan konfigurasi.');
            }
        }

        return redirect()->to(base_url('app/pengaturan/umum?jenjang=' . $targetJenjang))->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}