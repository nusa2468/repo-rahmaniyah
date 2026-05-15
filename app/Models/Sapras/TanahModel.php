<?php

namespace App\Models\Sapras;

class TanahModel extends BaseSaprasModel
{
    protected $table            = 'sapras_tanah';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['kode_jenjang', 'nama', 'luas', 'sertifikat', 'keterangan'];

    protected $validationRules = [
        'kode_jenjang' => 'required|max_length[10]',
        'nama'         => 'required|min_length[3]',
        'luas'         => 'required|numeric',
        'sertifikat'   => 'permit_empty|max_length[50]',
        'keterangan'   => 'permit_empty|max_length[255]',
    ];

    // FIX: Tambahkan '?' sebelum string agar menerima NULL dari Controller
    public function getPaginated(?string $kodeJenjang, int $perPage = 10)
    {
        return $this->byJenjang($kodeJenjang)
                    ->orderBy('id', 'DESC')
                    ->paginate($perPage);
    }
}