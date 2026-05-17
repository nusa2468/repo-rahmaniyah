<?php

namespace App\Models\Sapras;

use CodeIgniter\Model;

class AsetLokasiModel extends Model
{
    protected $table            = 'aset_lokasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Sesuai migration, tabel ini memiliki deleted_at
    protected $useSoftDeletes   = true; 

    protected $allowedFields = [
        'kode_jenjang', 
        'jenis_lokasi', 
        'nama_lokasi', 
        'kapasitas', 
        'keterangan'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'kode_jenjang' => 'required|max_length[20]',
        'jenis_lokasi' => 'required|in_list[Gedung,Ruang Kelas,Laboratorium,Gudang,Lainnya]',
        'nama_lokasi'  => 'required|max_length[255]',
        'kapasitas'    => 'permit_empty|integer',
        'keterangan'   => 'permit_empty|string',
    ];

    /**
     * Mengambil data lokasi berdasarkan hak akses unit (jenjang)
     */
    public function getPaginated(?string $kodeJenjang, int $perPage = 10)
    {
        $builder = $this->builder();
        if (!empty($kodeJenjang) && strtoupper($kodeJenjang) !== 'GLOBAL') {
            $builder->where('kode_jenjang', $kodeJenjang);
        }
        return $this->orderBy('jenis_lokasi', 'ASC')
                    ->orderBy('nama_lokasi', 'ASC')
                    ->paginate($perPage);
    }
}