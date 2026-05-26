<?php

namespace App\Models\Akuntansi;

use CodeIgniter\Model;

/**
 * AkuntansiJurnalModel (Enterprise Edition)
 * Mengelola Header Jurnal Umum (Transaksi Keuangan).
 * Tabel: akuntansi_jurnal
 */
class AkuntansiJurnalModel extends Model
{
    protected $table            = 'akuntansi_jurnal';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $useSoftDeletes   = false; 
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang', 
        'nomor_jurnal', 
        'tanggal', 
        'referensi', 
        'deskripsi', 
        'total_debit', 
        'total_kredit', 
        'sumber_transaksi', 
        'status', 
        'created_by'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'kode_jenjang'     => 'required|max_length[20]',
        'nomor_jurnal'     => 'required|max_length[50]',
        'tanggal'          => 'required|valid_date',
        'deskripsi'        => 'required|string',
        'total_debit'      => 'required|numeric',
        'total_kredit'     => 'required|numeric',
        'sumber_transaksi' => 'permit_empty|max_length[50]',
        'status'           => 'required|in_list[Draft,Posted,Void]'
    ];

    /**
     * Helper Scoping Data berdasarkan Unit Kerja User (Anti-Bocor).
     */
    protected function scopeData($builder)
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);

        if (!$isGlobal) {
            $builder->where('akuntansi_jurnal.kode_jenjang', $userJenjang);
        }
    }
    
    /**
     * Membangun Query Jurnal beserta data Pembuat Jurnal
     */
    public function getJurnalBuilder(?string $filterJenjang = null)
    {
        $builder = $this->db->table($this->table)
            ->select('akuntansi_jurnal.*, users.nama_lengkap as nama_pembuat')
            ->join('users', 'users.id = akuntansi_jurnal.created_by', 'left');

        // Terapkan Isolasi Unit
        $this->scopeData($builder);

        // Terapkan Filter Lanjutan (Abaikan jika MULTI/Konsolidasi)
        if (!empty($filterJenjang) && strtoupper($filterJenjang) !== 'MULTI') {
            $builder->where('akuntansi_jurnal.kode_jenjang', $filterJenjang);
        }

        return $builder;
    }
}