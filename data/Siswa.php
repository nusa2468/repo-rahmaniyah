<?php

namespace App\Controllers;

use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel; // Tambahkan ini jika dibutuhkan di form
use App\Models\GrupSiswaModel; // Tambahkan ini jika dibutuhkan di form
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Siswa extends BaseController
{
    protected $siswaModel;
    protected $kelasModel;
    protected $tahunAjaranModel;
    protected $grupSiswaModel;

    public function __construct()
    {
        helper('form');
        $this->siswaModel = new SiswaModel();
        // Asumsi model lain sudah tersedia
        $this->kelasModel = new KelasModel(); 
        $this->tahunAjaranModel = new TahunAjaranModel();
        $this->grupSiswaModel = new GrupSiswaModel();
    }

    public function index()
    {
        // PERBAIKAN KRITIS:
        // Mengubah pemanggilan dari getSiswaDetail() menjadi getSiswaLengkap()
        // agar sesuai dengan struktur data yang diharapkan oleh View (yaitu $data['siswa']->nis)
        $siswaData = $this->siswaModel->getSiswaDataWithRelations(); // Menggunakan nama fungsi yang telah diperbarui (conceptual)

        $data = [
            'title'          => 'Master Data - Siswa',
            'current_module' => 'master_data',
            'siswa_data'     => $siswaData, // Sekarang berisi data bersarang ['siswa' => {...}]
        ];

        return view('siswa/index', $data);
    }

    public function show($id = null)
    {
        return redirect()->to('app/siswa/edit/' . $id);
    }

    public function new()
    {
        $data = [
            'title'          => 'Tambah Siswa Baru',
            'current_module' => 'master_data',
            'kelas'          => $this->kelasModel->findAll(),
            'tahun_ajaran'   => $this->tahunAjaranModel->findAll(), // Tambahkan
            'grup_siswa'     => $this->grupSiswaModel->findAll(),   // Tambahkan
            'validation'     => \Config\Services::validation(),
        ];

        return view('siswa/form', $data);
    }

    public function create()
    {
        $dataPost = $this->request->getPost();
        
        // Logika pembuatan NIS (Nomor Induk Siswa)
        $lastSiswa = $this->siswaModel->orderBy('id', 'DESC')->first();
        // Menggunakan ->nis karena returnType Model disetel ke 'object'
        $lastId = $lastSiswa ? (int)substr($lastSiswa->nis, -4) : 0; 
        $dataPost['nis'] = 'SIS' . date('Y') . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        
        // Handle password untuk data baru
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $dataPost['password'] = password_hash($password, PASSWORD_DEFAULT);
        } else {
            // Berikan password default jika tidak diisi.
            $dataPost['password'] = password_hash('password123', PASSWORD_DEFAULT);
        }

        // --- Data untuk Enrollment/Relasi ---
        $enrollmentData = [
            'id_kelas'          => $this->request->getPost('id_kelas'),
            'id_tahun_ajaran'   => $this->request->getPost('id_tahun_ajaran'),
            'id_grup_siswa'     => $this->request->getPost('id_grup_siswa'),
            'status_akademik'   => 'Aktif', // Default status saat pendaftaran
        ];
        
        // Hapus data relasi dari data master
        if (isset($dataPost['email'])) unset($dataPost['email']);
        if (isset($dataPost['telepon'])) unset($dataPost['telepon']);
        if (isset($dataPost['alamat'])) unset($dataPost['alamat']);
        if (isset($dataPost['id_kelas'])) unset($dataPost['id_kelas']); 
        if (isset($dataPost['id_tahun_ajaran'])) unset($dataPost['id_tahun_ajaran']);
        if (isset($dataPost['id_grup_siswa'])) unset($dataPost['id_grup_siswa']);

        if ($this->siswaModel->save($dataPost)) {
            $newSiswaId = $this->siswaModel->getInsertID();

            // --- SIMPAN DATA KE TABEL RELASI LAIN (ENROLLMENT) ---
            $enrollmentModel = model('App\Models\SiswaEnrollmentModel');
            
            // Pastikan data enrollment yang wajib terisi
            if (!empty($enrollmentData['id_kelas']) && !empty($enrollmentData['id_tahun_ajaran'])) {
                $enrollmentModel->save([
                    'id_siswa'          => $newSiswaId,
                    'id_kelas'          => $enrollmentData['id_kelas'],
                    'id_tahun_ajaran'   => $enrollmentData['id_tahun_ajaran'],
                    'id_grup_siswa'     => $enrollmentData['id_grup_siswa'] ?? null, // Grup Siswa bisa nullable
                    'status_akademik'   => $enrollmentData['status_akademik'],
                ]);
            }
            // -----------------------------------------------------------------

            return redirect()->to(route_to('siswa_index'))->with('success', 'Data siswa berhasil ditambahkan dengan NIS: ' . $dataPost['nis']);
        } else {
            return redirect()->back()->withInput()->with('errors', $this->siswaModel->errors());
        }
    }

    public function edit($id)
    {
        // Memuat data Siswa + Enrollment Terkini untuk form edit
        $siswaData = $this->siswaModel->getSiswaDataWithRelations($id); // Asumsi Model bisa menerima ID tunggal
        $siswa = $siswaData[0]['siswa'] ?? null;
        $enrollment = $siswaData[0]['enrollment_terkini'] ?? null;

        if (!$siswa) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data Siswa tidak ditemukan');
        }

        $data = [
            'title'          => 'Edit Data Siswa',
            'current_module' => 'master_data',
            'siswa'          => $siswa,
            'enrollment'     => $enrollment, // Kirim data enrollment ke view
            'kelas'          => $this->kelasModel->findAll(),
            'tahun_ajaran'   => $this->tahunAjaranModel->findAll(),
            'grup_siswa'     => $this->grupSiswaModel->findAll(),
            'validation'     => \Config\Services::validation(),
        ];

        return view('siswa/form', $data);
    }

    public function update($id)
    {
        $dataPost = $this->request->getPost();
        
        // 1. WAJIB Sertakan ID di dalam $dataPost agar Model tahu ini adalah operasi update
        $dataPost['id'] = $id; 

        // 2. Handle update password: hanya hash jika password baru diisi
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $dataPost['password'] = password_hash($password, PASSWORD_DEFAULT);
        } else {
            // Hapus 'password' dari dataPost agar tidak menimpa password yang sudah ada
            unset($dataPost['password']); 
        }

        // --- Data untuk Enrollment/Relasi yang di-update ---
        $enrollmentData = [
            'id_kelas'          => $this->request->getPost('id_kelas'),
            'id_tahun_ajaran'   => $this->request->getPost('id_tahun_ajaran'),
            'id_grup_siswa'     => $this->request->getPost('id_grup_siswa'),
            // Status akademik mungkin diupdate di form lain, tapi kita jaga di sini
        ];
        
        // --- Hapus kolom yang bukan milik tabel master 'siswa' ---
        if (isset($dataPost['email'])) unset($dataPost['email']);
        if (isset($dataPost['telepon'])) unset($dataPost['telepon']);
        if (isset($dataPost['alamat'])) unset($dataPost['alamat']);
        if (isset($dataPost['id_kelas'])) unset($dataPost['id_kelas']); 
        if (isset($dataPost['id_tahun_ajaran'])) unset($dataPost['id_tahun_ajaran']);
        if (isset($dataPost['id_grup_siswa'])) unset($dataPost['id_grup_siswa']);
        // -------------------------------------------------------------------------

        if ($this->siswaModel->save($dataPost)) {
            // Logika untuk UPDATE data Enrollment (ini lebih kompleks:
            // 1. Cek apakah ada enrollment aktif untuk siswa ini.
            // 2. Jika ada, UPDATE enrollment tersebut.
            // 3. Jika kelas/tahun ajaran berubah, mungkin perlu INSERT enrollment baru (kenaikan kelas).
            
            // Skenario Sederhana: Hanya meng-update enrollment terbaru yang ditemukan.
            $enrollmentModel = model('App\Models\SiswaEnrollmentModel');
            $latestEnrollment = $enrollmentModel->where('id_siswa', $id)
                                                ->orderBy('id', 'DESC')
                                                ->first();
                                                
            if ($latestEnrollment) {
                 // Perbarui hanya jika ada perubahan pada id_kelas atau id_tahun_ajaran
                 if ($latestEnrollment['id_kelas'] != $enrollmentData['id_kelas'] ||
                     $latestEnrollment['id_tahun_ajaran'] != $enrollmentData['id_tahun_ajaran'] ||
                     $latestEnrollment['id_grup_siswa'] != $enrollmentData['id_grup_siswa']) 
                 {
                    $enrollmentModel->update($latestEnrollment['id'], [
                        'id_kelas'          => $enrollmentData['id_kelas'],
                        'id_tahun_ajaran'   => $enrollmentData['id_tahun_ajaran'],
                        'id_grup_siswa'     => $enrollmentData['id_grup_siswa'],
                    ]);
                 }
            } else {
                 // Jika tidak ada enrollment, buat baru (sama seperti create)
                 if (!empty($enrollmentData['id_kelas']) && !empty($enrollmentData['id_tahun_ajaran'])) {
                    $enrollmentModel->save([
                        'id_siswa'          => $id,
                        'id_kelas'          => $enrollmentData['id_kelas'],
                        'id_tahun_ajaran'   => $enrollmentData['id_tahun_ajaran'],
                        'id_grup_siswa'     => $enrollmentData['id_grup_siswa'] ?? null,
                        'status_akademik'   => 'Aktif',
                    ]);
                }
            }
            
            session()->setFlashdata('success', 'Data siswa berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->siswaModel->errors());
        }

        return redirect()->to(route_to('siswa_index'));
    }

    public function delete($id)
    {
        // Karena SiswaModel menggunakan Soft Deletes (useSoftDeletes = true),
        // metode delete() akan melakukan soft delete (mengisi kolom deleted_at)
        if ($this->siswaModel->delete($id)) {
            // Logika cascade delete/soft delete untuk data relasi bisa ditambahkan di sini.
            session()->setFlashdata('success', 'Data siswa berhasil dihapus (soft-deleted).');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus data siswa.');
        }

        return redirect()->to(route_to('siswa_index'));
    }
}