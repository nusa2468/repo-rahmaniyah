<?php

namespace App\Controllers\Kerjasama;

use App\Controllers\BaseController;
use App\Models\Kerjasama\KerjasamaModel;

class Kerjasama extends BaseController
{
    protected $kerjasamaModel;

    public function __construct()
    {
        $this->kerjasamaModel = new KerjasamaModel();
    }

    public function dashboard()
    {
        $data = [
            'title'    => 'Dashboard Kemitraan & KPI',
            'stats'    => $this->kerjasamaModel->getStats(),
            'expiring' => $this->kerjasamaModel->getExpiringMOU(30)
        ];
        return view('kerjasama/dashboard', $data);
    }

    public function index()
    {
        $data = [
            'title'     => 'Database Mitra & Dokumen Kerjasama',
            'kerjasama' => $this->kerjasamaModel->orderBy('tgl_akhir', 'ASC')->findAll(),
        ];
        return view('kerjasama/index', $data);
    }

    public function new()
    {
        $data = [
            'title'     => 'Registrasi Mitra Baru',
            'kerjasama' => null
        ];
        return view('kerjasama/form', $data);
    }

    public function edit($id)
    {
        $item = $this->kerjasamaModel->find($id);
        if (!$item) {
            return redirect()->to(base_url('app/kerjasama'))->with('error', 'Data tidak ditemukan.');
        }

        $data = [
            'title'     => 'Pembaruan Data Mitra',
            'kerjasama' => $item
        ];
        return view('kerjasama/form', $data);
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $programs = $this->request->getPost('program');
        $programString = $programs ? implode(', ', $programs) : '';

        $data = [
            'jenjang'        => $this->request->getPost('jenjang'),
            'nama_mitra'     => $this->request->getPost('nama_mitra'),
            'kategori'       => $this->request->getPost('kategori'),
            'alamat'         => $this->request->getPost('alamat'),
            'kontak_person'  => $this->request->getPost('kontak_person'),
            'no_telp'        => $this->request->getPost('no_telp'),
            'website'        => $this->request->getPost('website'),
            'tgl_mulai'      => $this->request->getPost('tgl_mulai'),
            'tgl_akhir'      => $this->request->getPost('tgl_akhir'),
            'program'        => $programString,
            'target_capaian' => $this->request->getPost('target_capaian'),
            'deskripsi'      => $this->request->getPost('deskripsi'),
            'status'         => $this->request->getPost('status'),
        ];

        // Path Dasar: public/uploads/kerjasama/
        $uploadPath = 'uploads/kerjasama/';

        // 1. Upload Logo (Mitra)
        $fileLogo = $this->request->getFile('logo');
        if ($fileLogo && $fileLogo->isValid() && !$fileLogo->hasMoved()) {
            if ($id) {
                $old = $this->kerjasamaModel->find($id);
                if ($old && $old['logo'] && file_exists($uploadPath . 'mitra/' . $old['logo'])) {
                    unlink($uploadPath . 'mitra/' . $old['logo']);
                }
            }
            $logoName = $fileLogo->getRandomName();
            $fileLogo->move($uploadPath . 'mitra', $logoName);
            $data['logo'] = $logoName;
        }

        // 2. Upload Dokumen MOU (PDF)
        $fileMOU = $this->request->getFile('file_mou');
        if ($fileMOU && $fileMOU->isValid() && !$fileMOU->hasMoved()) {
            if ($fileMOU->getMimeType() == 'application/pdf') {
                if ($id) {
                    $old = $this->kerjasamaModel->find($id);
                    if ($old && $old['file_mou'] && file_exists($uploadPath . 'dokumen_mou/' . $old['file_mou'])) {
                        unlink($uploadPath . 'dokumen_mou/' . $old['file_mou']);
                    }
                }
                $docName = 'MOU_' . time() . '.pdf';
                $fileMOU->move($uploadPath . 'dokumen_mou', $docName);
                $data['file_mou'] = $docName;
            }
        }

        if ($id) {
            $this->kerjasamaModel->update($id, $data);
            $msg = 'Informasi diperbarui.';
        } else {
            $this->kerjasamaModel->insert($data);
            $msg = 'Mitra berhasil ditambahkan.';
        }

        return redirect()->to(base_url('app/kerjasama'))->with('success', $msg);
    }

    public function delete($id)
    {
        $item = $this->kerjasamaModel->find($id);
        if ($item) {
            $baseUploadPath = FCPATH . 'uploads/kerjasama/'; = 'uploads/kerjasama/';
            if ($item['logo'] && file_exists($uploadPath . 'mitra/' . $item['logo'])) {
                unlink($uploadPath . 'mitra/' . $item['logo']);
            }
            if ($item['file_mou'] && file_exists($uploadPath . 'dokumen_mou/' . $item['file_mou'])) {
                unlink($uploadPath . 'dokumen_mou/' . $item['file_mou']);
            }
            $this->kerjasamaModel->delete($id);
            return redirect()->to(base_url('app/kerjasama'))->with('success', 'Data dihapus.');
        }
        return redirect()->to(base_url('app/kerjasama'))->with('error', 'Gagal menghapus.');
    }
}