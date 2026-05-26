<?php

namespace App\Models\Akuntansi;

use CodeIgniter\Model;

/**
 * AkuntansiCoaModel (Enterprise Edition)
 * Mengelola Bagan Akun (Chart of Accounts) Tunggal Yayasan.
 * Tabel: akuntansi_coa
 */
class AkuntansiCoaModel extends Model
{
    protected $table            = 'akuntansi_coa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Mengaktifkan Soft Deletes untuk keamanan rekam jejak audit
    protected $useSoftDeletes   = true; 
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang', 
        'id_kategori', 
        'kode_akun', 
        'nama_akun', 
        'saldo_awal', 
        'is_parent', 
        'parent_id', 
        'is_active',
        'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'kode_jenjang' => 'required|max_length[20]',
        'id_kategori'  => 'required|integer',
        'kode_akun'    => 'required|max_length[20]',
        'nama_akun'    => 'required|max_length[255]',
        'saldo_awal'   => 'permit_empty|numeric',
        'is_parent'    => 'permit_empty|in_list[0,1]',
        'is_active'    => 'permit_empty|in_list[0,1]'
    ];

    /**
     * Helper Scoping Data berdasarkan Unit Kerja User.
     */
    protected function scopeData($builder)
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);

        if (!$isGlobal) {
            $builder->where('akuntansi_coa.kode_jenjang', $userJenjang);
        }
    }

    /**
     * Membangun Query Master COA beserta Relasi Kategori dan Parent-nya
     */
    public function getCoaBuilder(?string $filterJenjang = null)
    {
        $builder = $this->db->table($this->table)
            ->select('
                akuntansi_coa.*, 
                akuntansi_kategori.nama_kategori, 
                akuntansi_kategori.saldo_normal,
                akuntansi_kategori.laporan_tujuan,
                parent_coa.nama_akun as nama_parent
            ')
            ->join('akuntansi_kategori', 'akuntansi_kategori.id = akuntansi_coa.id_kategori', 'left')
            ->join('akuntansi_coa as parent_coa', 'parent_coa.id = akuntansi_coa.parent_id', 'left')
            ->where('akuntansi_coa.deleted_at', null);

        // Eksekusi Global Scoping
        $this->scopeData($builder);

        // FIX BUG: Terapkan filter secara kaku. 
        // Jika filter adalah GLOBAL, maka HANYA tarik yang GLOBAL!
        if (!empty($filterJenjang)) {
            $builder->where('akuntansi_coa.kode_jenjang', $filterJenjang);
        }

        return $builder->orderBy('akuntansi_coa.kode_akun', 'ASC');
    }
}