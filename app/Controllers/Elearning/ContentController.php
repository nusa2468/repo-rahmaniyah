<?php

namespace App\Controllers\Elearning;

use App\Controllers\BaseController;
use App\Models\Elearning\ContentModel;
use App\Models\Elearning\CourseModel;

class ContentController extends BaseController
{
    protected $contentModel;
    protected $courseModel;

    public function __construct()
    {
        $this->contentModel = new ContentModel();
        $this->courseModel = new CourseModel();
    }

    public function saveMaterial()
    {
        $jenjang = session()->get('kode_jenjang');
        $courseId = $this->request->getPost('id_kelas');

        // Validasi Unit/Jenjang
        // Jika user adalah superadmin/global, skip filter jenjang
        $role = session()->get('role');
        $isSuperUser = ($role === 'superadmin' || $jenjang === 'GLOBAL');

        if ($isSuperUser) {
            $course = $this->courseModel->find($courseId);
        } else {
            // Gunakan 'filterJenjang' sesuai update CourseModel sebelumnya
            $course = $this->courseModel->filterJenjang($jenjang)->find($courseId);
        }

        if (!$course) {
            return redirect()->back()->with('error', 'Akses ditolak atau kelas tidak ditemukan.');
        }

        // Upload File
        $file = $this->request->getFile('file_lampiran');
        $fileName = null;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move('uploads/elearning/materials', $fileName);
        }

        // Simpan Data
        $dataToSave = [
            'id_kelas'      => $courseId,
            'id_topic'      => $this->request->getPost('id_topic') ?: null, // Handle jika kosong
            'tipe'          => $this->request->getPost('tipe'), // materi / tugas
            'judul'         => $this->request->getPost('judul'),
            'isi_teks'      => $this->request->getPost('isi_teks'),
            'file_lampiran' => $fileName,
            'deadline'      => $this->request->getPost('deadline') ?: null, // Handle jika kosong
            'poin_max'      => $this->request->getPost('poin_max') ?? 100,
        ];

        if ($this->contentModel->save($dataToSave)) {
            return redirect()->to("app/elearning/classwork/$courseId")->with('success', 'Konten berhasil dipublikasikan.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan konten.');
        }
    }
}