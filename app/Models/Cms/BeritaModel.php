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

    // Sesuai Migration: id_penulis
    protected $allowedFields    = [
        'kode_jenjang', 'judul', 'slug', 'konten', 'gambar', 
        'status', 'id_penulis', 
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getBeritaWithAuthor($jenjang = null)
    {
        $builder = $this->db->table($this->table);
        
        $builder->select('berita.*, users.username as author_username, users.nama_lengkap as author_name')
                ->join('users', 'users.id = berita.id_penulis', 'left')
                ->where('berita.status', 'published')
                ->where('berita.deleted_at', null);

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

    public function getBeritaBySlug($slug)
    {
        $builder = $this->db->table($this->table);

        return $builder->select('berita.*, users.username as author_username, users.nama_lengkap as author_name')
                    ->join('users', 'users.id = berita.id_penulis', 'left')
                    ->where('berita.slug', $slug)
                    ->where('berita.status', 'published')
                    ->where('berita.deleted_at', null)
                    ->get()
                    ->getRowArray();
    }
}