<?php

namespace App\Controllers\Elearning;

use App\Controllers\BaseController;
use App\Models\Elearning\QuizGradeModel;
use App\Models\Elearning\QuizModel;
use App\Models\Elearning\ContentModel; 

class ActivityController extends BaseController
{
    protected $quizGradeModel;
    protected $quizModel;
    protected $contentModel;
    protected $db;

    public function __construct()
    {
        $this->quizGradeModel = new QuizGradeModel();
        $this->quizModel      = new QuizModel();
        $this->contentModel   = new ContentModel();
        $this->db             = \Config\Database::connect();
        
        helper(['form', 'filesystem', 'date']);
    }

    /**
     * Menampilkan Laporan Nilai Kuis
     */
    public function gradeReport($quizId)
    {
        $jenjang = session()->get('kode_jenjang');
        $grades = $this->quizGradeModel->getGradesByUnit($quizId, $jenjang);

        $data = [
            'title'  => 'Laporan Nilai Kuis',
            'grades' => $grades
        ];

        return view('elearning/grade_report', $data);
    }

    /**
     * Menampilkan Detail Materi (BARU)
     * Menangani konten bertipe 'materi'
     */
    public function viewMaterial($contentId)
    {
        $content = $this->contentModel->find($contentId);

        if (!$content || $content['tipe'] !== 'materi') {
            return redirect()->back()->with('error', 'Materi tidak ditemukan.');
        }

        $data = [
            'title'   => $content['judul'],
            'content' => $content,
            'id_kelas'=> $content['id_kelas'] // Untuk tombol "Kembali"
        ];

        return view('elearning/material_detail', $data);
    }

    /**
     * Menampilkan Detail Tugas (Halaman upload)
     * Menangani konten bertipe 'tugas'
     */
    public function viewAssignment($contentId)
    {
        $content = $this->contentModel->find($contentId);
        
        if (!$content || $content['tipe'] !== 'tugas') {
            return redirect()->back()->with('error', 'Tugas tidak ditemukan.');
        }

        $userId = session()->get('user_id') ?? session()->get('id');

        // Cek apakah siswa sudah mengumpulkan sebelumnya (Pastikan tabel el_submissions ada)
        $submission = null;
        if($this->db->tableExists('el_submissions')) {
            $submission = $this->db->table('el_submissions')
                            ->where('id_content', $contentId)
                            ->where('id_siswa', $userId)
                            ->get()->getRowArray();
        }

        $data = [
            'title'      => $content['judul'],
            'content'    => $content,
            'submission' => $submission,
            'id_kelas'   => $content['id_kelas']
        ];

        return view('elearning/assignment_detail', $data);
    }

    /**
     * Proses Siswa Mengumpulkan Tugas (Upload File)
     */
    public function submitWork()
    {
        $userId = session()->get('user_id') ?? session()->get('id');
        if (!$userId) return redirect()->back()->with('error', 'Sesi berakhir.');

        $rules = [
            'id_content'      => 'required',
            'file_submission' => [
                'rules'  => 'uploaded[file_submission]|max_size[file_submission,10240]|ext_in[file_submission,pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,png,jpeg]',
                'errors' => [
                    'uploaded' => 'Anda belum memilih file.',
                    'max_size' => 'Ukuran file terlalu besar (Maks 10MB).',
                    'ext_in'   => 'Format file tidak diizinkan.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file_submission');
        $fileName = $userId . '_' . time() . '_' . $file->getRandomName();
        
        try {
            if ($file->isValid() && !$file->hasMoved()) {
                // Pastikan folder exists
                if (!is_dir('uploads/elearning/submissions')) {
                    mkdir('uploads/elearning/submissions', 0777, true);
                }
                
                $file->move('uploads/elearning/submissions', $fileName);

                $data = [
                    'id_content'   => $this->request->getPost('id_content'),
                    'id_siswa'     => $userId,
                    'file_path'    => $fileName,
                    'file_name'    => $file->getClientName(),
                    'catatan'      => $this->request->getPost('catatan'),
                    'submitted_at' => date('Y-m-d H:i:s'),
                    'status'       => 'submitted'
                ];

                $existing = $this->db->table('el_submissions')
                                     ->where('id_content', $data['id_content'])
                                     ->where('id_siswa', $userId)
                                     ->get()->getRow();

                if ($existing) {
                    $this->db->table('el_submissions')->where('id', $existing->id)->update($data);
                    $msg = 'Tugas berhasil diperbarui.';
                } else {
                    $this->db->table('el_submissions')->insert($data);
                    $msg = 'Tugas berhasil dikumpulkan.';
                }

                return redirect()->back()->with('success', $msg);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}