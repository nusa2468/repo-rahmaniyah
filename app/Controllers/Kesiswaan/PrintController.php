<?php

namespace App\Controllers\Kesiswaan;

use App\Controllers\BaseController;
use App\Models\SettingsModel;

class PrintController extends BaseController
{
    protected $db;
    protected $settingsModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->settingsModel = new SettingsModel();
    }

    private function setExcelHeader($filename)
    {
        header("Content-type: application/vnd-ms-excel");
        header("Content-Disposition: attachment; filename=$filename.xls");
    }

    private function getIdentitasSekolah($jenjang)
    {
        $context = $jenjang ?: 'Global'; 
        $settings = $this->settingsModel->getSettingsAsArray($context);

        return [
            'nama'   => $settings['nama_sekolah'] ?? 'YAYASAN PENDIDIKAN GENERASI JUARA',
            'alamat' => $settings['alamat_sekolah'] ?? 'Jl. Pendidikan No. 123, Kota Harapan Indah',
            'kontak' => 'Telp: ' . ($settings['telepon_sekolah'] ?? '-') . ' | Email: ' . ($settings['email_sekolah'] ?? 'info@sekolah.sch.id'),
        ];
    }

    // --- 1. EKSKUL ---
    public function ekskul()
    {
        $jenjang = $this->request->getPost('kode_jenjang');
        $format  = $this->request->getPost('format');

        $builder = $this->db->table('kesiswaan_ekskul')
            ->select('kesiswaan_ekskul.*, p.nama_lengkap as nama_pembina')
            ->join('pegawai p', 'p.id = kesiswaan_ekskul.guru_pembina_id', 'left')
            ->where('kesiswaan_ekskul.deleted_at', null);

        if ($jenjang) $builder->where('kesiswaan_ekskul.kode_jenjang', $jenjang);

        $data = [
            'laporan'   => $builder->get()->getResultArray(),
            'judul'     => "Laporan Data Ekstrakurikuler " . ($jenjang ? "Unit $jenjang" : "Semua Unit"),
            'format'    => $format,
            'periode'   => "Tahun Ajaran " . date('Y'),
            'identitas' => $this->getIdentitasSekolah($jenjang)
        ];

        if ($format === 'excel') $this->setExcelHeader("Laporan_Ekskul_" . date('Ymd'));
        return view('kesiswaan/print/ekskul', $data);
    }

    // --- 2. BK (BIMBINGAN KONSELING) ---
    public function bk()
    {
        $jenjang   = $this->request->getPost('kode_jenjang'); // Jika dipost dari form
        $startDate = $this->request->getPost('start_date');
        $endDate   = $this->request->getPost('end_date');
        $status    = $this->request->getPost('status_kasus');
        $format    = $this->request->getPost('format');

        $builder = $this->db->table('kesiswaan_bk_catatan')
            ->select('kesiswaan_bk_catatan.*, s.nama_lengkap, s.nis, s.kode_jenjang, k.nama_kasus, k.poin, k.jenis')
            ->join('siswa s', 's.id = kesiswaan_bk_catatan.siswa_id')
            ->join('kesiswaan_bk_kategori k', 'k.id = kesiswaan_bk_catatan.bk_kategori_id')
            ->where('kesiswaan_bk_catatan.deleted_at', null);

        // Filter Jenjang (Jika ada inputan hidden/select jenjang di form BK tab cetak, kalau tidak ambil dari session user login)
        // Disini kita asumsikan form BK mengirim kode_jenjang jika user superadmin, atau kita filter berdasarkan data yang ada
        
        if ($startDate && $endDate) {
            $builder->where("tanggal_kejadian BETWEEN '$startDate' AND '$endDate'");
        }
        if ($status) {
            $builder->where('status_penyelesaian', $status);
        }
        
        $data = [
            'laporan'   => $builder->orderBy('tanggal_kejadian', 'DESC')->get()->getResultArray(),
            'judul'     => "Laporan Bimbingan Konseling (BK)",
            'format'    => $format,
            'periode'   => ($startDate && $endDate) ? date('d/m/Y', strtotime($startDate)) . " s.d. " . date('d/m/Y', strtotime($endDate)) : "Semua Periode",
            'identitas' => $this->getIdentitasSekolah($jenjang)
        ];

        if ($format === 'excel') $this->setExcelHeader("Laporan_BK_" . date('Ymd'));
        return view('kesiswaan/print/bk', $data);
    }

    // --- 3. PRESTASI SISWA ---
    public function prestasi()
    {
        $jenjang = $this->request->getPost('kode_jenjang'); // Perlu ditambahkan input hidden di view tab cetak jika ingin filter jenjang
        $tingkat = $this->request->getPost('tingkat');
        $ta      = $this->request->getPost('tahun_ajar_id'); // ID Tahun Ajar
        $format  = $this->request->getPost('format');

        $builder = $this->db->table('kesiswaan_prestasi')
            ->select('kesiswaan_prestasi.*, s.nama_lengkap, s.nis, s.kode_jenjang')
            ->join('siswa s', 's.id = kesiswaan_prestasi.siswa_id')
            ->where('kesiswaan_prestasi.deleted_at', null);

        if ($tingkat) $builder->where('tingkat', $tingkat);
        if ($ta) $builder->where('tahun_ajar_id', $ta); // Pastikan kolom di DB adalah tahun_ajar_id sesuai fix sebelumnya

        $data = [
            'laporan'   => $builder->orderBy('tanggal_prestasi', 'DESC')->get()->getResultArray(),
            'judul'     => "Laporan Prestasi Siswa",
            'format'    => $format,
            'periode'   => "Tahun Ajaran Aktif", // Bisa disesuaikan mengambil nama tahun ajar dari DB
            'identitas' => $this->getIdentitasSekolah($jenjang)
        ];

        if ($format === 'excel') $this->setExcelHeader("Laporan_Prestasi_" . date('Ymd'));
        return view('kesiswaan/print/prestasi', $data);
    }

    // --- 4. PRESENSI KEGIATAN ---
    public function presensi()
    {
        $ekskulId  = $this->request->getPost('ekskul_id');
        $startDate = $this->request->getPost('start_date');
        $endDate   = $this->request->getPost('end_date');
        $format    = $this->request->getPost('format');

        $builder = $this->db->table('kesiswaan_ekskul_presensi')
            ->select('kesiswaan_ekskul_presensi.*, e.nama_ekskul, e.kode_jenjang')
            ->join('kesiswaan_ekskul e', 'e.id = kesiswaan_ekskul_presensi.ekskul_id')
            ->where('kesiswaan_ekskul_presensi.deleted_at', null);

        if ($ekskulId) $builder->where('ekskul_id', $ekskulId);
        if ($startDate && $endDate) {
            $builder->where("tanggal BETWEEN '$startDate' AND '$endDate'");
        }

        $result = $builder->orderBy('tanggal', 'ASC')->get()->getResultArray();
        
        // Ambil info jenjang dari data pertama untuk kop surat
        $jenjang = $result[0]['kode_jenjang'] ?? '';

        $data = [
            'laporan'   => $result,
            'judul'     => "Jurnal & Rekap Presensi Kegiatan",
            'format'    => $format,
            'periode'   => ($startDate && $endDate) ? date('d/m/Y', strtotime($startDate)) . " s.d. " . date('d/m/Y', strtotime($endDate)) : "Semua Periode",
            'identitas' => $this->getIdentitasSekolah($jenjang)
        ];

        if ($format === 'excel') $this->setExcelHeader("Laporan_Presensi_" . date('Ymd'));
        return view('kesiswaan/print/presensi', $data);
    }

    // --- 5. ALUMNI ---
    public function alumni()
    {
        $jenjang      = $this->request->getPost('kode_jenjang'); // Perlu input hidden di view jika ingin filter unit
        $tahunLulus   = $this->request->getPost('tahun_lulus');
        $status       = $this->request->getPost('status_kegiatan');
        $format       = $this->request->getPost('format');

        $builder = $this->db->table('kesiswaan_alumni')
            ->select('kesiswaan_alumni.*, s.nama_lengkap, s.nis, s.kode_jenjang')
            ->join('siswa s', 's.id = kesiswaan_alumni.siswa_id')
            ->where('kesiswaan_alumni.deleted_at', null);

        if ($tahunLulus) $builder->where('tahun_lulus', $tahunLulus);
        if ($status) $builder->where('status_kegiatan', $status);
        // Jika jenjang spesifik diminta (misal dari superadmin) atau otomatis dari data siswa
        // Kita biarkan query join di atas mengambil semua, nanti view bisa menampilkan kolom unit.

        $data = [
            'laporan'   => $builder->orderBy('tahun_lulus', 'DESC')->get()->getResultArray(),
            'judul'     => "Laporan Tracer Study Alumni",
            'format'    => $format,
            'periode'   => $tahunLulus ? "Lulusan Tahun $tahunLulus" : "Semua Angkatan",
            'identitas' => $this->getIdentitasSekolah($jenjang)
        ];

        if ($format === 'excel') $this->setExcelHeader("Laporan_Alumni_" . date('Ymd'));
        return view('kesiswaan/print/alumni', $data);
    }
}