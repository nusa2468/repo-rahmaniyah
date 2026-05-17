<?php

namespace App\Models\Cms;

use CodeIgniter\Model;

class PengumumanModel extends Model
{
    protected $table            = 'pengumuman';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'kode_jenjang', 'judul', 'slug', 'konten', 'lampiran', 
        'status', 'id_penulis', 'is_pinned',
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mengambil pengumuman dengan filter jenjang dan join author (id_penulis)
     */
    public function getPengumumanWithAuthor($jenjang = null, $limit = 0)
    {
        // PENTING: Gunakan $this->db->table(...) untuk isolasi Query
        $builder = $this->db->table($this->table); 
        
        // FIX FATAL ERROR: Mengganti users.fullname menjadi users.nama_lengkap
        $builder->select('pengumuman.*, users.username as author_name, users.nama_lengkap as author_fullname')
                ->join('users', 'users.id = pengumuman.id_penulis', 'left')
                ->groupStart()
                    ->where('pengumuman.status', 'Published')
                    ->orWhere('pengumuman.status', 'published')
                ->groupEnd()
                ->where('pengumuman.deleted_at', null);

        // Logic Filter Scope Jenjang
        if ($jenjang && strtoupper($jenjang) !== 'GLOBAL') {
            $builder->groupStart()
                    ->where('pengumuman.kode_jenjang', $jenjang)
                    ->orWhere('pengumuman.kode_jenjang', null)
                    ->orWhere('pengumuman.kode_jenjang', 'Global')
                    ->orWhere('pengumuman.kode_jenjang', 'GLOBAL')
                    ->groupEnd();
        }

        $builder->orderBy('pengumuman.created_at', 'DESC');
        
        // Limit digunakan untuk Dashboard/Widget Landing Page
        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Mendapatkan detail pengumuman berdasarkan slug
     */
    public function getPengumumanBySlug($slug)
    {
        $builder = $this->db->table($this->table);

        // FIX FATAL ERROR: Mengganti users.fullname menjadi users.nama_lengkap
        return $builder->select('pengumuman.*, users.username as author_name, users.nama_lengkap as author_fullname')
                    ->join('users', 'users.id = pengumuman.id_penulis', 'left')
                    ->where('pengumuman.slug', $slug)
                    ->groupStart()
                        ->where('pengumuman.status', 'Published')
                        ->orWhere('pengumuman.status', 'published')
                    ->groupEnd()
                    ->where('pengumuman.deleted_at', null)
                    ->get()
                    ->getRowArray();
    }
}