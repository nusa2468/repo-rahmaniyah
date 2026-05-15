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
     * UPDATE: Menggunakan db->table() langsung untuk menghindari interferensi query dari Controller
     */
    public function getBeritaWithAuthor($jenjang = null)
    {
        // PENTING: Gunakan $this->db->table(...) bukan $this->select(...)
        // Ini membuat builder baru yang "bersih" dari query where('jenjang') yang mungkin nyangkut dari Controller
        $builder = $this->db->table($this->table); 
        
        $builder->select('berita.*, users.username as author_name, users.fullname as author_fullname')
                ->join('users', 'users.id = berita.id_penulis', 'left')
                ->where('berita.status', 'published')
                ->where('berita.deleted_at', null); // Manual check soft delete karena pakai db->table

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
        // Gunakan teknik isolasi yang sama
        $builder = $this->db->table($this->table);

        return $builder->select('berita.*, users.username as author_name, users.fullname as author_fullname')
                    ->join('users', 'users.id = berita.id_penulis', 'left')
                    ->where('berita.slug', $slug)
                    ->where('berita.status', 'published')
                    ->where('berita.deleted_at', null)
                    ->get()
                    ->getRowArray(); // getRowArray() setara dengan first() untuk result array
    }
}