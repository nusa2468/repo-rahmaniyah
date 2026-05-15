<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CalonSiswaModel;
use App\Models\SiswaModel;

class Psb extends BaseController
{
    protected $calonSiswaModel;
    protected $siswaModel;

    public function __construct()
    {
        $this->calonSiswaModel = new CalonSiswaModel();
        $this->siswaModel = new SiswaModel();
        helper('form'); // Memuat helper untuk csrf_field()
    }

    public function index()
    {
        $data = [
            'title'             => 'Dashboard PSB',
            'current_module'    => 'psb',
            'total_pendaftar'   => $this->calonSiswaModel->countAllResults(),
            'diterima'          => $this->calonSiswaModel->where('status_pendaftaran', 'diterima')->countAllResults(),
            'ditolak'           => $this->calonSiswaModel->where('status_pendaftaran', 'ditolak')->countAllResults(),
            'pending'           => $this->calonSiswaModel->where('status_pendaftaran', 'pending')->countAllResults(),
            'pendaftar_terbaru' => $this->calonSiswaModel->orderBy('created_at', 'DESC')->limit(5)->findAll(),
        ];
        return view('psb/index', $data);
    }

    public function pendaftar()
    {
        $data = [
            'title'          => 'Data Pendaftar',
            'current_module' => 'psb',
            'calon_siswa'    => $this->calonSiswaModel->orderBy('created_at', 'DESC')->findAll(),
        ];
        return view('psb/pendaftar', $data);
    }

    public function updateStatus($id)
    {
        $status = $this->request->getPost('status');
        if (!in_array($status, ['pending', 'diterima', 'ditolak', 'cadangan'])) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $this->calonSiswaModel->update($id, ['status_pendaftaran' => $status]);

        return redirect()->to('app/psb/pendaftar')->with('success', 'Status pendaftar berhasil diperbarui.');
    }

    public function terimaSiswa($id_calon_siswa)
    {
        $calon = $this->calonSiswaModel->find($id_calon_siswa);
        if (!$calon) {
            return redirect()->back()->with('error', 'Data calon siswa tidak ditemukan.');
        }
        
        // Cek apakah siswa sudah pernah diproses
        if ($calon['status_pendaftaran'] === 'diterima') {
            return redirect()->back()->with('info', 'Siswa ini sudah pernah diterima dan diproses.');
        }

        // Generate NIS unik
        $lastSiswa = $this->siswaModel->orderBy('id', 'DESC')->first();
        $lastId = $lastSiswa ? (int)substr($lastSiswa['nis'], -4) : 0;
        $newNis = 'SIS' . date('Y') . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

        $dataSiswaBaru = [
            'nis'           => $newNis,
            'nama_lengkap'  => $calon['nama_lengkap'],
            'jenis_kelamin' => $calon['jenis_kelamin'],
            'alamat'        => $calon['alamat'],
            'email'         => null, // Bisa diisi nanti
            'telepon'       => $calon['telepon_wali'],
            'status'        => 'aktif',
        ];

        if ($this->siswaModel->insert($dataSiswaBaru)) {
            // Update status calon siswa menjadi 'diterima'
            $this->calonSiswaModel->update($id_calon_siswa, ['status_pendaftaran' => 'diterima']);
            return redirect()->to('app/psb/pendaftar')->with('success', 'Calon siswa berhasil diterima dan datanya telah ditambahkan ke master siswa dengan NIS: ' . $newNis);
        } else {
            return redirect()->back()->with('error', 'Gagal memproses penerimaan siswa.');
        }
    }
}

