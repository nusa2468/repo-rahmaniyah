<?php

namespace App\Models\Elearning;

use CodeIgniter\Model;

class TopicModel extends Model
{
    protected $table            = 'el_topics';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Aktifkan Soft Deletes sesuai Schema
    protected $useSoftDeletes   = true;

    // Sesuaikan dengan schema terbaru (id_kelas menggantikan id_course, plus timestamps)
    protected $allowedFields    = [
        'id_kelas',   
        'nama_topik', 
        'created_at', 'updated_at', 'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mendapatkan daftar topik di kelas tertentu dengan validasi unit (opsional)
     */
    public function getByCourse($classId, $jenjang = null)
    {
        $builder = $this->select('el_topics.*')
                        ->join('el_courses', 'el_courses.id = el_topics.id_kelas')
                        ->where('el_topics.id_kelas', $classId)
                        ->orderBy('el_topics.created_at', 'ASC');

        // Filter jenjang hanya jika parameter diberikan
        if ($jenjang) {
            $builder->where('el_courses.kode_jenjang', $jenjang);
        }

        return $builder->findAll();
    }
}