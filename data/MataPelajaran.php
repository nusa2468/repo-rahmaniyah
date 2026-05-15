<?php

namespace App\Controllers; // Menggunakan namespace App\Controllers sesuai permintaan terakhir

use App\Controllers\BaseController;
use App\Models\MataPelajaranModel;

class MataPelajaran extends BaseController 
{
    protected $model;

    public function __construct()
    {
        // Inisialisasi Model
        $this->model = new MataPelajaranModel();
        
        // Memuat helper form untuk penggunaan yang lebih clean
        helper(['form', 'url']);
    }

    // TAMPILAN DAFTAR (INDEX)
    public function index()
    {
        $data = [
            'mapel' => $this->model->findAll(),
            'title' => 'Data Mata Pelajaran'
        ];
        return view('matapelajaran/index', $data);
    }
    
    // TAMPILAN FORM TAMBAH (NEW)
    public function new()
    {
        $data = [
            'title' => 'Tambah Mata Pelajaran',
            'mapel' => [] // Sediakan array kosong untuk mencegah error pada view
        ];
        return view('matapelajaran/form', $data);
    }
    
    // LOGIKA SIMPAN DATA BARU (CREATE)
    public function create()
    {
        $post = $this->request->getPost();
        
        // NOTE: Model sudah memiliki validationRules. Kita hanya perlu memanggil save().
        if (!$this->model->save($post)) {
            // Jika validasi gagal, kembalikan ke form dengan data input dan error
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        return redirect()->to(base_url('app/matapelajaran'))->with('message', 'Data Mata Pelajaran berhasil ditambahkan.');
    }
    
    // TAMPILAN FORM EDIT (EDIT)
    public function edit($id = null)
    {
        $mapel = $this->model->find($id);

        if (!$mapel) {
            return redirect()->to(base_url('app/matapelajaran'))->with('message', 'Data tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Mata Pelajaran: ' . $mapel['nama_mapel'],
            'mapel' => $mapel
        ];
        return view('matapelajaran/form', $data);
    }
    
    // LOGIKA UPDATE DATA (UPDATE) - FIX UNTUK is_unique DENGAN MENGGUNAKAN save($data_dengan_id)
    public function update($id = null)
    {
        // 1. Ambil semua data dari request
        $post = $this->request->getPost();
        
        // 2. Cek apakah ID ditemukan. Ini penting sebelum mencoba update.
        if (!$this->model->find($id)) {
            return redirect()->to(base_url('app/matapelajaran'))->with('message', 'Data yang akan diupdate tidak ditemukan.');
        }

        // 3. Tambahkan ID ke array POST. 
        //    Ini adalah langkah KRUSIAL agar placeholder {id} di validationRules Model (is_unique[...,id,{id}]) dapat terisi.
        $post['id'] = $id;
        
        // 4. Lakukan update menggunakan metode save(). 
        //    Model akan otomatis mendeteksi adanya 'id' dan menjalankan proses update beserta validasi.
        if (!$this->model->save($post)) {
            // Jika validasi gagal, kembalikan ke form dengan data input dan error
            return redirect()->back()->withInput()->with('errors', $this->model->errors());
        }

        // 5. Sukses
        return redirect()->to(base_url('app/matapelajaran'))->with('message', 'Data Mata Pelajaran berhasil diperbarui.');
    }

    // LOGIKA HAPUS (DELETE)
    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return redirect()->to(base_url('app/matapelajaran'))->with('message', 'Data Mata Pelajaran berhasil dihapus.');
        }

        return redirect()->to(base_url('app/matapelajaran'))->with('message', 'Gagal menghapus data.');
    }
}