<?php

namespace App\Controllers\Cms;

use App\Controllers\BaseController;
use App\Models\Cms\PengumumanModel; // Pastikan Model ini ada

/**
 * Controller Pengumuman
 * Mengelola pengumuman sekolah dengan sinkronisasi multi-unit (SD/SMP/SMA/Global).
 */
class Pengumuman extends BaseController
{
    protected $pengumumanModel;
    protected $db;

    public function __construct()
    {
        $this->pengumumanModel = new PengumumanModel();
        $this->db              = \Config\Database::connect();
    }

    /**
     * Helper: Ambil Daftar Unit dari Database (Dinamis)
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
        
        // Fallback jika tabel kosong
        if (empty($daftarUnit)) {
            $daftarUnit = ['TK' => 'TK', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA'];
        }
        return $daftarUnit;
    }

    /**
     * Helper: Cek Akses Global
     */
    private function isGlobalAccess(?string $context): bool
    {
        $globalScopes = ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT'];
        return empty($context) || in_array(strtoupper($context), $globalScopes);
    }

    /**
     * Menampilkan daftar pengumuman untuk Admin.
     */
    public function index()
    {
        // 1. Setup Data Dinamis
        $daftarUnit = $this->getDaftarUnit();
        $sessionJenjang = session('kode_jenjang');
        $isGlobal = $this->isGlobalAccess($sessionJenjang);

        // 2. Filter Logic
        $filterJenjang = $this->request->getGet('jenjang');
        
        // Tentukan Scope Query
        $scopeQuery = $isGlobal ? $filterJenjang : $sessionJenjang;

        // Terapkan Filter pada Model
        if (!empty($scopeQuery)) {
            $this->pengumumanModel->where('kode_jenjang', $scopeQuery);
        }

        // Ambil Data (Pagination)
        // Gunakan method model jika ada getPengumumanWithAuthor, jika tidak fallback ke findAll/paginate
        // Asumsi method getPengumumanWithAuthor mengembalikan result array atau builder
        // Disini kita pakai standard builder agar aman dengan filter di atas
        $pengumuman = $this->pengumumanModel
                           ->select('pengumuman.*, users.username as penulis') // Contoh join
                           ->join('users', 'users.id = pengumuman.id_penulis', 'left')
                           ->orderBy('created_at', 'DESC')
                           ->paginate(10);

        $data = [
            'title'          => 'Kelola Pengumuman',
            'pengumuman'     => $pengumuman,
            'pager'          => $this->pengumumanModel->pager,
            
            // UI Helpers
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $daftarUnit
        ];

        return view('cms/pengumuman/index', $data);
    }

    /**
     * Form untuk membuat pengumuman baru.
     */
    public function new()
    {
        $data = [
            'title'      => 'Buat Pengumuman Baru', 
            'post'       => null,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('cms/pengumuman/form', $data);
    }

    /**
     * Form untuk mengedit pengumuman yang sudah ada.
     */
    public function edit($id)
    {
        $post = $this->pengumumanModel->find($id);
        if (!$post) {
            return redirect()->to(base_url('app/cms/pengumuman'))->with('error', 'Data tidak ditemukan');
        }

        // Cek Hak Akses Edit
        $sessionJenjang = session('kode_jenjang');
        if (!$this->isGlobalAccess($sessionJenjang)) {
            $postUnit = is_array($post) ? $post['kode_jenjang'] : $post->kode_jenjang;
            if (!empty($postUnit) && $postUnit !== $sessionJenjang) {
                return redirect()->to(base_url('app/cms/pengumuman'))->with('error', 'Anda tidak memiliki akses mengubah pengumuman unit lain.');
            }
        }

        $data = [
            'title'      => 'Edit Pengumuman', 
            'post'       => (object) $post,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('cms/pengumuman/form', $data);
    }

    /**
     * Memproses penyimpanan data (Insert/Update).
     */
    public function save()
    {
        $id = $this->request->getPost('id');
        $judul = $this->request->getPost('judul');
        
        // Logika Penentuan Unit
        $sessionJenjang = session('kode_jenjang');
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $isGlobal       = $this->isGlobalAccess($sessionJenjang);
        $finalJenjang   = $isGlobal ? $inputJenjang : $sessionJenjang;
        
        if (empty($finalJenjang) || strtoupper($finalJenjang) === 'GLOBAL') {
            $finalJenjang = null;
        }

        $data = [
            'kode_jenjang'     => $finalJenjang,
            'judul'            => $judul,
            'slug'             => url_title($judul, '-', true),
            'konten'           => $this->request->getPost('konten'),
            'status'           => $this->request->getPost('status') ?? 'published',
            'tanggal_berakhir' => $this->request->getPost('tanggal_berakhir') ?: null,
            'id_penulis'       => session()->get('id') ?? 1,
        ];

        try {
            if ($id) {
                $this->pengumumanModel->update($id, $data);
                $msg = 'Pengumuman berhasil diperbarui.';
            } else {
                $this->pengumumanModel->insert($data);
                $msg = 'Pengumuman berhasil diterbitkan.';
            }

            return redirect()->to(base_url('app/cms/pengumuman'))->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus pengumuman.
     */
    public function delete($id)
    {
        $post = $this->pengumumanModel->find($id);
        if ($post) {
            // Cek Hak Akses Hapus
            $sessionJenjang = session('kode_jenjang');
            if (!$this->isGlobalAccess($sessionJenjang)) {
                $postUnit = is_array($post) ? $post['kode_jenjang'] : $post->kode_jenjang;
                if (!empty($postUnit) && $postUnit !== $sessionJenjang) {
                    return redirect()->to(base_url('app/cms/pengumuman'))->with('error', 'Akses ditolak.');
                }
            }

            $this->pengumumanModel->delete($id);
            return redirect()->to(base_url('app/cms/pengumuman'))->with('success', 'Pengumuman berhasil dihapus.');
        }

        return redirect()->to(base_url('app/cms/pengumuman'))->with('error', 'Data tidak ditemukan.');
    }
}