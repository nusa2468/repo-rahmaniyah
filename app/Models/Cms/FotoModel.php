<?php

namespace App\Models\Cms;

use CodeIgniter\Model;

class FotoModel extends Model
{
    protected $table            = 'foto';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    // UPDATE: Menambahkan 'kode_jenjang'
    protected $allowedFields    = [
        'kode_jenjang', 'id_album', 'file_foto', 'caption',
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getFotosByAlbum($idAlbum)
    {
        return $this->where('id_album', $idAlbum)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
}