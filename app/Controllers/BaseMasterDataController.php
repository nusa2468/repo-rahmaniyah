<?php

namespace App\Controllers;

use App\Controllers\BaseController;

// Model Utama Master Data
use App\Models\JenjangModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\SiswaAkademikModel;
use App\Models\SiswaDemografiModel;
use App\Models\SiswaKeluargaModel;
use App\Models\TahunAjaranModel;
use App\Models\GrupSiswaModel;
use App\Models\MataPelajaranModel;
use App\Models\GuruModel;
use App\Models\KaryawanModel;
use App\Models\KurikulumModel;
use App\Models\UserModel;
use App\Models\JurusanModel;

// Model Kesiswaan (Integrasi Baru)
use App\Models\Kesiswaan\EkskulModel;
use App\Models\Kesiswaan\PesertaEkskulModel;
use App\Models\Kesiswaan\PrestasiSiswaModel;

// Model Khusus Akademik
use App\Models\KalenderPendidikanModel;
use App\Models\JadwalPelajaranModel;
use App\Models\AbsensiSiswaModel;
use App\Models\NilaiModel;
use App\Models\RaportModel;
use App\Models\KenaikanKelasModel;

// Model Pendukung Pegawai
use App\Models\RiwayatPendidikanModel;
use App\Models\PenugasanMengajarModel;

// Model Payroll & Keuangan
use App\Models\KomponenGajiModel;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Base Controller untuk semua modul Master Data.
 * Menyediakan instance model yang umum digunakan sehingga tidak perlu inisialisasi ulang di controller anak.
 */
abstract class BaseMasterDataController extends BaseController
{
    // Deklarasi properti model utama
    protected SiswaModel $siswaModel;
    protected SiswaAkademikModel $siswaAkademikModel;
    protected SiswaDemografiModel $siswaDemografiModel;
    protected SiswaKeluargaModel $siswaKeluargaModel;
    protected KelasModel $kelasModel;
    protected TahunAjaranModel $tahunAjaranModel;
    protected GrupSiswaModel $grupSiswaModel;
    protected MataPelajaranModel $mapelModel;
    protected JurusanModel $jurusanModel;
    protected KurikulumModel $kurikulumModel;
    protected JenjangModel $jenjangModel;
    protected UserModel $userModel;
    
    // Model Pegawai (Unified)
    protected GuruModel $guruModel;
    protected KaryawanModel $karyawanModel;
    
    // Model Kesiswaan (Tersedia untuk Siswa::show atau Dashboard Kesiswaan)
    protected EkskulModel $ekskulModel;
    protected PesertaEkskulModel $pesertaEkskulModel;
    protected PrestasiSiswaModel $prestasiSiswaModel;

    // Model Akademik Transaksional
    protected $kalenderPendidikanModel;
    protected $jadwalPelajaranModel;
    protected $absensiSiswaModel;
    protected $nilaiModel;
    protected $raportModel;
    protected $kenaikanKelasModel;
    
    // Model Pendukung lainnya
    protected $riwayatPendidikanModel;
    protected $penugasanMengajarModel;
    protected $komponenGajiModel;
    protected $CourseModel;

    protected $db;
    protected array $data = [];
    protected $helpers = ['form', 'url', 'html'];

    /**
     * Inisialisasi controller dan model.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->db = \Config\Database::connect();

        // 1. INSIALISASI MODEL UTAMA (Wajib Ada)
        $this->siswaModel          = new SiswaModel();
        $this->siswaAkademikModel  = new SiswaAkademikModel();
        $this->siswaDemografiModel = new SiswaDemografiModel();
        $this->siswaKeluargaModel  = new SiswaKeluargaModel();
        $this->jurusanModel        = new JurusanModel();
        $this->kelasModel          = new KelasModel();
        $this->tahunAjaranModel    = new TahunAjaranModel();
        $this->grupSiswaModel      = new GrupSiswaModel();
        $this->mapelModel          = new MataPelajaranModel();
        $this->kurikulumModel      = new KurikulumModel();
        $this->userModel           = new UserModel();
        
        // 2. MODEL PEGAWAI
        $this->guruModel           = new GuruModel();
        $this->karyawanModel       = new KaryawanModel();
        
        // 3. FAIL-SAFE JENJANG MODEL
        if (class_exists('App\Models\JenjangModel')) {
            $this->jenjangModel = new JenjangModel();
        } else {
            $this->jenjangModel = new class extends \CodeIgniter\Model {
                protected $table = 'jenjang_sekolah';
                protected $returnType = 'array';
            };
        }

        // 4. MODEL KESISWAAN (Sinkronisasi Fitur Baru)
        $this->ekskulModel        = new EkskulModel();
        $this->pesertaEkskulModel = new PesertaEkskulModel();
        
        // Cek keberadaan PrestasiSiswaModel (Jaga-jaga jika namespace berubah)
        if (class_exists('App\Models\Kesiswaan\PrestasiSiswaModel')) {
            $this->prestasiSiswaModel = new PrestasiSiswaModel();
        } elseif (class_exists('App\Models\PrestasiSiswaModel')) {
            $this->prestasiSiswaModel = new \App\Models\PrestasiSiswaModel();
        }

        // 5. FAIL-SAFE UNTUK MODEL AKADEMIK & OPSIONAL
        $this->_initOptionalModels();

        // 6. LOAD DATA GLOBAL UNTUK VIEW
        $this->data = $this->loadCommonData([
            'current_module' => 'masterdata',
            'title'          => 'Sistem Informasi Sekolah',
        ]);
    }

    /**
     * Inisialisasi model yang bersifat opsional untuk mencegah crash jika file belum ada.
     */
    private function _initOptionalModels()
    {
        $optionalModels = [
            'kalenderPendidikanModel' => KalenderPendidikanModel::class,
            'jadwalPelajaranModel'    => JadwalPelajaranModel::class,
            'absensiSiswaModel'       => AbsensiSiswaModel::class,
            'nilaiModel'              => NilaiModel::class,
            'raportModel'             => RaportModel::class,
            'kenaikanKelasModel'      => KenaikanKelasModel::class,
            'riwayatPendidikanModel'  => RiwayatPendidikanModel::class,
            'penugasanMengajarModel'  => PenugasanMengajarModel::class,
            'komponenGajiModel'       => KomponenGajiModel::class,
        ];

        foreach ($optionalModels as $property => $className) {
            if (class_exists($className)) {
                $this->$property = new $className();
            }
        }

        // Elearning Course Model (Spesifik Namespace)
        if (class_exists('App\Models\Elearning\CourseModel')) {
            $this->CourseModel = new \App\Models\Elearning\CourseModel();
        }
    }

    /**
     * Memuat data umum yang dibutuhkan oleh hampir semua View Master Data.
     */
    protected function loadCommonData(array $baseData = []): array
    {
        $tahunAjaranAktif = null;
        if ($this->db->tableExists('tahun_ajaran')) {
            $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        }

        $commonData = [
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
            'session'            => session(),
            'user_role'          => session()->get('role_name'),
            'user_jenjang'       => session()->get('kode_jenjang') ?? 'GLOBAL',
        ];

        return array_merge($baseData, $commonData);
    }

    /**
     * Helper untuk menggabungkan data internal dengan data tambahan dari controller anak.
     */
    protected function loadViewData(array $additionalData = []): array
    {
        return array_merge($this->data, $additionalData);
    }
}