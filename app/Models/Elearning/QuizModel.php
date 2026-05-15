<?php

namespace App\Models\Elearning;

use CodeIgniter\Model;

class QuizModel extends Model
{
    protected $table            = 'el_quizzes';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'id_course', 'id_topic', 'judul', 'deskripsi', 
        'durasi_menit', 'deadline', 'is_published'
    ];

    protected $useTimestamps = true;

    /**
     * Mendapatkan kuis dengan filter unit sekolah
     */
    public function getQuizzesByUnit($courseId, $jenjang)
    {
        return $this->select('el_quizzes.*')
                    ->join('el_courses', 'el_courses.id = el_quizzes.id_course')
                    ->where('el_courses.kode_jenjang', $jenjang)
                    ->where('el_quizzes.id_course', $courseId)
                    ->findAll();
    }
}