<?php

namespace App\Models\Elearning;

use CodeIgniter\Model;

class QuestionModel extends Model
{
    protected $table            = 'el_questions';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'id_quiz', 'pertanyaan', 'tipe_soal', 
        'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 
        'jawaban_benar', 'bobot_nilai'
    ];

    /**
     * Memastikan soal diambil dari kuis yang benar milik unit yang sah
     */
    public function getQuestionsSecure($quizId, $jenjang)
    {
        return $this->select('el_questions.*')
                    ->join('el_quizzes', 'el_quizzes.id = el_questions.id_quiz')
                    ->join('el_courses', 'el_courses.id = el_quizzes.id_course')
                    ->where('el_courses.kode_jenjang', $jenjang)
                    ->where('el_questions.id_quiz', $quizId)
                    ->findAll();
    }
}