<?php

namespace App\Models\Sapras;

class PeralatanModel extends BaseSaprasModel
{
    protected $table            = 'sapras_peralatan';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'kode_jenjang',
        'nama',
        'kondisi',
        'jumlah',
        'keterangan'
    ];

    protected $validationRules = [
        'kode_jenjang' => 'required|max_length[10]',
        'nama'         => 'required|min_length[3]',
        'kondisi'      => 'required|in_list[Baik,Rusak Ringan,Rusak Berat]',
        'jumlah'       => 'required|integer|greater_than_equal_to[0]',
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