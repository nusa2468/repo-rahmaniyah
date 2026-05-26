<?php

namespace App\Models\Akuntansi;

use CodeIgniter\Model;

/**
 * AkuntansiKategoriModel (Enterprise Edition)
 * Mengelola klasifikasi utama akun (Harta, Kewajiban, Aset Neto, dll).
 * Tabel: akuntansi_kategori
 */
class AkuntansiKategoriModel extends Model
{
    protected $table            = 'akuntansi_kategori';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang', // Ditambahkan agar konsisten dengan scoping unit
        'kode_kategori',
        'nama_kategori',
        'saldo_normal',
        'laporan_tujuan'
    ];

    protected $validationRules = [
        'kode_jenjang'   => 'required|max_length[20]',
        'kode_kategori'  => 'required|max_length[10]', // Pengecekan unique akan ditangani di level Controller per Unit
        'nama_kategori'  => 'required|max_length[100]',
        'saldo_normal'   => 'required|in_list[Debit,Kredit]',
        'laporan_tujuan' => 'required|in_list[Neraca,Aktivitas]' // Spesifik ISAK 35
    ];

    /**
     * Helper Scoping Data berdasarkan Unit Kerja User (Anti-Bocor).
     */
    protected function scopeData($builder)
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);

        // Jika bukan superadmin, paksa data hanya untuk unitnya sendiri
        if (!$isGlobal) {
            $builder->where($this->table . '.kode_jenjang', $userJenjang);
        }
    }

    /**
     * Override findAll untuk menerapkan scope secara otomatis (Keamanan Lapis 1)
     */
    public function findAll(?int $limit = null, int $offset = 0)
    {
        $builder = $this->builder();
        $this->scopeData($builder);
        return parent::findAll($limit, $offset);
    }
}