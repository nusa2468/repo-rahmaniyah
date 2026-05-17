<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\AsetBarangModel;
use App\Models\Sapras\AsetLokasiModel;
use App\Models\Sapras\AsetKategoriModel;
use App\Models\Sapras\AsetPengadaanModel;
use App\Models\Sapras\AsetPeminjamanModel;
use App\Models\Sapras\AsetPemeliharaanModel;

class DashboardController extends BaseController
{
    public function index()
    {
        // ===============================
        // 1. IDENTIFIKASI SCOPE OTORITAS
        // ===============================
        $kodeJenjang = session('kode_jenjang'); 
        $isGlobal = (empty($kodeJenjang) || strtoupper($kodeJenjang) === 'GLOBAL' || strtoupper($kodeJenjang) === 'YAYASAN');

        // ===============================
        // 2. INISIALISASI MODEL BARU
        // ===============================
        $kategoriModel     = new AsetKategoriModel();
        $lokasiModel       = new AsetLokasiModel();
        $barangModel       = new AsetBarangModel();
        $pengadaanModel    = new AsetPengadaanModel();
        $peminjamanModel   = new AsetPeminjamanModel();
        $pemeliharaanModel = new AsetPemeliharaanModel();

        // ===============================
        // 3. STATISTIK UTAMA (MASTER ASET)
        // ===============================
        
        // A. Total Katalog Aset
        if (!$isGlobal) $barangModel->where('kode_jenjang', $kodeJenjang);
        $totalAset = $barangModel->countAllResults();

        // B. Total Lokasi / Ruangan
        if (!$isGlobal) $lokasiModel->where('kode_jenjang', $kodeJenjang);
        $totalLokasi = $lokasiModel->countAllResults();

        // C. Valuasi Nilai Aset (Rp) - Sangat Penting untuk Finance
        if (!$isGlobal) $barangModel->where('kode_jenjang', $kodeJenjang);
        $dataNilai = $barangModel->selectSum('harga_perolehan')->first();
        $totalNilaiAset = $dataNilai['harga_perolehan'] ?? 0;

        // D. Kategori Aset Aktif
        $totalKategori = $kategoriModel->countAllResults();

        // ===============================
        // 4. STATISTIK KONDISI FISIK ASET
        // ===============================
        
        if (!$isGlobal) $barangModel->where('kode_jenjang', $kodeJenjang);
        $jmlBaik = $barangModel->where('kondisi', 'Baik')->countAllResults();

        if (!$isGlobal) $barangModel->where('kode_jenjang', $kodeJenjang);
        $jmlRingan = $barangModel->where('kondisi', 'Rusak Ringan')->countAllResults();

        if (!$isGlobal) $barangModel->where('kode_jenjang', $kodeJenjang);
        $jmlBerat = $barangModel->where('kondisi', 'Rusak Berat')->countAllResults();

        if (!$isGlobal) $barangModel->where('kode_jenjang', $kodeJenjang);
        $jmlAfkir = $barangModel->where('kondisi', 'Afkir/Dihapus')->countAllResults();

        $kondisi = [
            'baik'   => $jmlBaik,
            'ringan' => $jmlRingan,
            'berat'  => $jmlBerat,
            'afkir'  => $jmlAfkir
        ];

        // ===============================
        // 5. TRANSAKSIONAL (LIVE TRACKING)
        // ===============================
        
        // A. Peminjaman Aktif (Sedang diluar / telat dikembalikan)
        $qPeminjaman = $peminjamanModel->getPeminjamanBuilder()
                                       ->whereIn('aset_peminjaman.status', ['Dipinjam', 'Terlambat']);
        if (!$isGlobal) {
            $qPeminjaman->where('aset_barang.kode_jenjang', $kodeJenjang);
        }
        $peminjamanAktif = $qPeminjaman->countAllResults();

        // B. Pemeliharaan Aktif (Aset sedang diservis / Maintenance)
        $qPemeliharaan = $pemeliharaanModel->getPemeliharaanBuilder()
                                           ->whereIn('aset_pemeliharaan.status', ['Direncanakan', 'Sedang Proses']);
        if (!$isGlobal) {
            $qPemeliharaan->where('aset_barang.kode_jenjang', $kodeJenjang);
        }
        $pemeliharaanAktif = $qPemeliharaan->countAllResults();

        // C. Pengadaan / Proposal Belum Disetujui
        $qPengadaan = $pengadaanModel->getPengadaanBuilder($isGlobal ? null : $kodeJenjang)
                                     ->where('aset_pengadaan.status', 'Menunggu Approval');
        $pengadaanPending = $qPengadaan->countAllResults();

        // ===============================
        // 6. RENDER KE VIEW (UI)
        // ===============================
        return view('sapras/dashboard', [
            'title'        => 'Dashboard Manajemen Aset Terpadu',
            'kodeJenjang'  => $isGlobal ? 'GLOBAL' : $kodeJenjang,
            'summary'      => [
                'total_aset'         => $totalAset,
                'total_lokasi'       => $totalLokasi,
                'total_kategori'     => $totalKategori,
                'total_nilai_aset'   => $totalNilaiAset,
                'peminjaman_aktif'   => $peminjamanAktif,
                'pemeliharaan_aktif' => $pemeliharaanAktif,
                'pengadaan_pending'  => $pengadaanPending,
            ],
            'kondisi'      => $kondisi,
        ]);
    }
}