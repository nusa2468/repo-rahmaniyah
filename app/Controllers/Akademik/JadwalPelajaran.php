<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseAkademikController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use App\Models\KurikulumModel;
use App\Models\Sapras\RuanganModel;
use App\Models\KelasModel;
use App\Models\MataPelajaranModel;
use App\Models\GuruModel;             // FIX: KEMBALI KE GURUMODEL YANG SUDAH SEMPURNA
use App\Models\TahunAjaranModel;
use App\Models\JadwalPelajaranModel;

/**
 * Controller JadwalPelajaran
 * Mengelola matriks waktu belajar mengajar per unit.
 * STATUS: FIXED (Memanfaatkan GuruModel yang sudah meng-override tabel pegawai)
 */
class JadwalPelajaran extends BaseAkademikController
{
    protected ?KurikulumModel $kurikulumModel = null;
    protected ?RuanganModel $ruanganModel = null;
    protected ?KelasModel $kelasModel = null;
    protected ?MataPelajaranModel $mapelModel = null;
    protected ?GuruModel $guruModel = null;           // Property GuruModel
    protected ?TahunAjaranModel $tahunAjaranModel = null;
    protected ?JadwalPelajaranModel $jadwalPelajaranModel = null;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->kurikulumModel = new KurikulumModel();
        $this->kelasModel = new KelasModel();
        $this->mapelModel = new MataPelajaranModel();
        
        // Cukup panggil GuruModel (Di dalamnya sudah diarahkan ke tabel Pegawai)
        if (class_exists('App\Models\GuruModel')) {
            $this->guruModel = new GuruModel();
        } else {
            $this->guruModel = new class extends \CodeIgniter\Model {
                protected $table = 'pegawai';
                protected $primaryKey = 'id';
                protected $returnType = 'array';
            };
        }

        $this->tahunAjaranModel = new TahunAjaranModel();
        $this->jadwalPelajaranModel = new JadwalPelajaranModel();
        
        // Fail-safe model ruangan
        if (class_exists('App\Models\Sapras\RuanganModel')) {
            $this->ruanganModel = new RuanganModel();
        } else {
            $this->ruanganModel = new class extends \CodeIgniter\Model {
                public function findAll(?int $limit = null, int $offset = 0) { return []; }
            };
        }
    }
    
    /**
     * Menampilkan daftar jadwal pelajaran dengan Filter Unit Dinamis.
     */
    public function index(): string
    {
        // 1. Identifikasi Otoritas
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        // 2. Tangkap Filter & TA
        $unitParam     = $this->request->getGet('jenjang');
        $taAktif       = $this->tahunAjaranAktif ?? $this->tahunAjaranModel->where('status', 'aktif')->first();
        $tahunAjaranId = $taAktif['id'] ?? null;

        // 3. Penentuan Scope Jenjang
        if (!$isGlobal) {
            $kodeJenjang = strtoupper($sessionUnit);
        } else {
            $kodeJenjang = (!empty($unitParam) && strtoupper($unitParam) !== 'ALL' && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null;
        }

        // 4. Bangun Query
        $query = $this->jadwalPelajaranModel->getJadwalBuilder();

        if ($kodeJenjang) {
            $query->where('jadwal_pelajaran.kode_jenjang', $kodeJenjang);
        }

        if ($tahunAjaranId) {
            $query->where('jadwal_pelajaran.id_tahun_ajaran', $tahunAjaranId);
        }
        
        // Pencarian (Optional)
        $search = $this->request->getGet('search');
        if($search) {
             $query->groupStart()
                   ->like('mata_pelajaran.nama_mapel', $search)
                   ->orLike('guru.nama_lengkap', $search)
                   ->orLike('kelas.nama_kelas', $search)
                   ->groupEnd();
        }

        // 5. Paginasi
        $page    = (int) ($this->request->getGet('page_default') ?? 1);
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $countQuery = clone $query;
        $totalRows  = $countQuery->countAllResults();

        $jadwalData = $query->orderBy('FIELD(jadwal_pelajaran.hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu")', 'ASC', false)
                            ->orderBy('jadwal_pelajaran.jam_mulai', 'ASC')
                            ->limit($perPage, $offset)
                            ->get()
                            ->getResultArray();

        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $totalRows, 0);

        // 6. View Data
        $data = $this->loadViewData([
            'title'             => 'Penjadwalan Akademik',
            'current_module'    => 'akademik', 
            'jadwal'            => $jadwalData,
            'pager'             => $pager,
            'tahunAjaranInfo'   => ($taAktif['tahun_ajaran'] ?? 'TA N/A') . ' - ' . ($taAktif['semester'] ?? ''),
            'session_unit'      => $sessionUnit,
            'filter_selected'   => $unitParam ?? ($kodeJenjang ?? 'ALL')
        ]);

        return view('akademik/jadwalpelajaran/index', $data);
    }

    /**
     * Form Jadwal Baru.
     */
    public function new(): string
    {
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        
        $kelasQuery = $this->kelasModel->where('is_aktif', 1);
        $mapelQuery = $this->mapelModel->where('status', 'aktif');
        
        // KODE MENJADI LEBIH BERSIH: Kita tidak perlu lagi menulis where('jenis_pegawai', 'guru') 
        // karena GuruModel sudah mengurusnya di balik layar (Override findAll).
        $guruQuery  = $this->guruModel->where('status_aktif', 'aktif');

        // Filter Scope
        if (!$isGlobal) {
            $unit = strtoupper($sessionUnit);
            $kelasQuery->where('kode_jenjang', $unit);
            $mapelQuery->where('kode_jenjang', $unit);
            $guruQuery->where('kode_jenjang', $unit);
        }

        $taAktif = $this->tahunAjaranAktif ?? $this->tahunAjaranModel->where('status', 'aktif')->first();

        $data = $this->loadViewData([
            'title'          => 'Buat Jadwal Baru',
            'current_module' => 'akademik', 
            'kelas'          => $kelasQuery->orderBy('nama_kelas', 'ASC')->findAll(),
            'mapel'          => $mapelQuery->orderBy('nama_mapel', 'ASC')->findAll(),
            'guru'           => $guruQuery->orderBy('nama_lengkap', 'ASC')->findAll(),
            'tahun_ajaran'   => $this->tahunAjaranModel->findAll(), 
            'active_ta_id'   => $taAktif['id'] ?? null, 
            'jadwal'         => null,
            'kurikulum'      => $this->kurikulumModel->findAll(),
            'list_ruangan'   => $this->ruanganModel->findAll(),
            'session_unit'   => $sessionUnit
        ]);
        
        return view('akademik/jadwalpelajaran/form', $data);
    }

    /**
     * Simpan Jadwal.
     */
    public function create(): RedirectResponse
    {
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        
        // Validasi
        $rules = [
            'id_kelas'          => 'required|integer',  
            'id_mata_pelajaran' => 'required|integer',
            'id_guru'           => 'required|integer',
            'hari'              => 'required',
            'jam_mulai'         => 'required', 
            'jam_selesai'       => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idKelas = $this->request->getPost('id_kelas');
        $kelasData = $this->kelasModel->find($idKelas);
        
        if (!$kelasData) {
             return redirect()->back()->withInput()->with('error', 'Data kelas tidak valid.');
        }

        $unitTarget = $kelasData['kode_jenjang'];

        if (!$isGlobal && strtoupper($unitTarget) !== strtoupper($sessionUnit)) {
             return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki hak akses untuk unit ini.');
        }

        $taAktif = $this->tahunAjaranAktif ?? $this->tahunAjaranModel->where('status', 'aktif')->first();

        $payload = [
            'kode_jenjang'      => $unitTarget,
            'id_grup_siswa'     => $this->request->getPost('id_grup_siswa') ?: null,
            'id_kelas'          => $idKelas,
            'id_mata_pelajaran' => $this->request->getPost('id_mata_pelajaran'),
            'id_guru'           => $this->request->getPost('id_guru'),
            'id_tahun_ajaran'   => $this->request->getPost('id_tahun_ajaran') ?? ($taAktif['id'] ?? 1),
            'id_kurikulum'      => $this->request->getPost('id_kurikulum'),
            'id_ruangan'        => $this->request->getPost('id_ruangan') ?: null,
            'hari'              => $this->request->getPost('hari'),
            'jam_mulai'         => $this->request->getPost('jam_mulai'),
            'jam_selesai'       => $this->request->getPost('jam_selesai'),
        ];

        try {
            if (!$this->jadwalPelajaranModel->insert($payload)) {
                return redirect()->back()->withInput()->with('errors', $this->jadwalPelajaranModel->errors());
            }
            return redirect()->to('/app/akademik/jadwalpelajaran')->with('message', 'Jadwal berhasil diterbitkan.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Edit Jadwal.
     */
    public function edit($id = null): string|RedirectResponse
    {
        $jadwal = $this->jadwalPelajaranModel->find($id);
        if (!$jadwal) {
            throw PageNotFoundException::forPageNotFound('Jadwal tidak ditemukan.');
        }

        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        if (!$isGlobal && strtoupper($jadwal['kode_jenjang']) !== strtoupper($sessionUnit)) {
            return redirect()->to('/app/akademik/jadwalpelajaran')->with('error', 'Akses Ditolak.');
        }

        $kelasQuery = $this->kelasModel->where('is_aktif', 1);
        $mapelQuery = $this->mapelModel->where('status', 'aktif');
        // KODE MENJADI LEBIH BERSIH: Cukup panggil guruModel
        $guruQuery  = $this->guruModel->where('status_aktif', 'aktif');

        if (!$isGlobal) {
             $unit = strtoupper($sessionUnit);
             $kelasQuery->where('kode_jenjang', $unit);
             $mapelQuery->where('kode_jenjang', $unit);
             $guruQuery->where('kode_jenjang', $unit);
        }

        $taAktif = $this->tahunAjaranAktif ?? $this->tahunAjaranModel->where('status', 'aktif')->first();

        $data = $this->loadViewData([
            'title'          => 'Modifikasi Jadwal',
            'current_module' => 'akademik', 
            'jadwal'         => $jadwal,
            'kelas'          => $kelasQuery->orderBy('nama_kelas', 'ASC')->findAll(), 
            'mapel'          => $mapelQuery->orderBy('nama_mapel', 'ASC')->findAll(),
            'guru'           => $guruQuery->orderBy('nama_lengkap', 'ASC')->findAll(),
            'tahun_ajaran'   => $this->tahunAjaranModel->findAll(), 
            'kurikulum'      => $this->kurikulumModel->findAll(),
            'list_ruangan'   => $this->ruanganModel->findAll(),
            'session_unit'   => $sessionUnit
        ]);
        
        return view('akademik/jadwalpelajaran/form', $data); 
    }

    /**
     * Update Jadwal.
     */
    public function update($id = null): RedirectResponse
    {
        $existing = $this->jadwalPelajaranModel->find($id);
        if (!$existing) {
            return redirect()->to('/app/akademik/jadwalpelajaran')->with('error', 'Data tidak valid.');
        }
        
        $idKelas = $this->request->getPost('id_kelas');
        $kelasData = $this->kelasModel->find($idKelas);
        $unitTarget = $kelasData ? $kelasData['kode_jenjang'] : $existing['kode_jenjang'];

        $payload = [
            'kode_jenjang'      => $unitTarget,
            'id_grup_siswa'     => $this->request->getPost('id_grup_siswa') ?: null,
            'id_kelas'          => $idKelas,
            'id_mata_pelajaran' => $this->request->getPost('id_mata_pelajaran'),
            'id_guru'           => $this->request->getPost('id_guru'),
            'id_tahun_ajaran'   => $this->request->getPost('id_tahun_ajaran'),
            'id_kurikulum'      => $this->request->getPost('id_kurikulum'),
            'id_ruangan'        => $this->request->getPost('id_ruangan') ?: null,
            'hari'              => $this->request->getPost('hari'),
            'jam_mulai'         => $this->request->getPost('jam_mulai'),
            'jam_selesai'       => $this->request->getPost('jam_selesai'),
        ];

        try {
            if (!$this->jadwalPelajaranModel->update($id, $payload)) {
                return redirect()->back()->withInput()->with('errors', $this->jadwalPelajaranModel->errors());
            }
            return redirect()->to('/app/akademik/jadwalpelajaran')->with('message', 'Jadwal diperbarui.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete Jadwal
     */
    public function delete($id = null): RedirectResponse 
    { 
        if (!$id) {
            return redirect()->back()->with('error', 'ID Jadwal tidak valid.');
        }

        try {
            if ($this->jadwalPelajaranModel->delete($id)) {
                return redirect()->back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
            }
            return redirect()->back()->with('error', 'Gagal menghapus jadwal pelajaran.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}