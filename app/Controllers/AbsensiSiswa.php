<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AbsensiSiswaModel;
use App\Models\JadwalPelajaranModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\RedirectResponse; // Tambahkan untuk type hinting yang lebih baik

class AbsensiSiswa extends BaseController
{
    protected $kelasModel;
    protected $jadwalModel;
    protected $siswaModel;
    protected $absensiModel;
    protected $tahunAjaranModel;

    public function __construct()
    {
        // Pastikan semua model diinisialisasi
        $this->kelasModel = new KelasModel();
        $this->jadwalModel = new JadwalPelajaranModel();
        $this->siswaModel = new SiswaModel();
        $this->absensiModel = new AbsensiSiswaModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
        helper('form');
    }

    /**
     * Menampilkan halaman utama pemilihan kelas dan tanggal absensi.
     */
    public function index(): string
    {
        // Ambil hanya kelas dari tahun ajaran yang aktif
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        // Hanya ambil kelas yang terdaftar di tahun ajaran aktif
        $kelas = $tahunAjaranAktif ? $this->kelasModel->where('id_tahun_ajaran', $tahunAjaranAktif['id'])->findAll() : [];

        $data = [
            'title' => 'Manajemen Absensi Siswa',
            'current_module' => 'kurikulum',
            'kelas' => $kelas,
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
        ];
        
        return view('absensi_siswa/index', $data);
    }

    /**
     * Memproses pemilihan kelas dan tanggal, lalu menampilkan form pengisian absensi.
     */
    public function kelola(): string|RedirectResponse
    {
        $id_kelas = $this->request->getVar('id_kelas');
        $tanggal = $this->request->getVar('tanggal');

        if (!$id_kelas || !$tanggal) {
            // Menggunakan path yang konsisten '/absensi_siswa'
            return redirect()->to(base_url('app/absensi_siswa'))->with('error', 'Silakan pilih kelas dan tanggal terlebih dahulu.');
        }

        $nama_hari = $this->getNamaHari($tanggal);

        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        if(!$tahunAjaranAktif){
            // Menggunakan path yang konsisten '/absensi_siswa'
            return redirect()->to(base_url('app/absensi_siswa'))->with('error', 'Tidak ada Tahun Ajaran yang aktif. Silakan atur di Master Data.');
        }

        // Ambil jadwal pelajaran berdasarkan ID kelas, nama hari, dan tahun ajaran aktif
        $jadwal_hari_ini = $this->jadwalModel->getJadwalDetail($id_kelas, $nama_hari, $tahunAjaranAktif['id']);

        if (empty($jadwal_hari_ini)) {
            // Mengambil nama kelas untuk pesan error yang informatif
            $detail_kelas = $this->kelasModel->find($id_kelas);
            $nama_kelas = $detail_kelas['nama_kelas'] ?? $id_kelas;

            // Menggunakan path yang konsisten '/absensi_siswa'
            return redirect()->to(base_url('app/absensi_siswa'))->with('error', 'Tidak ada jadwal pelajaran untuk kelas ' . esc($nama_kelas) . ' pada hari ' . esc($nama_hari) . ' (' . Time::parse($tanggal)->toLocalizedString('d MMMM yyyy') . ') di tahun ajaran aktif.');
        }

        // --- PENGAMBILAN DATA SISWA (JOIN dengan siswa_enrollment) ---
        $siswa_di_kelas_raw = $this->siswaModel
            ->select('siswa.id, siswa.nis, siswa.nama_lengkap AS nama_siswa') 
            ->join('siswa_enrollment', 'siswa_enrollment.id_siswa = siswa.id')
            ->where('siswa_enrollment.id_kelas', $id_kelas)
            ->where('siswa_enrollment.status_akademik', 'Aktif') // Hanya siswa yang aktif di kelas tersebut
            ->findAll();
        
        // --- PENGAMBILAN NAMA KELAS ---
        $detail_kelas = $this->kelasModel->find($id_kelas);
        $nama_kelas_display = $detail_kelas['nama_kelas'] ?? 'N/A';


        $data = [
            'title'              => 'Kelola Absensi: Kelas ' . esc($nama_kelas_display) . ', Tgl ' . Time::parse($tanggal)->toLocalizedString('d MMMM yyyy'),
            'current_module'     => 'kurikulum',
            'nama_kelas_display' => $nama_kelas_display, 
            'siswa_di_kelas'     => $siswa_di_kelas_raw,
            'jadwal_hari_ini'    => $jadwal_hari_ini,
            'id_kelas'           => $id_kelas,
            'tanggal'            => $tanggal,
            'nama_hari'          => $nama_hari,
            'absensi_tersimpan'  => $this->getAbsensiTersimpan($jadwal_hari_ini, $tanggal)
        ];

        return view('absensi_siswa/kelola', $data);
    }

    /**
     * Menyimpan data absensi yang disubmit dari form.
     */
    public function simpan(): RedirectResponse
    {
        // 'absensi' akan berisi: [id_siswa] => [id_jadwal] => status
        $absensi_data = $this->request->getPost('absensi');
        $tanggal = $this->request->getPost('tanggal');
        
        if (empty($absensi_data)) {
            return redirect()->back()->with('error', 'Tidak ada data absensi yang dipilih untuk disimpan.');
        }

        $data_to_save = [];
        $submitted_jadwal_ids = [];

        foreach ($absensi_data as $id_siswa => $jadwal_absensi) {
            foreach ($jadwal_absensi as $id_jadwal => $status_kehadiran) {
                // Status kehadiran menggunakan string ENUM lengkap (hadir, sakit, izin, alpa)
                if (!empty($status_kehadiran)) { 
                    $data_to_save[] = [
                        'id_jadwal' => $id_jadwal,
                        'id_siswa' => $id_siswa,
                        'tanggal' => $tanggal,
                        'status' => $status_kehadiran, // String ENUM lengkap
                        'keterangan' => '' 
                    ];
                    $submitted_jadwal_ids[] = $id_jadwal;
                }
            }
        }
        
        if (empty($data_to_save)) {
             return redirect()->back()->with('error', 'Tidak ada data absensi yang valid untuk disimpan.');
        }

        // --- VALIDASI ID JADWAL (Penting untuk integritas data) ---
        $unique_jadwal_ids = array_unique($submitted_jadwal_ids);
        $count_in_db = $this->jadwalModel->whereIn('id', $unique_jadwal_ids)->countAllResults();

        if ($count_in_db !== count($unique_jadwal_ids)) {
            $valid_jadwal_q = $this->jadwalModel->select('id')->whereIn('id', $unique_jadwal_ids)->findAll();
            $valid_ids = array_column($valid_jadwal_q, 'id');
            $invalid_ids = array_diff($unique_jadwal_ids, $valid_ids);
            $error_message = "Gagal menyimpan: Ditemukan ID Jadwal tidak valid (" . implode(', ', $invalid_ids) . ").";
            return redirect()->back()->with('error', $error_message);
        }
        // --- AKHIR VALIDASI ---

        // Ambil ID jadwal yang valid dari data yang disubmit
        $unique_jadwal_ids_from_data = array_unique(array_column($data_to_save, 'id_jadwal')); 

        // Hapus data absensi yang sudah ada untuk tanggal dan jadwal ini (Mekanisme Upsert)
        $this->absensiModel->where('tanggal', $tanggal)
                            ->whereIn('id_jadwal', $unique_jadwal_ids_from_data)
                            ->delete();

        // Simpan data baru
        $success = $this->absensiModel->insertBatch($data_to_save);
        
        // Asumsi berhasil jika insertBatch tidak mengembalikan FALSE/Throwable
        if ($success !== false) {
            return redirect()->to(base_url('app/absensi_siswa'))->with('success', 'Data absensi berhasil disimpan untuk ' . Time::parse($tanggal)->toLocalizedString('d MMMM yyyy') . '.');
        } else {
            // Tangkap kegagalan yang disebabkan oleh DB (misalnya error Foreign Key, dll)
            return redirect()->back()->with('error', 'Gagal menyimpan data absensi. Pastikan semua Foreign Key (id_siswa, id_jadwal) valid.');
        }
    }

    /**
     * Helper untuk mendapatkan nama hari dalam Bahasa Indonesia dari tanggal.
     * @param string $tanggal
     * @return string
     */
    private function getNamaHari(string $tanggal): string
    {
        // Ubah format hari ke Bahasa Indonesia
        $time = Time::parse($tanggal);
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[$time->getDayOfWeek()];
    }

    /**
     * Mengambil data absensi yang sudah tersimpan dalam format map [id_siswa][id_jadwal] = status.
     * @param array $jadwal Array detail jadwal hari ini.
     * @param string $tanggal Tanggal absensi.
     * @return array
     */
    private function getAbsensiTersimpan(array $jadwal, string $tanggal): array
    {
        // Mengambil semua ID Jadwal dari jadwal hari ini
        $jadwal_ids = array_column($jadwal, 'id');
        if (empty($jadwal_ids)) {
            return [];
        }

        // Mengambil data absensi dari database
        $result = $this->absensiModel
            ->where('tanggal', $tanggal)
            ->whereIn('id_jadwal', $jadwal_ids)
            ->findAll();

        // Memetakan hasil ke format [id_siswa][id_jadwal] = status
        $absensi_map = [];
        foreach ($result as $item) {
            $absensi_map[$item['id_siswa']][$item['id_jadwal']] = $item['status']; 
        }
        return $absensi_map;
    }
}