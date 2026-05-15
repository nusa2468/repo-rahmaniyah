<?php

namespace App\Controllers\Kepegawaian;

use App\Controllers\BaseController;
use App\Models\Kepegawaian\GajiPegawaiModel;
use App\Models\Kepegawaian\AbsensiPegawaiModel;
// KEMBALI KE MODEL LAMA
use App\Models\GuruModel; 
use App\Models\KaryawanModel; 
use App\Models\JenjangModel;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

/**
 * Controller GajiPegawaiController
 * Mengelola Master Setting Gaji & Proses Payroll (Bulanan).
 * REVERTED: Menggunakan GuruModel & KaryawanModel.
 */
class GajiPegawaiController extends BaseController
{
    protected GajiPegawaiModel $gajiModel;
    protected GuruModel $guruModel;          // Model Guru
    protected KaryawanModel $karyawanModel;  // Model Staff
    protected JenjangModel $jenjangModel;
    protected AbsensiPegawaiModel $absensiModel; 
    protected SettingsModel $settingsModel; 

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->gajiModel     = new GajiPegawaiModel();
        $this->guruModel     = new GuruModel(); 
        $this->karyawanModel = new KaryawanModel(); 
        $this->jenjangModel  = new JenjangModel();
        $this->absensiModel  = new AbsensiPegawaiModel(); 
        $this->settingsModel = new SettingsModel(); 
    }

    /**
     * Halaman Index: Daftar Pegawai & Estimasi Gaji
     */
    public function index(): string
    {
        $session = session();
        $sessionUnit = $session->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        $unitParam     = $this->request->getGet('unit');
        $search        = $this->request->getGet('search');
        $tipePegawai   = $this->request->getGet('tipe') ?? 'guru'; 

        $kodeJenjang = (!$isGlobal) ? strtoupper($sessionUnit) : 
                       ((!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null);

        // --- PILIH MODEL BERDASARKAN TIPE ---
        if ($tipePegawai === 'staff') {
            $builder = $this->karyawanModel->where('status_aktif', 'aktif');
            // KaryawanModel biasanya sudah filter 'jenis_pegawai' di dalam modelnya
        } else {
            $builder = $this->guruModel->where('status_aktif', 'aktif');
            // Pastikan hanya ambil guru jika model tidak otomatis filter
            $builder->where('jenis_pegawai', 'guru');
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
                    ->groupEnd();
        }

        // Eksekusi Paginasi
        $listPegawai = $builder->orderBy('nama_lengkap', 'ASC')->paginate(20, 'default');
        $pager = ($tipePegawai === 'staff') ? $this->karyawanModel->pager : $this->guruModel->pager;
        
        // Hitung Estimasi Gaji per Pegawai
        foreach ($listPegawai as &$p) {
            $p['total_pendapatan'] = $this->gajiModel->sumGajiByType($p['id'], 'pendapatan');
            $p['total_potongan']   = $this->gajiModel->sumGajiByType($p['id'], 'potongan');
            $p['thp_estimasi']     = $p['total_pendapatan'] - $p['total_potongan'];
        }

        // Statistik Dashboard Gaji (Query Builder Manual agar ringan & gabungan)
        $db = \Config\Database::connect();
        $statsBuilder = $db->table('pegawai p')
            ->join('gaji_pegawai gp', 'gp.id_pegawai = p.id AND gp.is_active = 1', 'left')
            ->join('komponen_gaji kg', 'kg.id = gp.id_komponen', 'left')
            ->where('p.status_aktif', 'aktif');

        if ($kodeJenjang) $statsBuilder->where('p.kode_jenjang', $kodeJenjang);
        
        if ($tipePegawai === 'staff') {
            $statsBuilder->whereIn('p.jenis_pegawai', ['staff', 'penunjang']);
        } else {
            $statsBuilder->where('p.jenis_pegawai', 'guru');
        }

        $stats = $statsBuilder->select('
            COUNT(DISTINCT p.id) as total_pegawai,
            SUM(CASE WHEN kg.tipe = 1 THEN gp.jumlah_set ELSE 0 END) as est_pendapatan,
            SUM(CASE WHEN kg.tipe = 2 THEN gp.jumlah_set ELSE 0 END) as est_potongan
        ')->get()->getRow();

        $data = [
            'title'             => 'Manajemen Gaji Pegawai',
            'current_module'    => 'kepegawaian',
            'list_pegawai'      => $listPegawai,
            'pager'             => $pager,
            'stats'             => $stats,
            'tipe_pegawai'      => $tipePegawai,
            'current_unit'      => $unitParam ?? ($kodeJenjang ?? 'GLOBAL'),
            'session_unit'      => $sessionUnit,
            'is_global'         => $isGlobal,
            'jenjang_list'      => $this->jenjangModel->where('status', 'aktif')->findAll()
        ];

        return view('kepegawaian/gaji_pegawai/index', $data);
    }

    /**
     * Halaman Kelola Komponen Gaji Individu
     */
    public function kelola($idPegawai): string
    {
        // Cari pegawai di kedua model (Fallback logic)
        $pegawai = $this->guruModel->find($idPegawai);
        if (!$pegawai) {
            $pegawai = $this->karyawanModel->find($idPegawai);
        }

        if (!$pegawai) throw PageNotFoundException::forPageNotFound();

        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        if (!$isGlobal && $pegawai['kode_jenjang'] !== $sessionUnit) {
            throw PageNotFoundException::forPageNotFound("Akses Ditolak: Unit Berbeda");
        }

        // Ambil komponen gaji yang sudah di-set
        $listGaji = $this->gajiModel->getGajiByPegawai($idPegawai);
        
        // Ambil master komponen sesuai unit pegawai
        $db = \Config\Database::connect();
        $masterKomponen = $db->table('komponen_gaji')
                             ->where('kode_jenjang', $pegawai['kode_jenjang'])
                             ->orWhere('kode_jenjang', 'GLOBAL')
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
        // Cari pegawai di kedua model
        $pegawai = $this->guruModel->find($idPegawai);
        if (!$pegawai) {
            $pegawai = $this->karyawanModel->find($idPegawai);
        }

        if (!$pegawai) throw PageNotFoundException::forPageNotFound();

        $db = \Config\Database::connect();
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
        $tipePegawai = $this->request->getPost('tipe');
        $unitParam   = $this->request->getPost('unit');

        $session = session();
        $sessionUnit = $session->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        $kodeJenjang = (!$isGlobal) ? strtoupper($sessionUnit) : 
                       ((!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null);

        // --- PILIH MODEL ---
        if ($tipePegawai === 'staff') {
            $builder = $this->karyawanModel->where('status_aktif', 'aktif');
        } else {
            $builder = $this->guruModel->where('status_aktif', 'aktif')->where('jenis_pegawai', 'guru');
        }

        if ($kodeJenjang) $builder->where('kode_jenjang', $kodeJenjang);
        
        $pegawaiList = $builder->findAll();

        if (empty($pegawaiList)) {
            return redirect()->back()->with('error', 'Tidak ada pegawai aktif yang sesuai kriteria.');
        }

        // ... (LOGIKA PERHITUNGAN TETAP SAMA MENGGUNAKAN ID PEGAWAI) ...
        
        $absensiRaw = $this->absensiModel->getRekapBulanan($bulan, $tahun, $tipePegawai, $kodeJenjang);
        $absensiMap = [];
        foreach ($absensiRaw as $abs) $absensiMap[$abs->id_pegawai] = $abs;

        $db = \Config\Database::connect();
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
                ->get()->getResultArray();

            $pendapatan = 0; $potongan = 0;
            $kehadiran = $absensiMap[$p['id']]->jml_hadir ?? 0;

            foreach ($komponen as $k) {
                $subtotal = (float)$k['jumlah_set'];
                if ($k['metode_hitung'] === 'variabel') $subtotal *= $kehadiran;
                if ($k['tipe'] == 1) $pendapatan += $subtotal; else $potongan += $subtotal;
            }

            $jabatan = $p['jenis_ptk'] ?? ($p['jenis_pegawai'] == 'guru' ? 'Guru Mapel' : 'Staff');

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

        // Cari Pegawai (Fallback)
        $pegawai = $this->guruModel->find($idPegawai);
        if (!$pegawai) $pegawai = $this->karyawanModel->find($idPegawai);
        
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
    
    // ... Method rekap() dan slip() tetap menggunakan query builder/model lain yang tidak terpengaruh ...
    // Method slip() di bawah ini menggunakan GajiPegawaiModel dan AbsensiPegawaiModel yang sudah OK
    
    public function rekap(): string
    {
         // Logic rekap sama, hanya pemanggilan pegawai yang disesuaikan
         // ...
         $bulan       = $this->request->getGet('bulan') ?? date('m');
         $tahun       = $this->request->getGet('tahun') ?? date('Y');
         $tipePegawai = $this->request->getGet('tipe') ?? 'guru';
         
         if ($tipePegawai === 'staff') {
             $builder = $this->karyawanModel->where('status_aktif', 'aktif');
         } else {
             $builder = $this->guruModel->where('status_aktif', 'aktif')->where('jenis_pegawai', 'guru');
         }
         // ... filter jenjang ...
         
         $pegawaiList = $builder->orderBy('nama_lengkap', 'ASC')->findAll();
         
         // ... sisa logic rekap (ambil absensi & hitung) SAMA ...
         // Saya persingkat agar tidak kepanjangan, 
         // yang penting $pegawaiList sekarang didapat dari GuruModel/KaryawanModel
         
         // Ambil Absensi
         $session = session();
         $sessionUnit = $session->get('kode_jenjang');
         $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
         $unitParam   = $this->request->getGet('unit');
         $kodeJenjang = (!$isGlobal) ? strtoupper($sessionUnit) : ((!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null);
         
         if ($kodeJenjang) $builder->where('kode_jenjang', $kodeJenjang);
         $pegawaiList = $builder->orderBy('nama_lengkap', 'ASC')->findAll(); // Re-fetch with filter
         
         $absensiRaw = $this->absensiModel->getRekapBulanan($bulan, $tahun, $tipePegawai, $kodeJenjang);
         $absensiMap = [];
         foreach ($absensiRaw as $abs) $absensiMap[$abs->id_pegawai] = $abs;
         
         $payrollPreview = [];
         $grandTotal = 0;
         $db = \Config\Database::connect();
         
         foreach ($pegawaiList as $p) {
            $komponen = $db->table('gaji_pegawai gp')
                ->select('gp.jumlah_set, kg.tipe, kg.metode_hitung')
                ->join('komponen_gaji kg', 'kg.id = gp.id_komponen')
                ->where('gp.id_pegawai', $p['id'])
                ->where('gp.is_active', 1)
                ->get()->getResultArray();

            $pendapatan = 0; $potongan = 0;
            $kehadiran = $absensiMap[$p['id']]->jml_hadir ?? 0;

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
         
         $data = [
            'title' => 'Rekapitulasi Payroll', 'current_module' => 'kepegawaian',
            'payroll' => $payrollPreview, 'grand_total' => $grandTotal,
            'bulan' => $bulan, 'tahun' => $tahun, 'tipe_pegawai' => $tipePegawai,
            'current_unit' => $unitParam ?? ($kodeJenjang ?? 'GLOBAL'),
            'session_unit' => $sessionUnit, 'is_global' => $isGlobal,
            'jenjang_list' => $this->jenjangModel->where('status', 'aktif')->findAll()
        ];
        return view('kepegawaian/gaji_pegawai/rekap', $data);
    }

    public function slip($id): string
    {
        $db = \Config\Database::connect();
        $riwayat = $db->table('riwayat_gaji_pegawai')->where('id', $id)->get()->getRowArray();
        if (!$riwayat) throw PageNotFoundException::forPageNotFound("Data slip gaji tidak ditemukan.");
        
        $sekolah = $this->settingsModel->getSettingsAsArray($riwayat['kode_jenjang']);
        if (empty($sekolah['nama_sekolah'])) $sekolah = $this->settingsModel->getSettingsAsArray('Global');

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