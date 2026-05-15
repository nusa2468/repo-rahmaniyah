<?php

namespace App\Models\Sapras;

use CodeIgniter\Model;

class BaseSaprasModel extends Model
{
    // Konfigurasi Timestamps (Wajib dipertahankan agar created_at otomatis terisi)
    protected $useTimestamps = true; 
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Daftar Jenjang Valid
    protected $allowedJenjang = ['SD', 'SMP', 'SMA'];

    /**
     * Scope data berdasarkan Jenjang/Unit dengan handling aman
     */
    public function byJenjang(?string $kodeJenjang)
    {
        // Jika kode jenjang kosong atau tidak valid, anggap sebagai Global/Yayasan (return all)
        if (
            empty($kodeJenjang) ||
            ! in_array($kodeJenjang, $this->allowedJenjang)
        ) {
            return $this; 
        }

        // Gunakan nama tabel eksplisit untuk menghindari ambigu saat JOIN
        return $this->where($this->table . '.kode_jenjang', $kodeJenjang);
    }

    /**
     * Scope khusus untuk Yayasan melihat semua unit
     */
    public function yayasanScope()
    {
        return $this->whereIn(
            $this->table . '.kode_jenjang',
            $this->allowedJenjang
        );
    }
}