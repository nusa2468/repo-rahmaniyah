<?php

namespace App\Controllers\Portal;

use App\Controllers\BaseController;
use Config\Database;

/**
 * PortalPegawaiController
 * Menangani logika Portal Guru & Staff (Login, Dashboard, Jadwal, Profil, dll).
 * STATUS: ROBUST V38 (Fixed: Data Siswa Anti-Crash & Smart Join)
 */
class PortalPegawaiController extends BaseController
{
    protected $pegawaiModel;
    protected $settingsModel;
    protected $tahunAjaranModel;
    protected $db;

    public function __construct()
    {
        // Load Models
        $this->pegawaiModel     = model('App\Models\Portal\PortalPegawaiModel');
        $this->settingsModel    = model('App\Models\SettingsModel');
        $this->tahunAjaranModel = model('App\Models\TahunAjaranModel');
        $this->db = Database::connect();
        helper(['form', 'url', 'text', 'date']);
    }

    // =========================================================================
    // 1. AUTHENTICATION
    // =========================================================================

    public function login()
    {
        if (session()->get('pegawai_logged_in')) {
            return redirect()->to(base_url('portal/pegawai/dashboard'));
        }
        return view('portal/pegawai/login', ['title' => 'Login', 'sekolah' => $this->getSekolahData()]);
    }

    public function attemptLogin()
    {
        if (!$this->validate(['username' => 'required', 'password' => 'required'])) {
            return redirect()->back()->withInput()->with('error', 'Mohon lengkapi Username dan Password.');
        }

        $input    = trim((string)$this->request->getPost('username'));
        $password = trim((string)$this->request->getPost('password'));

        $pegawai = $this->pegawaiModel->getPegawaiForLogin($input);
        if (!$pegawai) return redirect()->back()->withInput()->with('error', 'Akun tidak ditemukan.');

        $dbPass = $pegawai['password'] ?? null;
        $isValid = false;
        $needRehash = false;

        // Cek Password
        if (!empty($dbPass)) {
            if (password_verify($password, $dbPass)) $isValid = true;
            elseif ($password === $dbPass) { $isValid = true; $needRehash = true; }
        } elseif (!empty($pegawai['user_id'])) {
            // Fallback ke tabel users
            $user = $this->db->table('users')->where('id', $pegawai['user_id'])->get()->getRowArray();
            if ($user && isset($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                $isValid = true;
            }
        }

        // Fallback Default NIP/NIPY
        if (!$isValid && empty($dbPass)) {
            $defaultPassword = $pegawai['nip'] ?? $pegawai['nipy']; 
            if ($defaultPassword && $password === $defaultPassword) {
                $isValid = true;
                $needRehash = true; 
            } else {
                 return redirect()->back()->withInput()->with('error', 'Password belum diatur. Login dengan NIP/NIPY.');
            }
        }

        if (!$isValid) return redirect()->back()->withInput()->with('error', 'Kata sandi salah.');

        // Auto Hash jika masih plain text atau default
        if ($needRehash && $this->db->fieldExists('password', 'pegawai')) {
            $this->pegawaiModel->update($pegawai['id'], ['password' => $password]);
        }

        if (strtolower($pegawai['status_aktif'] ?? 'aktif') !== 'aktif') {
            return redirect()->back()->withInput()->with('error', 'Akun Non-Aktif.');
        }

        session()->set([
            'pegawai_id'        => $pegawai['id'],
            'pegawai_nama'      => $pegawai['nama_lengkap'],
            'pegawai_nip'       => $pegawai['nip'] ?? $pegawai['nipy'],
            'pegawai_role'      => $pegawai['jenis_pegawai'],
            'pegawai_logged_in' => true,
        ]);

        return redirect()->to(base_url('portal/pegawai/dashboard'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('portal/pegawai/login'));
    }

    // =========================================================================
    // 2. DASHBOARD & FEATURES
    // =========================================================================

    public function index() { return $this->dashboard(); }

    public function dashboard()
    {
        $pegawai = $this->getPegawaiSession();
        if (!$pegawai) return $this->logout();

        $taData = $this->getAktifTA($pegawai); 
        $labelTA = $taData ? ($taData['tahun_ajaran'] . ' - ' . $taData['semester']) : 'Tahun Ajaran Belum Diatur';

        $jadwal = [];
        if ($this->isGuru($pegawai)) {
            $jadwal = $this->pegawaiModel->getJadwalMengajar($pegawai['id'], $this->getHariIndonesia(date('l')));
        }

        return view('portal/pegawai/dashboard', [
            'title'              => 'Dashboard Pegawai',
            'pegawai'            => $pegawai,
            'sekolah'            => $this->getSekolahData(),
            'jadwal_hari_ini'    => $jadwal,
            'presensi'           => $this->pegawaiModel->getRingkasanPresensi($pegawai['id'], date('m'), date('Y')),
            'pengumuman'         => $this->pegawaiModel->getPengumumanTerbaru(),
            'hari_ini'           => $this->getHariIndonesia(date('l')),
            'tahun_ajaran_label' => $labelTA,
            'security_alert'     => session()->getFlashdata('security_alert')
        ]);
    }

    public function jadwal()
    {
        $pegawai = $this->getPegawaiSession();
        if (!$pegawai) return $this->logout();
        if (!$this->isGuru($pegawai)) return redirect()->to('portal/pegawai/dashboard');

        $taData  = $this->getAktifTA($pegawai);
        $labelTA = $taData ? ($taData['tahun_ajaran'] . ' - ' . $taData['semester']) : '-';

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $jadwalGrouped = [];
        foreach ($days as $day) {
            $h = $this->pegawaiModel->getJadwalMengajar($pegawai['id'], $day);
            if (!empty($h)) $jadwalGrouped[$day] = $h;
        }

        return view('portal/pegawai/jadwal', [
            'title' => 'Jadwal Mengajar', 'pegawai' => $pegawai, 'sekolah' => $this->getSekolahData(),
            'jadwal' => $jadwalGrouped, 'tahun_ajaran_label' => $labelTA
        ]);
    }

    // --- FIX ERROR: DATA SISWA SMART JOIN LOGIC ---
    public function siswa()
    {
        $pegawai = $this->getPegawaiSession(); 
        if (!$pegawai) return $this->logout();

        // 1. Tentukan Tahun Ajaran Aktif (Penting untuk Enrollment)
        $taData  = $this->getAktifTA($pegawai);
        $idTahun = $taData['id'] ?? 0;
        $labelTA = $taData ? ($taData['tahun_ajaran'] . ' - ' . $taData['semester']) : 'Tahun Ajaran Belum Diatur';

        $siswaList = [];
        
        // 2. Query Data Siswa dengan Cek Struktur Tabel
        if ($this->db->tableExists('siswa')) {
            $builder = $this->db->table('siswa')
                ->select('siswa.*'); // Default select

            // Skenario A: Menggunakan Tabel Enrollment (Ideal / Enterprise)
            if ($this->db->tableExists('siswa_enrollment') && $idTahun > 0) {
                $builder->select('k.nama_kelas')
                        ->join('siswa_enrollment se', 'se.id_siswa = siswa.id AND se.id_tahun_ajaran = ' . $this->db->escape($idTahun), 'left')
                        ->join('kelas k', 'k.id = se.id_kelas', 'left');
            } 
            // Skenario B: Menggunakan Kolom id_kelas di Tabel Siswa (Legacy)
            // Kita cek dulu apakah kolomnya ada agar tidak error
            elseif ($this->db->fieldExists('id_kelas', 'siswa')) {
                $builder->select('k.nama_kelas')
                        ->join('kelas k', 'k.id = siswa.id_kelas', 'left');
            } 
            // Skenario C: Tidak ada info kelas (Fallback Anti-Crash)
            else {
                $builder->select("'' as nama_kelas");
            }

            // Filter Siswa per Unit Pegawai & Status Aktif
            $siswaList = $builder->where('siswa.kode_jenjang', $pegawai['kode_jenjang'])
                ->groupStart()
                    ->where('siswa.status', 'Aktif')
                    ->orWhere('siswa.status', 'active') // Toleransi format
                ->groupEnd()
                ->orderBy('siswa.nama_lengkap', 'ASC')
                ->limit(200) // Limit untuk performa
                ->get()->getResultArray();
        }

        return view('portal/pegawai/data_siswa', [
            'title'              => 'Data Siswa',
            'pegawai'            => $pegawai,
            'sekolah'            => $this->getSekolahData(),
            'tahun_ajaran_label' => $labelTA,
            'siswa_list'         => $siswaList
        ]);
    }

    public function nilai()
    {
        $pegawai = $this->getPegawaiSession(); 
        if (!$pegawai) return $this->logout();
        if (!$this->isGuru($pegawai)) return redirect()->to('portal/pegawai/dashboard');

        $taData  = $this->getAktifTA($pegawai);
        $idTahun = $taData['id'] ?? 0;
        $labelTA = $taData ? ($taData['tahun_ajaran'] . ' - ' . $taData['semester']) : '-';

        $kelasAjar = [];
        if($this->db->tableExists('jadwal_pelajaran') && $idTahun > 0) {
            $kelasAjar = $this->db->table('jadwal_pelajaran jp')
                ->select('k.nama_kelas, mp.nama_mapel, jp.id_kelas, jp.id_mata_pelajaran')
                ->join('kelas k', 'k.id = jp.id_kelas')
                ->join('mata_pelajaran mp', 'mp.id = jp.id_mata_pelajaran')
                ->where('jp.id_guru', $pegawai['id'])
                ->where('jp.id_tahun_ajaran', $idTahun) // Filter TA agar data akurat
                ->where('jp.deleted_at', null)
                ->groupBy('jp.id_kelas, jp.id_mata_pelajaran')
                ->get()->getResultArray();
        }

        return view('portal/pegawai/input_nilai', [
            'title'              => 'Input Nilai',
            'pegawai'            => $pegawai,
            'sekolah'            => $this->getSekolahData(),
            'tahun_ajaran_label' => $labelTA,
            'kelas_ajar'         => $kelasAjar
        ]);
    }

    public function keuangan()
    {
        $pegawai = $this->getPegawaiSession(); 
        if (!$pegawai) return $this->logout();

        $taData  = $this->getAktifTA($pegawai);
        $labelTA = $taData ? ($taData['tahun_ajaran'] . ' - ' . $taData['semester']) : '-';

        $riwayatGaji = []; 
        // TODO: Implement Real Query from Payroll Table
        // $riwayatGaji = $this->db->table('gaji_pegawai')...

        return view('portal/pegawai/slip_gaji', [
            'title'              => 'Slip Gaji & Keuangan',
            'pegawai'            => $pegawai,
            'sekolah'            => $this->getSekolahData(),
            'tahun_ajaran_label' => $labelTA,
            'riwayat_gaji'       => $riwayatGaji
        ]);
    }

    public function profil()
    {
        $pegawai = $this->getPegawaiSession();
        if (!$pegawai) return $this->logout();
        
        // Load detail jabatan jika ada
        if(method_exists($this->pegawaiModel, 'getDetailJabatan')) {
            $jabatan = $this->pegawaiModel->getDetailJabatan($pegawai['id']);
            if($jabatan) $pegawai = array_merge($pegawai, $jabatan);
        }

        return view('portal/pegawai/profil', [
            'title'   => 'Profil Saya',
            'pegawai' => $pegawai,
            'sekolah' => $this->getSekolahData()
        ]);
    }

    public function updatePassword()
    {
        $pegawai = $this->getPegawaiSession();
        if (!$pegawai) return $this->logout();

        if (!$this->validate(['old_password' => 'required', 'new_password' => 'required|min_length[6]', 'confirm_password' => 'required|matches[new_password]'])) {
            return redirect()->back()->with('error_password', 'Validasi gagal.');
        }

        $old = $this->request->getPost('old_password');
        $new = $this->request->getPost('new_password');
        
        $current = $pegawai['password'] ?? null;
        // Fallback user table check
        if (!$current && !empty($pegawai['user_id'])) {
             $u = $this->db->table('users')->where('id', $pegawai['user_id'])->get()->getRowArray();
             $current = $u['password_hash'] ?? $u['password'] ?? null;
        }

        $valid = false;
        if ($current) {
            if (password_verify($old, $current) || $old === $current) $valid = true;
        } else {
             // Check NIP default
             if ($old === ($pegawai['nip'] ?? $pegawai['nipy'])) $valid = true;
        }

        if (!$valid) return redirect()->back()->with('error_password', 'Password lama salah.');

        // Update
        if ($this->db->fieldExists('password', 'pegawai')) {
            $this->pegawaiModel->update($pegawai['id'], ['password' => $new]);
        }
        if (!empty($pegawai['user_id']) && $this->db->tableExists('users')) {
             $f = $this->db->fieldExists('password_hash', 'users') ? 'password_hash' : 'password';
             $this->db->table('users')->where('id', $pegawai['user_id'])->update([$f => password_hash($new, PASSWORD_DEFAULT)]);
        }

        return redirect()->back()->with('success_password', 'Password berhasil diperbarui.');
    }

    public function presensi() 
    {
        $pegawai = $this->getPegawaiSession();
        if (!$pegawai) return $this->logout();

        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $summary = $this->pegawaiModel->getRingkasanPresensi($pegawai['id'], $bulan, $tahun);
        $detail  = [];
        if(method_exists($this->pegawaiModel, 'getDetailPresensi')) {
            $detail = $this->pegawaiModel->getDetailPresensi($pegawai['id'], $bulan, $tahun);
        }

        return view('portal/pegawai/presensi', [
            'title'   => 'Riwayat Presensi',
            'pegawai' => $pegawai,
            'sekolah' => $this->getSekolahData(),
            'summary' => $summary,
            'detail'  => $detail,
            'filter'  => ['bulan' => $bulan, 'tahun' => $tahun]
        ]);
    }

    // --- HELPERS ---
    private function getPegawaiSession() {
        if (!session()->get('pegawai_logged_in')) return null;
        return $this->pegawaiModel->find(session()->get('pegawai_id'));
    }
    private function isGuru($pegawai) {
        return (isset($pegawai['jenis_pegawai']) && strtolower($pegawai['jenis_pegawai']) === 'guru') || 
               (isset($pegawai['jenis_ptk']) && str_contains(strtolower($pegawai['jenis_ptk'] ?? ''), 'guru'));
    }
    private function getAktifTA($pegawai) {
        $ta = $this->pegawaiModel->getTahunAjaranAktif($pegawai['id']);
        if (!$ta && $this->tahunAjaranModel) {
            $ta = $this->tahunAjaranModel->where('status', 'aktif')->where('kode_jenjang', 'GLOBAL')->first();
        }
        if (!$ta && $this->tahunAjaranModel) {
             $ta = $this->tahunAjaranModel->where('status', 'aktif')->first();
        }
        return $ta;
    }
    private function getSekolahData() {
        $data = ['nama_sekolah' => 'School ERP', 'logo' => null];
        try { if ($this->settingsModel) { 
            $s = $this->settingsModel->getSettingsAsArray('Global'); 
            if($s) $data = array_merge($data, array_filter($s));
        }} catch (\Throwable $e) {}
        return (object) $data;
    }
    private function getHariIndonesia($dayName) {
        $days = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
        return $days[$dayName] ?? $dayName;
    }
    private function getTahunAjaranLabel($idPegawai) {
        $ta = $this->getAktifTA(['id' => $idPegawai]);
        return $ta ? ($ta['tahun_ajaran'] . ' - ' . $ta['semester']) : 'Tahun Ajaran Aktif';
    }
}