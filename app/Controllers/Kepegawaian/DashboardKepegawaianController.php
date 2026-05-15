<?php

namespace App\Controllers\Kepegawaian;

use App\Controllers\BaseController;
use App\Models\Kepegawaian\AbsensiPegawaiModel;
use CodeIgniter\I18n\Time;

/**
 * DashboardKepegawaianController
 * Pusat kontrol dan monitoring kinerja SDM, Absensi, dan Penggajian.
 */
class DashboardKepegawaianController extends BaseController
{
    protected $db;
    protected $absensiModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        $this->absensiModel = new AbsensiPegawaiModel();
    }

    public function index()
    {
        $session = session();
        $sessionUnit = $session->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        $today = date('Y-m-d');

        // --- 1. STATISTIK SDM (Total Pegawai Aktif) ---
        $builder = $this->db->table('pegawai');
        $builder->select('
            COUNT(id) as total,
            SUM(CASE WHEN jenis_pegawai = "guru" THEN 1 ELSE 0 END) as total_guru,
            SUM(CASE WHEN jenis_pegawai IN ("staff", "penunjang") THEN 1 ELSE 0 END) as total_staff
        ');
        $builder->where('status_aktif', 'aktif');
        
        if (!$isGlobal) {
            $builder->where('kode_jenjang', $sessionUnit);
        }
        $statsSDM = $builder->get()->getRow();

        // --- 2. STATISTIK PRESENSI HARI INI ---
        // Menggunakan AbsensiPegawaiModel untuk query yang konsisten
        $statsAbsensi = $this->absensiModel->getDailyStats($today, null, (!$isGlobal ? $sessionUnit : null));

        // Hitung Persentase Kehadiran
        $totalWajibHadir = ($statsSDM->total ?? 0); 
        // (Opsional: kurangi yang cuti jika ada logika cuti)
        
        $jumlahHadir = $statsAbsensi->hadir ?? 0;
        $persenHadir = $totalWajibHadir > 0 ? round(($jumlahHadir / $totalWajibHadir) * 100) : 0;


        // --- 3. ESTIMASI PENGGAJIAN BULAN INI ---
        // Menghitung total gaji yang harus dibayarkan bulan ini
        // (Asumsi: Gaji Pokok + Tunjangan Tetap dari tabel gaji_pegawai)
        $payrollBuilder = $this->db->table('gaji_pegawai gp')
            ->selectSum('gp.jumlah_set', 'total_nominal')
            ->join('komponen_gaji kg', 'kg.id = gp.id_komponen')
            ->where('gp.is_active', 1)
            ->where('kg.tipe', 1); // 1 = Pendapatan
            
        if (!$isGlobal) {
            $payrollBuilder->where('gp.kode_jenjang', $sessionUnit);
        }
        $estimasiPayroll = $payrollBuilder->get()->getRow()->total_nominal ?? 0;


        // --- 4. DATA CHART TREN KEHADIRAN (7 HARI TERAKHIR) ---
        $trendBuilder = $this->db->table('absensi_pegawai ap')
            ->select('ap.tanggal, COUNT(ap.id) as total_hadir')
            ->join('pegawai p', 'p.id = ap.id_pegawai')
            ->where('ap.status', 'hadir')
            ->where('ap.tanggal >=', date('Y-m-d', strtotime('-7 days')))
            ->groupBy('ap.tanggal')
            ->orderBy('ap.tanggal', 'ASC');

        if (!$isGlobal) {
            $trendBuilder->where('p.kode_jenjang', $sessionUnit);
        }
        $trendAbsensi = $trendBuilder->get()->getResultArray();

        $data = [
            'title'          => 'Dashboard Kepegawaian',
            'current_module' => 'kepegawaian',
            'stats_sdm'      => $statsSDM,
            'stats_absensi'  => $statsAbsensi,
            'persen_hadir'   => $persenHadir,
            'estimasi_gaji'  => $estimasiPayroll,
            'trend_absensi'  => $trendAbsensi,
            'is_global'      => $isGlobal,
            'session_unit'   => $sessionUnit,
            'tanggal'        => $today
        ];

        return view('kepegawaian/dashboard', $data);
    }
}