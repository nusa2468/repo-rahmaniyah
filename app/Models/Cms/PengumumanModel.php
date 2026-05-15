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

    // Sesuai Migration: tanggal_berakhir, id_penulis
    protected $allowedFields    = [
        'kode_jenjang', 'judul', 'slug', 'konten', 
        'status', 'tanggal_berakhir', 'id_penulis',
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getPengumumanWithAuthor($jenjang = null)
    {
        $builder = $this->db->table($this->table);
        
        // Join ke users (asumsi tabel users ada field 'nama_lengkap')
        $builder->select('pengumuman.*, users.nama_lengkap as author_name')
                ->join('users', 'users.id = pengumuman.id_penulis', 'left')
                ->where('pengumuman.status', 'published')
                ->where('pengumuman.deleted_at', null);

        // Filter tanggal berakhir (opsional: sembunyikan jika sudah lewat)
        // $builder->where('pengumuman.tanggal_berakhir >=', date('Y-m-d'));

        if ($jenjang && strtoupper($jenjang) !== 'GLOBAL') {
            $builder->groupStart()
                    ->where('pengumuman.kode_jenjang', $jenjang)
                    ->orWhere('pengumuman.kode_jenjang', null)
                    ->orWhere('pengumuman.kode_jenjang', 'Global')
                    ->orWhere('pengumuman.kode_jenjang', 'GLOBAL')
                    ->groupEnd();
        }

        return $builder->orderBy('pengumuman.created_at', 'DESC') // pengumuman biasanya order by created
                       ->get()
                       ->getResultArray();
    }
}