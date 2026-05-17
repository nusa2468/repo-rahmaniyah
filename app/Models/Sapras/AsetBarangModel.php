<?php

namespace App\Models\Sapras;

use CodeIgniter\Model;

class AsetBarangModel extends Model
{
    protected $table            = 'aset_barang';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Aset yang dihapus masuk ke recycle bin (Soft Delete)
    protected $useSoftDeletes   = true; 

    protected $allowedFields = [
        'kode_jenjang', 
        'id_kategori', 
        'id_lokasi', 
        'id_penanggung_jawab', 
        'kode_aset', 
        'nama_aset', 
        'merk_spesifikasi', 
        'sumber_dana', 
        'status_kepemilikan',
        'tanggal_perolehan', 
        'harga_perolehan', 
        'kondisi', 
        'status_ketersediaan'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'id'                  => 'permit_empty|is_natural_no_zero', // <--- FIX ERROR PLACEHOLDER {id}
        'kode_jenjang'        => 'required|max_length[20]',
        'id_kategori'         => 'required|integer',
        'id_lokasi'           => 'permit_empty|integer',
        'kode_aset'           => 'required|max_length[100]|is_unique[aset_barang.kode_aset,id,{id}]',
        'nama_aset'           => 'required|max_length[255]',
        'sumber_dana'         => 'permit_empty|string|max_length[100]',
        'status_kepemilikan'  => 'required|in_list[Milik Sendiri,Sewa,Hibah/Wakaf,Pinjam Pakai]',
        'harga_perolehan'     => 'permit_empty|numeric',
        'kondisi'             => 'required|in_list[Baik,Rusak Ringan,Rusak Berat,Afkir/Dihapus]',
        'status_ketersediaan' => 'required|in_list[Tersedia,Dipinjam,Diperbaiki,Hilang]'
    ];

    public function getBarangBuilder(?string $kodeJenjang = null)
    {
        $builder = $this->db->table($this->table)
            ->select('
                aset_barang.*, 
                aset_kategori.nama_kategori, 
                aset_kategori.tipe_aset,
                aset_lokasi.nama_lokasi,
                pegawai.nama_lengkap as nama_penanggung_jawab
            ')
            ->join('aset_kategori', 'aset_kategori.id = aset_barang.id_kategori', 'left')
            ->join('aset_lokasi', 'aset_lokasi.id = aset_barang.id_lokasi', 'left')
            ->join('pegawai', 'pegawai.id = aset_barang.id_penanggung_jawab', 'left')
            ->where('aset_barang.deleted_at', null);

        if (!empty($kodeJenjang) && strtoupper($kodeJenjang) !== 'GLOBAL') {
            $builder->where('aset_barang.kode_jenjang', $kodeJenjang);
        }

        return $builder;
    }
}