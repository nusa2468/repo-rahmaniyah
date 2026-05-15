<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\PeralatanModel;

class PeralatanController extends BaseController
{
    protected $model;
    protected $db;

    public function __construct()
    {
        $this->model = new PeralatanModel();
        $this->db    = \Config\Database::connect();
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
            'title'          => 'Data Peralatan Sekolah',
            'peralatan'      => $this->model->getPaginated($scopeQuery, 10),
            'pager'          => $this->model->pager,
            
            // UI Helpers
            'sessionJenjang' => $sessionJenjang,
            'isUnitAdmin'    => $isUnitAdmin,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $daftarUnit
        ];

        return view('sapras/peralatan/index', $data);
    }

    public function new()
    {
        $data = [
            'title'      => 'Tambah Peralatan', 
            'peralatan'  => null,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('sapras/peralatan/form', $data);
    }

    public function edit($id)
    {
        $item = $this->model->find($id);
        if (!$item) {
            return redirect()->to(base_url('app/sapras/peralatan'))->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title'      => 'Edit Peralatan', 
            'peralatan'  => (object) $item,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('sapras/peralatan/form', $data);
    }

    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');

        $inputJenjang = $this->request->getPost('kode_jenjang');
        $sessionJenjang = session('kode_jenjang');
        $finalJenjang = !empty($inputJenjang) ? $inputJenjang : $sessionJenjang;

        $data = [
            'kode_jenjang' => $finalJenjang,
            'nama'         => $this->request->getPost('nama'),
            'kondisi'      => $this->request->getPost('kondisi'),
            'jumlah'       => $this->request->getPost('jumlah'),
            'keterangan'   => $this->request->getPost('keterangan'),
        ];

        if ($id) $data['id'] = $id;

        if (!$this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(base_url('app/sapras/peralatan'))->with('success', 'Data peralatan berhasil disimpan.');
    }

    public function delete($id)
    {
        if (!$id || !$this->model->find($id)) {
            return redirect()->to(base_url('app/sapras/peralatan'))->with('error', 'Data tidak ditemukan.');
        }
        $this->model->delete($id);
        return redirect()->to(base_url('app/sapras/peralatan'))->with('success', 'Data peralatan dihapus.');
    }
}