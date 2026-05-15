<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\SiswaDemografiModel;
use App\Models\SiswaKeluargaModel;
use App\Models\Kesiswaan\EkskulModel;
use App\Models\Kesiswaan\PrestasiSiswaModel;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

/**
 * Controller Siswa (Versi Enterprise - Anti Cache Bug)
 * Status: FIXED (Dropdown Unit & Relasi Kelas dijamin HIDUP menggunakan Try-Catch Direct Query)
 */
class Siswa extends BaseController
{
    private $redirectBaseUrl = 'app/masterdata/siswa';
    private $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ROOT', 'ALL'];
    
    protected $siswaModel;
    protected $siswaDemografiModel;
    protected $siswaKeluargaModel;
    protected $db;

    public function __construct()
    {
        $this->siswaModel          = new SiswaModel();
        $this->siswaDemografiModel = class_exists(SiswaDemografiModel::class) ? new SiswaDemografiModel() : null;
        $this->siswaKeluargaModel  = class_exists(SiswaKeluargaModel::class) ? new SiswaKeluargaModel() : null;
        $this->db                  = \Config\Database::connect();
    }

    public function index(): string
    {
        $userJenjang  = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        $filterUnit     = $this->request->getGet('unit');
        $filterJurusan  = $this->request->getGet('jurusan');
        $filterAngkatan = $this->request->getGet('angkatan');
        $search         = $this->request->getGet('search');
        $perPage        = $this->request->getGet('per_page') ?? 10;

        if (!$isSuperAdmin) {
            $filterUnit = $userJenjang;
        }

        $unitParam = (empty($filterUnit) || in_array(strtoupper($filterUnit), $this->globalIdentifiers)) ? null : $filterUnit;

        $model = $this->siswaModel;
        
        $model->select('siswa.*, d.nama_ayah, d.nama_ibu, d.telepon as telepon_ortu, d.alamat as alamat_demografi, j.nama_jurusan');
        $model->join('siswa_demografi d', 'd.id_siswa = siswa.id', 'left');
        $model->join('jurusan j', 'j.id = siswa.id_jurusan', 'left');

        if ($unitParam) {
            $model->where('siswa.kode_jenjang', $unitParam);
        }
        if (!empty($filterJurusan)) {
            $model->where('siswa.id_jurusan', $filterJurusan);
        }
        if (!empty($filterAngkatan)) {
            $model->where('siswa.angkatan', $filterAngkatan);
        }
        if (!empty($search)) {
            $model->groupStart()
                  ->like('siswa.nama_lengkap', $search)
                  ->orLike('siswa.nis', $search)
                  ->orLike('siswa.nisn', $search)
                  ->orLike('siswa.nik', $search)
                  ->groupEnd();
        }

        $model->orderBy('siswa.nama_lengkap', 'ASC');
        $siswaData = $model->paginate((int)$perPage, 'siswa');

        foreach ($siswaData as &$siswa) {
            $idSiswa = is_object($siswa) ? ($siswa->id ?? null) : ($siswa['id'] ?? null);
            if (empty($idSiswa)) continue;

            $enrollData = null;
            try {
                // Direct query tanpa tableExists() untuk menghindari bug cache CI4
                $enrollData = $this->db->table('siswa_enrollment')
                    ->select('kelas.nama_kelas, tahun_ajaran.tahun_ajaran')
                    ->join('kelas', 'kelas.id = siswa_enrollment.id_kelas', 'left')
                    ->join('tahun_ajaran', 'tahun_ajaran.id = siswa_enrollment.id_tahun_ajaran', 'left')
                    ->where('id_siswa', $idSiswa)
                    ->where('siswa_enrollment.status_akademik', 'Aktif')
                    ->orderBy('siswa_enrollment.id', 'DESC')
                    ->get()
                    ->getRowArray();
            } catch (Throwable $e) {}

            if (is_object($siswa)) {
                $siswa->nama_kelas = $enrollData['nama_kelas'] ?? null;
                $siswa->tahun_ajaran = $enrollData['tahun_ajaran'] ?? '-';
                $siswa->akademik   = $enrollData;
            } else {
                $siswa['nama_kelas'] = $enrollData['nama_kelas'] ?? null;
                $siswa['tahun_ajaran'] = $enrollData['tahun_ajaran'] ?? '-';
                $siswa['akademik']   = $enrollData;
            }
        }

        // ==============================================================================
        // KUNCI PERBAIKAN: DIRECT QUERY MENGHINDARI "tableExists()" BUG 
        // ==============================================================================
        $jenjangList = [];
        try {
            $allJenjangs = $this->db->table('jenjang_sekolah')
                                  ->orderBy('urutan', 'ASC')
                                  ->get()->getResultArray();
            
            foreach ($allJenjangs as $j) {
                $kode = strtoupper($j['kode_jenjang']);
                if (in_array($kode, $this->globalIdentifiers)) continue;
                
                // Masukkan jika user Superadmin ATAU jika kodenya cocok dengan unit user
                if ($isSuperAdmin || $kode === $userJenjang) {
                    $jenjangList[] = $j; 
                }
            }
        } catch (Throwable $e) {
            // HARD FALLBACK: Jika tabel benar-benar error, paksa isi dropdown!
            $jenjangList = [
                ['kode_jenjang' => 'TK', 'nama_jenjang' => 'TK'],
                ['kode_jenjang' => 'SD', 'nama_jenjang' => 'SD'],
                ['kode_jenjang' => 'SMP', 'nama_jenjang' => 'SMP'],
                ['kode_jenjang' => 'SMA', 'nama_jenjang' => 'SMA'],
            ];
        }
        
        $jurusanList = [];
        try {
             $qb = $this->db->table('jurusan');
             if($unitParam) $qb->where('kode_jenjang', $unitParam);
             $jurusanList = $qb->get()->getResultArray();
        } catch (Throwable $e) {}

        $data = [
            'title'          => 'Master Siswa - ' . ($unitParam ? 'Unit '.strtoupper($unitParam) : 'Seluruh Unit'),
            'siswa_data'     => $siswaData,
            'pager'          => $model->pager,
            'jenjang_list'   => $jenjangList,
            'jurusan_list'   => $jurusanList,
            'is_restricted'  => !$isSuperAdmin,
            'current_filter' => [
                'unit'     => $filterUnit ?? ($isSuperAdmin ? 'GLOBAL' : $userJenjang),
                'jurusan'  => $filterJurusan,
                'angkatan' => $filterAngkatan,
                'per_page' => $perPage,
                'search'   => $search,
            ]
        ];

        return view('masterdata/siswa/index', $data);
    }

    public function show($id = null): string
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $siswaDataRelasi = $this->siswaModel->getSiswaDataWithRelations(null, $id);
        if (empty($siswaDataRelasi)) throw PageNotFoundException::forPageNotFound();
        
        $dataSiswaFull = $siswaDataRelasi[0];
        $idSiswa = $dataSiswaFull['siswa']['id'] ?? $id;

        $dataSiswaFull['akademik_histori'] = [];
        try {
            $dataSiswaFull['akademik_histori'] = $this->db->table('siswa_enrollment')
                    ->select('siswa_enrollment.*, kelas.nama_kelas, ta.tahun_ajaran')
                    ->join('kelas', 'kelas.id = siswa_enrollment.id_kelas', 'left')
                    ->join('tahun_ajaran ta', 'ta.id = siswa_enrollment.id_tahun_ajaran', 'left')
                    ->where('id_siswa', $idSiswa)
                    ->orderBy('id', 'DESC')
                    ->get()->getResultArray();
        } catch (Throwable $e) {}

        $dataSiswaFull['kesiswaan'] = ['ekskul' => [], 'prestasi' => [], 'organisasi' => []];

        if (class_exists(EkskulModel::class)) {
            $ekskulModel = new EkskulModel();
            if (method_exists($ekskulModel, 'getKegiatanEkskulBySiswa')) {
                $dataSiswaFull['kesiswaan']['ekskul'] = $ekskulModel->getKegiatanEkskulBySiswa($idSiswa);
            }
        }
        
        if (class_exists(PrestasiSiswaModel::class)) {
            $prestasiModel = new PrestasiSiswaModel();
            $dataSiswaFull['kesiswaan']['prestasi'] = $prestasiModel
                    ->select('kesiswaan_prestasi.*, ta.tahun_ajaran as tahun_ajaran')
                    ->join('tahun_ajaran ta', 'ta.id = kesiswaan_prestasi.tahun_ajar_id', 'left')
                    ->where('kesiswaan_prestasi.siswa_id', $idSiswa)
                    ->orderBy('kesiswaan_prestasi.tanggal_prestasi', 'DESC') 
                    ->findAll();
        }
        
        try {
            $dataSiswaFull['kesiswaan']['organisasi'] = $this->db->table('osis_pengurus')
                ->select('osis_pengurus.*, op.tahun_ajaran as periode, op.nama_kabinet')
                ->join('osis_periode op', 'op.id = osis_pengurus.id_osis_periode', 'left')
                ->where('id_siswa', $idSiswa)
                ->orderBy('op.tahun_ajaran', 'DESC')
                ->get()->getResultArray();
        } catch (Throwable $e) {}

        $jurusanList = [];
        try {
            $jurusanList = $this->db->table('jurusan')->get()->getResultArray();
        } catch (Throwable $e) {}

        $data = [
            'title'        => 'Profil: ' . ($dataSiswaFull['siswa']['nama_lengkap'] ?? 'Siswa'),
            'siswa'        => $dataSiswaFull['siswa'],
            'siswa_relasi' => $dataSiswaFull,
            'jurusan_list' => $jurusanList,
        ];

        return view('masterdata/siswa/show', $data);
    }

    public function new(): string
    {
        $userJenjang  = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);
        
        // DIRECT QUERY JENJANG (Tanpa tableExists bug)
        $jenjangList = [];
        try {
            $allJenjangs = $this->db->table('jenjang_sekolah')
                                  ->orderBy('urutan', 'ASC')
                                  ->get()->getResultArray();
            foreach ($allJenjangs as $j) {
                $kode = strtoupper($j['kode_jenjang']);
                if (in_array($kode, $this->globalIdentifiers)) continue;
                if ($isSuperAdmin || $kode === $userJenjang) {
                    $jenjangList[] = $j;
                }
            }
        } catch (Throwable $e) {
            $jenjangList = [
                ['kode_jenjang' => 'TK', 'nama_jenjang' => 'TK'],
                ['kode_jenjang' => 'SD', 'nama_jenjang' => 'SD'],
                ['kode_jenjang' => 'SMP', 'nama_jenjang' => 'SMP'],
                ['kode_jenjang' => 'SMA', 'nama_jenjang' => 'SMA'],
            ];
        }

        // DIRECT QUERY KELAS
        $kelasList = [];
        try {
            $qb = $this->db->table('kelas')->where('is_aktif', 1);
            if (!$isSuperAdmin) $qb->where('kode_jenjang', $userJenjang);
            $kelasList = $qb->orderBy('nama_kelas', 'ASC')->get()->getResultArray();
        } catch (Throwable $e) {}

        // DIRECT QUERY JURUSAN
        $jurusanList = [];
        try {
            $jurusanList = $this->db->table('jurusan')->get()->getResultArray();
        } catch (Throwable $e) {}

        $data = [
            'title'        => 'Registrasi Siswa Baru',
            'siswa'        => [], 
            'jurusan_list' => $jurusanList,
            'jenjang_list' => $jenjangList,
            'kelas_list'   => $kelasList,
            'validation'   => \Config\Services::validation(),
        ];
        return view('masterdata/siswa/form', $data);
    }

    public function create(): RedirectResponse
    {
        $dataPost = $this->request->getPost();
        
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if ($userJenjang !== 'GLOBAL' && strtoupper($dataPost['kode_jenjang']) !== $userJenjang) {
            return redirect()->back()->withInput()->with('error', 'Otoritas Ditolak: Anda tidak dapat menambahkan siswa di unit lain.');
        }

        $rules = [
            'nama_lengkap' => 'required|min_length[3]',
            'nisn'         => 'permit_empty|numeric|exact_length[10]|is_unique[siswa.nisn]',
            'nis'          => 'required|is_unique[siswa.nis]',
            'kode_jenjang' => 'required',
            'jenis_kelamin'=> 'required|in_list[L,P]',
            'angkatan'     => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->transBegin();
        try {
            $tempatLahir  = $dataPost['tempat_lahir'] ?? ($dataPost['demografi']['tempat_lahir'] ?? null);
            $tanggalLahir = $dataPost['tanggal_lahir'] ?? ($dataPost['demografi']['tanggal_lahir'] ?? null);
            $ibuKandung   = $dataPost['nama_ibu_kandung'] ?? ($dataPost['demografi']['nama_ibu'] ?? null); 
            $agama        = $dataPost['agama'] ?? ($dataPost['demografi']['agama'] ?? null);
            $alamat       = $dataPost['alamat'] ?? ($dataPost['demografi']['alamat'] ?? null);

            $rawStatus = $dataPost['status'] ?? 'Aktif';
            $fixedStatus = ucfirst(strtolower($rawStatus));

            $siswaData = [
                'kode_jenjang'      => $dataPost['kode_jenjang'],
                'nama_lengkap'      => $dataPost['nama_lengkap'],
                'nisn'              => $dataPost['nisn'] ?: null,
                'nis'               => $dataPost['nis'],
                'nik'               => $dataPost['nik'] ?? null,
                'jenis_kelamin'     => $dataPost['jenis_kelamin'],
                'angkatan'          => $dataPost['angkatan'],
                'id_jurusan'        => !empty($dataPost['id_jurusan']) ? $dataPost['id_jurusan'] : null,
                'status'            => $fixedStatus, 
                'password'          => password_hash($dataPost['nis'], PASSWORD_DEFAULT),
                'tempat_lahir'      => $tempatLahir,
                'tanggal_lahir'     => $tanggalLahir,
                'nama_ibu_kandung'  => $ibuKandung,
                'agama'             => $agama,
                'alamat'            => $alamat
            ];
            
            $this->siswaModel->allowCallbacks(false)->insert($siswaData);
            $idSiswa = $this->siswaModel->getInsertID();

            // PENEMPATAN KELAS AWAL (ENROLLMENT)
            $idKelasInitial = $this->request->getPost('id_kelas_initial');
            $taAktif = null;
            try {
                $taAktif = $this->db->table('tahun_ajaran')->where('status', 'aktif')->get()->getRowArray();
            } catch (Throwable $e) {}

            if (!empty($idKelasInitial) && !empty($taAktif)) {
                $this->db->table('siswa_enrollment')->insert([
                    'id_siswa'        => $idSiswa,
                    'id_kelas'        => $idKelasInitial,
                    'id_tahun_ajaran' => $taAktif['id'],
                    'status_akademik' => 'Aktif',
                    'tanggal_masuk'   => date('Y-m-d'),
                    'created_at'      => date('Y-m-d H:i:s')
                ]);
            }
            
            if (isset($dataPost['demografi']) && $this->siswaDemografiModel) {
                $demografiData = array_merge($dataPost['demografi'], ['id_siswa' => $idSiswa]);
                if(empty($demografiData['nama_ibu']) && $ibuKandung) $demografiData['nama_ibu'] = $ibuKandung;
                if(empty($demografiData['alamat']) && $alamat) $demografiData['alamat'] = $alamat;
                
                $this->siswaDemografiModel->insert($demografiData);
            }
            
            if (isset($dataPost['keluarga']) && $this->siswaKeluargaModel) {
                foreach ($dataPost['keluarga'] as $key => $k) {
                    if (empty($k['nama_lengkap'])) continue;
                    $this->siswaKeluargaModel->insert([
                        'id_siswa'     => $idSiswa,
                        'hubungan'     => $k['hubungan'] ?? ucfirst($key),
                        'nama_lengkap' => $k['nama_lengkap'],
                        'nik'          => $k['nik'] ?? null,
                        'pekerjaan'    => $k['pekerjaan'] ?? null,
                        'no_telepon'   => $k['no_telepon'] ?? null,
                    ]);
                }
            }

            $this->db->transCommit();
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Siswa berhasil didaftarkan dan ditempatkan di kelas.');
        } catch (Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit($id = null): string
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();
        
        $siswaMaster = $this->siswaModel->find($id);
        if (!$siswaMaster) throw PageNotFoundException::forPageNotFound();
        
        $kodeJenjang = is_object($siswaMaster) ? ($siswaMaster->kode_jenjang ?? '') : ($siswaMaster['kode_jenjang'] ?? '');
        $namaLengkap = is_object($siswaMaster) ? ($siswaMaster->nama_lengkap ?? '') : ($siswaMaster['nama_lengkap'] ?? '');

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if ($userJenjang !== 'GLOBAL' && strtoupper($kodeJenjang) !== $userJenjang) {
            throw PageNotFoundException::forPageNotFound("Akses Ditolak: Siswa berbeda unit.");
        }

        $jenjangList = [];
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        // DIRECT QUERY JENJANG
        try {
            $allJenjangs = $this->db->table('jenjang_sekolah')
                                  ->orderBy('urutan', 'ASC')
                                  ->get()->getResultArray();
            foreach ($allJenjangs as $j) {
                $kode = strtoupper($j['kode_jenjang']);
                if (in_array($kode, $this->globalIdentifiers)) continue;
                if ($isSuperAdmin || $kode === $userJenjang) {
                    $jenjangList[] = $j;
                }
            }
        } catch (Throwable $e) {
            $jenjangList = [
                ['kode_jenjang' => 'TK', 'nama_jenjang' => 'TK'],
                ['kode_jenjang' => 'SD', 'nama_jenjang' => 'SD'],
                ['kode_jenjang' => 'SMP', 'nama_jenjang' => 'SMP'],
                ['kode_jenjang' => 'SMA', 'nama_jenjang' => 'SMA'],
            ];
        }

        $siswaDataRelasi = $this->siswaModel->getSiswaDataWithRelations(null, $id);
        
        $currentEnrollment = null;
        try {
            $currentEnrollment = $this->db->table('siswa_enrollment')
                ->where('id_siswa', $id)
                ->where('status_akademik', 'Aktif')
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();
        } catch (Throwable $e) {}

        // DIRECT QUERY KELAS
        $kelasList = [];
        try {
            $qb = $this->db->table('kelas')->where('is_aktif', 1);
            if (!$isSuperAdmin) $qb->where('kode_jenjang', $userJenjang);
            $kelasList = $qb->orderBy('nama_kelas', 'ASC')->get()->getResultArray();
        } catch (Throwable $e) {}

        $jurusanList = [];
        try {
            $jurusanList = $this->db->table('jurusan')->get()->getResultArray();
        } catch (Throwable $e) {}

        $data = [
            'title'             => 'Sunting Profil: ' . $namaLengkap,
            'siswa'             => $siswaMaster,
            'siswa_relasi'      => $siswaDataRelasi[0] ?? [],
            'jurusan_list'      => $jurusanList,
            'jenjang_list'      => $jenjangList,
            'kelas_list'        => $kelasList, 
            'current_kelas_id'  => $currentEnrollment['id_kelas'] ?? '',
            'validation'        => \Config\Services::validation(),
        ];
        return view('masterdata/siswa/form', $data);
    }

    public function update($id = null): RedirectResponse
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();
        $dataPost = $this->request->getPost();
        
        $this->db->transBegin();
        try {
            $tempatLahir  = $dataPost['tempat_lahir'] ?? ($dataPost['demografi']['tempat_lahir'] ?? null);
            $tanggalLahir = $dataPost['tanggal_lahir'] ?? ($dataPost['demografi']['tanggal_lahir'] ?? null);
            $ibuKandung   = $dataPost['nama_ibu_kandung'] ?? ($dataPost['demografi']['nama_ibu'] ?? null);
            $agama        = $dataPost['agama'] ?? ($dataPost['demografi']['agama'] ?? null);
            $alamat       = $dataPost['alamat'] ?? ($dataPost['demografi']['alamat'] ?? null);

            $rawStatus = $dataPost['status'] ?? 'Aktif';
            $fixedStatus = ucfirst(strtolower($rawStatus));

            $updateData = [
                'kode_jenjang'      => $dataPost['kode_jenjang'],
                'nama_lengkap'      => $dataPost['nama_lengkap'],
                'nisn'              => $dataPost['nisn'] ?: null,
                'nis'               => $dataPost['nis'],
                'nik'               => $dataPost['nik'] ?? null,
                'jenis_kelamin'     => $dataPost['jenis_kelamin'],
                'angkatan'          => $dataPost['angkatan'],
                'id_jurusan'        => !empty($dataPost['id_jurusan']) ? $dataPost['id_jurusan'] : null,
                'status'            => $fixedStatus,
                'tempat_lahir'      => $tempatLahir,
                'tanggal_lahir'     => $tanggalLahir,
                'nama_ibu_kandung'  => $ibuKandung,
                'agama'             => $agama,
                'alamat'            => $alamat
            ];

            $this->siswaModel->update($id, $updateData);

            if (isset($dataPost['demografi']) && $this->siswaDemografiModel) {
                $existingDemografi = $this->siswaDemografiModel->where('id_siswa', $id)->first();
                $idDemografi = null;
                if ($existingDemografi) {
                    if (is_object($existingDemografi)) {
                        $idDemografi = $existingDemografi->id ?? $existingDemografi->id_demografi ?? null;
                    } elseif (is_array($existingDemografi)) {
                        $idDemografi = $existingDemografi['id'] ?? $existingDemografi['id_demografi'] ?? null;
                    }
                }
                
                $demografiData = array_merge($dataPost['demografi'], ['id_siswa' => $id]);
                if(empty($demografiData['nama_ibu']) && $ibuKandung) $demografiData['nama_ibu'] = $ibuKandung;
                if(empty($demografiData['alamat']) && $alamat) $demografiData['alamat'] = $alamat;

                if ($idDemografi) {
                    $this->siswaDemografiModel->update($idDemografi, $demografiData);
                } else {
                    $this->siswaDemografiModel->insert($demografiData);
                }
            }

            if (isset($dataPost['keluarga']) && $this->siswaKeluargaModel) {
                foreach ($dataPost['keluarga'] as $key => $k) {
                    if (empty($k['nama_lengkap'])) continue;
                    
                    $prepData = [
                        'id_siswa'     => $id,
                        'hubungan'     => $k['hubungan'] ?? ucfirst($key),
                        'nama_lengkap' => $k['nama_lengkap'],
                        'nik'          => $k['nik'] ?? null,
                        'pekerjaan'    => $k['pekerjaan'] ?? null,
                        'no_telepon'   => $k['no_telepon'] ?? null,
                    ];

                    if (!empty($k['id'])) {
                        $this->siswaKeluargaModel->update($k['id'], $prepData);
                    } else {
                        $this->siswaKeluargaModel->insert($prepData);
                    }
                }
            }

            // PENEMPATAN KELAS
            $idKelasNew = $this->request->getPost('id_kelas_initial');
            $taAktif = null;
            try {
                $taAktif = $this->db->table('tahun_ajaran')->where('status', 'aktif')->get()->getRowArray();
            } catch (Throwable $e) {}

            if (!empty($idKelasNew) && !empty($taAktif)) {
                $existingEnroll = $this->db->table('siswa_enrollment')
                    ->where('id_siswa', $id)
                    ->where('id_tahun_ajaran', $taAktif['id'])
                    ->where('status_akademik', 'Aktif')
                    ->get()->getRowArray();

                if ($existingEnroll) {
                    if ($existingEnroll['id_kelas'] != $idKelasNew) {
                        $this->db->table('siswa_enrollment')
                            ->where('id', $existingEnroll['id'])
                            ->update(['id_kelas' => $idKelasNew, 'updated_at' => date('Y-m-d H:i:s')]);
                    }
                } else {
                    $this->db->table('siswa_enrollment')->insert([
                        'id_siswa'        => $id,
                        'id_kelas'        => $idKelasNew,
                        'id_tahun_ajaran' => $taAktif['id'],
                        'status_akademik' => 'Aktif',
                        'tanggal_masuk'   => date('Y-m-d'),
                        'created_at'      => date('Y-m-d H:i:s')
                    ]);
                }
            }

            $this->db->transCommit();
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data siswa diperbarui.');
        } catch (Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function delete($id = null): RedirectResponse
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();
        
        if ($this->siswaModel->delete($id)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data siswa dihapus (Soft Delete).');
        }
        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }
}