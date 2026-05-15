<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\PengeluaranModel;
use App\Models\JenjangModel;
use App\Models\HakAksesModel;
use App\Models\SettingsModel; 

class LaporanPengeluaranController extends BaseController
{
    protected $pengeluaranModel;
    protected $jenjangModel;
    protected $hakAksesModel;
    protected $settingsModel; 
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'number']);
        $this->pengeluaranModel = new PengeluaranModel();
        $this->jenjangModel     = new JenjangModel();
        $this->hakAksesModel    = new HakAksesModel();
        $this->settingsModel    = new SettingsModel(); 
        $this->db               = \Config\Database::connect();
    }

    public function index()
    {
        $session  = session();
        $userRole = $session->get('role');
        $userUnit = strtoupper($session->get('kode_jenjang') ?? '');

        // --- 1. CEK HAK AKSES ---
        $isSuperAdmin = false;
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            $isSuperAdmin = true;
        } else {
            $roleData = $this->hakAksesModel->where('name', $userRole)->first();
            if ($roleData && in_array(strtoupper($roleData['kode_jenjang'] ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT'])) {
                $isSuperAdmin = true;
            }
        }

        // --- 2. FILTER DATA ---
        $jenjangFilter = $this->request->getGet('jenjang');
        if (!$isSuperAdmin) {
            $jenjangFilter = $session->get('kode_jenjang');
        }

        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        // --- 3. BUILD QUERY (TABEL) ---
        $builder = $this->pengeluaranModel->builder()
            ->select('pengeluaran.*, kategori_anggaran.nama_kategori, kategori_anggaran.kode_kategori')
            ->join('kategori_anggaran', 'kategori_anggaran.id = pengeluaran.id_kategori', 'left')
            ->where('pengeluaran.deleted_at', null);

        if (!empty($jenjangFilter)) {
            $builder->where('pengeluaran.kode_jenjang', $jenjangFilter);
        }

        $builder->where('pengeluaran.tanggal >=', $startDate)
                ->where('pengeluaran.tanggal <=', $endDate);

        $builder->orderBy('pengeluaran.tanggal', 'DESC');

        // --- 4. PAGINATION ---
        $perPage     = 20;
        $currentPage = $this->request->getVar('page_default') ? (int)$this->request->getVar('page_default') : 1;
        $nomorUrut   = ($currentPage - 1) * $perPage;

        // Clone builder untuk hitung total KPI & Grafik
        $countBuilder = clone $builder;
        $chartBuilder = clone $builder;
        
        $totalPengeluaran = $countBuilder->selectSum('pengeluaran.jumlah')->get()->getRow()->jumlah ?? 0;

        // Eksekusi Pagination
        $this->pengeluaranModel->resetQuery();
        if(!empty($jenjangFilter)) $this->pengeluaranModel->where('kode_jenjang', $jenjangFilter);
        $this->pengeluaranModel->where('tanggal >=', $startDate);
        $this->pengeluaranModel->where('tanggal <=', $endDate);
        $this->pengeluaranModel->select('pengeluaran.*, kategori_anggaran.nama_kategori')
                               ->join('kategori_anggaran', 'kategori_anggaran.id = pengeluaran.id_kategori', 'left')
                               ->orderBy('tanggal', 'DESC');
                               
        $dataLaporan = $this->pengeluaranModel->paginate($perPage, 'default');

        // --- 5. DATA GRAFIK ---
        $chartDataRaw = $chartBuilder->select('DATE(pengeluaran.tanggal) as tgl, SUM(pengeluaran.jumlah) as total')
            ->groupBy('DATE(pengeluaran.tanggal)')
            ->orderBy('tgl', 'ASC')
            ->get()->getResultArray();

        $chartLabels = [];
        $chartValues = [];
        foreach ($chartDataRaw as $row) {
            $chartLabels[] = date('d M', strtotime($row['tgl']));
            $chartValues[] = (float)$row['total'];
        }

        // --- 6. KPI TAMBAHAN ---
        $itemTerbesar = $this->db->table('pengeluaran')
            ->select('keterangan, jumlah')
            ->where('deleted_at', null)
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate);
        if (!empty($jenjangFilter)) $itemTerbesar->where('kode_jenjang', $jenjangFilter);
        $topItem = $itemTerbesar->orderBy('jumlah', 'DESC')->limit(1)->get()->getRowArray();

        // --- 7. DROPDOWN ---
        $jenjangList = $this->jenjangModel->getDropdownOptions();
        foreach ($jenjangList as &$j) {
            if (strtoupper($j['kode_jenjang']) === 'GLOBAL') {
                $j['nama_jenjang'] = 'Agregat (Yayasan)';
            }
        }

        $data = [
            'title'             => 'Laporan Pengeluaran Kas',
            'current_module'    => 'keuangan',
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'laporan'           => $dataLaporan,
            'pager'             => $this->pengeluaranModel->pager,
            'nomor_urut'        => $nomorUrut,
            'total_pengeluaran' => $totalPengeluaran,
            'kpi' => [
                'item_terbesar'    => $topItem['keterangan'] ?? '-',
                'nominal_terbesar' => $topItem['jumlah'] ?? 0
            ],
            'jenjang_list'      => $jenjangList,
            'filter_jenjang'    => $jenjangFilter,
            'isSuperAdmin'      => $isSuperAdmin,
            'navigation'        => $this->getNavigation(),
            'chart_data' => [
                'labels'   => empty($chartLabels) ? ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'] : $chartLabels,
                'datasets' => empty($chartValues) ? [0, 0, 0, 0] : $chartValues
            ]
        ];

        return view('keuangan/laporan/pengeluaran', $data);
    }

    // --- FITUR CETAK ---
    public function cetak()
    {
        $session = session();
        $userRole = strtolower($session->get('role') ?? '');
        $userUnit = strtoupper($session->get('kode_jenjang') ?? '');

        // Hak Akses
        $isSuperAdmin = false;
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            $isSuperAdmin = true;
        } else {
            $roleData = $this->hakAksesModel->where('name', $userRole)->first();
            if ($roleData && in_array(strtoupper($roleData['kode_jenjang'] ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT'])) {
                $isSuperAdmin = true;
            }
        }

        // Parameter
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $jenjang   = $this->request->getGet('jenjang');
        $format    = $this->request->getGet('format');

        // Security Force Unit
        if (!$isSuperAdmin) {
            $jenjang = $session->get('kode_jenjang');
        }

        // Query Data Pengeluaran
        // FIX: Ubah 'jumlah' menjadi 'nominal' alias agar terbaca di View Cetak
        $query = $this->pengeluaranModel->builder()
            ->select('pengeluaran.tanggal, pengeluaran.jumlah as nominal, pengeluaran.kode_jenjang, pengeluaran.keterangan, kategori_anggaran.nama_kategori, pengeluaran.keterangan as pihak')
            ->join('kategori_anggaran', 'kategori_anggaran.id = pengeluaran.id_kategori', 'left')
            ->where('pengeluaran.deleted_at', null)
            ->where('pengeluaran.tanggal >=', $startDate)
            ->where('pengeluaran.tanggal <=', $endDate);

        if (!empty($jenjang)) {
            $query->where('pengeluaran.kode_jenjang', $jenjang);
        }

        $dataLaporan = $query->orderBy('tanggal', 'ASC')->get()->getResultArray();
        
        $totalNominal = 0;
        foreach($dataLaporan as $d) {
            $totalNominal += $d['nominal']; // Sekarang 'nominal' sudah ada
        }

        // Identitas Sekolah
        $identitas = $this->getIdentitasSekolah($jenjang);

        $data = [
            'judul'          => 'LAPORAN REALISASI PENGELUARAN',
            'laporan'        => $dataLaporan,
            'total_nominal'  => $totalNominal,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'jenjang_label'  => !empty($jenjang) ? "UNIT $jenjang" : "AGREGAT (SEMUA UNIT)",
            'instansi'       => $identitas, 
            'user_pencetak'  => session()->get('username') ?? 'Admin'
        ];

        if ($format === 'excel') {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=Laporan_Pengeluaran_" . date('Ymd') . ".xls");
        }

        return view('keuangan/laporan/cetak_pengeluaran', $data);
    }

    private function getIdentitasSekolah($jenjang)
    {
        $context = $jenjang ?: 'Global'; 
        $settings = $this->settingsModel->getSettingsAsArray($context);

        if (empty($settings) && $context !== 'Global') {
            $settings = $this->settingsModel->getSettingsAsArray('Global');
        }

        return [
            'nama'   => $settings['nama_sekolah'] ?? 'YAYASAN PENDIDIKAN GENERASI JUARA',
            'alamat' => $settings['alamat_sekolah'] ?? 'Jl. Pendidikan No. 123, Kota Harapan Indah',
            'kontak' => 'Telp: ' . ($settings['telepon_sekolah'] ?? '-') . ' | Email: ' . ($settings['email_sekolah'] ?? 'info@sekolah.sch.id'),
        ];
    }

    private function getNavigation()
    {
        return [
            'dashboard'   => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
            'budget'      => ['label' => 'Anggaran (Budget)', 'icon' => 'pie-chart', 'url' => 'app/keuangan/budget'],
            'tagihan'     => ['label' => 'Tagihan & Piutang', 'icon' => 'file-text', 'url' => 'app/keuangan/tagihan'],
            'pemasukan'  => ['label' => 'Pemasukan', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/laporan/pemasukan'],
            'pengeluaran' => ['label' => 'Pengeluaran', 'icon' => 'arrow-up-circle', 'url' => 'app/keuangan/laporan/pengeluaran'],
            'Akuntansi'     => ['label' => 'Akuntansi', 'icon' => 'printer', 'url' => 'app/keuangan/akuntansi'],
        ];
    }
}