<?php

namespace App\Controllers\Elearning;

use App\Controllers\BaseController;
use App\Models\Elearning\CourseModel;
use App\Models\Elearning\EnrolmentModel;
use App\Models\NilaiModel; // Import Model Utama

class GradeController extends BaseController
{
    protected $db;

    public function __construct() {
        $this->db = \Config\Database::connect();
        helper(['text', 'url']);
    }

    public function index($id_kelas)
    {
        $courseModel = new \App\Models\Elearning\CourseModel();
        $enrolModel = new \App\Models\Elearning\EnrolmentModel();

        $course = $courseModel->find($id_kelas);
        if (!$course) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $students = $enrolModel->getStudentsInCourse($id_kelas);
        $assignments = $this->db->table('el_contents')->where(['id_kelas' => $id_kelas, 'tipe' => 'tugas'])->get()->getResultArray();
        $quizzes = $this->db->table('el_quizzes')->where(['id_kelas' => $id_kelas, 'is_published' => 1])->get()->getResultArray();

        foreach ($students as &$s) {
            $s['grades'] = ['assignments' => [], 'quizzes' => []];
            $total = 0; $count = 0;

            foreach ($assignments as $a) {
                $sub = $this->db->table('el_submissions')->where(['id_content' => $a['id'], 'id_siswa' => $s['id_siswa']])->get()->getRow();
                $val = $sub->nilai ?? null;
                $s['grades']['assignments'][$a['id']] = $val ?? '-';
                if(is_numeric($val)) { $total += $val; $count++; }
            }

            foreach ($quizzes as $q) {
                $grade = $this->db->table('el_quiz_grades')->where(['id_quiz' => $q['id'], 'id_siswa' => $s['id_siswa']])->get()->getRow();
                $val = $grade->nilai_total ?? null;
                $s['grades']['quizzes'][$q['id']] = $val ?? '-';
                if(is_numeric($val)) { $total += $val; $count++; }
            }
            $s['rata_rata'] = ($count > 0) ? round($total / $count, 2) : 0;
        }

        return view('elearning/grades', [
            'title' => 'Buku Nilai - ' . $course['nama_kelas'],
            'course' => $course,
            'id_kelas' => $id_kelas,
            'students' => $students,
            'assignments' => $assignments,
            'quizzes' => $quizzes
        ]);
    }

    /**
     * FITUR INTEGRASI: Push nilai ke NilaiModel (Akademik Utama)
     */
    public function syncToAcademic($id_kelas)
    {
        $nilaiModel = new NilaiModel();
        $courseModel = new \App\Models\Elearning\CourseModel();
        
        $course = $courseModel->find($id_kelas);
        if (!$course) return redirect()->back()->with('error', 'Kelas tidak ditemukan.');

        // Ambil ID Mata Pelajaran dari Jadwal
        $jadwal = $this->db->table('jadwal_pelajaran')->where('id', $course['id_jadwal_pelajaran'])->get()->getRow();
        if (!$jadwal) return redirect()->back()->with('error', 'Mata pelajaran tidak terhubung dengan jadwal kurikulum.');

        // Ambil Tahun Ajaran Aktif dari Session
        $idTA = session()->get('id_tahun_ajaran') ?? 1;
        $semester = session()->get('semester') ?? 'Genap';

        // Ambil Data Siswa & Rata-rata Nilai (Gunakan logika yang sama dengan index)
        // Simulasi pengambilan data rata-rata...
        $enrolModel = new \App\Models\Elearning\EnrolmentModel();
        $students = $enrolModel->getStudentsInCourse($id_kelas);

        $successCount = 0;
        foreach ($students as $s) {
            // Hitung rata-rata kuis + tugas secara real-time
            $avg = $this->_calculateAverage($id_kelas, $s['id_siswa']);

            $dataNilai = [
                'id_tahun_ajaran'   => $idTA,
                'id_kelas'          => $id_kelas,
                'id_siswa'          => $s['id_siswa'],
                'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
                'id_guru'           => $course['id_guru'],
                'semester'          => $semester,
                'kode_jenjang'      => $course['kode_jenjang'],
                'nilai_tugas'       => $avg, // Sinkron ke kolom tugas akademik
                'keterangan'        => 'Sinkronisasi otomatis dari E-Learning'
            ];

            if ($nilaiModel->saveNilaiLengkap($dataNilai)) {
                $successCount++;
            }
        }

        return redirect()->back()->with('success', "Berhasil sinkronisasi $successCount data ke Buku Nilai Utama.");
    }

    private function _calculateAverage($id_kelas, $id_siswa) {
        $qNilai = $this->db->table('el_quiz_grades')
            ->selectAvg('nilai_total', 'avg')
            ->join('el_quizzes', 'el_quizzes.id = el_quiz_grades.id_quiz')
            ->where(['el_quizzes.id_kelas' => $id_kelas, 'id_siswa' => $id_siswa])
            ->get()->getRow();

        $tNilai = $this->db->table('el_submissions')
            ->selectAvg('nilai', 'avg')
            ->join('el_contents', 'el_contents.id = el_submissions.id_content')
            ->where(['el_contents.id_kelas' => $id_kelas, 'id_siswa' => $id_siswa])
            ->get()->getRow();

        $total = 0; $divider = 0;
        if($qNilai->avg) { $total += $qNilai->avg; $divider++; }
        if($tNilai->avg) { $total += $tNilai->avg; $divider++; }

        return ($divider > 0) ? round($total / $divider, 2) : 0;
    }
}