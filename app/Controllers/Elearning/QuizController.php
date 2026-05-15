<?php

namespace App\Controllers\Elearning;

use App\Controllers\BaseController;
use App\Models\Elearning\CourseModel;
use App\Models\Elearning\TopicModel;

class QuizController extends BaseController
{
    protected $courseModel;
    protected $topicModel;
    protected $db;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->topicModel  = new TopicModel();
        $this->db          = \Config\Database::connect();
        helper(['form', 'url']);
    }

    /**
     * Tampilan form pembuatan kuis baru (Langkah 1)
     */
    public function create($id_kelas)
    {
        $course = $this->courseModel->find($id_kelas);
        if (!$course) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Kelas tidak ditemukan.");
        }

        $data = [
            'title'          => 'Buat Kuis Baru',
            'id_kelas'       => $id_kelas,
            'nama_kelas'     => $course['nama_kelas'],
            'mata_pelajaran' => $course['mata_pelajaran'] ?? 'Umum',
            'topics'         => $this->topicModel->where('id_kelas', $id_kelas)->findAll(),
        ];

        return view('elearning/quiz_create', $data);
    }

    /**
     * Proses simpan header kuis
     */
    public function store()
    {
        $id_kelas = $this->request->getPost('id_kelas');
        
        $rules = [
            'judul'    => 'required|min_length[5]',
            'id_kelas' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Judul kuis minimal 5 karakter.');
        }

        $dataInsert = [
            'id_kelas'     => $id_kelas,
            'id_topic'     => $this->request->getPost('id_topic') ?: null,
            'judul'        => $this->request->getPost('judul'),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'durasi_menit' => $this->request->getPost('durasi_menit') ?? 0,
            'deadline'     => $this->request->getPost('deadline') ?: null,
            // Fitur Baru: Pengaturan Acak
            'acak_soal'    => $this->request->getPost('acak_soal') ? 1 : 0,
            'acak_jawaban' => $this->request->getPost('acak_jawaban') ? 1 : 0,
            'is_published' => 0, 
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($this->db->table('el_quizzes')->insert($dataInsert)) {
            $quizId = $this->db->insertID();
            return redirect()->to(base_url("app/elearning/quiz/questions/$quizId"))
                             ->with('success', 'Header kuis berhasil disimpan.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan header kuis.');
    }

    /**
     * Tampilan editor butir soal + Bank Soal (Langkah 2)
     */
    public function questions($id_quiz)
    {
        $quiz = $this->db->table('el_quizzes')->where('id', $id_quiz)->get()->getRowArray();
        if (!$quiz) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Kuis tidak ditemukan.");
        }

        // Fitur Bank Soal: Ambil soal dari kuis lain (akumulasi)
        $bankSoal = $this->db->table('el_questions')
                             ->select('el_questions.*, el_quizzes.judul as kuis_asal')
                             ->join('el_quizzes', 'el_quizzes.id = el_questions.id_quiz')
                             ->where('id_quiz !=', $id_quiz)
                             ->orderBy('el_questions.created_at', 'DESC')
                             ->get()->getResultArray();

        $data = [
            'title'     => 'Kelola Soal: ' . $quiz['judul'],
            'quiz'      => $quiz,
            'id_kelas'  => $quiz['id_kelas'],
            'questions' => $this->db->table('el_questions')->where('id_quiz', $id_quiz)->get()->getResultArray(),
            'bank_soal' => $bankSoal
        ];

        return view('elearning/quiz_questions', $data);
    }

    /**
     * Fitur Import dari Bank Soal
     */
    public function import_bank()
    {
        $id_quiz = $this->request->getPost('id_quiz');
        $selected_ids = $this->request->getPost('question_ids');

        if(empty($selected_ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu soal dari bank soal.');
        }

        $successCount = 0;
        foreach ($selected_ids as $id) {
            $soal = $this->db->table('el_questions')->where('id', $id)->get()->getRowArray();
            if ($soal) {
                unset($soal['id']); // Hapus ID lama agar ter-auto increment baru
                $soal['id_quiz']    = $id_quiz;
                $soal['created_at'] = date('Y-m-d H:i:s');
                $soal['updated_at'] = date('Y-m-d H:i:s');
                
                if ($this->db->table('el_questions')->insert($soal)) {
                    $successCount++;
                }
            }
        }

        return redirect()->back()->with('success', "$successCount soal berhasil diimport ke kuis ini.");
    }

    /**
     * Proses simpan butir soal baru
     */
    public function add_question()
    {
        $id_quiz = $this->request->getPost('id_quiz');
        
        $data = [
            'id_quiz'       => $id_quiz,
            'pertanyaan'    => $this->request->getPost('pertanyaan'),
            'tipe_soal'     => $this->request->getPost('tipe_soal') ?? 'pg',
            'opsi_a'        => $this->request->getPost('opsi_a'),
            'opsi_b'        => $this->request->getPost('opsi_b'),
            'opsi_c'        => $this->request->getPost('opsi_c'),
            'opsi_d'        => $this->request->getPost('opsi_d'),
            'opsi_e'        => $this->request->getPost('opsi_e'),
            'jawaban_benar' => $this->request->getPost('jawaban_benar'),
            'bobot_nilai'   => $this->request->getPost('bobot_nilai') ?? 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s')
        ];

        if ($this->db->table('el_questions')->insert($data)) {
            return redirect()->back()->with('success', 'Soal berhasil ditambahkan.');
        }
        return redirect()->back()->with('error', 'Gagal menambahkan soal.');
    }

    /**
     * Menghapus butir soal
     */
    public function delete_question($id)
    {
        if ($this->db->table('el_questions')->delete(['id' => $id])) {
            return redirect()->back()->with('success', 'Soal berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Gagal menghapus soal.');
    }

    /**
     * Publikasi kuis
     */
    public function publish($id)
    {
        $quiz = $this->db->table('el_quizzes')->where('id', $id)->get()->getRowArray();
        if (!$quiz) return redirect()->back()->with('error', 'Kuis tidak ditemukan.');

        $newStatus = ($quiz['is_published'] == 1) ? 0 : 1;
        $this->db->table('el_quizzes')->where('id', $id)->update(['is_published' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')]);

        $msg = $newStatus ? 'Kuis berhasil dipublikasikan.' : 'Kuis dikembalikan ke draf.';
        return redirect()->to(base_url('app/elearning/classwork/'.$quiz['id_kelas']))->with('success', $msg);
    }
}