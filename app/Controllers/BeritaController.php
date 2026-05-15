<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BeritaModel;

class BeritaController extends BaseController
{
    protected $beritaModel;

    public function __construct()
    {
        $this->beritaModel = new BeritaModel();
        helper('form');
    }

    public function index()
    {
        $data = [
            'title'          => 'Manajemen Berita',
            'current_module' => 'humas',
            'berita'         => $this->beritaModel->orderBy('created_at', 'DESC')->findAll(),
        ];
        return view('humas/berita/index', $data);
    }

    public function new()
    {
        $data = [
            'title'          => 'Tulis Berita Baru',
            'current_module' => 'humas',
        ];
        return view('humas/berita/form', $data);
    }

    public function create()
    {
        $data = $this->request->getPost();
        
        // Slug generation
        $slug = url_title($this->request->getPost('judul'), '-', true);
        $data['slug'] = $slug . '-' . time();

        // File upload handling
        $gambarFile = $this->request->getFile('gambar');
        if ($gambarFile->isValid() && !$gambarFile->hasMoved()) {
            $newName = $gambarFile->getRandomName();
            $gambarFile->move('uploads/berita', $newName);
            $data['gambar'] = $newName;
        }

        $data['id_penulis'] = session()->get('user_id');

        if ($this->beritaModel->save($data)) {
            return redirect()->to('app/berita')->with('success', 'Berita berhasil dipublikasikan.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->beritaModel->errors());
        }
    }

    public function edit($id = null)
    {
        $berita = $this->beritaModel->find($id);
        if (!$berita) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'          => 'Edit Berita',
            'current_module' => 'humas',
            'berita'         => $berita,
        ];
        return view('humas/berita/form', $data);
    }

    public function update($id = null)
    {
        $data = $this->request->getPost();

        // File upload handling
        $gambarFile = $this->request->getFile('gambar');
        if ($gambarFile && $gambarFile->isValid() && !$gambarFile->hasMoved()) {
            $newName = $gambarFile->getRandomName();
            $gambarFile->move('uploads/berita', $newName);
            $data['gambar'] = $newName;
        }

        if ($this->beritaModel->update($id, $data)) {
            return redirect()->to('app/berita')->with('success', 'Berita berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->beritaModel->errors());
        }
    }

    public function delete($id = null)
    {
        if ($this->beritaModel->delete($id)) {
            return redirect()->to('app/berita')->with('success', 'Berita berhasil dihapus.');
        } else {
            return redirect()->to('app/berita')->with('error', 'Gagal menghapus berita.');
        }
    }
}
