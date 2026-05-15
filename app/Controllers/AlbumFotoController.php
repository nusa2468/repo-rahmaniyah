<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AlbumFotoModel;

class AlbumFotoController extends BaseController
{
    protected $albumFotoModel;

    public function __construct()
    {
        $this->albumFotoModel = new AlbumFotoModel();
        helper('form');
    }

    public function index()
    {
        $data = [
            'title'          => 'Manajemen Galeri Foto',
            'current_module' => 'humas',
            'album'          => $this->albumFotoModel->orderBy('created_at', 'DESC')->findAll(),
        ];
        return view('humas/album/index', $data);
    }

    public function new()
    {
        $data = [
            'title'          => 'Tambah Foto Baru',
            'current_module' => 'humas',
        ];
        return view('humas/album/form', $data);
    }

    public function create()
    {
        $data = $this->request->getPost();
        
        // Untuk saat ini, kita gunakan placeholder URL. Fitur upload bisa ditambahkan nanti.
        if (empty($data['url_gambar'])) {
            $data['url_gambar'] = 'https://placehold.co/600x400/6c757d/ffffff?text=Gambar+Baru';
        }

        if ($this->albumFotoModel->save($data)) {
            return redirect()->to('app/albumfoto')->with('success', 'Foto berhasil ditambahkan ke galeri.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->albumFotoModel->errors());
        }
    }

    public function edit($id = null)
    {
        $foto = $this->albumFotoModel->find($id);
        if (!$foto) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'          => 'Edit Foto',
            'current_module' => 'humas',
            'foto'           => $foto,
        ];
        return view('humas/album/form', $data);
    }

    public function update($id = null)
    {
        $data = $this->request->getPost();

        if ($this->albumFotoModel->update($id, $data)) {
            return redirect()->to('app/albumfoto')->with('success', 'Informasi foto berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->albumFotoModel->errors());
        }
    }

    public function delete($id = null)
    {
        if ($this->albumFotoModel->delete($id)) {
            return redirect()->to('app/albumfoto')->with('success', 'Foto berhasil dihapus dari galeri.');
        } else {
            return redirect()->to('app/albumfoto')->with('error', 'Gagal menghapus foto.');
        }
    }
}

