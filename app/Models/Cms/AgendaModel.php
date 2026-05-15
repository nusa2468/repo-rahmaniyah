<?php

namespace App\Models\Cms;

use CodeIgniter\Model;

class AgendaModel extends Model
{
    protected $table            = 'agenda';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Mengaktifkan Soft Deletes sesuai migrasi (deleted_at)
    protected $useSoftDeletes   = true;

    // Disesuaikan dengan field di Migration CreateCmsTables
    // Note: 'lokasi' diubah menjadi 'tempat' sesuai migrasi
    protected $allowedFields    = [
        'kode_jenjang', 'nama_kegiatan', 'slug', 
        'tanggal_mulai', 'tanggal_selesai', 
        'tempat', 'keterangan', 'status',
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mengambil agenda berdasarkan jenjang
     * Menampilkan agenda yang akan datang (upcoming) atau hari ini
     */
    public function getAgendaByJenjang($jenjang = null, $limit = 5)
    {
        $builder = $this->where('status', 'published')
                        ->where('tanggal_mulai >=', date('Y-m-d')); // Hanya tampilkan agenda masa depan

        // Logic Filter Scope Jenjang
        if ($jenjang && $jenjang !== 'Global') {
            $builder->groupStart()
                    ->where('kode_jenjang', $jenjang)
                    ->orWhere('kode_jenjang', null) // Null = Global
                    ->orWhere('kode_jenjang', 'Global')
                    ->groupEnd();
        }

        return $builder->orderBy('tanggal_mulai', 'ASC')
                       ->findAll($limit);
    }

    /**
     * Mendapatkan detail agenda berdasarkan slug
     */
    public function getAgendaBySlug($slug)
    {
        return $this->where('slug', $slug)
                    ->where('status', 'published')
                    ->first();
    }
}