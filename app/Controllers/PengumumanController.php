<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PengumumanModel;

class PengumumanController extends BaseController
{
    protected $pengumumanModel;

    public function __construct()
    {
        $this->pengumumanModel = new PengumumanModel();
        helper('form');
    }

    public function index()
    {
        $data = [
            'title'          => 'Manajemen Pengumuman',
            'current_module' => 'humas',
            'pengumuman'     => $this->pengumumanModel->orderBy('created_at', 'DESC')->findAll(),
        ];
        return view('humas/pengumuman/index', $data);
    }

    public function new()
    {
        $data = [
            'title'          => 'Tulis Pengumuman Baru',
            'current_module' => 'humas',
        ];
        return view('humas/pengumuman/form', $data);
    }

    public function create()
    {
        $data = $this->request->getPost();
        $data['id_penulis'] = session()->get('user_id');

        if ($this->pengumumanModel->save($data)) {
            return redirect()->to('app/pengumuman')->with('success', 'Pengumuman berhasil dipublikasikan.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->pengumumanModel->errors());
        }
    }

    public function edit($id = null)
    {
        $pengumuman = $this->pengumumanModel->find($id);
        if (!$pengumuman) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'          => 'Edit Pengumuman',
            'current_module' => 'humas',
            'pengumuman'     => $pengumuman,
        ];
        return view('humas/pengumuman/form', $data);
    }

    public function update($id = null)
    {
        $data = $this->request->getPost();

        if ($this->pengumumanModel->update($id, $data)) {
            return redirect()->to('app/pengumuman')->with('success', 'Pengumuman berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->pengumumanModel->errors());
        }
    }

    public function delete($id = null)
    {
        if ($this->pengumumanModel->delete($id)) {
            return redirect()->to('app/pengumuman')->with('success', 'Pengumuman berhasil dihapus.');
        } else {
            return redirect()->to('app/pengumuman')->with('error', 'Gagal menghapus pengumuman.');
        }
    }
}

