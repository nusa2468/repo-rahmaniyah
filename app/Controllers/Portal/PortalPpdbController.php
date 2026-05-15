<?php

namespace App\Controllers\Portal;

use App\Controllers\BaseController;
// Models
use App\Models\Portal\PortalPpdbModel; 
use App\Models\Ppdb\AffiliateModel;
use App\Models\JenjangModel; 

/**
 * PortalPpdbController
 * Mengelola interaksi pendaftar/calon siswa di area publik portal.
 * Status: FIXED (Renamed daftar() -> register() to match Routes)
 */
class PortalPpdbController extends BaseController
{
    protected $ppdbModel = null;
    protected $affiliateModel = null;
    protected $jenjangModel = null;
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        // Safe Load Models (Mencegah Error 500 jika file model belum ada)
        if (class_exists('App\Models\Portal\PortalPpdbModel')) {
            $this->ppdbModel = new PortalPpdbModel();
        }
        
        if (class_exists('App\Models\Ppdb\AffiliateModel')) {
            $this->affiliateModel = new AffiliateModel();
        }
        
        if (class_exists('App\Models\JenjangModel')) {
            $this->jenjangModel = new JenjangModel();
        }
    }

    public function index()
    {
        $data = ['title' => 'Portal Penerimaan Peserta Siswa Baru'];
        return view('portal/ppdb/home', $data); // Sesuaikan dengan view landing page Anda
    }

    public function login()
    {
        if (session()->get('pendaftar_logged_in')) {
            return redirect()->to(base_url('portal/ppdb/cek-status'));
        }
        return view('portal/ppdb/login', ['title' => 'Login Pendaftar PPDB']);
    }

    public function attemptLogin()
    {
        $authKey = $this->request->getPost('auth_key'); // NIK atau No Pendaftaran
        
        $pendaftar = null;
        if ($this->ppdbModel) {
            $pendaftar = $this->ppdbModel->where('nik', $authKey)
                                         ->orWhere('no_pendaftaran', $authKey)
                                         ->first();
        } else {
            // Fallback Query Builder jika Model belum ada
            $pendaftar = $this->db->table('ppdb_pendaftar') // Pastikan nama tabel benar
                                  ->where('nik', $authKey)
                                  ->orWhere('no_pendaftaran', $authKey)
                                  ->get()->getRowArray();
        }

        if ($pendaftar) {
            // Normalisasi Akses Data (Object vs Array)
            $pendaftar = (array) $pendaftar; 
            
            session()->set([
                'pendaftar_id'        => $pendaftar['id'] ?? $pendaftar['pendaftar_id'],
                'pendaftar_nama'      => $pendaftar['nama_lengkap'],
                'pendaftar_no'        => $pendaftar['no_pendaftaran'],
                'pendaftar_logged_in' => true,
            ]);
            return redirect()->to(base_url('portal/ppdb/cek-status'));
        }

        return redirect()->back()->with('error', 'Data tidak ditemukan. Silakan cek kembali NIK/No Pendaftaran Anda.');
    }

    /**
     * [FIXED] Form Pendaftaran Online
     * Sebelumnya bernama 'daftar', diubah menjadi 'register' sesuai Routes.php
     */
    public function register()
    {
        $units = [];

        // 1. Ambil data Unit/Jenjang
        if ($this->jenjangModel) {
            $units = $this->jenjangModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        } elseif ($this->db->tableExists('jenjang')) {
            $units = $this->db->table('jenjang')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get()->getResultArray();
        } else {
            // Data Default jika tabel kosong
            $units = [
                ['kode_jenjang' => 'TK', 'nama_jenjang' => 'TK Islam Terpadu'],
                ['kode_jenjang' => 'SD', 'nama_jenjang' => 'SD Islam Terpadu'],
                ['kode_jenjang' => 'SMP', 'nama_jenjang' => 'SMP Islam Terpadu'],
                ['kode_jenjang' => 'SMA', 'nama_jenjang' => 'SMA Islam Terpadu'],
            ];
        }

        // 2. Ambil Data Afiliasi
        $affiliates = [];
        if ($this->affiliateModel) {
            $affiliates = $this->affiliateModel->where('status', 'Aktif')->findAll();
        } elseif ($this->db->tableExists('ppdb_affiliate')) {
            $affiliates = $this->db->table('ppdb_affiliate')->where('status', 'Aktif')->get()->getResultArray();
        }

        // 3. Filter & Map Data Unit
        $filteredUnits = [];
        foreach ($units as $unit) {
            $u = (array) $unit; // Pastikan Array
            $kode = strtoupper($u['kode_jenjang'] ?? '');
            
            if ($kode === 'GLOBAL') continue;

            $u['nama_sekolah'] = $u['nama_sekolah'] ?? $u['nama_jenjang'] ?? $kode;
            $filteredUnits[] = $u;
        }

        $data = [
            'title'      => 'Formulir Pendaftaran Siswa Baru',
            'affiliates' => $affiliates,
            'units'      => $filteredUnits,
            'validation' => \Config\Services::validation(),
        ];
        
        return view('portal/ppdb/register', $data);
    }

    public function submit()
    {
        $rules = [
            'kode_jenjang'   => 'required',
            'nama_lengkap'   => 'required|min_length[3]',
            'nik'            => 'required|exact_length[16]', 
            'nisn'           => 'required|exact_length[10]',
            'no_hp_whatsapp' => 'required',
            'jenis_kelamin'  => 'required|in_list[L,P]',
            'asal_sekolah'   => 'required',
            'bukti_setor'    => 'uploaded[bukti_setor]|max_size[bukti_setor,2048]|ext_in[bukti_setor,jpg,jpeg,png,pdf]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. Mohon lengkapi data.')->with('errors', $this->validator->getErrors());
        }

        $formData = $this->request->getPost();
        
        // Upload Bukti Setor
        $file = $this->request->getFile('bukti_setor');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $path = FCPATH . 'uploads/ppdb/bukti_bayar';
            if (!is_dir($path)) mkdir($path, 0777, true);
            
            $file->move($path, $newName);
            $formData['bukti_setor'] = $newName;
        }

        // Logic Tambahan
        $currYear = date('Y');
        $formData['tahun_ajaran'] = (date('n') > 6) ? "$currYear/".($currYear+1) : ($currYear-1)."/$currYear";
        
        // Generate No Pendaftaran
        if ($this->ppdbModel && method_exists($this->ppdbModel, 'generateNoPendaftaran')) {
            $formData['no_pendaftaran'] = $this->ppdbModel->generateNoPendaftaran($formData['kode_jenjang']);
        } else {
            // Fallback Generator
            $formData['no_pendaftaran'] = $formData['kode_jenjang'] . date('ymd') . rand(100,999);
        }

        $formData['status_seleksi']    = 'Pending';
        $formData['status_pembayaran'] = 'Menunggu Verifikasi';
        $formData['created_at']        = date('Y-m-d H:i:s');

        // Handling Afiliasi Fee
        if (!empty($formData['kode_afiliasi'])) {
            $agen = null;
            if ($this->affiliateModel) {
                $agen = $this->affiliateModel->where('kode_agen', $formData['kode_afiliasi'])->first();
            } elseif ($this->db->tableExists('ppdb_affiliate')) {
                $agen = $this->db->table('ppdb_affiliate')->where('kode_agen', $formData['kode_afiliasi'])->get()->getRowArray();
            }

            if ($agen) {
                $agen = (array) $agen;
                $formData['nominal_fee'] = $agen['fee_per_siswa'] ?? 0;
                $formData['status_fee']  = 'Pending';
            }
        }

        // Simpan Data
        $saved = false;
        if ($this->ppdbModel) {
            $saved = $this->ppdbModel->save($formData);
        } else {
            // Fallback Save
            $saved = $this->db->table('ppdb_pendaftar')->insert($formData);
        }

        if ($saved) {
            session()->setFlashdata('registered_unit', $formData['kode_jenjang']);
            return redirect()->to(base_url('portal/ppdb/success/' . $formData['no_pendaftaran']));
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data ke database.');
    }

    public function sukses($no_reg)
    {
        $pendaftar = null;
        
        if ($this->ppdbModel) {
            $pendaftar = $this->ppdbModel->where('no_pendaftaran', $no_reg)->first();
        } else {
            $pendaftar = $this->db->table('ppdb_pendaftar')->where('no_pendaftaran', $no_reg)->get()->getRowArray();
        }

        if (!$pendaftar) return redirect()->to(base_url('portal/ppdb/home'))->with('error', 'Data tidak ditemukan.');

        return view('portal/ppdb/success', [
            'title' => 'Pendaftaran Berhasil',
            'siswa' => (array) $pendaftar,
            'id_pendaftaran' => $no_reg // Kompatibilitas view
        ]);
    }

    public function cekStatus()
    {
        if (!session()->get('pendaftar_logged_in')) {
            return redirect()->to(base_url('portal/ppdb/login'));
        }

        $id = session()->get('pendaftar_id');
        $siswa = null;

        if ($this->ppdbModel) {
            $siswa = $this->ppdbModel->find($id);
        } else {
            $siswa = $this->db->table('ppdb_pendaftar')->where('id', $id)->get()->getRowArray();
        }

        return view('portal/ppdb/status', [
            'title' => 'Status Pendaftaran',
            'siswa' => (array) $siswa,
            'user'  => (array) $siswa // Kompatibilitas view
        ]);
    }

    public function logout()
    {
        session()->remove(['pendaftar_id', 'pendaftar_nama', 'pendaftar_no', 'pendaftar_logged_in']);
        return redirect()->to(base_url('portal/ppdb/login'));
    }
}