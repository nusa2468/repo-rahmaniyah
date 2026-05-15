<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\PembayaranModel;
use App\Models\JenjangModel;
use App\Models\HakAksesModel;
use App\Models\SettingsModel; // Added

class LaporanPemasukanController extends BaseController
{
    protected $pembayaranModel;
    protected $jenjangModel;
    protected $hakAksesModel;
    protected $settingsModel; // Added
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'number']);
        $this->pembayaranModel = new PembayaranModel();
        $this->jenjangModel    = new JenjangModel();
        $this->hakAksesModel   = new HakAksesModel();
        $this->settingsModel   = new SettingsModel(); // Instansiasi
        $this->db              = \Config\Database::connect();
    }

    public function index()
    {
        $session  = session();
        $userRole = $session->get('role');
        $userUnit = strtoupper($session->get('kode_jenjang') ?? '');

        // 1. Cek Hak Akses
        $isSuperAdmin = false;
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            $isSuperAdmin = true;
        } else {
            $roleData = $this->hakAksesModel->where('name', $userRole)->first();
            if ($roleData && in_array(strtoupper($roleData['kode_jenjang'] ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT'])) {
                $isSuperAdmin = true;
            }
        }

        // 2. Filter Data
        $jenjangFilter = $this->request->getGet('jenjang');
        if (!$isSuperAdmin) {
            $jenjangFilter = $session->get('kode_jenjang');
        }
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        // 3. Build Query
        $builder = $this->pembayaranModel->builder()
            ->select('pembayaran.*, siswa.nama_lengkap, siswa.nis, jenis_pembayaran.nama_pembayaran, tagihan.deskripsi as deskripsi_tagihan')
            ->join('tagihan', 'tagihan.id = pembayaran.id_tagihan', 'left')
            ->join('siswa', 'siswa.id = tagihan.id_siswa', 'left')
            ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran', 'left')
            ->where('pembayaran.deleted_at', null);

        if (!empty($jenjangFilter)) {
            $builder->where('pembayaran.kode_jenjang', $jenjangFilter);
        }
        $builder->where('pembayaran.tanggal_bayar >=', $startDate . ' 00:00:00')
                ->where('pembayaran.tanggal_bayar <=', $endDate . ' 23:59:59');
        $builder->orderBy('pembayaran.tanggal_bayar', 'DESC');

        // 4. Pagination
        $perPage     = 20;
        $currentPage = $this->request->getVar('page_default') ? (int)$this->request->getVar('page_default') : 1;
        $nomorUrut   = ($currentPage - 1) * $perPage;

        $countBuilder = clone $builder;
        $totalPemasukan = $countBuilder->selectSum('pembayaran.jumlah_bayar')->get()->getRow()->jumlah_bayar ?? 0;

        $chartBuilder = clone $builder;
        
        $this->pembayaranModel->resetQuery(); 
        if(!empty($jenjangFilter)) $this->pembayaranModel->where('pembayaran.kode_jenjang', $jenjangFilter);
        $this->pembayaranModel->where('pembayaran.tanggal_bayar >=', $startDate . ' 00:00:00');
        $this->pembayaranModel->where('pembayaran.tanggal_bayar <=', $endDate . ' 23:59:59');
        $this->pembayaranModel->select('pembayaran.*, siswa.nama_lengkap, siswa.nis, jenis_pembayaran.nama_pembayaran')
                              ->join('tagihan', 'tagihan.id = pembayaran.id_tagihan', 'left')
                              ->join('siswa', 'siswa.id = tagihan.id_siswa', 'left')
                              ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran', 'left')
                              ->orderBy('pembayaran.tanggal_bayar', 'DESC');

        $dataLaporan = $this->pembayaranModel->paginate($perPage, 'default');
        
        // 5. Grafik
        $chartDataRaw = $chartBuilder->select('DATE(pembayaran.tanggal_bayar) as tanggal, SUM(pembayaran.jumlah_bayar) as total')
            ->groupBy('DATE(pembayaran.tanggal_bayar)')
            ->orderBy('tanggal', 'ASC')
            ->get()->getResultArray();
        $chartLabels = [];
        $chartValues = [];
        foreach ($chartDataRaw as $row) {
            $chartLabels[] = date('d M', strtotime($row['tanggal']));
            $chartValues[] = (float)$row['total'];
        }

        $jenjangList = $this->jenjangModel->getDropdownOptions();
        foreach ($jenjangList as &$j) {
            if (strtoupper($j['kode_jenjang']) === 'GLOBAL') $j['nama_jenjang'] = 'Agregat (Yayasan)';
        }

        $data = [
            'title'           => 'Laporan Pemasukan Kas',
            'current_module'  => 'keuangan',
            'start_date'      => $startDate,
            'end_date'        => $endDate,
            'pembayaran'      => $dataLaporan,
            'pager'           => $this->pembayaranModel->pager,
            'nomor_urut'      => $nomorUrut,
            'total_pemasukan' => $totalPemasukan,
            'jenjang_list'    => $jenjangList,
            'filter_jenjang'  => $jenjangFilter,
            'isSuperAdmin'    => $isSuperAdmin,
            'navigation'      => $this->getNavigation(),
            'chart_data'      => [
                'labels'   => empty($chartLabels) ? ['Minggu 1', 'Minggu 2'] : $chartLabels,
                'datasets' => empty($chartValues) ? [0, 0] : $chartValues
            ]
        ];
        return view('keuangan/laporan/pemasukan', $data);
    }

    // --- FITUR CETAK ---
    public function cetak()
    {
        $session = session();
        $userRole = strtolower($session->get('role') ?? '');
        $userUnit = strtoupper($session->get('kode_jenjang') ?? '');
        
        $isSuperAdmin = false;
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            $isSuperAdmin = true;
        } else {
            $roleData = $this->hakAksesModel->where('name', $userRole)->first();
            if ($roleData && in_array(strtoupper($roleData['kode_jenjang'] ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT'])) {
                $isSuperAdmin = true;
            }
        }

        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $jenjang   = $this->request->getGet('jenjang');
        
        if (!$isSuperAdmin) $jenjang = $session->get('kode_jenjang');

        $query = $this->pembayaranModel->builder()
            ->select('pembayaran.tanggal_bayar, pembayaran.jumlah_bayar, pembayaran.kode_jenjang, pembayaran.metode_pembayaran, siswa.nama_lengkap as nama_siswa, siswa.nis, jenis_pembayaran.nama_pembayaran, tagihan.deskripsi as deskripsi_tagihan')
            ->join('tagihan', 'tagihan.id = pembayaran.id_tagihan', 'left')
            ->join('siswa', 'siswa.id = tagihan.id_siswa', 'left')
            ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran', 'left')
            ->where('pembayaran.deleted_at', null)
            ->where('pembayaran.tanggal_bayar >=', $startDate . ' 00:00:00')
            ->where('pembayaran.tanggal_bayar <=', $endDate . ' 23:59:59');

        if (!empty($jenjang)) $query->where('pembayaran.kode_jenjang', $jenjang);
        
        $dataLaporan = $query->orderBy('tanggal_bayar', 'ASC')->get()->getResultArray();
        
        // Identitas Sekolah
        $identitas = $this->getIdentitasSekolah($jenjang);

        $data = [
            'judul'          => 'LAPORAN REALISASI PEMASUKAN',
            'laporan'        => $dataLaporan,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'jenjang_label'  => !empty($jenjang) ? "UNIT $jenjang" : "AGREGAT (SEMUA UNIT)",
            'instansi'       => $identitas,
            'user_pencetak'  => session()->get('username') ?? 'Admin'
        ];

        return view('keuangan/laporan/cetak_pemasukan', $data);
    }

    private function getIdentitasSekolah($jenjang)
    {
        $settings = $this->settingsModel->getSettingsAsArray('Global');
        if ($jenjang && $jenjang !== 'Global') {
            $unitSettings = $this->settingsModel->getSettingsAsArray($jenjang);
            if (!empty($unitSettings)) $settings = array_merge($settings, $unitSettings);
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