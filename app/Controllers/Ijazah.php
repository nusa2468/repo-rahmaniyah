<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SiswaModel;
use App\Models\SiswaDemografiModel;
use App\Models\SettingsModel;

/**
 * Controller untuk mengelola data Ijazah dan kelulusan.
 */
class Ijazah extends BaseController
{
    protected $siswaModel;
    protected $siswaDemografiModel;
    protected $settingsModel;

    public function __construct()
    {
        // Inisialisasi model di konstruktor
        $this->siswaModel = new SiswaModel();
        $this->siswaDemografiModel = new SiswaDemografiModel();
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Menampilkan daftar siswa yang akan diurus data kelulusannya.
     */
    public function index()
    {
        // Query ini aman, karena kolomnya sudah dieksplisitkan.
        $data = [
            'title' => 'Manajemen Ijazah & Kelulusan',
            'siswa_lulus' => $this->siswaModel->select('siswa.id, siswa.nis, siswa.nama_lengkap, siswa.status, siswa_demografi.nomor_ijazah, siswa_demografi.tanggal_lulus')
                                             ->join('siswa_demografi', 'siswa.id = siswa_demografi.id_siswa', 'left')
                                             ->where('siswa.status', 'lulus')
                                             ->orderBy('siswa.nama_lengkap', 'ASC')
                                             ->findAll(),
        ];
        return view('ijazah/index', $data);
    }
    
    /**
     * Menyimpan Nomor Ijazah dan Tanggal Lulus.
     * Rute: POST /app/ijazah/save
     */
    public function save()
    {
        // 1. Ambil data dari request
        $id_siswa = $this->request->getPost('id_siswa');
        $nomor_ijazah = $this->request->getPost('nomor_ijazah');
        $tanggal_lulus = $this->request->getPost('tanggal_lulus');

        // 2. Validasi Input
        if (empty($id_siswa) || empty($nomor_ijazah) || empty($tanggal_lulus)) {
            return redirect()->back()->with('error', 'Semua field harus diisi (ID Siswa, Nomor Ijazah, Tanggal Lulus).');
        }

        // 3. Perbarui Kolom Status di tabel 'siswa'
        $this->siswaModel->update($id_siswa, [
            'status' => 'lulus', // Ubah status siswa menjadi lulus
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // 4. Perbarui Kolom Ijazah di tabel 'siswa_demografi'
        // Mencari objek demografi (FK 'id_siswa' dijamin ada)
        $demografi = $this->siswaDemografiModel->where('id_siswa', $id_siswa)->first();

        if ($demografi) {
            // PERBAIKAN: Menggunakan where()->update() untuk memaksa 
            // kolom WHERE menggunakan 'id_siswa', dan melewati 'null' 
            // sebagai kunci ($id) agar data array terbaca dengan benar.
            $this->siswaDemografiModel
                ->where('id_siswa', $demografi->id_siswa)
                ->update(null, [ // BARIS KRITIS: Tambahkan null di sini.
                    'nomor_ijazah' => $nomor_ijazah,
                    'tanggal_lulus' => $tanggal_lulus,
                ]);
        } else {
            // Jika data demografi belum ada, buat baru
            $this->siswaDemografiModel->insert([
                'id_siswa' => $id_siswa,
                'nomor_ijazah' => $nomor_ijazah,
                'tanggal_lulus' => $tanggal_lulus,
                // Kolom lain mungkin perlu default value
            ]);
        }


        return redirect()->to('app/ijazah')->with('success', 'Data ijazah dan status kelulusan berhasil disimpan.');
    }

    /**
     * Menampilkan halaman cetak ijazah untuk siswa tertentu.
     * Rute: GET /app/ijazah/view/123
     */
    public function view(int $id_siswa)
    {
        $selectFields = [
            // Kolom Siswa dasar (siswa.id, nis, nama, jenis_kelamin, NIK SISWA)
            'siswa.id', 
            'siswa.nis', 
            'siswa.nama_lengkap', 
            'siswa.jenis_kelamin',
            'siswa.nik AS nik_siswa', // NIK Siswa
            
            // Kolom dari siswa_demografi (tempat/tgl lahir, nama ibu, nomor ijazah)
            'siswa_demografi.tempat_lahir', 
            'siswa_demografi.tanggal_lahir', 
            'siswa_demografi.nama_ibu', 
            'siswa_demografi.nomor_ijazah', 
            'siswa_demografi.tanggal_lulus', 
            'siswa_demografi.agama',
            'siswa_demografi.alamat',
            'siswa_demografi.nama_ayah', 
        ];

        $siswaData = $this->siswaModel->select(implode(', ', $selectFields))
                                     ->join('siswa_demografi', 'siswa.id = siswa_demografi.id_siswa', 'left')
                                     ->find($id_siswa);

        if (!$siswaData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data Siswa tidak ditemukan.');
        }

        // 2. Ambil data Pengaturan Sekolah (Kop Surat & Kepala Sekolah)
        $sekolahSettings = $this->settingsModel->getSettingsAsArray();
        
        // 3. Siapkan data untuk View
        $data = [
            'title' => 'Cetak Ijazah',
            'siswa' => $siswaData,
            'sekolah' => $sekolahSettings,
            'leger_nilai' => [], // Placeholder untuk data nilai, view mengharapkannya
        ];

        return view('ijazah/view', $data);
    }
}