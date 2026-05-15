<?php

namespace App\Controllers\Sapras;

use App\Controllers\BaseController;
use App\Models\Sapras\TanahModel;
use App\Models\Sapras\GedungModel;
use App\Models\Sapras\RuanganModel;
use App\Models\Sapras\PeralatanModel;
use App\Models\Sapras\InventarisModel;

class DashboardController extends BaseController
{
    public function index()
    {
        // ===============================
        // 1. Ambil scope unit (DINAMIS)
        // ===============================
        $kodeJenjang = session('kode_jenjang'); // null = yayasan / global

        // ===============================
        // 2. Init model
        // ===============================
        $tanahModel      = new TanahModel();
        $gedungModel     = new GedungModel();
        $ruanganModel    = new RuanganModel();
        $peralatanModel  = new PeralatanModel();
        $inventarisModel = new InventarisModel();

        // ===============================
        // 3. SUMMARY (AGREGAT) - Safe Access Logic
        // ===============================
        
        // Total Luas Tanah
        $dataTanah = $tanahModel->byJenjang($kodeJenjang)->selectSum('luas')->first();
        $totalTanah = $dataTanah['luas'] ?? 0;

        // Total Luas Gedung
        $dataGedung = $gedungModel->byJenjang($kodeJenjang)->selectSum('luas')->first();
        $totalGedung = $dataGedung['luas'] ?? 0;

        // Total Kapasitas Ruangan
        $dataRuangan = $ruanganModel->byJenjang($kodeJenjang)->selectSum('kapasitas')->first();
        $totalKapasitas = $dataRuangan['kapasitas'] ?? 0;

        // Total Item (Peralatan + Inventaris)
        $dataAlat = $peralatanModel->byJenjang($kodeJenjang)->selectSum('jumlah')->first();
        $sumPeralatan = $dataAlat['jumlah'] ?? 0;

        $dataInv = $inventarisModel->byJenjang($kodeJenjang)->selectSum('jumlah')->first();
        $sumInventaris = $dataInv['jumlah'] ?? 0;

        $totalItem = $sumPeralatan + $sumInventaris;

        // ===============================
        // 4. COUNT DATA (Jumlah Record)
        // ===============================
        // Menggunakan instance yang sama (Model CI4 otomatis reset builder setelah query)
        $count = [
            'tanah'      => $tanahModel->byJenjang($kodeJenjang)->countAllResults(),
            'gedung'     => $gedungModel->byJenjang($kodeJenjang)->countAllResults(),
            'ruangan'    => $ruanganModel->byJenjang($kodeJenjang)->countAllResults(),
            'peralatan'  => $peralatanModel->byJenjang($kodeJenjang)->countAllResults(),
            'inventaris' => $inventarisModel->byJenjang($kodeJenjang)->countAllResults(),
        ];

        // ===============================
        // 5. KONDISI PERALATAN (Statistik Kondisi)
        // ===============================
        
        $dataBaik = $peralatanModel->byJenjang($kodeJenjang)->where('kondisi', 'Baik')->selectSum('jumlah')->first();
        $jmlBaik = $dataBaik['jumlah'] ?? 0;

        $dataRingan = $peralatanModel->byJenjang($kodeJenjang)->where('kondisi', 'Rusak Ringan')->selectSum('jumlah')->first();
        $jmlRingan = $dataRingan['jumlah'] ?? 0;

        $dataBerat = $peralatanModel->byJenjang($kodeJenjang)->where('kondisi', 'Rusak Berat')->selectSum('jumlah')->first();
        $jmlBerat = $dataBerat['jumlah'] ?? 0;

        $kondisi = [
            'baik'   => $jmlBaik,
            'ringan' => $jmlRingan,
            'berat'  => $jmlBerat,
        ];

        // ===============================
        // 6. KIRIM KE VIEW
        // ===============================
        return view('sapras/dashboard', [
            'title'       => 'Dashboard Statistik Sapras',
            'summary'     => [
                'total_tanah_m2'  => $totalTanah,
                'total_gedung_m2' => $totalGedung,
                'total_kapasitas' => $totalKapasitas,
                'total_item'      => $totalItem,
            ],
            'count'       => $count,
            'kondisi'     => $kondisi,
            'kodeJenjang' => $kodeJenjang, // Untuk badge / label di view
        ]);
    }
}