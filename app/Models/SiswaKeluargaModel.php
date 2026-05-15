<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola data informasi keluarga siswa (ayah, ibu, wali, dll.).
 * Disimpan di tabel 'siswa_keluarga'.
 */
class SiswaKeluargaModel extends Model
{
    protected $table            = 'siswa_keluarga';
    protected $primaryKey       = 'id'; 
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Dipertahankan 'array' karena ini adalah list keluarga
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Daftar field disesuaikan PERSIS dengan kolom yang ada di screenshot tabel Anda
    protected $allowedFields = [
        'id_siswa',
        'hubungan', // enum('Ayah', 'Ibu', 'Wali', 'Saudara', 'Lainnya')
        'nama_lengkap', // Menyimpan nama Ayah/Ibu/Wali
        'nik',
        'pekerjaan', // Menyimpan pekerjaan Ayah/Ibu/Wali
        'pendidikan',
        'no_telepon',
        'penghasilan',
        'alamat',
        'is_wali', // tinyint(1)
        // 'created_at' dan 'updated_at' ditangani oleh useTimestamps
    ];

    // Dates
    protected $useTimestamps = true; 
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    
    /**
     * Mengambil semua data keluarga untuk siswa tertentu.
     *
     * @param int $idSiswa ID Siswa
     * @return array
     */
    public function getKeluargaBySiswa(int $idSiswa): array
    {
        return $this->where('id_siswa', $idSiswa)->findAll();
    }
}