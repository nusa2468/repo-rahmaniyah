<?php

namespace App\Models\Akuntansi;

use CodeIgniter\Model;

/**
 * AkuntansiJurnalDetailModel (Enterprise Edition)
 * Mengelola Rincian (Baris) Debit & Kredit di setiap Jurnal.
 * Tabel: akuntansi_jurnal_detail
 */
class AkuntansiJurnalDetailModel extends Model
{
    protected $table            = 'akuntansi_jurnal_detail';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_jurnal', 
        'id_coa', 
        'debit', 
        'kredit', 
        'keterangan'
    ];

    protected $validationRules = [
        'id_jurnal'  => 'required|integer',
        'id_coa'     => 'required|integer',
        'debit'      => 'permit_empty|numeric',
        'kredit'     => 'permit_empty|numeric'
    ];

    /**
     * Helper Scoping Data berdasarkan Unit Kerja User (Melalui Relasi Jurnal).
     */
    protected function scopeData($builder)
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);

        if (!$isGlobal) {
            $builder->where('jurnal_induk.kode_jenjang', $userJenjang);
        }
    }

    /**
     * Mengambil seluruh baris transaksi (debit/kredit) dari sebuah Jurnal
     */
    public function getDetailByJurnal($idJurnal)
    {
        $builder = $this->db->table($this->table)
            ->select('
                akuntansi_jurnal_detail.*, 
                akuntansi_coa.kode_akun, 
                akuntansi_coa.nama_akun
            ')
            ->join('akuntansi_jurnal as jurnal_induk', 'jurnal_induk.id = akuntansi_jurnal_detail.id_jurnal', 'inner')
            ->join('akuntansi_coa', 'akuntansi_coa.id = akuntansi_jurnal_detail.id_coa', 'left');

        // Terapkan Isolasi Unit untuk mencegah pengintipan via bypass parameter ID Jurnal
        $this->scopeData($builder);

        return $builder->where('akuntansi_jurnal_detail.id_jurnal', $idJurnal)
            ->orderBy('akuntansi_jurnal_detail.debit', 'DESC') // Urutkan Debit di atas Kredit sesuai SAK
            ->get()
            ->getResultArray();
    }
}