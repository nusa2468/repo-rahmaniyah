<?php

namespace App\Models\Sapras;

use CodeIgniter\Model;

class AsetKategoriModel extends Model
{
    protected $table            = 'aset_kategori';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Master kategori tidak di soft-delete

    protected $allowedFields = [
        'kode_jenjang',
        'kode_kategori',
        'nama_kategori',
        'tipe_aset'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id'            => 'permit_empty|is_natural_no_zero', // <--- FIX ERROR PLACEHOLDER {id}
        'kode_jenjang'  => 'required|max_length[10]',
        'kode_kategori' => 'required|max_length[20]|is_unique[aset_kategori.kode_kategori,id,{id}]',
        'nama_kategori' => 'required|max_length[255]',
        'tipe_aset'     => 'required|in_list[Bangunan/Tanah,Elektronik,Furniture,Kendaraan,Lainnya]'
    ];

    /**
     * Memfilter Kategori berdasarkan Unit Jenjang
     */
    public function getPaginated(?string $kodeJenjang, int $perPage = 10)
    {
        $builder = $this->builder();
        if (!empty($kodeJenjang) && strtoupper($kodeJenjang) !== 'GLOBAL') {
            $builder->where('kode_jenjang', $kodeJenjang);
        }
        return $this->orderBy('nama_kategori', 'ASC')->paginate($perPage);
    }
}