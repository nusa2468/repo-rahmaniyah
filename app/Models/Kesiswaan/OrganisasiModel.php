<?php

namespace App\Models\Kesiswaan;

use CodeIgniter\Model;

class OrganisasiModel extends Model
{
    protected $table            = 'kesiswaan_organisasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'kode_jenjang', 'tahun_ajar_id', 'siswa_id', 'jabatan', 
        'jenis_organisasi', 'parent_id', 'urutan', 'status_aktif'
    ];
    protected $useTimestamps    = true;
    protected $deletedField     = 'deleted_at';

    public function getOrganisasi()
    {
        return $this->select('kesiswaan_organisasi.*, s.nama_lengkap, s.nis, s.kode_jenjang')
            ->join('siswa s', 's.id = kesiswaan_organisasi.siswa_id')
            ->where('kesiswaan_organisasi.deleted_at', null)
            ->orderBy('kesiswaan_organisasi.urutan', 'ASC') // Urutkan berdasarkan hierarki
            ->findAll();
    }

    public function saveOrganisasi($data)
    {
        // Pastikan checkbox status aktif terhandle (jika tidak dicentang tidak terkirim via POST)
        if(!isset($data['status_aktif'])) $data['status_aktif'] = 0;
        
        return $this->save($data);
    }
}