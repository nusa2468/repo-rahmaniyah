<?php

namespace App\Models\Cms;

use CodeIgniter\Model;

class AlbumModel extends Model
{
    // Sesuai Migration: table 'album_foto'
    protected $table            = 'album_foto'; 
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    // Sesuai Migration: judul (bukan nama_album), deskripsi, cover
    protected $allowedFields    = [
        'kode_jenjang', 'judul', 'slug', 'deskripsi', 
        'cover', 'status', 
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getAlbumsByJenjang($jenjang = null)
    {
        $builder = $this->db->table($this->table);
        
        $builder->where('status', 'publik') // Sesuai ENUM migrasi ['publik', 'internal']
                ->where('deleted_at', null);
        
        if ($jenjang && strtoupper($jenjang) !== 'GLOBAL') {
            $builder->groupStart()
                    ->where('kode_jenjang', $jenjang)
                    ->orWhere('kode_jenjang', null)
                    ->orWhere('kode_jenjang', 'Global')
                    ->orWhere('kode_jenjang', 'GLOBAL')
                    ->groupEnd();
        }

        return $builder->orderBy('created_at', 'DESC')->get()->getResultArray();
    }
}