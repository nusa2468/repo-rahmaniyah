<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\PembayaranModel;
use App\Models\Keuangan\TagihanModel;
use App\Models\Keuangan\PengeluaranModel;
use App\Models\Keuangan\BudgetModel;
use App\Models\JenjangModel;
use App\Models\HakAksesModel;

class DashboardController extends BaseController
{
    protected $pembayaranModel;
    protected $tagihanModel;
    protected $pengeluaranModel;
    protected $budgetModel;
    protected $jenjangModel;
    protected $hakAksesModel;
    protected $db;

    public function __construct()
    {
        $this->pembayaranModel  = new PembayaranModel();
        $this->tagihanModel     = new TagihanModel();
        $this->pengeluaranModel = new PengeluaranModel();
        $this->budgetModel      = new BudgetModel();
        $this->jenjangModel     = new JenjangModel();
        $this->hakAksesModel    = new HakAksesModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $session = session();
        $userRole = $session->get('role'); 
        $userUnit = strtoupper($session->get('kode_jenjang') ?? ''); 

        $tahunAktif = $session->get('tahun_aktif');
        if (!$tahunAktif) {
            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');
            $tahunAktif = ($currentMonth > 6) ? $currentYear . '/' . ($currentYear + 1) : ($currentYear - 1) . '/' . $currentYear;
        }

        $isSuperAdmin = false;
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            $isSuperAdmin = true;
        } else {
            $roleData = $this->hakAksesModel->where('name', $userRole)->first();
            if ($roleData) {
                $roleScope = strtoupper($roleData['kode_jenjang'] ?? '');
                if (in_array($roleScope, ['GLOBAL', 'YAYASAN', 'ROOT'])) {
                    $isSuperAdmin = true;
                }
            }
        }

        $jenjang = $this->request->getGet('jenjang');
        if (!$isSuperAdmin) {
            $jenjang = $session->get('kode_jenjang'); 
        }

        $targetBudget = $this->budgetModel->getTotalBudget($jenjang, $tahunAktif, 'beban');
        $totalPemasukan   = $this->getTotalPemasukan($jenjang, $tahunAktif);
        $totalPengeluaran = $this->getTotalPengeluaran($jenjang, $tahunAktif);
        
        $surplusDefisit = $totalPemasukan - $totalPengeluaran;
        $persenBudget   = ($targetBudget > 0) ? ($totalPengeluaran / $targetBudget) * 100 : 0;

        // FIX: Panggil getTotalPiutang yang sudah diperbaiki
        $totalPiutang = $this->getTotalPiutang($jenjang);

        $stats = [
            'total_pemasukan'   => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'total_piutang'     => $totalPiutang, 
            'budget_rencana'    => $targetBudget,
            'surplus_defisit'   => $surplusDefisit,
            'persen_budget'     => round($persenBudget, 1),
            'tahun_aktif'       => $tahunAktif
        ];

        $jenjangList = $this->jenjangModel->getDropdownOptions();

        $data = [
            'title'               => 'Dashboard Keuangan',
            'jenjang'             => $jenjang,
            'stats'               => $stats,
            'is_superadmin'       => $isSuperAdmin, 
            'navigation'          => $this->getNavigation(),
            'chart_cashflow'      => $this->getCashflowTrendData($jenjang),
            'chart_distribution'  => $this->getDistributionData($tahunAktif, $jenjang), 
            'recent_transactions' => $this->getMergedTransactions($jenjang),
            'jenjang_list'        => $jenjangList
        ];

        return view('keuangan/dashboard', $data);
    }

    private function getTotalPemasukan($jenjang, $tahun)
    {
        $builder = $this->db->table('pembayaran');
        if (!empty($jenjang)) $builder->where('kode_jenjang', $jenjang);
        if ($tahun && strpos($tahun, '/') !== false) {
            $years = explode('/', $tahun);
            if (count($years) == 2) {
                $builder->where('tanggal_bayar >=', $years[0] . '-07-01')
                        ->where('tanggal_bayar <=', $years[1] . '-06-30');
            }
        }
        return (float)($builder->selectSum('jumlah_bayar', 'total')->get()->getRow()->total ?? 0);
    }

    private function getTotalPengeluaran($jenjang, $tahun)
    {
        $builder = $this->db->table('pengeluaran');
        if (!empty($jenjang)) $builder->where('kode_jenjang', $jenjang);
        if ($tahun && strpos($tahun, '/') !== false) {
            $years = explode('/', $tahun);
            if (count($years) == 2) {
                $builder->where('tanggal >=', $years[0] . '-07-01')
                        ->where('tanggal <=', $years[1] . '-06-30');
            }
        }
        return (float)($builder->selectSum('jumlah', 'total')->get()->getRow()->total ?? 0);
    }

    /**
     * FIX: Menghitung Piutang dengan JOIN karena kolom 'terbayar' tidak ada di tabel tagihan.
     * Rumus: Total Tagihan (Belum Lunas) - Total Pembayaran (Utk Tagihan tsb)
     */
    private function getTotalPiutang($jenjang)
    {
        // 1. Ambil Total Tagihan (Kotor) yang belum lunas
        $builderTagihan = $this->db->table('tagihan');
        $builderTagihan->selectSum('jumlah', 'total_tagihan'); // Gunakan 'jumlah' sesuai struktur DB
        $builderTagihan->where('status !=', 'lunas');

        if (!empty($jenjang)) {
            $builderTagihan->where('kode_jenjang', $jenjang);
        }

        $resTagihan = $builderTagihan->get()->getRow();
        $totalTagihan = $resTagihan->total_tagihan ?? 0;

        // 2. Ambil Total Pembayaran (Cicilan) untuk tagihan yang belum lunas tersebut
        $builderBayar = $this->db->table('pembayaran');
        $builderBayar->selectSum('pembayaran.jumlah_bayar', 'total_bayar');
        $builderBayar->join('tagihan', 'tagihan.id = pembayaran.id_tagihan');
        $builderBayar->where('tagihan.status !=', 'lunas');
        
        if (!empty($jenjang)) {
            $builderBayar->where('tagihan.kode_jenjang', $jenjang);
        }

        $resBayar = $builderBayar->get()->getRow();
        $totalSudahBayar = $resBayar->total_bayar ?? 0;

        // 3. Piutang Bersih
        return (float)($totalTagihan - $totalSudahBayar);
    }

    private function getCashflowTrendData($jenjang)
    {
        $labels = []; $income = []; $expense = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d/m', strtotime($date));

            $in = $this->db->table('pembayaran')->where('tanggal_bayar', $date);
            if (!empty($jenjang)) $in->where('kode_jenjang', $jenjang);
            $income[] = (float)($in->selectSum('jumlah_bayar', 'total')->get()->getRow()->total ?? 0);

            $out = $this->db->table('pengeluaran')->where('tanggal', $date);
            if (!empty($jenjang)) $out->where('kode_jenjang', $jenjang);
            $expense[] = (float)($out->selectSum('jumlah', 'total')->get()->getRow()->total ?? 0);
        }
        return ['labels' => $labels, 'income' => $income, 'expense' => $expense];
    }

    private function getDistributionData($tahun, $jenjangScope = null)
    {
        $dist = [];
        if (!empty($jenjangScope)) {
             $query = $this->db->table('pembayaran')
                 ->select('jenis_pembayaran.nama_pembayaran, SUM(pembayaran.jumlah_bayar) as total')
                 ->join('tagihan', 'tagihan.id = pembayaran.id_tagihan')
                 ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran')
                 ->where('pembayaran.kode_jenjang', $jenjangScope)
                 ->groupBy('jenis_pembayaran.nama_pembayaran')
                 ->orderBy('total', 'DESC')
                 ->limit(5)
                 ->get();
             foreach ($query->getResult() as $row) $dist[$row->nama_pembayaran] = (float)$row->total;
             if (empty($dist)) $dist['Belum Ada Data'] = 0;
             return $dist;
        }

        $jenjangList = $this->jenjangModel->getDropdownOptions();
        foreach ($jenjangList as $j) {
            $res = $this->db->table('pembayaran')->where('kode_jenjang', $j['kode_jenjang'])->selectSum('jumlah_bayar', 'total')->get()->getRow();
            $dist[$j['nama_jenjang']] = (float)($res->total ?? 0);
        }
        
        $resGlobal = $this->db->table('pembayaran')->where('kode_jenjang', 'Global')->selectSum('jumlah_bayar', 'total')->get()->getRow();
        if (($resGlobal->total ?? 0) > 0) $dist['Yayasan'] = (float)$resGlobal->total;

        return $dist;
    }

    private function getMergedTransactions($jenjang)
    {
        $in = $this->db->table('pembayaran')
            ->select('pembayaran.tanggal_bayar as tanggal, "masuk" as jenis, jenis_pembayaran.nama_pembayaran as kategori, siswa.nama_lengkap as pihak_terkait, pembayaran.jumlah_bayar as jumlah, tagihan.deskripsi as deskripsi, pembayaran.kode_jenjang')
            ->join('tagihan', 'tagihan.id = pembayaran.id_tagihan')
            ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran')
            ->join('siswa', 'siswa.id = tagihan.id_siswa')
            ->orderBy('pembayaran.created_at', 'DESC')->limit(10);
        if (!empty($jenjang)) $in->where('pembayaran.kode_jenjang', $jenjang);
        $resIn = $in->get()->getResultArray();

        $out = $this->db->table('pengeluaran')
            ->select('pengeluaran.tanggal, "keluar" as jenis, kategori_anggaran.nama_kategori as kategori, pengeluaran.keterangan as pihak_terkait, pengeluaran.jumlah, pengeluaran.keterangan as deskripsi, pengeluaran.kode_jenjang')
            ->join('kategori_anggaran', 'kategori_anggaran.id = pengeluaran.id_kategori', 'left')
            ->orderBy('pengeluaran.created_at', 'DESC')->limit(10);
        if (!empty($jenjang)) $out->where('pengeluaran.kode_jenjang', $jenjang);
        $resOut = $out->get()->getResultArray();

        $merged = array_merge($resIn, $resOut);
        usort($merged, fn($a, $b) => strtotime($b['tanggal']) <=> strtotime($a['tanggal']));
        return array_slice($merged, 0, 10);
    }

    private function getNavigation()
    {
        return [
            'dashboard'   => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
            'budget'      => ['label' => 'Anggaran (Budget)', 'icon' => 'pie-chart', 'url' => 'app/keuangan/budget'],
            'tagihan'     => ['label' => 'Tagihan & Piutang', 'icon' => 'file-text', 'url' => 'app/keuangan/tagihan'],
            'pemasukan'  => ['label' => 'Pemasukan', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/laporan/pemasukan'],
            'pengeluaran' => ['label' => 'Pengeluaran', 'icon' => 'arrow-up-circle', 'url' => 'app/keuangan/pengeluaran'],
            'akuntansi'     => ['label' => 'Akuntansi', 'icon' => 'printer', 'url' => 'app/keuangan/akuntansi'],
        ];
    }
}