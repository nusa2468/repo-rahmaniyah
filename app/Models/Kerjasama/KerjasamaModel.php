<?php

namespace App\Models\Kerjasama;

use CodeIgniter\Model;

/**
 * Model KerjasamaModel
 * Mengelola data mitra strategis, legalitas MOU/PKS, dan target capaian program.
 */
class KerjasamaModel extends Model
{
    protected $table            = 'kerjasama';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    
    // Update allowedFields sesuai dengan migrasi terbaru
    protected $allowedFields    = [
        'jenjang', 
        'nama_mitra', 
        'logo', 
        'kategori', 
        'alamat', 
        'kontak_person', 
        'no_telp', 
        'website', 
        'tgl_mulai', 
        'tgl_akhir', 
        'file_mou', 
        'program', 
        'target_capaian', 
        'deskripsi', 
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mendapatkan daftar kerjasama berdasarkan jenjang (SD/SMP/SMA/Global)
     */
    public function getKerjasamaByJenjang($jenjang = 'Global')
    {
        return $this->where('jenjang', $jenjang)
                    ->where('status', 'aktif')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Mendapatkan daftar MOU yang akan segera berakhir (Default: 30 hari ke depan)
     * Digunakan untuk sistem notifikasi/pengingat di dashboard.
     */
    public function getExpiringMOU($days = 30)
    {
        $targetDate = date('Y-m-d', strtotime("+$days days"));
        $today      = date('Y-m-d');

        return $this->where('tgl_akhir >=', $today)
                    ->where('tgl_akhir <=', $targetDate)
                    ->where('status', 'aktif')
                    ->orderBy('tgl_akhir', 'ASC')
                    ->findAll();
    }

    /**
     * Statistik mendalam untuk dashboard kerjasama dan analisis KPI
     */
    public function getStats()
    {
        $today = date('Y-m-d');
        $h30   = date('Y-m-d', strtotime("+30 days"));

        return [
            'total'      => $this->countAllResults(),
            'aktif'      => $this->where('status', 'aktif')->countAllResults(),
            'nonaktif'   => $this->where('status', 'nonaktif')->countAllResults(),
            'expiring'   => $this->where('tgl_akhir >=', $today)
                                 ->where('tgl_akhir <=', $h30)
                                 ->countAllResults(),
            'expired'    => $this->where('tgl_akhir <', $today)
                                 ->where('tgl_akhir !=', null)
                                 ->countAllResults(),
            'per_unit'   => [
                'SD'     => $this->where('jenjang', 'SD')->countAllResults(),
                'SMP'    => $this->where('jenjang', 'SMP')->countAllResults(),
                'SMA'    => $this->where('jenjang', 'SMA')->countAllResults(),
                'Global' => $this->where('jenjang', 'Global')->countAllResults(),
            ]
        ];
    }
}