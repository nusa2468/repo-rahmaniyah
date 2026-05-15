<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KurikulumModel;
use CodeIgniter\API\ResponseTrait;
use ReflectionException;

/**
 * Controller untuk mengelola data Kurikulum.
 * Catatan: Logika eksklusivitas status 'aktif' kini ditangani di KurikulumModel menggunakan Hooks.
 */
class Kurikulum extends BaseController
{
    use ResponseTrait;

    protected $kurikulumModel;
    // Tentukan URL dasar untuk redirect
    private $redirectBaseUrl = '/app/kurikulum';

    public function __construct()
    {
        // Memuat Model Kurikulum
        $this->kurikulumModel = new KurikulumModel();
        helper(['form', 'url']);
    }

    /**
     * Fungsi untuk mengubah status kurikulum (aktif/tidak aktif).
     * Metode ini aman dari validasi nama_kurikulum yang gagal.
     */
    public function toggleStatus($id = null)
    {
        $model = new KurikulumModel();
        
        if ($id === null) {
            session()->setFlashdata('error', 'ID Kurikulum tidak valid.');
            return redirect()->back();
        }

        // 1. Ambil data kurikulum yang ada
        $currentData = $model->find($id);

        if (!$currentData) {
            session()->setFlashdata('error', 'Data Kurikulum ID: ' . $id . ' tidak ditemukan.');
            return redirect()->back();
        }

        // 2. Tentukan status baru
        $newStatus = ($currentData['status'] === 'aktif') ? 'tidak aktif' : 'aktif';

        // 3. Susun data UPDATE minimal
        // Kita HANYA mengirim status. Karena Model sudah menggunakan 'required_with',
        // validasi nama_kurikulum tidak akan dijalankan.
        $dataToUpdate = [
            'status' => $newStatus
        ];

        // 4. Jalankan update
        // JIKA $newStatus == 'aktif', hook setExclusiveStatus akan berjalan.
        // JIKA $newStatus == 'tidak aktif', update standar berjalan dan pasti lolos validasi status.
        if ($model->update($id, $dataToUpdate)) {
            session()->setFlashdata('message', 'Status Kurikulum ID: ' . $id . ' berhasil diubah menjadi **' . strtoupper($newStatus) . '**.');
        } else {
            // Jika ada error validasi lain (misalnya status tidak dikenal)
            $errors = $model->errors();
            $errorMessage = "Gagal mengubah status Kurikulum ID: {$id}.";
            if (!empty($errors)) {
                $errorMessage .= " Detail Error: " . implode(' | ', $errors);
            }
            session()->setFlashdata('error', $errorMessage);
        }

        return redirect()->to('/kurikulum');
    }
    

    /**
     * Menampilkan daftar semua Kurikulum (READ).
     */
    public function index()
    {
        $data = [
            'title' => 'Manajemen Kurikulum',
            // Ambil semua data kurikulum dari model
            'kurikulum' => $this->kurikulumModel->findAll()
        ];

        return view('kurikulum/index', $data);
    }

    /**
     * Menampilkan form untuk menambah Kurikulum baru (CREATE - Form).
     */
    public function new()
    {
        $data = [
            'title' => 'Tambah Kurikulum Baru',
            'validation' => \Config\Services::validation(),
            'kurikulum' => $this->kurikulumModel->getNew() 
        ];

        return view('kurikulum/form', $data); 
    }

    /**
     * Menyimpan data Kurikulum baru ke database (CREATE - Save).
     */
    public function create()
    {
        $data = $this->request->getPost();

        if (!$this->kurikulumModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $this->kurikulumModel->errors());
        }

        try {
            // TIDAK PERLU LOGIKA EKSKLUSIVITAS DI SINI!
            // Logika eksklusivitas (nonaktifkan yang lain jika status 'aktif') sudah ditangani di KurikulumModel Hook (beforeInsert).
            
            $this->kurikulumModel->save($data);
            
            session()->setFlashdata('success', 'Data Kurikulum berhasil ditambahkan.');
            return redirect()->to($this->redirectBaseUrl);
        } catch (ReflectionException $e) {
            session()->setFlashdata('error', 'Gagal menyimpan data Kurikulum: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit Kurikulum (UPDATE - Form).
     */
    public function edit(int $id)
    {
        $kurikulum = $this->kurikulumModel->find($id);

        if (!$kurikulum) {
            session()->setFlashdata('error', 'Data Kurikulum tidak ditemukan.');
            return redirect()->to($this->redirectBaseUrl);
        }

        $data = [
            'title' => 'Edit Kurikulum',
            'kurikulum' => $kurikulum,
            'validation' => \Config\Services::validation(),
        ];

        return view('kurikulum/form', $data);
    }

    /**
     * Memperbarui data Kurikulum di database (UPDATE - Save).
     */
    public function update(int $id)
    {
        $data = $this->request->getPost();
        $data['id'] = $id;

        if (!$this->kurikulumModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $this->kurikulumModel->errors());
        }

        $updateData = [
            'nama_kurikulum' => $data['nama_kurikulum'] ?? null,
            'status' => $data['status'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
        ];

        try {
            // TIDAK PERLU LOGIKA EKSKLUSIVITAS DI SINI!
            // Logika eksklusivitas (nonaktifkan yang lain jika status 'aktif') sudah ditangani di KurikulumModel Hook (beforeUpdate).

            $this->kurikulumModel->update($id, $updateData);
            
            session()->setFlashdata('success', 'Data Kurikulum berhasil diperbarui.');
            return redirect()->to($this->redirectBaseUrl);
        } catch (ReflectionException $e) {
            session()->setFlashdata('error', 'Gagal memperbarui data Kurikulum: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menghapus Kurikulum dari database (DELETE).
     */
    public function delete(int $id)
    {
        $kurikulum = $this->kurikulumModel->find($id);

        if (!$kurikulum) {
            session()->setFlashdata('error', 'Data Kurikulum tidak ditemukan.');
            return redirect()->to($this->redirectBaseUrl);
        }

        try {
            $this->kurikulumModel->delete($id);
            session()->setFlashdata('success', 'Data Kurikulum berhasil dihapus.');
            return redirect()->to($this->redirectBaseUrl);
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Gagal menghapus Kurikulum. Pastikan tidak ada Mata Pelajaran yang terkait dengan Kurikulum ini. Pesan Error: ' . $e->getMessage());
            return redirect()->to($this->redirectBaseUrl);
        }
    }
}