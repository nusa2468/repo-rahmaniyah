<?php

namespace App\Models\Keuangan;

use CodeIgniter\Model;

/**
 * Model Pembayaran
 * Mengelola transaksi realisasi pembayaran dan laporan pemasukan (Kas Masuk).
 */
class PembayaranModel extends Model
{
    protected $table      = 'pembayaran';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    
    // FITUR BARU: Mengaktifkan Soft Deletes untuk melindungi Audit Trail
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    // PERBAIKAN KRUSIAL: Menambahkan 'kode_jenjang' dan 'no_kwitansi'
    protected $allowedFields = [
        'kode_jenjang',      // Wajib untuk Filter Multi-Tenant
        'no_kwitansi',       // Bukti Transaksi Resmi
        'id_tagihan', 
        'id_user_admin', 
        'jumlah_bayar', 
        'tanggal_bayar', 
        'metode_pembayaran', 
        'bukti_bayar', 
        'keterangan',
        'deleted_at'
    ];

    /**
     * Helper internal untuk mencegah duplikasi JOIN pada tabel tagihan dan siswa.
     * Ini mencegah error #1066 "Not unique table/alias".
     */
    protected function _ensureSiswaTagihanJoined()
    {
        // Ambil string SQL sementara untuk mengecek apakah sudah ada join tagihan
        $sql = $this->builder()->getCompiledSelect(false);
        
        if (strpos($sql, 'JOIN `tagihan`') === false && strpos($sql, 'JOIN tagihan') === false) {
            $this->join('tagihan', 'tagihan.id = pembayaran.id_tagihan', 'inner')
                 ->join('siswa', 'siswa.id = tagihan.id_siswa', 'inner');
        }
        return $this;
    }

    /**
     * Scope untuk memfilter data berdasarkan Unit (SD/SMP/SMA).
     */
    public function scopeJenjang($kodeJenjang = null)
    {
        if ($kodeJenjang && strtoupper($kodeJenjang) !== 'GLOBAL') {
            $this->_ensureSiswaTagihanJoined();
            $this->where('siswa.kode_jenjang', $kodeJenjang);
        }
        return $this;
    }

    /**
     * Query dasar untuk laporan pemasukan dengan join lengkap.
     */
    public function getBaseLaporanQuery($startDate = null, $endDate = null)
    {
        $db = \Config\Database::connect();

        // Ambil tahun ajaran aktif
        $tahunAktifRow = $db->table('tahun_ajaran')->where('status', 'aktif')->get()->getRow();
        $idTahunAktif = $tahunAktifRow ? $tahunAktifRow->id : 0;

        // Pastikan join dasar tersedia
        $this->_ensureSiswaTagihanJoined();

        $builder = $this->select('
                pembayaran.*, 
                pembayaran.id as id_transaksi,
                siswa.nama_lengkap as nama_siswa, 
                siswa.nis, 
                tagihan.deskripsi as deskripsi_tagihan, 
                kelas.nama_kelas,
                IFNULL(users.nama_lengkap, "Sistem") as nama_admin
            ')
            // Join ke Enrollment & Kelas (Left Join untuk menghindari data hilang jika siswa belum di-enroll)
            ->join('siswa_enrollment', "siswa_enrollment.id_siswa = siswa.id AND siswa_enrollment.id_tahun_ajaran = " . $db->escape($idTahunAktif), 'left')
            ->join('kelas', 'kelas.id = siswa_enrollment.id_kelas', 'left')
            ->join('users', 'users.id = pembayaran.id_user_admin', 'left');

        // Filter tanggal hanya jika parameter diisi
        if ($startDate) {
            $builder->where('pembayaran.tanggal_bayar >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('pembayaran.tanggal_bayar <=', $endDate . ' 23:59:59');
        }

        return $builder->orderBy('pembayaran.tanggal_bayar', 'DESC')
                       ->orderBy('pembayaran.id', 'DESC');
    }

    /**
     * Mengambil total realisasi nominal pemasukan periode tertentu.
     * Digunakan untuk KPI dashboard laporan.
     */
    public function getTotalPemasukanPeriode($startDate, $endDate, $kodeJenjang = null)
    {
        $this->scopeJenjang($kodeJenjang);
        
        return (float) $this->selectSum('pembayaran.jumlah_bayar', 'total')
            ->where('pembayaran.tanggal_bayar >=', $startDate . ' 00:00:00')
            ->where('pembayaran.tanggal_bayar <=', $endDate . ' 23:59:59')
            ->get()->getRow()->total ?? 0;
    }

    /**
     * DASHBOARD HOME: Menghitung total realisasi pembayaran bulan ini.
     */
    public function getTotalRealisasiSppBulanIni()
    {
        $bulan = date('m');
        $tahun = date('Y');

        return (float) $this->selectSum('jumlah_bayar', 'total')
            ->where('MONTH(tanggal_bayar)', $bulan)
            ->where('YEAR(tanggal_bayar)', $tahun)
            ->get()->getRow()->total ?? 0;
    }

    /**
     * Mengambil riwayat pembayaran berdasarkan ID Tagihan tertentu.
     */
    public function getRiwayatByTagihan($id_tagihan)
    {
        if (empty($id_tagihan)) return [];

        return $this->select('pembayaran.*, IFNULL(users.nama_lengkap, "Sistem") as nama_admin')
            ->join('users', 'users.id = pembayaran.id_user_admin', 'left')
            ->where('pembayaran.id_tagihan', $id_tagihan)
            ->orderBy('pembayaran.tanggal_bayar', 'DESC')
            ->findAll();
    }
}