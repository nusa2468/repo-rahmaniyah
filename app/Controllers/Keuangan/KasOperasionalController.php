<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Akuntansi\AkuntansiJurnalModel;
use App\Models\Akuntansi\AkuntansiJurnalDetailModel;
use App\Models\Akuntansi\AkuntansiCoaModel;
use App\Models\JenjangModel;

/**
 * KasOperasionalController (Stealth Accounting)
 * Modul Kasir/Buku Kas untuk Admin Unit. Di balik layar, ini adalah Jurnal Akuntansi.
 */
class KasOperasionalController extends BaseController
{
    protected $jurnalModel;
    protected $jurnalDetailModel;
    protected $coaModel;
    protected $db;

    public function __construct()
    {
        $this->jurnalModel       = new AkuntansiJurnalModel();
        $this->jurnalDetailModel = new AkuntansiJurnalDetailModel();
        $this->coaModel          = new AkuntansiCoaModel();
        $this->db                = \Config\Database::connect();
    }

    private function checkSuperAdmin()
    {
        $session  = session();
        $userRole = strtolower($session->get('role') ?? $session->get('role_name') ?? '');
        $userUnit = strtoupper($session->get('kode_jenjang') ?? '');

        return in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']) || in_array($userRole, ['superadmin', 'yayasan']);
    }

    public function index()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        $jenjangFilter = $this->request->getGet('jenjang');
        if (!$isSuperAdmin) {
            $jenjangFilter = $session->get('kode_jenjang'); 
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?? date('Y-m-t');

        // 1. AMBIL DAFTAR COA PENDAPATAN & BEBAN UNTUK DROPDOWN KATEGORI
        $coaQuery = $this->db->table('akuntansi_coa c')
            ->select('c.id, c.kode_akun, c.nama_akun, k.kode_kategori')
            ->join('akuntansi_kategori k', 'k.id = c.id_kategori')
            ->where('c.is_parent', 0)
            ->where('c.is_active', 1)
            ->where('c.deleted_at', null);

        if (!$isSuperAdmin) {
            $coaQuery->where('c.kode_jenjang', $session->get('kode_jenjang'));
        } else if ($jenjangFilter) {
            $coaQuery->where('c.kode_jenjang', $jenjangFilter);
        }
        $coaList = $coaQuery->orderBy('c.nama_akun', 'ASC')->get()->getResultArray();

        $kategoriMasuk  = array_filter($coaList, fn($c) => $c['kode_kategori'] == '4'); // 4 = Pendapatan
        $kategoriKeluar = array_filter($coaList, fn($c) => $c['kode_kategori'] == '5'); // 5 = Beban

        // 2. QUERY TRANSAKSI BUKU KAS (DARI TABEL JURNAL)
        $builder = $this->db->table('akuntansi_jurnal j')
            ->select('j.id, j.tanggal, j.nomor_jurnal, j.deskripsi, j.referensi, j.sumber_transaksi, j.total_debit as nominal, j.kode_jenjang, c.nama_akun as nama_kategori')
            ->join('akuntansi_jurnal_detail d', 'd.id_jurnal = j.id', 'inner')
            ->join('akuntansi_coa c', 'c.id = d.id_coa', 'inner')
            ->whereIn('j.sumber_transaksi', ['Kas Masuk', 'Kas Keluar'])
            ->where('c.kode_akun !=', '1101'); // Ambil akun lawannya (Kategori Beban/Pendapatannya, bukan akun Kasnya)

        if (!empty($jenjangFilter)) $builder->where('j.kode_jenjang', $jenjangFilter);
        if ($startDate && $endDate) {
            $builder->where('j.tanggal >=', $startDate)->where('j.tanggal <=', $endDate);
        }

        // Paginasi Manual
        $page    = $this->request->getVar('page_default') ? (int)$this->request->getVar('page_default') : 1;
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $countBuilder = clone $builder;
        $totalRows = $countBuilder->countAllResults(false);

        $transaksi = $builder->orderBy('j.tanggal', 'DESC')->orderBy('j.id', 'DESC')
                             ->limit($perPage, $offset)->get()->getResultArray();

        $pager = \Config\Services::pager();
        $pager->store('default', $page, $perPage, $totalRows, 0);

        // 3. DAFTAR UNIT UNTUK DROPDOWN SUPERADMIN
        $jenjangList = [];
        if ($this->db->tableExists('jenjang_sekolah')) {
            $jenjangList = $this->db->table('jenjang_sekolah')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get()->getResultArray();
        }

        $data = [
            'title'          => 'Buku Kas Operasional',
            'current_module' => 'keuangan',
            'transaksi'      => $transaksi,
            'pager'          => $pager,
            'nomor_urut'     => $offset,
            'jenjang_list'   => $jenjangList,
            'filter_jenjang' => $jenjangFilter,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'isSuperAdmin'   => $isSuperAdmin,
            'kategori_masuk' => $kategoriMasuk,
            'kategori_keluar'=> $kategoriKeluar,
            'navigation'     => $this->getNavigation()
        ];

        return view('keuangan/kas_operasional/index', $data);
    }

    public function store()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();

        $jenis       = $this->request->getPost('jenis_transaksi'); // 'masuk' atau 'keluar'
        $tanggal     = $this->request->getPost('tanggal');
        $keterangan  = $this->request->getPost('keterangan');
        $referensi   = $this->request->getPost('referensi');
        $idKategori  = $this->request->getPost('id_kategori'); // Ini adalah ID COA (Beban/Pendapatan)
        $nominal     = preg_replace('/[^0-9]/', '', $this->request->getPost('nominal') ?: '0');
        
        $kodeJenjang = $this->request->getPost('kode_jenjang');
        if (!$isSuperAdmin) {
            $kodeJenjang = $session->get('kode_jenjang');
        }

        // --- VALIDASI AKUN KAS (Mencari Akun 1101 di Unit Tersebut) ---
        $coaKas = $this->coaModel->where('kode_jenjang', $kodeJenjang)
                                 ->where('kode_akun', '1101')
                                 ->where('deleted_at', null)
                                 ->first();
                                 
        if (!$coaKas) {
            // Fallback: Cari akun yang namanya mengandung kata 'Kas'
            $coaKas = $this->coaModel->where('kode_jenjang', $kodeJenjang)
                                     ->like('nama_akun', 'Kas')
                                     ->where('is_parent', 0)
                                     ->where('deleted_at', null)
                                     ->first();
            if (!$coaKas) {
                return redirect()->back()->withInput()->with('error', "Gagal: Akun Induk 'Kas' tidak ditemukan untuk Unit {$kodeJenjang}. Harap hubungi Admin Yayasan untuk merapikan Bagan Akun.");
            }
        }

        // --- PROSES TRANSLASI KE JURNAL AKUNTANSI ---
        $this->db->transBegin();
        try {
            // 1. Buat Header Jurnal
            $prefix = $jenis === 'masuk' ? 'IN-' : 'OUT-';
            $nomorJurnal = 'KAS-' . $prefix . date('Ym', strtotime($tanggal)) . '-' . strtoupper(substr(uniqid(), -4));

            $headerData = [
                'kode_jenjang'     => $kodeJenjang,
                'nomor_jurnal'     => $nomorJurnal,
                'tanggal'          => $tanggal,
                'referensi'        => $referensi,
                'deskripsi'        => $keterangan,
                'total_debit'      => $nominal,
                'total_kredit'     => $nominal,
                'sumber_transaksi' => $jenis === 'masuk' ? 'Kas Masuk' : 'Kas Keluar',
                'status'           => 'Posted',
                'created_by'       => $session->get('id') ?? $session->get('user_id'),
            ];

            $this->jurnalModel->insert($headerData);
            $idJurnal = $this->jurnalModel->getInsertID();

            // 2. Buat Detail Jurnal (Double-Entry Magic)
            if ($jenis === 'masuk') {
                // KAS BERTAMBAH (Debit), PENDAPATAN BERTAMBAH (Kredit)
                $this->jurnalDetailModel->insertBatch([
                    ['id_jurnal' => $idJurnal, 'id_coa' => $coaKas['id'], 'debit' => $nominal, 'kredit' => 0, 'keterangan' => 'Penerimaan Kas'],
                    ['id_jurnal' => $idJurnal, 'id_coa' => $idKategori, 'debit' => 0, 'kredit' => $nominal, 'keterangan' => $keterangan],
                ]);
            } else {
                // BEBAN BERTAMBAH (Debit), KAS BERKURANG (Kredit)
                $this->jurnalDetailModel->insertBatch([
                    ['id_jurnal' => $idJurnal, 'id_coa' => $idKategori, 'debit' => $nominal, 'kredit' => 0, 'keterangan' => $keterangan],
                    ['id_jurnal' => $idJurnal, 'id_coa' => $coaKas['id'], 'debit' => 0, 'kredit' => $nominal, 'keterangan' => 'Pengeluaran Kas'],
                ]);
            }

            $this->db->transCommit();
            return redirect()->to(base_url('app/keuangan/kas-operasional'))->with('success', 'Transaksi berhasil dicatat ke dalam Buku Kas.');

        } catch (\Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function delete($id = null)
    {
        if (!$id) return redirect()->back();

        $isSuperAdmin = $this->checkSuperAdmin();
        $jurnal = $this->jurnalModel->find($id);
        
        if (!$jurnal) return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');

        // Proteksi Unit
        if (!$isSuperAdmin && $jurnal['kode_jenjang'] !== session('kode_jenjang')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Hapus Jurnal (Detail akan otomatis terhapus jika DB menggunakan CASCADE)
        $this->jurnalModel->delete($id);
        // Karena useSoftDeletes = false di model, data akan terhapus fisik dari Jurnal
        
        return redirect()->to(base_url('app/keuangan/kas-operasional'))->with('success', 'Transaksi kas berhasil dibatalkan dan dihapus dari buku besar.');
    }

    private function getNavigation()
    {
        return [
            'dashboard'       => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
            'budget'          => ['label' => 'Anggaran (Budget)', 'icon' => 'pie-chart', 'url' => 'app/keuangan/budget'],
            'tagihan'         => ['label' => 'Tagihan & Piutang', 'icon' => 'file-text', 'url' => 'app/keuangan/tagihan'],
            'pembayaran'      => ['label' => 'Pemasukan SPP', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/pembayaran'],
            'kas-operasional' => ['label' => 'Buku Kas (In/Out)', 'icon' => 'exchange-alt', 'url' => 'app/keuangan/kas-operasional'],
            'laporan'         => ['label' => 'Laporan', 'icon' => 'printer', 'url' => 'app/keuangan/laporan/pengeluaran'],
        ];
    }
}