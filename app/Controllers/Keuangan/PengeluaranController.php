<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\PengeluaranModel;
use App\Models\JenjangModel;
use App\Models\HakAksesModel;
use App\Models\SettingsModel; // Tambahkan SettingsModel untuk fitur Tutup Buku
use App\Models\Akuntansi\AkuntansiJurnalModel;
use App\Models\Akuntansi\AkuntansiJurnalDetailModel;
use App\Models\Akuntansi\AkuntansiCoaModel;

/**
 * PengeluaranController (Enterprise Audit Edition)
 * Menangani CRUD, Stealth Accounting, dan Validasi Lock Date (Tutup Buku).
 */
class PengeluaranController extends BaseController
{
    protected $pengeluaranModel;
    protected $jenjangModel;
    protected $hakAksesModel;
    protected $settingsModel;
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'number']);
        $this->pengeluaranModel = new PengeluaranModel();
        $this->jenjangModel     = new JenjangModel();
        $this->hakAksesModel    = new HakAksesModel();
        $this->settingsModel    = new SettingsModel();
        $this->db               = \Config\Database::connect();
    }

    private function checkSuperAdmin()
    {
        $session  = session();
        $userRole = $session->get('role');
        $userUnit = strtoupper($session->get('kode_jenjang') ?? '');

        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) return true;
        
        $roleData = $this->hakAksesModel->where('name', $userRole)->first();
        if ($roleData && in_array(strtoupper($roleData['kode_jenjang'] ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT'])) return true;

        return false;
    }

    /**
     * Mengambil Tanggal Tutup Buku Terakhir
     */
    private function getLockDate($kodeJenjang)
    {
        // Cari di pengaturan unit, jika tidak ada cari di pengaturan Global
        $settings = $this->settingsModel->getSettingsAsArray($kodeJenjang);
        if (empty($settings['lock_date_keuangan'])) {
            $settings = $this->settingsModel->getSettingsAsArray('GLOBAL');
        }
        
        // Jika belum disetting sama sekali, default ke tahun 2000 (tidak ada yang dilock)
        return $settings['lock_date_keuangan'] ?? '2000-01-01';
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

        $page      = $this->request->getVar('page_default') ? (int)$this->request->getVar('page_default') : 1;
        $perPage   = 20;
        $nomorUrut = ($page - 1) * $perPage;

        $this->pengeluaranModel->resetQuery();
        
        $this->pengeluaranModel->select('pengeluaran.*, akuntansi_coa.nama_akun as nama_kategori')
                               ->join('akuntansi_coa', 'akuntansi_coa.id = pengeluaran.id_kategori', 'left')
                               ->where('pengeluaran.deleted_at', null);

        if (!empty($jenjangFilter)) {
            $this->pengeluaranModel->where('pengeluaran.kode_jenjang', $jenjangFilter);
        }
        if ($startDate && $endDate) {
            $this->pengeluaranModel->where('pengeluaran.tanggal >=', $startDate)
                                   ->where('pengeluaran.tanggal <=', $endDate);
        }

        $this->pengeluaranModel->orderBy('pengeluaran.tanggal', 'DESC')
                               ->orderBy('pengeluaran.created_at', 'DESC');

        $dataPengeluaran = $this->pengeluaranModel->paginate($perPage, 'default');

        $kpiQuery = $this->db->table('pengeluaran')->selectSum('jumlah')->where('deleted_at', null)
                             ->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);
        if (!empty($jenjangFilter)) $kpiQuery->where('kode_jenjang', $jenjangFilter);
        $totalPengeluaran = $kpiQuery->get()->getRow()->jumlah ?? 0;

        $jenjangList = $this->jenjangModel->getDropdownOptions();
        
        $kategoriList = $this->db->table('akuntansi_coa c')
                                 ->select('c.id, c.nama_akun as nama_kategori')
                                 ->join('akuntansi_kategori k', 'k.id = c.id_kategori')
                                 ->where('k.kode_kategori', '5') 
                                 ->where('c.is_parent', 0)
                                 ->where('c.kode_jenjang', 'GLOBAL') 
                                 ->orderBy('c.kode_akun', 'ASC')
                                 ->get()->getResultArray();

        // Ambil data Lock Date untuk dilempar ke UI
        $targetConfigUnit = $isSuperAdmin ? 'GLOBAL' : $session->get('kode_jenjang');
        $lockDate = $this->getLockDate($targetConfigUnit);

        $data = [
            'title'             => 'Transaksi Pengeluaran Operasional',
            'current_module'    => 'keuangan',
            'pengeluaran'       => $dataPengeluaran,
            'total_pengeluaran' => $totalPengeluaran,
            'pager'             => $this->pengeluaranModel->pager,
            'nomor_urut'        => $nomorUrut,
            'jenjang_list'      => $jenjangList,
            'filter_jenjang'    => $jenjangFilter,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'isSuperAdmin'      => $isSuperAdmin,
            'kategori_list'     => $kategoriList,
            'navigation'        => $this->getNavigation(),
            'kpi'               => ['item_terbesar' => '-', 'nominal_terbesar' => 0],
            'lock_date'         => $lockDate // INJEKSI LOCK DATE KE VIEW
        ];

        if (!empty($dataPengeluaran)) {
            $maxItem = collect($dataPengeluaran)->sortByDesc('jumlah')->first();
            $data['kpi'] = ['item_terbesar' => $maxItem['keterangan'], 'nominal_terbesar' => $maxItem['jumlah']];
        }

        return view('keuangan/pengeluaran/index', $data);
    }

    public function store()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        $id = $this->request->getPost('id');

        $jumlahInput = $this->request->getPost('jumlah');
        $jumlahBersih = (float) preg_replace('/[^0-9]/', '', $jumlahInput ?: '0');

        if ($jumlahBersih <= 0) {
            return redirect()->back()->withInput()->with('error', 'Nominal pengeluaran tidak valid.');
        }

        $kodeJenjang = $this->request->getPost('kode_jenjang');
        if (!$isSuperAdmin) {
            $kodeJenjang = $session->get('kode_jenjang'); 
        }

        $tanggalTransaksi = $this->request->getPost('tanggal');
        $lockDate = $this->getLockDate($isSuperAdmin ? 'GLOBAL' : $kodeJenjang);

        // =========================================================================
        // AUDIT TRAIL: VALIDASI TUTUP BUKU (LOCK DATE)
        // =========================================================================
        // 1. Cek apakah tanggal input baru berada di periode yang sudah ditutup
        if (strtotime($tanggalTransaksi) <= strtotime($lockDate)) {
            return redirect()->back()->withInput()->with('error', 'TIDAK DIIZINKAN: Tanggal transaksi berada dalam periode Tutup Buku (Locked). Hubungi Akuntan Yayasan.');
        }

        $namaFile = null;
        $fileBukti = $this->request->getFile('bukti');
        
        if ($id) {
            $existing = $this->pengeluaranModel->find($id);
            if ($existing) {
                if (!$isSuperAdmin && $existing['kode_jenjang'] !== $kodeJenjang) {
                    return redirect()->back()->with('error', 'Akses ditolak: Data unit lain.');
                }
                
                // 2. Cek apakah data lama yang mau diedit sudah berstatus Tutup Buku
                if (strtotime($existing['tanggal']) <= strtotime($lockDate)) {
                    return redirect()->back()->with('error', 'AUDIT LOCK: Data lama ini sudah dibukukan dan dikunci. Anda tidak dapat mengeditnya. Silakan minta Akuntan untuk membuat Jurnal Pembalik.');
                }

                $namaFile = $existing['bukti'];
            }
        }

        if ($fileBukti && $fileBukti->isValid() && !$fileBukti->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/pengeluaran';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);
            
            $newName = $fileBukti->getRandomName();
            $fileBukti->move($uploadPath, $newName);
            
            if ($namaFile && file_exists($uploadPath . '/' . $namaFile)) {
                unlink($uploadPath . '/' . $namaFile);
            }
            $namaFile = $newName;
        }

        $dataPengeluaran = [
            'id'           => $id ?: null,
            'kode_jenjang' => $kodeJenjang,
            'id_kategori'  => $this->request->getPost('id_kategori'), 
            'tanggal'      => $tanggalTransaksi,
            'jumlah'       => $jumlahBersih,
            'keterangan'   => $this->request->getPost('keterangan'),
            'bukti'        => $namaFile,
            'id_user'      => $session->get('id') ?? $session->get('user_id'),
        ];

        // Transaksi DB Integrasi Akuntansi
        $this->db->transBegin();
        try {
            if (!$this->pengeluaranModel->save($dataPengeluaran)) {
                throw new \Exception(implode(', ', $this->pengeluaranModel->errors()));
            }
            $pengeluaranId = $id ?: $this->pengeluaranModel->getInsertID();

            $jurnalModel = new AkuntansiJurnalModel();
            $jurnalDetailModel = new AkuntansiJurnalDetailModel();
            $coaModel = new AkuntansiCoaModel();

            $coaKas = $coaModel->where('kode_jenjang', 'GLOBAL')->where('kode_akun', '1101')->first();
            if (!$coaKas) {
                $coaKas = $coaModel->where('kode_jenjang', 'GLOBAL')->like('nama_akun', 'Kas')->where('is_parent', 0)->first();
            }
            if (!$coaKas) throw new \Exception("Akun Kas Utama tidak ditemukan di Master COA Yayasan.");

            $referensiJurnal = 'EXP-' . $pengeluaranId;
            $jurnalLama = $jurnalModel->where('referensi', $referensiJurnal)->first();

            $headerData = [
                'kode_jenjang'     => $kodeJenjang, 
                'tanggal'          => $dataPengeluaran['tanggal'],
                'referensi'        => $referensiJurnal,
                'deskripsi'        => 'Pengeluaran Ops: ' . $dataPengeluaran['keterangan'],
                'total_debit'      => $jumlahBersih,
                'total_kredit'     => $jumlahBersih,
                'sumber_transaksi' => 'Kas Keluar',
                'status'           => 'Posted',
                'created_by'       => $dataPengeluaran['id_user'],
            ];

            if ($jurnalLama) {
                $headerData['nomor_jurnal'] = $jurnalLama['nomor_jurnal'];
                $jurnalModel->update($jurnalLama['id'], $headerData);
                $idJurnal = $jurnalLama['id'];
                $jurnalDetailModel->where('id_jurnal', $idJurnal)->delete(); 
            } else {
                $randomString = strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4));
                $headerData['nomor_jurnal'] = 'OUT-' . date('Ym', strtotime($dataPengeluaran['tanggal'])) . '-' . $randomString;
                $jurnalModel->insert($headerData);
                $idJurnal = $jurnalModel->getInsertID();
            }

            $barisJurnal = [
                ['id_jurnal' => $idJurnal, 'id_coa' => $dataPengeluaran['id_kategori'], 'debit' => $jumlahBersih, 'kredit' => 0, 'keterangan' => $dataPengeluaran['keterangan']],
                ['id_jurnal' => $idJurnal, 'id_coa' => $coaKas['id'], 'debit' => 0, 'kredit' => $jumlahBersih, 'keterangan' => 'Pencairan Kas Ops'],
            ];
            $jurnalDetailModel->insertBatch($barisJurnal);

            $this->db->transCommit();
            $msg = $id ? 'Pengeluaran berhasil diperbarui dan dijurnal.' : 'Pengeluaran baru berhasil dicatat di Kas & Buku Besar.';
            return redirect()->to(base_url('app/keuangan/pengeluaran'))->with('success', $msg);

        } catch (\Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function delete($id = null)
    {
        if (!$id) return redirect()->back();

        $isSuperAdmin = $this->checkSuperAdmin();
        $kodeJenjang  = session('kode_jenjang');

        $data = $this->pengeluaranModel->find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        if (!$isSuperAdmin && $data['kode_jenjang'] !== $kodeJenjang) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // =========================================================================
        // AUDIT TRAIL: VALIDASI TUTUP BUKU (HAPUS)
        // =========================================================================
        $lockDate = $this->getLockDate($isSuperAdmin ? 'GLOBAL' : $kodeJenjang);
        if (strtotime($data['tanggal']) <= strtotime($lockDate)) {
            return redirect()->back()->with('error', 'AUDIT LOCK: Tidak dapat menghapus transaksi di bulan yang sudah ditutup bukunya. Hubungi Akuntan Yayasan.');
        }

        $this->db->transBegin();
        try {
            if ($data['bukti'] && file_exists(FCPATH . 'uploads/pengeluaran/' . $data['bukti'])) {
                unlink(FCPATH . 'uploads/pengeluaran/' . $data['bukti']);
            }

            // Hapus Data Pengeluaran Ops (Soft Delete Aktif secara Default di Model)
            $this->pengeluaranModel->delete($id);

            $jurnalModel = new AkuntansiJurnalModel();
            $jurnalModel->where('referensi', 'EXP-' . $id)->delete();

            $this->db->transCommit();
            return redirect()->to(base_url('app/keuangan/pengeluaran'))->with('success', 'Data pengeluaran dan jurnal terkait berhasil dihapus (Arsip).');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi pada sistem Akuntansi.');
        }
    }

    private function getNavigation()
    {
        $nav = [
            'dashboard'   => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
            'budget'      => ['label' => 'Anggaran (Budget)', 'icon' => 'pie-chart', 'url' => 'app/keuangan/budget'],
            'tagihan'     => ['label' => 'Tagihan & Piutang', 'icon' => 'file-text', 'url' => 'app/keuangan/tagihan'],
            'pembayaran'  => ['label' => 'Pemasukan', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/pembayaran'],
            'pengeluaran' => ['label' => 'Pengeluaran', 'icon' => 'arrow-up-circle', 'url' => 'app/keuangan/pengeluaran'],
            'laporan'     => ['label' => 'Laporan Ops', 'icon' => 'print', 'url' => 'app/keuangan/laporan/pengeluaran'],
        ];

        if (isset($nav['akuntansi'])) {
            unset($nav['akuntansi']);
        }
        
        return $nav;
    }
}