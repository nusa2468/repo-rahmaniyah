<?php

namespace App\Models\Pembelajaran;

use CodeIgniter\Model;

/**
 * EvaluasiBelajarModel
 * Mengelola data jadwal dan pengaturan evaluasi (Tugas, Kuis, UTS, UAS).
 * Mendukung isolasi data per jenjang (Unit Scoping).
 */
class EvaluasiBelajarModel extends Model
{
    protected $table            = 'pembelajaran_evaluasi_belajar';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang', 'mata_pelajaran_id', 'silabus_id', 'judul_evaluasi',
        'jenis_evaluasi', 'tanggal_mulai', 'tanggal_selesai', 'durasi',
        'kkm', 'status', 'instruksi', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Filter data evaluasi dengan join ke Mapel dan Silabus.
     */
    public function getFilteredData($keyword = null, $kodeJenjang = null)
    {
        $this->select('pembelajaran_evaluasi_belajar.*, mata_pelajaran.nama_mapel, pembelajaran_silabus.materi_pokok');
        $this->join('mata_pelajaran', 'mata_pelajaran.id = pembelajaran_evaluasi_belajar.mata_pelajaran_id', 'left');
        $this->join('pembelajaran_silabus', 'pembelajaran_silabus.id = pembelajaran_evaluasi_belajar.silabus_id', 'left');

        if ($kodeJenjang) {
            $this->where('pembelajaran_evaluasi_belajar.kode_jenjang', $kodeJenjang);
        }

        if ($keyword) {
            $this->groupStart()
                ->like('judul_evaluasi', $keyword)
                ->orLike('jenis_evaluasi', $keyword)
                ->orLike('mata_pelajaran.nama_mapel', $keyword)
            ->groupEnd();
        }

        return $this;
    }

    /**
     * Statistik Evaluasi Scoped per Unit.
     */
    public function getStatistics($kodeJenjang = null)
    {
        $builder = $this->db->table($this->table)->where('deleted_at', null);
        if ($kodeJenjang) {
            $builder->where('kode_jenjang', $kodeJenjang);
        }

        return [
            'total'     => (clone $builder)->countAllResults(),
            'kuis'      => (clone $builder)->where('jenis_evaluasi', 'Kuis')->countAllResults(),
            'tugas'     => (clone $builder)->where('jenis_evaluasi', 'Tugas')->countAllResults(),
            'uts_uas'   => (clone $builder)->whereIn('jenis_evaluasi', ['UTS', 'UAS'])->countAllResults(),
            'published' => (clone $builder)->where('status', 'Published')->countAllResults(),
        ];
    }
}