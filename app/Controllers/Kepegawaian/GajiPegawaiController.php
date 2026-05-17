<?php

namespace App\Controllers\Kepegawaian;

use App\Controllers\BaseController;
use App\Models\Kepegawaian\GajiPegawaiModel;
use App\Models\Kepegawaian\AbsensiPegawaiModel;
use App\Models\JenjangModel;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

/**
 * Controller GajiPegawaiController
 * Mengelola Master Setting Gaji & Proses Payroll (Bulanan).
 * STATUS: FIXED V4.1 (Perbaikan Variabel Pager & Inisialisasi Paginasi Manual)
 */
class GajiPegawaiController extends BaseController
{
    protected GajiPegawaiModel $gajiModel;
    protected JenjangModel $jenjangModel;
    protected AbsensiPegawaiModel $absensiModel; 
    protected SettingsModel $settingsModel; 
    
    private $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->gajiModel     = new GajiPegawaiModel();
        $this->absensiModel  = new AbsensiPegawaiModel(); 
        $this->settingsModel = new SettingsModel(); 
        
        // Fail-safe JenjangModel
        if (file_exists(APPPATH . 'Models/MasterData/JenjangModel.php')) {
            $this->jenjangModel = model('App\Models\MasterData\JenjangModel');
        } elseif (file_exists(APPPATH . 'Models/JenjangModel.php')) {
            $this->jenjangModel = new JenjangModel();
        } else {
            $this->jenjangModel = new class extends \CodeIgniter\Model {
                protected $table = 'jenjang_sekolah';
                protected $returnType = 'array';
                public function findAll(?int $limit = null, int $offset = 0) { return []; }
            };
        }
    }

    /**
     * Halaman Index: Daftar Pegawai & Estimasi Gaji
     */
    public function index(): string
    {
        $session = session();
        $sessionUnit = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole    = strtolower($session->get('role_name') ?? session()->get('role') ?? '');
        $isSuperAdmin = in_array($userRole, ['superadmin', 'yayasan']);

        $unitParam   = $this->request->getGet('unit');
        $search      = $this->request->getGet('search');
        $tipePegawai = $this->request->getGet('tipe') ?? 'guru'; // guru | staff | penunjang | all
        $page        = $this->request->getGet('page_pegawai') ?? 1;
        $perPage     = 20;

        if (!$isSuperAdmin) {
            $unitParam = $sessionUnit;
        }

        // Pisahkan antara "Semua Unit" (null) dengan "Unit Yayasan" (GLOBAL)
        $kodeJenjang = ($unitParam === '' || $unitParam === null) ? null : $unitParam;

        $db = \Config\Database::connect();
        $builder = $db->table('pegawai')->where('deleted_at', null)->where('status_aktif', 'aktif');

        // Filter Jenis Pegawai (Mencakup Penunjang)
        if (in_array($tipePegawai, ['guru', 'staff', 'penunjang'])) {
            $builder->where('jenis_pegawai', $tipePegawai);
        }

        // Filter Unit
        if ($kodeJenjang) {
            $builder->where('kode_jenjang', $kodeJenjang);
        }

        // Filter Pencarian
        if ($search) {
            $builder->groupStart()
                    ->like('nama_lengkap', $search)
                    ->orLike('nip', $search)
                    ->orLike('nik', $search)
                    ->groupEnd();
        }

        $countQuery = clone $builder;
        $totalRows  = $countQuery->countAllResults();
        
        $pager = \Config\Services::pager();
        
        // FIX: Pancing pager agar mengetahui total halaman (karena kita pakai Query Builder Manual)
        $pager->makeLinks($page, $perPage, $totalRows, 'default', 0, 'default');
        
        $listPegawai = $builder->orderBy('nama_lengkap', 'ASC')
                               ->limit($perPage, ($page - 1) * $perPage)
                               ->get()
                               ->getResultArray();
        
        // Hitung Estimasi Gaji per Pegawai
        foreach ($listPegawai as &$p) {
            $p['total_pendapatan'] = $this->gajiModel->sumGajiByType($p['id'], 'pendapatan');
            $p['total_potongan']   = $this->gajiModel->sumGajiByType($p['id'], 'potongan');
            $p['thp_estimasi']     = $p['total_pendapatan'] - $p['total_potongan'];
        }

        // Statistik Dashboard Gaji
        $statsBuilder = $db->table('pegawai p')
            ->join('gaji_pegawai gp', 'gp.id_pegawai = p.id AND gp.is_active = 1 AND gp.deleted_at IS NULL', 'left')
            ->join('komponen_gaji kg', 'kg.id = gp.id_komponen', 'left')
            ->where('p.status_aktif', 'aktif')->where('p.deleted_at', null);

        if ($kodeJenjang) $statsBuilder->where('p.kode_jenjang', $kodeJenjang);
        if (in_array($tipePegawai, ['guru', 'staff', 'penunjang'])) {
            $statsBuilder->where('p.jenis_pegawai', $tipePegawai);
        }

        $stats = $statsBuilder->select('
            COUNT(DISTINCT p.id) as total_pegawai,
            SUM(CASE WHEN kg.tipe = 1 THEN gp.jumlah_set ELSE 0 END) as est_pendapatan,
            SUM(CASE WHEN kg.tipe = 2 THEN gp.jumlah_set ELSE 0 END) as est_potongan
        ')->get()->getRow();

        $jenjangList = [];
        if ($isSuperAdmin) {
            $jenjangList = $this->jenjangModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        }

        $data = [
            'title'          => 'Manajemen Gaji Pegawai',
            'current_module' => 'kepegawaian',
            'list_pegawai'   => $listPegawai,
            // FIX: Variabel ini HARUS bernama 'pager' agar sinkron dengan View
            'pager'          => $pager, 
            'total_rows'     => $totalRows,
            'current_page'   => $page,
            'per_page'       => $perPage,
            'stats'          => $stats,
            'tipe_pegawai'   => $tipePegawai,
            'current_unit'   => $unitParam ?? '',
            'session_unit'   => $sessionUnit,
            'is_global'      => $isSuperAdmin,
            'jenjang_list'   => $jenjangList
        ];

        return view('kepegawaian/gaji_pegawai/index', $data);
    }

    /**
     * Halaman Kelola Komponen Gaji Individu
     */
    public function kelola($idPegawai): string
    {
        $db = \Config\Database::connect();
        
        // Cari pegawai langsung ke tabel utama, mencakup Yayasan & Penunjang
        $pegawai = $db->table('pegawai')->where('id', $idPegawai)->where('deleted_at', null)->get()->getRowArray();
        
        if (!$pegawai) throw PageNotFoundException::forPageNotFound();

        $sessionUnit = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $userRole    = strtolower(session()->get('role_name') ?? session()->get('role') ?? '');
        $isSuperAdmin = in_array($userRole, ['superadmin', 'yayasan']);

        if (!$isSuperAdmin && strtoupper($pegawai['kode_jenjang']) !== $sessionUnit) {
            throw PageNotFoundException::forPageNotFound("Akses Ditolak: Pegawai ini berada di unit yang berbeda.");
        }

        $listGaji = $this->gajiModel->getGajiByPegawai($idPegawai);
        
        // Ambil master komponen sesuai unit pegawai
        $masterKomponen = $db->table('komponen_gaji')
                             ->groupStart()
                                ->where('kode_jenjang', $pegawai['kode_jenjang'])
                                ->orWhere('kode_jenjang', 'GLOBAL')
                                ->orWhere('kode_jenjang', 'YAYASAN')
                             ->groupEnd()
                             ->where('is_aktif', 1)
                             ->get()->getResultArray();

        $data = [
            'title'          => 'Setting Gaji: ' . $pegawai['nama_lengkap'],
            'current_module' => 'kepegawaian',
            'pegawai'        => $pegawai,
            'list_gaji'      => $listGaji,
            'master_komponen'=> $masterKomponen,
            'total_pendapatan' => $this->gajiModel->sumGajiByType($idPegawai, 'pendapatan'),
            'total_potongan'   => $this->gajiModel->sumGajiByType($idPegawai, 'potongan')
        ];

        return view('kepegawaian/gaji_pegawai/kelola', $data);
    }

    /**
     * Halaman Riwayat Slip Gaji
     */
    public function riwayat($idPegawai): string
    {
        $db = \Config\Database::connect();
        $pegawai = $db->table('pegawai')->where('id', $idPegawai)->where('deleted_at', null)->get()->getRowArray();
        
        if (!$pegawai) throw PageNotFoundException::forPageNotFound();

        $riwayatGaji = $db->table('riwayat_gaji_pegawai')
                          ->where('id_pegawai', $idPegawai)
                          ->orderBy('tahun', 'DESC')
                          ->orderBy('bulan', 'DESC')
                          ->limit(24) 
                          ->get()->getResultArray();

        $data = [
            'title'          => 'Riwayat Gaji: ' . $pegawai['nama_lengkap'],
            'current_module' => 'kepegawaian',
            'pegawai'        => $pegawai,
            'riwayat_gaji'   => $riwayatGaji
        ];

        return view('kepegawaian/gaji_pegawai/riwayat', $data);
    }

    /**
     * Proses Generate Gaji Bulanan (Simpan ke Riwayat)
     */
    public function generate(): RedirectResponse
    {
        if ($this->request->getMethod() !== 'post') return redirect()->back();

        $bulan       = $this->request->getPost('bulan');
        $tahun       = $this->request->getPost('tahun');
        $tipePegawai = $this->request->getPost('tipe'); // guru, staff, penunjang
        $unitParam   = $this->request->getPost('unit');

        $session = session();
        $sessionUnit = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole    = strtolower($session->get('role_name') ?? session()->get('role') ?? '');
        $isSuperAdmin = in_array($userRole, ['superadmin', 'yayasan']);

        if (!$isSuperAdmin) {
            $unitParam = $sessionUnit;
        }

        $kodeJenjang = ($unitParam === '' || $unitParam === null) ? null : $unitParam;

        $db = \Config\Database::connect();
        $builder = $db->table('pegawai')->where('deleted_at', null)->where('status_aktif', 'aktif');

        if (in_array($tipePegawai, ['guru', 'staff', 'penunjang'])) {
            $builder->where('jenis_pegawai', $tipePegawai);
        }

        if ($kodeJenjang) $builder->where('kode_jenjang', $kodeJenjang);
        
        $pegawaiList = $builder->get()->getResultArray();

        if (empty($pegawaiList)) {
            return redirect()->back()->with('error', 'Tidak ada pegawai aktif yang sesuai kriteria.');
        }
        
        // Ambil rekap absensi
        $absensiRaw = $this->absensiModel->getRekapBulanan($bulan, $tahun, $tipePegawai, $kodeJenjang);
        $absensiMap = [];
        foreach ($absensiRaw as $abs) {
            $absensiMap[$abs->id_pegawai] = $abs;
        }

        $riwayatBatch = [];
        $time = Time::now()->toDateTimeString();
        $generatedCount = 0;

        $db->transStart();

        foreach ($pegawaiList as $p) {
            $noTransaksi = 'SLIP/' . $tahun . $bulan . '/' . str_pad($p['id'], 5, '0', STR_PAD_LEFT);
            
            $existing = $db->table('riwayat_gaji_pegawai')->where('no_transaksi', $noTransaksi)->countAllResults();
            if ($existing > 0) continue;

            $komponen = $db->table('gaji_pegawai gp')
                ->select('gp.jumlah_set, kg.tipe, kg.metode_hitung')
                ->join('komponen_gaji kg', 'kg.id = gp.id_komponen')
                ->where('gp.id_pegawai', $p['id'])
                ->where('gp.is_active', 1)
                ->where('gp.deleted_at', null)
                ->get()->getResultArray();

            $pendapatan = 0; $potongan = 0;
            // Toleransi jika ID Pegawai absensi menggunakan object/array
            $kehadiran = isset($absensiMap[$p['id']]) ? ($absensiMap[$p['id']]->jml_hadir ?? $absensiMap[$p['id']]['jml_hadir'] ?? 0) : 0;

            foreach ($komponen as $k) {
                $subtotal = (float)$k['jumlah_set'];
                if ($k['metode_hitung'] === 'variabel') $subtotal *= $kehadiran;
                if ($k['tipe'] == 1) $pendapatan += $subtotal; else $potongan += $subtotal;
            }

            $jabatan = !empty($p['jenis_ptk']) ? $p['jenis_ptk'] : strtoupper($p['jenis_pegawai']);

            $riwayatBatch[] = [
                'no_transaksi'     => $noTransaksi,
                'id_pegawai'       => $p['id'],
                'nama_pegawai'     => $p['nama_lengkap'],
                'jabatan_pegawai'  => $jabatan,
                'kode_jenjang'     => $p['kode_jenjang'],
                'bulan'            => $bulan,
                'tahun'            => $tahun,
                'total_pendapatan' => $pendapatan,
                'total_potongan'   => $potongan,
                'gaji_bersih'      => $pendapatan - $potongan,
                'status_bayar'     => 'Belum Dibayar',
                'metode_bayar'     => 'Transfer',
                'catatan'          => 'Gaji Periode ' . $bulan . '-' . $tahun,
                'created_at'       => $time,
                'updated_at'       => $time
            ];
            $generatedCount++;
        }

        if (!empty($riwayatBatch)) {
            foreach (array_chunk($riwayatBatch, 50) as $chunk) {
                $db->table('riwayat_gaji_pegawai')->insertBatch($chunk);
            }
        }
        $db->transComplete();

        if ($generatedCount > 0) return redirect()->back()->with('message', $generatedCount . ' Slip gaji berhasil digenerate.');
        return redirect()->back()->with('error', 'Gaji periode ini mungkin sudah pernah digenerate.');
    }

    public function simpanKomponen(): RedirectResponse
    {
        $idPegawai = $this->request->getPost('id_pegawai');
        $idKomponen = $this->request->getPost('id_komponen');
        $jumlah = $this->request->getPost('jumlah');

        $db = \Config\Database::connect();
        $pegawai = $db->table('pegawai')->where('id', $idPegawai)->get()->getRowArray();
        
        if (!$pegawai) return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');

        $existing = $this->gajiModel->where(['id_pegawai' => $idPegawai, 'id_komponen' => $idKomponen])->first();
        
        $data = ['id_pegawai' => $idPegawai, 'kode_jenjang' => $pegawai['kode_jenjang'], 'id_komponen' => $idKomponen, 'jumlah_set' => $jumlah, 'is_active' => 1];

        if ($existing) $this->gajiModel->update($existing['id'], $data);
        else $this->gajiModel->insert($data);

        return redirect()->back()->with('message', 'Komponen gaji berhasil disimpan.');
    }

    public function hapusKomponen($id): RedirectResponse
    {
        if ($this->gajiModel->delete($id)) return redirect()->back()->with('message', 'Komponen gaji dihapus.');
        return redirect()->back()->with('error', 'Gagal menghapus komponen.');
    }
    
    public function rekap(): string
    {
        $bulan       = $this->request->getGet('bulan') ?? date('m');
        $tahun       = $this->request->getGet('tahun') ?? date('Y');
        $tipePegawai = $this->request->getGet('tipe') ?? 'guru';

        $session = session();
        $sessionUnit = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole    = strtolower($session->get('role_name') ?? session()->get('role') ?? '');
        $isSuperAdmin = in_array($userRole, ['superadmin', 'yayasan']);

        $unitParam   = $this->request->getGet('unit');
        
        if (!$isSuperAdmin) {
            $unitParam = $sessionUnit;
        }

        $kodeJenjang = ($unitParam === '' || $unitParam === null) ? null : $unitParam;

        $db = \Config\Database::connect();
        $builder = $db->table('pegawai')->where('deleted_at', null)->where('status_aktif', 'aktif');

        if (in_array($tipePegawai, ['guru', 'staff', 'penunjang'])) {
            $builder->where('jenis_pegawai', $tipePegawai);
        }

        if ($kodeJenjang) $builder->where('kode_jenjang', $kodeJenjang);
        
        $pegawaiList = $builder->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();
         
        $absensiRaw = $this->absensiModel->getRekapBulanan($bulan, $tahun, $tipePegawai, $kodeJenjang);
        $absensiMap = [];
        foreach ($absensiRaw as $abs) {
            $absensiMap[$abs->id_pegawai] = $abs;
        }
         
        $payrollPreview = [];
        $grandTotal = 0;
         
        foreach ($pegawaiList as $p) {
            $komponen = $db->table('gaji_pegawai gp')
                ->select('gp.jumlah_set, kg.tipe, kg.metode_hitung')
                ->join('komponen_gaji kg', 'kg.id = gp.id_komponen')
                ->where('gp.id_pegawai', $p['id'])
                ->where('gp.is_active', 1)
                ->where('gp.deleted_at', null)
                ->get()->getResultArray();

            $pendapatan = 0; $potongan = 0;
            $kehadiran = isset($absensiMap[$p['id']]) ? ($absensiMap[$p['id']]->jml_hadir ?? $absensiMap[$p['id']]['jml_hadir'] ?? 0) : 0;

            foreach ($komponen as $k) {
                $subtotal = (float)$k['jumlah_set'];
                if ($k['metode_hitung'] === 'variabel') $subtotal *= $kehadiran;
                
                if ($k['tipe'] == 1) $pendapatan += $subtotal;
                else $potongan += $subtotal;
            }
            $thp = $pendapatan - $potongan;
            $grandTotal += $thp;
            $payrollPreview[] = ['pegawai' => $p, 'kehadiran' => $kehadiran, 'pendapatan' => $pendapatan, 'potongan' => $potongan, 'thp' => $thp];
        }
         
        $jenjangList = [];
        if ($isSuperAdmin) {
            $jenjangList = $this->jenjangModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        }

        $data = [
            'title' => 'Rekapitulasi Payroll', 'current_module' => 'kepegawaian',
            'payroll' => $payrollPreview, 'grand_total' => $grandTotal,
            'bulan' => $bulan, 'tahun' => $tahun, 'tipe_pegawai' => $tipePegawai,
            'current_unit' => $unitParam ?? '',
            'session_unit' => $sessionUnit, 'is_global' => $isSuperAdmin,
            'jenjang_list' => $jenjangList
        ];
        return view('kepegawaian/gaji_pegawai/rekap', $data);
    }

    public function slip($id): string
    {
        $db = \Config\Database::connect();
        $riwayat = $db->table('riwayat_gaji_pegawai')->where('id', $id)->get()->getRowArray();
        if (!$riwayat) throw PageNotFoundException::forPageNotFound("Data slip gaji tidak ditemukan.");
        
        $sekolah = $this->settingsModel->getSettingsAsArray($riwayat['kode_jenjang']);
        if (empty($sekolah['nama_sekolah'])) $sekolah = $this->settingsModel->getSettingsAsArray('GLOBAL');

        $kehadiran = $db->table('absensi_pegawai')
                        ->where('id_pegawai', $riwayat['id_pegawai'])
                        ->where('MONTH(tanggal)', $riwayat['bulan'])
                        ->where('YEAR(tanggal)', $riwayat['tahun'])
                        ->where('status', 'hadir')
                        ->where('deleted_at', null)
                        ->countAllResults();

        $komponenRaw = $db->table('gaji_pegawai gp')
                          ->select('gp.jumlah_set, kg.nama_komponen, kg.tipe, kg.metode_hitung')
                          ->join('komponen_gaji kg', 'kg.id = gp.id_komponen')
                          ->where('gp.id_pegawai', $riwayat['id_pegawai'])
                          ->where('gp.is_active', 1) 
                          ->where('gp.deleted_at', null) 
                          ->get()->getResultArray();

        $detail = ['pendapatan' => [], 'potongan' => []];
        foreach ($komponenRaw as $k) {
            $nominal = (float)$k['jumlah_set'];
            $keterangan = '';
            if ($k['metode_hitung'] === 'variabel') {
                $subtotal = $nominal * $kehadiran;
                $keterangan = "({$kehadiran} x Rp " . number_format($nominal, 0, ',', '.') . ")";
            } else {
                $subtotal = $nominal;
            }
            $item = ['nama' => $k['nama_komponen'], 'nominal' => $subtotal, 'keterangan' => $keterangan];
            if ($k['tipe'] == 1) $detail['pendapatan'][] = $item;
            else $detail['potongan'][] = $item;
        }

        $namaBulan = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];

        $data = [
            'riwayat' => $riwayat, 'sekolah' => $sekolah, 'detail' => $detail, 'kehadiran' => $kehadiran,
            'nama_bulan' => $namaBulan[$riwayat['bulan']] ?? $riwayat['bulan']
        ];
        return view('kepegawaian/gaji_pegawai/slip', $data);
    }
    
    public function bayar(): RedirectResponse
    {
        $id = $this->request->getPost('id');
        $tanggal = $this->request->getPost('tanggal_bayar');
        $metode = $this->request->getPost('metode_bayar');

        if (!$id) return redirect()->back()->with('error', 'ID Transaksi tidak valid.');

        $db = \Config\Database::connect();
        
        $data = [
            'status_bayar'  => 'Dibayar',
            'tanggal_bayar' => $tanggal,
            'metode_bayar'  => $metode,
            'updated_at'    => \CodeIgniter\I18n\Time::now()->toDateTimeString()
        ];

        $db->table('riwayat_gaji_pegawai')->where('id', $id)->update($data);

        return redirect()->back()->with('message', 'Pembayaran gaji berhasil dicatat.');
    }
}