<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\GuruModel;
use App\Models\KaryawanModel;
use App\Models\TahunAjaranModel;
use App\Models\KalenderPendidikanModel;
use App\Models\KelasModel;
use App\Models\Keuangan\TagihanModel;
use App\Models\Keuangan\PembayaranModel;

class Home extends BaseController
{
    protected $siswaModel;
    protected $guruModel;
    protected $karyawanModel;
    protected $tahunAjaranModel;
    protected $kalenderPendidikanModel;
    protected $kelasModel;
    protected $tagihanModel;
    protected $pembayaranModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        $this->guruModel = new GuruModel();
        $this->karyawanModel = new KaryawanModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
        $this->kalenderPendidikanModel = new KalenderPendidikanModel();
        
        // Pastikan Model ini ada
        $this->kelasModel = new KelasModel();

        $this->tagihanModel = new TagihanModel();
        $this->pembayaranModel = new PembayaranModel();
    }

    public function index(): string
    {
        // 1. Data Ringkasan
        $totalSiswaAktif = $this->siswaModel->countAllSiswaAktif();
        $totalGuruAktif = $this->guruModel->countAllGuruAktif();
        $totalKaryawanAktif = $this->karyawanModel->countAllKaryawanAktif();
        
        // Data Tambahan (Total Kelas)
        $totalKelas = $this->kelasModel->countAllResults();

        // 2. Data Keuangan
        $totalTargetSpp = $this->tagihanModel->getTotalTargetTagihanBulanIni();
        $totalRealisasiSpp = $this->pembayaranModel->getTotalRealisasiSppBulanIni();
        
        $capaianSpp = ($totalTargetSpp > 0) ? round(($totalRealisasiSpp / $totalTargetSpp) * 100) : 0;

        // 3. Data Grafik & Lainnya
        // Pastikan method ini ada di SiswaModel Anda dan me-return ARRAY
        $dataGrafikTingkat = $this->siswaModel->getSiswaAktifGroupedByTingkat();
        
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        
        $events = [];
        if ($tahunAjaranAktif) {
            $events = $this->kalenderPendidikanModel
                           ->where('tahun_ajaran_id', $tahunAjaranAktif['id'])
                           ->findAll();
        }

        // Penyusunan Data untuk View
        $data = [
            'title'              => 'Dashboard',
            'tahunAjaran'        => $tahunAjaranAktif['tahun_ajaran'] ?? 'Belum Diatur',
            'siswaAktif'         => $totalSiswaAktif,
            'guruAktif'          => $totalGuruAktif,
            'karyawanAktif'      => $totalKaryawanAktif,
            'totalKelas'         => $totalKelas,
            'capaianSpp'         => $capaianSpp,
            'realisasiSpp'       => $totalRealisasiSpp,
            
            // PERUBAHAN PENTING:
            // Kirim Array ASLI saja. Jangan di-json_encode di sini.
            // Biarkan View yang melakukan encoding.
            'dataGrafikTingkat'  => $dataGrafikTingkat,
            'calendarEvents'     => $events 
        ];
        
        return view('dashboard', $data);
    }
}