<?php

namespace App\Models\Sapras;

use CodeIgniter\Model;

class AsetPemeliharaanModel extends Model
{
    protected $table            = 'aset_pemeliharaan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'id_aset', 
        'jenis_pemeliharaan', 
        'tanggal_mulai', 
        'tanggal_selesai', 
        'pelaksana', 
        'biaya', 
        'keterangan', 
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id_aset'            => 'required|integer',
        'jenis_pemeliharaan' => 'required|in_list[Rutin/Preventif,Perbaikan/Kerusakan]',
        'tanggal_mulai'      => 'required|valid_date',
        'tanggal_selesai'    => 'permit_empty|valid_date',
        'biaya'              => 'permit_empty|numeric',
        'status'             => 'in_list[Direncanakan,Sedang Proses,Selesai,Batal]'
    ];

    /**
     * Membangun Query Pemeliharaan.
     * Menggabungkan data Aset Barang dan letak Lokasi Aset untuk mempermudah teknisi.
     */
    public function getPemeliharaanBuilder()
    {
        return $this->db->table($this->table)
            ->select('
                aset_pemeliharaan.*, 
                aset_barang.nama_aset, 
                aset_barang.kode_aset, 
                aset_barang.kode_jenjang,
                aset_lokasi.nama_lokasi
            ')
            ->join('aset_barang', 'aset_barang.id = aset_pemeliharaan.id_aset', 'left')
            ->join('aset_lokasi', 'aset_lokasi.id = aset_barang.id_lokasi', 'left');
    }
}