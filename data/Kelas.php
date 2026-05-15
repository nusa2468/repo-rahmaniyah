<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\GuruModel;
use App\Models\TahunAjaranModel;

class Kelas extends BaseController
{
    protected $kelasModel;
    protected $guruModel;
    protected $tahunAjaranModel;

    public function __construct()
    {
        $this->kelasModel = new KelasModel();
        $this->guruModel = new GuruModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
        helper('form');
    }

    public function index()
    {
        $data = [
            'title'          => 'Master Data - Kelas',
            'current_module' => 'master_data',
            'kelas'          => $this->kelasModel->getKelasDetail(),
        ];
        return view('kelas/index', $data);
    }

    public function show($id = null)
    {
        return redirect()->to('app/kelas/edit/' . $id);
    }

    public function new()
    {
        $data = [
            'title'          => 'Tambah Kelas Baru',
            'current_module' => 'master_data',
            'guru'           => $this->guruModel->findAll(),
            'tahun_ajaran'   => $this->tahunAjaranModel->findAll(),
        ];
        return view('kelas/form', $data);
    }

    public function create()
    {
        if ($this->kelasModel->save($this->request->getPost())) {
            return redirect()->to('app/kelas')->with('success', 'Data kelas berhasil ditambahkan.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->kelasModel->errors());
        }
    }

    public function edit($id = null)
    {
        $kelas = $this->kelasModel->find($id);
        if (!$kelas) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $data = [
            'title'          => 'Edit Data Kelas',
            'current_module' => 'master_data',
            'kelas'          => $kelas,
            'guru'           => $this->guruModel->findAll(),
            'tahun_ajaran'   => $this->tahunAjaranModel->findAll(),
        ];
        return view('kelas/form', $data);
    }

    public function update($id = null)
    {
        if ($this->kelasModel->update($id, $this->request->getPost())) {
            return redirect()->to('app/kelas')->with('success', 'Data kelas berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->kelasModel->errors());
        }
    }

    public function delete($id = null)
    {
        if ($this->kelasModel->delete($id)) {
            return redirect()->to('app/kelas')->with('success', 'Data kelas berhasil dihapus.');
        } else {
            return redirect()->to('app/kelas')->with('error', 'Gagal menghapus data kelas.');
        }
    }
}
