<?php

namespace App\Controllers\Kepegawaian;

use App\Controllers\BaseController;
use App\Models\Kepegawaian\AbsensiPegawaiModel;
use App\Models\TahunAjaranModel;
use App\Models\JenjangModel;
use App\Models\SettingsModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller AbsensiPegawaiController (Enterprise Unified Edition)
 * Mengelola ekosistem presensi Pegawai (Guru, Staff, Penunjang, Yayasan).
 * STATUS: FIXED V6 (Perbaikan Bug Redirect pada Method POST di CI4 Terbaru)
 */
class AbsensiPegawaiController extends BaseController
{
    protected AbsensiPegawaiModel $absensiModel;
    protected TahunAjaranModel $tahunAjaranModel;
    protected JenjangModel $jenjangModel;
    protected SettingsModel $settingsModel; 
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->db               = \Config\Database::connect();
        $this->absensiModel     = new AbsensiPegawaiModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
        $this->settingsModel    = new SettingsModel(); 
        
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
     * Halaman Utama Monitoring Presensi Harian
     */
    public function index(): string
    {
        $session = session();
        $sessionUnit  = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole     = strtolower($session->get('role_name') ?? session()->get('role') ?? '');
        $isSuperAdmin = in_array($userRole, ['superadmin', 'yayasan']);

        $unitParam   = $this->request->getGet('unit');
        $tanggal     = $this->request->getGet('tanggal') ?? date('Y-m-d');
        $tipePegawai = $this->request->getGet('tipe') ?? 'guru'; // guru | staff | penunjang | all

        if (!$isSuperAdmin) {
            $unitParam = $sessionUnit;
        }

        $kodeJenjang = ($unitParam === '' || $unitParam === null) ? null : $unitParam;

        $listAbsensi = $this->absensiModel->getAbsensiHarian($tanggal, $tipePegawai, $kodeJenjang);
        $stats       = $this->absensiModel->getDailyStats($tanggal, $tipePegawai, $kodeJenjang);

        $pegawaiBuilder = $this->db->table('pegawai')->where('status_aktif', 'aktif')->where('deleted_at', null);
        
        if ($kodeJenjang) {
            $pegawaiBuilder->where('kode_jenjang', $kodeJenjang);
        }
        
        if (in_array($tipePegawai, ['guru', 'staff', 'penunjang'])) {
            $pegawaiBuilder->where('jenis_pegawai', $tipePegawai);
        }

        $pegawaiList = $pegawaiBuilder->orderBy('nama_lengkap', 'ASC')->get()->getResultArray();
        $tahunAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();

        // Ambil Daftar Unit untuk filter Superadmin
        $jenjangList = [];
        if ($isSuperAdmin) {
            $jenjangList = $this->jenjangModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        }

        $data = [
            'title'              => 'Monitoring Presensi Pegawai',
            'current_module'     => 'kepegawaian',
            'list_absensi'       => $listAbsensi,
            'stats'              => $stats,
            'tanggal'            => $tanggal,
            'tipe_pegawai'       => $tipePegawai,
            'current_unit'       => $unitParam ?? '',
            'session_unit'       => $sessionUnit,
            'is_global'          => $isSuperAdmin,
            'tahun_ajaran_aktif' => $tahunAktif,
            'pegawai_list'       => $pegawaiList,
            'jenjang_list'       => $jenjangList
        ];

        return view('kepegawaian/absensi_pegawai/index', $data);
    }

    /**
     * Halaman Rekapitulasi Presensi Bulanan
     */
    public function rekap(): string
    {
        $session = session();
        $sessionUnit  = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole     = strtolower($session->get('role_name') ?? session()->get('role') ?? '');
        $isSuperAdmin = in_array($userRole, ['superadmin', 'yayasan']);

        $unitParam   = $this->request->getGet('unit');
        $bulan       = $this->request->getGet('bulan') ?? date('m');
        $tahun       = $this->request->getGet('tahun') ?? date('Y');
        $tipePegawai = $this->request->getGet('tipe') ?? 'guru';

        if (!$isSuperAdmin) {
            $unitParam = $sessionUnit;
        }

        $kodeJenjang = ($unitParam === '' || $unitParam === null) ? null : $unitParam;

        $rekapData = $this->absensiModel->getRekapBulanan($bulan, $tahun, $tipePegawai, $kodeJenjang);

        $targetSettings = $kodeJenjang ?? 'Global';
        $sekolah = $this->settingsModel->getSettingsAsArray($targetSettings);

        if (empty($sekolah['nama_sekolah'])) {
            $sekolah = $this->settingsModel->getSettingsAsArray('Global');
        }

        $jenjangList = [];
        if ($isSuperAdmin) {
            $jenjangList = $this->jenjangModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        }

        $data = [
            'title'          => 'Rekapitulasi Presensi Pegawai',
            'current_module' => 'kepegawaian',
            'rekap'          => $rekapData,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'tipe_pegawai'   => $tipePegawai,
            'current_unit'   => $unitParam ?? '',
            'is_global'      => $isSuperAdmin,
            'session_unit'   => $sessionUnit,
            'jenjang_list'   => $jenjangList,
            'sekolah'        => $sekolah 
        ];

        return view('kepegawaian/absensi_pegawai/rekap', $data);
    }

    public function prosesTap(): RedirectResponse
    {
        // FIX BUG: strtolower agar kebal terhadap upper/lowercase pada CI4 versi 4.3+
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to(base_url('app/kepegawaian/absensi-pegawai'));
        }
        
        $inputBarcode = $this->request->getPost('id_pegawai');
        $metode       = $this->request->getPost('metode') ?? 'online_terminal';
        
        if (!$inputBarcode) return redirect()->back()->with('error', 'Barcode/NIP Pegawai wajib disertakan.');
        
        // PENCARIAN CERDAS: Cek pegawai berdasarkan NIP, NIK, atau NIPY
        $builder = $this->db->table('pegawai')->groupStart()
                            ->where('nip', $inputBarcode)
                            ->orWhere('nik', $inputBarcode)
                            ->orWhere('nipy', $inputBarcode);
                            
        // Mencegah Error MySQL (Integer Overflow). 
        if (is_numeric($inputBarcode) && strlen($inputBarcode) < 11) {
            $builder->orWhere('id', $inputBarcode);
        }
        
        $pegawai = $builder->groupEnd()
                           ->where('status_aktif', 'aktif')
                           ->where('deleted_at', null)
                           ->get()->getRowArray();

        if (!$pegawai) {
            return redirect()->back()->with('error', 'Pegawai dengan Identitas/Barcode tersebut tidak ditemukan atau tidak aktif.');
        }
        
        if ($this->absensiModel->autoRecord((int)$pegawai['id'], $metode)) {
            return redirect()->back()->with('message', 'Aktivitas presensi real-time berhasil dicatat untuk: ' . $pegawai['nama_lengkap']);
        }
        return redirect()->back()->with('error', 'Gagal mencatat tap. Periksa jeda waktu tapping (min. 5 menit).');
    }

    public function simpanMassal(): RedirectResponse
    {
        $payload = $this->request->getPost('massal'); 
        $tanggal = $this->request->getPost('tanggal') ?? date('Y-m-d');
        
        if (empty($payload)) return redirect()->back()->with('error', 'Pilih setidaknya satu pegawai untuk diproses.');

        $this->db->transBegin();
        try {
            foreach ($payload as $idPegawai => $status) {
                if (empty($status)) continue;
                
                $pegawai = $this->db->table('pegawai')->where('id', $idPegawai)->get()->getRowArray();
                if (!$pegawai) continue;
                
                $existing = $this->absensiModel->where(['id_pegawai' => $idPegawai, 'tanggal' => $tanggal])->first();
                $data = [
                    'id_pegawai'   => $idPegawai,
                    'kode_jenjang' => $pegawai['kode_jenjang'],
                    'tanggal'      => $tanggal,
                    'status'       => $status,
                    'metode_absen' => 'manual',
                    'id_user_admin'=> session()->get('user_id')
                ];
                
                if ($existing) {
                    $this->absensiModel->update($existing->id, $data);
                } else { 
                    $data['jam_masuk'] = date('H:i:s'); 
                    $this->absensiModel->insert($data); 
                }
            }
            $this->db->transCommit();
            return redirect()->back()->with('message', 'Presensi massal berhasil disinkronkan.');
        } catch (\Throwable $th) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data.');
        }
    }

    public function updateStatus(): RedirectResponse
    {
        // FIX BUG: strtolower
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to(base_url('app/kepegawaian/absensi-pegawai'));
        }

        $idAbsensi = $this->request->getPost('id');
        $status    = $this->request->getPost('status');
        
        if ($this->absensiModel->update($idAbsensi, ['status' => $status, 'id_user_admin' => session()->get('user_id'), 'metode_absen'  => 'manual'])) {
            return redirect()->back()->with('message', 'Perubahan status berhasil diverifikasi.');
        }
        return redirect()->back()->with('error', 'Gagal memperbarui data.');
    }

    public function simpanManual(): RedirectResponse
    {
        $idPegawai = $this->request->getPost('id_pegawai');
        $status    = $this->request->getPost('status');
        $tanggal   = date('Y-m-d');
        
        $pegawai = $this->db->table('pegawai')->where('id', $idPegawai)->get()->getRowArray();
        if (!$pegawai) return redirect()->back()->with('error', 'Data Pegawai tidak valid.');

        $data = [
            'id_pegawai'   => $idPegawai,
            'kode_jenjang' => $pegawai['kode_jenjang'],
            'tanggal'      => $tanggal,
            'jam_masuk'    => date('H:i:s'),
            'status'       => $status,
            'metode_absen' => 'manual',
            'keterangan'   => $this->request->getPost('keterangan'),
            'id_user_admin'=> session()->get('user_id')
        ];
        
        if ($this->absensiModel->insert($data)) {
            return redirect()->back()->with('message', 'Data presensi individu berhasil disimpan.');
        }
        return redirect()->back()->with('error', 'Gagal menyimpan data manual.');
    }

    /**
     * PROSES ABSENSI ONLINE (WFA / Selfie + GPS)
     */
    public function prosesOnline(): RedirectResponse
    {
        // FIX BUG: strtolower
        if (strtolower($this->request->getMethod()) !== 'post') {
            return redirect()->to(base_url('app/kepegawaian/absensi-pegawai'));
        }

        $idPegawai  = $this->request->getPost('id_pegawai');
        $fotoBase64 = $this->request->getPost('foto_base64');
        $lat        = $this->request->getPost('latitude');
        $lng        = $this->request->getPost('longitude');
        $status     = 'hadir';

        if (!$idPegawai) return redirect()->back()->with('error', 'Identitas Pegawai wajib dipilih.');

        $pegawai = $this->db->table('pegawai')->where('id', $idPegawai)->get()->getRowArray();
        if (!$pegawai) return redirect()->back()->with('error', 'Data Pegawai tidak valid.');

        $tanggal = date('Y-m-d');
        $now = date('H:i:s');
        
        $keterangan = "Absensi Online (Selfie).";
        if (!empty($lat) && !empty($lng)) {
            $keterangan .= " GPS: {$lat}, {$lng}";
        } else {
            $keterangan .= " GPS: Tidak Ditemukan";
        }

        // Proses Foto Base64 (Kamera HTML5) Menjadi File Gambar Fisik (.jpg)
        $fileName = null;
        if (!empty($fotoBase64)) {
            $imgParts = explode(";base64,", $fotoBase64);
            if (count($imgParts) == 2) {
                $image_base64 = base64_decode($imgParts[1]);
                $fileName = 'absen_online_' . $idPegawai . '_' . time() . '.jpg';
                $path = FCPATH . 'uploads/absensi/';
                if (!is_dir($path)) mkdir($path, 0755, true);
                file_put_contents($path . $fileName, $image_base64);
            }
        }

        $existing = $this->absensiModel->where(['id_pegawai' => $idPegawai, 'tanggal' => $tanggal])->first();

        if (!$existing) {
            // Lakukan Check-In
            $data = [
                'id_pegawai'       => $idPegawai,
                'kode_jenjang'     => $pegawai['kode_jenjang'],
                'tanggal'          => $tanggal,
                'jam_masuk'        => $now,
                'status'           => $status,
                'metode_absen'     => 'online',
                'keterangan'       => $keterangan,
                'bukti_foto_masuk' => $fileName,
                'id_user_admin'    => session()->get('user_id')
            ];
            $this->absensiModel->insert($data);
            return redirect()->back()->with('message', 'Absensi Online (Check-In) berhasil dicatat beserta bukti Foto & Lokasi GPS.');
        } else if (empty($existing->jam_keluar)) {
            // Lakukan Check-Out
            $masukTime = strtotime($existing->jam_masuk);
            if ((strtotime($now) - $masukTime) > 60) { // Toleransi jeda minimal 1 menit
                $data = [
                    'jam_keluar'        => $now,
                    'metode_absen'      => 'online',
                    'keterangan'        => ($existing->keterangan ?? '') . ' | Out: ' . $keterangan,
                    'bukti_foto_keluar' => $fileName
                ];
                $this->absensiModel->update($existing->id, $data);
                return redirect()->back()->with('message', 'Absensi Online (Check-Out) berhasil dicatat beserta bukti Foto & Lokasi GPS.');
            } else {
                return redirect()->back()->with('error', 'Terlalu cepat untuk Check-Out. Tunggu beberapa saat lagi.');
            }
        }

        return redirect()->back()->with('error', 'Pegawai sudah menyelesaikan Check-In dan Check-Out hari ini.');
    }
}