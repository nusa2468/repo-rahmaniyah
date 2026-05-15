<?php

namespace App\Models\Sapras;

class RuanganModel extends BaseSaprasModel
{
    protected $table            = 'sapras_ruangan';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'kode_jenjang',
        'id_gedung',
        'nama',
        'kapasitas',
        'keterangan'
    ];

    protected $validationRules = [
        'kode_jenjang' => 'required|max_length[10]',
        'id_gedung'    => 'required|is_not_unique[sapras_gedung.id]',
        'nama'         => 'required|min_length[3]',
        'kapasitas'    => 'required|numeric',
    ];

    /**
     * Join dengan tabel Gedung untuk menampilkan nama gedung
     * UPDATE: Parameter kodeJenjang sekarang nullable (?string)
     * UPDATE: Menggunakan byJenjang() dari BaseSaprasModel untuk handle scope Global
     */
    public function getPaginatedWithGedung(?string $kodeJenjang, int $perPage = 10)
    {
        return $this->byJenjang($kodeJenjang) // Handle filter otomatis (Null = All)
                    ->select('sapras_ruangan.*, sapras_gedung.nama AS nama_gedung')
                    ->join('sapras_gedung', 'sapras_gedung.id = sapras_ruangan.id_gedung', 'left')
                    ->orderBy('sapras_ruangan.id', 'DESC')
                    ->paginate($perPage);
    }
}