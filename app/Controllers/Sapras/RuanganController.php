<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\RuanganModel;
use App\Models\Sapras\GedungModel;

class RuanganController extends BaseController
{
    protected $ruanganModel;
    protected $gedungModel;
    protected $db;

    public function __construct()
    {
        $this->ruanganModel = new RuanganModel();
        $this->gedungModel  = new GedungModel();
        $this->db           = \Config\Database::connect();
    }

    /**
     * Helper: Ambil Daftar Unit dari Database
     */
    private function getDaftarUnit()
    {
        $daftarUnit = [];
        try {
            if ($this->db->tableExists('jenjang_sekolah')) {
                $query = $this->db->table('jenjang_sekolah')->get();
                foreach ($query->getResultArray() as $row) {
                    $val = $row['kode_jenjang'];
                    $label = $row['nama'] ?? $row['nama_jenjang'] ?? $row['kode_jenjang'];
                    $daftarUnit[$val] = $label;
                }
            }
        } catch (\Exception $e) { }
        
        if (empty($daftarUnit)) {
            $daftarUnit = ['TK' => 'TK', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA'];
        }
        return $daftarUnit;
    }

    public function index()
    {
        // 1. Setup Data Dinamis
        $daftarUnit = $this->getDaftarUnit();
        $sessionJenjang = session('kode_jenjang');
        
        $strictUnits = array_keys($daftarUnit); 
        $isUnitAdmin = !empty($sessionJenjang) && in_array(strtoupper($sessionJenjang), $strictUnits);

        // 2. Filter Logic
        $filterJenjang = $this->request->getGet('jenjang');
        $scopeQuery = $isUnitAdmin ? $sessionJenjang : $filterJenjang;

        // 3. Prepare Data
        $data = [
            'title'          => 'Data Ruangan Sekolah',
            'ruangan'        => $this->ruanganModel->getPaginatedWithGedung($scopeQuery, 10),
            'pager'          => $this->ruanganModel->pager,
            // List Gedung untuk Dropdown di Modal (Filtered by Scope)
            'gedung'         => $this->gedungModel->byJenjang($scopeQuery)->findAll(),
            
            // UI Helpers
            'sessionJenjang' => $sessionJenjang,
            'isUnitAdmin'    => $isUnitAdmin,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $daftarUnit
        ];

        return view('sapras/ruangan/index', $data);
    }

    // Method New & Edit bisa dihapus jika menggunakan Modal di Index, 
    // atau disesuaikan jika ingin halaman terpisah. 
    // Di sini saya asumsikan pakai Modal sesuai kode view Anda sebelumnya.

    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');

        // Logika Unit: Jika Superadmin memilih gedung, unit ikut gedung tersebut (idealnya)
        // Atau ambil dari input hidden jika ada.
        // Disini kita ambil session dulu sebagai fallback aman.
        $inputJenjang = $this->request->getPost('kode_jenjang');
        $sessionJenjang = session('kode_jenjang');
        $finalJenjang = !empty($inputJenjang) ? $inputJenjang : $sessionJenjang;

        // Validasi tambahan: Jika user pilih gedung, pastikan kode_jenjang sesuai gedung tersebut (Opsional)
        
        $data = [
            'kode_jenjang' => $finalJenjang,
            'id_gedung'    => $this->request->getPost('id_gedung'),
            'nama'         => $this->request->getPost('nama'),
            'kapasitas'    => $this->request->getPost('kapasitas'),
            'keterangan'   => $this->request->getPost('keterangan'),
        ];

        if ($id) $data['id'] = $id;

        if (!$this->ruanganModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->ruanganModel->errors());
        }

        return redirect()->to(base_url('app/sapras/ruangan'))->with('success', 'Data ruangan berhasil disimpan.');
    }

    public function delete($id)
    {
        if (!$id || !$this->ruanganModel->find($id)) {
            return redirect()->to(base_url('app/sapras/ruangan'))->with('error', 'Data tidak ditemukan.');
        }
        $this->ruanganModel->delete($id);
        return redirect()->to(base_url('app/sapras/ruangan'))->with('success', 'Data ruangan berhasil dihapus.');
    }
}