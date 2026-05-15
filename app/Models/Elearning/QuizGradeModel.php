<?php

namespace App\Models\Elearning;

use CodeIgniter\Model;

class QuizGradeModel extends Model
{
    protected $table            = 'el_quiz_grades';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'id_quiz', 'id_siswa', 'jawaban_user', 
        'nilai_total', 'status', 'started_at', 'finished_at'
    ];

    /**
     * Menampilkan daftar nilai kuis per unit sekolah
     */
    public function getGradesByUnit($quizId, $jenjang)
    {
        return $this->select('el_quiz_grades.*')
                    ->join('el_quizzes', 'el_quizzes.id = el_quiz_grades.id_quiz')
                    ->join('el_courses', 'el_courses.id = el_quizzes.id_course')
                    ->where('el_courses.kode_jenjang', $jenjang)
                    ->where('el_quiz_grades.id_quiz', $quizId)
                    ->findAll();
    }
}