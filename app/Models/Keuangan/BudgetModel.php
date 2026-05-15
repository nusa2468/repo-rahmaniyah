<?php

namespace App\Models\Keuangan;

use CodeIgniter\Model;

/**
 * BudgetModel
 * Mengelola data anggaran unit (Target dana tahunan berbasis COA/ISAK 35).
 */
class BudgetModel extends Model
{
    protected $table      = 'anggaran_unit';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'kode_jenjang', 
        'id_kategori', 
        'tahun', 
        'nominal', 
        'keterangan'
    ];

    /**
     * getAllBudgets
     * Mengembalikan semua data anggaran dalam bentuk array.
     * Digunakan oleh LaporanBudgetController untuk laporan cetak atau ekspor data.
     */
    public function getAllBudgets($kodeJenjang = null, $tahun = null)
    {
        return $this->getFilteredBudgets($kodeJenjang, $tahun)->findAll();
    }

    /**
     * getFilteredBudgets
     * Menyiapkan Query Builder dengan Join COA dan Jenjang.
     * Fungsi ini mengembalikan builder agar bisa digunakan untuk paginate() di Controller.
     */
    public function getFilteredBudgets($kodeJenjang = null, $tahun = null)
    {
        $builder = $this->select('anggaran_unit.*, kategori_anggaran.nama_kategori, kategori_anggaran.kode_kategori, kategori_anggaran.kelompok, jenjang_sekolah.nama_jenjang')
            ->join('kategori_anggaran', 'kategori_anggaran.id = anggaran_unit.id_kategori', 'inner')
            ->join('jenjang_sekolah', 'jenjang_sekolah.kode_jenjang = anggaran_unit.kode_jenjang', 'left');

        /**
         * Logika Scope Unit:
         * 1. Jika $kodeJenjang berisi unit tertentu (SD/SMP/SMA/GLOBAL), filter akan diterapkan.
         * 2. Jika $kodeJenjang kosong (Superadmin memilih 'Agregat'), filter ditiadakan 
         * sehingga semua data unit muncul di tabel.
         */
        if (!empty($kodeJenjang)) {
            $builder->where('anggaran_unit.kode_jenjang', $kodeJenjang);
        }

        /**
         * Logika Tahun Ajaran:
         * Memastikan data yang muncul hanya milik Tahun Ajaran Aktif (misal: 2025/2026).
         */
        if (!empty($tahun)) {
            $builder->where('anggaran_unit.tahun', $tahun);
        }

        // Urutkan berdasarkan tahun terbaru dan urutan kode COA ISAK 35
        return $builder->orderBy('anggaran_unit.tahun', 'DESC')
                       ->orderBy('kategori_anggaran.kode_kategori', 'ASC');
    }

    /**
     * getTotalBudget
     * Helper untuk Dashboard/Summary: Hitung total budget per kelompok (Penghasilan vs Beban).
     * Mendukung variasi kelompok 'penghasilan' dan 'pendapatan' agar hasil statistik akurat.
     */
    public function getTotalBudget($kodeJenjang = null, $tahun = null, $kelompok = 'beban')
    {
        $builder = $this->selectSum('nominal', 'total')
            ->join('kategori_anggaran', 'kategori_anggaran.id = anggaran_unit.id_kategori');

        // Filter Scope Unit
        if (!empty($kodeJenjang)) {
            $builder->where('anggaran_unit.kode_jenjang', $kodeJenjang);
        }

        // Filter Tahun Ajaran
        if (!empty($tahun)) {
            $builder->where('anggaran_unit.tahun', $tahun);
        }

        // Normalisasi Kelompok ISAK 35
        if ($kelompok === 'penghasilan' || $kelompok === 'pendapatan') {
            $builder->whereIn('kategori_anggaran.kelompok', ['penghasilan', 'pendapatan']);
        } else {
            $builder->where('kategori_anggaran.kelompok', $kelompok);
        }

        $result = $builder->get()->getRow();

        return $result ? (float)$result->total : 0;
    }
}