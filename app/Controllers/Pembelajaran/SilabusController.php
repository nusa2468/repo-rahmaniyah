<?php

namespace App\Controllers\Pembelajaran;

use App\Controllers\BaseController;
use App\Models\Pembelajaran\SilabusModel;
use App\Models\Pembelajaran\RppModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

/**
 * SilabusController (Fixed & Optimized)
 * - Menggunakan GroupBy untuk mencegah duplikasi tampilan index.
 * - Menyimpan Kegiatan Pembelajaran & Penilaian dengan benar.
 */
class SilabusController extends BaseController
{
    use ResponseTrait;

    protected $silabusModel;
    protected $rppModel;
    protected $userModel;

    public function __construct()
    {
        if (class_exists(SilabusModel::class)) $this->silabusModel = new SilabusModel();
        if (class_exists(RppModel::class)) $this->rppModel = new RppModel();
        if (class_exists(UserModel::class)) $this->userModel = new UserModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('/login');

        $user = method_exists($this->userModel, 'getPenggunaWithRole') 
            ? $this->userModel->getPenggunaWithRole($userId)
            : $this->userModel->find($userId);

        if (!$user) return redirect()->to('/login')->with('error', 'Sesi tidak valid.');

        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        $isRestricted = ($userUnit !== 'GLOBAL');

        $kodeJenjang    = $isRestricted ? $userUnit : $this->request->getGet('kode_jenjang');
        $keyword        = $this->request->getGet('keyword');
        $jenisKurikulum = $this->request->getGet('jenis_kurikulum');

        // Gunakan nama tabel eksplisit
        $query = $this->silabusModel->orderBy('pembelajaran_silabus.created_at', 'DESC');
        
        // --- FIX PENTING: GROUPING ---
        // Mencegah duplikasi baris di tabel index.
        // Menyatukan kompetensi-kompetensi menjadi satu baris Header.
        $query->groupBy([
            'pembelajaran_silabus.kode_jenjang', 
            'pembelajaran_silabus.mata_pelajaran_id', 
            'pembelajaran_silabus.tingkat_kelas', 
            'pembelajaran_silabus.tahun_ajaran', 
            'pembelajaran_silabus.semester'
        ]);
        // -----------------------------

        if (method_exists($this->silabusModel, 'getFilteredData')) {
            $query->getFilteredData($keyword, $kodeJenjang, $jenisKurikulum);
        } else {
            if ($kodeJenjang && $kodeJenjang !== 'ALL') $query->where('kode_jenjang', $kodeJenjang);
            if ($keyword) $query->like('materi_pokok', $keyword);
        }

        $dataSilabus = $query->paginate(10, 'silabus');

        if ($this->rppModel && !empty($dataSilabus)) {
            foreach ($dataSilabus as &$silabus) {
                if(!isset($silabus['id'])) continue;
                $silabus['rpp_list'] = $this->rppModel
                    ->where('silabus_id', $silabus['id'])
                    ->orderBy('pertemuan_ke', 'ASC')
                    ->findAll();
                $silabus['jumlah_rpp'] = count($silabus['rpp_list']);
            }
        }

        $stats = method_exists($this->silabusModel, 'getStatistics')
            ? $this->silabusModel->getStatistics($isRestricted ? $userUnit : null)
            : ['total' => $this->silabusModel->countAllResults()];

        $data = [
            'title'            => 'Perangkat Ajar Terintegrasi',
            'silabus'          => $dataSilabus,
            'pager'            => $this->silabusModel->pager,
            'stats'            => $stats,
            'user_role'        => $user['role_name'] ?? 'User',
            'user_unit'        => $userUnit,
            'filter_jenjang'   => $kodeJenjang,
            'filter_kurikulum' => $jenisKurikulum,
            'filter_keyword'   => $keyword,
            'is_restricted'    => $isRestricted
        ];

        return view('pembelajaran/silabus/index', $data);
    }

    public function new()
    {
        return view('pembelajaran/silabus/form', [
            'title'      => 'Tambah Silabus',
            'silabus'    => null, 
            'mapel'      => $this->getMataPelajaranOptions(),
            'validation' => \Config\Services::validation()
        ]);
    }

    public function edit($id)
    {
        // PENTING: Pakai getFilteredData() agar JOIN Mata Pelajaran terjadi (Nama Mapel Muncul)
        $silabus = $this->silabusModel->getFilteredData()->find($id);
        
        if (!$silabus) return redirect()->to('app/pembelajaran/silabus')->with('error', 'Data tidak ditemukan.');

        $user = $this->userModel->find(session()->get('user_id'));
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        if ($userUnit !== 'GLOBAL' && strtoupper($silabus['kode_jenjang']) !== $userUnit) {
            return redirect()->to('app/pembelajaran/silabus')->with('error', 'Akses Ditolak.');
        }

        $criteria = [
            'mata_pelajaran_id' => $silabus['mata_pelajaran_id'],
            'tingkat_kelas'     => $silabus['tingkat_kelas'],
            'tahun_ajaran'      => $silabus['tahun_ajaran'],
            'semester'          => $silabus['semester'],
            'jenis_kurikulum'   => $silabus['jenis_kurikulum'],
            'kode_jenjang'      => $silabus['kode_jenjang']
        ];

        $silabusDetails = $this->silabusModel->where($criteria)->findAll();

        return view('pembelajaran/silabus/form', [
            'title'           => 'Edit Silabus',
            'silabus'         => $silabus,        
            'silabus_details' => $silabusDetails, 
            'mapel'           => $this->getMataPelajaranOptions(),
            'validation'      => \Config\Services::validation()
        ]);
    }

    public function createBulk()
    {
        $items = $this->request->getPost('items');
        if (empty($items) || !is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada data kompetensi yang diinput.');
        }
        
        $jenisKurikulum = $this->request->getPost('jenis_kurikulum');
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $userId         = session()->get('user_id');

        $user = $this->userModel->find($userId);
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        if ($userUnit !== 'GLOBAL' && $inputJenjang !== $userUnit) {
            return redirect()->back()->with('error', 'Akses Ditolak.');
        }

        $commonData = [
            'kode_jenjang'             => $inputJenjang,
            'mata_pelajaran_id'        => $this->request->getPost('mata_pelajaran_id'),
            'tingkat_kelas'            => $this->request->getPost('tingkat_kelas'),
            // Kolom semester & tahun_ajaran WAJIB ADA di tabel pembelajaran_silabus
            'tahun_ajaran'             => $this->request->getPost('tahun_ajaran'),
            'semester'                 => $this->request->getPost('semester'),
            'jenis_kurikulum'          => $jenisKurikulum,
            'status'                   => 'Final',
            'created_by'               => $userId,
            'fase'                     => $this->request->getPost('fase'),
            'capaian_pembelajaran'     => $this->request->getPost('capaian_pembelajaran'),
            'profil_pelajar_pancasila' => $this->request->getPost('profil_pelajar_pancasila'),
            'tema'                     => $this->request->getPost('tema'),
            'subtema'                  => $this->request->getPost('subtema'),
            'kompetensi_inti'          => $this->request->getPost('kompetensi_inti'),
        ];

        $batchData = [];
        foreach ($items as $item) {
            $row = array_merge($commonData, [
                'materi_pokok'             => $item['materi_pokok'] ?? '',
                'alokasi_waktu'            => $item['alokasi_waktu'] ?? '',
                'sumber_belajar'           => $item['sumber_belajar'] ?? '',
                'kompetensi_dasar'         => $item['kompetensi_dasar'] ?? '',
                'indikator'                => $item['indikator'] ?? '',
                'alur_tujuan_pembelajaran' => $item['alur_tujuan_pembelajaran'] ?? '',
                // FIX: Tambahkan kolom baru agar tersimpan
                'kegiatan_pembelajaran'    => $item['kegiatan_pembelajaran'] ?? '',
                'penilaian'                => $item['penilaian'] ?? '',
            ]);
            $batchData[] = $row;
        }

        if (!empty($batchData)) {
            $this->silabusModel->insertBatch($batchData);
        }

        return redirect()->to('app/pembelajaran/silabus')->with('message', count($batchData) . ' Kompetensi berhasil disimpan.');
    }

    public function updateBulk($headerId = null)
    {
        $items = $this->request->getPost('items');
        if ($headerId === null) return redirect()->back()->with('error', 'ID Header hilang.');

        $refSilabus = $this->silabusModel->find($headerId);
        if (!$refSilabus) return redirect()->to('app/pembelajaran/silabus')->with('error', 'Data tidak ditemukan.');

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        if ($userUnit !== 'GLOBAL' && strtoupper($refSilabus['kode_jenjang']) !== $userUnit) {
            return redirect()->back()->with('error', 'Akses Ditolak.');
        }

        $contextWhere = [
            'mata_pelajaran_id' => $refSilabus['mata_pelajaran_id'],
            'tingkat_kelas'     => $refSilabus['tingkat_kelas'],
            'tahun_ajaran'      => $refSilabus['tahun_ajaran'],
            'semester'          => $refSilabus['semester'],
            'kode_jenjang'      => $refSilabus['kode_jenjang'],
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $this->silabusModel->where($contextWhere)->delete(); 

        $commonData = [
            'kode_jenjang'             => $refSilabus['kode_jenjang'],
            'mata_pelajaran_id'        => $refSilabus['mata_pelajaran_id'],
            'tingkat_kelas'            => $refSilabus['tingkat_kelas'],
            'tahun_ajaran'             => $refSilabus['tahun_ajaran'],
            'semester'                 => $refSilabus['semester'],
            'jenis_kurikulum'          => $refSilabus['jenis_kurikulum'], 
            'status'                   => 'Final',
            'created_by'               => $userId,
            'fase'                     => $this->request->getPost('fase'),
            'capaian_pembelajaran'     => $this->request->getPost('capaian_pembelajaran'),
            'profil_pelajar_pancasila' => $this->request->getPost('profil_pelajar_pancasila'),
            'tema'                     => $this->request->getPost('tema'),
            'subtema'                  => $this->request->getPost('subtema'),
            'kompetensi_inti'          => $this->request->getPost('kompetensi_inti'),
        ];

        $batchData = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                $row = array_merge($commonData, [
                    'materi_pokok'             => $item['materi_pokok'] ?? '',
                    'alokasi_waktu'            => $item['alokasi_waktu'] ?? '',
                    'sumber_belajar'           => $item['sumber_belajar'] ?? '',
                    'kompetensi_dasar'         => $item['kompetensi_dasar'] ?? '',
                    'indikator'                => $item['indikator'] ?? '',
                    'alur_tujuan_pembelajaran' => $item['alur_tujuan_pembelajaran'] ?? '',
                    // FIX: Tambahkan kolom baru agar tersimpan
                    'kegiatan_pembelajaran'    => $item['kegiatan_pembelajaran'] ?? '',
                    'penilaian'                => $item['penilaian'] ?? '',
                ]);
                $batchData[] = $row;
            }
        }

        if (!empty($batchData)) {
            $this->silabusModel->insertBatch($batchData);
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }

        return redirect()->to('app/pembelajaran/silabus')->with('message', 'Silabus berhasil diperbarui.');
    }

    public function delete($id = null)
    {
        if ($id === null) return redirect()->back();
        
        // Hapus SEMUA data yang satu grup (Contextual Delete)
        $silabus = $this->silabusModel->find($id);
        if ($silabus) {
             $contextWhere = [
                'mata_pelajaran_id' => $silabus['mata_pelajaran_id'],
                'tingkat_kelas'     => $silabus['tingkat_kelas'],
                'tahun_ajaran'      => $silabus['tahun_ajaran'],
                'semester'          => $silabus['semester'],
                'kode_jenjang'      => $silabus['kode_jenjang'],
            ];
            $this->silabusModel->where($contextWhere)->delete();
        } else {
            $this->silabusModel->delete($id);
        }
        
        return redirect()->to('app/pembelajaran/silabus')->with('message', 'Data silabus berhasil dihapus.');
    }

    public function print($id)
    {
        return redirect()->back()->with('info', 'Fitur cetak sedang dikonfigurasi.');
    }

    private function getMataPelajaranOptions()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('mata_pelajaran')) {
            // Mengambil kolom yang diperlukan untuk fitur Dropdown Berantai di View
            return $db->table('mata_pelajaran')
                      ->select('id, nama_mapel, kode_mapel, kode_jenjang, tingkat')
                      ->where('status', 'aktif') 
                      ->orderBy('nama_mapel', 'ASC')
                      ->get()->getResultArray();
        }
        return []; 
    }
}