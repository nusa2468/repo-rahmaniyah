<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Riwayat Pendidikan
 * Menangani data riwayat pendidikan formal pegawai.
 * Relasi: id_pegawai -> pegawai.id
 */
class RiwayatPendidikanModel extends Model
{
    protected $table            = 'riwayat_pendidikan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Data sejarah biasanya dihapus permanen jika salah
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'id_pegawai', 
        'jenjang',      // SD, SMP, SMA, D3, S1, S2, S3
        'nama_sekolah', // Nama Institusi
        'jurusan',      // Jurusan/Prodi
        'tahun_masuk', 
        'tahun_lulus', 
        'nilai_akhir'   // IPK / NEM
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    // Validasi sederhana
    protected $validationRules = [
        'id_pegawai'   => 'required|integer',
        'jenjang'      => 'required|max_length[20]',
        'nama_sekolah' => 'required|max_length[255]',
        'tahun_lulus'  => 'required|valid_date[Y]',
    ];
}