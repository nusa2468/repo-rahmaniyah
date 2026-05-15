<?php

namespace App\Controllers\Pembelajaran;

use App\Controllers\BaseController;
use App\Models\Pembelajaran\BahanAjarModel;
use App\Models\Pembelajaran\RppModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class BahanAjarController extends BaseController
{
    use ResponseTrait;

    protected $bahanAjarModel;
    protected $rppModel;
    protected $userModel;

    public function __construct()
    {
        if (class_exists(BahanAjarModel::class)) $this->bahanAjarModel = new BahanAjarModel();
        if (class_exists(RppModel::class)) $this->rppModel = new RppModel();
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

        $query = $this->bahanAjarModel->orderBy('created_at', 'DESC');
        
        if (method_exists($this->bahanAjarModel, 'getFilteredData')) {
            $dataBahan = $query->getFilteredData($keyword, $kodeJenjang)->paginate(10, 'bahan');
        } else {
            if ($kodeJenjang && $kodeJenjang !== 'ALL') {
                $query->where('kode_jenjang', $kodeJenjang);
            }
            if ($keyword) {
                $query->like('judul_bahan', $keyword);
            }
            $dataBahan = $query->paginate(10, 'bahan');
        }

        return view('pembelajaran/bahan_ajar/index', [
            'title'          => 'Manajemen Bahan Ajar',
            'bahan_ajar'     => $dataBahan,
            'pager'          => $this->bahanAjarModel->pager,
            'stats'          => ['total' => $this->bahanAjarModel->countAllResults()],
            'filter_jenjang' => $kodeJenjang,
            'is_restricted'  => $isRestricted,
            'user_role'      => $user['role_name'] ?? 'User'
        ]);
    }

    // ... (Method create, new, edit, update, delete, show tetap sama seperti sebelumnya) ...

    public function create()
    {
        if (!$this->validate(['judul_bahan' => 'required', 'rpp_id' => 'required'])) {
            return redirect()->back()->withInput()->with('error', 'Data wajib (Judul & RPP) belum lengkap.');
        }

        // FIXED: Join ke Silabus untuk ambil mata_pelajaran_id
        $rpp = $this->rppModel->select('pembelajaran_rpp.*, pembelajaran_silabus.mata_pelajaran_id')
            ->join('pembelajaran_silabus', 'pembelajaran_silabus.id = pembelajaran_rpp.silabus_id')
            ->find($this->request->getPost('rpp_id'));
        
        if (!$rpp) {
            return redirect()->back()->with('error', 'Data RPP tidak valid.');
        }
        
        $file = $this->request->getFile('file_path');
        $filePath = '';
        $jenisFile = $this->request->getPost('jenis_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/materi', $newName);
            $filePath = 'uploads/materi/' . $newName;

            if (empty($jenisFile)) {
                $ext = strtolower($file->getExtension());
                $jenisFile = match (true) {
                    in_array($ext, ['pdf']) => 'PDF',
                    in_array($ext, ['ppt', 'pptx']) => 'PPT',
                    in_array($ext, ['mp4', 'avi']) => 'Video',
                    default => 'Doc',
                };
            }
        } else {
            $filePath = $this->request->getPost('file_path');
        }

        $saveData = [
            'kode_jenjang'      => $rpp['kode_jenjang'] ?? 'GLOBAL',
            'rpp_id'            => $this->request->getPost('rpp_id'),
            'mata_pelajaran_id' => $rpp['mata_pelajaran_id'] ?? 0, // Ambil dari hasil join
            'judul_bahan'       => $this->request->getPost('judul_bahan'),
            'jenis_file'        => $jenisFile,
            'file_path'         => $filePath,
            'deskripsi'         => $this->request->getPost('deskripsi'),
            'status'            => 'Final',
            'created_by'        => session()->get('user_id')
        ];

        $this->bahanAjarModel->insert($saveData);
        return redirect()->to(base_url('app/pembelajaran/bahan-ajar'))->with('message', 'Bahan ajar berhasil ditambahkan.');
    }

    public function update($id = null)
    {
        if ($id === null) return redirect()->back()->with('error', 'ID tidak valid.');

        $data = $this->request->getPost();
        
        $file = $this->request->getFile('file_path');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/materi', $newName);
            $data['file_path'] = 'uploads/materi/' . $newName;
            
            $ext = strtolower($file->getExtension());
            if (empty($data['jenis_file'])) {
                $data['jenis_file'] = match (true) {
                    in_array($ext, ['pdf']) => 'PDF',
                    in_array($ext, ['ppt', 'pptx']) => 'PPT',
                    in_array($ext, ['mp4', 'avi']) => 'Video',
                    default => 'Doc',
                };
            }
        }

        unset($data['_method'], $data['csrf_test_name']);

        if (empty($data)) {
            return redirect()->back()->with('warning', 'Tidak ada perubahan data.');
        }

        $this->bahanAjarModel->update($id, $data);
        return redirect()->to(base_url('app/pembelajaran/bahan-ajar'))->with('message', 'Data berhasil diperbarui.');
    }

    public function delete($id = null)
    {
        $this->bahanAjarModel->delete($id);
        return redirect()->to(base_url('app/pembelajaran/bahan-ajar'))->with('message', 'Data berhasil dihapus.');
    }

    /**
     * GENERATE BAHAN AJAR (SINGLE)
     */
    public function generate($rppId = null)
    {
        if (!$rppId) return redirect()->back()->with('error', 'ID RPP tidak valid.');

        $exists = $this->bahanAjarModel->where('rpp_id', $rppId)->first();
        if ($exists) {
            return redirect()->to(base_url('app/pembelajaran/bahan-ajar/edit/' . $exists['id']))
                             ->with('info', 'Bahan ajar sudah ada. Diarahkan ke edit.');
        }

        // FIXED: Join Silabus untuk ambil mata_pelajaran_id
        $rpp = $this->rppModel->select('pembelajaran_rpp.*, pembelajaran_silabus.mata_pelajaran_id')
            ->join('pembelajaran_silabus', 'pembelajaran_silabus.id = pembelajaran_rpp.silabus_id')
            ->find($rppId);
            
        if (!$rpp) return redirect()->back()->with('error', 'Data RPP tidak ditemukan.');

        $data = [
            'kode_jenjang'      => $rpp['kode_jenjang'],
            'rpp_id'            => $rpp['id'],
            'mata_pelajaran_id' => $rpp['mata_pelajaran_id'], // Ambil dari join
            'judul_bahan'       => 'Materi: ' . $rpp['topik'],
            'jenis_file'        => 'PDF',
            'file_path'         => '',
            'deskripsi'         => 'Draf materi otomatis dari RPP: ' . $rpp['topik'],
            'status'            => 'Draft',
            'created_by'        => session()->get('user_id'),
            'created_at'        => date('Y-m-d H:i:s')
        ];

        $this->bahanAjarModel->insert($data);
        $newId = $this->bahanAjarModel->getInsertID();

        return redirect()->to(base_url('app/pembelajaran/bahan-ajar/edit/' . $newId))
                         ->with('message', 'Draf Bahan Ajar berhasil dibuat.');
    }

    /**
     * GENERATE BAHAN AJAR MASSAL (Dari RPP ke Bahan Ajar)
     */
    public function generateMassal()
    {
        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');

        // 1. Ambil RPP sesuai unit user, JOIN dengan Silabus untuk dapat mapel ID
        // FIXED: Select kolom spesifik dan JOIN
        $rppQuery = $this->rppModel->select('pembelajaran_rpp.id, pembelajaran_rpp.kode_jenjang, pembelajaran_rpp.topik, pembelajaran_silabus.mata_pelajaran_id')
                                   ->join('pembelajaran_silabus', 'pembelajaran_silabus.id = pembelajaran_rpp.silabus_id');
                                   
        if ($userUnit !== 'GLOBAL') {
            $rppQuery->where('pembelajaran_rpp.kode_jenjang', $userUnit);
        }
        $allRpp = $rppQuery->findAll();
        
        $count = 0;
        foreach ($allRpp as $rpp) {
            // 2. Cek Duplikasi: Apakah RPP ini sudah ada di tabel bahan_ajar?
            $exists = $this->bahanAjarModel->where('rpp_id', $rpp['id'])->first();
            
            if (!$exists) {
                // 3. Buat Draf Bahan Ajar
                $this->bahanAjarModel->insert([
                    'kode_jenjang'      => $rpp['kode_jenjang'],
                    'rpp_id'            => $rpp['id'],
                    'mata_pelajaran_id' => $rpp['mata_pelajaran_id'] ?? 0, // Ambil dari join
                    'judul_bahan'       => 'Materi: ' . $rpp['topik'],
                    'jenis_file'        => 'PDF', // Default
                    'deskripsi'         => 'Draf otomatis dari RPP',
                    'status'            => 'Draft',
                    'created_by'        => $userId,
                    'created_at'        => date('Y-m-d H:i:s')
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            return redirect()->to(base_url('app/pembelajaran/rpp'))->with('message', "Berhasil! $count draf Bahan Ajar dibuat dari RPP yang belum memiliki materi.");
        } else {
            return redirect()->to(base_url('app/pembelajaran/rpp'))->with('info', "Semua RPP sudah memiliki Bahan Ajar. Tidak ada data baru yang dibuat.");
        }
    }

    // Helper views
    public function new() {
        return view('pembelajaran/bahan_ajar/form', ['title' => 'Tambah', 'bahan' => null, 'rpp' => $this->rppModel->findAll()]);
    }
    public function edit($id) {
        $bahan = $this->bahanAjarModel->find($id);
        return view('pembelajaran/bahan_ajar/form', ['title' => 'Edit', 'bahan' => $bahan, 'rpp' => $this->rppModel->findAll()]);
    }
}