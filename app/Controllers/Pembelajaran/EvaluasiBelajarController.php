<?php

namespace App\Controllers\Pembelajaran;

use App\Controllers\BaseController;
use App\Models\Pembelajaran\EvaluasiBelajarModel;
use App\Models\Pembelajaran\SilabusModel;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class EvaluasiBelajarController extends BaseController
{
    use ResponseTrait;

    protected $evaluasiModel;
    protected $silabusModel;
    protected $userModel;

    public function __construct()
    {
        $this->evaluasiModel = new EvaluasiBelajarModel();
        $this->silabusModel  = new SilabusModel();
        $this->userModel     = new UserModel();
    }

    public function index()
    {
        $user = $this->userModel->find(session()->get('user_id'));
        if (!$user) return redirect()->to(base_url('login'));

        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');
        $isRestricted = ($userUnit !== 'GLOBAL');
        $kodeJenjang = $isRestricted ? $userUnit : $this->request->getGet('kode_jenjang');
        
        $keyword = $this->request->getGet('keyword');

        $this->evaluasiModel->orderBy('created_at', 'DESC');
        
        if ($keyword) {
            $this->evaluasiModel->like('judul_evaluasi', $keyword);
        }
        
        if (!empty($kodeJenjang)) {
            $this->evaluasiModel->where('kode_jenjang', $kodeJenjang);
        }

        $dataEvaluasi = $this->evaluasiModel->getFilteredData($keyword, $kodeJenjang)->paginate(10, 'evaluasi');

        return view('pembelajaran/evaluasi_belajar/index', [
            'title'          => 'Manajemen Evaluasi Belajar',
            'evaluasi'       => $dataEvaluasi,
            'pager'          => $this->evaluasiModel->pager,
            'stats'          => method_exists($this->evaluasiModel, 'getStatistics') ? $this->evaluasiModel->getStatistics($isRestricted ? $userUnit : null) : [],
            'filter_jenjang' => $kodeJenjang,
            'is_restricted'  => $isRestricted,
            'user_role'      => session()->get('role_name') ?? 'user'
        ]);
    }

    public function show($id)
    {
        $data = $this->evaluasiModel->getFilteredData()->find($id);
        if (!$data) return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar'))->with('error', 'Data tidak ditemukan.');

        return view('pembelajaran/evaluasi_belajar/detail', [
            'title'    => 'Detail Evaluasi',
            'evaluasi' => $data
        ]);
    }

    public function new()
    {
        $user = $this->userModel->find(session()->get('user_id'));
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');

        // [FIX UTAMA]: Tambahkan Select & Join agar kode_mapel tersedia di View
        $silabusQuery = $this->silabusModel
            ->select('pembelajaran_silabus.*, mata_pelajaran.kode_mapel, mata_pelajaran.nama_mapel')
            ->join('mata_pelajaran', 'mata_pelajaran.id = pembelajaran_silabus.mata_pelajaran_id', 'left');

        if ($userUnit !== 'GLOBAL') {
            $silabusQuery->where('pembelajaran_silabus.kode_jenjang', $userUnit);
        }

        return view('pembelajaran/evaluasi_belajar/form', [
            'title'    => 'Tambah Evaluasi Baru',
            'evaluasi' => null,
            'silabus'  => $silabusQuery->findAll()
        ]);
    }

    public function create()
    {
        if (!$this->validate([
            'judul_evaluasi' => 'required', 
            'silabus_id'     => 'required',
            'jenis_evaluasi' => 'required',
            'tanggal_mulai'  => 'required'
        ])) {
            return redirect()->back()->withInput()->with('error', 'Mohon lengkapi data wajib.');
        }

        $silabus = $this->silabusModel->find($this->request->getPost('silabus_id'));
        
        $saveData = $this->request->getPost();
        $saveData['kode_jenjang']      = $silabus['kode_jenjang'] ?? 'GLOBAL';
        $saveData['mata_pelajaran_id'] = $silabus['mata_pelajaran_id'] ?? null;
        $saveData['created_by']        = session()->get('user_id');

        $this->evaluasiModel->insert($saveData);
        return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar'))->with('message', 'Evaluasi berhasil dijadwalkan.');
    }

    public function edit($id)
    {
        $data = $this->evaluasiModel->find($id);
        if (!$data) return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar'))->with('error', 'Data tidak ditemukan.');

        $user = $this->userModel->find(session()->get('user_id'));
        $userUnit = strtoupper($user['kode_jenjang'] ?? 'GLOBAL');

        if ($userUnit !== 'GLOBAL' && strtoupper($data['kode_jenjang']) !== $userUnit) {
            return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar'))->with('error', 'Izin Ditolak.');
        }

        // [FIX UTAMA]: Tambahkan Select & Join agar kode_mapel tersedia di View
        $silabusQuery = $this->silabusModel
            ->select('pembelajaran_silabus.*, mata_pelajaran.kode_mapel, mata_pelajaran.nama_mapel')
            ->join('mata_pelajaran', 'mata_pelajaran.id = pembelajaran_silabus.mata_pelajaran_id', 'left');

        if ($userUnit !== 'GLOBAL') {
            $silabusQuery->where('pembelajaran_silabus.kode_jenjang', $userUnit);
        }

        return view('pembelajaran/evaluasi_belajar/form', [
            'title'    => 'Edit Evaluasi',
            'evaluasi' => $data,
            'silabus'  => $silabusQuery->findAll()
        ]);
    }

    public function update($id)
    {
        if (strtolower($this->request->getMethod()) === 'get') {
            return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar/edit/' . $id));
        }

        $data = $this->evaluasiModel->find($id);
        if (!$data) return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar'))->with('error', 'Data tidak ditemukan.');

        $updateData = $this->request->getPost();
        unset($updateData['_method']); 

        $this->evaluasiModel->update($id, $updateData);
        return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar'))->with('message', 'Evaluasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $data = $this->evaluasiModel->find($id);
        if (!$data) return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar'))->with('error', 'Data tidak ditemukan.');
        
        $this->evaluasiModel->delete($id);
        return redirect()->to(base_url('app/pembelajaran/evaluasi-belajar'))->with('message', 'Evaluasi telah dihapus.');
    }
}