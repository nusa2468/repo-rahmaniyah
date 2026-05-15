<?php

namespace App\Models\Cms;

use CodeIgniter\Model;

class AgendaModel extends Model
{
    protected $table            = 'agenda';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    // Sesuai Migration: nama_kegiatan, tempat, keterangan
    protected $allowedFields    = [
        'kode_jenjang', 'nama_kegiatan', 'slug', 
        'tanggal_mulai', 'tanggal_selesai', 'tempat', 'keterangan', 
        'status', 
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mendapatkan agenda berdasarkan jenjang
     */
    public function getAgendaByJenjang($jenjang = null)
    {
        $builder = $this->db->table($this->table);
        
        $builder->where('status', 'published')
                ->where('deleted_at', null);

        if ($jenjang && strtoupper($jenjang) !== 'GLOBAL') {
            $builder->groupStart()
                 ->where('kode_jenjang', $jenjang)
                 ->orWhere('kode_jenjang', null)
                 ->orWhere('kode_jenjang', 'Global')
                 ->orWhere('kode_jenjang', 'GLOBAL')
                 ->groupEnd();
        }

        return $builder->orderBy('tanggal_mulai', 'ASC')->get()->getResultArray();
    }

    /**
     * Mendapatkan agenda mendatang
     */
    public function getUpcomingAgenda($limit = 5)
    {
        return $this->where('status', 'published')
                    ->where('tanggal_mulai >=', date('Y-m-d'))
                    ->orderBy('tanggal_mulai', 'ASC')
                    ->findAll($limit);
    }
}