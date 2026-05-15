<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseMasterDataController;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller IdentitasSekolah
 * Mengelola profil, visi, misi, dan identitas resmi sekolah per jenjang.
 * * UPDATE: Menggunakan logika robust untuk list jenjang & integrasi BaseMasterDataController.
 */
class IdentitasSekolah extends BaseMasterDataController
{
    protected $settingsModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Load Model Khusus Settings (JenjangModel sudah diload otomatis oleh BaseMasterDataController)
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Menampilkan form identitas berdasarkan filter jenjang yang AKTIF saja
     */
    public function index(): string
    {
        // 1. Ambil daftar jenjang yang statusnya 'aktif' dari database
        // Menggunakan standar CodeIgniter builder
        $jenjangAktifDb = $this->jenjangModel->where('status', 'aktif')
                                             ->orderBy('urutan', 'ASC')
                                             ->findAll();
        
        // 2. Siapkan array penampung
        $listAktif = [];
        
        // A. Tambahkan entitas Global secara manual sebagai identitas Yayasan/Pusat
        $listAktif[] = (object) [
            'kode_jenjang' => 'GLOBAL', 
            'nama_jenjang' => 'Global / Yayasan'
        ];

        // B. Masukkan jenjang dari database (Konversi ke Object untuk konsistensi View)
        foreach ($jenjangAktifDb as $j) {
            // Pastikan casting ke object jika model mengembalikan array
            $j = (object) $j;
            $listAktif[] = $j;
        }

        // 3. Buat array daftar kode yang valid (untuk validasi input GET)
        $validCodes = [];
        foreach($listAktif as $item) {
            $validCodes[] = $item->kode_jenjang;
        }

        // 4. Menangkap filter jenjang dari query string (?unit=...)
        $jenjang = $this->request->getVar('unit');

        // 5. Validasi: Jika unit kosong atau tidak ada di list valid, default ke 'GLOBAL'
        if (!$jenjang || !in_array(strtoupper($jenjang), array_map('strtoupper', $validCodes))) {
            $jenjang = 'GLOBAL';
        }

        // 6. Validasi Keamanan Akses (Scope Unit untuk Admin Unit)
        // Jika user bukan Pusat, paksa redirect ke unit mereka sendiri
        $sessionUnit = session('unit_kerja');
        if ($sessionUnit && !in_array($sessionUnit, ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            if ($jenjang !== $sessionUnit) {
                return redirect()->to('app/masterdata/identitas?unit=' . $sessionUnit);
            }
        }

        // Siapkan data untuk View (Menggunakan loadViewData dari BaseController)
        $data = $this->loadViewData([
            'title'          => 'Informasi & Identitas Sekolah', 
            'current_module' => 'masterdata',
            'current_unit'   => $jenjang,
            'list_jenjang'   => $listAktif, // Data tab navigasi
            'settings'       => $this->settingsModel->getSettingsAsArray($jenjang),
        ]);

        return view('masterdata/identitas/index', $data);
    }
    
    /**
     * Memproses update data secara massal berdasarkan jenjang yang dipilih
     */
    public function update()
    {
        // Default ke GLOBAL jika hidden input kosong/dimanipulasi
        $jenjang  = $this->request->getPost('jenjang_target') ?: 'GLOBAL';
        $postData = $this->request->getPost();
        
        // 1. Validasi Akses Lagi (Security Layer)
        $sessionUnit = session('unit_kerja');
        if ($sessionUnit && !in_array($sessionUnit, ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            if ($jenjang !== $sessionUnit) {
                return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak memiliki izin mengubah data unit ini.');
            }
        }

        $this->db->transStart();

        try {
            foreach ($postData as $key => $value) {
                // Filter: Abaikan field sistem CI4 dan field kontrol form
                if (in_array($key, ['csrf_test_name', 'jenjang_target', '_method'])) {
                    continue;
                }

                // Opsional: Trim value string agar bersih dari spasi
                if (is_string($value)) {
                    $value = trim($value);
                }

                // Simpan ke database menggunakan SettingsModel
                $this->settingsModel->updateSetting($jenjang, $key, $value);
            }
            
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException("Gagal melakukan commit database.");
            }

            return redirect()->to(base_url('app/masterdata/identitas?unit=' . $jenjang))
                             ->with('message', "Informasi Sekolah untuk unit <b>$jenjang</b> berhasil diperbarui.");

        } catch (\Exception $e) {
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}