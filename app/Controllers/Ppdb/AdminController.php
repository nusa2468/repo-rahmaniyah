<?php

namespace App\Controllers\Ppdb;

use App\Controllers\BaseController;
use App\Models\Ppdb\PendaftarModel;
use App\Models\Ppdb\AffiliateModel;

/**
 * AdminController - Modul PPDB (Enterprise Edition)
 * Mengelola alur kerja pendaftaran, verifikasi, dan data siswa.
 * UPDATED: Menambahkan fitur Export Excel & PDF (Print View).
 */
class AdminController extends BaseController
{
    // Konstanta Status Seleksi (Single Source of Truth)
    public const STATUS_PENDING = 'Pending';
    public const STATUS_LOLOS   = 'Lolos';
    public const STATUS_GAGAL   = 'Gagal';

    // Daftar Role dengan Hak Akses Global (Bisa melihat semua unit)
    protected array $privilegedRoles = ['superadmin', 'yayasan'];

    protected $pendaftarModel;
    protected $affiliateModel;

    public function __construct()
    {
        $this->pendaftarModel = new PendaftarModel();
        $this->affiliateModel = new AffiliateModel();
    }

    /**
     * Dashboard Monitoring (index)
     */
    public function index()
    {
        $filterJenjang = $this->request->getGet('jenjang');

        $stats   = $this->pendaftarModel->getStatsDashboard($filterJenjang);
        $terbaru = $this->pendaftarModel->getTerbaru(5, $filterJenjang);

        $afiliasiStats = $this->affiliateModel->getPerformanceStats($filterJenjang);
        $stats['total_afiliasi'] = count($afiliasiStats);
        $stats['total_fee']      = array_sum(array_column($afiliasiStats, 'total_potensi_fee'));

        $data = [
            'title'         => 'Dashboard Monitoring PPDB',
            'stats'         => $stats,
            'terbaru'       => $terbaru,
            'currentModule' => 'ppdb',
            'active_menu'   => 'ppdb_dashboard'
        ];

        return view('ppdb/index', $data);
    }

    /**
     * Master List Pendaftar (Pagination & Search)
     */
    public function list()
    {
        $session = session();
        $role    = $session->get('role_name');
        $userJenjang = $session->get('kode_jenjang'); 
        $urlJenjang  = $this->request->getGet('jenjang');

        // Logic Filter
        if (!in_array($role, $this->privilegedRoles) && $userJenjang && $userJenjang != 'GLOBAL') {
            $this->pendaftarModel->where('kode_jenjang', $userJenjang);
        } elseif (in_array($role, $this->privilegedRoles) && $urlJenjang && $urlJenjang !== 'Semua') {
            $this->pendaftarModel->where('kode_jenjang', $urlJenjang);
        }

        $keyword = $this->request->getGet('q');
        if ($keyword) {
            $this->pendaftarModel->groupStart()
                ->like('nama_lengkap', $keyword)
                ->orLike('no_pendaftaran', $keyword)
                ->orLike('nik', $keyword)
                ->orLike('asal_sekolah', $keyword)
                ->groupEnd();
        }

        $data = [
            'title'         => 'Database Master Pendaftar',
            'pendaftar'     => $this->pendaftarModel->orderBy('created_at', 'DESC')->paginate(20),
            'pager'         => $this->pendaftarModel->pager,
            'currentModule' => 'ppdb',
            'active_menu'   => 'ppdb_list',
            'keyword'       => $keyword
        ];

        return view('ppdb/pendaftar_list', $data); 
    }

    // =========================================================================
    // FITUR EXPORT DATA (BARU)
    // =========================================================================

    /**
     * Export Excel (CSV Download)
     * Mengunduh data sesuai filter unit yang aktif.
     */
    public function exportExcel()
    {
        $session = session();
        $role    = $session->get('role_name');
        $userJenjang = $session->get('kode_jenjang'); 
        $urlJenjang  = $this->request->getGet('jenjang');

        // Terapkan Filter Scope (Sama seperti List)
        if (!in_array($role, $this->privilegedRoles) && $userJenjang && $userJenjang != 'GLOBAL') {
            $this->pendaftarModel->where('kode_jenjang', $userJenjang);
        } elseif (in_array($role, $this->privilegedRoles) && $urlJenjang && $urlJenjang !== 'Semua') {
            $this->pendaftarModel->where('kode_jenjang', $urlJenjang);
        }

        // Ambil semua data tanpa pagination
        $data = $this->pendaftarModel->orderBy('created_at', 'DESC')->findAll();

        // Setup Header File
        $filename = 'Data_PPDB_' . ($urlJenjang ?: 'Semua') . '_' . date('Ymd_His') . '.csv';
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv;");

        // Tulis File CSV
        $file = fopen('php://output', 'w');
        
        // Header Kolom
        fputcsv($file, ['No', 'No Pendaftaran', 'Unit', 'Tahun Ajaran', 'Nama Siswa', 'NIK', 'NISN', 'JK', 'Asal Sekolah', 'Jalur', 'Skor', 'Status Seleksi', 'Status Bayar', 'Kode Agen']);

        $i = 1;
        foreach ($data as $row) {
            fputcsv($file, [
                $i++,
                $row->no_pendaftaran,
                $row->kode_jenjang,
                $row->tahun_ajaran,
                $row->nama_lengkap,
                "'" . $row->nik, // Tanda kutip agar excel membaca sebagai teks (bukan angka ilmiah)
                "'" . $row->nisn,
                $row->jenis_kelamin,
                $row->asal_sekolah,
                $row->jalur_masuk,
                $row->skor_akhir,
                $row->status_seleksi,
                $row->status_pembayaran,
                $row->kode_afiliasi
            ]);
        }

        fclose($file);
        exit;
    }

    /**
     * Export PDF (Print View)
     * Menampilkan halaman cetak laporan yang bersih.
     */
    public function exportPdf()
    {
        $session = session();
        $role    = $session->get('role_name');
        $userJenjang = $session->get('kode_jenjang'); 
        $urlJenjang  = $this->request->getGet('jenjang');

        // Terapkan Filter Scope
        if (!in_array($role, $this->privilegedRoles) && $userJenjang && $userJenjang != 'GLOBAL') {
            $this->pendaftarModel->where('kode_jenjang', $userJenjang);
        } elseif (in_array($role, $this->privilegedRoles) && $urlJenjang && $urlJenjang !== 'Semua') {
            $this->pendaftarModel->where('kode_jenjang', $urlJenjang);
        }

        $data = [
            'title'     => 'Laporan Data PPDB',
            'unit'      => $urlJenjang ?: ($userJenjang ?: 'Semua Unit'),
            'pendaftar' => $this->pendaftarModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('ppdb/print_laporan', $data);
    }

    /**
     * Cetak Formulir Per Siswa
     */
    public function print($id)
    {
        $pendaftar = $this->pendaftarModel->find($id);
        
        if (!$pendaftar || !$this->checkAccess($pendaftar)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Gunakan view yang sama tapi modenya 'single'
        $data = [
            'title'     => 'Formulir Pendaftaran',
            'unit'      => $pendaftar->kode_jenjang,
            'single'    => $pendaftar
        ];

        return view('ppdb/print_laporan', $data);
    }

    // =========================================================================
    // END OF EXPORT
    // =========================================================================

    public function detail($id = null)
    {
        $pendaftar = $this->pendaftarModel->find($id);

        if (!$pendaftar || !$this->checkAccess($pendaftar)) {
            return redirect()->to(base_url('app/ppdb/list'))->with('error', 'Data tidak ditemukan atau Anda tidak memiliki akses.');
        }

        $data = [
            'title'         => 'Detail Profil: ' . $pendaftar->nama_lengkap,
            'pendaftar'     => $pendaftar,
            'currentModule' => 'ppdb',
            'active_menu'   => 'ppdb_list'
        ];

        return view('ppdb/detail', $data);
    }

    public function add()
    {
        $data = [
            'title'         => 'Registrasi Pendaftar Baru',
            'pendaftar'     => null,
            'affiliates'    => $this->affiliateModel->getActiveAffiliates(),
            'currentModule' => 'ppdb',
            'active_menu'   => 'ppdb_list'
        ];

        return view('ppdb/form', $data);
    }

    public function edit($id = null)
    {
        $pendaftar = $this->pendaftarModel->find($id);

        if (!$pendaftar || !$this->checkAccess($pendaftar)) {
            return redirect()->to(base_url('app/ppdb/list'))->with('error', 'Akses ditolak.');
        }

        $data = [
            'title'         => 'Perbarui Data: ' . $pendaftar->nama_lengkap,
            'pendaftar'     => $pendaftar,
            'affiliates'    => $this->affiliateModel->getActiveAffiliates(),
            'currentModule' => 'ppdb',
            'active_menu'   => 'ppdb_list'
        ];

        return view('ppdb/form', $data);
    }

    public function save($id = null)
    {
        $session = session();
        $data    = $this->request->getPost();

        if ($id) {
            $existing = $this->pendaftarModel->find($id);
            if (!$existing || !$this->checkAccess($existing)) {
                return redirect()->back()->with('error', 'Akses ilegal.');
            }
        } else {
            $jenjang = $session->get('kode_jenjang');
            if (in_array($session->get('role_name'), $this->privilegedRoles)) {
                $jenjang = $data['kode_jenjang'] ?? 'GLOBAL'; 
            }
            $data['kode_jenjang']   = $jenjang;
            $data['no_pendaftaran'] = $this->pendaftarModel->generateNoPendaftaran($jenjang);
            $currYear = date('Y');
            $data['tahun_ajaran']   = (date('n') > 6) ? "$currYear/".($currYear+1) : ($currYear-1)."/$currYear";
        }

        $file = $this->request->getFile('bukti_setor');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if (!is_dir(FCPATH . 'uploads/ppdb/bukti_bayar')) mkdir(FCPATH . 'uploads/ppdb/bukti_bayar', 0777, true);
            $file->move(FCPATH . 'uploads/ppdb/bukti_bayar', $newName);
            $data['bukti_setor'] = $newName;

            if ($id) {
                $oldData = $this->pendaftarModel->find($id);
                if ($oldData->bukti_setor && file_exists(FCPATH . 'uploads/ppdb/bukti_bayar/' . $oldData->bukti_setor)) {
                    unlink(FCPATH . 'uploads/ppdb/bukti_bayar/' . $oldData->bukti_setor);
                }
            }
        }

        if ($id) {
            $this->pendaftarModel->update($id, $data);
            $msg = "Data siswa berhasil diperbarui.";
        } else {
            $this->pendaftarModel->insert($data);
            $msg = "Siswa baru berhasil didaftarkan.";
        }

        return redirect()->to(base_url('app/ppdb/list'))->with('success', $msg);
    }

    public function verifikasi($id, $status)
    {
        $pendaftar = $this->pendaftarModel->find($id);
        if (!$pendaftar || !$this->checkAccess($pendaftar)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        if (in_array($status, [self::STATUS_PENDING, self::STATUS_LOLOS, self::STATUS_GAGAL])) {
            $this->pendaftarModel->update($id, ['status_seleksi' => $status]);
            return redirect()->back()->with('success', "Status berhasil diubah menjadi $status.");
        }
        return redirect()->back()->with('error', 'Status tidak valid.');
    }

    public function delete($id)
    {
        $pendaftar = $this->pendaftarModel->find($id);
        if (!$pendaftar || !$this->checkAccess($pendaftar)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        if ($this->pendaftarModel->delete($id)) {
            return redirect()->to(base_url('app/ppdb/list'))->with('success', 'Data berhasil diarsipkan.');
        }
        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }

    protected function checkAccess($data)
    {
        $session = session();
        $role    = $session->get('role_name');
        $jenjang = $session->get('kode_jenjang');

        if (in_array($role, $this->privilegedRoles)) return true;
        if (isset($data->kode_jenjang) && $data->kode_jenjang !== $jenjang) return false;
        return true;
    }
}