<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\AsetPengadaanModel;
use App\Models\Sapras\AsetKategoriModel;
use Throwable;

class PengadaanAset extends BaseController
{
    protected $model;
    protected $kategoriModel;
    protected $db;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL'];

    public function __construct()
    {
        $this->model         = new AsetPengadaanModel();
        $this->kategoriModel = new AsetKategoriModel();
        $this->db            = \Config\Database::connect();
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
        } catch (Throwable $e) { } 
        
        if (empty($daftarUnit)) {
            $daftarUnit = ['TK' => 'TK PRATAMA', 'SD' => 'SD PRATAMA', 'SMP' => 'SMP PRATAMA', 'SMA' => 'SMA PRATAMA'];
        }
        
        return $daftarUnit;
    }

    /**
     * Helper: Ambil daftar pegawai (untuk dropdown Pemohon)
     */
    private function getDaftarPegawai($kodeJenjang)
    {
        $builder = $this->db->table('pegawai')
                            ->select('id, nama_lengkap, jenis_ptk as jabatan_fungsional')
                            ->where('status_aktif', 'aktif');
                            
        if (!in_array(strtoupper($kodeJenjang), $this->globalIdentifiers)) {
            $builder->where('kode_jenjang', $kodeJenjang);
        }
        
        return $builder->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();
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

        // --- FIX PAGINATION: Paginasi Manual untuk Query Builder ---
        $builder = $this->model->getPengadaanBuilder($scopeQuery);
        
        $perPage = 10;
        $page    = (int)($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        $pengadaanData = $builder->orderBy('aset_pengadaan.created_at', 'DESC')
                                 ->limit($perPage, $offset)
                                 ->get()
                                 ->getResultArray();

        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $total, 0);

        $data = [
            'title'          => 'Manajemen Pengadaan Aset',
            'pengadaan'      => $pengadaanData,
            'pager'          => $pager,
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $scopeQuery,
            'daftarUnit'     => $daftarUnit
        ];
        
        return view('sapras/pengadaan/index', $data);
    }

    public function new()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL'); 
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);
        $filterJenjang = $this->request->getGet('jenjang') ?? ($isGlobal ? 'GLOBAL' : $sessionJenjang);

        // Ambil Data Relasi
        $kategoriList = $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll();
        $pegawaiList  = $this->getDaftarPegawai($filterJenjang);

        $data = [
            'title'         => 'Pengajuan Pengadaan Baru', 
            'pengadaan'     => null,
            'daftarUnit'    => $this->getDaftarUnit(),
            'kategoriList'  => $kategoriList,
            'pegawaiList'   => $pegawaiList,
            'filterJenjang' => $filterJenjang,
            'isGlobal'      => $isGlobal
        ];
        return view('sapras/pengadaan/form', $data);
    }

    public function edit($id)
    {
        $pengadaan = $this->model->find($id);
        if (!$pengadaan) {
            return redirect()->to(base_url('app/sapras/pengadaan'))->with('error', 'Data pengajuan pengadaan tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // Proteksi: Admin Unit tidak boleh mengedit Pengadaan unit lain
        if (!$isGlobal && strtoupper($pengadaan['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/pengadaan'))->with('error', 'Akses Ditolak. Pengajuan ini milik unit lain.');
        }

        $kategoriList = $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll();
        $pegawaiList  = $this->getDaftarPegawai($pengadaan['kode_jenjang']);

        $data = [
            'title'         => 'Edit/Review Pengajuan Aset', 
            'pengadaan'     => (object) $pengadaan, 
            'daftarUnit'    => $this->getDaftarUnit(),
            'kategoriList'  => $kategoriList,
            'pegawaiList'   => $pegawaiList,
            'filterJenjang' => $pengadaan['kode_jenjang'],
            'isGlobal'      => $isGlobal
        ];
        return view('sapras/pengadaan/form', $data);
    }

    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');
        
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);

        $finalJenjang = $isGlobal ? ($inputJenjang ?: 'GLOBAL') : $sessionJenjang;

        // Bersihkan format mata uang (Misal: 15.000.000 menjadi 15000000)
        $estimasiBiaya = preg_replace('/[^0-9]/', '', $this->request->getPost('estimasi_biaya') ?: '0');

        $data = [
            'kode_jenjang'     => $finalJenjang,
            'judul_pengajuan'  => $this->request->getPost('judul_pengajuan'),
            'id_kategori'      => $this->request->getPost('id_kategori'),
            'jumlah_diminta'   => $this->request->getPost('jumlah_diminta'),
            'estimasi_biaya'   => $estimasiBiaya,
            'alasan_kebutuhan' => $this->request->getPost('alasan_kebutuhan'),
            'id_pemohon'       => $this->request->getPost('id_pemohon'),
            'status'           => $this->request->getPost('status') ?: 'Draft',
            'catatan_reviewer' => $this->request->getPost('catatan_reviewer'),
        ];

        if ($id) {
            $data['id'] = $id;
            // Pertahankan nomor pengajuan yang sudah ada
            $existing = $this->model->find($id);
            $data['no_pengajuan'] = $this->request->getPost('no_pengajuan') ?: ($existing['no_pengajuan'] ?? '');
        } else {
            // Auto-Generate Nomor Pengajuan Baru (Contoh: REQ-202603-ABCD)
            $randomString = strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4));
            $data['no_pengajuan'] = 'REQ-' . date('Ym') . '-' . $randomString;
        }

        if (!$this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        $pesan = $id ? 'Pengajuan pengadaan berhasil diperbarui.' : 'Pengajuan pengadaan aset baru berhasil dibuat.';
        return redirect()->to(base_url('app/sapras/pengadaan'))->with('success', $pesan);
    }

    public function delete($id)
    {
        $pengadaan = $this->model->find($id);
        
        if (!$id || !$pengadaan) {
            return redirect()->to(base_url('app/sapras/pengadaan'))->with('error', 'Data pengajuan tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        if (!$isGlobal && strtoupper($pengadaan['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/pengadaan'))->with('error', 'Akses Ditolak.');
        }

        // Opsional: Cegah penghapusan jika status sudah disetujui/dibeli (Hanya boleh hapus Draft / Ditolak)
        if (in_array($pengadaan['status'], ['Disetujui', 'Selesai/Dibeli'])) {
            return redirect()->to(base_url('app/sapras/pengadaan'))->with('error', 'Gagal dihapus! Pengajuan yang sudah disetujui atau dibeli tidak dapat dihapus. Silakan arsipkan saja.');
        }

        $this->model->delete($id);
        
        return redirect()->to(base_url('app/sapras/pengadaan'))->with('success', 'Data pengajuan berhasil dihapus.');
    }
}