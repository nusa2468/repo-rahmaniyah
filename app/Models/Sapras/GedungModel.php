<?php

namespace App\Models\Sapras;

class GedungModel extends BaseSaprasModel
{
    protected $table            = 'sapras_gedung';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'kode_jenjang',
        'nama',
        'tahun',
        'luas',
        'keterangan'
    ];

    protected $validationRules = [
        'kode_jenjang' => 'required|max_length[10]',
        'nama'         => 'required|min_length[3]',
        'tahun'        => 'required|numeric|exact_length[4]',
        'luas'         => 'required|numeric',
    ];

    /**
     * UPDATE: Parameter kodeJenjang sekarang nullable (?string)
     */
    public function getPaginated(?string $kodeJenjang, int $perPage = 10)
    {
        return $this->byJenjang($kodeJenjang)
                    ->orderBy('id', 'DESC')
                    ->paginate($perPage);
    }
}