<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\JurusanModel;
use App\Models\JenjangModel;

class Jurusan extends BaseController
{
    protected $jurusanModel;
    protected $jenjangModel;
    protected $userRole;
    protected $userJenjang;

    public function __construct()
    {
        $this->jurusanModel = new JurusanModel();
        
        // Cek keberadaan JenjangModel untuk form & dropdown filter
        if (class_exists('App\Models\JenjangModel')) {
            $this->jenjangModel = new JenjangModel();
        }

        // Normalisasi Role
        $this->userRole = strtolower(session()->get('role_name') ?? session()->get('role') ?? ''); 
        
        // Ambil konteks unit (Prioritas: kode_jenjang -> kode_unit)
        $this->userJenjang = session()->get('kode_jenjang') ?? session()->get('kode_unit');
    }

    public function index()
    {
        // 1. Siapkan List Jenjang untuk Dropdown Filter (Hanya Superadmin/Yayasan)
        $listJenjang = [];
        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);

        if ($isGlobalUser && $this->jenjangModel) {
            $listJenjang = $this->jenjangModel->where('status', 'aktif')
                                              ->orderBy('urutan', 'ASC')
                                              ->findAll();
        }

        // 2. Tangkap Filter dari URL
        $filterJenjang = $this->request->getGet('kode_jenjang');

        // 3. Ambil Data Jurusan
        // Jika Superadmin memilih filter, gunakan filter tersebut.
        // Jika tidak, gunakan logika bawaan model (getScopedData)
        if ($isGlobalUser && !empty($filterJenjang) && $filterJenjang !== 'Semua') {
            $jurusan = $this->jurusanModel->where('kode_jenjang', $filterJenjang)
                                          ->orderBy('nama_jurusan', 'ASC')
                                          ->findAll();
        } else {
            $jurusan = $this->jurusanModel->getScopedData($this->userRole, $this->userJenjang);
        }

        $data = [
            'title'          => 'Data Jurusan',
            'role'           => $this->userRole,
            'jenjang'        => $this->userJenjang,
            'jurusan'        => $jurusan,
            'listJenjang'    => $listJenjang,   // Data untuk Dropdown di View
            'filter_jenjang' => $filterJenjang  // State terpilih
        ];
        
        return view('masterdata/jurusan/index', $data);
    }

    public function form($id = null)
    {
        $jurusan = null;
        if ($id) {
            // Cek akses sebelum ambil data
            if (!$this->jurusanModel->checkAccess($id, $this->userRole, $this->userJenjang)) {
                return redirect()->to('/app/masterdata/jurusan')->with('error', 'Akses Ditolak.');
            }
            $jurusan = $this->jurusanModel->find($id);
        }

        // Ambil Data Jenjang untuk Dropdown di Form (Khusus Superadmin)
        $listJenjang = [];
        if (in_array($this->userRole, ['superadmin', 'yayasan']) && $this->jenjangModel) {
            $listJenjang = $this->jenjangModel->where('status', 'aktif')
                                              ->orderBy('urutan', 'ASC')
                                              ->findAll();
        }

        $data = [
            'title'       => $id ? 'Edit Jurusan' : 'Tambah Jurusan',
            'role'        => $this->userRole,
            'userJenjang' => $this->userJenjang,
            'jurusan'     => $jurusan,
            'listJenjang' => $listJenjang
        ];

        return view('masterdata/jurusan/form', $data);
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        
        // Logika Jenjang: Superadmin bisa set manual, Admin Unit dipaksa ikut sesinya
        $targetJenjang = $this->request->getPost('kode_jenjang');
        
        if (!in_array($this->userRole, ['superadmin', 'yayasan'])) {
            $targetJenjang = $this->userJenjang;
        }

        // Validasi Unit (Opsional): SD/SMP/TK biasanya tidak punya jurusan
        // if (!in_array($this->userRole, ['superadmin', 'yayasan']) && in_array($targetJenjang, ['TK', 'SD', 'SMP'])) {
        //    return redirect()->back()->with('error', "Unit $targetJenjang tidak memerlukan data Jurusan.");
        // }

        $dataSave = [
            'id'           => $id, // Penting untuk deteksi Update di Model (is_unique validation)
            'kode_jenjang' => $targetJenjang,
            'kode_jurusan' => strtoupper($this->request->getPost('kode_jurusan')),
            'nama_jurusan' => $this->request->getPost('nama_jurusan'),
            'status'       => $this->request->getPost('status'),
            'keterangan'   => $this->request->getPost('keterangan'),
        ];

        // Jika Update, cek akses kepemilikan data
        if ($id) {
            if (!$this->jurusanModel->checkAccess($id, $this->userRole, $this->userJenjang)) {
                return redirect()->to('app/masterdata/jurusan')->with('error', 'Akses Ilegal terdeteksi.');
            }
        }

        // Gunakan save() standar (validasi ada di Model)
        if (!$this->jurusanModel->save($dataSave)) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.')->with('errors', $this->jurusanModel->errors());
        }

        return redirect()->to('/app/masterdata/jurusan')->with('success', 'Data Jurusan berhasil disimpan.');
    }

    public function delete($id)
    {
        if (!$this->jurusanModel->checkAccess($id, $this->userRole, $this->userJenjang)) {
            return redirect()->to('/app/masterdata/jurusan')->with('error', 'Gagal: Akses Ditolak.');
        }

        $this->jurusanModel->delete($id);
        return redirect()->to('/app/masterdata/jurusan')->with('success', 'Data berhasil dihapus.');
    }
}