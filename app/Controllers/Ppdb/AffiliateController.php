<?php

namespace App\Controllers\Ppdb;

use App\Controllers\BaseController;
use App\Models\Ppdb\PendaftarModel;
use App\Models\Ppdb\AffiliateModel;
use App\Models\SettingsModel; 

/**
 * AffiliateController - Modul PPDB (Enterprise Edition)
 * Mengelola data agen marketing, statistik afiliasi, komisi pendaftaran, dan konfigurasi skema.
 */
class AffiliateController extends BaseController
{
    protected array $privilegedRoles = ['superadmin', 'yayasan'];

    protected $pendaftarModel;
    protected $affiliateModel;
    protected $settingsModel;

    public function __construct()
    {
        $this->pendaftarModel = new PendaftarModel();
        $this->affiliateModel = new AffiliateModel();
        
        if (file_exists(APPPATH . 'Models/SettingsModel.php')) {
            $this->settingsModel = new SettingsModel();
        }
    }

    /**
     * Dashboard Afiliasi Terpadu (Admin View)
     */
    public function index()
    {
        $filterJenjang = $this->request->getGet('jenjang');
        $agen = $this->affiliateModel->getPerformanceStats($filterJenjang);
        
        $globalKpi = [
            'total_leads' => 0,
            'total_lunas' => 0,
            'total_fee'   => 0,
        ];

        if (!empty($agen)) {
            foreach ($agen as $a) {
                $globalKpi['total_leads'] += (int)($a->total_leads ?? 0);
                $globalKpi['total_lunas'] += (int)($a->total_lunas ?? 0);
                $globalKpi['total_fee']   += (float)($a->total_potensi_fee ?? 0); 
            }
        }

        $data = [
            'title'         => 'Dashboard & Database Afiliasi',
            'stats'         => $globalKpi,
            'agen'          => $agen,
            'currentModule' => 'ppdb',
            'active_menu'   => 'ppdb_affiliate'
        ];

        return view('ppdb/affiliate/index', $data);
    }

    public function detail($id)
    {
        $agen = $this->affiliateModel->find($id);
        if (!$agen || !$this->checkAccess($agen)) return redirect()->to(base_url('app/ppdb/affiliate'))->with('error', 'Data tidak ditemukan.');

        $siswa = $this->pendaftarModel->where('kode_afiliasi', $agen->kode_agen)->orderBy('created_at', 'DESC')->findAll();

        $data = ['title' => 'Detail Performa: ' . $agen->nama_agen, 'agen' => $agen, 'siswa' => $siswa, 'currentModule' => 'ppdb', 'active_menu' => 'ppdb_affiliate'];
        return view('ppdb/affiliate/agen_detail', $data);
    }

    public function addAgen()
    {
        $data = ['title' => 'Registrasi Agen Baru', 'agen' => null, 'currentModule' => 'ppdb', 'active_menu' => 'ppdb_affiliate'];
        return view('ppdb/affiliate/form', $data);
    }

    public function editAgen($id)
    {
        $agen = $this->affiliateModel->find($id);
        if (!$agen || !$this->checkAccess($agen)) return redirect()->to(base_url('app/ppdb/affiliate'))->with('error', 'Akses ditolak.');

        $data = ['title' => 'Edit Profil Agen: ' . $agen->nama_agen, 'agen' => $agen, 'currentModule' => 'ppdb', 'active_menu' => 'ppdb_affiliate'];
        return view('ppdb/affiliate/form', $data);
    }

    public function saveAgen($id = null)
    {
        $session = session();
        $data    = $this->request->getPost();
        
        if ($id) {
            $existing = $this->affiliateModel->find($id);
            if (!$existing || !$this->checkAccess($existing)) return redirect()->back()->with('error', 'Akses ilegal.');
            $data['affiliate_id'] = $id;
        } else {
            $jenjang = $session->get('kode_jenjang');
            if (in_array($session->get('role_name'), $this->privilegedRoles)) {
                $jenjang = $data['kode_jenjang'] ?? 'GLOBAL';
            }
            $data['kode_jenjang'] = $jenjang;
            
            if (empty($data['kode_agen'])) {
                $data['kode_agen'] = $this->affiliateModel->generateKodeAgen($jenjang);
            }
            
            if (empty($data['status'])) {
                $data['status'] = 'Aktif'; 
            }
        }

        if ($this->affiliateModel->save($data)) {
            $msg = $id ? 'Data profil agen berhasil diperbarui.' : 'Agen baru berhasil didaftarkan.';
            return redirect()->to(base_url('app/ppdb/affiliate'))->with('success', $msg);
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data agen.');
    }

    public function deleteAgen($id)
    {
        $agen = $this->affiliateModel->find($id);
        if (!$agen || !$this->checkAccess($agen)) return redirect()->back()->with('error', 'Akses ditolak.');

        if ($this->affiliateModel->delete($id)) return redirect()->to(base_url('app/ppdb/affiliate'))->with('success', 'Agen berhasil dinonaktifkan.');
        return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data.');
    }

    /**
     * [UPDATED] Manajemen Komisi / Fee Pencairan
     * Menampilkan daftar leads dengan detail agen (JOIN) dan status fee.
     */
    public function fee()
    {
        // Menggunakan Query Builder untuk JOIN dengan tabel Affiliates
        // Agar admin bisa melihat Nama Agen & Rekening, bukan cuma kode.
        $builder = $this->pendaftarModel->builder();
        $builder->select('pendaftar_biodata.*, affiliates.nama_agen, affiliates.nama_bank, affiliates.nomor_rekening, affiliates.nama_rekening');
        $builder->join('affiliates', 'affiliates.kode_agen = pendaftar_biodata.kode_afiliasi', 'left');
        
        $builder->where('pendaftar_biodata.kode_afiliasi !=', null);
        $builder->where('pendaftar_biodata.kode_afiliasi !=', '');
        
        // Filter berdasarkan status pembayaran siswa (Prioritas Lunas)
        // $builder->whereIn('pendaftar_biodata.status_pembayaran', ['Lunas', 'Verified']); // Opsional jika ingin filter ketat

        $builder->orderBy('pendaftar_biodata.created_at', 'DESC');
        
        $pendaftar = $builder->get()->getResult(); // Mengembalikan Object

        $data = [
            'title'         => 'Monitoring Komisi Afiliasi',
            'pendaftar'     => $pendaftar,
            'currentModule' => 'ppdb',
            'active_menu'   => 'ppdb_affiliate'
        ];

        return view('ppdb/affiliate/fee_list', $data);
    }

    /**
     * [BARU] Proses Pembayaran Fee
     * Menandai fee siswa tertentu sebagai 'Dibayar'
     */
    public function bayarFee($id)
    {
        // Cek Hak Akses
        if (!in_array(session('role_name'), $this->privilegedRoles)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $siswa = $this->pendaftarModel->find($id);
        if (!$siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan.');
        }

        // Update status fee
        // Pastikan field status_fee ada di allowedFields PendaftarModel
        $this->pendaftarModel->update($id, [
            'status_fee' => 'Dibayar'
        ]);

        return redirect()->back()->with('success', 'Komisi berhasil ditandai sebagai Dibayar.');
    }

    /**
     * Halaman Konfigurasi Global Skema Fee & Bonus
     */
    public function konfigurasi()
    {
        $session = session();
        if (!in_array($session->get('role_name'), $this->privilegedRoles)) {
            return redirect()->to(base_url('app/ppdb/affiliate'))->with('error', 'Akses konfigurasi terbatas.');
        }

        $settings = $this->settingsModel ? $this->settingsModel->getSettingsAsArray('Global') : [];
        $currYear = date('Y');
        $defaults = [
            'ppdb_wave1_label' => 'Gelombang 1 (Early Bird)', 'ppdb_wave1_fee' => 750000, 'ppdb_wave1_end' => $currYear . '-12-31',
            'ppdb_wave2_label' => 'Gelombang 2 (Reguler)',    'ppdb_wave2_fee' => 500000, 'ppdb_wave2_end' => ($currYear + 1) . '-03-31',
            'ppdb_wave3_label' => 'Gelombang 3 (Last Call)',  'ppdb_wave3_fee' => 350000, 'ppdb_wave3_end' => ($currYear + 1) . '-07-15',
            'ppdb_target_step' => 3, 'ppdb_bonus_amt' => 1000000,
        ];

        $finalSettings = array_merge($defaults, $settings);
        $data = [
            'title'         => 'Konfigurasi Skema Komisi & Bonus',
            'settings'      => $finalSettings,
            'currentModule' => 'ppdb',
            'active_menu'   => 'ppdb_affiliate_config' 
        ];

        return view('ppdb/affiliate/konfigurasi', $data);
    }

    public function saveKonfigurasi()
    {
        $session = session();
        if (!in_array($session->get('role_name'), $this->privilegedRoles)) return redirect()->back()->with('error', 'Akses ditolak.');
        if (!$this->settingsModel) return redirect()->back()->with('error', 'Model Settings tidak ditemukan.');

        $inputs = $this->request->getPost();
        
        $configKeys = [
            'ppdb_wave1_label', 'ppdb_wave1_fee', 'ppdb_wave1_end',
            'ppdb_wave2_label', 'ppdb_wave2_fee', 'ppdb_wave2_end',
            'ppdb_wave3_label', 'ppdb_wave3_fee', 'ppdb_wave3_end',
            'ppdb_target_step', 'ppdb_bonus_amt'
        ];

        $count = 0;
        foreach ($configKeys as $key) {
            if (isset($inputs[$key])) {
                $this->settingsModel->updateSetting('Global', $key, $inputs[$key]);
                $count++;
            }
        }

        return redirect()->to(base_url('app/ppdb/affiliate/konfigurasi'))->with('success', "Berhasil memperbarui $count pengaturan skema.");
    }

    protected function checkAccess($data)
    {
        $session = session();
        $role    = $session->get('role_name');
        $jenjang = $session->get('kode_jenjang');
        if (in_array($role, $this->privilegedRoles)) return true;
        $dataJenjang = is_object($data) ? $data->kode_jenjang : ($data['kode_jenjang'] ?? '');
        return $dataJenjang === $jenjang;
    }
}