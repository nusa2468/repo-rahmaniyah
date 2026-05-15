<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OsisModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;
use CodeIgniter\Exceptions\PageNotFoundException; // Tambahkan import ini

class OsisController extends BaseController
{
    protected $osisModel;
    protected $siswaModel;
    protected $tahunAjaranModel;

    public function __construct()
    {
        $this->osisModel = new OsisModel();
        $this->siswaModel = new SiswaModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
        helper('form');
    }

    public function index()
    {
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        $id_tahun_ajaran_aktif = $tahunAjaranAktif['id'] ?? null;

        $data = [
            'title'          => 'Manajemen Kepengurusan OSIS',
            'current_module' => 'kesiswaan',
            'pengurus'       => $this->osisModel->getPengurusDetail($id_tahun_ajaran_aktif),
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
        ];
        // FIX: Memastikan path ke view sudah benar: 'kesiswaan/osis/index'
        return view('kesiswaan/osis/index', $data);
    }

    /**
     * Menampilkan form untuk menambah pengurus OSIS baru.
     */
    public function new()
    {
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        if (!$tahunAjaranAktif) {
            return redirect()->to('app/osis')->with('error', 'Silakan tentukan Tahun Ajaran yang aktif terlebih dahulu.');
        }

        // Mengambil siswa yang belum terdaftar di OSIS untuk tahun ajaran aktif
        $siswa_list = $this->osisModel->getSiswaNonOsis($tahunAjaranAktif['id']);

        $data = [
            'title'          => 'Tambah Anggota OSIS',
            'current_module' => 'kesiswaan',
            'pengurus'       => null, // Menggunakan 'pengurus' agar konsisten dengan edit
            'siswa_list'     => $siswa_list,
        ];
        // Path View: kesiswaan/osis/form
        return view('kesiswaan/osis/form', $data);
    }

    /**
     * Menyimpan data pengurus OSIS baru.
     */
    public function create()
    {
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        if (!$tahunAjaranAktif) {
            return redirect()->to('app/osis')->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }
        
        $data = $this->request->getPost();
        $data['id_tahun_ajaran'] = $tahunAjaranAktif['id'];

        // PENTING: Untuk CREATE, kita perlu memanggil validasi manual di Controller
        // karena kita menginjeksi id_tahun_ajaran secara manual.
        $validationRules = $this->osisModel->getValidationRules();
        // Hapus rule is_unique karena akan dicek di Model::save(), tapi tambahkan rule id_tahun_ajaran
        unset($validationRules['id']); 

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($this->osisModel->save($data)) {
            return redirect()->to('app/osis')->with('success', 'Anggota OSIS berhasil ditambahkan.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->osisModel->errors());
        }
    }

    /**
     * Menampilkan form untuk mengedit pengurus OSIS.
     */
    public function edit($id = null)
    {
        $pengurus = $this->osisModel->find($id);
        if (!$pengurus) {
            throw PageNotFoundException::forPageNotFound();
        }
        
        // Ambil detail nama siswa untuk ditampilkan di form edit
        $siswa = $this->siswaModel->find($pengurus['id_siswa']);
        $pengurus['nama_siswa'] = $siswa['nama_lengkap'] ?? 'Nama Tidak Ditemukan';

        $data = [
            'title'          => 'Edit Anggota OSIS',
            'current_module' => 'kesiswaan',
            'pengurus'       => $pengurus,
            // Saat edit, kita tidak perlu siswa_list, hanya data yang sudah ada.
        ];
        // Path View: kesiswaan/osis/form
        return view('kesiswaan/osis/form', $data);
    }

    /**
     * Memperbarui data pengurus OSIS.
     */
    public function update($id = null)
    {
        $post = $this->request->getPost();
        $post['id'] = $id;

        // Modifikasi validasi is_unique
        $validationRules = $this->osisModel->getValidationRules();
        $validationRules['id_siswa'] = "required|integer|is_unique[osis.id_siswa,id,{$id}]";

        // Gunakan validasi manual untuk proses update (dengan $post['id'])
        if (!$this->validate($validationRules)) {
            return redirect()->back()
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }
        
        // Gunakan save($post) yang akan memicu update karena $post['id'] ada
        if ($this->osisModel->save($post)) {
            return redirect()->to('app/osis')->with('success', 'Data anggota OSIS berhasil diperbarui.');
        } else {
            // Jika gagal database, kembali ke form edit
            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Gagal menyimpan data OSIS.');
        }
    }

    /**
     * Menghapus data pengurus OSIS.
     */
    public function delete($id = null)
    {
        if ($this->osisModel->delete($id)) {
            return redirect()->to('app/osis')->with('success', 'Anggota OSIS berhasil dihapus.');
        } else {
            return redirect()->to('app/osis')->with('error', 'Gagal menghapus anggota OSIS.');
        }
    }
}
