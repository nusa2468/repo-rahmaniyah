<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\MataPelajaranModel;
use App\Models\NilaiModel;
use App\Models\SiswaAkademikModel;
use App\Models\TahunAjaranModel;
use Exception;

class Nilai extends BaseController
{
    protected KelasModel $kelasModel;
    protected SiswaAkademikModel $siswaAkademikModel;
    protected MataPelajaranModel $mapelModel;
    protected TahunAjaranModel $tahunAjaranModel;
    protected NilaiModel $nilaiModel;

    public function __construct()
    {
        $this->kelasModel = new KelasModel();
        $this->siswaAkademikModel = new SiswaAkademikModel();
        $this->mapelModel = new MataPelajaranModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
        $this->nilaiModel = new NilaiModel();
        helper('form');
        log_message('debug', 'Controller Nilai diinisialisasi.');
    }

    /**
     * Menampilkan halaman utama untuk memilih Kelas dan Mata Pelajaran.
     */
    public function index()
    {
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        
        // Hanya ambil kelas yang terkait dengan tahun ajaran aktif
        $kelas = $tahunAjaranAktif ? $this->kelasModel->where('id_tahun_ajaran', $tahunAjaranAktif['id'])->findAll() : [];

        $data = [
            'title' => 'Manajemen Nilai Siswa',
            'current_module' => 'akademik', 
            'kelas' => $kelas,
            'mapel' => $this->mapelModel->findAll(),
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
        ];

        return view('nilai/index', $data);
    }

    /**
     * Memuat halaman input nilai berdasarkan kelas dan mata pelajaran yang dipilih.
     */
    public function kelola()
    {
        $id_kelas = $this->request->getVar('id_kelas');
        // Mendukung nama field 'id_mapel' atau 'id_mata_pelajaran' dari form
        $id_mapel = $this->request->getVar('id_mapel') ?? $this->request->getVar('id_mata_pelajaran'); 
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();

        log_message('debug', 'Memulai fungsi kelola(). Parameter: Kelas=' . $id_kelas . ', Mapel=' . $id_mapel);

        if (!$id_kelas || !$id_mapel || !$tahunAjaranAktif) {
            log_message('error', 'Parameter ID Kelas, ID Mapel, atau Tahun Ajaran Aktif tidak ditemukan.');
            return redirect()->to('app/nilai')->with('error', 'Silakan pilih kelas dan mata pelajaran terlebih dahulu.');
        }

        try {
            // --- PENGAMBILAN INFORMASI PENTING ---
            $kelas_info = $this->kelasModel->find($id_kelas);
            $mapel_info = $this->mapelModel->find($id_mapel);
            
            if (!$kelas_info || !$mapel_info) {
                 log_message('error', "Data Kelas ({$id_kelas}) atau Mata Pelajaran ({$id_mapel}) tidak valid.");
                 return redirect()->to('app/nilai')->with('error', 'Data Kelas atau Mata Pelajaran tidak valid.');
            }
            
            // --- BOBOT NILAI DINAMIS (Default 30:30:30:10) ---
            $bobot = [
                'tugas'    => (float)($mapel_info['bobot_tugas'] ?? 0.30), 
                'uts'      => (float)($mapel_info['bobot_uts'] ?? 0.30),
                'uas'      => (float)($mapel_info['bobot_uas'] ?? 0.30),
                'absensi'  => (float)($mapel_info['bobot_absensi'] ?? 0.10), // Bobot absensi
            ];
            
            // Periksa total bobot
            $total_bobot = array_sum($bobot);
            if ($total_bobot != 1.0) {
                 log_message('warning', "Total bobot tidak sama dengan 1.0 ({$total_bobot}) untuk Mapel ID: {$id_mapel}. Menggunakan default.");
                 // Fallback atau peringatan bisa ditambahkan di sini jika diperlukan
            }

            // Panggilan ke Model: Siswa di kelas yang bersangkutan
            $siswa_di_kelas = $this->siswaAkademikModel->getSiswaDiKelas($id_kelas, $tahunAjaranAktif['id']);
            
            // Panggilan ke Model: Nilai yang sudah tersimpan untuk mapel ini
            $nilai_tersimpan = $this->nilaiModel->getNilaiByKelasAndMapel(
                (int)$id_kelas, 
                (int)$id_mapel, 
                (int)$tahunAjaranAktif['id']
            );

            // --- TAMBAHAN KRITIS UNTUK HIDDEN FIELDS di View ---
            // Sesuaikan pengambilan semester dan ID Guru yang login sesuai implementasi autentikasi Anda.
            $semester_saat_ini = 'Ganjil'; // TODO: GANTI dengan nilai semester yang berlaku (e.g., dari setting TA)
            $id_guru_yang_login = session()->get('id_guru') ?? 1; // TODO: GANTI dengan ID Guru yang sedang login
            
            // Siapkan data untuk View
            $data = [
                'title' => 'Input Nilai Siswa: ' . $mapel_info['nama_mapel'] . ' - ' . $kelas_info['nama_kelas'],
                'current_module' => 'akademik',
                'siswa_di_kelas' => $siswa_di_kelas,
                'kelas_info' => $kelas_info,
                'mapel_info' => $mapel_info,
                'tahun_ajaran_aktif' => $tahunAjaranAktif,
                'bobot' => $bobot, 
                'nilai_tersimpan' => $nilai_tersimpan,
                // Variabel yang dibutuhkan oleh View dan method simpan()
                'semester_saat_ini' => $semester_saat_ini, 
                'id_guru_yang_login' => $id_guru_yang_login, 
            ];
            
            return view('nilai/kelola', $data);

        } catch (Exception $e) {
            log_message('error', 'Error saat memuat data nilai di Nilai/kelola: ' . $e->getMessage() . ' di file ' . $e->getFile() . ' baris ' . $e->getLine());
            return redirect()->to('app/nilai')->with('error', 'Gagal memuat data nilai. Detail: ' . $e->getMessage());
        }
    }

    /**
     * Menyimpan atau memperbarui nilai dari form input.
     */
    public function simpan()
    {
        if (!$this->request->is('post')) {
            return redirect()->back()->with('error', 'Metode tidak diizinkan. Harap kirimkan formulir.');
        }

        $data_post = $this->request->getPost();
        
        // Data wajib dari Hidden Field
        $id_kelas = $data_post['id_kelas'] ?? null;
        $id_mapel = $data_post['id_mapel'] ?? null;
        $semester = $data_post['semester'] ?? null; 
        $id_guru = $data_post['id_guru'] ?? null; 
        
        // Mengambil ID Tahun Ajaran Aktif
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        $id_tahun_ajaran = $tahunAjaranAktif['id'] ?? null;

        // Validasi Kunci Utama
        if (!$id_kelas || !$id_mapel || !$semester || !$id_tahun_ajaran || !$id_guru) {
            log_message('error', 'Data kunci untuk simpan nilai tidak lengkap: ' . json_encode($data_post));
            $missing = [];
            if (!$id_kelas) $missing[] = 'ID Kelas';
            if (!$id_mapel) $missing[] = 'ID Mapel';
            if (!$semester) $missing[] = 'Semester';
            if (!$id_tahun_ajaran) $missing[] = 'ID Tahun Ajaran';
            if (!$id_guru) $missing[] = 'ID Guru';

            return redirect()->back()->with('error', 'Gagal: Data kunci tidak lengkap. Variabel hilang: ' . implode(', ', $missing) . '. Pastikan semua field hidden di View terisi.');
        }
        
        $data_nilai_siswa = $data_post['nilai_siswa'] ?? [];

        if (empty($data_nilai_siswa)) {
            return redirect()->back()->with('error', 'Tidak ada data nilai yang perlu disimpan.');
        }
        
        $berhasil = 0;
        
        // Mulai transaksi database
        $this->nilaiModel->transStart();

        try {
            // Mapping dari nama field di View ke kolom di Database
            $field_mapping = [
                'tugas' => 'nilai_tugas', 
                'uts' => 'nilai_uts',
                'uas' => 'nilai_uas',
                'absensi' => 'nilai_absensi', // Menambahkan absensi
                'nilai_akhir' => 'nilai_akhir',
                // Asumsi View juga mengirim 'keterangan' jika ada, namun di sini kita fokus pada nilai
            ];

            foreach ($data_nilai_siswa as $id_siswa => $data_nilai) {
                
                $data_simpan = [
                    'id_siswa'          => (int)$id_siswa,
                    'id_mata_pelajaran' => (int)$id_mapel,
                    'id_guru'           => (int)$id_guru,
                    'id_tahun_ajaran'   => (int)$id_tahun_ajaran,
                    'id_kelas'          => (int)$id_kelas, 
                    'semester'          => $semester, 
                ];
                
                foreach ($field_mapping as $view_key => $db_column) {
                    $nilai = $data_nilai[$view_key] ?? null;

                    // Perbaikan: Pastikan nilai numerik adalah float, atau set ke 0.0 jika kosong
                    if (in_array($db_column, ['nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_absensi', 'nilai_akhir'])) {
                        // Cek apakah nilai valid dan numerik
                        if ($nilai !== null && is_numeric($nilai) && trim((string)$nilai) !== '') {
                            $data_simpan[$db_column] = (float)$nilai;
                        } else {
                            // Set ke 0.0 jika kosong atau tidak valid
                            $data_simpan[$db_column] = 0.0; 
                        }
                    } else if ($db_column == 'keterangan') {
                           // Keterangan
                           $data_simpan[$db_column] = $nilai; 
                    }
                }
                
                // Panggil method di Model untuk Insert atau Update (UPSERT)
                $this->nilaiModel->saveNilaiLengkap($data_simpan);
                $berhasil++;
            }
            
            $this->nilaiModel->transComplete();

            if ($this->nilaiModel->transStatus() === false) {
                // Transaksi gagal karena alasan database
                $dbError = $this->nilaiModel->error();
                log_message('error', 'Transaksi simpan nilai gagal: ' . $dbError['message']);
                return redirect()->back()->with('error', 'Gagal menyimpan data nilai secara keseluruhan. Silakan cek log database. Detail DB: ' . $dbError['message']);
            }
            
            // Response sukses
            return redirect()->to('app/nilai')->with('success', "Penyimpanan nilai berhasil! ({$berhasil} data berhasil diproses).");

        } catch (Exception $e) {
            $this->nilaiModel->transRollback();
            log_message('error', 'Exception saat simpan nilai: ' . $e->getMessage());
            // Response gagal
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data. Detail: ' . $e->getMessage());
        }
    }
}