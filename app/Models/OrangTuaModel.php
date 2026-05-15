<?php

namespace App\Models;

use CodeIgniter\Model;

class OrangTuaModel extends Model
{
    protected $table = 'orang_tua';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama_lengkap', 
        'email', 
        'password', 
        'no_telepon', 
        'pekerjaan', 
        'alamat', 
        'status',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Aturan validasi untuk pendaftaran/input data
    protected $validationRules = [
        'nama_lengkap' => 'required|min_length[3]',
        'email'        => 'required|valid_email|is_unique[orang_tua.email]',
        'password'     => 'required|min_length[6]',
        'no_telepon'   => 'required|min_length[10]|max_length[15]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Maaf, email ini sudah terdaftar. Silakan gunakan email lain.',
        ],
    ];

    /**
     * Mengambil data orang tua beserta data siswa yang terkait.
     * @param int $ortuId ID Orang Tua
     * @return array|null
     */
    public function getOrangTuaWithSiswa(int $ortuId)
    {
        return $this->select('orang_tua.*, s.nama_lengkap as nama_siswa, s.nis, s.id_kelas')
                    ->join('siswa s', 's.id_orang_tua_portal = orang_tua.id', 'left')
                    ->where('orang_tua.id', $ortuId)
                    ->findAll();
    }
}