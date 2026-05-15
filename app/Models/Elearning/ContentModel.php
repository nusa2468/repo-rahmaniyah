<?php

namespace App\Models\Elearning;

use CodeIgniter\Model;

class ContentModel extends Model
{
    protected $table            = 'el_contents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Aktifkan Soft Deletes karena ada kolom deleted_at
    protected $useSoftDeletes   = true; 
    
    protected $allowedFields    = [
        'id_kelas',      // Sesuai dengan migrasi terbaru (bukan id_course)
        'id_topic', 
        'tipe',          // 'materi' atau 'tugas'
        'judul', 
        'isi_teks',      // Sesuai schema (sebelumnya deskripsi)
        'file_lampiran', // Sesuai schema (sebelumnya file_path)
        'deadline',      // Sesuai schema (sebelumnya due_date)
        'poin_max',
        'created_at', 'updated_at', 'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation Rules
    protected $validationRules = [
        'id_kelas' => 'required|integer',
        'judul'    => 'required|min_length[3]',
        'tipe'     => 'required|in_list[materi,tugas]',
    ];

    /**
     * Helper untuk mengambil konten berdasarkan topik
     */
    public function getByTopic($topicId)
    {
        return $this->where('id_topic', $topicId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Helper untuk mengambil semua tugas di kelas tertentu yang belum deadline
     */
    public function getActiveAssignments($classId)
    {
        return $this->where('id_kelas', $classId)
                    ->where('tipe', 'tugas')
                    ->where('deadline >=', date('Y-m-d H:i:s'))
                    ->orderBy('deadline', 'ASC')
                    ->findAll();
    }
}