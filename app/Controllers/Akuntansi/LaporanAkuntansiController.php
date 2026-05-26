<?php

namespace App\Controllers\Akuntansi;

use App\Controllers\BaseController;
use App\Models\Akuntansi\AkuntansiCoaModel;
use App\Models\SettingsModel;

class LaporanAkuntansiController extends BaseController
{
    protected $coaModel;
    protected $settingsModel;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ROOT'];

    public function __construct()
    {
        $this->coaModel = new AkuntansiCoaModel();
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Helper: Injeksi Kop Surat Dinamis berdasarkan Filter Entitas
     */
    private function getIdentitasLaporan($filterJenjang)
    {
        // Jika laporannya Multi-Kolom atau Global, tarik identitas Yayasan ('Global')
        $targetJenjang = ($filterJenjang === 'MULTI' || in_array(strtoupper($filterJenjang), $this->globalIdentifiers)) 
                         ? 'Global' 
                         : $filterJenjang;
                         
        return $this->settingsModel->getSettingsAsArray($targetJenjang);
    }

    private function getDaftarUnit()
    {
        $db = \Config\Database::connect();
        $daftarUnit = [];
        if ($db->tableExists('jenjang_sekolah')) {
            $query = $db->table('jenjang_sekolah')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get();
            foreach ($query->getResultArray() as $row) {
                if (!in_array(strtoupper($row['kode_jenjang']), $this->globalIdentifiers)) {
                    $daftarUnit[strtoupper($row['kode_jenjang'])] = $row['nama_jenjang'];
                }
            }
        }
        return $daftarUnit;
    }

    private function cleanAccountName($name)
    {
        $hapus = [' Yayasan', ' Pusat', ' Unit SD', ' Unit SMP', ' Unit SMA', ' Unit TK', ' SD', ' SMP', ' SMA', ' TK'];
        return trim(str_ireplace($hapus, '', $name));
    }

    /**
     * 1. NERACA SALDO (TRIAL BALANCE / KERTAS KERJA)
     * Esensi Jantung Akuntansi: Saldo Awal -> Mutasi -> Saldo Akhir
     */
    public function neracaSaldo()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        // PENGAMANAN MUTLAK: Hanya Yayasan yang boleh akses laporan ini
        if (!$isGlobal) return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak! Modul Laporan Akuntansi Konsolidasi adalah wewenang eksklusif Pusat/Yayasan.');

        $filterJenjang = $this->request->getGet('jenjang') ?? 'GLOBAL';
        $startDate     = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate       = $this->request->getGet('end_date') ?? date('Y-m-t');

        $db = \Config\Database::connect();
        
        // Ambil Kerangka COA Lengkap (Hanya akun Postable / is_parent = 0)
        $coaListRaw = $db->table('akuntansi_coa')
            ->select('kode_akun, MAX(nama_akun) as nama_akun, akuntansi_kategori.saldo_normal')
            ->join('akuntansi_kategori', 'akuntansi_kategori.id = akuntansi_coa.id_kategori', 'left')
            ->where('akuntansi_coa.is_parent', 0)
            ->where('akuntansi_coa.deleted_at', null);

        if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') {
            $coaListRaw->where('akuntansi_coa.kode_jenjang', $filterJenjang);
        }
        $coaListRaw = $coaListRaw->groupBy('kode_akun, akuntansi_kategori.saldo_normal')
                                 ->orderBy('kode_akun', 'ASC')
                                 ->get()->getResultArray();

        // Tarik Saldo Awal Berdiri (Opening Balance dari Master COA)
        $saldoAwalQuery = $db->table('akuntansi_coa')->select('kode_akun, SUM(saldo_awal) as total_awal')->where('deleted_at', null);
        if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') $saldoAwalQuery->where('kode_jenjang', $filterJenjang);
        $saldoAwalRaw = $saldoAwalQuery->groupBy('kode_akun')->get()->getResultArray();
        
        $saldoAwalMap = [];
        foreach($saldoAwalRaw as $sa) $saldoAwalMap[$sa['kode_akun']] = (float)$sa['total_awal'];

        // Tarik Mutasi Jurnal (Debit/Kredit) pada Periode Berjalan
        $mutasiQuery = $db->table('akuntansi_jurnal_detail jd')
            ->select('c.kode_akun, SUM(jd.debit) as tot_debit, SUM(jd.kredit) as tot_kredit')
            ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
            ->join('akuntansi_coa c', 'c.id = jd.id_coa')
            ->where('j.tanggal >=', $startDate)
            ->where('j.tanggal <=', $endDate)
            ->where('j.status', 'Posted');
            
        if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') $mutasiQuery->where('j.kode_jenjang', $filterJenjang);
        $mutasiRaw = $mutasiQuery->groupBy('c.kode_akun')->get()->getResultArray();
        
        $mutasiMap = [];
        foreach($mutasiRaw as $mu) {
            $mutasiMap[$mu['kode_akun']] = [
                'debit' => (float)$mu['tot_debit'],
                'kredit' => (float)$mu['tot_kredit']
            ];
        }

        // Kalkulasi Lajur (Worksheet)
        $laporan = [];
        $grandTotal = [
            'awal_d' => 0, 'awal_k' => 0,
            'mut_d' => 0, 'mut_k' => 0,
            'akhir_d' => 0, 'akhir_k' => 0,
        ];

        foreach ($coaListRaw as $akun) {
            $kode = $akun['kode_akun'];
            $sn   = $akun['saldo_normal'];
            
            // Saldo Awal
            $saldoAwal = $saldoAwalMap[$kode] ?? 0;
            $awalD = ($sn == 'Debit') ? $saldoAwal : 0;
            $awalK = ($sn == 'Kredit') ? $saldoAwal : 0;

            // Mutasi Berjalan
            $mutD = $mutasiMap[$kode]['debit'] ?? 0;
            $mutK = $mutasiMap[$kode]['kredit'] ?? 0;

            // Saldo Akhir (Dengan penanganan Abnormal Balance)
            $akhirD = 0; $akhirK = 0;
            if ($sn == 'Debit') {
                $net = $awalD + $mutD - $mutK;
                if ($net >= 0) $akhirD = $net; else $akhirK = abs($net); 
            } else {
                $net = $awalK + $mutK - $mutD;
                if ($net >= 0) $akhirK = $net; else $akhirD = abs($net);
            }

            // Skip akun jika kosong total dari awal sampai akhir
            if ($awalD == 0 && $awalK == 0 && $mutD == 0 && $mutK == 0 && $akhirD == 0 && $akhirK == 0) continue;

            $laporan[] = [
                'kode_akun' => $kode,
                'nama_akun' => $this->cleanAccountName($akun['nama_akun']),
                'awal_d'    => $awalD,
                'awal_k'    => $awalK,
                'mut_d'     => $mutD,
                'mut_k'     => $mutK,
                'akhir_d'   => $akhirD,
                'akhir_k'   => $akhirK,
            ];

            // Akumulasi Total Bawah
            $grandTotal['awal_d'] += $awalD; $grandTotal['awal_k'] += $awalK;
            $grandTotal['mut_d'] += $mutD;   $grandTotal['mut_k'] += $mutK;
            $grandTotal['akhir_d'] += $akhirD; $grandTotal['akhir_k'] += $akhirK;
        }

        $data = [
            'title'          => 'Neraca Saldo (Trial Balance)',
            'current_module' => 'akuntansi',
            'sekolah'        => $this->getIdentitasLaporan($filterJenjang),
            'laporan'        => $laporan,
            'grandTotal'     => $grandTotal,
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $this->getDaftarUnit()
        ];

        return view('akuntansi/laporan/neraca_saldo', $data);
    }

    /**
     * 2. LAPORAN POSISI KEUANGAN (NERACA)
     */
    public function posisiKeuangan()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        if (!$isGlobal) return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak!');

        $filterJenjang = $this->request->getGet('jenjang') ?? 'GLOBAL';
        $endDate       = $this->request->getGet('end_date') ?? date('Y-m-t');

        $db = \Config\Database::connect();
        $daftarUnit = $this->getDaftarUnit();
        
        $isMultiColumn = ($filterJenjang === 'MULTI');
        $isGlobalTotal = ($filterJenjang === 'GLOBAL');
        $specificUnit  = (!$isMultiColumn && !$isGlobalTotal) ? $filterJenjang : null;

        $coaListRaw = $db->table('akuntansi_coa')
            ->select('kode_akun, MAX(nama_akun) as nama_akun, is_parent, akuntansi_kategori.kode_kategori, akuntansi_kategori.saldo_normal')
            ->join('akuntansi_kategori', 'akuntansi_kategori.id = akuntansi_coa.id_kategori', 'left')
            ->whereIn('akuntansi_kategori.kode_kategori', ['1', '2', '3'])
            ->where('akuntansi_coa.deleted_at', null)
            ->groupBy('kode_akun, is_parent, akuntansi_kategori.kode_kategori, akuntansi_kategori.saldo_normal')
            ->orderBy('kode_akun', 'ASC')
            ->get()->getResultArray();

        $coaList = [];
        foreach ($coaListRaw as $c) {
            $c['nama_akun'] = $this->cleanAccountName($c['nama_akun']);
            $coaList[] = $c;
        }

        $saldoAwalRaw = $db->table('akuntansi_coa')->select('kode_akun, kode_jenjang, SUM(saldo_awal) as saldo_awal')->where('deleted_at', null)->groupBy('kode_akun, kode_jenjang')->get()->getResultArray();
        $saldoAwalMap = [];
        foreach($saldoAwalRaw as $sa) $saldoAwalMap[$sa['kode_akun']][$sa['kode_jenjang']] = (float)$sa['saldo_awal'];

        $mutasiRaw = $db->table('akuntansi_jurnal_detail jd')
            ->select('c.kode_akun, j.kode_jenjang, SUM(jd.debit) as tot_debit, SUM(jd.kredit) as tot_kredit')
            ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
            ->join('akuntansi_coa c', 'c.id = jd.id_coa')
            ->where('j.tanggal <=', $endDate)
            ->where('j.status', 'Posted')
            ->groupBy('c.kode_akun, j.kode_jenjang')
            ->get()->getResultArray();
            
        $mutasiMap = [];
        foreach($mutasiRaw as $mu) {
            $mutasiMap[$mu['kode_akun']][$mu['kode_jenjang']] = ['debit' => (float)$mu['tot_debit'], 'kredit' => (float)$mu['tot_kredit']];
        }

        $activeUnits = [];
        if ($isMultiColumn) {
            $activeUnits['GLOBAL'] = 'YAYASAN';
            foreach($daftarUnit as $k => $v) $activeUnits[$k] = "UNIT " . strtoupper($k);
        } else {
            $allUnits = array_keys($daftarUnit);
            $allUnits[] = 'GLOBAL';
        }

        $laporan = ['Aset' => [], 'Liabilitas' => [], 'Aset Neto' => []];
        $totalAset = 0; $totalLiabilitas = 0; $totalAsetNeto = 0;
        $totalPerUnit = ['Aset' => [], 'Liabilitas' => [], 'Aset Neto' => []];

        if ($isMultiColumn) {
            foreach($activeUnits as $k => $v) {
                $totalPerUnit['Aset'][$k] = 0; $totalPerUnit['Liabilitas'][$k] = 0; $totalPerUnit['Aset Neto'][$k] = 0;
            }
        }

        foreach ($coaList as $akun) {
            $kode = $akun['kode_akun'];
            $sn   = $akun['saldo_normal'];
            $rowTotal = 0;
            $akun['saldo_per_unit'] = [];

            if ($isMultiColumn) {
                foreach($activeUnits as $uKode => $uName) {
                    $awal = $saldoAwalMap[$kode][$uKode] ?? 0;
                    $deb  = $mutasiMap[$kode][$uKode]['debit'] ?? 0;
                    $kre  = $mutasiMap[$kode][$uKode]['kredit'] ?? 0;
                    $akhir= ($sn == 'Debit') ? ($awal + $deb - $kre) : ($awal + $kre - $deb);

                    $akun['saldo_per_unit'][$uKode] = $akhir;
                    $rowTotal += $akhir;

                    if ($akun['is_parent'] == 0) {
                        $catName = ($akun['kode_kategori'] == '1') ? 'Aset' : (($akun['kode_kategori'] == '2') ? 'Liabilitas' : 'Aset Neto');
                        $totalPerUnit[$catName][$uKode] += $akhir;
                    }
                }
            } else {
                foreach($allUnits as $uKode) {
                    if ($specificUnit && $uKode !== $specificUnit) continue;

                    $awal = $saldoAwalMap[$kode][$uKode] ?? 0;
                    $deb  = $mutasiMap[$kode][$uKode]['debit'] ?? 0;
                    $kre  = $mutasiMap[$kode][$uKode]['kredit'] ?? 0;
                    $akhir= ($sn == 'Debit') ? ($awal + $deb - $kre) : ($awal + $kre - $deb);

                    $rowTotal += $akhir;
                }
            }
            
            $akun['saldo_akhir'] = $rowTotal;

            if ($akun['kode_kategori'] == '1') {
                $laporan['Aset'][] = $akun;
                if ($akun['is_parent'] == 0) $totalAset += $rowTotal;
            } elseif ($akun['kode_kategori'] == '2') {
                $laporan['Liabilitas'][] = $akun;
                if ($akun['is_parent'] == 0) $totalLiabilitas += $rowTotal;
            } elseif ($akun['kode_kategori'] == '3') {
                $laporan['Aset Neto'][] = $akun;
                if ($akun['is_parent'] == 0) $totalAsetNeto += $rowTotal;
            }
        }

        $data = [
            'title'           => 'Laporan Posisi Keuangan',
            'current_module'  => 'akuntansi',
            'sekolah'         => $this->getIdentitasLaporan($filterJenjang),
            'laporan'         => $laporan,
            'totalAset'       => $totalAset,
            'totalLiabilitas' => $totalLiabilitas,
            'totalAsetNeto'   => $totalAsetNeto,
            'endDate'         => $endDate,
            'filterJenjang'   => $filterJenjang,
            'daftarUnit'      => $daftarUnit,
            'isMultiColumn'   => $isMultiColumn,
            'activeUnits'     => $activeUnits,
            'totalPerUnit'    => $totalPerUnit
        ];

        return view('akuntansi/laporan/posisi_keuangan', $data);
    }

    /**
     * 3. LAPORAN AKTIVITAS (LABA/RUGI)
     */
    public function aktivitas()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        if (!$isGlobal) return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak!');

        $filterJenjang = $this->request->getGet('jenjang') ?? 'GLOBAL';
        $startDate     = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate       = $this->request->getGet('end_date') ?? date('Y-m-t');

        $db = \Config\Database::connect();
        $daftarUnit = $this->getDaftarUnit();
        
        $isMultiColumn = ($filterJenjang === 'MULTI');
        $isGlobalTotal = ($filterJenjang === 'GLOBAL');
        $specificUnit  = (!$isMultiColumn && !$isGlobalTotal) ? $filterJenjang : null;

        $coaListRaw = $db->table('akuntansi_coa')
            ->select('kode_akun, MAX(nama_akun) as nama_akun, is_parent, akuntansi_kategori.kode_kategori, akuntansi_kategori.saldo_normal')
            ->join('akuntansi_kategori', 'akuntansi_kategori.id = akuntansi_coa.id_kategori', 'left')
            ->whereIn('akuntansi_kategori.kode_kategori', ['4', '5'])
            ->where('akuntansi_coa.deleted_at', null)
            ->groupBy('kode_akun, is_parent, akuntansi_kategori.kode_kategori, akuntansi_kategori.saldo_normal')
            ->orderBy('kode_akun', 'ASC')
            ->get()->getResultArray();

        $coaList = [];
        foreach ($coaListRaw as $c) {
            $c['nama_akun'] = $this->cleanAccountName($c['nama_akun']);
            $coaList[] = $c;
        }

        $mutasiRaw = $db->table('akuntansi_jurnal_detail jd')
            ->select('c.kode_akun, j.kode_jenjang, SUM(jd.debit) as tot_debit, SUM(jd.kredit) as tot_kredit')
            ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
            ->join('akuntansi_coa c', 'c.id = jd.id_coa')
            ->where('j.tanggal >=', $startDate)
            ->where('j.tanggal <=', $endDate)
            ->where('j.status', 'Posted')
            ->groupBy('c.kode_akun, j.kode_jenjang')
            ->get()->getResultArray();
            
        $mutasiMap = [];
        foreach($mutasiRaw as $mu) {
            $mutasiMap[$mu['kode_akun']][$mu['kode_jenjang']] = ['debit' => (float)$mu['tot_debit'], 'kredit' => (float)$mu['tot_kredit']];
        }

        $activeUnits = [];
        if ($isMultiColumn) {
            $activeUnits['GLOBAL'] = 'YAYASAN';
            foreach($daftarUnit as $k => $v) $activeUnits[$k] = "UNIT " . strtoupper($k);
        } else {
            $allUnits = array_keys($daftarUnit);
            $allUnits[] = 'GLOBAL';
        }

        $laporan = ['Pendapatan' => [], 'Beban' => []];
        $totalPendapatan = 0; $totalBeban = 0;
        $totalPerUnit = ['Pendapatan' => [], 'Beban' => []];
        $surplusPerUnit = [];

        if ($isMultiColumn) {
            foreach($activeUnits as $k => $v) {
                $totalPerUnit['Pendapatan'][$k] = 0; $totalPerUnit['Beban'][$k] = 0; $surplusPerUnit[$k] = 0;
            }
        }

        foreach ($coaList as $akun) {
            $kode = $akun['kode_akun'];
            $sn   = $akun['saldo_normal'];
            $rowTotal = 0;
            $akun['mutasi_per_unit'] = [];

            if ($isMultiColumn) {
                foreach($activeUnits as $uKode => $uName) {
                    $deb = $mutasiMap[$kode][$uKode]['debit'] ?? 0;
                    $kre = $mutasiMap[$kode][$uKode]['kredit'] ?? 0;
                    $mutasi = ($sn == 'Debit') ? ($deb - $kre) : ($kre - $deb);

                    $akun['mutasi_per_unit'][$uKode] = $mutasi;
                    $rowTotal += $mutasi;

                    if ($akun['is_parent'] == 0) {
                        $catName = ($akun['kode_kategori'] == '4') ? 'Pendapatan' : 'Beban';
                        $totalPerUnit[$catName][$uKode] += $mutasi;
                    }
                }
            } else {
                foreach($allUnits as $uKode) {
                    if ($specificUnit && $uKode !== $specificUnit) continue;

                    $deb = $mutasiMap[$kode][$uKode]['debit'] ?? 0;
                    $kre = $mutasiMap[$kode][$uKode]['kredit'] ?? 0;
                    $mutasi = ($sn == 'Debit') ? ($deb - $kre) : ($kre - $deb);

                    $rowTotal += $mutasi;
                }
            }
            
            $akun['mutasi'] = $rowTotal;

            if ($akun['kode_kategori'] == '4') {
                $laporan['Pendapatan'][] = $akun;
                if ($akun['is_parent'] == 0) $totalPendapatan += $rowTotal;
            } elseif ($akun['kode_kategori'] == '5') {
                $laporan['Beban'][] = $akun;
                if ($akun['is_parent'] == 0) $totalBeban += $rowTotal;
            }
        }

        if ($isMultiColumn) {
            foreach($activeUnits as $uKode => $uName) {
                $surplusPerUnit[$uKode] = $totalPerUnit['Pendapatan'][$uKode] - $totalPerUnit['Beban'][$uKode];
            }
        }

        $data = [
            'title'           => 'Laporan Aktivitas',
            'current_module'  => 'akuntansi',
            'sekolah'         => $this->getIdentitasLaporan($filterJenjang),
            'laporan'         => $laporan,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban'      => $totalBeban,
            'surplusDefisit'  => $totalPendapatan - $totalBeban,
            'startDate'       => $startDate,
            'endDate'         => $endDate,
            'filterJenjang'   => $filterJenjang,
            'daftarUnit'      => $daftarUnit,
            'isMultiColumn'   => $isMultiColumn,
            'activeUnits'     => $activeUnits,
            'totalPerUnit'    => $totalPerUnit,
            'surplusPerUnit'  => $surplusPerUnit
        ];

        return view('akuntansi/laporan/aktivitas', $data);
    }

    /**
     * 4. Laporan Perubahan Aset Neto (ISAK 35)
     */
    public function perubahanAsetNeto()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        if (!$isGlobal) return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak!');

        $filterJenjang = $this->request->getGet('jenjang') ?? 'GLOBAL';
        $startDate     = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate       = $this->request->getGet('end_date') ?? date('Y-m-t');

        $db = \Config\Database::connect();

        // Target Jenjang untuk COA Master (Kalau Multi, base-nya dari Global)
        $targetCoaJenjang = ($filterJenjang === 'MULTI') ? 'GLOBAL' : $filterJenjang;

        // Saldo Awal Aset Neto (Sebelum Start Date)
        $builderAsetNeto = $this->coaModel->getCoaBuilder($targetCoaJenjang)
            ->select('akuntansi_coa.*, akuntansi_kategori.kode_kategori')
            ->where('akuntansi_kategori.kode_kategori', '3')
            ->where('akuntansi_coa.is_parent', 0);
            
        $akunAsetNeto = $builderAsetNeto->get()->getResultArray();

        $awalTanpa = 0; $awalDengan = 0;

        foreach ($akunAsetNeto as $akun) {
            $mutasiSblm = $db->table('akuntansi_jurnal_detail jd')
                ->selectSum('debit')->selectSum('kredit')
                ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
                ->where('id_coa', $akun['id'])
                ->where('j.tanggal <', $startDate)
                ->where('j.status', 'Posted');
            
            if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') {
                 $mutasiSblm->where('j.kode_jenjang', $filterJenjang);
            }
            $mutasiSblm = $mutasiSblm->get()->getRow();
                
            $saldoAwal = (float)$akun['saldo_awal'];
            if ($akun['saldo_normal'] == 'Kredit') {
                $saldoAwal += ($mutasiSblm->kredit ?? 0) - ($mutasiSblm->debit ?? 0);
            } else {
                $saldoAwal += ($mutasiSblm->debit ?? 0) - ($mutasiSblm->kredit ?? 0);
            }
            
            if (str_starts_with($akun['kode_akun'], '31')) {
                $awalTanpa += $saldoAwal;
            } elseif (str_starts_with($akun['kode_akun'], '32')) {
                $awalDengan += $saldoAwal;
            }
        }

        // Surplus/Defisit Periode Berjalan
        $coaAktivitas = $this->coaModel->getCoaBuilder($targetCoaJenjang)
            ->select('akuntansi_coa.*, akuntansi_kategori.kode_kategori')
            ->whereIn('akuntansi_kategori.kode_kategori', ['4', '5'])
            ->where('akuntansi_coa.is_parent', 0)
            ->get()->getResultArray();

        $surplusTanpa = 0; $surplusDengan = 0;

        foreach ($coaAktivitas as $akun) {
            $mutasiKini = $db->table('akuntansi_jurnal_detail jd')
                ->selectSum('debit')->selectSum('kredit')
                ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
                ->where('id_coa', $akun['id'])
                ->where('j.tanggal >=', $startDate)
                ->where('j.tanggal <=', $endDate)
                ->where('j.status', 'Posted');
                
            if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') {
                 $mutasiKini->where('j.kode_jenjang', $filterJenjang);
            }
            $mutasiKini = $mutasiKini->get()->getRow();

            $deb = (float)($mutasiKini->debit ?? 0);
            $kre = (float)($mutasiKini->kredit ?? 0);

            if ($akun['kode_kategori'] == '4') {
                $net = $kre - $deb;
                if (str_starts_with($akun['kode_akun'], '41')) {
                    $surplusTanpa += $net;
                } elseif (str_starts_with($akun['kode_akun'], '42')) {
                    $surplusDengan += $net;
                } else {
                    $surplusTanpa += $net; 
                }
            } else {
                $net = $deb - $kre;
                $surplusTanpa -= $net; // Beban mengurangi surplus tanpa pembatasan
            }
        }

        $data = [
            'title'          => 'Laporan Perubahan Aset Neto',
            'current_module' => 'akuntansi',
            'sekolah'        => $this->getIdentitasLaporan($filterJenjang),
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $this->getDaftarUnit(),
            'awalTanpa'      => $awalTanpa,
            'surplusTanpa'   => $surplusTanpa,
            'akhirTanpa'     => $awalTanpa + $surplusTanpa,
            'awalDengan'     => $awalDengan,
            'surplusDengan'  => $surplusDengan,
            'akhirDengan'    => $awalDengan + $surplusDengan,
            'totalAwal'      => $awalTanpa + $awalDengan,
            'totalSurplus'   => $surplusTanpa + $surplusDengan,
            'totalAkhir'     => ($awalTanpa + $surplusTanpa) + ($awalDengan + $surplusDengan),
        ];

        return view('akuntansi/laporan/perubahan_aset_neto', $data);
    }

    /**
     * 5. Laporan Arus Kas (Sederhana Metode Langsung)
     */
    public function arusKas()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        if (!$isGlobal) return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak!');

        $filterJenjang = $this->request->getGet('jenjang') ?? 'GLOBAL';
        $startDate     = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate       = $this->request->getGet('end_date') ?? date('Y-m-t');

        $db = \Config\Database::connect();
        $targetCoaJenjang = ($filterJenjang === 'MULTI') ? 'GLOBAL' : $filterJenjang;

        $akunKasBank = $this->coaModel->getCoaBuilder($targetCoaJenjang)
            ->groupStart()
                ->like('akuntansi_coa.nama_akun', 'Kas', 'both')
                ->orLike('akuntansi_coa.nama_akun', 'Bank', 'both')
            ->groupEnd()
            ->where('akuntansi_coa.is_parent', 0)
            ->get()->getResultArray();

        $idsKasBank = array_column($akunKasBank, 'id');
        
        $saldoAwalKas = 0;
        $kasMasuk = 0;
        $kasKeluar = 0;

        if (!empty($idsKasBank)) {
            foreach ($akunKasBank as $akun) {
                $mutasiSblm = $db->table('akuntansi_jurnal_detail jd')
                    ->selectSum('debit')->selectSum('kredit')
                    ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
                    ->where('id_coa', $akun['id'])
                    ->where('j.tanggal <', $startDate)
                    ->where('j.status', 'Posted');
                    
                if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') $mutasiSblm->where('j.kode_jenjang', $filterJenjang);
                $mutasiSblm = $mutasiSblm->get()->getRow();
                    
                $saldoAwal = (float)$akun['saldo_awal'];
                if ($akun['saldo_normal'] == 'Debit') {
                    $saldoAwal += ($mutasiSblm->debit ?? 0) - ($mutasiSblm->kredit ?? 0);
                } else {
                    $saldoAwal += ($mutasiSblm->kredit ?? 0) - ($mutasiSblm->debit ?? 0);
                }
                $saldoAwalKas += $saldoAwal;
            }

            $mutasiKas = $db->table('akuntansi_jurnal_detail jd')
                ->selectSum('debit')->selectSum('kredit')
                ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
                ->whereIn('id_coa', $idsKasBank)
                ->where('j.tanggal >=', $startDate)
                ->where('j.tanggal <=', $endDate)
                ->where('j.status', 'Posted');
                
            if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') $mutasiKas->where('j.kode_jenjang', $filterJenjang);
            $mutasiKas = $mutasiKas->get()->getRow();

            $kasMasuk = (float)($mutasiKas->debit ?? 0);
            $kasKeluar = (float)($mutasiKas->kredit ?? 0);
        }

        $data = [
            'title'          => 'Laporan Arus Kas',
            'current_module' => 'akuntansi',
            'sekolah'        => $this->getIdentitasLaporan($filterJenjang),
            'saldoAwalKas'   => $saldoAwalKas,
            'kasMasuk'       => $kasMasuk,
            'kasKeluar'      => $kasKeluar,
            'kenaikanKas'    => $kasMasuk - $kasKeluar,
            'saldoAkhirKas'  => $saldoAwalKas + ($kasMasuk - $kasKeluar),
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $this->getDaftarUnit()
        ];

        return view('akuntansi/laporan/arus_kas', $data);
    }

    /**
     * 6. Catatan Atas Laporan Keuangan (CALK)
     */
    public function calk()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        if (!$isGlobal) return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak!');

        $filterJenjang = $this->request->getGet('jenjang') ?? 'GLOBAL';
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $sekolah = $this->getIdentitasLaporan($filterJenjang);

        $data = [
            'title'          => 'Catatan Atas Laporan Keuangan',
            'current_module' => 'akuntansi',
            'sekolah'        => $sekolah,
            'tahun'          => $tahun,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $this->getDaftarUnit()
        ];

        return view('akuntansi/laporan/calk', $data);
    }
}