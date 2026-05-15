<?php

namespace App\Models\Pembelajaran;

use CodeIgniter\Model;

/**
 * BankSoalModel
 * Menguruskan pangkalan data soalan bagi Kurikulum K13 & Merdeka.
 * Menyokong pengasingan data mengikut unit (Unit Scoping).
 */
class BankSoalModel extends Model
{
    protected $table            = 'pembelajaran_bank_soal';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang', 'jenis_kurikulum', 'fase', 'mata_pelajaran_id', 
        'silabus_id', 'kode_soal', 'topik', 'jenis_soal', 
        'tingkat_kesulitan', 'level_kognitif', 'pertanyaan', 
        'opsi_jawaban', 'kunci_jawaban', 'is_acak_opsi', 'bobot'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mengambil data soalan dengan penapisan unit dan kata kunci.
     */
    public function getFilteredData($keyword = null, $kodeJenjang = null)
    {
        $this->select('pembelajaran_bank_soal.*, mata_pelajaran.nama_mapel, pembelajaran_silabus.materi_pokok');
        $this->join('mata_pelajaran', 'mata_pelajaran.id = pembelajaran_bank_soal.mata_pelajaran_id', 'left');
        $this->join('pembelajaran_silabus', 'pembelajaran_silabus.id = pembelajaran_bank_soal.silabus_id', 'left');

        if ($kodeJenjang) {
            $this->where('pembelajaran_bank_soal.kode_jenjang', $kodeJenjang);
        }

        if ($keyword) {
            $this->groupStart()
                ->like('pembelajaran_bank_soal.topik', $keyword)
                ->orLike('pembelajaran_bank_soal.pertanyaan', $keyword)
                ->orLike('pembelajaran_bank_soal.kode_soal', $keyword)
            ->groupEnd();
        }

        return $this;
    }

    /**
     * Statistik soalan mengikut unit (Scoped).
     */
    public function getStatistics($kodeJenjang = null)
    {
        $builder = $this->db->table($this->table)->where('deleted_at', null);
        if ($kodeJenjang) {
            $builder->where('kode_jenjang', $kodeJenjang);
        }

        return [
            'total'   => (clone $builder)->countAllResults(),
            'pg'      => (clone $builder)->where('jenis_soal', 'PG')->countAllResults(),
            'essay'   => (clone $builder)->where('jenis_soal', 'Essay')->countAllResults(),
            'mudah'   => (clone $builder)->where('tingkat_kesulitan', 'Mudah')->countAllResults(),
            'sedang'  => (clone $builder)->where('tingkat_kesulitan', 'Sedang')->countAllResults(),
            'sukar'   => (clone $builder)->where('tingkat_kesulitan', 'Sukar')->countAllResults(),
        ];
    }
}