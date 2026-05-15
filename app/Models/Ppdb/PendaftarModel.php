<?php

namespace App\Models\Ppdb;

use CodeIgniter\Model;

/**
 * PendaftarModel (Enterprise Edition)
 * Menangani logika database pendaftar dengan dukungan Filter Unit Dinamis.
 */
class PendaftarModel extends Model
{
    protected $table            = 'pendaftar_biodata';
    protected $primaryKey       = 'pendaftar_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object'; 
    protected $useSoftDeletes   = true;
    
    protected $allowedFields    = [
        'no_pendaftaran', 'user_id', 'kode_jenjang', 'tahun_ajaran', 
        'nama_lengkap', 'nik', 'nisn', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'alamat_lengkap', 'no_hp_whatsapp', 'asal_sekolah', 'nama_ayah', 'nama_ibu',
        'jalur_masuk', 'skor_akhir', 'status_seleksi', 'status_pembayaran', 'metode_bayar',
        'bukti_setor', 'kode_afiliasi', 'nominal_fee', 'status_fee'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mengambil Statistik Dashboard dengan Logika Scoping Cerdas.
     * UPDATE: Menerima parameter $filterJenjang dari Controller
     */
    public function getStatsDashboard($filterJenjang = null)
    {
        // 1. Base Query
        $builder = $this->builder();

        // 2. Terapkan Filter Scope (Unit/Jenjang)
        $this->applyScope($builder, $filterJenjang);

        // 3. Hitung Statistik (Single Query Aggregation - Lebih Cepat)
        $query = $builder->select("
            COUNT(*) as total,
            SUM(CASE WHEN status_seleksi = 'Lolos' THEN 1 ELSE 0 END) as lolos,
            SUM(CASE WHEN status_seleksi = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status_seleksi = 'Gagal' THEN 1 ELSE 0 END) as gagal,
            SUM(CASE WHEN status_pembayaran = 'Lunas' THEN 1 ELSE 0 END) as lunas
        ")->get();

        return $query->getRowArray(); 
    }

    /**
     * Mengambil Data Pendaftar Terbaru dengan Scope.
     */
    public function getTerbaru($limit = 5, $filterJenjang = null)
    {
        $builder = $this->builder();
        
        // 1. Terapkan Filter Scope
        $this->applyScope($builder, $filterJenjang);

        // 2. Ambil data
        return $builder->orderBy('created_at', 'DESC')
                       ->limit($limit)
                       ->get()
                       ->getResult(); 
    }

    /**
     * Logic Inti Scoping: Menentukan apakah harus filter unit atau global.
     * Menggantikan scopeData() agar support parameter.
     */
    protected function applyScope($builder, $filterJenjang = null)
    {
        $session = session();
        $role    = $session->get('role_name');
        $myUnit  = $session->get('kode_jenjang');

        // CASE A: Admin Unit Biasa (Bukan Global)
        // -> Wajib dikunci ke unitnya sendiri, abaikan filter URL.
        if (!in_array($role, ['superadmin', 'yayasan'])) {
            if ($myUnit && $myUnit !== 'GLOBAL') {
                $builder->where('kode_jenjang', $myUnit);
            }
            return;
        }

        // CASE B: Superadmin / Yayasan (Akses Global)
        // -> Cek apakah ada request filter spesifik dari URL/Controller
        // -> Inilah yang membuat dropdown filter berfungsi!
        if (!empty($filterJenjang) && $filterJenjang !== 'Semua') {
            $builder->where('kode_jenjang', $filterJenjang);
        }

        // Jika Superadmin dan filter kosong/Semua, maka otomatis tampilkan GLOBAL.
    }

    /**
     * Generate Nomor Pendaftaran Otomatis: PPDB-{JENJANG}-{TAHUN}-{URUT}
     * Contoh: PPDB-SMA-2025-001
     */
    public function generateNoPendaftaran($jenjang)
    {
        $tahun = date('Y');
        $prefix = "PPDB-" . strtoupper($jenjang) . "-" . $tahun;

        // Cari nomor terakhir di database dengan prefix yg sama
        $lastRow = $this->table($this->table)
                        ->like('no_pendaftaran', $prefix, 'after')
                        ->orderBy('pendaftar_id', 'DESC')
                        ->select('no_pendaftaran')
                        ->limit(1)
                        ->get()
                        ->getRow();

        $urutan = 1;
        if ($lastRow) {
            // Extract nomor urut dari string (3 digit terakhir)
            $lastNo = substr($lastRow->no_pendaftaran, -3);
            $urutan = intval($lastNo) + 1;
        }

        return $prefix . "-" . sprintf("%03d", $urutan);
    }
}