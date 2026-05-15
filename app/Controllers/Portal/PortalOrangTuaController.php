<?php

namespace App\Controllers\Portal; // Namespace disesuaikan dengan folder Portal

use App\Controllers\BaseController;
use App\Models\AbsensiSiswaModel;
use App\Models\SiswaModel;
use App\Models\JadwalPelajaranModel; 
use App\Models\MataPelajaranModel; 
use App\Models\KelasModel; 
use App\Models\OrangTuaModel; // Ditambahkan: Model Orang Tua
use CodeIgniter\I18n\Time;

// PENTING: Controller ini diasumsikan berjalan setelah user (Orang Tua) berhasil login
// dan ID Siswa yang bersangkutan telah teridentifikasi.

class PortalOrangTuaController extends BaseController 
{
    protected $absensiModel;
    protected $siswaModel;
    protected $jadwalModel;
    protected $mapelModel;
    protected $kelasModel;
    protected $orangTuaModel; // Properti baru untuk Model OrangTua

    public function __construct()
    {
        // Inisialisasi semua model yang mungkin dibutuhkan
        $this->absensiModel = new AbsensiSiswaModel();
        $this->siswaModel = new SiswaModel();
        $this->jadwalModel = new JadwalPelajaranModel(); 
        $this->mapelModel = new MataPelajaranModel(); 
        $this->kelasModel = new KelasModel(); 
        $this->orangTuaModel = new OrangTuaModel(); // Inisialisasi Model OrangTua
    }

    // Fungsi utama: Menampilkan Dasbor atau Profil Orang Tua
    public function index()
    {
        // PENTING: Ambil ID Siswa dari Sesi Login Orang Tua
        $id_siswa_login = session()->get('id_siswa_login'); 
        $id_siswa_yang_diakses = $id_siswa_login ?? 1;

        // Ambil data profil Orang Tua berdasarkan ID Siswa
        $profile = $this->orangTuaModel->where('id_siswa', $id_siswa_yang_diakses)->first();
        
        // Ambil data Siswa
        $siswa = $this->siswaModel->select('id, nama_lengkap, nis')->find($id_siswa_yang_diakses);

        $data = [
            'title' => 'Dasbor Portal Orang Tua',
            'current_module' => 'portal_ortu',
            'siswa' => $siswa,
            'profile' => $profile,
            // 'message' => 'Selamat datang di Portal Orang Tua!',
        ];

        return view('portal_ortu/dashboard', $data); // Buat view 'portal_ortu/dashboard.php'
    }

    // Fungsi untuk menampilkan rekap absensi satu siswa (Fungsi yang sudah ada)
    public function rekapAbsensiSiswa()
    {
        // PENTING: Ambil ID Siswa dari Sesi Login Orang Tua
        $id_siswa_login = session()->get('id_siswa_login'); 
        $id_siswa_yang_diakses = $id_siswa_login ?? 1; // Fallback ke ID 1 jika sesi tidak ditemukan
        
        // 1. Ambil Data Siswa
        $siswa = $this->siswaModel->select('id, nis, nama_lengkap, id_kelas')->find($id_siswa_yang_diakses);
        
        if (!$siswa) {
            return view('errors/html/error_404', ['message' => 'Data Siswa tidak ditemukan atau sesi login tidak valid.']);
        }

        // 2. Ambil Rekap Absensi Siswa (Contoh: 30 hari terakhir)
        $rekap_absensi = $this->absensiModel
            ->select('absensi_siswa.*, j.nama_mapel, k.nama_kelas')
            ->join('jadwal_pelajaran jp', 'jp.id = absensi_siswa.id_jadwal', 'left')
            ->join('mata_pelajaran j', 'j.id = jp.id_mapel', 'left')
            ->join('kelas k', 'k.id = jp.id_kelas', 'left')
            ->where('absensi_siswa.id_siswa', $id_siswa_yang_diakses) 
            ->orderBy('absensi_siswa.tanggal', 'DESC')
            ->limit(30) 
            ->findAll();

        $data = [
            'title' => 'Rekap Absensi Siswa',
            'current_module' => 'portal_ortu',
            'siswa' => $siswa,
            'rekap_absensi' => $rekap_absensi,
        ];

        return view('portal_ortu/rekap_absensi', $data);
    }
    
    // CRUD: Metode untuk mengedit profil Orang Tua (R/U)
    public function editProfile()
    {
        // PENTING: Ambil ID Siswa dari Sesi Login Orang Tua
        $id_siswa_login = session()->get('id_siswa_login'); 
        $id_siswa_yang_diakses = $id_siswa_login ?? 1;

        // Ambil data profil Orang Tua berdasarkan ID Siswa
        $profile = $this->orangTuaModel->where('id_siswa', $id_siswa_yang_diakses)->first();
        
        if (!$profile) {
            // Jika data profil belum ada, sediakan form untuk Create (C)
            // Ini biasanya terjadi jika data profil orang tua belum diinput sama sekali
            $profile = $this->orangTuaModel->where('id_siswa', $id_siswa_yang_diakses)->getNewObject();
            $profile->id_siswa = $id_siswa_yang_diakses;
        }

        $data = [
            'title' => 'Edit Profil Orang Tua',
            'current_module' => 'portal_ortu',
            'profile' => $profile,
        ];
        
        return view('portal_ortu/edit_profile', $data); // Buat view 'portal_ortu/edit_profile.php'
    }

    // CRUD: Metode untuk menyimpan atau memperbarui profil Orang Tua (C/U)
    public function updateProfile()
    {
        // PENTING: Ambil ID Siswa dari Sesi Login Orang Tua
        $id_siswa_login = session()->get('id_siswa_login'); 
        $id_siswa_yang_diakses = $id_siswa_login ?? 1;

        // Ambil data lama (jika ada)
        $old_profile = $this->orangTuaModel->where('id_siswa', $id_siswa_yang_diakses)->first();
        $id_ortu = $old_profile['id'] ?? null; // ID Orang Tua (jika sudah ada)

        // Validasi dan set data
        if (!$this->validate($this->orangTuaModel->validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_siswa' => $id_siswa_yang_diakses, // Pastikan ID Siswa ikut tersimpan
            'nama_ayah' => $this->request->getPost('nama_ayah'),
            'pekerjaan_ayah' => $this->request->getPost('pekerjaan_ayah'),
            'telepon_ayah' => $this->request->getPost('telepon_ayah'),
            'nama_ibu' => $this->request->getPost('nama_ibu'),
            'pekerjaan_ibu' => $this->request->getPost('pekerjaan_ibu'),
            'telepon_ibu' => $this->request->getPost('telepon_ibu'),
            'alamat' => $this->request->getPost('alamat'),
        ];

        if ($id_ortu) {
            // Update (U) data yang sudah ada
            $this->orangTuaModel->update($id_ortu, $data);
            $message = 'Profil Orang Tua berhasil diperbarui.';
        } else {
            // Insert (C) data baru
            $this->orangTuaModel->insert($data);
            $message = 'Profil Orang Tua berhasil ditambahkan.';
        }

        return redirect()->to(site_url('portal/orangtua'))->with('success', $message);
    }
}