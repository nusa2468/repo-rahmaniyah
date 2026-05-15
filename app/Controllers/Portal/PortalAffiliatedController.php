<?php

namespace App\Controllers\Portal;

use App\Controllers\BaseController;
use App\Models\Portal\PortalAffiliatedModel;
use App\Models\Portal\PortalPpdbModel;
use App\Models\SettingsModel; 
use App\Models\JenjangModel; 

/**
 * PortalAffiliatedController
 * FIX: Menggunakan asArray() pada query builder untuk menghindari error "stdClass as array" di View.
 * UPDATE: Menghapus seluruh rute prefix 'app' agar sesuai dengan struktur portal independen.
 * UPDATE: Menambahkan kembali method register() dan submitRegistration() yang hilang.
 */
class PortalAffiliatedController extends BaseController
{
    protected $affiliateModel;
    protected $ppdbModel;
    protected $settingsModel;
    protected $jenjangModel;

    public function __construct()
    {
        $this->affiliateModel = new PortalAffiliatedModel();
        $this->ppdbModel      = new PortalPpdbModel();
        
        $this->settingsModel = class_exists('App\Models\SettingsModel') ? new SettingsModel() : null;
        $this->jenjangModel  = class_exists('App\Models\JenjangModel') ? new JenjangModel() : null;
        
        helper(['form', 'url', 'text']);
    }

    public function index()
    {
        $foundationInfo = $this->settingsModel ? $this->settingsModel->getSettingsAsArray('Global') : [];
        return view('portal/affiliated/home', [
            'title'        => 'Program Kemitraan', 
            'foundation'   => $foundationInfo, 
            'is_logged_in' => session()->get('mitra_logged_in'), 
            'mitra_nama'   => session()->get('mitra_nama')
        ]);
    }

    public function login()
    {
        if (session()->get('mitra_logged_in')) {
            return redirect()->to(base_url('portal/affiliated/dashboard'));
        }
        return view('portal/affiliated/login', ['title' => 'Login Mitra PPDB']);
    }

    public function attemptLogin()
    {
        $kodeAgen = $this->request->getPost('kode_agen');
        $noHp     = $this->request->getPost('no_hp');
        
        // FIX: Tambahkan asArray() agar hasil pencarian berupa array
        $mitra = $this->affiliateModel->asArray()
                                      ->where('kode_agen', $kodeAgen)
                                      ->where('no_hp', $noHp)
                                      ->where('status', 'Aktif')
                                      ->first();

        if ($mitra) {
            session()->set([
                'mitra_id'        => $mitra['affiliate_id'] ?? $mitra['id'],
                'mitra_nama'      => $mitra['nama_agen'],
                'mitra_kode'      => $mitra['kode_agen'],
                'mitra_fee'       => $mitra['fee_per_siswa'] ?? 50000,
                'mitra_logged_in' => true,
            ]);
            return redirect()->to(base_url('portal/affiliated/dashboard'));
        }
        return redirect()->back()->with('error', 'Login gagal. Akun tidak ditemukan atau belum Aktif.');
    }

    /**
     * Menampilkan Form Pendaftaran Mitra Baru
     */
    public function register()
    {
        if (session()->get('mitra_logged_in')) {
            return redirect()->to(base_url('portal/affiliated/dashboard'));
        }
        return view('portal/affiliated/register', ['title' => 'Pendaftaran Mitra Baru']);
    }

    /**
     * Memproses Pendaftaran Mitra Baru
     */
    public function submitRegistration()
    {
        $rules = [
            'nama_agen'      => 'required|min_length[3]', 
            'no_hp'          => 'required|is_unique[affiliates.no_hp]', // Pastikan nama tabel sesuai DB (misal: affiliates atau agen)
            'email'          => 'required|valid_email|is_unique[affiliates.email]', 
            'nama_bank'      => 'required',
            'nomor_rekening' => 'required', 
            'nama_rekening'  => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. Pastikan Email/No HP belum terdaftar.');
        }

        $data = $this->request->getPost();
        
        // Generate Kode Agen
        $data['kode_agen'] = 'AGN-' . strtoupper(substr(md5(uniqid()), 0, 4)); 
        $data['status'] = 'Non-Aktif'; // Default menunggu approval admin
        $data['fee_per_siswa'] = 50000; // Fee default

        if ($this->affiliateModel->save($data)) {
            return redirect()->to(base_url('portal/affiliated/login'))->with('success', 'Pendaftaran berhasil! Kode Agen Anda: <b>' . $data['kode_agen'] . '</b>. Silakan tunggu aktivasi dari admin.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data pendaftaran.');
    }

    public function dashboard()
    {
        // 1. Proteksi Akses
        if (!session()->get('mitra_logged_in')) {
            return redirect()->to(base_url('portal/affiliated/login'));
        }
        
        $kodeAgen   = session()->get('mitra_kode');
        $feeDefault = session()->get('mitra_fee') ?? 50000;
        
        // 2. Ambil Data Referral (Siswa yang mendaftar menggunakan kode agen ini)
        // FIX: Tambahkan asArray() agar $referrals berupa array of arrays, bukan array of objects
        $referrals = $this->ppdbModel->asArray()
                                     ->where('kode_afiliasi', $kodeAgen)
                                     ->orderBy('created_at', 'DESC')
                                     ->findAll();

        // 3. Hitung Statistik Valid (Siswa Lunas/Verified)
        $totalDaftar = count($referrals);
        $totalValid  = 0;
        foreach ($referrals as $r) {
            $status = strtoupper($r['status_pembayaran'] ?? '');
            if (in_array($status, ['LUNAS', 'VERIFIED', 'CICILAN'])) {
                $totalValid++;
            }
        }

        // 4. Logika Gamifikasi & Gelombang Fee
        $currYear = date('Y');
        $waves = [
            1 => ['label' => 'Gelombang 1', 'end' => $currYear . '-12-31', 'fee' => 75000],
            2 => ['label' => 'Gelombang 2', 'end' => ($currYear + 1) . '-03-31', 'fee' => 50000],
            3 => ['label' => 'Gelombang 3', 'end' => ($currYear + 1) . '-07-15', 'fee' => 35000],
        ];

        // 5. Hitung Bonus Progresif
        $targetStep  = 3; 
        $bonusAmount = 1000000; 
        $pencapaianBonus = ($targetStep > 0) ? floor($totalValid / $targetStep) : 0;
        $bonusDidapat    = $pencapaianBonus * $bonusAmount;
        $feeDasar        = $totalValid * $feeDefault;

        $data = [
            'title'      => 'Dashboard Mitra',
            'referrals'  => $referrals,
            'mitra'      => session()->get(), 
            'stats'      => [
                'total_daftar' => $totalDaftar,
                'total_valid'  => $totalValid,
                'total_leads'  => $totalDaftar,
                'total_lunas'  => $totalValid,
                'total_fee'    => $feeDasar + $bonusDidapat
            ],
            'gamification' => [
                'mode_skema'    => 'progresif',
                'waves'         => $waves,
                'active_wave'   => 2,
                'current_fee'   => $feeDefault,
                'fee_dasar'     => $feeDasar,
                'bonus_didapat' => $bonusDidapat,
                'target_step'   => $targetStep,
                'next_target'   => ($pencapaianBonus + 1) * $targetStep,
                'sisa_target'   => (($pencapaianBonus + 1) * $targetStep) - $totalValid,
                'bonus_amount'  => $bonusAmount
            ],
            'foundation' => $this->settingsModel ? $this->settingsModel->getSettingsAsArray('Global') : []
        ];

        return view('portal/affiliated/dashboard', $data);
    }

    public function logout()
    {
        session()->remove(['mitra_id', 'mitra_nama', 'mitra_kode', 'mitra_fee', 'mitra_logged_in']);
        return redirect()->to(base_url('portal/affiliated/login'));
    }
}