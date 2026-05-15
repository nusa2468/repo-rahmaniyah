<?php

namespace App\Models\Kesiswaan;

use CodeIgniter\Model;

class BkModel extends Model
{
    protected $table            = 'kesiswaan_bk_catatan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'kode_jenjang', 'tahun_ajar_id', 'siswa_id', 'bk_kategori_id', 
        'tanggal_kejadian', 'keterangan_detail', 'tindak_lanjut', 'status_penyelesaian'
    ];
    protected $useTimestamps    = true;
    protected $deletedField     = 'deleted_at';

    // --- GET DATA ---
    public function getCatatanBK()
    {
        return $this->select('kesiswaan_bk_catatan.*, s.nama_lengkap, s.kode_jenjang, k.nama_kasus, k.jenis, k.poin')
            ->join('siswa s', 's.id = kesiswaan_bk_catatan.siswa_id')
            ->join('kesiswaan_bk_kategori k', 'k.id = kesiswaan_bk_catatan.bk_kategori_id')
            ->where('kesiswaan_bk_catatan.deleted_at', null)
            ->orderBy('kesiswaan_bk_catatan.tanggal_kejadian', 'DESC')
            ->findAll();
    }

    // --- HELPER KATEGORI ---
    public function getKategoriBK()
    {
        return $this->db->table('kesiswaan_bk_kategori')
            ->orderBy('jenis', 'ASC')
            ->orderBy('nama_kasus', 'ASC')
            ->get()->getResultArray();
    }

    // --- WRAPPER SAVE ---
    public function saveKasusBK($data)
    {
        return $this->save($data);
    }
}