<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseAkademikController;
use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\RedirectResponse;
use App\Models\JadwalPelajaranModel;
use App\Models\AbsensiSiswaModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;

/**
 * Controller AbsensiSiswa
 * Mengelola presensi harian siswa per kelas dan mata pelajaran.
 * REFAKTOR: Menggunakan aturan Role Scoping dari HakAksesModel (Anti-Hardcode).
 */
class AbsensiSiswa extends BaseAkademikController
{
    protected $jadwalModel;
    protected $absensiModel;
    
    // PERBAIKAN: Properti ini dihapus karena sudah didefinisikan di BaseAkademikController
    // protected $kelasModel; 
    // protected $siswaModel; 

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->jadwalModel  = new JadwalPelajaranModel();
        $this->absensiModel = new AbsensiSiswaModel();
        
        // Inisialisasi Model jika belum di-load oleh BaseAkademikController
        if (is_null($this->kelasModel)) {
            $this->kelasModel = new KelasModel();
        }
        
        if (is_null($this->siswaModel)) {
            $this->siswaModel = new SiswaModel();
        }
    }

    /**
     * Halaman utama: Riwayat Absensi + Filter Unit Dinamis
     */
    public function index(): string
    {
        // 1. Identifikasi Otoritas Berdasarkan Standar HakAksesModel
        $sessionUnit = session()->get('kode_jenjang');
        
        // Aturan: User dianggap Global jika session kosong atau bernilai 'GLOBAL'
        $isGlobal = (empty($sessionUnit) || in_array(strtoupper($sessionUnit), ['GLOBAL', 'YAYASAN', 'PUSAT']));
        
        // 2. Tangkap Filter dari Request GET
        $unitParam     = $this->request->getGet('unit');
        $filterKelas   = $this->request->getGet('id_kelas');
        $filterTanggal = $this->request->getGet('filter_tanggal'); 
        $search        = trim($this->request->getGet('search') ?? '');

        // 3. Penentuan Scope Jenjang (Logika Dinamis)
        if (!$isGlobal) {
            // Jika Admin Unit, kunci ke unit miliknya sendiri (Force Scope)
            $kodeJenjang = strtoupper($sessionUnit);
        } else {
            // Jika Superadmin/Global, ambil dari parameter atau default null (Semua Unit)
            $kodeJenjang = (!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null;
        }

        // 4. Ambil Data Kelas Berdasarkan Scope yang Berlaku
        // Pastikan hanya mengambil kelas di tahun ajaran aktif
        $kelasBuilder = $this->kelasModel->where('is_aktif', 1);
        
        if (isset($this->tahunAjaranAktif['id'])) {
             $kelasBuilder->where('id_tahun_ajaran', $this->tahunAjaranAktif['id']);
        }
        
        if ($kodeJenjang) {
            $kelasBuilder->where('kode_jenjang', $kodeJenjang);
        }
        $listKelas = $kelasBuilder->orderBy('nama_kelas', 'ASC')->findAll();

        // 5. Query Riwayat Absensi
        // Jika $kodeJenjang adalah null, maka model akan menampilkan semua data (Akses Global)
        $builder = $this->absensiModel->getAbsensiLengkap(
            $kodeJenjang, 
            $filterTanggal,
            $filterKelas ? (int)$filterKelas : null
        );

        if ($search !== '') {
            $builder->groupStart()
                    ->like('siswa.nama_lengkap', $search)
                    ->orLike('siswa.nis', $search)
                    ->groupEnd();
        }

        $perPage = 25;
        $absensi = $builder->paginate($perPage, 'default');

        // 6. Siapkan Data untuk View
        $data = $this->loadViewData([
            'title'             => 'Manajemen Presensi Siswa',
            'current_module'    => 'akademik',
            'kelas'             => $listKelas,
            'absensi'           => $absensi,
            'pager'             => $this->absensiModel->pager,
            'kodeJenjang'       => $kodeJenjang ?? 'GLOBAL', // Untuk status visual di View
            'session_unit'      => $sessionUnit,             // Digunakan View untuk lock UI
            'current_filter'    => [
                'unit'           => $unitParam ?? ($isGlobal ? 'GLOBAL' : $sessionUnit),
                'search'         => $search,
                'filter_tanggal' => $filterTanggal,
                'per_page'       => $perPage
            ],
            // Data tambahan untuk view index
            'filterTanggal'     => $filterTanggal,
            'filterKelas'       => $filterKelas,
        ]);

        return view('akademik/absensi_siswa/index', $data);
    }

    /**
     * Form input absensi harian per kelas & tanggal
     */
    public function kelola()
    {
        $id_kelas = $this->request->getGet('id_kelas');
        $tanggal  = $this->request->getGet('tanggal');

        if (!$id_kelas || !$tanggal) {
            return redirect()->to('/app/akademik/absensi-siswa')->with('error', 'Silakan tentukan kelas dan tanggal.');
        }

        // --- PROTEKSI AKSES BERDASARKAN ROLE (SINKRON) ---
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || in_array(strtoupper($sessionUnit), ['GLOBAL', 'YAYASAN', 'PUSAT']));
        
        $detail_kelas = $this->kelasModel->find($id_kelas);
        
        if (!$detail_kelas) {
             return redirect()->to('/app/akademik/absensi-siswa')->with('error', 'Kelas tidak ditemukan.');
        }

        if (!$isGlobal) {
            // Admin Unit dilarang mengelola kelas di luar unitnya
            if (strtoupper($detail_kelas['kode_jenjang']) !== strtoupper($sessionUnit)) {
                return redirect()->to('/app/akademik/absensi-siswa')
                                 ->with('error', 'Akses Ditolak: Anda tidak memiliki otoritas pada unit ini.');
            }
        }

        $nama_hari = $this->getNamaHari($tanggal);
        $idTa      = $this->tahunAjaranAktif['id'] ?? null;

        if (!$idTa) {
             return redirect()->to('/app/akademik/absensi-siswa')->with('error', 'Tahun ajaran aktif belum disetting.');
        }

        // Ambil Jadwal di hari tersebut
        // FIX: Menggunakan logika getJadwalHarianKelas untuk menangani kelas penuh vs rombel
        $jadwal_hari_ini = $this->jadwalModel->getJadwalHarianKelas($id_kelas, $nama_hari, $idTa);

        if (empty($jadwal_hari_ini)) {
            return redirect()->to('/app/akademik/absensi-siswa')
                             ->with('error', "Jadwal pelajaran tidak ditemukan untuk kelas " . esc($detail_kelas['nama_kelas']) . " pada hari " . esc($nama_hari));
        }

        // Ambil Daftar Siswa Aktif via Enrollment
        $siswa_di_kelas = $this->siswaModel
            ->select('siswa.id, siswa.nis, siswa.nama_lengkap AS nama_siswa, siswa.jenis_kelamin')
            ->join('siswa_enrollment se', 'se.id_siswa = siswa.id')
            ->where('se.id_kelas', $id_kelas)
            ->where('se.id_tahun_ajaran', $idTa)
            ->where('se.status_akademik', 'Aktif')
            ->orderBy('siswa.nama_lengkap', 'ASC')
            ->findAll();

        $data = $this->loadViewData([
            'title'              => 'Presensi: ' . ($detail_kelas['nama_kelas'] ?? ''),
            'current_module'     => 'akademik',
            'nama_kelas_display' => $detail_kelas['nama_kelas'],
            'siswa_di_kelas'     => $siswa_di_kelas,
            'jadwal_hari_ini'    => $jadwal_hari_ini,
            'id_kelas'           => $id_kelas,
            'tanggal'            => $tanggal,
            'kode_jenjang'       => $detail_kelas['kode_jenjang'],
            'absensi_tersimpan'  => $this->getAbsensiTersimpan($jadwal_hari_ini, $tanggal),
        ]);

        return view('akademik/absensi_siswa/kelola', $data);
    }

    /**
     * Simpan data absensi secara batch dengan validasi unit
     */
    public function simpan(): RedirectResponse
    {
        $absensi_data = $this->request->getPost('absensi');
        $tanggal      = $this->request->getPost('tanggal');
        $id_kelas     = $this->request->getPost('id_kelas');

        if (empty($absensi_data) || !$id_kelas) {
            return redirect()->back()->with('error', 'Payload data tidak lengkap.');
        }

        $kelas = $this->kelasModel->find($id_kelas);
        if (!$kelas) return redirect()->back()->with('error', 'Kelas invalid.');
        
        // Sinkronisasi data simpan
        $data_to_save = [];
        $jadwal_ids   = [];

        // Structure form input: absensi[id_siswa][id_jadwal] = status
        foreach ($absensi_data as $id_siswa => $per_jadwal) {
            foreach ($per_jadwal as $id_jadwal => $status) {
                if ($status) {
                    $keterangan = $this->request->getPost("keterangan.$id_siswa.$id_jadwal") ?? '';
                    
                    $data_to_save[] = [
                        'kode_jenjang' => $kelas['kode_jenjang'],
                        'id_jadwal'    => (int)$id_jadwal,
                        'id_siswa'     => (int)$id_siswa,
                        'tanggal'      => $tanggal,
                        'status'       => $status,
                        'keterangan'   => $keterangan,
                        'updated_at'   => date('Y-m-d H:i:s')
                    ];
                    $jadwal_ids[] = (int)$id_jadwal;
                }
            }
        }

        if (empty($data_to_save)) {
            return redirect()->back()->with('error', 'Harap pilih setidaknya satu status kehadiran.');
        }

        // Transaksi DB
        $this->absensiModel->db->transStart();

        // Hapus record lama untuk menjaga integritas data (Refresh Mode)
        // Hapus berdasarkan tanggal dan jadwal yang sedang diproses
        $this->absensiModel->where('tanggal', $tanggal)
                           ->whereIn('id_jadwal', array_unique($jadwal_ids))
                           ->delete();

        // Insert Batch
        $this->absensiModel->insertBatch($data_to_save);

        $this->absensiModel->db->transComplete();

        if ($this->absensiModel->db->transStatus() === false) {
             return redirect()->back()->with('error', 'Gagal memproses penyimpanan data ke database.');
        }

        return redirect()->to('/app/akademik/absensi-siswa')
                         ->with('message', 'Data presensi berhasil diperbarui.');
    }

    /**
     * Konversi tanggal ke nama hari Indonesia
     */
    private function getNamaHari(string $tanggal): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[(int)Time::parse($tanggal)->format('w')];
    }

    /**
     * Mapping data absensi yang sudah ada untuk ditampilkan di form
     */
    private function getAbsensiTersimpan(array $jadwal, string $tanggal): array
    {
        $ids = array_column($jadwal, 'id');
        if (empty($ids)) return [];

        $records = $this->absensiModel->where('tanggal', $tanggal)
                                      ->whereIn('id_jadwal', $ids)
                                      ->findAll();
        $map = [];
        foreach ($records as $r) {
            // Map: [siswa_id][jadwal_id] = [status, keterangan]
            $map[$r['id_siswa']][$r['id_jadwal']] = [
                'status' => $r['status'],
                'keterangan' => $r['keterangan']
            ];
        }
        return $map;
    }
}