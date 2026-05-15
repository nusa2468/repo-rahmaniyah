<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk menangani Transaksi Gaji Bulanan (gaji_bulanan) dan Detail (gaji_bulanan_detail).
 * Tabel ini menyimpan data historis (snapshot) gaji yang telah diproses.
 */
class TransaksiGajiModel extends Model
{
    // Tabel Ringkasan Gaji Bulanan
    protected $table          = 'gaji_bulanan';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = true;
    protected $allowedFields  = [
        'id_karyawan', 'bulan', 'tahun', 'nik', 'nama_lengkap', 'jabatan',
        'total_pendapatan', 'total_potongan', 'gaji_bersih', 'status_pembayaran', 'tanggal_proses'
    ];

    // Nama tabel detail
    protected $detailTable = 'gaji_bulanan_detail';

    /**
     * Memeriksa apakah gaji untuk bulan dan tahun tertentu sudah pernah diproses.
     * @param int $bulan
     * @param int $tahun
     * @return bool
     */
    public function isGajiProcessed($bulan, $tahun)
    {
        // Cek apakah sudah ada data gaji untuk periode ini
        return $this->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->countAllResults() > 0;
    }

    /**
     * Menyimpan detail komponen gaji untuk semua karyawan dalam batch.
     * Fungsi ini memerlukan mapping antara id_karyawan dan id_gaji_bulanan.
     * @param array $gajiDetailBatch Array detail gaji yang dikelompokkan berdasarkan id_karyawan.
     * @param int $bulan
     * @param int $tahun
     * @return bool
     */
    public function insertDetailBatch(array $gajiDetailBatch, $bulan, $tahun)
    {
        // Ambil semua ID transaksi gaji bulanan yang baru saja di-insert
        // Ini dilakukan untuk mendapatkan mapping antara id_karyawan dan id_gaji_bulanan
        $ringkasanGaji = $this->where('bulan', $bulan)
                            ->where('tahun', $tahun)
                            ->select('id, id_karyawan')
                            ->findAll();
        
        // Buat array mapping: [id_karyawan => id_gaji_bulanan]
        $mapping = array_column($ringkasanGaji, 'id', 'id_karyawan');
        $finalDetailBatch = [];

        foreach ($gajiDetailBatch as $id_karyawan => $details) {
            // Cari ID transaksi bulanan yang sesuai
            $id_gaji_bulanan = $mapping[$id_karyawan] ?? null;

            if ($id_gaji_bulanan) {
                foreach ($details as $detail) {
                    $finalDetailBatch[] = [
                        'id_gaji_bulanan' => $id_gaji_bulanan,
                        'id_komponen' => $detail['id_komponen'],
                        'nama_komponen' => $detail['nama_komponen'],
                        'tipe_komponen' => $detail['tipe_komponen'],
                        'nominal' => $detail['nominal'],
                        'keterangan' => $detail['keterangan'] ?? null, // Tambahkan null default untuk keterangan
                        'created_at' => date('Y-m-d H:i:s'), // Atur created_at manual karena insertBatch
                    ];
                }
            }
        }
        
        if (empty($finalDetailBatch)) {
            return true; // Tidak ada detail untuk diinsert, anggap berhasil
        }

        // Insert Batch ke tabel detail
        return $this->db->table($this->detailTable)->insertBatch($finalDetailBatch);
    }

    /**
     * Mengambil ringkasan riwayat gaji (grouped by month and year).
     * Digunakan untuk halaman riwayat/laporan bulanan.
     * @return array
     */
    public function getRiwayatGajiSummary()
    {
        return $this->db->table($this->table)
            ->select('bulan, tahun, COUNT(id) as total_karyawan, SUM(gaji_bersih) as total_gaji')
            ->groupBy('bulan, tahun')
            ->orderBy('tahun', 'DESC')
            ->orderBy('bulan', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil detail komponen gaji (penambah/pengurang) berdasarkan ID Transaksi Gaji Bulanan.
     * @param int $id_gaji_bulanan
     * @return array
     */
    public function getDetailGaji($id_gaji_bulanan)
    {
        return $this->db->table($this->detailTable)
            ->where('id_gaji_bulanan', $id_gaji_bulanan)
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil data ringkasan untuk slip gaji (header).
     * @param int $id_gaji
     * @return array|null
     */
    public function getSlipGaji($id_gaji)
    {
        return $this->find($id_gaji);
    }
}