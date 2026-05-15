<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseUnitModel extends Model
{
    // Hook otomatis CI4 untuk filter data
    protected $beforeFind   = ['applyUnitScope'];
    protected $beforeInsert = ['setUnitScope'];

    /**
     * Otomatis memfilter data setiap kali melakukan query (findAll, find, first, dll)
     */
    protected function applyUnitScope(array $data)
    {
        $kodeJenjang = session()->get('kode_jenjang');

        // Jika user bukan GLOBAL/SUPERADMIN, paksa filter berdasarkan unitnya
        if ($kodeJenjang && $kodeJenjang !== 'GLOBAL') {
            $this->where($this->table . '.kode_jenjang', $kodeJenjang);
        }

        return $data;
    }

    /**
     * Otomatis mengisi kolom kode_jenjang saat insert data baru
     */
    protected function setUnitScope(array $data)
    {
        $kodeJenjang = session()->get('kode_jenjang');

        // Jika Admin Unit input data, paksa kode_jenjang mengikuti unit si admin
        if ($kodeJenjang && $kodeJenjang !== 'GLOBAL') {
            $data['data']['kode_jenjang'] = $kodeJenjang;
        }

        return $data;
    }
}