<?php

namespace App\Models\Kesiswaan;

use CodeIgniter\Model;

class AlumniModel extends Model
{
    protected $table            = 'kesiswaan_alumni';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'kode_jenjang', 'siswa_id', 'tahun_lulus', 'status_kegiatan', 
        'nama_instansi', 'jabatan_jurusan', 'testimoni', 'kontak_public'
    ];
    protected $useTimestamps    = true;
    protected $deletedField     = 'deleted_at';

    public function getAlumni()
    {
        return $this->select('kesiswaan_alumni.*, s.nama_lengkap, s.nis, s.kode_jenjang')
            ->join('siswa s', 's.id = kesiswaan_alumni.siswa_id')
            ->where('kesiswaan_alumni.deleted_at', null)
            ->orderBy('kesiswaan_alumni.tahun_lulus', 'DESC')
            ->findAll();
    }

    public function saveAlumni($data)
    {
        return $this->save($data);
    }
}