<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseMasterDataController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

/**
 * Controller Kelas (Enterprise Edition - Final Fixed)
 * Mengelola data Rombongan Belajar dengan dukungan Multi-Unit dan Pagination.
 * Sinkronisasi: Menggunakan kolom 'id_wali_kelas' sesuai KelasModel.
 */
class Kelas extends BaseMasterDataController
{
    private string $redirectBaseUrl = 'app/masterdata/kelas';
    private array $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT'];

    /**
     * Menampilkan daftar kelas dengan dukungan scoping unit.
     */
    public function index(): string
    {
        // 1. Identifikasi Otoritas
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        // 2. Tangkap Request Filter
        $filterUnit = $this->request->getGet('unit');
        $search     = $this->request->getGet('search');
        $perPage    = $this->request->getGet('per_page') ?? 10;

        // 3. Force Scope jika bukan Superadmin
        if (!$isSuperAdmin) {
            $filterUnit = $userJenjang;
        }

        $unitParam = (empty($filterUnit) || in_array(strtoupper($filterUnit), $this->globalIdentifiers)) ? null : $filterUnit;

        // 4. Bangun Query via Model
        $model = $this->kelasModel;
        $query = $model->getKelasBuilder($unitParam, $search);
        
        $data = [
            'title'          => 'Manajemen Rombongan Belajar',
            'kelas'          => $query->paginate((int)$perPage, 'kelas'),
            'pager'          => $model->pager,
            'jenjang_list'   => $this->jenjangModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll(),
            'stats'          => $model->getStats($unitParam),
            'current_filter' => [
                'unit'     => $filterUnit ?? ($isSuperAdmin ? 'GLOBAL' : $userJenjang),
                'search'   => $search,
                'per_page' => $perPage
            ],
            'is_restricted'  => !$isSuperAdmin
        ];
        
        return view('masterdata/kelas/index', $this->loadViewData($data));
    }

    /**
     * Menampilkan Detail Kelas (Method Baru)
     */
    public function show($id = null)
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        // Query Builder Manual untuk Detail Lengkap
        // Pastikan relasi tabel sesuai dengan database Anda
        $model = $this->kelasModel;
        $model->select('
            kelas.*, 
            guru.nama_lengkap as nama_wali_kelas, 
            tahun_ajaran.tahun_ajaran, 
            tahun_ajaran.semester,
            kurikulum.nama_kurikulum,
            jurusan.nama_jurusan
        ');
        // Join Tables
        $model->join('pegawai as guru', 'guru.id = kelas.id_wali_kelas', 'left'); // Asumsi tabel guru adalah 'pegawai'
        $model->join('tahun_ajaran', 'tahun_ajaran.id = kelas.id_tahun_ajaran', 'left');
        $model->join('kurikulum', 'kurikulum.id = kelas.id_kurikulum', 'left');
        $model->join('jurusan', 'jurusan.id = kelas.id_jurusan', 'left');
        
        $kelas = $model->find($id);

        if (!$kelas) throw PageNotFoundException::forPageNotFound();

        // Cek Otoritas Unit
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        if (!$isSuperAdmin && strtoupper($kelas['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Akses Ditolak: Data ini milik unit lain.');
        }

        $data = [
            'title' => 'Detail Rombongan Belajar',
            'kelas' => $kelas
        ];

        return view('masterdata/kelas/detail', $data);
    }

    /**
     * Menampilkan form untuk menambah kelas baru.
     */
    public function new(): string
    {
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        // Persiapkan Query Dropdown yang terfilter scope
        $kurikulumQuery = $this->kurikulumModel->where('status', 'aktif');
        $taQuery        = $this->tahunAjaranModel;
        $jurusanQuery   = $this->jurusanModel;
        $guruQuery      = $this->guruModel->where('status_aktif', 'aktif')->orderBy('nama_lengkap', 'ASC');

        // Jika Admin Unit, batasi pilihan hanya untuk unitnya
        if (!$isSuperAdmin) {
            $kurikulumQuery->where('kode_jenjang', $userJenjang);
            $taQuery->where('kode_jenjang', $userJenjang);
            $jurusanQuery->where('kode_jenjang', $userJenjang);
            $guruQuery->where('kode_jenjang', $userJenjang);
        }

        $activeTa = $taQuery->where('status', 'aktif')->first();

        $data = [
            'title'             => 'Registrasi Rombel Baru',
            'kelas'             => [
                'is_aktif'        => 1,
                'id_tahun_ajaran' => $activeTa['id'] ?? null,
                'kode_jenjang'    => $isSuperAdmin ? '' : $userJenjang
            ],
            'guru_list'         => $guruQuery->findAll(), 
            'tahun_ajaran_list' => $taQuery->orderBy('id', 'DESC')->findAll(),
            'kurikulum_list'    => $kurikulumQuery->findAll(),
            'jenjang_list'      => $this->jenjangModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll(),
            'jurusan_list'      => $jurusanQuery->findAll(),
            'is_restricted'     => !$isSuperAdmin,
            'validation'        => \Config\Services::validation(),
        ];
        
        return view('masterdata/kelas/form', $this->loadViewData($data));
    }

    /**
     * Menyimpan data kelas baru ke database.
     */
    public function create(): RedirectResponse
    {
        $dataPost = $this->request->getPost();
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');

        // Proteksi Unit pada payload
        if (!in_array($userJenjang, $this->globalIdentifiers)) {
            $dataPost['kode_jenjang'] = $userJenjang;
        }

        // --- SINKRONISASI & SANITASI FOREIGN KEY ---
        if (empty($dataPost['id_jurusan'])) $dataPost['id_jurusan'] = null;
        if (empty($dataPost['id_wali_kelas'])) $dataPost['id_wali_kelas'] = null;
        
        // Pastikan kapasitas ada defaultnya
        if (empty($dataPost['kapasitas'])) $dataPost['kapasitas'] = 36;

        if (!$this->kelasModel->insert($dataPost)) {
            return redirect()->back()->withInput()->with('errors', $this->kelasModel->errors());
        }

        return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Rombongan Belajar berhasil didaftarkan.');
    }

    /**
     * Menampilkan form edit kelas.
     */
    public function edit($id = null): string
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();
        $kelas = $this->kelasModel->find($id);
        if (!$kelas) throw PageNotFoundException::forPageNotFound('Data Rombel tidak ditemukan.');

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);
        
        // Proteksi: Cek kepemilikan unit
        if (!$isSuperAdmin && strtoupper($kelas['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))
                             ->with('error', 'Akses Ditolak: Anda dilarang mengedit data unit lain.');
        }

        // Filter Guru Pengampu berdasarkan unit kelas
        $guruQuery = $this->guruModel->where('status_aktif', 'aktif')->orderBy('nama_lengkap', 'ASC');
        if (!$isSuperAdmin) {
            $guruQuery->where('kode_jenjang', $kelas['kode_jenjang']);
        }

        $data = [
            'title'             => 'Sunting Rombel: ' . $kelas['nama_kelas'],
            'kelas'             => $kelas,
            'guru_list'         => $guruQuery->findAll(),
            'tahun_ajaran_list' => $this->tahunAjaranModel->orderBy('id', 'DESC')->findAll(),
            'kurikulum_list'    => $this->kurikulumModel->findAll(),
            'jenjang_list'      => $this->jenjangModel->where('status', 'aktif')->findAll(),
            'jurusan_list'      => $this->jurusanModel->where('kode_jenjang', $kelas['kode_jenjang'])->findAll(),
            'is_restricted'     => !$isSuperAdmin,
            'validation'        => \Config\Services::validation(),
        ];
        
        return view('masterdata/kelas/form', $this->loadViewData($data));
    }

    /**
     * Memperbarui data kelas yang sudah ada.
     */
    public function update($id = null): RedirectResponse
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $dataPost = $this->request->getPost();
        $existing = $this->kelasModel->find($id);
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');

        // Validasi Otoritas Update
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($existing['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Otoritas Ditolak.');
        }

        // --- SINKRONISASI & SANITASI ---
        if (empty($dataPost['id_jurusan'])) $dataPost['id_jurusan'] = null;
        if (empty($dataPost['id_wali_kelas'])) $dataPost['id_wali_kelas'] = null;

        if (!$this->kelasModel->update($id, $dataPost)) {
            return redirect()->back()->withInput()->with('errors', $this->kelasModel->errors());
        }

        return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data Rombel berhasil diperbarui.');
    }

    /**
     * Menghapus data kelas (Mendukung Soft Delete).
     */
    public function delete($id = null): RedirectResponse
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $kelas = $this->kelasModel->find($id);
        if (!$kelas) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($kelas['kode_jenjang']) !== $userJenjang) {
            return redirect()->back()->with('error', 'Otoritas Ditolak.');
        }

        try {
            // Cek ketergantungan data (misal: apakah ada siswa terdaftar)
            $db = \Config\Database::connect();
            $isUsed = $db->table('siswa_enrollment')->where('id_kelas', $id)->countAllResults();
            
            if ($isUsed > 0) {
                return redirect()->back()->with('error', "Gagal Hapus: Kelas '{$kelas['nama_kelas']}' masih memiliki siswa yang terdaftar. Pindahkan siswa terlebih dahulu.");
            }

            if ($this->kelasModel->delete($id)) {
                return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Rombongan belajar berhasil dihapus.');
            }
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Kesalahan Database: ' . $e->getMessage());
        }

        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }
}