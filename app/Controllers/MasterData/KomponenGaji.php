<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\KomponenGajiModel; // Pastikan model ini ada
use App\Models\JenjangModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller KomponenGaji (Enterprise Edition)
 * Mengelola komponen pendapatan dan potongan gaji dengan proteksi unit.
 */
class KomponenGaji extends BaseController
{
    protected $komponenGajiModel;
    protected $jenjangModel;

    private $redirectBaseUrl = 'app/masterdata/komponen-gaji';
    private $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT'];

    public function __construct()
    {
        // Inisialisasi Model secara eksplisit
        if (class_exists('App\Models\KomponenGajiModel')) {
            $this->komponenGajiModel = new \App\Models\KomponenGajiModel();
        }

        // Fail-safe JenjangModel
        if (file_exists(APPPATH . 'Models/JenjangModel.php')) {
            $this->jenjangModel = new JenjangModel();
        } else {
            $this->jenjangModel = new class extends \CodeIgniter\Model {
                protected $table = 'jenjang_sekolah';
                protected $returnType = 'array';
                public function findAll(int $limit = 0, int $offset = 0) { return []; }
            };
        }
    }

    /**
     * Menampilkan daftar komponen gaji dengan dukungan Scoping Unit, Pagination, dan Search.
     */
    public function index()
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole    = strtolower($session->get('role_name') ?? '');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        // Parameter Request
        $filterUnit = $this->request->getGet('unit');
        $search     = $this->request->getGet('search');
        $perPage    = $this->request->getGet('per_page') ?? 10;

        // Paksa filter jika bukan Super Admin
        if (!$isSuperAdmin) {
            $filterUnit = $userJenjang;
        }

        $unitParam = (empty($filterUnit) || in_array(strtoupper($filterUnit), $this->globalIdentifiers)) ? null : $filterUnit;

        // Query melalui Model Builder
        // Pastikan model memiliki method getKomponenBuilder atau gunakan builder manual
        if (method_exists($this->komponenGajiModel, 'getKomponenBuilder')) {
            $query = $this->komponenGajiModel->getKomponenBuilder($unitParam, $search);
            $stats = $this->komponenGajiModel->getStats($unitParam);
        } else {
            // Fallback Manual Builder jika method model belum update
            $query = $this->komponenGajiModel->builder();
            if ($unitParam) $query->where('kode_jenjang', $unitParam);
            if ($search) $query->like('nama_komponen', $search);
            $stats = []; // Fallback stats kosong
        }

        // Ambil list jenjang untuk filter UI (Anti Bocor)
        $jenjangList = [];
        if ($isSuperAdmin) {
            $allJenjangs = $this->jenjangModel->asArray()->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
            $jenjangList = array_filter($allJenjangs, function($j) {
                return !in_array(strtoupper($j['kode_jenjang']), $this->globalIdentifiers);
            });
        }

        $data = [
            'title'          => 'Manajemen Komponen Gaji',
            'role'           => $userRole,
            'jenjang'        => $userJenjang,
            'komponen_list'  => $query->paginate((int)$perPage, 'komponen'),
            'pager'          => $this->komponenGajiModel->pager,
            'pager_obj'      => $this->komponenGajiModel->pager, // Untuk kompatibilitas view pegawai
            'stats'          => $stats,
            'jenjang_list'   => $jenjangList,
            'is_restricted'  => !$isSuperAdmin,
            'current_filter' => [
                'unit'     => $filterUnit ?? ($isSuperAdmin ? 'GLOBAL' : $userJenjang),
                'per_page' => $perPage,
                'search'   => $search
            ]
        ];

        return view('masterdata/komponen_gaji/index', $data);
    }

    /**
     * Form tambah komponen baru.
     */
    public function new()
    {
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        $allJenjangs = $this->jenjangModel->asArray()->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        $filteredJenjang = array_filter($allJenjangs, function($j) use ($isSuperAdmin, $userJenjang) {
            $kode = strtoupper($j['kode_jenjang']);
            if (in_array($kode, $this->globalIdentifiers)) return false;
            return $isSuperAdmin || $kode === $userJenjang;
        });

        $data = [
            'title'        => 'Tambah Komponen Gaji',
            'komponen'     => [
                'is_aktif'     => 1,
                'kode_jenjang' => $isSuperAdmin ? '' : $userJenjang,
                'tipe'         => 1, // Default Pendapatan
                'metode_hitung'=> 'fixed'
            ],
            'jenjang_list' => $filteredJenjang,
            'validation'   => \Config\Services::validation(),
        ];
        
        return view('masterdata/komponen_gaji/form', $data);
    }

    /**
     * Menyimpan data komponen baru.
     */
    public function create()
    {
        $dataPost = $this->request->getPost();
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');

        // Security Check: Pastikan unit yang diinput sesuai otoritas
        if ($userJenjang !== 'GLOBAL' && !in_array($userJenjang, $this->globalIdentifiers) && $dataPost['kode_jenjang'] !== $userJenjang) {
             return redirect()->back()->withInput()->with('error', 'Otoritas Ditolak: Unit tidak sesuai.');
        }

        if (!$this->komponenGajiModel->insert($dataPost)) {
            return redirect()->back()->withInput()->with('errors', $this->komponenGajiModel->errors());
        }

        return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Komponen gaji berhasil disimpan.');
    }

    /**
     * Form edit data komponen.
     */
    public function edit($id = null)
    {
        $komponen = $this->komponenGajiModel->find($id);
        if (!$komponen) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        // Proteksi Lintas Unit
        if (!$isSuperAdmin && strtoupper($komponen['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Otoritas Ditolak: Anda tidak dapat mengedit komponen unit lain.');
        }

        $allJenjangs = $this->jenjangModel->asArray()->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        $filteredJenjang = array_filter($allJenjangs, function($j) use ($isSuperAdmin, $userJenjang) {
            $kode = strtoupper($j['kode_jenjang']);
            if (in_array($kode, $this->globalIdentifiers)) return false;
            return $isSuperAdmin || $kode === $userJenjang;
        });

        $data = [
            'title'        => 'Edit Komponen: ' . $komponen['nama_komponen'],
            'komponen'     => $komponen,
            'jenjang_list' => $filteredJenjang,
            'validation'   => \Config\Services::validation(),
        ];

        return view('masterdata/komponen_gaji/form', $data);
    }

    /**
     * Memperbarui data komponen gaji.
     */
    public function update($id = null)
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $existing = $this->komponenGajiModel->find($id);
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        if (!$isSuperAdmin && strtoupper($existing['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Akses Ditolak.');
        }

        $dataPost = $this->request->getPost();
        
        // Gunakan update dengan validasi model
        if (!$this->komponenGajiModel->update($id, $dataPost)) {
            return redirect()->back()->withInput()->with('errors', $this->komponenGajiModel->errors());
        }

        return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data komponen berhasil diperbarui.');
    }

    /**
     * Hapus data (Soft Delete).
     */
    public function delete($id = null)
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $komponen = $this->komponenGajiModel->find($id);
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        if (!$isSuperAdmin && strtoupper($komponen['kode_jenjang']) !== $userJenjang) {
            return redirect()->back()->with('error', 'Otoritas Ditolak.');
        }

        if ($this->komponenGajiModel->delete($id)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Komponen berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }
}