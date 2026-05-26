<?php

namespace App\Controllers\Akuntansi;

use App\Controllers\BaseController;
use App\Models\Akuntansi\AkuntansiJurnalModel;
use App\Models\Akuntansi\AkuntansiJurnalDetailModel;
use App\Models\Akuntansi\AkuntansiCoaModel;
use Throwable;

class JurnalController extends BaseController
{
    protected $jurnalModel;
    protected $jurnalDetailModel;
    protected $coaModel;
    protected $db;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ROOT'];

    public function __construct()
    {
        $this->jurnalModel       = new AkuntansiJurnalModel();
        $this->jurnalDetailModel = new AkuntansiJurnalDetailModel();
        $this->coaModel          = new AkuntansiCoaModel();
        $this->db                = \Config\Database::connect();
    }

    /**
     * KUNCI PENGAMAN (GATEKEEPER)
     * Memastikan hanya level Holding / Yayasan yang bisa masuk.
     * Admin Sekolah (SD/SMP/SMA) yang mencoba akses URL ini secara paksa akan ditendang.
     */
    private function checkYayasanPrivilege()
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole    = strtolower($session->get('role') ?? $session->get('role_name') ?? '');
        
        $isGlobal = in_array($userJenjang, $this->globalIdentifiers) || in_array($userRole, ['superadmin', 'yayasan']);
        
        return $isGlobal;
    }

    public function index()
    {
        if (!$this->checkYayasanPrivilege()) {
            return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak Lapis Server! Modul Akuntansi, COA, dan Jurnal adalah wewenang eksklusif Holding / Kantor Yayasan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        // FIX: Default ke MULTI (Konsolidasi) agar Yayasan langsung melihat seluruh transaksi
        $filterJenjang = $this->request->getGet('jenjang') ?? 'MULTI';

        // Ambil Data Jurnal
        $jurnalData = $this->jurnalModel->getJurnalBuilder($filterJenjang)
                                        ->orderBy('tanggal', 'DESC')
                                        ->orderBy('id', 'DESC')
                                        ->get()->getResultArray();

        // Ambil Daftar Unit untuk Dropdown Konsolidasi Yayasan
        $daftarUnit = [];
        if ($this->db->tableExists('jenjang_sekolah')) {
            $query = $this->db->table('jenjang_sekolah')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get();
            foreach ($query->getResultArray() as $row) {
                if (!in_array(strtoupper($row['kode_jenjang']), $this->globalIdentifiers)) {
                    $daftarUnit[strtoupper($row['kode_jenjang'])] = $row['nama_jenjang'];
                }
            }
        }

        $data = [
            'title'         => 'Buku Jurnal Umum',
            'current_module'=> 'akuntansi',
            'jurnal'        => $jurnalData,
            'isGlobal'      => true, // Pasti true karena sudah lolos pengecekan di atas
            'filterJenjang' => $filterJenjang,
            'daftarUnit'    => $daftarUnit
        ];

        return view('akuntansi/jurnal/index', $data);
    }

    public function new()
    {
        if (!$this->checkYayasanPrivilege()) {
            return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak! Anda bukan pengguna level Yayasan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $filterJenjang  = $this->request->getGet('jenjang') ?? $sessionJenjang;

        // Ambil COA (Hanya yang bukan Header / is_parent = 0)
        $coaList = $this->coaModel->getCoaBuilder($filterJenjang)
                                  ->where('akuntansi_coa.is_parent', 0)
                                  ->where('akuntansi_coa.is_active', 1)
                                  ->get()->getResultArray();

        $daftarUnit = [];
        if ($this->db->tableExists('jenjang_sekolah')) {
            $query = $this->db->table('jenjang_sekolah')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get();
            foreach ($query->getResultArray() as $row) {
                if (!in_array(strtoupper($row['kode_jenjang']), $this->globalIdentifiers)) {
                    $daftarUnit[strtoupper($row['kode_jenjang'])] = $row['nama_jenjang'];
                }
            }
        }

        $data = [
            'title'         => 'Entri Jurnal Baru',
            'current_module'=> 'akuntansi',
            'coaList'       => $coaList,
            'isGlobal'      => true,
            'filterJenjang' => $filterJenjang,
            'daftarUnit'    => $daftarUnit
        ];

        return view('akuntansi/jurnal/form', $data);
    }

    public function save()
    {
        if (!$this->checkYayasanPrivilege()) {
            return redirect()->to(base_url('app/keuangan/dashboard'))->with('error', 'Akses Ditolak! Pelanggaran Hak Akses Terdeteksi.');
        }

        $session = session();
        
        $kodeJenjang = $this->request->getPost('kode_jenjang');
        $tanggal     = $this->request->getPost('tanggal');
        $deskripsi   = $this->request->getPost('deskripsi');
        $referensi   = $this->request->getPost('referensi');
        
        // Data Arrays dari Baris Jurnal
        $id_coa     = $this->request->getPost('id_coa') ?? [];
        $debit      = $this->request->getPost('debit') ?? [];
        $kredit     = $this->request->getPost('kredit') ?? [];
        $keterangan = $this->request->getPost('keterangan_baris') ?? [];

        // 1. Validasi Balance (Debit = Kredit)
        $totalDebit  = 0;
        $totalKredit = 0;
        $barisJurnal = [];

        for ($i = 0; $i < count($id_coa); $i++) {
            if (empty($id_coa[$i])) continue; 
            
            $valDebit  = (float) preg_replace('/[^0-9]/', '', $debit[$i] ?: '0');
            $valKredit = (float) preg_replace('/[^0-9]/', '', $kredit[$i] ?: '0');

            if ($valDebit == 0 && $valKredit == 0) continue; 

            $totalDebit += $valDebit;
            $totalKredit += $valKredit;

            $barisJurnal[] = [
                'id_coa'     => $id_coa[$i],
                'debit'      => $valDebit,
                'kredit'     => $valKredit,
                'keterangan' => $keterangan[$i] ?? null
            ];
        }

        if (count($barisJurnal) < 2) {
            return redirect()->back()->withInput()->with('error', 'Jurnal minimal harus memiliki 2 baris (Debit & Kredit).');
        }

        if ($totalDebit !== $totalKredit) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan. Total Debit dan Kredit tidak seimbang (Unbalanced).');
        }

        // 2. Buat Nomor Jurnal Unik (JU-YYYYMM-XXXX)
        $nomorJurnal = 'JU-' . date('Ym', strtotime($tanggal)) . '-' . strtoupper(substr(uniqid(), -4));

        // 3. Database Transaction (Simpan Header & Detail secara Atomik)
        $this->db->transBegin();
        try {
            $headerData = [
                'kode_jenjang'     => $kodeJenjang,
                'nomor_jurnal'     => $nomorJurnal,
                'tanggal'          => $tanggal,
                'referensi'        => $referensi,
                'deskripsi'        => $deskripsi,
                'total_debit'      => $totalDebit,
                'total_kredit'     => $totalKredit,
                'sumber_transaksi' => 'Manual',
                'status'           => 'Posted',
                'created_by'       => $session->get('id') ?? $session->get('user_id'),
            ];

            $this->jurnalModel->insert($headerData);
            $idJurnal = $this->jurnalModel->getInsertID();

            foreach ($barisJurnal as &$baris) {
                $baris['id_jurnal'] = $idJurnal;
            }
            $this->jurnalDetailModel->insertBatch($barisJurnal);

            $this->db->transCommit();
            return redirect()->to(base_url('app/akuntansi/jurnal'))->with('success', 'Jurnal Umum berhasil dicatat dan diposting.');

        } catch (Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function detail($idJurnal)
    {
        if (!$this->checkYayasanPrivilege()) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses Terlarang.']);
        }

        $jurnal = $this->jurnalModel->find($idJurnal);
        if (!$jurnal) return $this->response->setJSON(['status' => false, 'message' => 'Not Found']);

        $detail = $this->jurnalDetailModel->getDetailByJurnal($idJurnal);
        return $this->response->setJSON([
            'status' => true,
            'jurnal' => $jurnal,
            'detail' => $detail
        ]);
    }
}