<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\AsetKategoriModel;
use Throwable;

class KategoriAset extends BaseController
{
    protected $model;
    protected $db;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL'];

    public function __construct()
    {
        $this->model = new AsetKategoriModel();
        $this->db    = \Config\Database::connect();
    }

    /**
     * Helper: Ambil Daftar Unit secara dinamis dari Database Jenjang
     */
    private function getDaftarUnit()
    {
        $daftarUnit = [];
        try {
            if ($this->db->tableExists('jenjang_sekolah')) {
                $query = $this->db->table('jenjang_sekolah')
                                  ->whereNotIn('kode_jenjang', $this->globalIdentifiers)
                                  ->where('status', 'aktif')
                                  ->orderBy('urutan', 'ASC')
                                  ->get();
                                  
                foreach ($query->getResultArray() as $row) {
                    $val = strtoupper($row['kode_jenjang']);
                    $label = $row['nama_jenjang'] ?? "UNIT $val";
                    $daftarUnit[$val] = $label;
                }
            }
        } catch (Throwable $e) { } // Silent fail jika tabel belum siap
        
        // Fallback jika DB kosong agar dropdown form tidak kosong melompong
        if (empty($daftarUnit)) {
            $daftarUnit = ['TK' => 'TK PRATAMA', 'SD' => 'SD PRATAMA', 'SMP' => 'SMP PRATAMA', 'SMA' => 'SMA PRATAMA'];
        }
        
        return $daftarUnit;
    }

    public function index()
    {
        $daftarUnit = $this->getDaftarUnit();
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL'); 
        
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        $filterJenjang = $this->request->getGet('jenjang');
        
        // Penentuan Scope Query berdasarkan hak akses user
        if ($isGlobal) {
            $scopeQuery = (!empty($filterJenjang) && !in_array(strtoupper($filterJenjang), $this->globalIdentifiers)) 
                          ? $filterJenjang 
                          : 'GLOBAL';
        } else {
            $scopeQuery = $sessionJenjang;
        }

        $data = [
            'title'          => 'Manajemen Kategori Aset',
            'kategori'       => $this->model->getPaginated($scopeQuery, 10),
            'pager'          => $this->model->pager,
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $scopeQuery,
            'daftarUnit'     => $daftarUnit
        ];
        
        return view('sapras/kategori/index', $data);
    }

    public function new()
    {
        $data = [
            'title'      => 'Tambah Kategori Aset', 
            'kategori'   => null,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('sapras/kategori/form', $data);
    }

    public function edit($id)
    {
        $kategori = $this->model->find($id);
        if (!$kategori) {
            return redirect()->to(base_url('app/sapras/kategori'))->with('error', 'Data kategori aset tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // Proteksi: Admin Unit tidak boleh mengedit Kategori milik unit lain
        if (!$isGlobal && strtoupper($kategori['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/kategori'))->with('error', 'Akses Ditolak. Kategori ini milik unit lain.');
        }

        $data = [
            'title'      => 'Edit Kategori Aset', 
            'kategori'   => (object) $kategori, 
            'daftarUnit' => $this->getDaftarUnit() 
        ];
        return view('sapras/kategori/form', $data);
    }

    public function save($id = null)
    {
        // 1. Dapatkan ID (baik dari parameter routing atau input hidden form)
        $id = $id ?? $this->request->getPost('id');
        
        // 2. Proteksi & Penentuan Jenjang
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);

        // Jika bukan global, paksakan kode_jenjang menggunakan session milik user tersebut
        $finalJenjang = $isGlobal ? ($inputJenjang ?: 'GLOBAL') : $sessionJenjang;

        // 3. Susun Data (Fields diizinkan oleh AsetKategoriModel)
        $data = [
            'kode_jenjang'  => $finalJenjang,
            'kode_kategori' => strtoupper($this->request->getPost('kode_kategori')),
            'nama_kategori' => $this->request->getPost('nama_kategori'),
            'tipe_aset'     => $this->request->getPost('tipe_aset'),
        ];

        if ($id) {
            $data['id'] = $id;
        }

        // 4. Proses Simpan (Akan divalidasi otomatis oleh Model)
        if (!$this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        $pesan = $id ? 'Data kategori berhasil diperbarui.' : 'Data kategori berhasil ditambahkan.';
        return redirect()->to(base_url('app/sapras/kategori'))->with('success', $pesan);
    }

    public function delete($id)
    {
        $kategori = $this->model->find($id);
        
        if (!$id || !$kategori) {
            return redirect()->to(base_url('app/sapras/kategori'))->with('error', 'Data tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // Proteksi Hapus Lintas Unit
        if (!$isGlobal && strtoupper($kategori['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/kategori'))->with('error', 'Akses Ditolak.');
        }

        // PERHATIAN: Pastikan tidak ada aset yang masih menggunakan kategori ini sebelum dihapus
        // Opsional: Cek relasi ke tabel aset_barang (jika diperlukan)
        $barangTerkait = $this->db->table('aset_barang')->where('id_kategori', $id)->countAllResults();
        if ($barangTerkait > 0) {
            return redirect()->to(base_url('app/sapras/kategori'))->with('error', "Gagal dihapus! Terdapat $barangTerkait aset barang yang menggunakan kategori ini.");
        }

        // Eksekusi Hapus Permanen (Sesuai setting AsetKategoriModel useSoftDeletes = false)
        $this->model->delete($id);
        
        return redirect()->to(base_url('app/sapras/kategori'))->with('success', 'Kategori aset berhasil dihapus permanen.');
    }
}