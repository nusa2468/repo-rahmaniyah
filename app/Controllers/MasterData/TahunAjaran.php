<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController; 
use App\Models\JenjangModel;
use App\Models\TahunAjaranModel; 
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller TahunAjaran (Enterprise Edition)
 * Mengelola kalender akademik (Tahun Ajaran & Semester) dengan proteksi unit.
 */
class TahunAjaran extends BaseController
{
    protected $tahunAjaranModel;
    protected $jenjangModel;

    private string $redirectBaseUrl = 'app/masterdata/tahunajaran';
    private array $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT'];

    public function __construct()
    {
        // Inisialisasi Model secara eksplisit
        if (class_exists('App\Models\TahunAjaranModel')) {
            $this->tahunAjaranModel = new TahunAjaranModel();
        }

        // Fail-safe JenjangModel
        if (file_exists(APPPATH . 'Models/JenjangModel.php')) {
            $this->jenjangModel = new JenjangModel();
        } else {
            $this->jenjangModel = new class extends \CodeIgniter\Model {
                protected $table = 'jenjang_sekolah';
                protected $returnType = 'array';
                public function findAll(int $limit = 0, int $offset = 0) { return []; }
                public function getAktifForIdentitas() { return []; }
            };
        }
    }

    /**
     * Menampilkan daftar Tahun Ajaran dengan dukungan Scoping Unit & Search.
     */
    public function index(): string
    {
        $session = session();
        // 1. Ambil Otoritas Session
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole    = strtolower($session->get('role_name') ?? '');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        // 2. Ambil Parameter Request
        $filterUnit = $this->request->getGet('unit');
        $search     = $this->request->getGet('search');
        $perPage    = $this->request->getGet('per_page') ?? 10;

        // Paksa filter jika bukan Super Admin
        if (!$isSuperAdmin) {
            $filterUnit = $userJenjang;
        }

        $unitParam = (empty($filterUnit) || in_array(strtoupper($filterUnit), $this->globalIdentifiers)) ? null : $filterUnit;

        // 3. Query Building dengan Scoping
        $model = $this->tahunAjaranModel;
        
        // Gunakan select manual atau builder dari model jika tersedia
        $model->select('tahun_ajaran.*, js.nama_jenjang as unit_sekolah');
        $model->join('jenjang_sekolah js', 'js.kode_jenjang = tahun_ajaran.kode_jenjang', 'left');

        if ($unitParam) {
            $model->where('tahun_ajaran.kode_jenjang', $unitParam);
        }

        if (!empty($search)) {
            $model->groupStart()
                  ->like('tahun_ajaran.tahun_ajaran', $search)
                  ->orLike('tahun_ajaran.keterangan', $search)
                  ->groupEnd();
        }

        // Urutkan: Aktif di atas, lalu berdasarkan tahun terbaru
        $model->orderBy('tahun_ajaran.status', 'ASC')
              ->orderBy('tahun_ajaran.tahun_ajaran', 'DESC')
              ->orderBy('tahun_ajaran.semester', 'DESC');

        // 4. Ambil daftar jenjang untuk filter (Hanya yang diizinkan)
        $jenjangList = [];
        if ($isSuperAdmin) {
            $allJenjangs = method_exists($this->jenjangModel, 'getAktifForIdentitas') 
                            ? $this->jenjangModel->getAktifForIdentitas() 
                            : $this->jenjangModel->where('status', 'aktif')->findAll();
                            
            $jenjangList = array_filter($allJenjangs, function($j) {
                $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
                return !in_array($kode, $this->globalIdentifiers);
            });
        }

        $data = [
            'title'          => 'Master Tahun Ajaran',
            'role'           => $userRole,
            'jenjang'        => $userJenjang,
            'tahun_ajaran'   => $model->paginate((int)$perPage, 'tahun_ajaran'),
            'pager'          => $model->pager,
            'pager_obj'      => $model->pager, 
            'jenjang_list'   => $jenjangList,
            'is_restricted'  => !$isSuperAdmin,
            'current_filter' => [
                'unit'     => $filterUnit ?? ($isSuperAdmin ? 'GLOBAL' : $userJenjang),
                'per_page' => $perPage,
                'search'   => $search
            ]
        ];

        return view('masterdata/tahunajaran/index', $data);
    }

    public function show($id = null): RedirectResponse
    {
        return redirect()->to(base_url($this->redirectBaseUrl . '/edit/' . $id));
    }

    /**
     * Form tambah Tahun Ajaran baru.
     */
    public function new(): string
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        $allJenjang = method_exists($this->jenjangModel, 'getAktifForIdentitas') 
                        ? $this->jenjangModel->getAktifForIdentitas() 
                        : $this->jenjangModel->where('status', 'aktif')->findAll();

        // Admin Unit hanya bisa menambah TA untuk unitnya sendiri
        $filteredJenjang = array_filter($allJenjang, function($j) use ($isSuperAdmin, $userJenjang) {
            $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
            if (in_array($kode, $this->globalIdentifiers)) return false;
            return $isSuperAdmin || $kode === $userJenjang;
        });

        $data = [
            'title'        => 'Tambah Tahun Ajaran & Semester',
            'tahun_ajaran' => [],
            'list_jenjang' => $filteredJenjang,
            'validation'   => \Config\Services::validation(),
        ];

        return view('masterdata/tahunajaran/form', $data);
    }

    /**
     * Simpan data baru dengan validasi keamanan dan integritas tanggal.
     */
    public function create(): RedirectResponse
    {
        $dataPost = $this->request->getPost();
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');

        // Security Check: Mencegah injeksi kode_jenjang dari unit lain
        if ($userJenjang !== 'GLOBAL' && !in_array($userJenjang, $this->globalIdentifiers)) {
            // Paksa override input jika user bukan admin global
            $dataPost['kode_jenjang'] = $userJenjang;
        }

        // 1. Validasi Duplikasi Periode (Scoped per Unit)
        if (!$this->_validateDuplicatePeriode($dataPost)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Kombinasi Tahun Ajaran & Semester ini sudah ada untuk unit tersebut.');
        }

        // 2. Validasi Urutan Tanggal
        if (!$this->_validateDateOrder($dataPost)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Tanggal Selesai tidak boleh mendahului Tanggal Mulai.');
        }

        // 3. Validasi Tumpang Tindih (Overlap)
        if (!$this->_validateDateOverlap($dataPost)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Rentang waktu tumpang tindih dengan periode lain di unit yang sama.');
        }

        if ($this->tahunAjaranModel->save($dataPost)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Tahun Ajaran & Semester berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('errors', $this->tahunAjaranModel->errors());
    }

    /**
     * Form edit dengan proteksi unit.
     */
    public function edit($id = null): string
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $ta = $this->tahunAjaranModel->find($id);
        if (!$ta) throw PageNotFoundException::forPageNotFound();

        // Proteksi Otoritas
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);
        
        if (!$isSuperAdmin && strtoupper($ta['kode_jenjang']) !== $userJenjang) {
            // Menggunakan view error 403 atau pesan error
            return view('errors/html/error_403', ['message' => 'Akses Ditolak: Anda tidak memiliki izin mengedit data unit lain.']);
        }

        $allJenjang = method_exists($this->jenjangModel, 'getAktifForIdentitas') 
                        ? $this->jenjangModel->getAktifForIdentitas() 
                        : $this->jenjangModel->where('status', 'aktif')->findAll();

        $filteredJenjang = array_filter($allJenjang, function($j) use ($isSuperAdmin, $userJenjang) {
            $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
            return $isSuperAdmin || $kode === $userJenjang;
        });

        $data = [
            'title'        => 'Edit Tahun Ajaran & Semester',
            'tahun_ajaran' => $ta,
            'list_jenjang' => $filteredJenjang,
            'validation'   => \Config\Services::validation(),
        ];

        return view('masterdata/tahunajaran/form', $data);
    }

    /**
     * Update data dengan validasi ulang.
     */
    public function update($id = null): RedirectResponse
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $dataPost = $this->request->getPost();
        $dataPost['id'] = $id;

        // Security Check Unit
        $existing = $this->tahunAjaranModel->find($id);
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($existing['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Akses Ditolak.');
        }
        
        // Pastikan kode jenjang konsisten (tidak diubah paksa lewat inspect element)
        if (!in_array($userJenjang, $this->globalIdentifiers)) {
            $dataPost['kode_jenjang'] = $userJenjang;
        }

        // 1. Validasi Duplikasi Periode (Scoped)
        if (!$this->_validateDuplicatePeriode($dataPost, $id)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Periode ini sudah ada di unit yang sama.');
        }

        // 2. Validasi Urutan Tanggal
        if (!$this->_validateDateOrder($dataPost)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Urutan tanggal tidak valid.');
        }

        // 3. Validasi Tumpang Tindih
        if (!$this->_validateDateOverlap($dataPost, $id)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Terjadi tumpang tindih periode di unit yang sama.');
        }

        if ($this->tahunAjaranModel->save($dataPost)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Tahun Ajaran berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('errors', $this->tahunAjaranModel->errors());
    }

    /**
     * Hapus data (Hanya jika tidak sedang AKTIF).
     */
    public function delete($id = null): RedirectResponse
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $ta = $this->tahunAjaranModel->find($id);
        if (!$ta) throw PageNotFoundException::forPageNotFound();

        // Proteksi Otoritas
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($ta['kode_jenjang']) !== $userJenjang) {
            return redirect()->back()->with('error', 'Otoritas Ditolak.');
        }

        // Jangan izinkan hapus yang sedang aktif
        if (strtolower($ta['status'] ?? '') === 'aktif') {
            return redirect()->back()->with('error', 'Gagal: Periode yang sedang AKTIF tidak dapat dihapus.');
        }

        if ($this->tahunAjaranModel->delete($id)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }

    /**
     * Private: Validasi urutan tanggal (Mulai <= Selesai).
     */
    private function _validateDateOrder(array $dataPost): bool
    {
        if (empty($dataPost['tanggal_mulai']) || empty($dataPost['tanggal_selesai'])) {
            return true; // Skip jika tanggal tidak diisi (tergantung rules model)
        }
        return strtotime($dataPost['tanggal_selesai']) >= strtotime($dataPost['tanggal_mulai']);
    }

    /**
     * Private: Validasi tumpang tindih tanggal pada unit yang sama.
     */
    private function _validateDateOverlap(array $dataPost, $id = null): bool
    {
        if (empty($dataPost['tanggal_mulai']) || empty($dataPost['tanggal_selesai']) || empty($dataPost['kode_jenjang'])) {
            return true;
        }

        $startDate = $dataPost['tanggal_mulai'];
        $endDate = $dataPost['tanggal_selesai'];
        $jenjang = $dataPost['kode_jenjang'];

        $builder = $this->tahunAjaranModel->builder()
            ->where('kode_jenjang', $jenjang)
            ->groupStart()
                ->where('tanggal_mulai <=', $endDate)
                ->where('tanggal_selesai >=', $startDate)
            ->groupEnd();

        if ($id) {
            $builder->where('id !=', $id);
        }

        return $builder->countAllResults() === 0;
    }

    /**
     * Private: Validasi Keunikan Tahun Ajaran + Semester dalam satu Unit (Scope).
     * Mencegah duplikat seperti: "2024/2025 Ganjil" ada dua kali di unit "SD".
     */
    private function _validateDuplicatePeriode(array $dataPost, $id = null): bool
    {
        if (empty($dataPost['tahun_ajaran']) || empty($dataPost['semester']) || empty($dataPost['kode_jenjang'])) {
            return true; // Biarkan validasi model yang menangani field kosong
        }

        $where = [
            'tahun_ajaran' => $dataPost['tahun_ajaran'],
            'semester'     => $dataPost['semester'],
            'kode_jenjang' => $dataPost['kode_jenjang']
        ];

        $builder = $this->tahunAjaranModel->where($where);

        if ($id) {
            $builder->where('id !=', $id);
        }

        // Return TRUE jika tidak ada duplikat (count == 0)
        return $builder->countAllResults() === 0;
    }
}