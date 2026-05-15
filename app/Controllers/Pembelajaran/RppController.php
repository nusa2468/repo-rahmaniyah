<?php

namespace App\Controllers\Pembelajaran;

use App\Controllers\BaseController;
use App\Models\Pembelajaran\RppModel;
use App\Models\Pembelajaran\SilabusModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

/**
 * RppController (Integrated Workflow)
 * Mendukung Single Edit dan Bulk Create (Massal) dari Silabus.
 */
class RppController extends BaseController
{
    use ResponseTrait;

    protected $rppModel;
    protected $silabusModel;
    protected $userModel;

    public function __construct()
    {
        // Inisialisasi Model secara aman
        if (class_exists('App\Models\Pembelajaran\RppModel')) {
            $this->rppModel = new \App\Models\Pembelajaran\RppModel();
        }
        if (class_exists('App\Models\Pembelajaran\SilabusModel')) {
            $this->silabusModel = new \App\Models\Pembelajaran\SilabusModel();
        }
        if (class_exists('App\Models\UserModel')) {
            $this->userModel = new \App\Models\UserModel();
        }
    }

    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to('login');

        // RBAC & Unit Scoping
        $user = $this->userModel->find($userId);
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        $isRestricted = ($userUnit !== 'GLOBAL');

        // Filter
        $kodeJenjang = $isRestricted ? $userUnit : $this->request->getGet('kode_jenjang');
        $keyword = $this->request->getGet('keyword');
        $jenisKurikulum = $this->request->getGet('jenis_kurikulum');

        // Query dengan Filter dari Model
        $this->rppModel->orderBy('created_at', 'DESC');
        
        if (method_exists($this->rppModel, 'getFilteredData')) {
            $this->rppModel->getFilteredData($keyword, $kodeJenjang, $jenisKurikulum);
        } else {
            // Fallback query jika method getFilteredData belum ada di model
             $this->rppModel->select('pembelajaran_rpp.*, pembelajaran_silabus.materi_pokok as silabus_materi')
                   ->join('pembelajaran_silabus', 'pembelajaran_silabus.id = pembelajaran_rpp.silabus_id', 'left');
             if ($kodeJenjang && $kodeJenjang !== 'ALL') $this->rppModel->where('pembelajaran_rpp.kode_jenjang', $kodeJenjang);
        }

        return view('pembelajaran/rpp/index', [
            'title'          => 'Manajemen Modul Ajar (RPP)',
            'rpp'            => $this->rppModel->paginate(10, 'rpp'),
            'pager'          => $this->rppModel->pager,
            'user_unit'      => $userUnit,
            'is_restricted'  => $isRestricted,
            'filter_jenjang' => $kodeJenjang,
            'filter_keyword' => $keyword,
            'filter_kurikulum'=> $jenisKurikulum,
            'stats'          => $this->rppModel->getStatistics($isRestricted ? $userUnit : null)
        ]);
    }

    /**
     * Form RPP Baru (Support Single & Bulk).
     */
    public function new()
    {
        $user = $this->userModel->find(session()->get('user_id'));
        $unit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        
        // Ambil Opsi Silabus sesuai unit
        $silabusQuery = $this->silabusModel->select('id, materi_pokok, kode_jenjang, tingkat_kelas, jenis_kurikulum');
        if ($unit !== 'GLOBAL') {
            $silabusQuery->where('kode_jenjang', $unit);
        }

        return view('pembelajaran/rpp/form', [
            'title'   => 'Tambah RPP',
            'rpp'     => null, 
            'silabus' => $silabusQuery->findAll()
        ]);
    }

    /**
     * CREATE SINGLE: Simpan satu RPP
     */
    public function create()
    {
        if (!$this->validate(['silabus_id' => 'required'])) {
            return redirect()->back()->withInput()->with('error', 'Silabus harus dipilih.');
        }

        $silabusId = $this->request->getPost('silabus_id');
        $silabus = $this->silabusModel->find($silabusId);

        if (!$silabus) {
            return redirect()->back()->withInput()->with('error', 'Silabus referensi tidak valid.');
        }

        // Ambil input form (flat)
        $input = $this->request->getPost();
        
        // Jika input datang dari items[0] (fallback), ambil elemen pertamanya
        if (isset($input['items']) && is_array($input['items'])) {
            $input = reset($input['items']);
        }

        $data = $this->mapRppData($input, $silabus);
        
        $userId = session()->get('user_id');
        $data['created_by'] = $userId;
        
        $user = $this->userModel->find($userId);
        if (isset($user['role']) && $user['role'] == 'guru') {
            $data['guru_id'] = $userId;
        } else {
            $data['guru_id'] = $this->request->getPost('guru_id') ?: null; 
        }
        
        $data['status'] = 'Final';

        $this->rppModel->insert($data);
        return redirect()->to(base_url('app/pembelajaran/rpp'))->with('message', 'RPP berhasil dibuat.');
    }

    /**
     * CREATE BULK: Menangani input massal dari rpp/form (repeater items).
     */
    public function createBulk()
    {
        $items = $this->request->getPost('items');
        $silabusId = $this->request->getPost('silabus_id');

        if (empty($items) || !is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada data pertemuan yang diinput.');
        }
        if (!$silabusId) {
            return redirect()->back()->withInput()->with('error', 'Silabus referensi wajib dipilih.');
        }

        $silabus = $this->silabusModel->find($silabusId);
        if (!$silabus) return redirect()->back()->with('error', 'Silabus tidak valid.');

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);
        
        $batchData = [];
        foreach ($items as $item) {
            $row = $this->mapRppData($item, $silabus);
            $row['created_by'] = $userId;

            if (isset($user['role']) && $user['role'] == 'guru') {
                $row['guru_id'] = $userId;
            } else {
                $row['guru_id'] = $this->request->getPost('guru_id') ?: null;
            }
            
            $row['status'] = 'Final';
            $batchData[] = $row;
        }

        if (!empty($batchData)) {
            $this->rppModel->insertBatch($batchData);
        }

        // Redirect ke Index RPP agar user melihat hasilnya
        return redirect()->to(base_url('app/pembelajaran/rpp'))->with('message', count($batchData) . ' Pertemuan RPP berhasil dibuat.');
    }

    /**
     * Form Edit Single.
     */
    public function edit($id)
    {
        $rpp = $this->rppModel->find($id);
        if (!$rpp) return redirect()->back()->with('error', 'Data RPP hilang.');

        $user = $this->userModel->find(session()->get('user_id'));
        $unit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        
        // Proteksi Data Lintas Unit
        if ($unit !== 'GLOBAL' && $rpp['kode_jenjang'] !== $unit) {
            return redirect()->back()->with('error', 'Akses Ditolak.');
        }

        $silabusQuery = $this->silabusModel->select('id, materi_pokok, kode_jenjang, tingkat_kelas, jenis_kurikulum');
        if ($unit !== 'GLOBAL') {
            $silabusQuery->where('kode_jenjang', $unit);
        }

        return view('pembelajaran/rpp/form', [
            'title'   => 'Edit RPP',
            'rpp'     => $rpp, 
            'silabus' => $silabusQuery->findAll()
        ]);
    }

    /**
     * UPDATE: Menangani edit single
     */
    public function update($id = null)
    {
        if ($id === null) return redirect()->back()->with('error', 'ID Invalid.');

        // 1. Ambil Data Eksisting (Handle Array/Object Return)
        $existingRpp = $this->rppModel->find($id);
        if (!$existingRpp) return redirect()->back()->with('error', 'Data tidak ditemukan.');
        
        // Konversi ke array jika object
        $existingArray = is_object($existingRpp) ? (array) $existingRpp : $existingRpp;

        // 2. Ambil Data Input
        $postData = $this->request->getPost();
        
        // Normalisasi Input (Flat vs Nested Array)
        // Form view menggunakan names seperti 'topik' saat edit (Flat)
        $inputData = [];
        if (isset($postData['items']) && is_array($postData['items'])) {
            $inputData = reset($postData['items']); 
        } else {
            $inputData = $postData; 
        }
        
        // 3. Ambil Silabus Induk (dari hidden input atau existing)
        $silabusId = !empty($inputData['silabus_id']) ? $inputData['silabus_id'] : ($existingArray['silabus_id'] ?? null);
        
        if (!$silabusId) {
             return redirect()->back()->withInput()->with('error', 'ID Silabus hilang. Mohon refresh halaman.');
        }

        $silabus = $this->silabusModel->find($silabusId);
        if (!$silabus) {
             return redirect()->back()->withInput()->with('error', 'Data Silabus Induk tidak ditemukan.');
        }

        // 4. Merge Data Lama dengan Input Baru
        $mergedData = array_merge($existingArray, $inputData);
        
        // 5. Mapping Data untuk Update
        $updateData = $this->mapRppData($mergedData, $silabus);
        
        // Bersihkan field sistem CI4 dan field yang tidak perlu
        unset(
            $updateData['id'], 
            $updateData['_method'], 
            $updateData['csrf_test_name'], 
            $updateData['items'],
            $updateData['updated_at'] // Biarkan model yang set ini (useTimestamps = true)
        );

        // 6. Eksekusi Update
        // Proteksi: Pastikan array tidak kosong sebelum update
        if (empty($updateData)) {
             return redirect()->back()->withInput()->with('error', 'Tidak ada data valid untuk disimpan.');
        }

        if ($this->rppModel->update($id, $updateData)) {
            return redirect()->to(base_url('app/pembelajaran/rpp'))->with('message', 'Perubahan RPP berhasil disimpan.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui database.');
        }
    }

    public function delete($id = null)
    {
        if ($id === null) return redirect()->back();
        $this->rppModel->delete($id);
        return redirect()->back()->with('message', 'RPP berhasil dihapus.');
    }

    // ... (method show, print, generate tetap sama)

    public function show($id)
    {
        $rpp = $this->rppModel->find($id);
        if (!$rpp) return redirect()->back()->with('error', 'Data tidak ditemukan.');
        
        $silabus = $this->silabusModel->find($rpp['silabus_id']);
        if (!empty($silabus)) {
             $db = \Config\Database::connect();
             $mapel = $db->table('mata_pelajaran')->where('id', $silabus['mata_pelajaran_id'])->get()->getRowArray();
             $rpp['nama_mapel'] = $mapel['nama_mapel'] ?? '-';
        }
        
        return view('pembelajaran/rpp/detail', [
            'title'   => 'Detail RPP',
            'rpp'     => $rpp,
            'silabus' => $silabus
        ]);
    }
    
    public function print($id) { return $this->show($id); }
    public function generate() { return redirect()->back()->with('info', 'Fitur generate sedang dalam perbaikan.'); }
    public function generateMassal() { return $this->generate(); }

    private function mapRppData($input, $silabus)
    {
        if (!$silabus) return [];

        return [
            'kode_jenjang'        => $silabus['kode_jenjang'],
            'jenis_kurikulum'     => $silabus['jenis_kurikulum'] ?? 'Merdeka',
            'silabus_id'          => $silabus['id'],
            'tema'                => $input['tema'] ?? $silabus['tema'] ?? null,
            'subtema'             => $input['subtema'] ?? $silabus['subtema'] ?? null,
            'fase'                => $silabus['fase'] ?? null,
            'pertemuan_ke'        => $input['pertemuan_ke'] ?? 1,
            'topik'               => $input['topik'] ?? ($silabus['materi_pokok'] ?? 'Topik Baru'), 
            'tujuan_pembelajaran' => $input['tujuan_pembelajaran'] ?? '',
            'metode_pembelajaran' => $input['metode_pembelajaran'] ?? '',
            'langkah_pembelajaran'=> $input['langkah_pembelajaran'] ?? '',
            'pemahaman_bermakna'  => $input['pemahaman_bermakna'] ?? null,
            'pertanyaan_pemantik' => $input['pertanyaan_pemantik'] ?? null,
            'media_alat'          => $input['media_alat'] ?? null,
            'penilaian'           => $input['penilaian'] ?? null,
        ];
    }
}