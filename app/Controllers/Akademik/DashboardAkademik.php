<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseAkademikController;

/**
 * DashboardAkademik Controller
 * Pusat analisis data proses belajar mengajar, absensi, dan penilaian.
 * Implementasi Role Scoping 'GLOBAL' & 'UNIT' (Anti-Bocor Data).
 */
class DashboardAkademik extends BaseAkademikController
{
    /**
     * Menampilkan dashboard akademik dengan filter otomatis berdasarkan hak akses.
     */
    public function index()
    {
        // 1. IDENTIFIKASI OTORITAS (Sesuai HakAksesModel)
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);
        
        $tahunAjaranId = $this->tahunAjaranAktif['id'] ?? 0;

        // -------------------------------------------------------------------------
        // 1. STATISTIK PESERTA DIDIK (FILTERED BY UNIT)
        // -------------------------------------------------------------------------
        $siswaBuilder = $this->db->table('siswa_enrollment se')
            ->join('siswa s', 's.id = se.id_siswa')
            ->join('kelas k', 'k.id = se.id_kelas')
            ->where('se.id_tahun_ajaran', $tahunAjaranId)
            ->where('se.status_akademik', 'Aktif')
            // 'se' tidak punya deleted_at, gunakan filter s dan k yang punya soft delete
            ->where('s.deleted_at', null)
            ->where('k.deleted_at', null);
        
        if (!$isGlobal) {
            // Proteksi: Admin unit hanya menghitung siswa di unitnya sendiri
            $siswaBuilder->where('k.kode_jenjang', $userJenjang);
        }

        $totalSiswa = $siswaBuilder->countAllResults();

        // -------------------------------------------------------------------------
        // 2. STATISTIK ROMBEL / KELAS AKTIF (FILTERED BY UNIT)
        // -------------------------------------------------------------------------
        $kelasBuilder = $this->db->table('kelas')
            ->where('id_tahun_ajaran', $tahunAjaranId)
            ->where('is_aktif', 1)
            ->where('deleted_at', null);

        if (!$isGlobal) {
            $kelasBuilder->where('kode_jenjang', $userJenjang);
        }
        $totalKelas = $kelasBuilder->countAllResults();

        // -------------------------------------------------------------------------
        // 3. KPI KEHADIRAN HARI INI (REAL-TIME FILTERED)
        // -------------------------------------------------------------------------
        $tglHariIni = date('Y-m-d');
        $absensiBuilder = $this->db->table('absensi_siswa abs')
            ->join('siswa s', 's.id = abs.id_siswa')
            ->where('abs.tanggal', $tglHariIni)
            ->where('abs.deleted_at', null)
            ->where('s.deleted_at', null);
        
        if (!$isGlobal) {
            $absensiBuilder->where('s.kode_jenjang', $userJenjang);
        }

        // Pakai alias 'abs.status' untuk menghindari ambiguous column 'status'
        $countAbsensi = $absensiBuilder->select('
            SUM(CASE WHEN abs.status = "hadir" THEN 1 ELSE 0 END) as hadir,
            COUNT(abs.id) as total
        ')->get()->getRowArray();

        $persenHadir = ($countAbsensi['total'] ?? 0) > 0 
            ? round(($countAbsensi['hadir'] / $countAbsensi['total']) * 100) 
            : 0;

        // -------------------------------------------------------------------------
        // 4. STATUS PENERBITAN RAPOR (FILTERED BY UNIT)
        // -------------------------------------------------------------------------
        $raporBuilder = $this->db->table('raport r')
            ->join('siswa_enrollment se', 'se.id = r.id_enrollment')
            ->join('kelas k', 'k.id = se.id_kelas')
            ->where('se.id_tahun_ajaran', $tahunAjaranId);
        
        if (!$isGlobal) {
            $raporBuilder->where('k.kode_jenjang', $userJenjang);
        }

        $statsRapor = $raporBuilder->select('
            SUM(CASE WHEN r.status_raport = "Published" THEN 1 ELSE 0 END) as published,
            COUNT(r.id) as total
        ')->get()->getRowArray();

        // -------------------------------------------------------------------------
        // 5. TREND ABSENSI 7 HARI TERAKHIR (DYNAMICS SCOPE)
        // -------------------------------------------------------------------------
        $trendBuilder = $this->db->table('absensi_siswa abs')
            ->select('abs.tanggal, COUNT(abs.id) as total_hadir')
            ->join('siswa s', 's.id = abs.id_siswa')
            ->where('abs.status', 'hadir')
            ->where('abs.tanggal >=', date('Y-m-d', strtotime('-7 days')))
            ->where('abs.deleted_at', null)
            ->where('s.deleted_at', null);

        if (!$isGlobal) {
            $trendBuilder->where('s.kode_jenjang', $userJenjang);
        }

        $trendAbsensi = $trendBuilder->groupBy('abs.tanggal')
            ->orderBy('abs.tanggal', 'ASC')
            ->get()->getResultArray();

        // 6. PACKAGING DATA UNTUK VIEW
        $data = [
            'title'             => 'Intelligence Dashboard Akademik',
            'current_module'    => 'akademik',
            'total_siswa'       => $totalSiswa,
            'total_kelas'       => $totalKelas,
            'persen_hadir'      => $persenHadir,
            'stats_rapor'       => $statsRapor,
            'trend_absensi'     => $trendAbsensi,
            'is_global'         => $isGlobal,
            'user_jenjang'      => $userJenjang, // FIXED: Menggunakan variabel $userJenjang yang sudah didefinisikan
            'tahun_ajaran_aktif' => $this->tahunAjaranAktif
        ];

        return view('akademik/dashboard', $this->loadViewData($data));
    }
}