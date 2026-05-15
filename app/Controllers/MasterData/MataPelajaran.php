<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\JenjangModel;
use App\Models\KurikulumModel;
use App\Models\MataPelajaranModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

/**
 * Controller MataPelajaran (Enterprise Edition)
 * Mengelola data mata pelajaran dengan proteksi scope unit dan validasi bobot nilai.
 * Fitur Smart: Dropdown Tingkat Dinamis via JenjangModel.
 */
class MataPelajaran extends BaseController
{
    protected $mapelModel;
    protected $jenjangModel;
    protected $kurikulumModel;
    
    private string $redirectBaseUrl = 'app/masterdata/matapelajaran'; 
    private array $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT'];

    public function __construct()
    {
        $this->mapelModel = new MataPelajaranModel();
        
        if (class_exists(JenjangModel::class)) {
            $this->jenjangModel = new JenjangModel();
        }
        if (class_exists(KurikulumModel::class)) {
            $this->kurikulumModel = new KurikulumModel();
        }
    }

    /**
     * Helper: Dapatkan Rentang Tingkat Berdasarkan Jenjang (DINAMIS DB)
     * Mengambil data dari tabel jenjang (level_awal s.d level_akhir).
     */
    private function getLevelsByJenjang(?string $jenjang): array
    {
        $jenjangKode = strtoupper($jenjang ?? '');

        // 1. Jika Global/Kosong, kembalikan semua range
        if (empty($jenjangKode) || in_array($jenjangKode, $this->globalIdentifiers)) {
            return range(0, 12); 
        }

        // 2. Ambil Konfigurasi Dinamis dari Database
        if ($this->jenjangModel) {
            $data = $this->jenjangModel->where('kode_jenjang', $jenjangKode)->first();
            
            // Deteksi kolom level di database (support variasi nama kolom)
            $start = $data['level_awal'] ?? $data['level_start'] ?? $data['min_level'] ?? null;
            $end   = $data['level_akhir'] ?? $data['level_end']   ?? $data['max_level'] ?? null;

            // Jika konfigurasi ditemukan di DB, gunakan itu
            if ($start !== null && $end !== null) {
                return range((int)$start, (int)$end);
            }
        }

        // 3. Fallback (Hanya jika DB belum dikonfigurasi) - Standar Nasional
        return match ($jenjangKode) {
            'SD', 'MI', 'SDLB'            => range(1, 6),
            'SMP', 'MTS', 'SMPLB'         => range(7, 9),
            'SMA', 'MA', 'SMK', 'SMALB'   => range(10, 12),
            'TK', 'PAUD', 'RA', 'KB'      => [0],
            default                       => range(1, 12)
        };
    }

    /**
     * Menampilkan daftar Mata Pelajaran dengan dukungan Scoping Unit & Search.
     */
    public function index(): string
    {
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        $filterUnit    = $this->request->getGet('unit');
        $filterTingkat = $this->request->getGet('tingkat'); 
        $filterSemester = $this->request->getGet('semester'); // [UPDATE] Ambil filter semester
        $search        = $this->request->getGet('search');
        $perPage       = $this->request->getGet('per_page') ?? 10;

        if (!$isSuperAdmin) {
            $filterUnit = $userJenjang;
        }

        // Tentukan Unit Aktif untuk konteks filter
        $activeUnit = (empty($filterUnit) || in_array(strtoupper($filterUnit), $this->globalIdentifiers)) ? null : $filterUnit;

        // --- SMART FILTER TINGKAT (DINAMIS) ---
        // Generate opsi tingkat berdasarkan unit yang sedang dipilih
        $availableLevels = $this->getLevelsByJenjang($activeUnit ?: ($isSuperAdmin ? null : $userJenjang));

        $model = $this->mapelModel;
        $model->select('mata_pelajaran.*, kurikulum.nama_kurikulum, js.nama_jenjang as unit_sekolah');
        $model->join('kurikulum', 'kurikulum.id = mata_pelajaran.kurikulum_id', 'left');
        $model->join('jenjang_sekolah js', 'js.kode_jenjang = mata_pelajaran.kode_jenjang', 'left');

        if ($activeUnit) {
            $model->where('mata_pelajaran.kode_jenjang', $activeUnit);
        }

        // Filter Tingkat (Hanya jika tingkat valid untuk unit tersebut)
        if (!empty($filterTingkat) && in_array($filterTingkat, $availableLevels)) {
            $model->where('mata_pelajaran.tingkat', $filterTingkat);
        }

        // [UPDATE] Filter Semester
        if (!empty($filterSemester)) {
            $model->where('mata_pelajaran.semester', $filterSemester);
        }

        if (!empty($search)) {
            $model->groupStart()
                  ->like('mata_pelajaran.nama_mapel', $search)
                  ->orLike('mata_pelajaran.kode_mapel', $search)
                  ->groupEnd();
        }

        // Sorting
        $model->orderBy('mata_pelajaran.kode_jenjang', 'ASC')
              ->orderBy('mata_pelajaran.tingkat', 'ASC')
              ->orderBy('mata_pelajaran.semester', 'ASC') // [UPDATE] Sort juga berdasarkan semester
              ->orderBy('mata_pelajaran.kelompok', 'ASC')
              ->orderBy('mata_pelajaran.nama_mapel', 'ASC');

        // Data Jenjang untuk Dropdown Filter
        $jenjangList = [];
        if ($this->jenjangModel) {
            $allJenjangs = method_exists($this->jenjangModel, 'getAktifForIdentitas') 
                ? $this->jenjangModel->getAktifForIdentitas() 
                : $this->jenjangModel->findAll();
                
            $jenjangList = array_filter($allJenjangs, function($j) use ($isSuperAdmin, $userJenjang) {
                $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
                if (in_array($kode, $this->globalIdentifiers)) return false;
                return $isSuperAdmin || $kode === $userJenjang;
            });
        }

        $data = [
            'title'            => 'Master Mata Pelajaran',
            'mapel_list'       => $model->paginate((int)$perPage, 'mapel'),
            'pager'            => $model->pager,
            'jenjang_list'     => $jenjangList,
            'is_restricted'    => !$isSuperAdmin,
            'available_levels' => $availableLevels, // Kirim opsi tingkat cerdas ke view
            'current_filter'   => [
                'unit'     => $filterUnit ?? ($isSuperAdmin ? 'GLOBAL' : $userJenjang),
                'tingkat'  => $filterTingkat,
                'semester' => $filterSemester, // [UPDATE] Kirim value filter semester ke view
                'per_page' => $perPage,
                'search'   => $search
            ]
        ];

        return view('masterdata/matapelajaran/index', $data);
    }
    
    /**
     * Form Tambah.
     */
    public function new(): string
    {
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);
        
        // Tentukan level default berdasarkan user login
        $defaultLevels = $this->getLevelsByJenjang($isSuperAdmin ? null : $userJenjang);

        $kurikulumList = [];
        if ($this->kurikulumModel) {
            $kurikulumBuilder = $this->kurikulumModel->where('status', 'aktif');
            if (!$isSuperAdmin) {
                $kurikulumBuilder->where('kode_jenjang', $userJenjang);
            }
            $kurikulumList = $kurikulumBuilder->findAll(); 
        }

        $filteredJenjang = [];
        if ($this->jenjangModel) {
            $allJenjang = method_exists($this->jenjangModel, 'getAktifForIdentitas')
                ? $this->jenjangModel->getAktifForIdentitas()
                : $this->jenjangModel->findAll();
                
            $filteredJenjang = array_filter($allJenjang, function($j) use ($isSuperAdmin, $userJenjang) {
                $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
                return $isSuperAdmin || $kode === $userJenjang;
            });
        }

        $data = [
            'title'            => 'Tambah Mata Pelajaran',
            'mapel'            => [
                'tingkat'       => '', 
                'semester'      => '', // [UPDATE] Tambahkan default empty string untuk semester
                'bobot_tugas'   => 0.4,
                'bobot_uts'     => 0.2,
                'bobot_uas'     => 0.3,
                'bobot_absensi' => 0.1,
                'status'        => 'aktif',
                'kode_jenjang'  => $isSuperAdmin ? '' : $userJenjang
            ], 
            'validation'       => \Config\Services::validation(),
            'kurikulum'        => $kurikulumList, 
            'jenjang_list'     => $filteredJenjang,
            'is_restricted'    => !$isSuperAdmin,
            'user_unit'        => $userJenjang,
            'available_levels' => $defaultLevels // Kirim ke view form
        ];
        
        return view('masterdata/matapelajaran/form', $data);
    }
    
    /**
     * Edit Mata Pelajaran.
     */
    public function edit($id): string
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $mapel = $this->mapelModel->find($id);
        if (!$mapel) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);
        
        if (!$isSuperAdmin && strtoupper($mapel['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Akses Ditolak: Data ini milik unit lain.');
        }

        // Ambil level cerdas berdasarkan jenjang mapel tersebut
        $smartLevels = $this->getLevelsByJenjang($mapel['kode_jenjang']);

        $kurikulumList = [];
        if ($this->kurikulumModel) {
            $kurikulumList = $this->kurikulumModel->groupStart()
                ->groupStart()
                    ->where('status', 'aktif')
                    ->where('kode_jenjang', $mapel['kode_jenjang'])
                ->groupEnd()
                ->orWhere('id', $mapel['kurikulum_id'])
            ->groupEnd()
            ->findAll();
        }
        
        $filteredJenjang = [];
        if ($this->jenjangModel) {
            $allJenjang = method_exists($this->jenjangModel, 'getAktifForIdentitas')
                ? $this->jenjangModel->getAktifForIdentitas()
                : $this->jenjangModel->findAll();

            $filteredJenjang = array_filter($allJenjang, function($j) use ($isSuperAdmin, $userJenjang) {
                $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
                return $isSuperAdmin || $kode === $userJenjang;
            });
        }

        $data = [
            'title'            => 'Edit Mata Pelajaran: ' . $mapel['nama_mapel'],
            'mapel'            => $mapel,
            'validation'       => \Config\Services::validation(),
            'kurikulum'        => $kurikulumList,
            'jenjang_list'     => $filteredJenjang,
            'is_restricted'    => !$isSuperAdmin,
            'user_unit'        => $userJenjang,
            'available_levels' => $smartLevels // Kirim ke view form
        ];
        
        return view('masterdata/matapelajaran/form', $data);
    }

    /**
     * Menampilkan Detail Mata Pelajaran.
     */
    public function show($id = null)
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $model = $this->mapelModel;
        $model->select('mata_pelajaran.*, kurikulum.nama_kurikulum, js.nama_jenjang as unit_sekolah');
        $model->join('kurikulum', 'kurikulum.id = mata_pelajaran.kurikulum_id', 'left');
        $model->join('jenjang_sekolah js', 'js.kode_jenjang = mata_pelajaran.kode_jenjang', 'left');
        
        $mapel = $model->find($id);

        if (!$mapel) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        if (!$isSuperAdmin && strtoupper($mapel['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Akses Ditolak: Data ini milik unit lain.');
        }

        $data = [
            'title' => 'Detail Mata Pelajaran',
            'mapel' => $mapel
        ];

        return view('masterdata/matapelajaran/detail', $data);
    }
    
    public function create(): RedirectResponse
    {
        $post = $this->request->getPost();
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        if (!$isSuperAdmin) {
            $post['kode_jenjang'] = $userJenjang;
        }

        try {
            if (!$this->mapelModel->insert($post)) {
                return redirect()->back()->withInput()->with('errors', $this->mapelModel->errors());
            }
        } catch (Throwable $t) {
            return redirect()->back()->withInput()->with('error', $t->getMessage());
        }

        return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }
    
    public function update($id = null): RedirectResponse
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $existing = $this->mapelModel->find($id);
        if (!$existing) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        if (!$isSuperAdmin && strtoupper($existing['kode_jenjang']) !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Akses Ditolak.');
        }

        $post = $this->request->getPost();
        $post['id'] = $id; 

        try {
            if (!$this->mapelModel->save($post)) {
                return redirect()->back()->withInput()->with('errors', $this->mapelModel->errors());
            }
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function delete($id = null): RedirectResponse
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $mapel = $this->mapelModel->find($id);
        if (!$mapel) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        if (!$isSuperAdmin && strtoupper($mapel['kode_jenjang']) !== $userJenjang) {
             return redirect()->back()->with('error', 'Otoritas Ditolak.');
        }

        if ($this->mapelModel->delete($id)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data Mata Pelajaran berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }
}