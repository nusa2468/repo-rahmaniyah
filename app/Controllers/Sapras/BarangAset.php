<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\AsetBarangModel;
use App\Models\Sapras\AsetKategoriModel;
use App\Models\Sapras\AsetLokasiModel;
use Throwable;

class BarangAset extends BaseController
{
    protected $model;
    protected $kategoriModel;
    protected $lokasiModel;
    protected $db;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL'];

    public function __construct()
    {
        $this->model         = new AsetBarangModel();
        $this->kategoriModel = new AsetKategoriModel();
        $this->lokasiModel   = new AsetLokasiModel();
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
     * Helper: Ambil daftar pegawai untuk dropdown Penanggung Jawab
     */
    private function getDaftarPegawai($kodeJenjang)
    {
        $builder = $this->db->table('pegawai')
                            ->select('id, nama_lengkap, jenis_ptk as jabatan_fungsional')
                            ->where('status_aktif', 'aktif');
                            
        // PROTEKSI: Filter Unit Pegawai
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
        
        // Tangkap Filter
        $filterJenjang = $this->request->getGet('jenjang');
        $filterKategori = $this->request->getGet('kategori');
        $filterLokasi = $this->request->getGet('lokasi');
        $filterKondisi = $this->request->getGet('kondisi');
        $search = $this->request->getGet('search');

        // PROTEKSI 1: Penentuan Scope Jenjang Query (ANTI BOCOR)
        if ($isGlobal) {
            $scopeQuery = (!empty($filterJenjang) && !in_array(strtoupper($filterJenjang), $this->globalIdentifiers)) 
                          ? $filterJenjang 
                          : 'GLOBAL';
        } else {
            // PAKSA GUNAKAN SESSION JIKA BUKAN GLOBAL (Abaikan getGet)
            $scopeQuery = $sessionJenjang;
        }

        // Gunakan Fat Model Builder
        $builder = $this->model->getBarangBuilder($scopeQuery);

        // Apply Filter Lanjutan
        if (!empty($filterKategori)) {
            $builder->where('aset_barang.id_kategori', $filterKategori);
        }
        if (!empty($filterLokasi)) {
            $builder->where('aset_barang.id_lokasi', $filterLokasi);
        }
        if (!empty($filterKondisi)) {
            $builder->where('aset_barang.kondisi', $filterKondisi);
        }
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('aset_barang.nama_aset', $search)
                    ->orLike('aset_barang.kode_aset', $search)
                    ->orLike('aset_barang.merk_spesifikasi', $search)
                    ->groupEnd();
        }

        // Paginasi Custom pada Builder
        $perPage = 15;
        $page = (int)($this->request->getGet('page_aset') ?? 1);
        $offset = ($page - 1) * $perPage;

        // Clone builder untuk menghitung total baris
        $countBuilder = clone $builder;
        $total = $countBuilder->countAllResults();

        // Ambil data untuk halaman ini
        $barangData = $builder->orderBy('aset_barang.created_at', 'DESC')
                              ->limit($perPage, $offset)
                              ->get()
                              ->getResultArray();

        // Buat Pager CI4 Manual
        $pager = \Config\Services::pager();
        $pager->store('aset', $page, $perPage, $total, 0);

        // Data Relasi untuk Dropdown Filter
        $kategoriList = $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll();
        
        $lokasiQuery = $this->lokasiModel;
        // PROTEKSI LOKASI FILTER
        if (!$isGlobal) $lokasiQuery->where('kode_jenjang', $sessionJenjang);
        $lokasiList = $lokasiQuery->orderBy('nama_lokasi', 'ASC')->findAll();

        $data = [
            'title'          => 'Katalog Barang Aset',
            'barang'         => $barangData,
            'pager'          => $pager,
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $scopeQuery,
            'daftarUnit'     => $daftarUnit,
            // Filter Data
            'kategoriList'   => $kategoriList,
            'lokasiList'     => $lokasiList,
            'filterKategori' => $filterKategori,
            'filterLokasi'   => $filterLokasi,
            'filterKondisi'  => $filterKondisi,
            'search'         => $search
        ];
        
        return view('sapras/barang/index', $data);
    }

    public function new()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL'); 
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);
        
        // PROTEKSI 2: Cegah injeksi parameter URL (?jenjang=SMP) oleh Admin non-global
        $filterJenjang = $isGlobal ? ($this->request->getGet('jenjang') ?: 'GLOBAL') : $sessionJenjang;

        // Ambil Data Relasi untuk Dropdown
        $kategoriList = $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll();
        
        $lokasiQuery = $this->lokasiModel;
        if ($filterJenjang !== 'GLOBAL') $lokasiQuery->where('kode_jenjang', $filterJenjang);
        $lokasiList = $lokasiQuery->orderBy('nama_lokasi', 'ASC')->findAll();

        $pegawaiList = $this->getDaftarPegawai($filterJenjang);

        $data = [
            'title'         => 'Registrasi Aset Baru', 
            'barang'        => null,
            'daftarUnit'    => $this->getDaftarUnit(),
            'kategoriList'  => $kategoriList,
            'lokasiList'    => $lokasiList,
            'pegawaiList'   => $pegawaiList,
            'filterJenjang' => $filterJenjang,
            'isGlobal'      => $isGlobal
        ];
        return view('sapras/barang/form', $data);
    }

    public function edit($id)
    {
        $barang = $this->model->find($id);
        if (!$barang) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Data aset tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // PROTEKSI 3: Admin Unit tidak boleh mengedit Aset unit lain meskipun tahu ID-nya
        if (!$isGlobal && strtoupper($barang['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Akses Ditolak. Aset ini milik unit lain.');
        }

        // Ambil Data Relasi Sesuai Unit Aset
        $kategoriList = $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll();
        
        $lokasiQuery = $this->lokasiModel;
        if (strtoupper($barang['kode_jenjang']) !== 'GLOBAL') {
            $lokasiQuery->where('kode_jenjang', $barang['kode_jenjang']);
        }
        $lokasiList = $lokasiQuery->orderBy('nama_lokasi', 'ASC')->findAll();

        $pegawaiList = $this->getDaftarPegawai($barang['kode_jenjang']);

        $data = [
            'title'         => 'Edit Informasi Aset', 
            'barang'        => (object) $barang, 
            'daftarUnit'    => $this->getDaftarUnit(),
            'kategoriList'  => $kategoriList,
            'lokasiList'    => $lokasiList,
            'pegawaiList'   => $pegawaiList,
            'filterJenjang' => $barang['kode_jenjang'],
            'isGlobal'      => $isGlobal
        ];
        return view('sapras/barang/form', $data);
    }

    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');
        
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);

        // PROTEKSI 4: Paksa kode jenjang sesuai sesi jika bukan Superadmin (Anti Post-man Manipulation)
        $finalJenjang = $isGlobal ? ($inputJenjang ?: 'GLOBAL') : $sessionJenjang;

        // Bersihkan format mata uang (Misal: 15.000.000 menjadi 15000000)
        $hargaPerolehan = preg_replace('/[^0-9]/', '', $this->request->getPost('harga_perolehan') ?: '0');

        $data = [
            'kode_jenjang'        => $finalJenjang,
            'id_kategori'         => $this->request->getPost('id_kategori'),
            'id_lokasi'           => $this->request->getPost('id_lokasi') ?: null,
            'id_penanggung_jawab' => $this->request->getPost('id_penanggung_jawab') ?: null,
            'nama_aset'           => $this->request->getPost('nama_aset'),
            'merk_spesifikasi'    => $this->request->getPost('merk_spesifikasi'),
            'sumber_dana'         => $this->request->getPost('sumber_dana'),
            'status_kepemilikan'  => $this->request->getPost('status_kepemilikan') ?: 'Milik Sendiri',
            'tanggal_perolehan'   => $this->request->getPost('tanggal_perolehan') ?: null,
            'harga_perolehan'     => $hargaPerolehan,
            'kondisi'             => $this->request->getPost('kondisi') ?: 'Baik',
            'status_ketersediaan' => $this->request->getPost('status_ketersediaan') ?: 'Tersedia',
        ];

        if ($id) {
            $data['id'] = $id;
            // Pertahankan kode aset yang sudah ada
            $existing = $this->model->find($id);
            
            // PROTEKSI TAMBAHAN saat edit, pastikan dia berhak mengedit data ini
            if (!$isGlobal && strtoupper($existing['kode_jenjang']) !== $sessionJenjang) {
                return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Akses Ditolak.');
            }
            
            $data['kode_aset'] = $this->request->getPost('kode_aset') ?: ($existing['kode_aset'] ?? '');
        } else {
            // Auto-Generate Barcode Aset (Contoh: AST-202603-K9L2X)
            $randomString = strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5));
            $data['kode_aset'] = 'AST-' . date('Ym') . '-' . $randomString;
        }

        if (!$this->model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        $pesan = $id ? 'Data aset berhasil diperbarui.' : 'Aset baru berhasil diregistrasi ke sistem.';
        return redirect()->to(base_url('app/sapras/barang'))->with('success', $pesan);
    }

    public function delete($id)
    {
        $barang = $this->model->find($id);
        
        if (!$id || !$barang) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Aset tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // PROTEKSI 5: Hapus lintas unit ditolak!
        if (!$isGlobal && strtoupper($barang['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Akses Ditolak.');
        }

        // PENCEGAHAN: Cek jika barang sedang dipinjam atau diperbaiki
        if (in_array($barang['status_ketersediaan'], ['Dipinjam', 'Diperbaiki'])) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Gagal dihapus! Aset ini tercatat sedang dipinjam atau sedang diperbaiki. Kembalikan aset terlebih dahulu sebelum dihapus.');
        }

        // Gunakan Soft Delete
        $this->model->delete($id);
        
        return redirect()->to(base_url('app/sapras/barang'))->with('success', 'Aset berhasil dihapus (Soft Delete) dari inventaris aktif.');
    }

    /**
     * Fitur Cetak Label Stiker Barcode per Aset
     */
    public function printLabel($id)
    {
        $barang = $this->model->getBarangBuilder()->where('aset_barang.id', $id)->get()->getRowArray();
        
        if (!$barang) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Aset tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);

        // PROTEKSI 6: Cetak label lintas unit ditolak
        if (!$isGlobal && strtoupper($barang['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/sapras/barang'))->with('error', 'Akses Ditolak.');
        }
        
        // AMBIL DATA YAYASAN DARI SETTINGS MODEL
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getSettingsAsArray('Global');

        return view('sapras/barang/print_label', [
            'title'        => 'Cetak Label Aset',
            'barang'       => $barang,
            'nama_yayasan' => $settings['nama_yayasan'] ?? 'YAYASAN PENDIDIKAN'
        ]);
    }

    /**
     * Fitur Cetak Laporan Rekapitulasi (Kertas A4 / Landscape)
     */
    public function printReport()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL'); 
        $isGlobal = in_array($sessionJenjang, $this->globalIdentifiers);
        
        $filterJenjang = $this->request->getGet('jenjang');
        $filterKategori = $this->request->getGet('kategori');
        $filterLokasi = $this->request->getGet('lokasi');
        $filterKondisi = $this->request->getGet('kondisi');

        // PROTEKSI 7: Laporan hanya mengambil data sesuai otoritas
        if ($isGlobal) {
            $scopeQuery = (!empty($filterJenjang) && !in_array(strtoupper($filterJenjang), $this->globalIdentifiers)) ? $filterJenjang : 'GLOBAL';
        } else {
            $scopeQuery = $sessionJenjang;
        }

        $builder = $this->model->getBarangBuilder($scopeQuery);

        // Terapkan Filter yang Sama dengan Index
        if (!empty($filterKategori)) $builder->where('aset_barang.id_kategori', $filterKategori);
        if (!empty($filterLokasi)) $builder->where('aset_barang.id_lokasi', $filterLokasi);
        if (!empty($filterKondisi)) $builder->where('aset_barang.kondisi', $filterKondisi);

        // Ambil semua data (Tanpa Limit Paginasi) untuk Laporan
        $barangData = $builder->orderBy('aset_barang.created_at', 'DESC')->get()->getResultArray();

        // AMBIL DATA YAYASAN DARI SETTINGS MODEL
        $settingsModel = new \App\Models\SettingsModel();
        $settings = $settingsModel->getSettingsAsArray('Global');

        return view('sapras/barang/print_report', [
            'title'         => 'Laporan Rekapitulasi Aset Inventaris',
            'barang'        => $barangData,
            'filterJenjang' => $scopeQuery,
            'tanggalCetak'  => date('d F Y H:i'),
            'nama_yayasan'  => $settings['nama_yayasan'] ?? 'YAYASAN PENDIDIKAN'
        ]);
    }
}