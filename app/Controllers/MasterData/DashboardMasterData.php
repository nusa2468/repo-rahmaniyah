<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseMasterDataController;

/**
 * DashboardMasterData Controller
 * Mengelola tampilan ringkasan metrik KPI dan navigasi utama Master Data.
 */
class DashboardMasterData extends BaseMasterDataController
{
    public function index()
    {
        // [FIX UTAMA] LOAD HELPER MENU
        // Baris ini mengatasi error "Call to undefined function check_menu_access()"
        helper('menu'); 

        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        
        // Cek hak akses global (Yayasan/Pusat)
        $isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);

        // 1. Ambil Statistik Pegawai (Guru vs Staff)
        // Menggunakan SUM IF untuk efisiensi query
        $statsPegawai = $this->guruModel->select('
            COUNT(*) as total,
            SUM(CASE WHEN jenis_pegawai = "guru" THEN 1 ELSE 0 END) as total_guru,
            SUM(CASE WHEN jenis_pegawai IN ("staff", "penunjang") THEN 1 ELSE 0 END) as total_staff,
            SUM(CASE WHEN status_aktif = "aktif" THEN 1 ELSE 0 END) as total_aktif
        ')->get()->getRowArray();

        // 2. Ambil Statistik Siswa
        $siswaBuilder = $this->db->table('siswa');
        
        // Filter jenjang jika bukan user global
        if (!$isGlobal) {
            $siswaBuilder->where('kode_jenjang', $userJenjang);
        }

        $statsSiswa = $siswaBuilder->select('
            COUNT(*) as total,
            SUM(CASE WHEN status = "aktif" THEN 1 ELSE 0 END) as total_aktif,
            SUM(CASE WHEN jenis_kelamin = "L" THEN 1 ELSE 0 END) as total_l,
            SUM(CASE WHEN jenis_kelamin = "P" THEN 1 ELSE 0 END) as total_p
        ')->get()->getRowArray();

        // 3. Data untuk Grafik: Distribusi Siswa per Unit
        // Digunakan untuk Bar Chart
        $distribusiSiswa = $this->db->table('siswa')
            ->select('kode_jenjang, COUNT(*) as jumlah')
            ->groupBy('kode_jenjang')
            ->orderBy('jumlah', 'DESC') // Urutkan dari yang terbanyak
            ->get()->getResultArray();

        // 4. Data untuk Grafik: Komposisi Pegawai
        // [FIX DATA] Key disesuaikan dengan View: 'Staff/Penunjang'
        $komposisiPegawai = [
            'Guru' => (int)($statsPegawai['total_guru'] ?? 0),
            'Staff/Penunjang' => (int)($statsPegawai['total_staff'] ?? 0) 
        ];

        // Hitung total unit sekolah
        $totalUnit = $this->jenjangModel->countAllResults();

        $data = [
            'title'             => 'Dashboard Master Data',
            'stats_pegawai'     => $statsPegawai,
            'stats_siswa'       => $statsSiswa,
            'total_unit'        => $totalUnit,
            'chart_siswa_unit'  => $distribusiSiswa,
            'chart_pegawai'     => $komposisiPegawai,
            'is_global'         => $isGlobal,
            'user_jenjang'      => $userJenjang
        ];

        // Inject data global (user profile, sidebar menu, dll) lewat BaseController
        // Pastikan loadViewData ada di BaseController, jika tidak, gunakan $data saja
        $finalData = method_exists($this, 'loadViewData') ? $this->loadViewData($data) : $data;

        return view('masterdata/dashboard', $finalData);
    }
}