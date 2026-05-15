<?php

namespace App\Models\Portal;

use CodeIgniter\Model;

/**
 * PortalPpdbModel
 * Mengelola data pendaftaran dari sisi Portal Publik.
 */
class PortalPpdbModel extends Model
{
    protected $table            = 'pendaftar_biodata';
    protected $primaryKey       = 'pendaftar_id';

    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    
    // Fitur Timestamps & Soft Deletes
    protected $useTimestamps    = true; 
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    
    protected $useSoftDeletes   = true; 
    protected $deletedField     = 'deleted_at';

    /**
     * SINKRONISASI DENGAN MIGRATION:
     * Field 'kode_jenjang', 'tahun_ajaran', dan 'bukti_setor' wajib ada.
     */
    protected $allowedFields = [
        'no_pendaftaran',
        'user_id',
        'kode_jenjang', // UPDATE: Scope Unit
        'tahun_ajaran', // UPDATE: Filter Periode
        'nama_lengkap',
        'nik',
        'nisn',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat_lengkap',
        'no_hp_whatsapp',
        'asal_sekolah',
        'nama_ayah',
        'nama_ibu',
        'jalur_masuk',
        'skor_akhir',
        'status_seleksi',
        'status_pembayaran',
        'metode_bayar',
        'bukti_setor', // UPDATE: Disesuaikan dengan controller
        'kode_afiliasi',
        'nominal_fee',
        'status_fee'
    ];

    /**
     * Generate Nomor Pendaftaran Unik + Scope Unit
     * Format: SMA-2026-A1B2C3
     * * @param string|null $jenjang Kode Jenjang (TK/SD/SMP/SMA)
     */
    public function generateNoPendaftaran($jenjang = null)
    {
        $tahun = date('Y');
        
        // Jika tidak ada jenjang (misal direct script), default 'REG'
        if (!$jenjang) {
            $jenjang = 'REG';
        }

        // Pastikan format uppercase dan bersih dari spasi
        $jenjangClean = strtoupper(trim($jenjang));

        // Prefix: SMA-2026-
        $prefix = $jenjangClean . '-' . $tahun . '-';
        
        // Menggunakan random bytes (hex) untuk keunikan tinggi & performa lebih baik 
        // daripada query 'last row' yang rawan race condition saat trafik tinggi.
        try {
            $random = strtoupper(bin2hex(random_bytes(3))); // Menghasilkan 6 karakter unik
        } catch (\Exception $e) {
            // Fallback jika random_bytes gagal
            $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        }

        return $prefix . $random;
    }
}