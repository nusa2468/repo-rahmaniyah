<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\AsetLokasiModel;
use Throwable;

class LokasiAset extends BaseController
{
    protected $model;
    protected $db;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL'];

    public function __construct()
    {
        $this->model = new AsetLokasiModel();
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
            'title'          => 'Manajemen Lokasi & Ruangan',
            'lokasi'         => $this->model->getPaginated($scopeQuery, 10),
            'pager'          => $this->model->pager,
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $scopeQuery,
            'daftarUnit'     => $daftarUnit
        ];
        
        return view('sapras/lokasi/index', $data);
    }

    public function new()
    {
        $data = [
            'title'      => 'Tambah Lokasi Aset', 
            'lokasi'     => null,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('sapras/lokasi/form', $data);
    }

    public function edit($id)
    {
        $lokasi = $this->model->find($id);
        if (!$lokasi) {
            return redirect()->to(base_url('app/sapras/lokasi'))->with('error', 'Data lokasi/ruangan tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // Proteksi: Admin Unit tidak boleh mengedit Ruangan milik unit lain
        if (!$isGlobal && strtoupper($lokasi['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/lokasi'))->with('error', 'Akses Ditolak. Lokasi ini milik unit lain.');
        }

        $data = [
            'title'      => 'Edit Lokasi Aset', 
            'lokasi'     => (object) $lokasi, 
            'daftarUnit' => $this->getDaftarUnit() 
        ];
        return view('sapras/lokasi/form', $data);
    }

    public function save($id = null)
    {
        // 1. Dapatkan ID
        $id = $id ?? $this->request->getPost('id');
        
        // 2. Proteksi & Penentuan Jenjang
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);

        // Jika bukan global, paksakan kode_jenjang menggunakan session milik user tersebut
        $finalJenjang = $isGlobal ? ($inputJenjang ?: 'GLOBAL') : $sessionJenjang;

        // 3. Susun Data (Fields diizinkan oleh AsetLokasiModel)
        $data = [
            'kode_jenjang' => $finalJenjang,
            'jenis_lokasi' => $this->request->getPost('jenis_lokasi'),
            'nama_lokasi'  => $this->request->getPost('nama_lokasi'),
            'kapasitas'    => $this->request->getPost('kapasitas') ?: 0,
            'keterangan'   => $this->request->getPost('keterangan'),
        ];

        if ($id) {
            $data['id'] = $id;
        }

        // 4. Proses Simpan (Akan divalidasi otomatis oleh Model)
        if (!$this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        $pesan = $id ? 'Data lokasi berhasil diperbarui.' : 'Data lokasi/ruangan baru berhasil ditambahkan.';
        return redirect()->to(base_url('app/sapras/lokasi'))->with('success', $pesan);
    }

    public function delete($id)
    {
        $lokasi = $this->model->find($id);
        
        if (!$id || !$lokasi) {
            return redirect()->to(base_url('app/sapras/lokasi'))->with('error', 'Data lokasi tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // Proteksi Hapus Lintas Unit
        if (!$isGlobal && strtoupper($lokasi['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/lokasi'))->with('error', 'Akses Ditolak.');
        }

        // PERHATIAN: Pastikan tidak ada aset barang di dalam ruangan ini sebelum dihapus
        $barangTerkait = $this->db->table('aset_barang')
                                  ->where('id_lokasi', $id)
                                  ->where('deleted_at', null)
                                  ->countAllResults();
                                  
        if ($barangTerkait > 0) {
            return redirect()->to(base_url('app/sapras/lokasi'))
                             ->with('error', "Gagal dihapus! Terdapat $barangTerkait item aset yang tercatat masih berada di dalam lokasi ini. Pindahkan aset terlebih dahulu.");
        }

        // Eksekusi Hapus (Soft Delete aktif dari model)
        $this->model->delete($id);
        
        return redirect()->to(base_url('app/sapras/lokasi'))->with('success', 'Lokasi/Ruangan berhasil dihapus.');
    }
}