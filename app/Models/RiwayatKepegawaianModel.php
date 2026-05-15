<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Riwayat Kepegawaian
 * Menangani arsip SK, Kenaikan Pangkat, dan Jabatan Fungsional.
 */
class RiwayatKepegawaianModel extends Model
{
    protected $table            = 'riwayat_kepegawaian';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'id_pegawai', 
        'jenis_sk', 
        'no_sk', 
        'tanggal_sk', 
        'tmt_sk', 
        'masa_kerja_tahun', 
        'masa_kerja_bulan',
        'status_kepegawaian', 
        'pangkat_golongan', 
        'jabatan_fungsional',
        'is_aktif'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validasi Dasar
    protected $validationRules = [
        'id_pegawai' => 'required|integer',
        'no_sk'      => 'required|max_length[100]',
        'tmt_sk'     => 'required|valid_date',
    ];

    /**
     * Set satu SK menjadi aktif dan non-aktifkan yang lain untuk pegawai tersebut.
     */
    public function setActiveSK($id_pegawai, $id_sk_aktif)
    {
        // Reset semua ke 0
        $this->where('id_pegawai', $id_pegawai)
             ->set(['is_aktif' => 0])
             ->update();
             
        // Set yang dipilih ke 1
        return $this->update($id_sk_aktif, ['is_aktif' => 1]);
    }
    
    /**
     * Ambil SK aktif saat ini untuk pegawai tertentu
     */
    public function getActiveSK($id_pegawai)
    {
        return $this->where('id_pegawai', $id_pegawai)
                    ->where('is_aktif', 1)
                    ->first();
    }
}