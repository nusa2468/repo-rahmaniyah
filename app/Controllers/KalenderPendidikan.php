<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\KalenderPendidikanModel;
use App\Models\TahunAjaranModel;
use CodeIgniter\HTTP\RedirectResponse;

class KalenderPendidikan extends BaseController
{
    protected $kalenderModel;
    protected $tahunAjaranModel;

    public function __construct()
    {
        // Pastikan Model KalenderPendidikanModel sudah diperbaiki di file Model
        $this->kalenderModel = new KalenderPendidikanModel();
        // Asumsi TahunAjaranModel sudah tersedia
        $this->tahunAjaranModel = new TahunAjaranModel(); 
        helper(['form', 'url']);
    }

    // -------------------------------------------------------------------
    // TAMPILAN INDEX (Membaca Data Kalender Aktif)
    // -------------------------------------------------------------------

    /**
     * Menampilkan daftar acara kalender pendidikan, difilter berdasarkan Tahun Ajaran Aktif.
     */
    public function index(): string
    {
        // Ambil data TAHUN AJARAN AKTIF
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        $tahunAjaranId = $tahunAjaranAktif['id'] ?? null;
        
        // Inisialisasi query Active Record
        $kalenderQuery = $this->kalenderModel
            ->select('kalender_pendidikan.*, tahun_ajaran.tahun_ajaran')
            ->join('tahun_ajaran', 'tahun_ajaran.id = kalender_pendidikan.tahun_ajaran_id', 'left');

        // Lakukan FILTERING berdasarkan Tahun Ajaran Aktif jika ditemukan
        if ($tahunAjaranId) {
            $kalenderQuery = $kalenderQuery->where('kalender_pendidikan.tahun_ajaran_id', $tahunAjaranId);
        }
        
        $kalender = $kalenderQuery->findAll();
        
        $data = [
            'title'             => 'Kurikulum - Daftar Kalender Pendidikan',
            'current_module'    => 'kurikulum',
            'kalender'          => $kalender,
            // Variabel ini dibutuhkan oleh view untuk menampilkan status TA aktif
            'tahun_ajaran_aktif'  => $tahunAjaranAktif, 
        ];

        return view('kalender/index', $data);
    }

    // -------------------------------------------------------------------
    // CREATE (Membuat Acara Baru)
    // -------------------------------------------------------------------

    /**
     * Menampilkan form untuk menambah acara baru.
     */
    public function new(): string
    {
        $tahunAjaranList = $this->tahunAjaranModel->findAll();
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();

        $data = [
            'title'             => 'Tambah Acara Kalender Pendidikan',
            'current_module'    => 'kurikulum',
            'tahun_ajaran_list' => $tahunAjaranList,
            // Kirim array kosong untuk mode 'new'
            'kalender'          => [], 
            'tahun_ajaran_aktif'  => $tahunAjaranAktif, 
            'errors'            => session('errors') ?? [],
        ];

        return view('kalender/form', $data);
    }

    /**
     * Menyimpan data acara baru (Mengandalkan Model Hooks untuk mapping field).
     */
    public function create(): RedirectResponse
    {
        $postData = $this->request->getPost();

        // Data dikirim ke Model menggunakan NAMA FIELD VALIDASI (nama_acara, tanggal_mulai, dll.)
        $data = [
            'tahun_ajaran_id' => $postData['tahun_ajaran_id'] ?? null,
            // Mapping: title (Form) -> nama_acara (Validasi Model)
            'nama_acara'      => $postData['title'] ?? null,
            // Mapping: start (Form) -> tanggal_mulai (Validasi Model)
            'tanggal_mulai'   => $postData['start'] ?? null,
            // Mapping: end (Form) -> tanggal_selesai (Validasi Model)
            'tanggal_selesai' => $postData['end'] ?? null,
            'keterangan'      => $postData['keterangan'] ?? null,
            'color'           => $postData['color'] ?? '#007bff',  
        ];
        
        // Validasi data berdasarkan rules di Model
        if (!$this->kalenderModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $this->kalenderModel->errors());
        }

        // Memanggil save(). Data akan melalui hook mapFields di Model (merubah nama field ke DB: title, start, end).
        if ($this->kalenderModel->save($data)) {
            session()->setFlashdata('success', 'Acara Kalender berhasil ditambahkan.');
        } else {
            session()->setFlashdata('error', 'Gagal menyimpan data karena kesalahan database.');
        }

        return redirect()->to(base_url('app/kalender'));
    }

    // -------------------------------------------------------------------
    // UPDATE (Mengubah Acara)
    // -------------------------------------------------------------------

    /**
     * Menampilkan form untuk mengedit acara berdasarkan ID.
     */
    public function edit($id = null): string|RedirectResponse
    {
        $kalender = $this->kalenderModel->find($id);

        if (!$kalender) {
            session()->setFlashdata('error', 'Data acara kalender tidak ditemukan.');
            return redirect()->to(base_url('app/kalender'));
        }
        
        // Data dari DB (title, start, end) disiapkan sesuai kunci yang diharapkan View/Form
        $kalenderDataForForm = [
            'id'              => $kalender['id'],
            'tahun_ajaran_id' => $kalender['tahun_ajaran_id'],
            'title'           => $kalender['title'] ?? '',
            'keterangan'      => $kalender['keterangan'] ?? '', 
            'start'           => $kalender['start'] ?? '',
            'end'             => $kalender['end'] ?? '',
            'color'           => $kalender['color'] ?? '#007bff',
        ];

        $tahunAjaranList = $this->tahunAjaranModel->findAll();
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();

        $data = [
            'title'             => 'Edit Acara Kalender Pendidikan',
            'current_module'    => 'kurikulum',
            'kalender'          => $kalenderDataForForm, 
            'tahun_ajaran_list' => $tahunAjaranList,
            'tahun_ajaran_aktif'  => $tahunAjaranAktif, 
            'errors'            => session('errors') ?? [],
        ];

        return view('kalender/form', $data);
    }

    /**
     * Memperbarui data acara yang ada (Mengandalkan Model Hooks untuk mapping field).
     */
    public function update($id = null): RedirectResponse
    {
        $postData = $this->request->getPost();
        
        if (!$this->kalenderModel->find($id)) {
            session()->setFlashdata('error', 'Data acara kalender tidak ditemukan.');
            return redirect()->to(base_url('app/kalender'));
        }
        
        // Data dikirim ke Model menggunakan NAMA FIELD VALIDASI (nama_acara, tanggal_mulai, dll.)
        $data = [
            'id'              => $id, // ID diperlukan untuk validasi update (jika ada rule is_unique)
            'tahun_ajaran_id' => $postData['tahun_ajaran_id'] ?? null,
            // Mapping: title (Form) -> nama_acara (Validasi Model)
            'nama_acara'      => $postData['title'] ?? null,
            // Mapping: start (Form) -> tanggal_mulai (Validasi Model)
            'tanggal_mulai'   => $postData['start'] ?? null,
            // Mapping: end (Form) -> tanggal_selesai (Validasi Model)
            'tanggal_selesai' => $postData['end'] ?? null,
            'keterangan'      => $postData['keterangan'] ?? null,
            'color'           => $postData['color'] ?? '#007bff',
        ];

        // Validasi data berdasarkan rules di Model
        if (!$this->kalenderModel->validate($data)) {
            return redirect()->back()->withInput()->with('errors', $this->kalenderModel->errors());
        }

        // Memanggil update($id, $data). Data akan melalui hook mapFields di Model.
        if ($this->kalenderModel->update($id, $data)) {
            session()->setFlashdata('success', 'Acara Kalender berhasil diperbarui.');
        } else {
            session()->setFlashdata('error', 'Gagal memperbarui data karena kesalahan database.');
        }

        return redirect()->to(base_url('app/kalender'));
    }

    // -------------------------------------------------------------------
    // DELETE (Menghapus Acara)
    // -------------------------------------------------------------------

    /**
     * Menghapus acara berdasarkan ID.
     */
    public function delete($id = null): RedirectResponse
    {
        if (!$this->kalenderModel->find($id)) {
            session()->setFlashdata('error', 'Data acara kalender tidak ditemukan.');
            return redirect()->to(base_url('app/kalender'));
        }

        if ($this->kalenderModel->delete($id)) {
            session()->setFlashdata('success', 'Acara Kalender berhasil dihapus.');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus data.');
        }

        return redirect()->to(base_url('app/kalender'));
    }
}