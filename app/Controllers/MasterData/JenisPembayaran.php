<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\JenisPembayaranModel;
use App\Models\JenjangModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class JenisPembayaran extends BaseController
{
    protected $jenisPembayaranModel;
    protected $jenjangModel;

    public function __construct()
    {
        $this->jenisPembayaranModel = model(JenisPembayaranModel::class);
        $this->jenjangModel         = model(JenjangModel::class);

        // Helper dimuat di sini agar tersedia di semua method
        helper(['form', 'url']);
    }

    /**
     * Daftar jenis pembayaran dengan fitur filter jenjang dan pagination
     */
    public function index()
    {
        // 1. Ambil filter jenjang dari URL (?jenjang=SD)
        $filterJenjang = $this->request->getGet('jenjang');
        
        $perPage = 10;

        // Pastikan Model Jenjang memiliki method getDropdownOptions()
        $jenjangAktif = $this->jenjangModel->getDropdownOptions();

        $data = [
            'title'          => 'Master Data - Jenis Pembayaran',
            'current_module' => 'keuangan',
            
            // Mengambil data ter-filter & ter-paginate dari Model
            'jenis_pembayaran' => $this->jenisPembayaranModel->getPaginated($perPage, $filterJenjang),
            'pager'            => $this->jenisPembayaranModel->pager,
            
            // Data untuk dropdown filter
            'jenjang_aktif'    => $jenjangAktif, 
            'filter_jenjang'   => $filterJenjang,
        ];

        // Pastikan path View ini sesuai dengan struktur folder Anda
        return view('masterdata/jenispembayaran/index', $data);
    }

    /**
     * Form tambah baru
     */
    public function new()
    {
        $data = [
            'title'           => 'Tambah Jenis Pembayaran Baru',
            'current_module'  => 'keuangan',
            'validation'      => \Config\Services::validation(),
            'jenjang_options' => $this->jenjangModel->getDropdownOptions(), 
        ];

        return view('masterdata/jenispembayaran/form', $data);
    }

    /**
     * Proses simpan data baru
     * Route: POST /app/masterdata/jenispembayaran (jika resource)
     * atau /app/masterdata/jenispembayaran/create (jika presenter/manual)
     */
    public function create()
    {
        $postData = $this->request->getPost();

        // 1. Coba Simpan
        if ($this->jenisPembayaranModel->save($postData)) {
            return redirect()
                ->to('/app/masterdata/jenispembayaran')
                ->with('success', 'Jenis pembayaran berhasil ditambahkan.');
        }

        // 2. Jika Gagal Validasi
        return redirect()
            ->back()
            ->withInput()
            ->with('errors', $this->jenisPembayaranModel->errors());
    }

    /**
     * Form edit
     */
    public function edit($id = null)
    {
        $jenis = $this->jenisPembayaranModel->find($id);

        if (!$jenis) {
            throw PageNotFoundException::forPageNotFound(
                "Jenis pembayaran dengan ID $id tidak ditemukan."
            );
        }

        $data = [
            'title'           => 'Edit Jenis Pembayaran',
            'current_module'  => 'keuangan',
            'jenis'           => $jenis,
            'validation'      => \Config\Services::validation(),
            'jenjang_options' => $this->jenjangModel->getDropdownOptions(),
        ];

        return view('masterdata/jenispembayaran/form', $data);
    }

    /**
     * Proses update data
     */
    public function update($id = null)
    {
        $postData = $this->request->getPost();
        
        // Safety: Hapus ID dari array data agar tidak menimpa ID di URL
        unset($postData['id']); 

        // Pastikan ID valid sebelum update
        if (!$this->jenisPembayaranModel->find($id)) {
             throw PageNotFoundException::forPageNotFound("ID tidak ditemukan.");
        }

        // Proses Update
        if ($this->jenisPembayaranModel->update($id, $postData)) {
            return redirect()
                ->to('/app/masterdata/jenispembayaran')
                ->with('success', 'Jenis pembayaran berhasil diperbarui.');
        }

        // Jika Gagal (Validasi)
        return redirect()
            ->back()
            ->withInput()
            ->with('errors', $this->jenisPembayaranModel->errors());
    }

    /**
     * Hapus data (Soft Delete)
     */
    public function delete($id = null)
    {
        if ($this->jenisPembayaranModel->delete($id)) {
            return redirect()
                ->to('/app/masterdata/jenispembayaran')
                ->with('success', 'Jenis pembayaran berhasil dihapus.');
        }

        return redirect()
            ->to('/app/masterdata/jenispembayaran')
            ->with('error', 'Gagal menghapus jenis pembayaran.');
    }

    /**
     * Redirect detail ke form edit
     */
    public function show($id = null)
    {
        return redirect()->to("/app/masterdata/jenispembayaran/edit/$id");
    }
}