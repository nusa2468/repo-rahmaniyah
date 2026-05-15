<?php

namespace App\Controllers\Kepegawaian;

use App\Controllers\BaseController;
use App\Models\Kepegawaian\AbsensiPegawaiModel;
use App\Models\GuruModel;       
use App\Models\TahunAjaranModel;
use App\Models\JenjangModel;
use App\Models\SettingsModel; // Tambahkan Model Settings
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller AbsensiPegawaiController (Enterprise Unified Edition)
 * Mengelola ekosistem presensi Pegawai (Guru & Staff).
 * Lokasi: app/Controllers/Kepegawaian/AbsensiPegawaiController.php
 */
class AbsensiPegawaiController extends BaseController
{
    protected AbsensiPegawaiModel $absensiModel;
    protected GuruModel $pegawaiModel;
    protected TahunAjaranModel $tahunAjaranModel;
    protected JenjangModel $jenjangModel;
    protected SettingsModel $settingsModel; // Property baru

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Inisialisasi semua model
        $this->absensiModel     = new AbsensiPegawaiModel();
        $this->pegawaiModel     = new GuruModel(); 
        $this->tahunAjaranModel = new TahunAjaranModel();
        $this->jenjangModel     = new JenjangModel();
        $this->settingsModel    = new SettingsModel(); // Init SettingsModel
    }

    /**
     * Halaman Utama Monitoring Presensi Harian
     */
    public function index(): string
    {
        $session = session();
        $sessionUnit = $session->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        $unitParam     = $this->request->getGet('unit');
        $tanggal       = $this->request->getGet('tanggal') ?? date('Y-m-d');
        $tipePegawai   = $this->request->getGet('tipe') ?? 'guru'; 

        $kodeJenjang = (!$isGlobal) ? strtoupper($sessionUnit) : 
                       ((!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null);

        $listAbsensi = $this->absensiModel->getAbsensiHarian($tanggal, $tipePegawai, $kodeJenjang);
        $stats       = $this->absensiModel->getDailyStats($tanggal, $tipePegawai, $kodeJenjang);

        $pegawaiBuilder = $this->pegawaiModel->where('status_aktif', 'aktif');
        if ($kodeJenjang) $pegawaiBuilder->where('kode_jenjang', $kodeJenjang);
        
        if ($tipePegawai === 'staff') {
            $pegawaiBuilder->whereIn('jenis_pegawai', ['staff', 'penunjang']);
        } else {
            $pegawaiBuilder->where('jenis_pegawai', 'guru');
        }

        $pegawaiList = $pegawaiBuilder->orderBy('nama_lengkap', 'ASC')->findAll();
        $tahunAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();

        $data = [
            'title'              => 'Monitoring Presensi Pegawai',
            'current_module'     => 'kepegawaian',
            'list_absensi'       => $listAbsensi,
            'stats'              => $stats,
            'tanggal'            => $tanggal,
            'tipe_pegawai'       => $tipePegawai,
            'current_unit'       => $unitParam ?? ($kodeJenjang ?? 'GLOBAL'),
            'session_unit'       => $sessionUnit,
            'is_global'          => $isGlobal,
            'tahun_ajaran_aktif' => $tahunAktif,
            'pegawai_list'       => $pegawaiList
        ];

        return view('kepegawaian/absensi_pegawai/index', $data);
    }

    /**
     * Halaman Rekapitulasi Presensi Bulanan
     */
    public function rekap(): string
    {
        $session = session();
        $sessionUnit = $session->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        $unitParam   = $this->request->getGet('unit');
        $bulan       = $this->request->getGet('bulan') ?? date('m');
        $tahun       = $this->request->getGet('tahun') ?? date('Y');
        $tipePegawai = $this->request->getGet('tipe') ?? 'guru';

        $kodeJenjang = (!$isGlobal) ? strtoupper($sessionUnit) : 
                       ((!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null);

        $rekapData = $this->absensiModel->getRekapBulanan($bulan, $tahun, $tipePegawai, $kodeJenjang);

        // --- AMBIL IDENTITAS SEKOLAH UNTUK KOP SURAT ---
        // Jika filter unit aktif, ambil setting unit tersebut. Jika tidak (Global), ambil setting Global.
        $targetSettings = $kodeJenjang ?? 'Global';
        $sekolah = $this->settingsModel->getSettingsAsArray($targetSettings);

        // Fallback: Jika data sekolah unit kosong, ambil data Global/Yayasan
        if (empty($sekolah['nama_sekolah'])) {
            $sekolah = $this->settingsModel->getSettingsAsArray('Global');
        }

        $data = [
            'title'          => 'Rekapitulasi Presensi Pegawai',
            'current_module' => 'kepegawaian',
            'rekap'          => $rekapData,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'tipe_pegawai'   => $tipePegawai,
            'current_unit'   => $unitParam ?? ($kodeJenjang ?? 'GLOBAL'),
            'is_global'      => $isGlobal,
            'session_unit'   => $sessionUnit,
            'jenjang_list'   => $this->jenjangModel->where('status', 'aktif')->findAll(),
            'sekolah'        => $sekolah // Data identitas dikirim ke View
        ];

        return view('kepegawaian/absensi_pegawai/rekap', $data);
    }

    // ... (Method prosesTap, simpanMassal, updateStatus, simpanManual tetap sama seperti sebelumnya) ...
    
    public function prosesTap(): RedirectResponse
    {
        if ($this->request->getMethod() !== 'post') return redirect()->to(base_url('app/kepegawaian/absensi-pegawai'));
        $idPegawai = $this->request->getPost('id_pegawai');
        $metode    = $this->request->getPost('metode') ?? 'online_terminal';
        if (!$idPegawai) return redirect()->back()->with('error', 'Identitas Pegawai wajib disertakan.');
        if ($this->absensiModel->autoRecord((int)$idPegawai, $metode)) return redirect()->back()->with('message', 'Aktivitas presensi real-time berhasil dicatat.');
        return redirect()->back()->with('error', 'Gagal mencatat tap. Periksa ID atau jeda waktu tapping (min. 5 menit).');
    }

    public function simpanMassal(): RedirectResponse
    {
        $payload = $this->request->getPost('massal'); 
        $tanggal = $this->request->getPost('tanggal') ?? date('Y-m-d');
        if (empty($payload)) return redirect()->back()->with('error', 'Pilih setidaknya satu pegawai untuk diproses.');

        foreach ($payload as $idPegawai => $status) {
            if (empty($status)) continue;
            $pegawai = $this->pegawaiModel->find($idPegawai);
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
            if ($existing) $this->absensiModel->update($existing->id, $data);
            else { $data['jam_masuk'] = date('H:i:s'); $this->absensiModel->insert($data); }
        }
        return redirect()->back()->with('message', 'Presensi massal berhasil disinkronkan.');
    }

    public function updateStatus(): RedirectResponse
    {
        if ($this->request->getMethod() !== 'post') return redirect()->to(base_url('app/kepegawaian/absensi-pegawai'));
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
        $pegawai = $this->pegawaiModel->find($idPegawai);
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
        if ($this->absensiModel->insert($data)) return redirect()->back()->with('message', 'Data presensi individu berhasil disimpan.');
        return redirect()->back()->with('error', 'Gagal menyimpan data manual.');
    }
}