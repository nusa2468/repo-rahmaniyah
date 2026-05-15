<?php

namespace App\Models\Cms;

use CodeIgniter\Model;

class PengumumanModel extends Model
{
    protected $table            = 'pengumuman';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Mengaktifkan Soft Deletes sesuai migrasi (deleted_at)
    protected $useSoftDeletes   = true;

    // Disesuaikan dengan field di Migration CreateCmsTables
    // Perubahan: 'isi' -> 'konten', 'id_user' -> 'id_penulis'
    protected $allowedFields    = [
        'kode_jenjang', 'judul', 'slug', 
        'konten', 'status', 'tanggal_berakhir', 
        'id_penulis', 
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mengambil pengumuman aktif dengan filter jenjang
     * Menampilkan pengumuman yang statusnya published dan belum kadaluarsa
     */
    public function getPengumumanWithAuthor($jenjang = null, $limit = 5)
    {
        $builder = $this->select('pengumuman.*, users.fullname as author_fullname')
                        ->join('users', 'users.id = pengumuman.id_penulis', 'left')
                        ->where('pengumuman.status', 'published')
                        ->groupStart() // Filter tanggal berakhir (jika ada)
                            ->where('tanggal_berakhir >=', date('Y-m-d'))
                            ->orWhere('tanggal_berakhir', null)
                        ->groupEnd();

        // Logic Filter Scope Jenjang
        if ($jenjang && $jenjang !== 'Global') {
            $builder->groupStart()
                    ->where('pengumuman.kode_jenjang', $jenjang)
                    ->orWhere('pengumuman.kode_jenjang', null) // Null = Global
                    ->orWhere('pengumuman.kode_jenjang', 'Global')
                    ->groupEnd();
        }

        return $builder->orderBy('pengumuman.created_at', 'DESC')
                       ->findAll($limit);
    }

    /**
     * Mendapatkan detail pengumuman berdasarkan slug
     */
    public function getPengumumanBySlug($slug)
    {
        return $this->select('pengumuman.*, users.fullname as author_fullname')
                    ->join('users', 'users.id = pengumuman.id_penulis', 'left')
                    ->where('pengumuman.slug', $slug)
                    ->where('pengumuman.status', 'published')
                    ->first();
    }
}