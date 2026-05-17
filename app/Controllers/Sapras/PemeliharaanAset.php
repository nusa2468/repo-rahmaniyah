<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\AsetPemeliharaanModel;
use App\Models\Sapras\AsetBarangModel;
use Throwable;

class PemeliharaanAset extends BaseController
{
    protected $model;
    protected $barangModel;
    protected $db;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL'];

    public function __construct()
    {
        $this->model       = new AsetPemeliharaanModel();
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
        $builder = $this->model->getPemeliharaanBuilder();

        if ($scopeQuery !== 'GLOBAL') {
            $builder->where('aset_barang.kode_jenjang', $scopeQuery);
        }

        // --- FIX PAGINATION ---
        $perPage = 10;
        $page    = (int)($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        $pemeliharaanData = $builder->orderBy('aset_pemeliharaan.created_at', 'DESC')
                                    ->limit($perPage, $offset)
                                    ->get()
                                    ->getResultArray();

        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $total, 0);

        $data = [
            'title'          => 'Manajemen Pemeliharaan & Servis',
            'pemeliharaan'   => $pemeliharaanData,
            'pager'          => $pager,
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $scopeQuery,
            'daftarUnit'     => $daftarUnit
        ];
        
        return view('sapras/pemeliharaan/index', $data);
    }

    public function new()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL'); 
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);
        $filterJenjang = $this->request->getGet('jenjang') ?? ($isGlobal ? 'GLOBAL' : $sessionJenjang);

        // Ambil Daftar Aset (Semua barang bisa dipelihara/servis kecuali yang sudah afkir)
        $barangQuery = $this->barangModel->where('kondisi !=', 'Afkir/Dihapus');
        if ($filterJenjang !== 'GLOBAL') {
            $barangQuery->where('kode_jenjang', $filterJenjang);
        }
        $barangList = $barangQuery->orderBy('nama_aset', 'ASC')->findAll();

        $data = [
            'title'         => 'Registrasi Pemeliharaan Baru', 
            'pemeliharaan'  => null,
            'daftarUnit'    => $this->getDaftarUnit(),
            'barangList'    => $barangList,
            'filterJenjang' => $filterJenjang,
            'isGlobal'      => $isGlobal
        ];
        return view('sapras/pemeliharaan/form', $data);
    }

    public function edit($id)
    {
        $pemeliharaan = $this->model->find($id);
        if (!$pemeliharaan) {
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('error', 'Data pemeliharaan tidak ditemukan.');
        }

        // Cari detail barang untuk proteksi jenjang
        $barang = $this->barangModel->find($pemeliharaan['id_aset']);
        $kodeJenjangBarang = $barang ? strtoupper($barang['kode_jenjang']) : 'GLOBAL';

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // Proteksi Lintas Unit
        if (!$isGlobal && $kodeJenjangBarang !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('error', 'Akses Ditolak. Transaksi ini milik unit lain.');
        }

        $barangQuery = $this->barangModel->where('kondisi !=', 'Afkir/Dihapus');
        if ($kodeJenjangBarang !== 'GLOBAL') {
            $barangQuery->where('kode_jenjang', $kodeJenjangBarang);
        }
        $barangList = $barangQuery->orderBy('nama_aset', 'ASC')->findAll();

        $data = [
            'title'         => 'Update Status Pemeliharaan', 
            'pemeliharaan'  => (object) $pemeliharaan, 
            'daftarUnit'    => $this->getDaftarUnit(),
            'barangList'    => $barangList,
            'filterJenjang' => $kodeJenjangBarang,
            'isGlobal'      => $isGlobal
        ];
        return view('sapras/pemeliharaan/form', $data);
    }

    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');
        $idAset = $this->request->getPost('id_aset');
        
        $statusBaru = $this->request->getPost('status') ?: 'Direncanakan';
        $jenisPemeliharaan = $this->request->getPost('jenis_pemeliharaan');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');

        // Pembersihan format mata uang (Rp 350.000 -> 350000)
        $biayaServis = preg_replace('/[^0-9]/', '', $this->request->getPost('biaya') ?: '0');

        // Jika statusnya selesai tapi tanggal selesai kosong, isi otomatis dengan hari ini
        if ($statusBaru === 'Selesai' && empty($tanggalSelesai)) {
            $tanggalSelesai = date('Y-m-d');
        }

        $data = [
            'id_aset'            => $idAset,
            'jenis_pemeliharaan' => $jenisPemeliharaan,
            'tanggal_mulai'      => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai'    => $tanggalSelesai ?: null,
            'pelaksana'          => $this->request->getPost('pelaksana'),
            'biaya'              => $biayaServis,
            'keterangan'         => $this->request->getPost('keterangan'),
            'status'             => $statusBaru,
        ];

        if ($id) {
            $data['id'] = $id;
        }

        // =====================================================================
        // TRANSACTION: Simpan Pemeliharaan + Sinkronisasi Kondisi Aset
        // =====================================================================
        $this->db->transBegin();
        try {
            if (!$this->model->save($data)) {
                $this->db->transRollback();
                return redirect()->back()->withInput()->with('errors', $this->model->errors());
            }

            // --- AUTO-SYNC LOGIC ---
            // Jika ini perbaikan kerusakan dan sedang diproses, status aset berubah menjadi 'Diperbaiki'
            if ($jenisPemeliharaan === 'Perbaikan/Kerusakan' && $statusBaru === 'Sedang Proses') {
                $this->barangModel->update($idAset, ['status_ketersediaan' => 'Diperbaiki']);
            } 
            // Jika sudah selesai (baik preventif maupun perbaikan), kembalikan ketersediaan aset menjadi 'Tersedia'
            elseif ($statusBaru === 'Selesai' || $statusBaru === 'Batal') {
                $updateAset = ['status_ketersediaan' => 'Tersedia'];
                
                // Jika jenisnya perbaikan dan selesai, asumsikan kondisinya kembali 'Baik'
                if ($statusBaru === 'Selesai' && $jenisPemeliharaan === 'Perbaikan/Kerusakan') {
                    $updateAset['kondisi'] = 'Baik';
                }
                
                $this->barangModel->update($idAset, $updateAset);
            }

            $this->db->transCommit();
            $pesan = $id ? 'Status pemeliharaan/servis berhasil diperbarui.' : 'Data pemeliharaan/servis baru berhasil dicatat.';
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('success', $pesan);

        } catch (Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $pemeliharaan = $this->model->find($id);
        
        if (!$id || !$pemeliharaan) {
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('error', 'Data pemeliharaan tidak ditemukan.');
        }

        $barang = $this->barangModel->find($pemeliharaan['id_aset']);
        $kodeJenjangBarang = $barang ? strtoupper($barang['kode_jenjang']) : 'GLOBAL';

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        if (!$isGlobal && $kodeJenjangBarang !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('error', 'Akses Ditolak.');
        }

        $this->db->transBegin();
        try {
            // Hapus log pemeliharaan
            $this->model->delete($id);

            // AUTO-RELEASE: Jika perbaikan sedang diproses lalu dihapus, kembalikan ketersediaan barang jadi Tersedia
            if ($pemeliharaan['status'] === 'Sedang Proses' && $pemeliharaan['jenis_pemeliharaan'] === 'Perbaikan/Kerusakan' && $barang) {
                $this->barangModel->update($pemeliharaan['id_aset'], ['status_ketersediaan' => 'Tersedia']);
            }

            $this->db->transCommit();
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('success', 'Data log pemeliharaan berhasil dihapus.');
        } catch (Throwable $e) {
            $this->db->transRollback();
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('error', 'Gagal menghapus log pemeliharaan.');
        }
    }

    /**
     * Fitur Cetak Label Kontrol Pemeliharaan (Stiker)
     */
    public function printLabel($id)
    {
        $pemeliharaan = $this->model->getPemeliharaanBuilder()->where('aset_pemeliharaan.id', $id)->get()->getRowArray();
        
        if (!$pemeliharaan) {
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('error', 'Data pemeliharaan tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        if (!$isGlobal && strtoupper($pemeliharaan['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/pemeliharaan'))->with('error', 'Akses Ditolak.');
        }
        
        // AMBIL DATA YAYASAN DARI SETTINGS MODEL
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getSettingsAsArray('Global');

        return view('sapras/pemeliharaan/print_label', [
            'title'        => 'Cetak Label Kontrol Servis',
            'pemeliharaan' => $pemeliharaan,
            'nama_yayasan' => $settings['nama_yayasan'] ?? 'YAYASAN PENDIDIKAN'
        ]);
    }

    /**
     * Fitur Cetak Laporan Pemeliharaan (Kertas A4 Landscape)
     */
    public function printReport()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL'); 
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);
        
        $filterJenjang = $this->request->getGet('jenjang');

        if ($isGlobal) {
            $scopeQuery = (!empty($filterJenjang) && !in_array(strtoupper($filterJenjang), $this->globalIdentifiers)) ? $filterJenjang : 'GLOBAL';
        } else {
            $scopeQuery = $sessionJenjang;
        }

        $builder = $this->model->getPemeliharaanBuilder();
        if ($scopeQuery !== 'GLOBAL') {
            $builder->where('aset_barang.kode_jenjang', $scopeQuery);
        }

        $pemeliharaanData = $builder->orderBy('aset_pemeliharaan.tanggal_mulai', 'DESC')->get()->getResultArray();

        // AMBIL DATA YAYASAN DARI SETTINGS MODEL
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getSettingsAsArray('Global');

        return view('sapras/pemeliharaan/print_report', [
            'title'         => 'Laporan Pemeliharaan & Servis Aset',
            'pemeliharaan'  => $pemeliharaanData,
            'filterJenjang' => $scopeQuery,
            'tanggalCetak'  => date('d F Y H:i'),
            'nama_yayasan'  => $settings['nama_yayasan'] ?? 'YAYASAN PENDIDIKAN'
        ]);
    }

    /**
     * Fitur Cetak Kartu Riwayat Pemeliharaan per Item Aset (Maintenance Card)
     */
    public function printRiwayat($id_aset)
    {
        // Ambil Data Barang secara detail
        $barang = $this->barangModel->getBarangBuilder()->where('aset_barang.id', $id_aset)->get()->getRowArray();
        
        if (!$barang) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Data aset tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // Proteksi Lintas Unit
        if (!$isGlobal && strtoupper($barang['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Akses Ditolak.');
        }

        // Ambil Riwayat Servis
        $riwayat = $this->model->getPemeliharaanBuilder()
                               ->where('aset_pemeliharaan.id_aset', $id_aset)
                               ->orderBy('tanggal_mulai', 'DESC')
                               ->get()
                               ->getResultArray();

        // AMBIL DATA YAYASAN DARI SETTINGS MODEL
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getSettingsAsArray('Global');

        return view('sapras/pemeliharaan/print_riwayat', [
            'title'        => 'Kartu Riwayat Servis Aset',
            'barang'       => $barang,
            'riwayat'      => $riwayat,
            'tanggalCetak' => date('d F Y H:i'),
            'nama_yayasan' => $settings['nama_yayasan'] ?? 'YAYASAN PENDIDIKAN'
        ]);
    }
}