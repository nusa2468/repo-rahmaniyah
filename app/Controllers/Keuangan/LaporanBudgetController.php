<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\BudgetModel;
use App\Models\JenjangModel;
use App\Models\HakAksesModel; // Import HakAksesModel

/**
 * LaporanBudgetController
 * Mengelola anggaran unit (penghasilan & beban) sesuai standar ISAK 35.
 * Mendukung pembatasan akses berdasarkan Scope Unit dan Tahun Ajaran Aktif.
 */
class LaporanBudgetController extends BaseController
{
    protected $budgetModel;
    protected $jenjangModel;
    protected $hakAksesModel; // Property HakAkses
    protected $db;

    public function __construct()
    {
        $this->budgetModel   = new BudgetModel();
        $this->jenjangModel  = new JenjangModel();
        $this->hakAksesModel = new HakAksesModel(); // Instansiasi
        $this->db            = \Config\Database::connect();
    }

    /**
     * Tampilan Utama Manajemen Anggaran
     */
    public function index()
    {
        $session  = session();
        $userRole = $session->get('role'); 
        $userUnit = strtoupper($session->get('kode_jenjang') ?? ''); 
        
        // 1. Tentukan Tahun Ajaran Aktif
        $tahunAktif = $session->get('tahun_aktif');
        if (!$tahunAktif) {
            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');
            if ($currentMonth > 6) {
                $tahunAktif = $currentYear . '/' . ($currentYear + 1);
            } else {
                $tahunAktif = ($currentYear - 1) . '/' . $currentYear;
            }
        }

        // 2. Logika Scope Access Control (100% DINAMIS - SAMA DENGAN DASHBOARD)
        $isSuperAdmin = false;

        // Cek 1: Apakah Unit user di session adalah GLOBAL? (Level Akun)
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            $isSuperAdmin = true;
        } 
        // Cek 2: Cek Konfigurasi Role di Database (Level Hak Akses)
        else {
            $roleData = $this->hakAksesModel->where('name', $userRole)->first();
            if ($roleData) {
                $roleScope = strtoupper($roleData['kode_jenjang'] ?? '');
                if (in_array($roleScope, ['GLOBAL', 'YAYASAN', 'ROOT'])) {
                    $isSuperAdmin = true;
                }
            }
        }

        $jenjangFilter = $this->request->getGet('jenjang');
        
        if (!$isSuperAdmin) {
            // Jika bukan superadmin, paksa filter ke unit user sendiri (Anti Bocor)
            $jenjangFilter = $session->get('kode_jenjang');
        }

        // 3. Konfigurasi Pagination
        $currentPage = $this->request->getVar('page_default') ? (int)$this->request->getVar('page_default') : 1;
        $perPage     = 10;
        
        // Hitung nomor urut awal untuk kolom No.
        // Rumus: (Halaman Saat Ini - 1) * Jumlah Per Halaman
        $nomorUrut   = ($currentPage - 1) * $perPage;

        // 4. Ambil data list anggaran dengan PAGINATION
        $budgets = $this->budgetModel->getFilteredBudgets($jenjangFilter, $tahunAktif)->paginate($perPage, 'default');

        // 5. Hitung Ringkasan Anggaran
        $summary = $this->getBudgetSummary($jenjangFilter, $tahunAktif);

        // 6. Siapkan Data untuk Grafik (DINAMIS)
        $chartUnit = $this->getUnitBudgetChartData($isSuperAdmin ? null : $userUnit, $tahunAktif);

        // 7. Ambil Daftar Jenjang untuk Dropdown (DINAMIS)
        $jenjangList = $this->jenjangModel->getDropdownOptions();
        
        // Optional: Rename Global jika ada di list
        foreach ($jenjangList as &$j) {
            if (strtoupper($j['kode_jenjang']) === 'GLOBAL') {
                $j['nama_jenjang'] = 'Agregat (Yayasan)';
            }
        }

        $data = [
            'title'          => 'Manajemen Anggaran (ISAK 35)',
            'budgets'        => $budgets,
            'pager'          => $this->budgetModel->pager,
            'nomor_urut'     => $nomorUrut, // Data untuk kolom No.
            'summary'        => $summary,
            'chart_unit'     => $chartUnit,
            'jenjang'        => $jenjangList,
            'categories'     => $this->db->table('kategori_anggaran')->orderBy('kode_kategori', 'ASC')->get()->getResultArray(),
            'filter_jenjang' => $jenjangFilter,
            'tahun_aktif'    => $tahunAktif,
            'is_superadmin'  => $isSuperAdmin,
            'navigation'     => $this->getNavigation() // Fitur Navigasi
        ];

        return view('keuangan/budget/index', $data);
    }

    /**
     * Helper untuk menghitung Ringkasan Anggaran (KPI Dashboard)
     */
    private function getBudgetSummary($jenjang = null, $tahun = null)
    {
        $summary = ['penghasilan' => 0, 'beban' => 0, 'surplus' => 0];

        // Hitung Penerimaan
        $p = $this->db->table('anggaran_unit')
            ->selectSum('nominal', 'total')
            ->join('kategori_anggaran', 'kategori_anggaran.id = anggaran_unit.id_kategori')
            ->whereIn('kategori_anggaran.kelompok', ['penghasilan', 'pendapatan']);
        
        if (!empty($jenjang)) $p->where('anggaran_unit.kode_jenjang', $jenjang);
        if (!empty($tahun))   $p->where('anggaran_unit.tahun', $tahun);
        
        $resP = $p->get()->getRow();
        $summary['penghasilan'] = (float)($resP->total ?? 0);

        // Hitung Pengeluaran
        $b = $this->db->table('anggaran_unit')
            ->selectSum('nominal', 'total')
            ->join('kategori_anggaran', 'kategori_anggaran.id = anggaran_unit.id_kategori')
            ->where('kategori_anggaran.kelompok', 'beban');
        
        if (!empty($jenjang)) $b->where('anggaran_unit.kode_jenjang', $jenjang);
        if (!empty($tahun))   $b->where('anggaran_unit.tahun', $tahun);
        
        $resB = $b->get()->getRow();
        $summary['beban'] = (float)($resB->total ?? 0);

        $summary['surplus'] = $summary['penghasilan'] - $summary['beban'];

        return $summary;
    }

    /**
     * Mengambil data distribusi anggaran untuk Grafik secara DINAMIS
     */
    private function getUnitBudgetChartData($lockedUnit = null, $tahun = null)
    {
        $units = [];

        if ($lockedUnit) {
            // Jika dikunci (Admin Unit), hanya unit tersebut
            $units[] = $lockedUnit;
        } else {
            // Jika Superadmin, ambil semua unit aktif dari Database
            $jenjangData = $this->jenjangModel->getDropdownOptions();
            foreach ($jenjangData as $j) {
                $units[] = $j['kode_jenjang'];
            }
            // Tambahkan Global secara eksplisit jika perlu
            $units[] = 'Global';
        }

        $income = [];
        $expense = [];
        $labels = [];

        foreach ($units as $kode) {
            // Penerimaan
            $in = $this->db->table('anggaran_unit')
                ->selectSum('nominal', 'total')
                ->join('kategori_anggaran', 'kategori_anggaran.id = anggaran_unit.id_kategori')
                ->whereIn('kategori_anggaran.kelompok', ['penghasilan', 'pendapatan'])
                ->where('anggaran_unit.kode_jenjang', $kode);
            if ($tahun) $in->where('anggaran_unit.tahun', $tahun);
            $resIn = $in->get()->getRow();
            $income[] = (float)($resIn->total ?? 0);

            // Pengeluaran
            $ex = $this->db->table('anggaran_unit')
                ->selectSum('nominal', 'total')
                ->join('kategori_anggaran', 'kategori_anggaran.id = anggaran_unit.id_kategori')
                ->where('kategori_anggaran.kelompok', 'beban')
                ->where('anggaran_unit.kode_jenjang', $kode);
            if ($tahun) $ex->where('anggaran_unit.tahun', $tahun);
            $resEx = $ex->get()->getRow();
            $expense[] = (float)($resEx->total ?? 0);

            // Label
            $labels[] = (strtoupper($kode) === 'GLOBAL') ? 'Agregat' : $kode;
        }

        return [
            'labels'  => $labels,
            'income'  => $income,
            'expense' => $expense
        ];
    }

    /**
     * Menyimpan atau memperbarui data anggaran
     */
    public function save()
    {
        $session = session();
        $id = $this->request->getPost('id');
        $nominalRaw = $this->request->getPost('nominal');
        $nominal = str_replace(['.', ','], ['', '.'], $nominalRaw);

        // Otoritas Input: Cek hak akses lagi (Secure Check)
        $userRole = $session->get('role');
        $userUnit = strtoupper($session->get('kode_jenjang') ?? '');
        
        $isSuperAdmin = false;
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            $isSuperAdmin = true;
        } else {
            $roleData = $this->hakAksesModel->where('name', $userRole)->first();
            if ($roleData && in_array(strtoupper($roleData['kode_jenjang'] ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT'])) {
                $isSuperAdmin = true;
            }
        }

        $kodeJenjang = $this->request->getPost('kode_jenjang');
        
        if (!$isSuperAdmin) {
            // Paksa unit sesuai session user jika bukan superadmin (Dropdown Terkunci secara backend)
            $kodeJenjang = $session->get('kode_jenjang');
        }

        $data = [
            'kode_jenjang' => $kodeJenjang ?: null,
            'id_kategori'  => $this->request->getPost('id_kategori'),
            'tahun'        => $this->request->getPost('tahun'),
            'nominal'      => (float)($nominal ?: 0),
            'keterangan'   => $this->request->getPost('keterangan'),
        ];

        if ($id) {
            $this->budgetModel->update($id, $data);
            $msg = 'Data anggaran berhasil diperbarui.';
        } else {
            $this->budgetModel->insert($data);
            $msg = 'Anggaran baru berhasil ditambahkan.';
        }

        return redirect()->to(base_url('app/keuangan/budget'))->with('success', $msg);
    }

    /**
     * Menghapus data anggaran
     */
    public function delete($id)
    {
        $data = $this->budgetModel->find($id);
        if (!$data) {
            return redirect()->to(base_url('app/keuangan/budget'))->with('error', 'Data tidak ditemukan.');
        }

        // Optional: Tambahkan cek hak akses delete di sini

        $this->budgetModel->delete($id);
        return redirect()->to(base_url('app/keuangan/budget'))->with('success', 'Anggaran berhasil dihapus.');
    }

    /**
     * Konfigurasi Navigasi Modul Keuangan.
     * Digunakan oleh View untuk membuat tab/menu.
     */
    private function getNavigation()
    {
        return [
            'dashboard'   => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
            'budget'      => ['label' => 'Anggaran (Budget)', 'icon' => 'pie-chart', 'url' => 'app/keuangan/budget'],
            'tagihan'     => ['label' => 'Tagihan & Piutang', 'icon' => 'file-text', 'url' => 'app/keuangan/tagihan'],
            'pemasukan'  => ['label' => 'Pemasukan', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/laporan/pemasukan'],
            'pengeluaran' => ['label' => 'Pengeluaran', 'icon' => 'arrow-up-circle', 'url' => 'app/keuangan/laporan/pengeluaran'],
            'Akuntansi'     => ['label' => 'Akuntansi', 'icon' => 'printer', 'url' => 'app/keuangan/akuntansi'],
        ];
    }
}