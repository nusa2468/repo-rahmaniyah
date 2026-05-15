<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model GrupSiswa (Rombongan Belajar)
 * Sinkronisasi 100% dengan JadwalPelajaranModel dan Scope Unit (kode_jenjang).
 * Menangani kelompok siswa di dalam kelas struktural per Jenjang (SD/SMP/SMA).
 */
class GrupSiswaModel extends Model
{
    protected $table            = 'grup_siswa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    /**
     * SINKRONISASI KRITIS: 
     * Kolom id_kelas di sini merujuk ke tabel kelas (Struktural).
     * Kolom kode_jenjang digunakan untuk filter scope unit.
     */
    protected $allowedFields    = [
        'kode_jenjang',   // Unit Scope (SD/SMP/SMA)
        'id_kelas',       // ID Kelas Struktural (Contoh: Kelas 1, Kelas 7, dll)
        'nama_grup',      // Nama Rombel (Contoh: Grup A, Reguler, Intensif)
        'tahun_ajaran',   // Format: 2025/2026
        'keterangan'
    ];

    // Konfigurasi Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Aturan Validasi untuk Integritas Data
    protected $validationRules = [
        'kode_jenjang' => 'required|max_length[10]',
        // Pastikan ID Kelas benar-benar ada di tabel kelas
        'id_kelas'     => 'required|is_natural_no_zero|is_not_unique[kelas.id]',
        'nama_grup'    => 'required|min_length[2]|max_length[100]',
        'tahun_ajaran' => 'required|max_length[9]'
    ];

    protected $validationMessages = [
        'kode_jenjang' => [
            'required' => 'Unit (SD/SMP/SMA) wajib ditentukan.'
        ],
        'id_kelas' => [
            'required'      => 'Kelas induk struktural harus dipilih.',
            'is_not_unique' => 'Data Kelas Induk tidak ditemukan di database.'
        ],
        'nama_grup' => [
            'required' => 'Nama rombongan belajar wajib diisi.'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * SCOPE: Filter berdasarkan Unit (kode_jenjang).
     * Memudahkan sinkronisasi dengan filter unit di Controller Jadwal.
     * @return $this
     */
    public function scopeJenjang(string $kodeJenjang)
    {
        return $this->where($this->table . '.kode_jenjang', $kodeJenjang);
    }

    /**
     * Mengambil daftar Grup (Rombel) lengkap dengan data Kelas Strukturalnya.
     * Digunakan untuk dropdown pada form Jadwal Pelajaran.
     * @return array
     */
    public function getGrupWithKelas($kodeJenjang = null): array
    {
        // Menggunakan $this->select agar state model terjaga
        $this->select('grup_siswa.*, kelas.nama_kelas, kelas.kode_jenjang as jenjang_induk')
             ->join('kelas', 'kelas.id = grup_siswa.id_kelas', 'left')
             ->where('grup_siswa.deleted_at', null);

        if ($kodeJenjang && !in_array(strtoupper($kodeJenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $this->where('grup_siswa.kode_jenjang', $kodeJenjang);
        }

        return $this->orderBy('kelas.tingkat', 'ASC') // Urutkan berdasarkan tingkat kelas dulu
                    ->orderBy('kelas.nama_kelas', 'ASC')
                    ->orderBy('grup_siswa.nama_grup', 'ASC')
                    ->findAll();
    }

    /**
     * Mengambil detail satu grup beserta informasi kelasnya.
     * @return array|null
     */
    public function getGrupDetail(int $id): ?array
    {
        return $this->select('grup_siswa.*, kelas.nama_kelas')
                    ->join('kelas', 'kelas.id = grup_siswa.id_kelas', 'left')
                    ->where('grup_siswa.id', $id)
                    ->first();
    }
}