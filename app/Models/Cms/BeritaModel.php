<?php

namespace App\Models\Cms;

use CodeIgniter\Model;

class BeritaModel extends Model
{
    protected $table            = 'berita';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $useSoftDeletes   = true; 

    protected $allowedFields    = [
        'kode_jenjang', 'judul', 'slug', 'konten', 'gambar', 
        'status', 'id_penulis', 
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mengambil berita dengan filter jenjang dan join author (id_penulis)
     */
    public function getBeritaWithAuthor($jenjang = null)
    {
        $builder = $this->db->table($this->table); 
        
        // FIX FATAL ERROR: Mengganti users.fullname menjadi users.nama_lengkap
        $builder->select('berita.*, users.username as author_name, users.nama_lengkap as author_fullname')
                ->join('users', 'users.id = berita.id_penulis', 'left')
                ->groupStart()
                    ->where('berita.status', 'Published')
                    ->orWhere('berita.status', 'published')
                ->groupEnd()
                ->where('berita.deleted_at', null);

        // Logic Filter Scope Jenjang
        if ($jenjang && strtoupper($jenjang) !== 'GLOBAL') {
            $builder->groupStart()
                    ->where('berita.kode_jenjang', $jenjang)
                    ->orWhere('berita.kode_jenjang', null)
                    ->orWhere('berita.kode_jenjang', 'Global')
                    ->orWhere('berita.kode_jenjang', 'GLOBAL')
                    ->groupEnd();
        }

        return $builder->orderBy('berita.created_at', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    /**
     * Mendapatkan detail berita berdasarkan slug
     */
    public function getBeritaBySlug($slug)
    {
        $builder = $this->db->table($this->table);

        // FIX FATAL ERROR: Mengganti users.fullname menjadi users.nama_lengkap
        return $builder->select('berita.*, users.username as author_name, users.nama_lengkap as author_fullname')
                    ->join('users', 'users.id = berita.id_penulis', 'left')
                    ->where('berita.slug', $slug)
                    ->groupStart()
                        ->where('berita.status', 'Published')
                        ->orWhere('berita.status', 'published')
                    ->groupEnd()
                    ->where('berita.deleted_at', null)
                    ->get()
                    ->getRowArray();
    }
}