<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Komponen Gaji (Enterprise Edition)
 * Mengelola komponen pendapatan (1) dan potongan (2) gaji.
 * Fitur: Multi-Unit Scope (Jenjang), Pagination Builder, & Callbacks Data.
 */
class KomponenGajiModel extends Model
{
    protected $table            = 'komponen_gaji';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Field yang boleh diisi melalui insert/update massal.
     */
    protected $allowedFields = [
        'kode_komponen',
        'kode_jenjang',
        'nama_komponen',
        'tipe',           // 1: Pendapatan, 2: Potongan
        'metode_hitung',  // fixed, variabel
        'nominal_default',
        'keterangan',
        'is_default',
        'is_aktif'
    ];

    /**
     * Aturan Validasi.
     */
    protected $validationRules = [
        'id'              => 'permit_empty|is_natural_no_zero',
        'kode_komponen'   => 'required|regex_match[/^[A-Z0-9_]+$/]|is_unique[komponen_gaji.kode_komponen,id,{id}]|max_length[20]',
        'kode_jenjang'    => 'required|max_length[20]',
        'nama_komponen'   => 'required|min_length[3]|max_length[100]',
        'tipe'            => 'required|in_list[1,2]',
        'metode_hitung'   => 'required|in_list[fixed,variabel]',
        'nominal_default' => 'required|decimal|greater_than_equal_to[0]',
        'keterangan'      => 'permit_empty|max_length[255]',
        'is_default'      => 'permit_empty|in_list[0,1]',
        'is_aktif'        => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'kode_komponen' => [
            'required'    => 'Kode komponen wajib diisi.',
            'is_unique'   => 'Kode komponen sudah terdaftar.',
            'regex_match' => 'Format kode: Huruf besar, Angka, dan Underscore saja.',
        ],
        'kode_jenjang' => [
            'required'    => 'Unit jenjang wajib ditentukan.',
        ],
        'nominal_default' => [
            'greater_than_equal_to' => 'Nominal tidak boleh negatif.',
        ],
    ];

    protected $beforeInsert = ['sanitizeData'];
    protected $beforeUpdate = ['sanitizeData'];

    protected function sanitizeData(array $data): array
    {
        if (isset($data['data'])) {
            if (isset($data['data']['is_default'])) {
                $data['data']['is_default'] = ($data['data']['is_default'] == 'on' || $data['data']['is_default'] == 1) ? 1 : 0;
            } else {
                if ($this->db->table($this->table)->where($this->primaryKey, $data['id'] ?? 0)->countAllResults() == 0) {
                    $data['data']['is_default'] = 0;
                }
            }

            if (isset($data['data']['is_aktif'])) {
                $data['data']['is_aktif'] = ($data['data']['is_aktif'] == 'on' || $data['data']['is_aktif'] == 1) ? 1 : 0;
            } else {
                if ($this->db->table($this->table)->where($this->primaryKey, $data['id'] ?? 0)->countAllResults() == 0) {
                    $data['data']['is_aktif'] = 1; 
                }
            }
        }
        return $data;
    }

    public function getKomponenBuilder(?string $kode_jenjang = null, ?string $search = null)
    {
        $builder = $this->select('komponen_gaji.*, js.nama_jenjang as unit_sekolah');
        $builder->join('jenjang_sekolah js', 'js.kode_jenjang = komponen_gaji.kode_jenjang', 'left');
        $builder->where('komponen_gaji.deleted_at', null);

        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('komponen_gaji.kode_jenjang', $kode_jenjang);
        }

        if (!empty($search)) {
            $builder->groupStart()
                    ->like('komponen_gaji.nama_komponen', $search)
                    ->orLike('komponen_gaji.kode_komponen', $search)
                    ->groupEnd();
        }

        return $builder->orderBy('komponen_gaji.tipe', 'ASC')
                       ->orderBy('komponen_gaji.nama_komponen', 'ASC');
    }

    public function getStats(?string $kode_jenjang = null): array
    {
        $builder = $this->db->table($this->table)->where('deleted_at', null);

        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('kode_jenjang', $kode_jenjang);
        }

        $all = $builder->get()->getResultArray();

        $stats = [
            'total'      => count($all),
            'pendapatan' => 0,
            'potongan'   => 0,
            'default'    => 0,
            'non_aktif'  => 0
        ];

        foreach ($all as $item) {
            if ($item['tipe'] == 1) $stats['pendapatan']++;
            if ($item['tipe'] == 2) $stats['potongan']++;
            if ($item['is_default'] == 1) $stats['default']++;
            if ($item['is_aktif'] == 0) $stats['non_aktif']++;
        }

        return $stats;
    }
}