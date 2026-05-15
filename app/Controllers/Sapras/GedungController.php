<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\GedungModel;

class GedungController extends BaseController
{
    protected $model;
    protected $db;

    public function __construct()
    {
        $this->model = new GedungModel();
        $this->db    = \Config\Database::connect();
    }

    /**
     * Helper: Ambil Daftar Unit dari Database Jenjang
     * Menggantikan hardcode array.
     */
    private function getDaftarUnit()
    {
        $daftarUnit = [];
        try {
            // Cek keberadaan tabel untuk menghindari error fatal jika migrasi belum jalan
            if ($this->db->tableExists('jenjang_sekolah')) {
                $query = $this->db->table('jenjang_sekolah')->get();
                foreach ($query->getResultArray() as $row) {
                    $val = $row['kode_jenjang'];
                    // Fallback nama jika kolom 'nama' berbeda
                    $label = $row['nama'] ?? $row['nama_jenjang'] ?? $row['kode_jenjang'];
                    $daftarUnit[$val] = $label;
                }
            }
        } catch (\Exception $e) {
            // Silent fail, return empty array. Sistem akan bergantung pada data DB.
        }
        return $daftarUnit;
    }

    public function index()
    {
        // 1. Ambil Daftar Unit Dinamis
        $daftarUnit = $this->getDaftarUnit();

        // 2. Deteksi Scope User (Unit vs Yayasan)
        $sessionJenjang = session('kode_jenjang');
        
        // Strict Units diambil dari keys database agar sinkron
        $strictUnits = array_keys($daftarUnit); 
        $isUnitAdmin = !empty($sessionJenjang) && in_array(strtoupper($sessionJenjang), $strictUnits);

        // 3. Ambil Filter dari URL (hanya untuk Superadmin)
        $filterJenjang = $this->request->getGet('jenjang');
        
        // 4. Tentukan Scope Akhir
        $scopeQuery = $isUnitAdmin ? $sessionJenjang : $filterJenjang;

        $data = [
            'title'          => 'Data Gedung / Bangunan',
            'gedung'         => $this->model->getPaginated($scopeQuery, 10),
            'pager'          => $this->model->pager,
            'sessionJenjang' => $sessionJenjang,
            'isUnitAdmin'    => $isUnitAdmin,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $daftarUnit // Data dinamis untuk Filter Dropdown
        ];
        return view('sapras/gedung/index', $data);
    }

    public function new()
    {
        $data = [
            'title'      => 'Tambah Gedung Baru', 
            'gedung'     => null,
            'daftarUnit' => $this->getDaftarUnit() // Kirim data dinamis ke Form
        ];
        return view('sapras/gedung/form', $data);
    }

    public function edit($id)
    {
        $gedung = $this->model->find($id);
        if (!$gedung) {
            return redirect()->to(base_url('app/sapras/gedung'))->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Data Gedung', 
            'gedung'     => (object) $gedung,
            'daftarUnit' => $this->getDaftarUnit() // Kirim data dinamis ke Form
        ];
        return view('sapras/gedung/form', $data);
    }

    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');

        // Logika Dinamis: Prioritaskan input POST (untuk Superadmin), fallback ke Session (untuk Admin Unit)
        $inputJenjang = $this->request->getPost('kode_jenjang');
        $sessionJenjang = session('kode_jenjang');
        $finalJenjang = !empty($inputJenjang) ? $inputJenjang : $sessionJenjang;

        $data = [
            'kode_jenjang' => $finalJenjang,
            'nama'         => $this->request->getPost('nama'),
            'tahun'        => $this->request->getPost('tahun'),
            'luas'         => $this->request->getPost('luas'),
            'keterangan'   => $this->request->getPost('keterangan'),
        ];

        if ($id) $data['id'] = $id;

        if (!$this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(base_url('app/sapras/gedung'))->with('success', 'Data gedung berhasil disimpan.');
    }

    public function delete($id)
    {
        if (!$id || !$this->model->find($id)) {
            return redirect()->to(base_url('app/sapras/gedung'))->with('error', 'Data tidak ditemukan.');
        }

        $this->model->delete($id);
        return redirect()->to(base_url('app/sapras/gedung'))->with('success', 'Data gedung berhasil dihapus.');
    }
}