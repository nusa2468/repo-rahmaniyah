<?php

namespace App\Controllers\Pembelajaran;

use App\Controllers\BaseController;
use App\Models\Pembelajaran\BankSoalModel;
use App\Models\Pembelajaran\SilabusModel;
use App\Models\Pembelajaran\BahanAjarModel; // Diperlukan untuk generate massal
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class BankSoalController extends BaseController
{
    use ResponseTrait;

    protected $bankSoalModel;
    protected $silabusModel;
    protected $bahanAjarModel;
    protected $userModel;

    public function __construct()
    {
        // Load model dengan pengecekan aman
        if (class_exists(BankSoalModel::class)) $this->bankSoalModel = new BankSoalModel();
        if (class_exists(SilabusModel::class)) $this->silabusModel = new SilabusModel();
        if (class_exists(BahanAjarModel::class)) $this->bahanAjarModel = new BahanAjarModel();
        if (class_exists(UserModel::class)) $this->userModel = new UserModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        if (!$userId) return redirect()->to(base_url('login'));

        $user = $this->userModel->getPenggunaWithRole($userId);
        if (!$user) {
            $user = $this->userModel->find($userId);
            $user['role_name'] = session()->get('role');
        }

        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        $isRestricted = ($userUnit !== 'GLOBAL');
        
        $kodeJenjang = $isRestricted ? $userUnit : $this->request->getGet('kode_jenjang');
        $keyword = $this->request->getGet('keyword');

        $query = $this->bankSoalModel->orderBy('created_at', 'DESC');
        
        if (method_exists($this->bankSoalModel, 'getFilteredData')) {
            $dataSoal = $query->getFilteredData($keyword, $kodeJenjang)->paginate(10, 'bank_soal');
        } else {
            if ($kodeJenjang && $kodeJenjang !== 'ALL') $query->where('kode_jenjang', $kodeJenjang);
            if ($keyword) $query->groupStart()->like('pertanyaan', $keyword)->orLike('topik', $keyword)->groupEnd();
            $dataSoal = $query->paginate(10, 'bank_soal');
        }

        // Statistik Fallback
        $stats = method_exists($this->bankSoalModel, 'getStatistics') 
            ? $this->bankSoalModel->getStatistics($isRestricted ? $userUnit : null)
            : ['total' => $this->bankSoalModel->countAllResults()];

        return view('pembelajaran/bank_soal/index', [
            'title'          => 'Manajemen Bank Soal',
            'bank_soal'      => $dataSoal,
            'pager'          => $this->bankSoalModel->pager,
            'stats'          => $stats,
            'filter_jenjang' => $kodeJenjang,
            'is_restricted'  => $isRestricted,
            'user_role'      => $user['role_name'] ?? 'User'
        ]);
    }

    public function show($id)
    {
        $soal = $this->bankSoalModel->find($id);
        if (!$soal) return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('error', 'Data soal tidak ditemukan.');

        $user = $this->userModel->find(session()->get('user_id'));
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');

        if ($userUnit !== 'GLOBAL' && strtoupper($soal['kode_jenjang']) !== $userUnit) {
            return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('error', 'Akses Ditolak.');
        }

        return view('pembelajaran/bank_soal/detail', [
            'title' => 'Detail Soal',
            'soal'  => $soal
        ]);
    }

    public function new()
    {
        $user = $this->userModel->find(session()->get('user_id'));
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');

        $silabusQuery = $this->silabusModel;
        if ($userUnit !== 'GLOBAL') $silabusQuery->where('kode_jenjang', $userUnit);

        return view('pembelajaran/bank_soal/form', [
            'title'   => 'Tambah Soal Baru',
            'soal'    => null,
            'silabus' => $silabusQuery->findAll()
        ]);
    }

    // --- LOGIKA SIMPAN SATUAN (OLD) ---
    public function create()
    {
        if (!$this->validate(['kode_soal' => 'required', 'pertanyaan' => 'required'])) {
            return redirect()->back()->withInput()->with('error', 'Mohon lengkapi data wajib (Kode & Pertanyaan).');
        }

        $silabusId = $this->request->getPost('silabus_id');
        $silabus = $this->silabusModel->find($silabusId);

        if (!$silabus) {
            return redirect()->back()->withInput()->with('error', 'Referensi Silabus tidak valid.');
        }
        
        $saveData = [
            'kode_jenjang'      => $silabus['kode_jenjang'],
            'jenis_kurikulum'   => $silabus['jenis_kurikulum'],
            'fase'              => $silabus['fase'] ?? null,
            'mata_pelajaran_id' => $silabus['mata_pelajaran_id'],
            'silabus_id'        => $silabusId,
            'kode_soal'         => $this->request->getPost('kode_soal'),
            'topik'             => $this->request->getPost('topik'),
            'jenis_soal'        => $this->request->getPost('jenis_soal'),
            'tingkat_kesulitan' => $this->request->getPost('tingkat_kesulitan'),
            'level_kognitif'    => $this->request->getPost('level_kognitif'),
            'pertanyaan'        => $this->request->getPost('pertanyaan'),
            'kunci_jawaban'     => $this->request->getPost('kunci_jawaban'),
            'bobot'             => $this->request->getPost('bobot') ?? 1,
            'opsi_jawaban'      => json_encode($this->request->getPost('opsi') ?? []),
            'created_at'        => date('Y-m-d H:i:s')
        ];

        $this->bankSoalModel->insert($saveData);
        return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('message', 'Soal berhasil ditambahkan.');
    }

    // --- LOGIKA SIMPAN BANYAK (BULK INSERT) ---
    public function saveBulk()
    {
        // 1. Ambil Input Global
        $globalSilabusId = $this->request->getPost('global_silabus_id');
        $globalJenis = $this->request->getPost('global_jenis_soal');
        $globalKesulitan = $this->request->getPost('global_tingkat_kesulitan');
        $soalList = $this->request->getPost('soal');

        // 2. Validasi Dasar
        if (!$globalSilabusId) {
            return redirect()->back()->withInput()->with('error', 'Silabus (Materi) wajib dipilih untuk semua soal.');
        }
        if (empty($soalList) || !is_array($soalList)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada data soal yang dikirim.');
        }

        // 3. Ambil Data Silabus (Parent Data)
        // Kita butuh data ini untuk mengisi kolom jenjang, mapel, kurikulum, dll.
        $silabus = $this->silabusModel->find($globalSilabusId);
        if (!$silabus) {
            return redirect()->back()->withInput()->with('error', 'Data Silabus yang dipilih tidak valid.');
        }

        $batchData = [];
        $timestamp = date('Y-m-d H:i:s');
        $count = 0;

        // 4. Loop Data Soal
        foreach ($soalList as $idx => $item) {
            // Skip jika pertanyaan kosong/hanya spasi
            if (empty(trim($item['pertanyaan'] ?? ''))) continue;

            // Handle Opsi (Array -> JSON)
            $opsiJson = '{}';
            if (isset($item['opsi']) && is_array($item['opsi'])) {
                // Filter opsi kosong
                $cleanOpsi = array_filter($item['opsi'], function($v) { return !empty(trim($v)); });
                $opsiJson = json_encode($cleanOpsi);
            }

            // Generate Kode Soal jika kosong
            $kodeSoal = !empty($item['kode_soal']) ? $item['kode_soal'] : 'Q-BLK-' . strtoupper(substr(uniqid(), -5));

            $batchData[] = [
                // Data Turunan dari Silabus
                'kode_jenjang'      => $silabus['kode_jenjang'],
                'jenis_kurikulum'   => $silabus['jenis_kurikulum'],
                'fase'              => $silabus['fase'] ?? null,
                'mata_pelajaran_id' => $silabus['mata_pelajaran_id'],
                'silabus_id'        => $silabus['id'],

                // Data Form Global
                'jenis_soal'        => $globalJenis,
                'tingkat_kesulitan' => $globalKesulitan,
                'level_kognitif'    => 'L1', // Default L1
                'bobot'             => 1,    // Default bobot 1

                // Data Per Item
                'kode_soal'         => $kodeSoal,
                'pertanyaan'        => $item['pertanyaan'],
                'kunci_jawaban'     => $item['kunci'] ?? '',
                'opsi_jawaban'      => $opsiJson,
                'topik'             => $silabus['materi_pokok'] ?? 'Umum', // Topik default ambil nama materi
                
                'created_at'        => $timestamp
            ];
            $count++;
        }

        // 5. Eksekusi Insert Batch
        if ($count > 0) {
            $this->bankSoalModel->insertBatch($batchData);
            return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('message', "Berhasil menyimpan $count soal sekaligus.");
        }

        return redirect()->back()->withInput()->with('warning', 'Tidak ada data soal valid yang tersimpan. Pastikan kolom pertanyaan terisi.');
    }

    public function edit($id)
    {
        $soal = $this->bankSoalModel->find($id);
        if (!$soal) return redirect()->back()->with('error', 'Data tidak ditemukan');

        $user = $this->userModel->find(session()->get('user_id'));
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');

        if ($userUnit !== 'GLOBAL' && strtoupper($soal['kode_jenjang']) !== $userUnit) {
            return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('error', 'Akses Ditolak.');
        }

        $silabusQuery = $this->silabusModel;
        if ($userUnit !== 'GLOBAL') $silabusQuery->where('kode_jenjang', $userUnit);

        return view('pembelajaran/bank_soal/form', [
            'title'   => 'Edit Soal',
            'soal'    => $soal,
            'silabus' => $silabusQuery->findAll()
        ]);
    }

    public function update($id = null)
    {
        if ($id === null) return redirect()->back()->with('error', 'ID tidak valid.');

        // Fallback untuk method GET (jika diakses via URL)
        if ($this->request->getMethod() === 'get') {
            return redirect()->to(base_url('app/pembelajaran/bank-soal/edit/' . $id));
        }

        $updateData = $this->request->getPost();
        
        // Bersihkan data internal CI4 agar tidak error di database
        unset($updateData['_method'], $updateData['csrf_test_name']);

        // Handle Opsi Jawaban (Array to JSON)
        $opsi = $this->request->getPost('opsi');
        if ($opsi) {
            $updateData['opsi_jawaban'] = json_encode($opsi);
        }
        // Hapus 'opsi' dari array utama karena kolomnya 'opsi_jawaban'
        unset($updateData['opsi']);

        if (empty($updateData)) {
            return redirect()->back()->with('warning', 'Tidak ada perubahan data.');
        }

        $this->bankSoalModel->update($id, $updateData);
        return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('message', 'Soal berhasil diperbarui.');
    }

    public function delete($id = null)
    {
        $soal = $this->bankSoalModel->find($id);
        if (!$soal) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $user = $this->userModel->find(session()->get('user_id'));
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');

        if ($userUnit !== 'GLOBAL' && strtoupper($soal['kode_jenjang']) !== $userUnit) {
            return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('error', 'Akses Ditolak.');
        }

        $this->bankSoalModel->delete($id);
        return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('message', 'Soal berhasil dihapus.');
    }

    /**
     * GENERATE BANK SOAL MASSAL
     * Membuat draf soal berdasarkan Bahan Ajar yang ada.
     */
    public function generateMassal()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');

        // 1. Ambil Bahan Ajar (Join dengan RPP untuk dapat silabus_id)
        $bahanQuery = $this->bahanAjarModel->select('pembelajaran_bahan_ajar.*, pembelajaran_rpp.silabus_id')
                                           ->join('pembelajaran_rpp', 'pembelajaran_rpp.id = pembelajaran_bahan_ajar.rpp_id', 'left');
        
        if ($userUnit !== 'GLOBAL') {
            $bahanQuery->where('pembelajaran_bahan_ajar.kode_jenjang', $userUnit);
        }
        $allBahan = $bahanQuery->findAll();

        $count = 0;
        foreach ($allBahan as $bahan) {
            // 2. Cek Duplikasi (Berdasarkan kemiripan Topik)
            $exists = $this->bankSoalModel->like('topik', $bahan['judul_bahan'])->first();

            if (!$exists) {
                // 3. Buat Draf Soal
                $data = [
                    'kode_jenjang'      => $bahan['kode_jenjang'],
                    'silabus_id'        => $bahan['silabus_id'] ?? null,
                    'mata_pelajaran_id' => $bahan['mata_pelajaran_id'],
                    'kode_soal'         => 'Q-' . strtoupper(substr(md5(uniqid()), 0, 5)), // Kode Unik Random
                    'topik'             => $bahan['judul_bahan'], // Topik diambil dari judul bahan ajar
                    'jenis_soal'        => 'PG', // Default Pilihan Ganda
                    'tingkat_kesulitan' => 'Sedang',
                    'level_kognitif'    => 'L1',
                    'pertanyaan'        => '<p>Buat pertanyaan berdasarkan materi: <strong>' . $bahan['judul_bahan'] . '</strong></p>',
                    'kunci_jawaban'     => 'A',
                    'opsi_jawaban'      => json_encode(['A' => 'Opsi A', 'B' => 'Opsi B', 'C' => 'Opsi C', 'D' => 'Opsi D']),
                    'created_at'        => date('Y-m-d H:i:s')
                ];
                $this->bankSoalModel->insert($data);
                $count++;
            }
        }

        if ($count > 0) {
            return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('message', "Berhasil! $count draf soal berhasil digenerate dari Bahan Ajar.");
        } else {
            return redirect()->to(base_url('app/pembelajaran/bank-soal'))->with('info', "Tidak ada bahan ajar baru untuk digenerate.");
        }
    }
}