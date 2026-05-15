<?php

namespace App\Models\Keuangan;

use CodeIgniter\Model;

class PengeluaranModel extends Model
{
    protected $table      = 'pengeluaran';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    
    // Kolom tahun_ajaran tidak perlu ada di sini (Alternatif 1)
    protected $allowedFields = [
        'kode_jenjang', 
        'tanggal',
        'kategori',
        'keterangan',
        'jumlah',
        'metode_pembayaran',
        'bukti_transaksi',
        'id_user_input'
    ];

    /**
     * Scope untuk memfilter data berdasarkan Unit.
     * Menggunakan Query Builder internal CI4.
     */
    public function scopeJenjang(?string $kodeJenjang = null)
    {
        if (!empty($kodeJenjang)) {
            $this->where($this->table . '.kode_jenjang', $kodeJenjang);
        }
        return $this;
    }

    /**
     * Menghitung Total Pengeluaran (KPI Dashboard)
     * Menggunakan rentang tanggal dari tabel master tahun ajaran.
     */
    public function getTotalPengeluaran($startDate, $endDate, $kodeJenjang = null)
    {
        // Reset Builder dan terapkan scope
        $builder = $this->scopeJenjang($kodeJenjang);
        
        $res = $builder->selectSum('jumlah', 'total')
                       ->where('tanggal >=', $startDate)
                       ->where('tanggal <=', $endDate)
                       ->get()->getRow();

        return (float)($res->total ?? 0);
    }

    /**
     * Query Dasar Laporan & Log Transaksi Terakhir
     */
    public function getBaseLaporanQuery($startDate, $endDate, $kodeJenjang = null)
    {
        $this->scopeJenjang($kodeJenjang);
        
        return $this->select('pengeluaran.*, IFNULL(users.nama_lengkap, "Admin") as nama_admin')
            ->join('users', 'users.id = pengeluaran.id_user_input', 'left')
            ->where('tanggal >=', $startDate)
            ->where('tanggal <=', $endDate)
            ->orderBy('tanggal', 'DESC')
            ->orderBy('id', 'DESC');
    }

    /**
     * KPI: Item Pengeluaran Terbesar
     */
    public function getItemTerbesar($startDate, $endDate, $kodeJenjang = null)
    {
        $this->scopeJenjang($kodeJenjang);
        
        return $this->where('tanggal >=', $startDate)
                    ->where('tanggal <=', $endDate)
                    ->orderBy('jumlah', 'DESC')
                    ->first();
    }
}