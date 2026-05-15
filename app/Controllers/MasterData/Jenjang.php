<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\JenjangModel;

class Jenjang extends BaseController
{
    protected $jenjangModel;

    public function __construct()
    {
        // Pastikan Model sudah dibuat di app/Models/JenjangModel.php
        $this->jenjangModel = new JenjangModel();
    }

    /**
     * Dashboard Statistik Jenjang
     * URL: /app/masterdata/jenjang/dashboard
     */
    public function dashboard()
    {
        $data = [
            'title'        => 'Dashboard Master Jenjang',
            'stats'        => $this->jenjangModel->getStats(),
            'jenjang_list' => $this->jenjangModel->orderBy('urutan', 'ASC')->findAll(),
        ];
        return view('masterdata/jenjang/dashboard', $data);
    }

    /**
     * Menampilkan Daftar Jenjang (Halaman Utama)
     * URL: /app/masterdata/jenjang
     */
    public function index()
    {
        $data = [
            'title'        => 'Daftar Jenjang Pendidikan',
            'stats'        => $this->jenjangModel->getStats(),
            'jenjang_list' => $this->jenjangModel->orderBy('urutan', 'ASC')->findAll(),
        ];
        return view('masterdata/jenjang/index', $data);
    }

    /**
     * Form Tambah Jenjang Baru
     * URL: /app/masterdata/jenjang/new
     */
    public function new()
    {
        $data = ['title' => 'Tambah Jenjang Baru', 'jenjang' => null];
        return view('masterdata/jenjang/form', $data);
    }

    /**
     * Form Edit Jenjang
     * URL: /app/masterdata/jenjang/edit/{id}
     */
    public function edit($id)
    {
        $item = $this->jenjangModel->find($id);
        if (!$item) {
            return redirect()->to(base_url('app/masterdata/jenjang'))->with('error', 'Data tidak ditemukan.');
        }

        $data = ['title' => 'Edit Jenjang', 'jenjang' => $item];
        return view('masterdata/jenjang/form', $data);
    }

    /**
     * Proses Simpan Data (Insert & Update)
     * Menggunakan method spoofing untuk update jika ID tersedia
     */
    public function save($id = null)
    {
        // Tangkap ID dari Hidden Input jika tidak ada di URL
        $id = $id ?? $this->request->getVar('id');

        // Data yang akan disimpan
        $data = [
            'id'           => $id, // Penting untuk deteksi Update di Model (is_unique validation)
            'nama_jenjang' => $this->request->getVar('nama_jenjang'),
            'kode_jenjang' => $this->request->getVar('kode_jenjang'),
            'keterangan'   => $this->request->getVar('keterangan'),
            'urutan'       => $this->request->getVar('urutan'),
            'status'       => $this->request->getVar('status'),
        ];

        // Validasi dan Simpan menggunakan Model
        if ($this->jenjangModel->save($data)) {
            $msg = $id ? 'Data jenjang berhasil diperbarui.' : 'Jenjang baru berhasil ditambahkan.';
            return redirect()->to(base_url('app/masterdata/jenjang'))->with('success', $msg);
        } else {
            // Jika gagal, kembalikan input dan pesan error
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.')->with('errors', $this->jenjangModel->errors());
        }
    }

    /**
     * Hapus Data
     * URL: /app/masterdata/jenjang/delete/{id}
     */
    public function delete($id)
    {
        if ($this->jenjangModel->find($id)) {
            $this->jenjangModel->delete($id);
            return redirect()->to(base_url('app/masterdata/jenjang'))->with('success', 'Jenjang berhasil dihapus.');
        }
        return redirect()->to(base_url('app/masterdata/jenjang'))->with('error', 'Data tidak ditemukan.');
    }
}