<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\TanahModel;

class TanahController extends BaseController
{
    protected $model;
    protected $db;

    public function __construct()
    {
        $this->model = new TanahModel();
        $this->db    = \Config\Database::connect();
    }

    /**
     * Helper: Ambil Daftar Unit dari Database Jenjang
     */
    private function getDaftarUnit()
    {
        $daftarUnit = [];
        try {
            // Cek apakah tabel jenjang_sekolah ada
            if ($this->db->tableExists('jenjang_sekolah')) {
                $query = $this->db->table('jenjang_sekolah')->get();
                foreach ($query->getResultArray() as $row) {
                    $val = $row['kode_jenjang'];
                    $label = $row['nama'] ?? $row['nama_jenjang'] ?? $row['kode_jenjang'];
                    $daftarUnit[$val] = $label;
                }
            }
        } catch (\Exception $e) { } // Silent fail
        
        // Fallback jika DB kosong agar dropdown tidak kosong melompong
        if (empty($daftarUnit)) {
            $daftarUnit = ['TK' => 'TK PRATAMA', 'SD' => 'SD PRATAMA', 'SMP' => 'SMP PRATAMA', 'SMA' => 'SMA PRATAMA'];
        }
        
        return $daftarUnit;
    }

    public function index()
    {
        $daftarUnit = $this->getDaftarUnit();
        $sessionJenjang = session('kode_jenjang'); 
        
        $strictUnits = array_keys($daftarUnit); 
        $isUnitAdmin = !empty($sessionJenjang) && in_array(strtoupper($sessionJenjang), $strictUnits);

        $filterJenjang = $this->request->getGet('jenjang');
        $scopeQuery = $isUnitAdmin ? $sessionJenjang : $filterJenjang;

        $data = [
            'title'          => 'Manajemen Aset Tanah',
            'tanah'          => $this->model->getPaginated($scopeQuery, 10),
            'pager'          => $this->model->pager,
            'sessionJenjang' => $sessionJenjang,
            'isUnitAdmin'    => $isUnitAdmin,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $daftarUnit
        ];
        
        return view('sapras/tanah/index', $data);
    }

    public function new()
    {
        $data = [
            'title'      => 'Tambah Data Tanah', 
            'tanah'      => null,
            'daftarUnit' => $this->getDaftarUnit() // Kirim Data Unit
        ];
        return view('sapras/tanah/form', $data);
    }

    public function edit($id)
    {
        $tanah = $this->model->find($id);
        if (!$tanah) {
            return redirect()->to(base_url('app/sapras/tanah'))->with('error', 'Data tanah tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Data Tanah', 
            'tanah'      => (object) $tanah,    // Cast ke Object
            'daftarUnit' => $this->getDaftarUnit() // PENTING: Kirim Data Unit agar dropdown terisi
        ];
        return view('sapras/tanah/form', $data);
    }

    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $sessionJenjang = session('kode_jenjang');
        $finalJenjang = !empty($inputJenjang) ? $inputJenjang : $sessionJenjang;

        $data = [
            'kode_jenjang' => $finalJenjang,
            'nama'         => $this->request->getPost('nama'),
            'luas'         => $this->request->getPost('luas'),
            'sertifikat'   => $this->request->getPost('sertifikat'),
            'keterangan'   => $this->request->getPost('keterangan'),
        ];

        if ($id) $data['id'] = $id;

        if (!$this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(base_url('app/sapras/tanah'))->with('success', 'Data aset tanah berhasil disimpan.');
    }

    public function delete($id)
    {
        if (!$id || !$this->model->find($id)) {
            return redirect()->to(base_url('app/sapras/tanah'))->with('error', 'Data tidak ditemukan.');
        }
        $this->model->delete($id);
        return redirect()->to(base_url('app/sapras/tanah'))->with('success', 'Aset tanah berhasil dihapus.');
    }
}