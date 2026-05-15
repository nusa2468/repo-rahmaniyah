<?php

namespace App\Controllers\Cms;

use App\Controllers\BaseController;
use App\Models\Cms\BeritaModel; // Pastikan Model ini ada, atau sesuaikan namespace

/**
 * Controller Berita
 * Menangani CRUD Berita dengan sistem Multi-Unit (Dynamic Scoping).
 */
class Berita extends BaseController
{
    protected $beritaModel;
    protected $db;

    public function __construct()
    {
        // Jika belum ada class BeritaModel, Anda bisa menggunakan builder langsung atau membuat modelnya
        // Disini kita asumsikan Model sudah dibuat standard CI4
        $this->beritaModel = new BeritaModel(); 
        $this->db          = \Config\Database::connect();
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
     * Menampilkan daftar berita
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
        // Jika Admin Unit, paksa filter ke sesinya. Jika Superadmin, gunakan filter URL atau tampilkan semua.
        $scopeQuery = $isGlobal ? $filterJenjang : $sessionJenjang;

        // Terapkan Filter pada Model
        if (!empty($scopeQuery)) {
            $this->beritaModel->where('kode_jenjang', $scopeQuery);
        }

        // Ambil Data (Pagination)
        $berita = $this->beritaModel->orderBy('created_at', 'DESC')->paginate(10);

        $data = [
            'title'          => 'Kelola Berita Sekolah',
            'berita'         => $berita,
            'pager'          => $this->beritaModel->pager,
            
            // UI Helpers
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $daftarUnit
        ];

        return view('cms/berita/index', $data);
    }

    /**
     * Form tambah berita baru.
     */
    public function new()
    {
        $data = [
            'title'      => 'Tulis Berita Baru', 
            'berita'     => null,
            'daftarUnit' => $this->getDaftarUnit() // Kirim unit ke form
        ];
        return view('cms/berita/form', $data);
    }

    /**
     * Form edit berita.
     */
    public function edit($id)
    {
        $berita = $this->beritaModel->find($id);
        if (!$berita) {
            return redirect()->to(base_url('app/cms/berita'))->with('error', 'Data tidak ditemukan');
        }

        // Cek Hak Akses Edit (Admin Unit tidak boleh edit berita unit lain)
        $sessionJenjang = session('kode_jenjang');
        if (!$this->isGlobalAccess($sessionJenjang)) {
            // Jika berita punya unit dan unitnya beda dengan user, tolak
            if (!empty($berita['kode_jenjang']) && $berita['kode_jenjang'] !== $sessionJenjang) {
                return redirect()->to(base_url('app/cms/berita'))->with('error', 'Anda tidak memiliki akses mengubah berita unit lain.');
            }
        }

        $data = [
            'title'      => 'Edit Berita', 
            'berita'     => (object) $berita,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('cms/berita/form', $data);
    }

    /**
     * Proses Simpan (Insert/Update).
     */
    public function save()
    {
        $id = $this->request->getPost('id');
        $judul = $this->request->getPost('judul');

        // Logika Penentuan Unit (Kode Jenjang)
        $sessionJenjang = session('kode_jenjang');
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $isGlobal       = $this->isGlobalAccess($sessionJenjang);

        // Jika Global, ambil dari input form. Jika Admin Unit, paksa dari session.
        $finalJenjang = $isGlobal ? $inputJenjang : $sessionJenjang;
        
        // Pastikan 'Global' disimpan sebagai NULL agar konsisten
        if (empty($finalJenjang) || strtoupper($finalJenjang) === 'GLOBAL') {
            $finalJenjang = null;
        }

        $data = [
            'kode_jenjang' => $finalJenjang,
            'judul'        => $judul,
            'slug'         => url_title($judul, '-', true),
            'konten'       => $this->request->getPost('konten'),
            'status'       => $this->request->getPost('status') ?? 'published',
            'id_penulis'   => session()->get('id') ?? 1, // Sesuaikan dengan key session user ID Anda
        ];

        // Handle Upload Gambar
        $file = $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Hapus gambar lama jika proses Edit
            if ($id) {
                $oldData = $this->beritaModel->find($id);
                // Cek array access vs object access
                $oldImg = is_array($oldData) ? ($oldData['gambar'] ?? null) : ($oldData->gambar ?? null);
                
                if ($oldImg && file_exists('uploads/berita/' . $oldImg)) {
                    unlink('uploads/berita/' . $oldImg);
                }
            }

            $newName = $file->getRandomName();
            $file->move('uploads/berita', $newName);
            $data['gambar'] = $newName;
        }

        try {
            if ($id) {
                $this->beritaModel->update($id, $data);
                $msg = 'Berita berhasil diperbarui.';
            } else {
                $this->beritaModel->insert($data);
                $msg = 'Berita berhasil diterbitkan.';
            }
            return redirect()->to(base_url('app/cms/berita'))->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan berita: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus berita dan file gambarnya.
     */
    public function delete($id)
    {
        $berita = $this->beritaModel->find($id);
        
        if ($berita) {
            // Cek Hak Akses Hapus
            $sessionJenjang = session('kode_jenjang');
            if (!$this->isGlobalAccess($sessionJenjang)) {
                $newsUnit = is_array($berita) ? $berita['kode_jenjang'] : $berita->kode_jenjang;
                if (!empty($newsUnit) && $newsUnit !== $sessionJenjang) {
                    return redirect()->to(base_url('app/cms/berita'))->with('error', 'Akses ditolak.');
                }
            }

            // Hapus file fisik gambar jika ada
            $imgName = is_array($berita) ? ($berita['gambar'] ?? null) : ($berita->gambar ?? null);
            if ($imgName && file_exists('uploads/berita/' . $imgName)) {
                unlink('uploads/berita/' . $imgName);
            }
            
            $this->beritaModel->delete($id);
            return redirect()->to(base_url('app/cms/berita'))->with('success', 'Berita berhasil dihapus.');
        }

        return redirect()->to(base_url('app/cms/berita'))->with('error', 'Data tidak ditemukan.');
    }
}