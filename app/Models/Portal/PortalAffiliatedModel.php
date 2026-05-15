<?php

namespace App\Models\Portal;

use CodeIgniter\Model;

class PortalAffiliatedModel extends Model
{
    protected $table            = 'affiliates';
    protected $primaryKey       = 'affiliate_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'nama_agen', 'kode_agen', 'no_hp', 'email', 'alamat', 
        'status', 'metode_agen', 'target_pendaftar', 'fee_per_siswa', 
        'nama_bank', 'nomor_rekening', 'nama_rekening', 'catatan'
    ];

    protected $useTimestamps = true;

    /**
     * Mengambil statistik performa agen
     */
    public function getStats($kodeAgen)
    {
        $db = \Config\Database::connect();
        return $db->table('pendaftar_biodata')
                  ->select('
                    COUNT(pendaftar_id) as total_pendaftar,
                    SUM(CASE WHEN status_pembayaran = "Lunas" THEN 1 ELSE 0 END) as total_lunas,
                    SUM(nominal_fee) as total_fee_rp
                  ')
                  ->where('kode_afiliasi', $kodeAgen)
                  ->get()
                  ->getRow();
    }

    /**
     * Mengambil daftar siswa yang direferensikan oleh agen ini
     */
    public function getReferredStudents($kodeAgen)
    {
        $db = \Config\Database::connect();
        return $db->table('pendaftar_biodata')
                  ->where('kode_afiliasi', $kodeAgen)
                  ->orderBy('created_at', 'DESC')
                  ->get()
                  ->getResult();
    }
}