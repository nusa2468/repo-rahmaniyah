<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TahunAjaranModel;

class TahunAjaran extends BaseController
{
    protected $tahunAjaranModel;

    public function __construct()
    {
        $this->tahunAjaranModel = new TahunAjaranModel();
        // Memuat helper form, berguna untuk view
        helper('form');
    }

    /**
     * Menampilkan daftar semua Tahun Ajaran dan Semester.
     */
    public function index()
    {
        // Mengambil semua data dan mengurutkannya
        $data = [
            'title'          => 'Master Data - Tahun Ajaran & Semester',
            'current_module' => 'master_data',
            // Mengambil semua data, diurutkan berdasarkan tahun ajaran (terbaru)
            'tahun_ajaran'   => $this->tahunAjaranModel->orderBy('tahun_ajaran', 'DESC')->findAll(),
        ];
        return view('tahunajaran/index', $data);
    }

    /**
     * Redirect dari show() ke edit() untuk kenyamanan.
     */
    public function show($id = null)
    {
        return redirect()->to('app/tahunajaran/edit/' . $id);
    }

    /**
     * Menampilkan form untuk menambah Tahun Ajaran baru.
     */
    public function new()
    {
        $data = [
            'title'          => 'Tambah Tahun Ajaran & Semester Baru',
            'current_module' => 'master_data',
            'validation'     => \Config\Services::validation(),
        ];
        return view('tahunajaran/form', $data);
    }

    /**
     * Menyimpan data Tahun Ajaran baru ke database.
     */
    public function create()
    {
        $dataPost = $this->request->getPost();

        // TAHAP 1: VALIDASI URUTAN TANGGAL
        if (!$this->_validateDateOrder($dataPost)) {
            session()->setFlashdata('error', 'Gagal menyimpan: Tanggal Selesai tidak boleh mendahului Tanggal Mulai.');
            return redirect()->back()->withInput();
        }

        // TAHAP 2: VALIDASI TUMPANG TINDIH RENTANG TANGGAL (Penting untuk Integritas Data)
        if (!$this->_validateDateOverlap($dataPost)) {
            session()->setFlashdata('error', 'Gagal menyimpan: Rentang Tanggal Mulai dan Selesai yang dimasukkan tumpang tindih dengan Tahun Ajaran/Semester lain yang sudah ada.');
            return redirect()->back()->withInput();
        }
        
        // Logika menonaktifkan TA lama dan unik komposit sudah ditangani di Model Hook.
        
        // Simpan data. Jika validasi gagal, save() akan mengembalikan false.
        if ($this->tahunAjaranModel->save($dataPost)) {
            // Sukses
            return redirect()->to('app/tahunajaran')->with('success', 'Tahun Ajaran & Semester berhasil ditambahkan.');
        } else {
            // Gagal, kembali dengan pesan error validasi dari Model
            // Menggunakan with('errors', ...) untuk menyimpan array error validasi di sesi
            return redirect()->back()->withInput()->with('errors', $this->tahunAjaranModel->errors());
        }
    }

    /**
     * Menampilkan form untuk mengedit Tahun Ajaran yang ada.
     */
    public function edit($id = null)
    {
        $tahun_ajaran = $this->tahunAjaranModel->find($id);
        if (!$tahun_ajaran) {
            // Tampilkan error 404 jika ID tidak ditemukan
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'          => 'Edit Tahun Ajaran & Semester',
            'current_module' => 'master_data',
            'tahun_ajaran'   => $tahun_ajaran,
            // Mengambil error dari sesi (jika ada setelah gagal update)
            'errors'         => session()->getFlashdata('errors'), 
            'validation'     => \Config\Services::validation(),
        ];
        return view('tahunajaran/form', $data);
    }

    /**
     * Memperbarui data Tahun Ajaran yang ada.
     */
    public function update($id = null)
    {
        $dataPost = $this->request->getPost();

        // Wajib: Tambahkan ID ke array data agar Model tahu ini adalah UPDATE
        $dataPost['id'] = $id; 

        // TAHAP 1: VALIDASI URUTAN TANGGAL
        if (!$this->_validateDateOrder($dataPost)) {
            session()->setFlashdata('error', 'Gagal memperbarui: Tanggal Selesai tidak boleh mendahului Tanggal Mulai.');
            return redirect()->back()->withInput();
        }

        // TAHAP 2: VALIDASI TUMPANG TINDIH RENTANG TANGGAL (Penting untuk Integritas Data)
        if (!$this->_validateDateOverlap($dataPost, $id)) {
            session()->setFlashdata('error', 'Gagal memperbarui: Rentang Tanggal Mulai dan Selesai yang dimasukkan tumpang tindih dengan Tahun Ajaran/Semester lain yang sudah ada.');
            return redirect()->back()->withInput();
        }
        
        // Logika menonaktifkan TA lama dan unik komposit sudah ditangani di Model Hook.
        
        // Simpan data. Jika validasi gagal, save() akan mengembalikan false.
        if ($this->tahunAjaranModel->save($dataPost)) {
            // Sukses
            session()->setFlashdata('success', 'Tahun Ajaran & Semester berhasil diperbarui.');
            return redirect()->to('app/tahunajaran');
        } else {
            // Gagal, kembali dengan pesan error validasi
            // PENTING: Gunakan with('errors', ...) agar pesan error dari Model Hooks juga terbawa.
            return redirect()->back()->withInput()->with('errors', $this->tahunAjaranModel->errors());
        }
    }

    /**
     * Menghapus Tahun Ajaran.
     */
    public function delete($id = null)
    {
        // Ambil konteks aktif untuk pengecekan
        $activeContext = $this->tahunAjaranModel->getActiveAcademicContext();
        
        // Pengecekan Integritas: Tidak boleh menghapus Tahun Ajaran yang aktif
        if ($activeContext && (int)$activeContext['id'] === (int)$id) {
            return redirect()->to('app/tahunajaran')->with('error', 'Gagal menghapus: Tahun Ajaran yang sedang AKTIF tidak dapat dihapus.');
        }

        if ($this->tahunAjaranModel->delete($id)) {
            return redirect()->to('app/tahunajaran')->with('success', 'Tahun Ajaran & Semester berhasil dihapus.');
        } else {
            // Logika standar jika penghapusan gagal (misalnya karena foreign key constraint)
            return redirect()->to('app/tahunajaran')->with('error', 'Gagal menghapus Tahun Ajaran & Semester. Data mungkin sedang digunakan atau sudah terhapus.');
        }
    }

    /**
     * Validasi kustom untuk mencegah Tanggal Selesai mendahului Tanggal Mulai.
     * @param array $dataPost Data yang diposting (termasuk tanggal_mulai dan tanggal_selesai)
     * @return bool True jika tanggal sudah terurut, False jika tidak.
     */
    private function _validateDateOrder(array $dataPost): bool
    {
        // Pastikan field ada
        if (!isset($dataPost['tanggal_mulai']) || !isset($dataPost['tanggal_selesai'])) {
            return false;
        }

        $startDate = strtotime($dataPost['tanggal_mulai']);
        $endDate = strtotime($dataPost['tanggal_selesai']);

        // Tanggal Selesai harus lebih besar atau sama dengan Tanggal Mulai
        return $endDate >= $startDate;
    }

    /**
     * Validasi kustom untuk mencegah rentang waktu (tanggal mulai/selesai) tumpang tindih 
     * dengan Tahun Ajaran/Semester lain yang sudah ada.
     * * @param array $dataPost Data yang diposting (termasuk tanggal_mulai dan tanggal_selesai)
     * @param int|null $id ID Tahun Ajaran saat ini (null jika membuat baru)
     * @return bool True jika tidak ada tumpang tindih, False jika ada.
     */
    private function _validateDateOverlap(array $dataPost, $id = null): bool
    {
        // Pastikan tanggal_mulai dan tanggal_selesai ada
        if (!isset($dataPost['tanggal_mulai']) || !isset($dataPost['tanggal_selesai'])) {
             // Jika field tidak ada, ini sudah dicek di _validateDateOrder, tapi kita jaga
            return false; 
        }

        $startDate = $dataPost['tanggal_mulai'];
        $endDate = $dataPost['tanggal_selesai'];

        // Menggunakan query builder untuk mencari entri yang tumpang tindih.
        // Tumpang tindih jika: (tanggal_mulai entri lama <= tanggal_selesai entri baru) 
        //                    AND (tanggal_selesai entri lama >= tanggal_mulai entri baru)
        
        $builder = $this->tahunAjaranModel->builder();
        
        $builder->where("tanggal_mulai <=", $endDate)
                ->where("tanggal_selesai >=", $startDate);

        // Jika ini operasi update, kecualikan ID entri yang sedang diupdate
        if ($id) {
            $builder->where('id !=', $id);
        }

        $count = $builder->countAllResults();

        // Jika count > 0, berarti ada tumpang tindih
        return $count === 0;
    }
}