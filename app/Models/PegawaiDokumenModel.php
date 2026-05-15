<?php

namespace App\Models;

use CodeIgniter\Model;

class PegawaiDokumenModel extends Model
{
    protected $table            = 'pegawai_dokumen';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; 
    
    protected $allowedFields    = [
        'id_pegawai', 'jenis_dokumen', 'nama_file', 'file_path', 'tipe_file', 'ukuran_file'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Helper: Ambil dokumen milik pegawai tertentu
    public function getDokumenByPegawai($id_pegawai)
    {
        return $this->where('id_pegawai', $id_pegawai)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}