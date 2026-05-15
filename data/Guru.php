<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\UserModel;
use App\Models\Elearning\VirtualClassModel; 
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller untuk mengelola Master Data Guru (Admin Access) dan menyediakan akses ke modul lain.
 */
class Guru extends BaseController
{
    protected $guruModel;
    protected $userModel;
    protected $virtualClassModel; 
    
    // ID Role Guru - Sesuaikan dengan nilai di tabel 'roles'
    private const GURU_ROLE_ID = 2; 

    public function __construct()
    {
        $this->guruModel = new GuruModel();
        $this->userModel = new UserModel();
        $this->virtualClassModel = new VirtualClassModel(); 
        helper('form');
        $this->db = \Config\Database::connect();
    }

    /**
     * Menampilkan daftar semua data guru.
     * Logika di-update untuk menyertakan data relasional dan statistik yang dibutuhkan view.
     */
    public function index(): string
    {
        // 1. Ambil semua data guru
        $allGurus = $this->guruModel->orderBy('nama_lengkap', 'ASC')->findAll();

        // --- AKTIFKAN DEBUGGING SEMENTARA UNTUK CEK DATA (UNCOMMENT DI BAWAH) ---
        // Jika Anda melihat array kosong 'Array ( )', berarti:
        // A. Database kosong, atau
        // B. Semua data sudah di-soft-delete (karena GuruModel menggunakan useSoftDeletes = true).
        // echo '<pre>';
        // print_r($allGurus);
        // echo '</pre>';
        // die();
        // --- SELESAI DEBUGGING ---
        
        $gurusWithRelations = [];
        $totalGuruAktif = 0;
        $totalPNS = 0;
        $totalNonSertifikasi = 0;

        foreach ($allGurus as $guru) {
            // Asumsi: Ambil data relasi yang aktif/terkini (Membutuhkan model/query relasi yang sebenarnya)
            $kepegawaian = $this->_getLatestKepegawaian($guru['id']);
            $pendidikan = $this->_getHighestPendidikan($guru['id']);
            $penugasan = $this->_getLatestPenugasan($guru['id']);

            // Hitung statistik
            if ($kepegawaian && ($kepegawaian['status_kepegawaian'] !== 'Non-Aktif')) {
                $totalGuruAktif++;
            }
            if ($kepegawaian && in_array($kepegawaian['status_kepegawaian'], ['PNS', 'PPPK'])) {
                $totalPNS++;
            }
            // Asumsi: Ada field status_sertifikasi pada data kepegawaian
            if ($kepegawaian && ($kepegawaian['status_sertifikasi'] ?? 'Non-Sertifikasi') === 'Non-Sertifikasi') {
                 $totalNonSertifikasi++;
            }

            $gurusWithRelations[] = [
                'guru' => (object)$guru, 
                'kepegawaian_aktif' => (object)$kepegawaian,
                'pendidikan_tertinggi' => (object)$pendidikan,
                'penugasan_terkini' => (object)$penugasan,
            ];
        }
        
        $data = [
            'title'          => 'Master Data - Guru',
            'current_module' => 'master_data',
            'gurus'          => $gurusWithRelations, // FIX: Mengganti 'guru' menjadi 'gurus' dan menyediakan struktur data relasional
            'totalGuruAktif' => $totalGuruAktif,
            'totalPNS'       => $totalPNS,
            'totalNonSertifikasi' => $totalNonSertifikasi,
        ];
        
        return view('guru/index', $data);
    }

    /**
     * Menampilkan form untuk menambah guru baru.
     */
    public function new(): string
    {
        $data = [
            'title'          => 'Tambah Data Guru',
            'current_module' => 'master_data',
            'validation'     => session('validation') ?? \Config\Services::validation(),
        ];
        return view('guru/form', $data);
    }

    /**
     * Memproses data form untuk menambah guru baru.
     */
    public function create(): RedirectResponse
    {
        $rules = [
            'nuptk'         => 'required|max_length[16]|is_unique[guru.nuptk]',
            'nik'           => 'required|max_length[16]|is_unique[guru.nik]',
            'nama_lengkap'  => 'required|min_length[3]|max_length[255]',
            'username'      => 'required|alpha_dash|min_length[5]|is_unique[users.username]',
            'email'         => 'required|valid_email|is_unique[users.email]',
            'password'      => 'required|min_length[8]',
            'pass_confirm'  => 'required|matches[password]',
            // Tambahkan validasi untuk bidang guru lainnya di sini
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $postData = $this->request->getPost();
        
        $this->db->transBegin();

        try {
            // 1. Simpan data User
            $userData = [
                'id_role' => self::GURU_ROLE_ID, 
                'nama_lengkap' => $postData['nama_lengkap'],
                'username' => $postData['username'],
                'email' => $postData['email'],
                'password' => $postData['password'], // Password akan di-hash oleh UserModel/Auth
                'active' => 1, 
                'is_active' => 1, 
            ];
            $this->userModel->insert($userData);
            $newUserId = $this->userModel->getInsertID();

            // 2. Simpan data Guru
            $guruData = [
                'id_user' => $newUserId,
                'nuptk' => $postData['nuptk'],
                'nik' => $postData['nik'],
                // ... Bidang guru lainnya
            ];
            $this->guruModel->insert($guruData);

            $this->db->transCommit();
            return redirect()->to('app/guru')->with('success', 'Data guru dan akun login berhasil ditambahkan.');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal membuat Guru: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal membuat data guru. Silakan coba lagi. Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Menampilkan form edit data guru.
     */
    public function edit($id = null): string
    {
        $guru = $this->guruModel->find($id);
        if (!$guru) {
            throw PageNotFoundException::forPageNotFound('Data guru tidak ditemukan.');
        }

        $user = $this->userModel->find($guru['id_user']);
        
        if (!$user) {
              throw PageNotFoundException::forPageNotFound('Data user terkait guru tidak ditemukan.');
        }

        $data = [
            'title'          => 'Edit Data Guru',
            'current_module' => 'master_data',
            'guru'           => array_merge($guru, (array) $user), 
            'validation'     => session('validation') ?? \Config\Services::validation(), 
        ];
        return view('guru/form', $data);
    }
    
    /**
     * Memproses data form untuk memperbarui guru.
     */
    public function update($id = null): RedirectResponse
    {
        $existingGuru = $this->guruModel->find($id);
        if (!$existingGuru) {
            throw PageNotFoundException::forPageNotFound();
        }
        // Pastikan kita mendapatkan instance object/array user yang ada
        $existingUser = $this->userModel->find($existingGuru['id_user']);

        $postData = $this->request->getPost();
        
        // Aturan validasi (memperbolehkan pengecualian ID saat update)
        $rules = [
            'nuptk'         => 'required|max_length[16]|is_unique[guru.nuptk,id,' . $id . ']',
            'nik'           => 'required|max_length[16]|is_unique[guru.nik,id,' . $id . ']',
            'nama_lengkap'  => 'required|min_length[3]|max_length[255]',
            // Penting: Gunakan ID user untuk validasi unik username/email
            'username'      => 'required|alpha_dash|min_length[5]|is_unique[users.username,id,' . $existingUser['id'] . ']',
            'email'         => 'required|valid_email|is_unique[users.email,id,' . $existingUser['id'] . ']',
            'password'      => 'permit_empty|min_length[8]',
            'pass_confirm'  => 'matches[password]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $this->db->transBegin();

        try {
            // 1. Update data Guru
            $guruData = [
                'nuptk' => $postData['nuptk'],
                'nik' => $postData['nik'],
                // ... Bidang guru lainnya
            ];
            $this->guruModel->update($id, $guruData);

            // 2. Update data User
            $userData = [
                'nama_lengkap' => $postData['nama_lengkap'],
                'username' => $postData['username'],
                'email' => $postData['email'],
                'active' => $postData['active'] ?? 1,
                'is_active' => $postData['is_active'] ?? 1,
            ];
            if (!empty($postData['password'])) {
                // Password akan di-hash oleh UserModel/Auth
                $userData['password'] = $postData['password'];
            }

            $this->userModel->update($existingUser['id'], $userData);

            $this->db->transCommit();

            return redirect()->to('app/guru')->with('success', 'Data guru dan akun login berhasil diperbarui.');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal memperbarui Guru: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data guru. Silakan coba lagi. Error: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus data guru (Soft Delete).
     */
    public function delete($id = null): RedirectResponse
    {
        $guru = $this->guruModel->find($id);
        if (!$guru) {
            return redirect()->to('app/guru')->with('error', 'Data guru tidak ditemukan.');
        }

        $this->db->transBegin();

        try {
            // 1. Soft Delete Guru
            if (!$this->guruModel->delete($id)) {
                throw new \Exception('Gagal soft delete data guru.');
            }
            // 2. Soft Delete User terkait
            if (!$this->userModel->delete($guru['id_user'])) {
                throw new \Exception('Gagal menghapus data user.');
            }

            $this->db->transCommit();
            return redirect()->to('app/guru')->with('success', 'Data guru dan akun login berhasil dihapus.');

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal menghapus Guru: ' . $e->getMessage());
            return redirect()->to('app/guru')->with('error', 'Gagal menghapus data guru. Transaksi dibatalkan.');
        }
    }

    /**
     * CONTOH METHOD UNTUK ADMIN: Menampilkan dashboard E-learning secara umum.
     * Metode ini dapat digunakan admin untuk memonitor seluruh kelas virtual.
     */
    public function elearningIndex(): string
    {
        // Ambil SEMUA kelas virtual untuk ditampilkan kepada Admin
        $classes = $this->virtualClassModel
                        ->findAll();
        
        $data = [
            'title' => 'E-learning - Daftar Kelas Virtual',
            'current_module' => 'master_data', // Atau modul baru 'e_learning_admin'
            'classes' => $classes,
        ];

        return view('guru/elearning_dashboard', $data); // Asumsi view ini menampilkan daftar kelas
    }

    // =========================================================================
    // HELPER METHODS (Simulasi untuk memenuhi struktur data di View/guru/index.php)
    // =========================================================================

    /**
     * Simulasi mengambil data kepegawaian aktif/terbaru untuk seorang guru.
     * Dalam implementasi nyata, ini akan mengambil data dari tabel 'guru_kepegawaian'
     * dan memfilter status 'aktif' atau yang paling baru (terbaru).
     */
    private function _getLatestKepegawaian(int $guruId): array
    {
        // Gantikan dengan logika database yang sebenarnya (join/subquery)
        // Jika tidak ada data di DB, kembalikan array kosong atau null.
        return [
            'status_kepegawaian' => 'PNS',
            'jenis_ptk' => 'Guru Kelas',
            'status_sertifikasi' => 'Sertifikasi', // Digunakan untuk menghitung Non-Sertifikasi
        ];
    }

    /**
     * Simulasi mengambil data pendidikan tertinggi guru.
     * Dalam implementasi nyata, ini akan mengambil data dari tabel 'guru_pendidikan'
     * dan mengurutkan berdasarkan jenjang tertinggi (S3 > S2 > S1, dst).
     */
    private function _getHighestPendidikan(int $guruId): array
    {
        // Gantikan dengan logika database yang sebenarnya
        return [
            'jenjang' => 'S1',
            'bidang_studi' => 'Pendidikan Matematika',
        ];
    }

    /**
     * Simulasi mengambil tugas mengajar dan tugas tambahan terkini guru.
     * Dalam implementasi nyata, ini akan mengambil data dari tabel 'guru_penugasan'
     * berdasarkan Tahun Ajaran aktif dan status 'aktif'.
     */
    private function _getLatestPenugasan(int $guruId): array
    {
        // Gantikan dengan logika database yang sebenarnya
        return [
            'mapel_diampu' => 'Matematika Wajib (X, XI)',
            'tugas_tambahan' => 'Wakil Kepala Sekolah Bidang Kurikulum',
        ];
    }
}