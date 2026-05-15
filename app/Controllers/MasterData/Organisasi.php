<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\OrganisasiModel;
use App\Models\JabatanModel;
use App\Models\GuruModel;
use App\Models\KaryawanModel;
use App\Models\JenjangModel;

class Organisasi extends BaseController
{
    protected $organisasiModel;
    protected $jabatanModel;
    protected $guruModel;
    protected $karyawanModel;
    protected $jenjangModel;
    protected $userRole;
    protected $userJenjang;

    public function __construct()
    {
        $this->organisasiModel = new OrganisasiModel();
        $this->jabatanModel    = new JabatanModel();
        $this->guruModel       = new GuruModel();
        $this->karyawanModel   = new KaryawanModel();
        
        // Fail-safe jika JenjangModel belum ada
        if (file_exists(APPPATH . 'Models/JenjangModel.php')) {
            $this->jenjangModel = new JenjangModel();
        } else {
            $this->jenjangModel = new class extends \CodeIgniter\Model {
                public function findAll() { return []; }
                public function where($k, $v) { return $this; }
                public function orderBy($k, $d) { return $this; }
                public function asArray() { return $this; }
            };
        }

        // Normalisasi Role & Unit
        $this->userRole    = strtolower(session()->get('role_name') ?? session()->get('role') ?? ''); 
        $this->userJenjang = session()->get('kode_jenjang') ?? session()->get('kode_unit');
    }

    public function index()
    {
        // 1. Siapkan List Jenjang untuk Dropdown Filter (Hanya Superadmin/Yayasan)
        $listJenjang = [];
        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);

        if ($isGlobalUser && $this->jenjangModel) {
            $listJenjang = $this->jenjangModel->asArray()
                                              ->where('status', 'aktif')
                                              ->orderBy('urutan', 'ASC')
                                              ->findAll();
        }

        // 2. Tangkap Filter dari URL
        $filterJenjang = $this->request->getGet('kode_jenjang');

        // 3. Ambil Data Organisasi (Gunakan method di Model yang support filtering)
        // Jika model belum punya filter manual, kita bisa modifikasi sedikit logicnya di sini atau di model
        // Asumsi getFullOrganisasi() bisa menerima parameter filter
        $organisasi = $this->organisasiModel->getFullOrganisasi();

        // Manual filter jika model belum support parameter (sementara)
        if (!empty($filterJenjang) && $filterJenjang !== 'Semua') {
            $organisasi = array_filter($organisasi, function($item) use ($filterJenjang) {
                return ($item['kode_jenjang'] ?? '') == $filterJenjang;
            });
        } elseif (!$isGlobalUser) {
            // Admin Unit: Filter Paksa
            $organisasi = array_filter($organisasi, function($item) {
                return ($item['kode_jenjang'] ?? '') == $this->userJenjang;
            });
        }

        $data = [
            'title'          => 'Manajemen Struktur Organisasi',
            'organisasi'     => $organisasi,
            'role'           => $this->userRole,
            'jenjang'        => $this->userJenjang,
            'listJenjang'    => $listJenjang,
            'filter_jenjang' => $filterJenjang
        ];
        return view('masterdata/organisasi/index', $data);
    }

    public function visual()
    {
        $data = [
            'title'      => 'Bagan Visual Organisasi',
            'organisasi' => $this->organisasiModel->getFullOrganisasi(),
        ];
        return view('masterdata/organisasi/visual', $data);
    }

    public function new()
    {
        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);
        $listJenjang = [];

        if ($isGlobalUser) {
            $jenjangAktif = $this->jenjangModel->asArray()->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
            $globalItem = ['kode_jenjang' => 'Global', 'nama_jenjang' => 'Global / Yayasan'];
            $listJenjang = array_merge([$globalItem], $jenjangAktif);
        } else {
             // Admin Unit
             $unitInfo = $this->jenjangModel->asArray()->where('kode_jenjang', $this->userJenjang)->first();
             $listJenjang = [['kode_jenjang' => $this->userJenjang, 'nama_jenjang' => $unitInfo['nama_jenjang'] ?? $this->userJenjang]];
        }

        $data = [
            'title'         => 'Tambah Personel Organisasi',
            'organisasi'    => [], 
            'list_jenjang'  => $listJenjang,
            'list_jabatan'  => $this->jabatanModel->asArray()->orderBy('kode_jenjang', 'ASC')->orderBy('level', 'ASC')->findAll(),
            'list_guru'     => $this->guruModel->asArray()->where('status_aktif', 'aktif')->orderBy('nama_lengkap', 'ASC')->findAll(),
            'list_karyawan' => $this->karyawanModel->asArray()->where('status_aktif', 'aktif')->orderBy('nama_lengkap', 'ASC')->findAll(),
        ];
        return view('masterdata/organisasi/form', $data);
    }

    public function edit($id)
    {
        $item = $this->organisasiModel->asArray()->find($id);
        
        if (!$item) {
            return redirect()->to('app/masterdata/organisasi')->with('error', 'Data tidak ditemukan.');
        }

        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);
        if (!$isGlobalUser && $item['kode_jenjang'] !== $this->userJenjang) {
             return redirect()->to('app/masterdata/organisasi')->with('error', 'Akses Ditolak.');
        }

        // Logic List Jenjang (Sama dengan new)
        $listJenjang = [];
        if ($isGlobalUser) {
            $jenjangAktif = $this->jenjangModel->asArray()->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
            $globalItem = ['kode_jenjang' => 'Global', 'nama_jenjang' => 'Global / Yayasan'];
            $listJenjang = array_merge([$globalItem], $jenjangAktif);
        } else {
             $listJenjang = [['kode_jenjang' => $this->userJenjang, 'nama_jenjang' => $this->userJenjang]];
        }

        $data = [
            'title'         => 'Edit Personel Organisasi',
            'organisasi'    => $item,
            'list_jenjang'  => $listJenjang,
            'list_jabatan'  => $this->jabatanModel->asArray()->orderBy('kode_jenjang', 'ASC')->orderBy('level', 'ASC')->findAll(),
            'list_guru'     => $this->guruModel->asArray()->where('status_aktif', 'aktif')->orderBy('nama_lengkap', 'ASC')->findAll(),
            'list_karyawan' => $this->karyawanModel->asArray()->where('status_aktif', 'aktif')->orderBy('nama_lengkap', 'ASC')->findAll(),
        ];
        return view('masterdata/organisasi/form', $data);
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        
        $guruId = $this->request->getPost('guru_id');
        $karyawanId = $this->request->getPost('karyawan_id');
        $idPegawai = !empty($guruId) ? $guruId : (!empty($karyawanId) ? $karyawanId : null);

        $data = [
            'jenis_organisasi' => $this->request->getPost('jenis_organisasi'),
            'kode_jenjang'     => $this->request->getPost('kode_jenjang'), 
            'jabatan_id'       => $this->request->getPost('jabatan_id'),
            'nama_jabatan'     => $this->request->getPost('nama_jabatan'),
            'parent_id'        => $this->request->getPost('parent_id') ?: null,
            'id_pegawai'       => $idPegawai,
            'nama_pengampu'    => $this->request->getPost('nama_pengampu'),
            'nip'              => $this->request->getPost('nip'),
            'urutan'           => $this->request->getPost('urutan'),
            'status'           => $this->request->getPost('status') ?: 'aktif',
        ];

        if ($idPegawai) {
            $data['nama_pengampu'] = null;
            $data['nip']           = null;
        }

        if ($id) {
            $this->organisasiModel->update($id, $data);
            $message = 'Data personel berhasil diperbarui.';
        } else {
            $this->organisasiModel->insert($data);
            $message = 'Data personel berhasil ditambahkan.';
        }

        return redirect()->to('app/masterdata/organisasi')->with('success', $message);
    }

    public function delete($id)
    {
        if ($this->organisasiModel->find($id)) {
            $this->organisasiModel->delete($id);
            return redirect()->to('app/masterdata/organisasi')->with('success', 'Data berhasil dihapus.');
        }
        return redirect()->to('app/masterdata/organisasi')->with('error', 'Gagal menghapus data.');
    }
}