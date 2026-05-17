<?php

namespace App\Models\Sapras;

use CodeIgniter\Model;

class AsetPeminjamanModel extends Model
{
    protected $table            = 'aset_peminjaman';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'id_aset', 
        'tipe_peminjam', 
        'id_peminjam', 
        'tanggal_pinjam', 
        'estimasi_kembali', 
        'tanggal_kembali', 
        'keperluan', 
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id_aset'          => 'required|integer',
        'tipe_peminjam'    => 'required|in_list[Pegawai,Siswa]',
        'id_peminjam'      => 'required|integer',
        'tanggal_pinjam'   => 'required|valid_date',
        'estimasi_kembali' => 'required|valid_date',
        'status'           => 'in_list[Menunggu,Dipinjam,Dikembalikan,Terlambat]'
    ];

    /**
     * Membangun Query Peminjaman.
     * Menggabungkan informasi dari Aset Barang dan Pemohon (Pegawai).
     */
    public function getPeminjamanBuilder()
    {
        return $this->db->table($this->table)
            ->select('
                aset_peminjaman.*, 
                aset_barang.nama_aset, 
                aset_barang.kode_aset,
                aset_barang.kode_jenjang,
                pegawai.nama_lengkap as nama_peminjam
            ')
            ->join('aset_barang', 'aset_barang.id = aset_peminjaman.id_aset', 'left')
            // Join ke pegawai. (Bisa dikembangkan menggunakan UNION/Join Dinamis jika peminjam adalah 'Siswa')
            ->join('pegawai', "pegawai.id = aset_peminjaman.id_peminjam AND aset_peminjaman.tipe_peminjam = 'Pegawai'", 'left');
    }
}