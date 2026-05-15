<?php

namespace App\Controllers;

use App\Controllers\BaseController;

// Models Umum
use App\Models\TahunAjaranModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\SiswaAkademikModel; 
use App\Models\GuruModel;
use App\Models\MataPelajaranModel;
use App\Models\KurikulumModel; 
// --- MODEL SISWA TAMBAHAN ---
use App\Models\SiswaDemografiModel; 
use App\Models\SiswaKeluargaModel; 
// --- MODEL ENROLLMENT ---
use App\Models\SiswaEnrollmentModel;

// Models Khusus Akademik
use App\Models\KalenderPendidikanModel;
use App\Models\JadwalPelajaranModel;
use App\Models\AbsensiSiswaModel;
use App\Models\NilaiModel; 
use App\Models\RaportModel;
use App\Models\KenaikanKelasModel;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseAkademikController
 * Base Controller untuk semua modul Akademik.
 * Menyediakan inisialisasi model dan konteks akademik (Tahun Ajaran Aktif).
 * * Update: Menjaga semua properti model tetap lengkap untuk stabilitas sistem.
 */
abstract class BaseAkademikController extends BaseController 
{
    // --- Model Properties (Lengkap sesuai permintaan) ---
    // Menggunakan Type Hinting nullable agar aman jika inisialisasi gagal
    protected ?TahunAjaranModel $tahunAjaranModel = null;
    protected ?KelasModel $kelasModel = null;
    protected ?SiswaModel $siswaModel = null; 
    protected ?SiswaAkademikModel $siswaAkademikModel = null; 
    protected ?SiswaEnrollmentModel $siswaEnrollmentModel = null; 
    protected ?SiswaDemografiModel $siswaDemografiModel = null; 
    protected ?SiswaKeluargaModel $siswaKeluargaModel = null; 
    protected ?GuruModel $guruModel = null;
    protected ?MataPelajaranModel $mapelModel = null;
    protected ?KurikulumModel $kurikulumModel = null; 

    protected ?KalenderPendidikanModel $kalenderPendidikanModel = null;
    protected ?JadwalPelajaranModel $jadwalPelajaranModel = null;
    protected ?AbsensiSiswaModel $absensiSiswaModel = null;
    protected ?NilaiModel $nilaiModel = null; 
    protected ?RaportModel $raportModel = null;
    protected ?KenaikanKelasModel $kenaikanKelasModel = null;
    
    // --- Konteks Data ---
    // Note: Jika di BaseController tidak ada $tahunAjaranAktif, deklarasi ini aman.
    protected $tahunAjaranAktif = null; 
    protected array $data = [];
    protected ?int $idGuruAktif = null; 

    /**
     * Constructor: Inisialisasi Model dan Konteks Akademik.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Panggil initController kelas induk (BaseController)
        parent::initController($request, $response, $logger);
        
        // Gunakan properti $this->session yang sudah diinisialisasi di BaseController
        $session = $this->session;

        try {
            // 1. Inisialisasi Model Kritis
            // Menggunakan helper model() untuk fleksibilitas pemanggilan class
            $this->tahunAjaranModel      = model(TahunAjaranModel::class);
            $this->kelasModel            = model(KelasModel::class);
            $this->siswaModel            = model(SiswaModel::class);
            $this->siswaAkademikModel    = model(SiswaAkademikModel::class); 
            $this->siswaEnrollmentModel  = model(SiswaEnrollmentModel::class); 
            $this->siswaDemografiModel   = model(SiswaDemografiModel::class);
            $this->siswaKeluargaModel    = model(SiswaKeluargaModel::class);
            $this->guruModel             = model(GuruModel::class);
            $this->mapelModel            = model(MataPelajaranModel::class);
            $this->kurikulumModel        = model(KurikulumModel::class); 

            // 2. Inisialisasi Model Khusus Akademik
            $this->kalenderPendidikanModel = model(KalenderPendidikanModel::class);
            $this->jadwalPelajaranModel    = model(JadwalPelajaranModel::class);
            $this->absensiSiswaModel       = model(AbsensiSiswaModel::class);
            $this->nilaiModel              = model(NilaiModel::class); 
            $this->raportModel             = model(RaportModel::class);
            $this->kenaikanKelasModel      = model(KenaikanKelasModel::class);

            // 3. Set Konteks Akademik
            if ($this->tahunAjaranModel) {
                $this->tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
            }

            // 4. Set ID Guru Aktif dari Session
            $this->idGuruAktif = $session->get('id_guru_login') ?? $session->get('guru_id') ?? null;
            
        } catch (\Throwable $e) {
            // Tangkap semua error Kritis agar aplikasi tidak langsung mati
            log_message('critical', 'Gagal Inisialisasi Model di BaseAkademikController: ' . $e->getMessage());
            $session->setFlashdata('error', 'KRITIS: Gagal memuat komponen database akademik. Silakan hubungi admin. Detail: ' . $e->getMessage());
        }

        // 5. Muat Data Umum untuk View
        $this->data = $this->loadCommonData([
            'current_module'     => 'akademik', 
            'title'              => 'Modul Akademik',
            'tahun_ajaran_aktif' => $this->tahunAjaranAktif, 
            'id_guru_aktif'      => $this->idGuruAktif,
            'user_data'          => $this->userData // dari BaseController
        ]);
        
        // Peringatan jika Tahun Ajaran belum diset
        if (empty($this->tahunAjaranAktif) && !$session->getFlashdata('error')) {
            $session->setFlashdata('warning', 'Peringatan: Tahun Ajaran AKTIF tidak ditemukan di database.');
        }
    }

    /**
     * Memuat data umum yang dibutuhkan oleh banyak view di modul akademik.
     */
    protected function loadCommonData(array $baseData = []): array
    {
        return $baseData;
    }

    /**
     * Helper untuk menggabungkan data default dengan data spesifik controller/method.
     */
    protected function loadViewData(array $additionalData = []): array
    {
        return array_merge($this->data, $additionalData);
    }
}