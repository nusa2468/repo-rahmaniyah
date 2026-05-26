<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\TagihanModel;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\JenisPembayaranModel;
use App\Models\TahunAjaranModel;
use App\Models\Keuangan\PembayaranModel;
use App\Models\JenjangModel;
use App\Models\HakAksesModel;
use App\Models\SettingsModel;

// Import Model Akuntansi untuk Stealth Accounting (Auto-Jurnal Piutang)
use App\Models\Akuntansi\AkuntansiJurnalModel;
use App\Models\Akuntansi\AkuntansiJurnalDetailModel;
use App\Models\Akuntansi\AkuntansiCoaModel;

class TagihanController extends BaseController
{
    protected $tagihanModel;
    protected $siswaModel;
    protected $kelasModel;
    protected $jenisPembayaranModel;
    protected $tahunAjaranModel;
    protected $pembayaranModel;
    protected $jenjangModel;    
    protected $hakAksesModel;   
    protected $settingsModel;
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'number']);
        $this->tagihanModel         = new TagihanModel();
        $this->siswaModel           = new SiswaModel();
        $this->kelasModel           = new KelasModel();
        $this->jenisPembayaranModel = new JenisPembayaranModel();
        $this->tahunAjaranModel     = new TahunAjaranModel();
        $this->pembayaranModel      = new PembayaranModel();
        $this->jenjangModel         = new JenjangModel();    
        $this->hakAksesModel        = new HakAksesModel();   
        $this->settingsModel        = new SettingsModel();
        $this->db                   = \Config\Database::connect();
    }

    /**
     * Helper: Cek Status Superadmin (Dinamis via Database)
     */
    private function checkSuperAdmin()
    {
        $session  = session();
        $userRole = $session->get('role'); 
        $userUnit = strtoupper($session->get('kode_jenjang') ?? ''); 

        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            return true;
        } 
        $roleData = $this->hakAksesModel->where('name', $userRole)->first();
        if ($roleData) {
            $roleScope = strtoupper($roleData['kode_jenjang'] ?? '');
            if (in_array($roleScope, ['GLOBAL', 'YAYASAN', 'ROOT'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper: Mendapatkan Tanggal Tutup Buku
     */
    private function getLockDate($kodeJenjang)
    {
        $settings = $this->settingsModel->getSettingsAsArray($kodeJenjang);
        if (empty($settings['lock_date_keuangan'])) {
            $settings = $this->settingsModel->getSettingsAsArray('GLOBAL');
        }
        return $settings['lock_date_keuangan'] ?? '2000-01-01';
    }

    /**
     * Menampilkan daftar tagihan
     */
    public function index()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        $jenjangFilter = $this->request->getGet('jenjang');
        if (!$isSuperAdmin) {
            $jenjangFilter = $session->get('kode_jenjang'); 
        }

        $id_siswa        = $this->request->getGet('id_siswa');
        $selected_bulan  = $this->request->getGet('bulan_jatuh_tempo');
        $selected_status = $this->request->getGet('status');

        $this->tagihanModel->scopeJenjang($jenjangFilter)
                           ->getBaseQueryWithDetails($id_siswa, $selected_bulan);

        if ($selected_status && in_array($selected_status, ['lunas', 'belum_lunas'])) {
            $this->tagihanModel->where('tagihan.status', $selected_status);
        }

        $this->tagihanModel->orderBy('tagihan.tanggal_jatuh_tempo', 'DESC');

        $dataTagihan = $this->tagihanModel->paginate(20, 'default');
        $dataTagihan = $this->tagihanModel->processStatusReal($dataTagihan); 

        $summary = $this->tagihanModel->getGlobalSummary($jenjangFilter, $id_siswa, $selected_bulan);

        $jenjangList = $this->jenjangModel->getDropdownOptions();
        
        $siswaQuery = $this->siswaModel->select('id, nis, nama_lengkap')->where('status', 'aktif');
        if ($jenjangFilter) {
            $siswaQuery->where('kode_jenjang', $jenjangFilter);
        }
        $siswaList = $siswaQuery->orderBy('nama_lengkap', 'ASC')->findAll();

        $targetConfigUnit = $isSuperAdmin ? 'GLOBAL' : $session->get('kode_jenjang');
        $lockDate = $this->getLockDate($targetConfigUnit);

        $data = [
            'title'           => 'Manajemen Tagihan Siswa',
            'current_module'  => 'keuangan',
            'tagihan'         => $dataTagihan,
            'pager'           => $this->tagihanModel->pager,
            'active_unit'     => $jenjangFilter, 
            'filter_jenjang'  => $jenjangFilter, 
            'isSuperAdmin'    => $isSuperAdmin,
            'jenjang_list'    => $jenjangList,
            'navigation'      => $this->getNavigation(),
            'total_tagihan'   => $summary['total_tagihan'], 
            'total_dibayar'   => $summary['total_dibayar'],
            'total_terutang'  => $summary['total_terutang'],
            'siswa_list'      => $siswaList,
            'selected_siswa'  => $id_siswa,
            'selected_bulan'  => $selected_bulan,
            'selected_status' => $selected_status,
            'lock_date'       => $lockDate
        ];

        return view('keuangan/tagihan/index', $data);
    }

    public function form($id = null)
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        $kodeJenjang = $isSuperAdmin ? $this->request->getGet('jenjang') : $session->get('kode_jenjang');

        $tagihan = null;
        if ($id) {
            $query = $this->tagihanModel
                        ->select('tagihan.*, siswa.nama_lengkap, siswa.nis')
                        ->join('siswa', 'siswa.id = tagihan.id_siswa');

            if (!$isSuperAdmin) {
                $query->where('tagihan.kode_jenjang', $kodeJenjang);
            }

            $tagihan = $query->find($id);
            if (!$tagihan) return redirect()->to(base_url('app/keuangan/tagihan'))->with('error', 'Data tagihan tidak ditemukan.');
            
            $kodeJenjang = $tagihan['kode_jenjang'];
        }

        $jenisPembayaran = $this->jenisPembayaranModel;
        $siswaList       = $this->siswaModel->select('id, nis, nama_lengkap')->where('status', 'aktif');

        if (!empty($kodeJenjang)) {
            $jenisPembayaran->where('kode_jenjang', $kodeJenjang);
            $siswaList->where('kode_jenjang', $kodeJenjang);
        }

        $data = [
            'title'            => ($id) ? 'Ubah Data Tagihan' : 'Tambah Tagihan Baru',
            'current_module'   => 'keuangan',
            'jenis_pembayaran' => $jenisPembayaran->findAll(),
            'siswa_list'       => $siswaList->orderBy('nama_lengkap', 'ASC')->findAll(),
            'tagihan'          => $tagihan,
            'active_unit'      => $kodeJenjang,
            'isSuperAdmin'     => $isSuperAdmin,
            'jenjang_list'     => $this->jenjangModel->getDropdownOptions(), 
            'navigation'       => $this->getNavigation(),
        ];

        return view('keuangan/tagihan/form', $data);
    }

    /**
     * Proses Simpan (Insert/Update) dengan Accrual Stealth Accounting
     */
    public function save()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        $id = $this->request->getPost('id');

        $kodeJenjang = $this->request->getPost('kode_jenjang');
        if (!$isSuperAdmin) $kodeJenjang = $session->get('kode_jenjang');

        if (!$this->validate([
            'id_jenis_pembayaran' => 'required',
            'jumlah'              => 'required|numeric',
            'tanggal_jatuh_tempo' => 'required|valid_date'
        ])) {
             return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // =========================================================================
        // AUDIT TRAIL: VALIDASI TUTUP BUKU
        // =========================================================================
        $lockDate = $this->getLockDate($isSuperAdmin ? 'GLOBAL' : $kodeJenjang);
        
        $tglTagihanCheck = date('Y-m-d');
        if ($id) {
            $existing = $this->tagihanModel->find($id);
            if ($existing) $tglTagihanCheck = $existing['tanggal_tagihan'] ?? date('Y-m-d');
            
            if (!$isSuperAdmin && $existing['kode_jenjang'] !== $kodeJenjang) {
                return redirect()->back()->with('error', 'Pelanggaran Akses: Anda tidak berhak mengubah data unit lain.');
            }
        }
        
        if (strtotime($tglTagihanCheck) <= strtotime($lockDate)) {
             return redirect()->back()->withInput()->with('error', 'TIDAK DIIZINKAN: Tidak dapat memodifikasi tagihan di periode Tutup Buku (Locked).');
        }

        $formData = [
            'id'                  => $id ?: null,
            'id_jenis_pembayaran' => $this->request->getPost('id_jenis_pembayaran'),
            'deskripsi'           => $this->request->getPost('deskripsi'),
            'jumlah'              => $this->request->getPost('jumlah'),
            'tanggal_jatuh_tempo' => $this->request->getPost('tanggal_jatuh_tempo'),
        ];

        if (!$id) {
            $formData['id_siswa']        = $this->request->getPost('id_siswa');
            $formData['status']          = 'belum_lunas';
            $formData['tanggal_tagihan'] = date('Y-m-d');
            
            if (!empty($kodeJenjang)) {
                $formData['kode_jenjang'] = $kodeJenjang;
            } else {
                $siswa = $this->siswaModel->find($formData['id_siswa']);
                $formData['kode_jenjang'] = $siswa['kode_jenjang'] ?? null;
            }
        }

        $this->db->transBegin();
        try {
            if (!$this->tagihanModel->save($formData)) {
                throw new \Exception(implode(', ', $this->tagihanModel->errors()));
            }
            $tagihanId = $id ?: $this->tagihanModel->getInsertID();

            // =========================================================================
            // STEALTH ACCOUNTING: PENGAKUAN PIUTANG (ACCRUAL BASIS)
            // =========================================================================
            $jurnalModel = new AkuntansiJurnalModel();
            $jurnalDetailModel = new AkuntansiJurnalDetailModel();
            $coaModel = new AkuntansiCoaModel();

            // 1. Cari Akun Piutang SPP (Debit)
            $coaPiutang = $coaModel->where('kode_jenjang', 'GLOBAL')->where('kode_akun', '1102')->first();
            if (!$coaPiutang) $coaPiutang = $coaModel->where('kode_jenjang', 'GLOBAL')->like('nama_akun', 'Piutang')->where('is_parent', 0)->first();
            if (!$coaPiutang) throw new \Exception("Akun Piutang tidak ditemukan di Master COA Yayasan.");

            // 2. Cari Akun Pendapatan Jasa / SPP (Kredit)
            $coaPendapatan = $coaModel->where('kode_jenjang', 'GLOBAL')->like('nama_akun', 'Pendapatan Jasa')->where('is_parent', 0)->first();
            if (!$coaPendapatan) $coaPendapatan = $coaModel->where('kode_jenjang', 'GLOBAL')->like('kode_akun', '41', 'after')->where('is_parent', 0)->first();
            if (!$coaPendapatan) throw new \Exception("Akun Pendapatan tidak ditemukan di Master COA Yayasan.");

            $referensiJurnal = 'INV-' . $tagihanId;
            $jurnalLama = $jurnalModel->where('referensi', $referensiJurnal)->first();

            $headerData = [
                'kode_jenjang'     => $formData['kode_jenjang'],
                'tanggal'          => $formData['tanggal_tagihan'] ?? ($existing['tanggal_tagihan'] ?? date('Y-m-d')),
                'referensi'        => $referensiJurnal,
                'deskripsi'        => 'Pengakuan Piutang: ' . $formData['deskripsi'],
                'total_debit'      => $formData['jumlah'],
                'total_kredit'     => $formData['jumlah'],
                'sumber_transaksi' => 'Pengakuan Piutang',
                'status'           => 'Posted',
                'created_by'       => session()->get('id') ?? session()->get('user_id'),
            ];

            if ($jurnalLama) {
                $headerData['nomor_jurnal'] = $jurnalLama['nomor_jurnal'];
                if (!$jurnalModel->update($jurnalLama['id'], $headerData)) {
                    throw new \Exception("Gagal update jurnal: " . implode(', ', $jurnalModel->errors()));
                }
                $idJurnal = $jurnalLama['id'];
                $jurnalDetailModel->where('id_jurnal', $idJurnal)->delete();
            } else {
                $randomString = strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4));
                $headerData['nomor_jurnal'] = 'INV-' . date('Ym', strtotime($headerData['tanggal'])) . '-' . $randomString;
                
                if (!$jurnalModel->insert($headerData)) {
                    throw new \Exception("Gagal membuat jurnal: " . implode(', ', $jurnalModel->errors()));
                }
                $idJurnal = $jurnalModel->getInsertID();
            }

            // (Debit) Piutang Bertambah, (Kredit) Pendapatan Bertambah
            $barisJurnal = [
                ['id_jurnal' => $idJurnal, 'id_coa' => $coaPiutang['id'], 'debit' => $formData['jumlah'], 'kredit' => 0, 'keterangan' => $headerData['deskripsi']],
                ['id_jurnal' => $idJurnal, 'id_coa' => $coaPendapatan['id'], 'debit' => 0, 'kredit' => $formData['jumlah'], 'keterangan' => $headerData['deskripsi']],
            ];
            if (!$jurnalDetailModel->insertBatch($barisJurnal)) {
                throw new \Exception("Gagal menyimpan rincian jurnal.");
            }

            $this->db->transCommit();
            $message = $id ? 'Data tagihan diperbarui dan jurnal disesuaikan.' : 'Tagihan baru dibuat dan diakui sebagai piutang.';
            return redirect()->to(base_url('app/keuangan/tagihan'))->with('success', $message);

        } catch (\Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal memproses akuntansi: ' . $e->getMessage());
        }
    }

    public function detail($id = null)
    {
        if (!$id) return redirect()->to(base_url('app/keuangan/tagihan'));

        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        $kodeJenjang = $session->get('kode_jenjang');
        
        $query = $this->tagihanModel;
        if (!$isSuperAdmin) {
            $query->where('kode_jenjang', $kodeJenjang);
        }
        
        $cekTagihan = $query->find($id);

        if (!$cekTagihan) {
             return redirect()->to(base_url('app/keuangan/tagihan'))->with('error', 'Data tidak ditemukan atau akses ditolak.');
        }

        $tagihanDetail = $this->tagihanModel->getTagihanById($id);
        $pembayaran = $this->pembayaranModel->where('id_tagihan', $id)->orderBy('tanggal_bayar', 'DESC')->findAll();

        $data = [
            'title'          => 'Detail Tagihan Siswa',
            'current_module' => 'keuangan',
            'tagihan'        => $tagihanDetail,
            'pembayaran'     => $pembayaran,
            'active_unit'    => $cekTagihan['kode_jenjang'],
            'navigation'     => $this->getNavigation(),
            'isSuperAdmin'   => $isSuperAdmin
        ];

        return view('keuangan/tagihan/detail', $data);
    }

    public function show($id = null) { return $this->detail($id); }

    public function delete($id = null)
    {
        if (!$id) return redirect()->back();

        $isSuperAdmin = $this->checkSuperAdmin();
        $kodeJenjang  = session('kode_jenjang');
        
        $query = $this->tagihanModel;
        if (!$isSuperAdmin) $query->where('kode_jenjang', $kodeJenjang);
        $tagihan = $query->find($id);

        if (!$tagihan) return redirect()->back()->with('error', 'Akses ditolak atau data tidak ada.');

        $lockDate = $this->getLockDate($isSuperAdmin ? 'GLOBAL' : $tagihan['kode_jenjang']);
        if (strtotime($tagihan['tanggal_tagihan']) <= strtotime($lockDate)) {
             return redirect()->back()->with('error', 'AUDIT LOCK: Tidak dapat menghapus tagihan yang sudah dikunci oleh Tutup Buku.');
        }

        $cekPembayaran = $this->pembayaranModel->where('id_tagihan', $id)->countAllResults();
        if ($cekPembayaran > 0) {
            return redirect()->back()->with('error', 'Gagal: Tagihan sudah memiliki riwayat pembayaran. Hapus riwayat pembayaran terlebih dahulu.');
        }

        $this->db->transBegin();
        try {
            // Hapus Tagihan Fisik
            $this->tagihanModel->delete($id);

            // AUTO-REVERSAL JURNAL AKUNTANSI
            $jurnalModel = new AkuntansiJurnalModel();
            $jurnalModel->where('referensi', 'INV-' . $id)->delete();

            $this->db->transCommit();
            return redirect()->to(base_url('app/keuangan/tagihan'))->with('success', 'Tagihan dihapus dan jurnal akuntansi telah dibatalkan.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi jurnal akuntansi.');
        }
    }

    public function mass_form()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        $kodeJenjang = $isSuperAdmin ? $this->request->getGet('jenjang') : $session->get('kode_jenjang');
        
        $jenisQuery = $this->jenisPembayaranModel;
        $kelasQuery = $this->kelasModel;

        if (!empty($kodeJenjang)) {
            $jenisQuery->where('kode_jenjang', $kodeJenjang);
            $kelasQuery->where('kode_jenjang', $kodeJenjang);
        }

        $listBulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        $data = [
            'title'            => 'Generate Tagihan Masal',
            'current_module'   => 'keuangan',
            'jenis_pembayaran' => $jenisQuery->where('status', 'aktif')->findAll(),
            'kelas'            => $kelasQuery->where('is_aktif', 1)->findAll(),
            'active_unit'      => $kodeJenjang,
            'isSuperAdmin'     => $isSuperAdmin,
            'jenjang_list'     => $this->jenjangModel->getDropdownOptions(),
            'listBulan'        => $listBulan,
            'navigation'       => $this->getNavigation()
        ];
        return view('keuangan/tagihan/mass_create', $data);
    }

    /**
     * Generate Massal (Auto-Journal Batch) - PER BULAN
     */
    public function generate_proses()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        $inputJenjang = $this->request->getPost('kode_jenjang');
        $kodeJenjang  = $isSuperAdmin ? $inputJenjang : $session->get('kode_jenjang');

        $id_jenis_pembayaran = $this->request->getPost('id_jenis_pembayaran');
        $id_kelas            = $this->request->getPost('id_kelas');
        $bulan_input         = $this->request->getPost('bulan') ?: date('m');
        $tahun_input         = $this->request->getPost('tahun') ?: date('Y');
        
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        $jenisPembayaran  = $this->jenisPembayaranModel->find($id_jenis_pembayaran);

        if (!$jenisPembayaran || !$tahunAjaranAktif) {
            return redirect()->back()->with('error', 'Konfigurasi tidak valid.');
        }

        // =========================================================================
        // AUDIT TRAIL: VALIDASI TUTUP BUKU
        // =========================================================================
        $lockDate = $this->getLockDate($isSuperAdmin ? 'GLOBAL' : $kodeJenjang);
        if (strtotime(date('Y-m-d')) <= strtotime($lockDate)) {
             return redirect()->back()->with('error', 'TIDAK DIIZINKAN: Tidak dapat membuat tagihan baru karena tanggal hari ini berada dalam periode Tutup Buku (Locked).');
        }

        if (!empty($kodeJenjang) && $jenisPembayaran['kode_jenjang'] !== $kodeJenjang && $jenisPembayaran['kode_jenjang'] !== 'GLOBAL') {
             return redirect()->back()->with('error', 'Jenis pembayaran tidak sesuai dengan unit yang dipilih.');
        }

        $querySiswa = $this->siswaModel
            ->select('siswa.id, siswa.kode_jenjang, siswa.nama_lengkap')
            ->join('siswa_enrollment', 'siswa_enrollment.id_siswa = siswa.id')
            ->where([
                'siswa_enrollment.id_kelas'        => $id_kelas, 
                'siswa_enrollment.id_tahun_ajaran' => $tahunAjaranAktif['id'], 
                'siswa.status'                     => 'aktif'
            ]);
        
        if (!empty($kodeJenjang)) $querySiswa->where('siswa.kode_jenjang', $kodeJenjang);

        $siswaDiKelas = $querySiswa->findAll();

        if (empty($siswaDiKelas)) {
            return redirect()->back()->with('error', 'Tidak ada siswa aktif ditemukan di kelas terpilih.');
        }

        $batchData  = [];
        $nama_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        foreach ($siswaDiKelas as $siswa) {
            $jenjangTagihan = !empty($kodeJenjang) ? $kodeJenjang : $siswa['kode_jenjang'];

            // Pengecekan agar tidak generate ganda untuk bulan yang sama
            if ($jenisPembayaran['tipe'] == 'bulanan') {
                $deskripsi = $jenisPembayaran['nama_pembayaran'] . ' - ' . $nama_bulan[$bulan_input] . ' ' . $tahun_input;
                
                $cekDuplikat = $this->tagihanModel->where('id_siswa', $siswa['id'])
                                                  ->where('id_jenis_pembayaran', $id_jenis_pembayaran)
                                                  ->where('deskripsi', $deskripsi)
                                                  ->countAllResults();
                if ($cekDuplikat > 0) continue; // Lewati jika siswa ini sudah digenerate untuk bulan ini
                
                $batchData[] = [
                    'kode_jenjang'        => $jenjangTagihan,
                    'id_siswa'            => $siswa['id'],
                    'nama_siswa'          => $siswa['nama_lengkap'], // Temporary untuk jurnal
                    'id_jenis_pembayaran' => $id_jenis_pembayaran,
                    'deskripsi'           => $deskripsi,
                    'jumlah'              => $jenisPembayaran['nominal'],
                    'status'              => 'belum_lunas',
                    'tanggal_tagihan'     => date('Y-m-d'),
                    'tanggal_jatuh_tempo' => "{$tahun_input}-{$bulan_input}-10",
                    'created_at'          => date('Y-m-d H:i:s')
                ];
            } 
            else {
                // TAGIHAN INSIDENTAL (BEBAS)
                $batchData[] = [
                    'kode_jenjang'        => $jenjangTagihan,
                    'id_siswa'            => $siswa['id'],
                    'nama_siswa'          => $siswa['nama_lengkap'], 
                    'id_jenis_pembayaran' => $id_jenis_pembayaran,
                    'deskripsi'           => $jenisPembayaran['nama_pembayaran'],
                    'jumlah'              => $jenisPembayaran['nominal'],
                    'status'              => 'belum_lunas',
                    'tanggal_tagihan'     => date('Y-m-d'),
                    'tanggal_jatuh_tempo' => date('Y-m-d', strtotime('+30 days')),
                    'created_at'          => date('Y-m-d H:i:s')
                ];
            }
        }

        if (!empty($batchData)) {
            $this->db->transBegin();
            try {
                $jurnalModel = new AkuntansiJurnalModel();
                $jurnalDetailModel = new AkuntansiJurnalDetailModel();
                $coaModel = new AkuntansiCoaModel();

                $coaPiutang = $coaModel->where('kode_jenjang', 'GLOBAL')->where('kode_akun', '1102')->first();
                if (!$coaPiutang) $coaPiutang = $coaModel->where('kode_jenjang', 'GLOBAL')->like('nama_akun', 'Piutang')->where('is_parent', 0)->first();
                if (!$coaPiutang) throw new \Exception("Akun Piutang tidak ditemukan.");

                $coaPendapatan = $coaModel->where('kode_jenjang', 'GLOBAL')->like('nama_akun', 'Pendapatan Jasa')->where('is_parent', 0)->first();
                if (!$coaPendapatan) $coaPendapatan = $coaModel->where('kode_jenjang', 'GLOBAL')->like('kode_akun', '41', 'after')->where('is_parent', 0)->first();
                if (!$coaPendapatan) throw new \Exception("Akun Pendapatan tidak ditemukan.");

                $idAdmin = $session->get('id') ?? $session->get('user_id');

                foreach ($batchData as $data) {
                    $namaSiswa = $data['nama_siswa'];
                    unset($data['nama_siswa']); // Bersihkan kolom temporary
                    
                    if (!$this->tagihanModel->insert($data)) {
                        throw new \Exception("Gagal menyimpan tagihan.");
                    }
                    $tagihanId = $this->tagihanModel->getInsertID();

                    $randomString = strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4));
                    
                    $headerData = [
                        'kode_jenjang'     => $data['kode_jenjang'],
                        'nomor_jurnal'     => 'INV-' . date('Ym', strtotime($data['tanggal_tagihan'])) . '-' . $randomString,
                        'tanggal'          => $data['tanggal_tagihan'],
                        'referensi'        => 'INV-' . $tagihanId,
                        'deskripsi'        => 'Piutang: ' . $data['deskripsi'] . ' a.n ' . $namaSiswa,
                        'total_debit'      => $data['jumlah'],
                        'total_kredit'     => $data['jumlah'],
                        'sumber_transaksi' => 'Pengakuan Piutang',
                        'status'           => 'Posted',
                        'created_by'       => $idAdmin,
                    ];
                    
                    if (!$jurnalModel->insert($headerData)) {
                        throw new \Exception("Gagal membuat jurnal header: " . implode(', ', $jurnalModel->errors()));
                    }
                    $idJurnal = $jurnalModel->getInsertID();

                    $barisJurnal = [
                        ['id_jurnal' => $idJurnal, 'id_coa' => $coaPiutang['id'], 'debit' => $data['jumlah'], 'kredit' => 0, 'keterangan' => $headerData['deskripsi']],
                        ['id_jurnal' => $idJurnal, 'id_coa' => $coaPendapatan['id'], 'debit' => 0, 'kredit' => $data['jumlah'], 'keterangan' => $headerData['deskripsi']],
                    ];
                    
                    if (!$jurnalDetailModel->insertBatch($barisJurnal)) {
                        throw new \Exception("Gagal memasukkan detail jurnal.");
                    }
                }

                $this->db->transCommit();
                return redirect()->to(base_url('app/keuangan/tagihan'))->with('success', count($batchData) . ' tagihan berhasil digenerate dan otomatis diakui sebagai piutang di Buku Besar.');
            } catch (\Exception $e) {
                $this->db->transRollback();
                return redirect()->back()->with('error', 'Gagal memproses akuntansi: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Gagal memproses pembuatan tagihan. Mungkin semua siswa di kelas ini sudah memiliki tagihan untuk bulan tersebut.');
    }
    
    public function bayar($id = null)
    {
        if (!$id) return redirect()->to(base_url('app/keuangan/tagihan'));
        return redirect()->to(base_url('app/keuangan/pembayaran/create/' . $id));
    }

    private function getNavigation()
    {
        $nav = [
            'dashboard'   => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
            'budget'      => ['label' => 'Anggaran (Budget)', 'icon' => 'pie-chart', 'url' => 'app/keuangan/budget'],
            'tagihan'     => ['label' => 'Tagihan & Piutang', 'icon' => 'file-text', 'url' => 'app/keuangan/tagihan'],
            'pemasukan'   => ['label' => 'Pemasukan', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/laporan/pemasukan'],
            'pengeluaran' => ['label' => 'Pengeluaran', 'icon' => 'arrow-up-circle', 'url' => 'app/keuangan/laporan/pengeluaran'],
        ];
        if (isset($nav['Akuntansi'])) unset($nav['Akuntansi']);
        if (isset($nav['akuntansi'])) unset($nav['akuntansi']);
        return $nav;
    }
}