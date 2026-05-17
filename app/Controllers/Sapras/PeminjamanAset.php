<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\AsetPeminjamanModel;
use App\Models\Sapras\AsetBarangModel;
use Throwable;

class PeminjamanAset extends BaseController
{
    protected $model;
    protected $barangModel;
    protected $db;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL'];

    public function __construct()
    {
        $this->model       = new AsetPeminjamanModel();
        $this->barangModel = new AsetBarangModel();
        $this->db          = \Config\Database::connect();
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
     * Helper: Ambil daftar Pegawai (Guru/Staff)
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

    /**
     * Helper: Ambil daftar Siswa (Jika siswa diizinkan meminjam barang lab/perpus)
     */
    private function getDaftarSiswa($kodeJenjang)
    {
        $builder = $this->db->table('siswa')
                            ->select('id, nama_lengkap, nis');
                            
        if (!in_array(strtoupper($kodeJenjang), $this->globalIdentifiers)) {
            $builder->where('kode_jenjang', $kodeJenjang);
        }
        // Pastikan hanya siswa yang statusnya aktif yang boleh pinjam
        return $builder->where('status', 'Aktif')->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();
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

        // Fat Model Builder
        $builder = $this->model->getPeminjamanBuilder();

        if ($scopeQuery !== 'GLOBAL') {
            $builder->where('aset_barang.kode_jenjang', $scopeQuery);
        }

        // --- FIX PAGINATION ---
        $perPage = 10;
        $page    = (int)($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        $peminjamanData = $builder->orderBy('aset_peminjaman.created_at', 'DESC')
                                  ->limit($perPage, $offset)
                                  ->get()
                                  ->getResultArray();

        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $total, 0);

        $data = [
            'title'          => 'Logistik & Peminjaman Aset',
            'peminjaman'     => $peminjamanData,
            'pager'          => $pager,
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $scopeQuery,
            'daftarUnit'     => $daftarUnit
        ];
        
        return view('sapras/peminjaman/index', $data);
    }

    public function new()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL'); 
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);
        $filterJenjang = $this->request->getGet('jenjang') ?? ($isGlobal ? 'GLOBAL' : $sessionJenjang);

        // Ambil Daftar Aset yang "Tersedia" saja (Barang yg dipinjam/rusak tidak akan muncul di form)
        $barangQuery = $this->barangModel->where('status_ketersediaan', 'Tersedia');
        if ($filterJenjang !== 'GLOBAL') {
            $barangQuery->where('kode_jenjang', $filterJenjang);
        }
        $barangList = $barangQuery->orderBy('nama_aset', 'ASC')->findAll();

        $data = [
            'title'         => 'Registrasi Peminjaman Baru', 
            'peminjaman'    => null,
            'daftarUnit'    => $this->getDaftarUnit(),
            'barangList'    => $barangList,
            'pegawaiList'   => $this->getDaftarPegawai($filterJenjang),
            'siswaList'     => $this->getDaftarSiswa($filterJenjang),
            'filterJenjang' => $filterJenjang,
            'isGlobal'      => $isGlobal
        ];
        return view('sapras/peminjaman/form', $data);
    }

    public function edit($id)
    {
        $peminjaman = $this->model->find($id);
        if (!$peminjaman) {
            return redirect()->to(base_url('app/sapras/peminjaman'))->with('error', 'Data peminjaman tidak ditemukan.');
        }

        // Cari detail barang yang dipinjam untuk proteksi jenjang
        $barang = $this->barangModel->find($peminjaman['id_aset']);
        $kodeJenjangBarang = $barang ? strtoupper($barang['kode_jenjang']) : 'GLOBAL';

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // Proteksi: Admin Unit tidak boleh mengedit Peminjaman aset milik unit lain
        if (!$isGlobal && $kodeJenjangBarang !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/peminjaman'))->with('error', 'Akses Ditolak. Transaksi ini milik unit lain.');
        }

        // Ambil Daftar Aset (Sertakan barang yang sedang dipinjam ini agar terpilih di dropdown)
        $barangQuery = $this->barangModel->groupStart()
                                         ->where('status_ketersediaan', 'Tersedia')
                                         ->orWhere('id', $peminjaman['id_aset'])
                                         ->groupEnd();
                                         
        if ($kodeJenjangBarang !== 'GLOBAL') {
            $barangQuery->where('kode_jenjang', $kodeJenjangBarang);
        }
        $barangList = $barangQuery->orderBy('nama_aset', 'ASC')->findAll();

        $data = [
            'title'         => 'Update Status Peminjaman', 
            'peminjaman'    => (object) $peminjaman, 
            'daftarUnit'    => $this->getDaftarUnit(),
            'barangList'    => $barangList,
            'pegawaiList'   => $this->getDaftarPegawai($kodeJenjangBarang),
            'siswaList'     => $this->getDaftarSiswa($kodeJenjangBarang),
            'filterJenjang' => $kodeJenjangBarang,
            'isGlobal'      => $isGlobal
        ];
        return view('sapras/peminjaman/form', $data);
    }

    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');
        $idAset = $this->request->getPost('id_aset');
        $statusBaru = $this->request->getPost('status') ?: 'Menunggu';
        $tanggalKembali = $this->request->getPost('tanggal_kembali');

        // Jika statusnya dikembalikan tapi tanggal kembalinya kosong, auto-isi dengan waktu sekarang
        if ($statusBaru === 'Dikembalikan' && empty($tanggalKembali)) {
            $tanggalKembali = date('Y-m-d H:i:s');
        }

        $data = [
            'id_aset'          => $idAset,
            'tipe_peminjam'    => $this->request->getPost('tipe_peminjam') ?: 'Pegawai',
            'id_peminjam'      => $this->request->getPost('id_peminjam'),
            'tanggal_pinjam'   => $this->request->getPost('tanggal_pinjam'),
            'estimasi_kembali' => $this->request->getPost('estimasi_kembali'),
            'tanggal_kembali'  => $tanggalKembali ?: null,
            'keperluan'        => $this->request->getPost('keperluan'),
            'status'           => $statusBaru,
        ];

        if ($id) {
            $data['id'] = $id;
        }

        // =====================================================================
        // TRANSACTION: Simpan Peminjaman + Sinkronisasi Status Master Aset
        // =====================================================================
        $this->db->transBegin();
        try {
            if (!$this->model->save($data)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('errors', $this->model->errors());
            }

            // SINKRONISASI STATUS BARANG KE KATALOG ASET
            if ($statusBaru === 'Dipinjam' || $statusBaru === 'Terlambat') {
                $this->barangModel->update($idAset, ['status_ketersediaan' => 'Dipinjam']);
            } elseif ($statusBaru === 'Dikembalikan') {
                $this->barangModel->update($idAset, ['status_ketersediaan' => 'Tersedia']);
            }

            $this->db->transCommit();
            $pesan = $id ? 'Status peminjaman berhasil diperbarui.' : 'Peminjaman baru berhasil dicatat.';
            return redirect()->to(base_url('app/sapras/peminjaman'))->with('success', $pesan);

        } catch (Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $peminjaman = $this->model->find($id);
        
        if (!$id || !$peminjaman) {
            return redirect()->to(base_url('app/sapras/peminjaman'))->with('error', 'Data peminjaman tidak ditemukan.');
        }

        $barang = $this->barangModel->find($peminjaman['id_aset']);
        $kodeJenjangBarang = $barang ? strtoupper($barang['kode_jenjang']) : 'GLOBAL';

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        if (!$isGlobal && $kodeJenjangBarang !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/peminjaman'))->with('error', 'Akses Ditolak.');
        }

        $this->db->transBegin();
        try {
            // Hapus log peminjaman
            $this->model->delete($id);

            // AUTO-RELEASE: Jika transaksi dihapus, kembalikan status barang menjadi Tersedia (jika tadinya berstatus dipinjam)
            if (in_array($peminjaman['status'], ['Dipinjam', 'Terlambat']) && $barang) {
                $this->barangModel->update($peminjaman['id_aset'], ['status_ketersediaan' => 'Tersedia']);
            }

            $this->db->transCommit();
            return redirect()->to(base_url('app/sapras/peminjaman'))->with('success', 'Data log peminjaman berhasil dihapus dan status aset dikembalikan (Release).');
        } catch (Throwable $e) {
            $this->db->transRollback();
            return redirect()->to(base_url('app/sapras/peminjaman'))->with('error', 'Gagal menghapus log peminjaman.');
        }
    }
}