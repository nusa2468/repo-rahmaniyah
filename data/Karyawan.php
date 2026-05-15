<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KaryawanModel;
use CodeIgniter\Exceptions\PageNotFoundException; // Tambahkan ini

/**
 * Controller untuk mengelola data master Karyawan (Staf Non-Guru).
 */
class Karyawan extends BaseController
{
    protected $karyawanModel;

    public function __construct()
    {
        // Inisialisasi Model Karyawan
        $this->karyawanModel = new KaryawanModel();
        // Memuat helper form untuk penggunaan pada view
        helper(['form', 'url']); 
    }

    /**
     * Menampilkan daftar semua karyawan.
     */
    public function index()
    {
        $data = [
            'title'          => 'Master Data - Karyawan',
            'current_module' => 'master_data',
            // Mengambil semua data karyawan, diurutkan berdasarkan nama lengkap
            'karyawan'       => $this->karyawanModel->orderBy('nama_lengkap', 'ASC')->findAll(),
        ];
        return view('karyawan/index', $data);
    }

    /**
     * Metode show() ini biasanya digunakan untuk menampilkan detail, 
     * tetapi di sini diarahkan ke halaman edit/form.
     */
    public function show($id = null)
    {
        return redirect()->to('app/karyawan/edit/' . $id);
    }

    /**
     * Menampilkan form untuk menambah karyawan baru.
     */
    public function new()
    {
        $data = [
            'title'          => 'Tambah Karyawan Baru',
            'current_module' => 'master_data',
            'validation'     => \Config\Services::validation(),
        ];
        return view('karyawan/form', $data);
    }

    /**
     * Menyimpan data karyawan baru ke database.
     */
    public function create()
    {
        // 1. Ambil data POST
        $dataPost = $this->request->getPost();

        // 2. Lakukan validasi data secara eksplisit
        if (!$this->karyawanModel->validate($dataPost)) {
            // Jika validasi gagal, kembali ke form dengan input sebelumnya dan error
            session()->setFlashdata('errors', $this->karyawanModel->errors());
            return redirect()->back()->withInput();
        }

        // 3. Simpan data (jika validasi berhasil)
        if ($this->karyawanModel->save($dataPost)) {
            session()->setFlashdata('success', 'Data karyawan berhasil ditambahkan.');
            return redirect()->to('app/karyawan');
        } else {
            // Ini adalah fallback jika save() gagal karena alasan lain (jarang terjadi setelah validasi)
            session()->setFlashdata('error', 'Gagal menyimpan data karyawan ke database.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit data karyawan.
     */
    public function edit($id = null)
    {
        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $karyawan = $this->karyawanModel->find($id);
        
        if (!$karyawan) {
            // Menggunakan Exception CodeIgniter untuk 404
            throw PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'          => 'Edit Data Karyawan',
            'current_module' => 'master_data',
            'karyawan'       => $karyawan,
            'validation'     => \Config\Services::validation(),
        ];
        return view('karyawan/form', $data);
    }

    /**
     * Memperbarui data karyawan yang sudah ada.
     */
    public function update($id = null)
    {
        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 1. Ambil data POST
        $dataPost = $this->request->getPost();

        // 2. Wajib: Tambahkan ID ke array data agar Model tahu ini adalah UPDATE 
        // dan validasi is_unique dengan placeholder {id} bekerja.
        $dataPost['id'] = $id;

        // 3. Lakukan validasi data secara eksplisit
        if (!$this->karyawanModel->validate($dataPost)) {
            // Jika validasi gagal, kembali ke form dengan input sebelumnya dan error
            session()->setFlashdata('errors', $this->karyawanModel->errors());
            return redirect()->back()->withInput();
        }

        // 4. Simpan/Update data
        if ($this->karyawanModel->save($dataPost)) {
            session()->setFlashdata('success', 'Data karyawan berhasil diperbarui.');
            return redirect()->to('app/karyawan');
        } else {
             // Fallback
            session()->setFlashdata('error', 'Gagal memperbarui data karyawan di database.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menghapus data karyawan berdasarkan ID.
     */
    public function delete($id = null)
    {
        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        
        if ($this->karyawanModel->delete($id)) {
            session()->setFlashdata('success', 'Data karyawan berhasil dihapus.');
            return redirect()->to('app/karyawan');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus data karyawan. Data mungkin digunakan di tabel lain.');
            return redirect()->to('app/karyawan');
        }
    }
}