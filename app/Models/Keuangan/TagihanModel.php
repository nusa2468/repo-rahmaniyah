<?php

namespace App\Models\Keuangan;

use CodeIgniter\Model;

/**
 * Model Tagihan
 * Lokasi: app/Models/Keuangan/TagihanModel.php
 */
class TagihanModel extends Model
{
    protected $table      = 'tagihan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'kode_jenjang', 
        'id_siswa', 
        'id_jenis_pembayaran', 
        'deskripsi', 
        'jumlah', 
        'tanggal_tagihan', 
        'tanggal_jatuh_tempo', 
        'status'
    ];

    protected $useTimestamps = true;

    // --- Validation Rules ---
    protected $validationRules = [
        'kode_jenjang'        => 'required|in_list[SD,SMP,SMA]',
        'id_siswa'            => 'required',
        'id_jenis_pembayaran' => 'required',
        'deskripsi'           => 'required|min_length[5]|max_length[255]',
        'jumlah'              => 'required|decimal|greater_than[0]',
        'tanggal_tagihan'     => 'required|valid_date',
        'tanggal_jatuh_tempo' => 'required|valid_date',
        'status'              => 'required|in_list[belum_lunas,lunas,mencicil,sebagian]'
    ];

    protected $validationMessages = [
        'kode_jenjang' => ['required' => 'Unit kerja wajib ditentukan.'],
        'jumlah'       => ['required' => 'Nominal tagihan wajib diisi.'],
    ];

    protected $skipValidation = false;

    /**
     * SCOPE: Filter berdasarkan Unit Kerja (Jenjang)
     * Mengatasi error "Call to undefined method scopeJenjang"
     */
    public function scopeJenjang(string $kodeJenjang = null)
    {
        if ($kodeJenjang) {
            return $this->where($this->table . '.kode_jenjang', $kodeJenjang);
        }
        return $this;
    }

    /**
     * DASHBOARD: Menghitung total target tagihan bulan ini
     */
    public function getTotalTargetTagihanBulanIni()
    {
        $bulanSekarang = date('m');
        $tahunSekarang = date('Y');

        $result = $this->db->table($this->table)
            ->selectSum('jumlah')
            ->where('MONTH(tanggal_jatuh_tempo)', $bulanSekarang)
            ->where('YEAR(tanggal_jatuh_tempo)', $tahunSekarang)
            ->get()
            ->getRow();

        return $result ? (float)$result->jumlah : 0;
    }

    /**
     * DETAIL: Mengambil satu data tagihan lengkap dengan total terbayar via Subquery
     */
    public function getTagihanById($id)
    {
        $db = \Config\Database::connect();
        
        $tahunAktifRow = $db->table('tahun_ajaran')->where('status', 'aktif')->get()->getRow();
        $idTahunAktif  = $tahunAktifRow ? $tahunAktifRow->id : 0;

        return $this->db->table($this->table)
            ->select('
                tagihan.*, 
                siswa.nama_lengkap, 
                siswa.nis, 
                jenis_pembayaran.nama_pembayaran, 
                kelas.nama_kelas,
                (SELECT COALESCE(SUM(jumlah_bayar), 0) FROM pembayaran WHERE id_tagihan = tagihan.id) as total_terbayar
            ')
            ->join('siswa', 'siswa.id = tagihan.id_siswa', 'inner')
            ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran', 'inner')
            ->join('siswa_enrollment', 'siswa_enrollment.id_siswa = tagihan.id_siswa AND siswa_enrollment.id_tahun_ajaran = ' . $db->escape($idTahunAktif), 'left')
            ->join('kelas', 'kelas.id = siswa_enrollment.id_kelas', 'left')
            ->where('tagihan.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * INDEX: Query dasar untuk daftar tagihan (mendukung Pagination)
     * Mengembalikan Builder, bukan Array.
     */
    public function getBaseQueryWithDetails($id_siswa = null, $bulan_jatuh_tempo = null)
    {
        $db = \Config\Database::connect();
        $tahunAktifRow = $db->table('tahun_ajaran')->where('status', 'aktif')->get()->getRow();
        $idTahunAktif  = $tahunAktifRow ? $tahunAktifRow->id : 0;

        // Subquery untuk menghitung total bayar per tagihan (Lebih efisien daripada Group By besar)
        $subqueryPembayaran = "(SELECT COALESCE(SUM(p.jumlah_bayar), 0) FROM pembayaran p WHERE p.id_tagihan = tagihan.id)";

        $this->select("
                tagihan.*,
                siswa.nama_lengkap, 
                siswa.nis, 
                jenis_pembayaran.nama_pembayaran,
                kelas.nama_kelas,
                $subqueryPembayaran as total_terbayar_real
            ")
            ->join('siswa', 'siswa.id = tagihan.id_siswa', 'inner')
            ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran', 'inner')
            ->join('siswa_enrollment', 'siswa_enrollment.id_siswa = tagihan.id_siswa AND siswa_enrollment.id_tahun_ajaran = ' . $db->escape($idTahunAktif), 'left')
            ->join('kelas', 'kelas.id = siswa_enrollment.id_kelas', 'left');

        if ($id_siswa) {
            $this->where('tagihan.id_siswa', $id_siswa);
        }

        if ($bulan_jatuh_tempo) {
            $this->where("DATE_FORMAT(tagihan.tanggal_jatuh_tempo, '%Y-%m')", $bulan_jatuh_tempo);
        }

        return $this; // Return Builder untuk chain pagination
    }

    /**
     * SUMMARY: Menghitung Total Global (Tagihan, Terbayar, Piutang)
     * Digunakan untuk kartu statistik agar menghitung SEMUA data filter, bukan cuma halaman 1.
     */
    public function getGlobalSummary($kodeJenjang, $id_siswa = null, $bulan_jatuh_tempo = null)
    {
        $builder = $this->db->table($this->table);
        
        // Terapkan Filter yang sama
        if ($kodeJenjang) $builder->where('kode_jenjang', $kodeJenjang);
        if ($id_siswa) $builder->where('id_siswa', $id_siswa);
        if ($bulan_jatuh_tempo) $builder->where("DATE_FORMAT(tanggal_jatuh_tempo, '%Y-%m')", $bulan_jatuh_tempo);

        // Subquery Pembayaran untuk sum global
        // Note: Join ke pembayaran bisa berat jika data besar, opsi terbaik pakai subquery atau logic terpisah.
        // Disini kita pakai pendekatan simple sum join:
        
        $query = $builder->select('
            SUM(tagihan.jumlah) as total_tagihan,
            (SELECT SUM(jumlah_bayar) FROM pembayaran WHERE pembayaran.kode_jenjang = tagihan.kode_jenjang AND pembayaran.id_tagihan IN (SELECT id FROM tagihan as t2 WHERE t2.kode_jenjang = tagihan.kode_jenjang)) as total_dibayar_global
        ')->get()->getRow();
        
        // Alternatif Query yang lebih aman dan cepat: Hitung Total Tagihan Saja dulu
        // Total Terbayar dihitung terpisah agar tidak double counting akibat join
        
        // 1. Total Tagihan
        $b1 = $this->db->table($this->table);
        if ($kodeJenjang) $b1->where('kode_jenjang', $kodeJenjang);
        if ($id_siswa) $b1->where('id_siswa', $id_siswa);
        if ($bulan_jatuh_tempo) $b1->where("DATE_FORMAT(tanggal_jatuh_tempo, '%Y-%m')", $bulan_jatuh_tempo);
        $totalTagihan = $b1->selectSum('jumlah')->get()->getRow()->jumlah ?? 0;

        // 2. Total Terbayar (Join Tagihan untuk filter tanggal jatuh tempo)
        $b2 = $this->db->table('pembayaran')->join('tagihan', 'tagihan.id = pembayaran.id_tagihan');
        if ($kodeJenjang) $b2->where('tagihan.kode_jenjang', $kodeJenjang);
        if ($id_siswa) $b2->where('tagihan.id_siswa', $id_siswa);
        if ($bulan_jatuh_tempo) $b2->where("DATE_FORMAT(tagihan.tanggal_jatuh_tempo, '%Y-%m')", $bulan_jatuh_tempo);
        $totalDibayar = $b2->selectSum('pembayaran.jumlah_bayar')->get()->getRow()->jumlah_bayar ?? 0;

        return [
            'total_tagihan'  => (float) $totalTagihan,
            'total_dibayar'  => (float) $totalDibayar,
            'total_terutang' => (float) ($totalTagihan - $totalDibayar)
        ];
    }

    /**
     * HELPER: Proses status Lunas/Cicil untuk tampilan tabel
     */
    public function processStatusReal(array $data)
    {
        foreach ($data as &$item) {
            $val_jumlah = (float) $item['jumlah'];
            $terbayar   = (float) ($item['total_terbayar_real'] ?? 0);
            
            // Hitung sisa
            $item['sisa_tagihan'] = $val_jumlah - $terbayar;

            // Toleransi 1 rupiah untuk pembulatan desimal
            if ($terbayar >= ($val_jumlah - 1)) {
                $item['status_real'] = 'lunas';
            } elseif ($terbayar > 0) {
                $item['status_real'] = 'cicilan';
            } else {
                $item['status_real'] = 'belum_lunas';
            }
        }
        return $data;
    }
}