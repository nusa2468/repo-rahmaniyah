<?php

namespace App\Controllers\Kesiswaan;

use App\Controllers\BaseController;
use App\Models\Kesiswaan\EkskulModel;
use App\Models\Kesiswaan\OrganisasiModel;
use App\Models\Kesiswaan\BkModel;
use App\Models\Kesiswaan\AlumniModel;
use App\Models\Kesiswaan\PrestasiSiswaModel; 
use App\Models\JenjangModel; // ADDED: Import JenjangModel

class DashboardKesiswaanController extends BaseController
{
    protected $ekskulModel;
    protected $organisasiModel;
    protected $bkModel;
    protected $alumniModel;
    protected $prestasiModel;
    protected $jenjangModel; // ADDED
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->ekskulModel     = new EkskulModel();
        $this->organisasiModel = new OrganisasiModel();
        $this->bkModel         = new BkModel();
        $this->alumniModel     = new AlumniModel();
        $this->prestasiModel   = new PrestasiSiswaModel(); 
        $this->jenjangModel    = new JenjangModel(); // ADDED: Instansiasi
        
        $this->session         = session();
        $this->db              = \Config\Database::connect();
    }

    public function index()
    {
        $tab = $this->request->getGet('tab') ?? 'dashboard';
        
        // Ambil konteks dinamis
        $jenjang = $this->session->get('kode_jenjang') ?? $this->session->get('kode_unit');
        $isGlobal = in_array(strtoupper($jenjang ?? ''), ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT']);

        // --- 0. FETCH MASTER JENJANG (DYNAMIC MENU) ---
        // Mengambil daftar jenjang aktif untuk dropdown filter & modal
        $jenjangList = $this->jenjangModel->getDropdownOptions();

        // --- 1. FETCH DATA SISWA AKTIF ---
        $builderSiswa = $this->db->table('siswa')
            ->select('id, nama_lengkap, nis, kode_jenjang') 
            ->orderBy('nama_lengkap', 'ASC');
            
        if ($jenjang && !$isGlobal) {
            $builderSiswa->where('kode_jenjang', $jenjang);
        }
        $siswaList = $builderSiswa->get()->getResultArray();

        // --- 2. FETCH DATA GURU/PEGAWAI ---
        $guruList = $this->db->table('pegawai')
            ->select('id, nama_lengkap, nip')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'           => 'Modul Kesiswaan - ' . ($jenjang ?? 'Global'),
            'tab'             => $tab,
            'jenjang'         => $jenjang,
            'jenjang_list'    => $jenjangList, // ADDED: Kirim ke View
            'navigation'      => $this->getNavigation(),
            'siswa_list'      => $siswaList,
            'guru_list'       => $guruList,
            'filter_kategori' => $this->request->getGet('kategori'),
            'filter_hari'     => $this->request->getGet('hari'),
            'filter_sort'     => $this->request->getGet('sort'),
        ];

        // --- 3. SWITCH TAB LOGIC ---
        switch ($tab) {
            case 'ekskul':
                $filters = [
                    'kategori' => $this->request->getGet('kategori'),
                    'hari'     => $this->request->getGet('hari')
                ];
                $sort = $this->request->getGet('sort');
                $data['ekskul_list'] = $this->ekskulModel->getEkskul($filters, $sort);
                break;

            case 'ekskul_anggota':
                $data['anggota_list']  = $this->ekskulModel->getAllAnggota();
                $data['master_ekskul'] = $this->ekskulModel->getEkskul(); 
                break;

            case 'ekskul_presensi':
                $data['presensi_list'] = $this->ekskulModel->getAllPresensi();
                $data['master_ekskul'] = $this->ekskulModel->getEkskul();
                $data['all_anggota']   = $this->ekskulModel->getAllAnggota(); 
                break;

            case 'organisasi':
                $data['organisasi_list'] = $this->organisasiModel->getOrganisasi();
                break;

            case 'bk':
                $data['bk_list'] = $this->bkModel->getCatatanBK();
                $data['kategori_bk'] = $this->bkModel->getKategoriBK();
                break;

            case 'alumni':
                $data['alumni_list'] = $this->alumniModel->getAlumni();
                break;
                
            case 'prestasi':
                $data['prestasi_list'] = $this->prestasiModel->getPrestasiDetail();
                break;

            case 'cetak':
                $data['ekskul_list'] = $this->ekskulModel->getEkskul(); 
                break;

            default: // Dashboard Utama
                $builderEkskul = $this->ekskulModel->builder()->where('deleted_at', null);
                $this->scopeDataManual($builderEkskul, 'kode_jenjang');
                $totalEkskul = $builderEkskul->countAllResults();

                $builderBk = $this->bkModel->builder()->where('deleted_at', null);
                $this->scopeDataManual($builderBk, 'kode_jenjang');
                $totalKasus = $builderBk->countAllResults();

                $builderAlumni = $this->alumniModel->builder()->where('deleted_at', null);
                $this->scopeDataManual($builderAlumni, 'kode_jenjang');
                $totalAlumni = $builderAlumni->countAllResults();
                
                $builderPrestasi = $this->prestasiModel->builder()
                    ->join('siswa', 'siswa.id = kesiswaan_prestasi.siswa_id') 
                    ->where('kesiswaan_prestasi.deleted_at', null);
                if ($jenjang && !$isGlobal) {
                    $builderPrestasi->where('siswa.kode_jenjang', $jenjang);
                }
                $totalPrestasi = $builderPrestasi->countAllResults();
                
                $builderAnggota = $this->db->table('kesiswaan_ekskul_anggota')
                    ->join('siswa', 'siswa.id = kesiswaan_ekskul_anggota.siswa_id')
                    ->where('kesiswaan_ekskul_anggota.deleted_at', null);
                if ($jenjang && !$isGlobal) {
                    $builderAnggota->where('siswa.kode_jenjang', $jenjang);
                }
                $totalAnggota = $builderAnggota->countAllResults();

                $builderPresensi = $this->db->table('kesiswaan_ekskul_presensi')
                    ->join('kesiswaan_ekskul', 'kesiswaan_ekskul.id = kesiswaan_ekskul_presensi.ekskul_id')
                    ->where('kesiswaan_ekskul_presensi.deleted_at', null);
                if ($jenjang && !$isGlobal) {
                    $builderPresensi->where('kesiswaan_ekskul.kode_jenjang', $jenjang);
                }
                $totalPresensi = $builderPresensi->countAllResults();

                $data['stats'] = [
                    'total_ekskul'   => $totalEkskul,
                    'total_kasus'    => $totalKasus,
                    'total_alumni'   => $totalAlumni,
                    'total_prestasi' => $totalPrestasi,
                    'total_anggota'  => $totalAnggota,
                    'total_presensi' => $totalPresensi 
                ];
                
                $data['prestasi_list'] = $this->prestasiModel->getPrestasiDetail();
                break;
        }

        return view('kesiswaan/index', $data);
    }

    private function scopeDataManual($builder, $field = 'kode_jenjang')
    {
        $context = $this->session->get('kode_jenjang') ?? $this->session->get('kode_unit');
        $isGlobal = in_array(strtoupper($context ?? ''), ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT']);

        if ($context && !$isGlobal) {
            $builder->where($field, $context);
        }
    }

    private function getNavigation()
    {
        return [
            'dashboard'       => ['label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            'ekskul'          => ['label' => 'Data Ekskul', 'icon' => 'activity'],
            'ekskul_anggota'  => ['label' => 'Anggota Ekskul', 'icon' => 'users'],
            'ekskul_presensi' => ['label' => 'Presensi', 'icon' => 'clipboard-check'],
            'organisasi'      => ['label' => 'OSIS/MPK', 'icon' => 'flag'],
            'bk'              => ['label' => 'Bimbingan Konseling', 'icon' => 'shield-alert'],
            'alumni'          => ['label' => 'Tracer Alumni', 'icon' => 'graduation-cap'],
            'prestasi'        => ['label' => 'Prestasi Siswa', 'icon' => 'trophy'], 
            'cetak'           => ['label' => 'Cetak Laporan', 'icon' => 'printer'],
        ];
    }
}