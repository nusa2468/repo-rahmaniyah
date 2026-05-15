<?php

namespace App\Controllers; // Biasanya Base Controller berada di namespace App\Controllers

use App\Models\EkstrakurikulerModel;
use App\Models\PesertaEkskulModel;
use App\Models\AlumniModel;
use App\Models\OsisModel; 
use App\Models\PrestasiSiswaModel;
use App\Models\GuruModel;
use App\Models\SiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\BkModel; // DITAMBAHKAN: Model untuk Bimbingan Konseling (BK)
use CodeIgniter\Controller; 

/**
 * Base Controller untuk Modul Kesiswaan.
 *
 * Controller ini berfungsi sebagai kelas induk (parent) bagi semua controller
 * di bawah modul Kesiswaan (e.g., OsisController, AlumniController, EkskulController, KesiswaanReportController).
 * Tujuannya adalah untuk menginisialisasi semua model dan helper yang sering digunakan
 * di modul Kesiswaan.
 */
class BaseKesiswaanController extends Controller
{
    /**
     * @var EkstrakurikulerModel
     */
    protected $ekskulModel;

    /**
     * @var PesertaEkskulModel
     */
    protected $pesertaEkskulModel;

    /**
     * @var AlumniModel
     */
    protected $alumniModel;

    /**
     * @var OsisModel 
     */
    protected $osisModel;

    /**
     * @var PrestasiSiswaModel
     */
    protected $prestasiModel;

    /**
     * @var BkModel 
     */
    protected $bkModel; // DITAMBAHKAN: Properti untuk Model BK

    /**
     * @var GuruModel
     */
    protected $guruModel;

    /**
     * @var SiswaModel
     */
    protected $siswaModel;

    /**
     * @var TahunAjaranModel
     */
    protected $tahunAjaranModel;

    // Data umum untuk view
    protected array $data = [];

    /**
     * Constructor: Inisialisasi Model dan Helper.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Panggil konstruktor/initController kelas induk
        parent::initController($request, $response, $logger);
        
        // 1. Inisialisasi semua model
        $this->ekskulModel = new EkstrakurikulerModel();
        $this->pesertaEkskulModel = new PesertaEkskulModel();
        $this->alumniModel = new AlumniModel();
        $this->osisModel = new OsisModel(); 
        $this->prestasiModel = new PrestasiSiswaModel();
        $this->bkModel = new BkModel(); // DITAMBAHKAN: Inisialisasi Model BK
        $this->guruModel = new GuruModel();
        $this->siswaModel = new SiswaModel();
        $this->tahunAjaranModel = new TahunAjaranModel();

        // 2. Load helper yang dibutuhkan
        helper('form');

        // 3. Set data default yang akan selalu ada di setiap view Kesiswaan
        $this->data = [
            'current_module' => 'kesiswaan',
            'title' => 'Modul Kesiswaan',
        ];
    }

    /**
     * Helper untuk menggabungkan data default dengan data spesifik controller.
     * @param array $additionalData Data spesifik yang akan ditambahkan atau menimpa data default.
     * @return array
     */
    protected function loadViewData(array $additionalData = []): array
    {
        return array_merge($this->data, $additionalData);
    }
}