<?php

namespace App\Models\Ppdb;

use CodeIgniter\Model;

/**
 * AffiliateModel - Modul PPDB (Enterprise Edition)
 * Mengelola data agen marketing dan statistik performa (KPI).
 * Sinkron dengan tabel 'affiliates' pada Migration CreatePpdbTable.
 */
class AffiliateModel extends Model
{
    protected $table            = 'affiliates';
    protected $primaryKey       = 'affiliate_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = true;
    
    protected $allowedFields    = [
        'kode_jenjang',      // Scope Unit
        'nama_agen', 
        'kode_agen', 
        'no_hp', 
        'email', 
        'alamat', 
        'status', 
        'metode_agen',       // Strategi
        'target_pendaftar',  // KPI
        'fee_per_siswa',     // Komisi
        'nama_bank', 
        'nomor_rekening',
        'nama_rekening',
        'catatan'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // --- VALIDATION RULES (Sesuai Migration) ---
    protected $validationRules = [
        'affiliate_id'     => 'permit_empty|integer',
        'nama_agen'        => 'required|min_length[3]|max_length[255]',
        'kode_agen'        => 'required|max_length[50]|is_unique[affiliates.kode_agen,affiliate_id,{affiliate_id}]',
        'status'           => 'required|in_list[Aktif,Non-Aktif]',
        'target_pendaftar' => 'permit_empty|integer',
        'fee_per_siswa'    => 'permit_empty|numeric',
        'no_hp'            => 'permit_empty|max_length[20]',
        'email'            => 'permit_empty|valid_email|max_length[100]',
    ];

    protected $validationMessages = [
        'kode_agen' => [
            'is_unique' => 'Kode agen ini sudah terdaftar, silakan gunakan kode lain.'
        ],
        'nama_agen' => [
            'required' => 'Nama agen wajib diisi.'
        ]
    ];

    /**
     * Logic Inti Scoping: Filter data berdasarkan Role & Request.
     */
    protected function applyScope($builder, $filterJenjang = null, $alias = '')
    {
        $session = session();
        $role    = $session->get('role_name');
        $myUnit  = $session->get('kode_jenjang');
        
        $field = $alias ? $alias . '.kode_jenjang' : 'kode_jenjang';

        // 1. Admin Unit -> Wajib dikunci ke unitnya sendiri
        if (!in_array($role, ['superadmin', 'yayasan'])) {
            if ($myUnit && $myUnit !== 'GLOBAL') {
                $builder->where($field, $myUnit);
            }
            return;
        }

        // 2. Superadmin -> Cek filter dari URL
        if (!empty($filterJenjang) && $filterJenjang !== 'Semua') {
            $builder->where($field, $filterJenjang);
        }
    }

    /**
     * Mengambil Statistik Performa Agen (Real-time).
     * @param string|null $filterJenjang Filter dari URL Dashboard
     */
    public function getPerformanceStats($filterJenjang = null)
    {
        $builder = $this->db->table($this->table . ' a');

        // Terapkan Scope
        $this->applyScope($builder, $filterJenjang, 'a');

        // UPDATE: Menambahkan field lengkap (no_hp, email, bank, dll) agar tidak error di View
        return $builder
            ->select('
                a.affiliate_id, 
                a.nama_agen, 
                a.kode_agen, 
                a.kode_jenjang,
                a.no_hp,            
                a.email,            
                a.nama_bank,        
                a.nomor_rekening,   
                a.metode_agen,      
                a.target_pendaftar,
                a.fee_per_siswa,
                a.status,
                COUNT(p.pendaftar_id) as total_leads,
                SUM(CASE WHEN p.status_pembayaran = "Lunas" THEN 1 ELSE 0 END) as total_lunas,
                SUM(CASE WHEN p.status_pembayaran = "Lunas" THEN p.nominal_fee ELSE 0 END) as total_potensi_fee
            ')
            // Join ke data pendaftar untuk hitung konversi (Leads)
            ->join('pendaftar_biodata p', 'p.kode_afiliasi = a.kode_agen AND p.deleted_at IS NULL', 'left')
            ->where('a.deleted_at', null)
            ->groupBy('a.affiliate_id')
            ->orderBy('total_leads', 'DESC')
            ->get()
            ->getResult();
    }

    /**
     * Mengambil daftar agen aktif untuk dropdown di Form Pendaftaran.
     */
    public function getActiveAffiliates()
    {
        $builder = $this->builder();
        $this->applyScope($builder); // Default scope (User Login)
        
        // FIX: Builder tidak punya method findAll(), gunakan get()->getResult()
        return $builder->where('status', 'Aktif')
                       ->orderBy('nama_agen', 'ASC')
                       ->get()
                       ->getResult();
    }

    /**
     * Cek apakah kode agen sudah ada (Validasi Unik Manual jika diperlukan).
     */
    public function isCodeUnique($code, $excludeId = null)
    {
        $query = $this->where('kode_agen', $code);
        if ($excludeId) {
            $query->where('affiliate_id !=', $excludeId);
        }
        return $query->countAllResults() === 0;
    }

    /**
     * Generate Kode Agen Otomatis: {UNIT}-AGT-{HEX}
     * Contoh: SMA-AGT-A1B2
     */
    public function generateKodeAgen($jenjang = null)
    {
        if (!$jenjang) {
            $jenjang = session()->get('kode_jenjang') ?? 'MKT';
        }
        
        // Pastikan jenjang bersih
        $jenjang = ($jenjang === 'GLOBAL') ? 'MKT' : $jenjang;
        
        $prefix = strtoupper($jenjang) . '-AGT-';
        $random = strtoupper(bin2hex(random_bytes(2))); // 4 karakter hex unik

        return $prefix . $random;
    }
}