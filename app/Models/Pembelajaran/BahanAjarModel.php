<?php

namespace App\Models\Pembelajaran;

use CodeIgniter\Model;

/**
 * BahanAjarModel
 * Mengelola data materi pembelajaran (PDF, Video, Tautan, dll).
 * Terintegrasi dengan Unit Scoping untuk isolasi data.
 * FIXED: Menggunakan nama tabel 'mata_pelajaran' sesuai struktur database.
 */
class BahanAjarModel extends Model
{
    protected $table            = 'pembelajaran_bahan_ajar';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang', 'rpp_id', 'mata_pelajaran_id', 'judul_bahan', 
        'jenis_file', 'file_path', 'deskripsi', 'status', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Filter data bahan ajar dengan join ke RPP & Mapel
     * FIX: Mengubah 'matapelajaran' menjadi 'mata_pelajaran'
     */
    public function getFilteredData($keyword = null, $kodeJenjang = null)
    {
        $this->select('pembelajaran_bahan_ajar.*, pembelajaran_rpp.topik as rpp_topik, mata_pelajaran.nama_mapel');
        $this->join('pembelajaran_rpp', 'pembelajaran_rpp.id = pembelajaran_bahan_ajar.rpp_id', 'left');
        $this->join('mata_pelajaran', 'mata_pelajaran.id = pembelajaran_bahan_ajar.mata_pelajaran_id', 'left');

        if ($kodeJenjang) {
            $this->where('pembelajaran_bahan_ajar.kode_jenjang', $kodeJenjang);
        }

        if ($keyword) {
            $this->groupStart()
                ->like('pembelajaran_bahan_ajar.judul_bahan', $keyword)
                ->orLike('pembelajaran_bahan_ajar.deskripsi', $keyword)
                ->orLike('mata_pelajaran.nama_mapel', $keyword)
            ->groupEnd();
        }

        return $this;
    }

    /**
     * Statistik Scoped per Unit
     */
    public function getStatistics($kodeJenjang = null)
    {
        $builder = $this->db->table($this->table)->where('deleted_at', null);
        if ($kodeJenjang) {
            $builder->where('kode_jenjang', $kodeJenjang);
        }

        return [
            'total'   => (clone $builder)->countAllResults(),
            'video'   => (clone $builder)->where('jenis_file', 'Video')->countAllResults(),
            'pdf'     => (clone $builder)->where('jenis_file', 'PDF')->countAllResults(),
            'tautan'  => (clone $builder)->where('jenis_file', 'Tautan')->countAllResults(),
            'sd'      => (clone $builder)->where('kode_jenjang', 'SD')->countAllResults(),
            'smp'     => (clone $builder)->where('kode_jenjang', 'SMP')->countAllResults(),
            'sma'     => (clone $builder)->where('kode_jenjang', 'SMA')->countAllResults(),
        ];
    }
}