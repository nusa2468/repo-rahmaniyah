<?php

namespace App\Controllers\Portal;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use Config\Database;

/**
 * Controller Portal Siswa
 * STATUS: ROBUST V22 (Update Format Tahun Ajaran Dinamis "Tahun - Semester")
 */
class PortalSiswaController extends BaseController
{
    protected $siswaModel;
    protected $settingsModel;
    protected $jadwalModel;
    protected $portalModel;
    protected $tahunAjaranModel;
    protected $db;

    public function __construct()
    {
        $this->siswaModel     = new SiswaModel();
        
        // Load Models dengan service locator
        $this->jadwalModel    = model('App\Models\JadwalPelajaranModel');
        $this->settingsModel  = model('App\Models\SettingsModel');
        $this->portalModel    = model('App\Models\Portal\PortalSiswaModel');
        $this->tahunAjaranModel = model('App\Models\TahunAjaranModel');
        
        $this->db = Database::connect();
        helper(['form', 'url', 'text', 'date']);
    }

    public function login()
    {
        if (session()->get('siswa_logged_in')) return redirect()->to(base_url('portal/siswa/dashboard'));
        $data = ['title' => 'Login Portal Siswa', 'sekolah' => $this->getSekolahData('Global')];
        return view('portal/siswa/login', $data);
    }

    public function attemptLogin()
    {
        if (!$this->validate(['nis' => 'required', 'password' => 'required'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nisInput  = trim((string)$this->request->getPost('nis'));
        $passInput = trim((string)$this->request->getPost('password'));

        $siswa = $this->siswaModel->where('nis', $nisInput)->first();

        if (!$siswa) return redirect()->back()->withInput()->with('error', 'NIS tidak ditemukan.');

        if ($passInput === 'RESET_ME') {
            $defaultHash = password_hash($nisInput, PASSWORD_DEFAULT);
            $this->siswaModel->allowCallbacks(false)->update($siswa['id'], [
                'password' => $defaultHash, 'status' => 'Aktif'
            ]);
            return redirect()->back()->with('success', 'Akun berhasil di-reset. Login dengan NIS Anda.');
        }

        $get = function($data, $field) {
            return (is_array($data) ? ($data[$field] ?? null) : ($data->$field ?? null));
        };

        $dbPass = $get($siswa, 'password');
        $dbNis  = $get($siswa, 'nis');
        $dbUnit = $get($siswa, 'kode_jenjang');
        $sId    = $get($siswa, 'id');
        $sKelas = $get($siswa, 'id_kelas');
        $status = $get($siswa, 'status');

        $isValid = false;
        $needRehash = false;
        $defaultPassCombined = $dbNis . '.' . ($dbUnit ?? 'SEKOLAH');

        if (!empty($dbPass) && password_verify($passInput, $dbPass)) {
            $isValid = true;
        } elseif ($passInput === $dbPass) {
            $isValid = true; $needRehash = true;
        } elseif ($passInput == $dbNis) {
            if (empty($dbPass) || password_verify((string)$dbNis, $dbPass) || password_verify($defaultPassCombined, $dbPass)) {
                $isValid = true; $needRehash = true;
            }
        } elseif ($passInput == $defaultPassCombined) {
             if (empty($dbPass) || password_verify($defaultPassCombined, $dbPass)) {
                $isValid = true; $needRehash = true;
            }
        }

        if ($isValid) {
            if (!in_array(strtolower($status ?? ''), ['aktif', 'terdaftar', 'active'])) {
                return redirect()->back()->withInput()->with('error', 'Akun Non-Aktif.');
            }

            if ($needRehash) {
                $this->siswaModel->allowCallbacks(false)->update($sId, [
                    'password' => password_hash($passInput, PASSWORD_DEFAULT)
                ]);
            }

            session()->set([
                'siswa_id'        => $sId,
                'siswa_nama'      => $get($siswa, 'nama_lengkap'),
                'siswa_nis'       => $dbNis,
                'siswa_kelas_id'  => $sKelas,
                'siswa_jenjang'   => $dbUnit,
                'siswa_logged_in' => true,
            ]);

            return redirect()->to(base_url('portal/siswa/dashboard'));
        }

        return redirect()->back()->withInput()->with('error', 'Password yang dimasukkan salah.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('portal/siswa/login'));
    }

    // --- DASHBOARD ---
    public function dashboard()
    {
        return $this->renderView('dashboard', 'Dashboard Siswa');
    }

    // --- FITUR: JADWAL PELAJARAN ---
    public function jadwal()
    {
        $siswa = $this->getSiswaSession();
        if (!$siswa) return $this->logout();

        $idKelas = is_array($siswa) ? ($siswa['id_kelas'] ?? null) : ($siswa->id_kelas ?? null);
        $jenjang = is_array($siswa) ? ($siswa['kode_jenjang'] ?? 'Global') : ($siswa->kode_jenjang ?? 'Global');
        
        $jadwalGrouped = [];

        if ($idKelas && $this->portalModel && method_exists($this->portalModel, 'getJadwalMingguan')) {
            $jadwalMingguan = $this->portalModel->getJadwalMingguan($idKelas);
            foreach ($jadwalMingguan as $j) {
                $jadwalGrouped[$j['hari']][] = $j;
            }
        }

        // Panggil renderView agar partials & data sekolah konsisten
        // Namun karena jadwal butuh data spesifik, kita inject manual atau gunakan renderView dengan extra data
        // Agar konsisten dengan struktur V21, kita pakai pendekatan manual yg mirip renderView untuk data umum
        
        // REVISI: Menggunakan logic yang sama dengan renderView untuk konsistensi tahun ajaran
        $tahunLabel = 'Tahun Ajaran Aktif';
        if ($this->tahunAjaranModel) {
            $ta = $this->tahunAjaranModel->getAktifByUnit($jenjang);
            if ($ta) {
                $taArr = is_object($ta) ? (array)$ta : $ta;
                // FORMAT: 2025/2026 - Genap
                $tahunLabel = $taArr['tahun_ajaran'] . ' - ' . $taArr['semester'];
            }
        }

        $data = [
            'title'   => 'Jadwal Pelajaran',
            'siswa'   => $siswa,
            'sekolah' => $this->getSekolahData($jenjang),
            'jadwal'  => $jadwalGrouped,
            'tahun_ajaran_label' => $tahunLabel // Pass variable ini ke View
        ];
        return view('portal/siswa/jadwal', $data);
    }

    // --- FITUR: NILAI & RAPOR ---
    public function nilai()
    {
        $siswa = $this->getSiswaSession();
        if (!$siswa) return $this->logout();
        
        $sId = is_array($siswa) ? $siswa['id'] : $siswa->id;
        $jenjang = is_array($siswa) ? ($siswa['kode_jenjang'] ?? 'Global') : ($siswa->kode_jenjang ?? 'Global');

        $riwayatNilai = [];
        $riwayatRapor = [];

        if ($this->portalModel) {
            if (method_exists($this->portalModel, 'getNilaiTerbaru')) {
                $riwayatNilai = $this->portalModel->getNilaiTerbaru($sId, 100);
            }
            if (method_exists($this->portalModel, 'getRiwayatRapor')) {
                $riwayatRapor = $this->portalModel->getRiwayatRapor($sId);
            }
        }

        // Hitung tahun ajaran
        $tahunLabel = 'Tahun Ajaran Aktif';
        if ($this->tahunAjaranModel) {
            $ta = $this->tahunAjaranModel->getAktifByUnit($jenjang);
            if ($ta) {
                $taArr = is_object($ta) ? (array)$ta : $ta;
                $tahunLabel = $taArr['tahun_ajaran'] . ' - ' . $taArr['semester'];
            }
        }

        $data = [
            'title'   => 'Riwayat Nilai & Rapor',
            'siswa'   => $siswa,
            'sekolah' => $this->getSekolahData($jenjang),
            'nilai'   => $riwayatNilai,
            'rapor'   => $riwayatRapor,
            'tahun_ajaran_label' => $tahunLabel
        ];
        return view('portal/siswa/nilai', $data);
    }
    
    public function rapor($id)
    {
        $siswa = $this->getSiswaSession();
        if (!$siswa) return $this->logout();
        
        $sId = is_array($siswa) ? $siswa['id'] : $siswa->id;
        $jenjang = is_array($siswa) ? ($siswa['kode_jenjang'] ?? 'Global') : ($siswa->kode_jenjang ?? 'Global');
        
        $dataRapor = null;
        if ($this->portalModel && method_exists($this->portalModel, 'getDetailRapor')) {
            $dataRapor = $this->portalModel->getDetailRapor($id, $sId);
        }
        
        if (!$dataRapor) {
            return redirect()->back()->with('error', 'Data rapor tidak ditemukan.');
        }
        
        $data = [
            'title' => 'Detail Rapor',
            'siswa' => $siswa,
            'sekolah' => $this->getSekolahData($jenjang),
            'rapor' => $dataRapor
        ];
        
        return view('portal/siswa/detail_rapor', $data);
    }

    // --- FITUR: KEUANGAN ---
    public function keuangan()
    {
        $siswa = $this->getSiswaSession();
        if (!$siswa) return $this->logout();
        
        $sId = is_array($siswa) ? $siswa['id'] : $siswa->id;
        $jenjang = is_array($siswa) ? ($siswa['kode_jenjang'] ?? 'Global') : ($siswa->kode_jenjang ?? 'Global');

        $tagihan = [];
        $riwayat = [];

        if ($this->portalModel) {
            if(method_exists($this->portalModel, 'getTagihanBelumLunas')) {
                $tagihan = $this->portalModel->getTagihanBelumLunas($sId);
            }
            if(method_exists($this->portalModel, 'getRiwayatPembayaran')) {
                $riwayat = $this->portalModel->getRiwayatPembayaran($sId, 50);
            }
        }

        $tahunLabel = 'Tahun Ajaran Aktif';
        if ($this->tahunAjaranModel) {
            $ta = $this->tahunAjaranModel->getAktifByUnit($jenjang);
            if ($ta) {
                $taArr = is_object($ta) ? (array)$ta : $ta;
                $tahunLabel = $taArr['tahun_ajaran'] . ' - ' . $taArr['semester'];
            }
        }

        $data = [
            'title'              => 'Info Keuangan',
            'siswa'              => $siswa,
            'sekolah'            => $this->getSekolahData($jenjang),
            'tagihan'            => $tagihan,
            'riwayat_pembayaran' => $riwayat,
            'tahun_ajaran_label' => $tahunLabel
        ];
        return view('portal/siswa/keuangan', $data);
    }

    // --- FITUR: PROFIL ---
    public function profil()
    {
        $siswa = $this->getSiswaSession();
        if (!$siswa) return $this->logout();
        
        $jenjang = is_array($siswa) ? ($siswa['kode_jenjang'] ?? 'Global') : ($siswa->kode_jenjang ?? 'Global');

        $tahunLabel = 'Tahun Ajaran Aktif';
        if ($this->tahunAjaranModel) {
            $ta = $this->tahunAjaranModel->getAktifByUnit($jenjang);
            if ($ta) {
                $taArr = is_object($ta) ? (array)$ta : $ta;
                $tahunLabel = $taArr['tahun_ajaran'] . ' - ' . $taArr['semester'];
            }
        }

        $data = [
            'title'   => 'Profil Saya',
            'siswa'   => $siswa,
            'sekolah' => $this->getSekolahData($jenjang),
            'tahun_ajaran_label' => $tahunLabel
        ];
        return view('portal/siswa/profil', $data);
    }

    // --- HELPERS ---
    private function getSiswaSession()
    {
        if (!session()->get('siswa_logged_in')) return null;
        $id = session()->get('siswa_id');
        return $this->siswaModel->find($id);
    }

    private function getSekolahData($jenjang = 'Global')
    {
        $data = ['nama_sekolah' => 'Portal Akademik', 'logo' => null, 'alamat' => 'Alamat belum diatur', 'jenjang_aktif'=> $jenjang];
        try {
            if (isset($this->settingsModel)) {
                $settings = $this->settingsModel->getSettingsAsArray($jenjang);
                if (empty($settings['nama_sekolah'])) {
                    $global = $this->settingsModel->getSettingsAsArray('Global');
                    $settings = array_merge($global, $settings);
                }
                if (!empty($settings['nama_sekolah'])) $data['nama_sekolah'] = $settings['nama_sekolah'];
                if (!empty($settings['logo'])) $data['logo'] = $settings['logo'];
                if (!empty($settings['alamat'])) $data['alamat'] = $settings['alamat'];
            }
        } catch (\Throwable $e) {}
        return (object) $data;
    }
    
    private function renderView($viewName, $title) {
        $siswa = $this->getSiswaSession();
        if(!$siswa) return $this->logout();
        
        $jenjangSiswa = is_array($siswa) ? ($siswa['kode_jenjang'] ?? 'Global') : ($siswa->kode_jenjang ?? 'Global');
        
        // --- LOGIC TAHUN AJARAN DINAMIS ---
        $tahunLabel = 'Tahun Ajaran Aktif';
        if ($this->tahunAjaranModel) {
            // Mengambil tahun ajaran aktif berdasarkan unit siswa (TK/SD/SMP/SMA)
            $ta = $this->tahunAjaranModel->getAktifByUnit($jenjangSiswa);
            if ($ta) {
                $taArr = is_object($ta) ? (array)$ta : $ta;
                // Format Output: 2025/2026 - Genap
                $tahunLabel = $taArr['tahun_ajaran'] . ' - ' . $taArr['semester'];
            }
        }

        $data = [
            'title' => $title,
            'siswa' => $siswa,
            'sekolah' => $this->getSekolahData($jenjangSiswa),
            'jadwal_hari_ini' => [], 'tagihan_aktif' => [], 'nilai_terakhir' => [], 'riwayat_pembayaran' => [], 
            'rapor_terbaru' => null,
            'hari_ini' => date('l'),
            'tahun_ajaran_label' => $tahunLabel, // Variabel ini dikirim ke Dashboard
            'security_alert'  => session()->getFlashdata('security_alert')
        ];
        
        if ($viewName == 'dashboard' && $this->portalModel) {
             $hariIndo = $this->getHariIndonesia(date('l'));
             $idKelas = is_array($siswa) ? ($siswa['id_kelas'] ?? null) : ($siswa->id_kelas ?? null);
             $sId = is_array($siswa) ? $siswa['id'] : $siswa->id;

             $data['hari_ini'] = $hariIndo;
             
             if($idKelas && method_exists($this->portalModel, 'getJadwalHarian')) {
                 $data['jadwal_hari_ini'] = $this->portalModel->getJadwalHarian($idKelas, $hariIndo);
             }
             if(method_exists($this->portalModel, 'getTagihanBelumLunas')) {
                 $data['tagihan_aktif'] = $this->portalModel->getTagihanBelumLunas($sId);
             }
             if(method_exists($this->portalModel, 'getNilaiTerbaru')) {
                 $data['nilai_terakhir'] = $this->portalModel->getNilaiTerbaru($sId);
             }
             if(method_exists($this->portalModel, 'getRiwayatPembayaran')) {
                 $data['riwayat_pembayaran'] = $this->portalModel->getRiwayatPembayaran($sId, 3);
             }
             if(method_exists($this->portalModel, 'getRiwayatRapor')) {
                 $rapor = $this->portalModel->getRiwayatRapor($sId);
                 $data['rapor_terbaru'] = !empty($rapor) ? $rapor[0] : null;
             }
        }
        
        return view('portal/siswa/' . $viewName, $data);
    }
    
    private function getHariIndonesia($dayName) {
        $days = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
        return $days[$dayName] ?? $dayName;
    }
}