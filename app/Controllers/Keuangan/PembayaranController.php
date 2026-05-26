<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\TagihanModel;
use App\Models\Keuangan\PembayaranModel;
use App\Models\SiswaModel;
use App\Models\SettingsModel;     // Untuk Kop Surat Cetak
use App\Models\JenjangModel;      // Untuk Dropdown Unit
use App\Models\HakAksesModel;     // Untuk Security Anti-Bocor

// Import Model Akuntansi untuk Stealth Accounting (Auto-Jurnal)
use App\Models\Akuntansi\AkuntansiJurnalModel;
use App\Models\Akuntansi\AkuntansiJurnalDetailModel;
use App\Models\Akuntansi\AkuntansiCoaModel;

/**
 * Controller untuk mengelola transaksi pembayaran siswa.
 * Terintegrasi penuh dengan Buku Besar Akuntansi (Stealth Accounting) & Fitur Tutup Buku.
 */
class PembayaranController extends BaseController
{
    protected $tagihanModel;
    protected $pembayaranModel;
    protected $siswaModel;
    protected $settingsModel;
    protected $jenjangModel;
    protected $hakAksesModel;
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'number', 'text']);
        $this->tagihanModel     = new TagihanModel();
        $this->pembayaranModel  = new PembayaranModel();
        $this->siswaModel       = new SiswaModel();
        $this->settingsModel    = new SettingsModel();
        $this->jenjangModel     = new JenjangModel();
        $this->hakAksesModel    = new HakAksesModel();
        $this->db               = \Config\Database::connect();
    }

    /**
     * Helper: Cek Status Superadmin (100% Dinamis via Database)
     */
    private function checkSuperAdmin()
    {
        $session  = session();
        $userRole = $session->get('role'); 
        $userUnit = strtoupper($session->get('kode_jenjang') ?? ''); 

        // Cek 1: Unit Global
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            return true;
        } 
        
        // Cek 2: Konfigurasi Role di Database
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
     * Helper: Mendapatkan Tanggal Kunci (Tutup Buku) untuk Audit Trail
     */
    private function getLockDate($kodeJenjang)
    {
        $settings = $this->settingsModel->getSettingsAsArray($kodeJenjang);
        if (empty($settings['lock_date_keuangan'])) {
            $settings = $this->settingsModel->getSettingsAsArray('GLOBAL');
        }
        return $settings['lock_date_keuangan'] ?? '2000-01-01'; // Default: Tidak ada yang dilock
    }

    /**
     * Menampilkan Riwayat Pembayaran (Index)
     */
    public function index()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        // 1. Logika Filter Jenjang (Anti Bocor)
        $jenjangFilter = $this->request->getGet('jenjang');
        if (!$isSuperAdmin) {
            $jenjangFilter = $session->get('kode_jenjang'); // Paksa unit sendiri
        }

        // 2. Ambil Filter Lain
        $tanggal_mulai   = $this->request->getGet('tanggal_mulai');
        $tanggal_selesai = $this->request->getGet('tanggal_selesai');

        // 3. Siapkan Query Manual
        $builder = $this->pembayaranModel;
        
        $builder->select('pembayaran.*, siswa.nama_lengkap, siswa.nis, jenis_pembayaran.nama_pembayaran, tagihan.deskripsi as deskripsi_tagihan, pegawai.nama_lengkap as nama_admin')
            ->join('tagihan', 'tagihan.id = pembayaran.id_tagihan')
            ->join('siswa', 'siswa.id = tagihan.id_siswa')
            ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran')
            ->join('pegawai', 'pegawai.id = pembayaran.id_user_admin', 'left') // Log Admin
            ->where('pembayaran.deleted_at', null);

        // Terapkan Filter Unit
        if (!empty($jenjangFilter)) {
            $builder->where('pembayaran.kode_jenjang', $jenjangFilter);
        }

        // Terapkan Filter Tanggal
        if ($tanggal_mulai && $tanggal_selesai) {
            $builder->where('pembayaran.tanggal_bayar >=', $tanggal_mulai)
                    ->where('pembayaran.tanggal_bayar <=', $tanggal_selesai);
        }

        $builder->orderBy('pembayaran.created_at', 'DESC');

        // 4. Eksekusi Pagination (20 data per halaman)
        $page = $this->request->getVar('page_default') ? (int)$this->request->getVar('page_default') : 1;
        $perPage = 20;
        
        $dataPembayaran = $builder->paginate($perPage, 'default');
        $pager = $this->pembayaranModel->pager; 
        
        // Hitung nomor urut
        $nomorUrut = ($page - 1) * $perPage;

        // 5. Data Pendukung (Dropdown Unit & Status Lock)
        $jenjangList = $this->jenjangModel->getDropdownOptions();
        $targetConfigUnit = $isSuperAdmin ? 'GLOBAL' : $session->get('kode_jenjang');
        $lockDate = $this->getLockDate($targetConfigUnit);

        $data = [
            'title'           => 'Riwayat Transaksi Pembayaran',
            'current_module'  => 'keuangan',
            'pembayaran'      => $dataPembayaran,
            'pager'           => $pager,
            'nomor_urut'      => $nomorUrut,
            
            // Filter Data
            'tanggal_mulai'   => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'filter_jenjang'  => $jenjangFilter,
            'isSuperAdmin'    => $isSuperAdmin,
            'jenjang_list'    => $jenjangList,
            'lock_date'       => $lockDate,
            'navigation'      => $this->getNavigation()
        ];

        return view('keuangan/pembayaran/index', $data);
    }

    /**
     * Menampilkan form input pembayaran.
     */
    public function create($id_tagihan = null)
    {
        if (!$id_tagihan) return redirect()->back();

        $isSuperAdmin = $this->checkSuperAdmin();
        $kodeJenjang  = session('kode_jenjang');

        // 1. Query Tagihan dengan Validasi Unit
        $query = $this->tagihanModel
            ->select('tagihan.*, siswa.nama_lengkap, siswa.nis, siswa.kode_jenjang, jenis_pembayaran.nama_pembayaran')
            ->join('siswa', 'siswa.id = tagihan.id_siswa')
            ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran');

        if (!$isSuperAdmin) {
            $query->where('tagihan.kode_jenjang', $kodeJenjang);
        }

        $tagihan = $query->find($id_tagihan);

        if (!$tagihan) {
            return redirect()->to(base_url('app/keuangan/tagihan'))
                             ->with('error', 'Data tagihan tidak ditemukan atau di luar akses unit Anda.');
        }

        // 2. Hitung Sisa Tagihan Real-time
        $pembayaranExisting = $this->pembayaranModel
                                   ->where('id_tagihan', $id_tagihan)
                                   ->selectSum('jumlah_bayar')
                                   ->first();
                                   
        $totalTerbayar = (float)($pembayaranExisting['jumlah_bayar'] ?? 0);
        $sisaTagihan   = (float)$tagihan['jumlah'] - $totalTerbayar;

        // 3. Ambil Riwayat untuk ditampilkan di sidebar form
        $riwayat = $this->pembayaranModel->where('id_tagihan', $id_tagihan)
                                         ->orderBy('tanggal_bayar', 'DESC')
                                         ->findAll();

        $data = [
            'title'              => 'Input Pembayaran',
            'current_module'     => 'keuangan',
            'tagihan'            => $tagihan,
            'riwayat_pembayaran' => $riwayat,
            'sisa_tagihan'       => $sisaTagihan,
            'active_unit'        => $tagihan['kode_jenjang'],
            'navigation'         => $this->getNavigation(),
            'validation'         => \Config\Services::validation()
        ];

        return view('keuangan/pembayaran/create', $data);
    }

    /**
     * Proses simpan pembayaran dengan Stealth Accounting
     */
    public function store()
    {
        $isSuperAdmin = $this->checkSuperAdmin();
        $kodeJenjangSession = session('kode_jenjang');
        $session = session();

        $rules = [
            'id_tagihan'        => 'required', 
            'jumlah_bayar'      => 'required|numeric|greater_than[0]',
            'tanggal_bayar'     => 'required|valid_date',
            'metode_pembayaran' => 'required|in_list[Tunai,Transfer,Lainnya]',
            'bukti_bayar'       => 'max_size[bukti_bayar,2048]|is_image[bukti_bayar]|mime_in[bukti_bayar,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id_tagihan  = $this->request->getPost('id_tagihan');
        $jumlahInput = (float) $this->request->getPost('jumlah_bayar');
        $tanggalBayar= $this->request->getPost('tanggal_bayar') ?? date('Y-m-d');
        $metode      = $this->request->getPost('metode_pembayaran');
        
        $idAdmin = $session->get('id') ?? $session->get('user_id');
        
        $db = \Config\Database::connect();
        $db->transBegin();

        $namaFileBukti = null;

        try {
            // 1. Validasi Tagihan & Scope Unit (Double Check)
            $tagihan = $this->tagihanModel->find($id_tagihan);
            if (!$tagihan) throw new \Exception('Tagihan tidak ditemukan.');

            if (!$isSuperAdmin && !empty($kodeJenjangSession) && $tagihan['kode_jenjang'] !== $kodeJenjangSession) {
                throw new \Exception('Akses ditolak: Tagihan ini milik unit lain.');
            }

            // =========================================================================
            // AUDIT TRAIL: VALIDASI TUTUP BUKU
            // =========================================================================
            $siswa = $this->siswaModel->find($tagihan['id_siswa']);
            $kodeJenjangSiswa = $siswa['kode_jenjang'] ?? 'GLOBAL';

            $lockDate = $this->getLockDate($isSuperAdmin ? 'GLOBAL' : $kodeJenjangSiswa);
            if (strtotime($tanggalBayar) <= strtotime($lockDate)) {
                throw new \Exception('TIDAK DIIZINKAN: Tanggal pembayaran berada dalam periode Tutup Buku (Locked).');
            }

            // 2. Hitung Sisa & Validasi Overpayment
            $res = $this->pembayaranModel->where('id_tagihan', $id_tagihan)
                                         ->selectSum('jumlah_bayar', 'total')
                                         ->first();
            
            $sudahBayar   = (float)($res['total'] ?? 0);
            $sisaHarusnya = (float)$tagihan['jumlah'] - $sudahBayar;

            if ($jumlahInput > ($sisaHarusnya + 100)) { // Toleransi 100 perak pembulatan
                 throw new \Exception('Jumlah bayar melebihi sisa tagihan. Sisa: Rp ' . number_format($sisaHarusnya, 0, ',', '.'));
            }

            // 3. Handle Upload Bukti
            $fileBukti = $this->request->getFile('bukti_bayar');
            if ($fileBukti && $fileBukti->isValid() && !$fileBukti->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/pembayaran';
                if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);
                
                $namaFileBukti = $fileBukti->getRandomName();
                $fileBukti->move($uploadPath, $namaFileBukti);
            }

            // 4. Insert Pembayaran
            $noKwitansi = 'KWT-' . date('Ym') . '-' . strtoupper(random_string('alnum', 5));
            $this->pembayaranModel->insert([
                'kode_jenjang'      => $tagihan['kode_jenjang'],
                'id_tagihan'        => $id_tagihan,
                'no_kwitansi'       => $noKwitansi,
                'id_user_admin'     => $idAdmin,
                'jumlah_bayar'      => $jumlahInput,
                'tanggal_bayar'     => $tanggalBayar,
                'metode_pembayaran' => $metode,
                'keterangan'        => $this->request->getPost('keterangan'),
                'bukti_bayar'       => $namaFileBukti
            ]);
            $idPembayaran = $this->pembayaranModel->getInsertID();

            // 5. Update Status Tagihan
            $totalSekarang = $sudahBayar + $jumlahInput;
            $statusBaru    = ($totalSekarang >= ((float)$tagihan['jumlah'] - 100)) ? 'lunas' : 'sebagian';
            
            $this->tagihanModel->update($id_tagihan, [
                'terbayar'   => $totalSekarang,
                'status'     => $statusBaru,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // =========================================================================
            // 6. STEALTH ACCOUNTING: JURNAL PENERIMAAN KAS
            // =========================================================================
            $jurnalModel = new AkuntansiJurnalModel();
            $jurnalDetailModel = new AkuntansiJurnalDetailModel();
            $coaModel = new AkuntansiCoaModel();

            // C1. Cari Akun Kas Yayasan/Unit (Debit)
            $coaKas = $coaModel->where('kode_jenjang', 'GLOBAL')->where('kode_akun', '1101')->first();
            if (!$coaKas) $coaKas = $coaModel->where('kode_jenjang', 'GLOBAL')->like('nama_akun', 'Kas')->where('is_parent', 0)->first();
            if (!$coaKas) throw new \Exception("Akun Kas Utama tidak ditemukan di Master COA Yayasan.");

            // C2. Cari Akun Pendapatan SPP (Kredit)
            $coaPendapatan = $coaModel->where('kode_jenjang', 'GLOBAL')->like('nama_akun', 'Pendapatan Jasa')->where('is_parent', 0)->first();
            if (!$coaPendapatan) $coaPendapatan = $coaModel->where('kode_jenjang', 'GLOBAL')->like('kode_akun', '41', 'after')->where('is_parent', 0)->first();
            if (!$coaPendapatan) throw new \Exception("Akun Pendapatan tidak ditemukan di Master COA Yayasan.");

            // Insert Header Jurnal
            $headerData = [
                'kode_jenjang'     => $kodeJenjangSiswa, 
                'nomor_jurnal'     => 'IN-' . date('Ym', strtotime($tanggalBayar)) . '-' . strtoupper(random_string('alnum', 4)),
                'tanggal'          => $tanggalBayar,
                'referensi'        => $noKwitansi,
                'deskripsi'        => 'Penerimaan Tagihan ID-'.$id_tagihan.' a.n ' . ($siswa['nama_lengkap'] ?? 'Siswa'),
                'total_debit'      => $jumlahInput,
                'total_kredit'     => $jumlahInput,
                'sumber_transaksi' => 'Kas Masuk',
                'status'           => 'Posted',
                'created_by'       => $idAdmin,
            ];
            $jurnalModel->insert($headerData);
            $idJurnal = $jurnalModel->getInsertID();

            // Insert Double-Entry Details
            $barisJurnal = [
                // DEBIT: Kas Bertambah
                ['id_jurnal' => $idJurnal, 'id_coa' => $coaKas['id'], 'debit' => $jumlahInput, 'kredit' => 0, 'keterangan' => 'Penerimaan Kas via ' . $metode],
                // KREDIT: Pendapatan Bertambah
                ['id_jurnal' => $idJurnal, 'id_coa' => $coaPendapatan['id'], 'debit' => 0, 'kredit' => $jumlahInput, 'keterangan' => $headerData['deskripsi']],
            ];
            $jurnalDetailModel->insertBatch($barisJurnal);

            $db->transCommit();
            return redirect()->to(base_url('app/keuangan/tagihan'))
                             ->with('success', 'Pembayaran berhasil disimpan dan dijurnal otomatis. Status: ' . strtoupper($statusBaru));

        } catch (\Exception $e) {
            $db->transRollback();
            // Hapus file jika gagal database
            if ($namaFileBukti && file_exists(FCPATH . 'uploads/pembayaran/' . $namaFileBukti)) {
                unlink(FCPATH . 'uploads/pembayaran/' . $namaFileBukti);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Batal Pembayaran & Auto Reversal Jurnal (Delete)
     */
    public function delete($id = null)
    {
        if (!$id) return redirect()->back();

        $isSuperAdmin = $this->checkSuperAdmin();
        $sessionJenjang = session('kode_jenjang');

        $pembayaran = $this->pembayaranModel->find($id);
        if (!$pembayaran) return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');

        $tagihan = $this->tagihanModel->find($pembayaran['id_tagihan']);
        $siswa = $this->siswaModel->find($tagihan['id_siswa'] ?? 0);
        $kodeJenjangSiswa = $siswa['kode_jenjang'] ?? 'GLOBAL';

        if (!$isSuperAdmin && $kodeJenjangSiswa !== $sessionJenjang) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // =========================================================================
        // AUDIT TRAIL: VALIDASI TUTUP BUKU PADA SAAT HAPUS
        // =========================================================================
        $lockDate = $this->getLockDate($isSuperAdmin ? 'GLOBAL' : $kodeJenjangSiswa);
        if (strtotime($pembayaran['tanggal_bayar']) <= strtotime($lockDate)) {
            return redirect()->back()->with('error', 'AUDIT LOCK: Tidak dapat membatalkan penerimaan di bulan yang sudah ditutup bukunya. Hubungi Akuntan Yayasan untuk Jurnal Pembalik.');
        }

        $this->db->transBegin();
        try {
            // A. KEMBALIKAN STATUS TAGIHAN (Revert Sisa Tagihan)
            $terbayarRevert = max(0, $tagihan['terbayar'] - $pembayaran['jumlah_bayar']);
            $statusRevert = ($terbayarRevert <= 0) ? 'Belum Bayar' : 'Sebagian';
            
            $this->tagihanModel->update($tagihan['id'], [
                'terbayar' => $terbayarRevert,
                'status'   => $statusRevert
            ]);

            // B. HAPUS PEMBAYARAN (Hapus fisik bukti opsional jika ada)
            if ($pembayaran['bukti_bayar'] && file_exists(FCPATH . 'uploads/pembayaran/' . $pembayaran['bukti_bayar'])) {
                unlink(FCPATH . 'uploads/pembayaran/' . $pembayaran['bukti_bayar']);
            }
            $this->pembayaranModel->delete($id);

            // C. AUTO-REVERSAL JURNAL AKUNTANSI
            // Hapus berdasarkan nomor kwitansi yang tersimpan di kolom referensi jurnal
            $jurnalModel = new AkuntansiJurnalModel();
            $jurnalModel->where('referensi', $pembayaran['no_kwitansi'])->delete();

            $this->db->transCommit();
            return redirect()->to(base_url('app/keuangan/tagihan'))->with('success', 'Pembayaran dibatalkan. Tagihan direvisi, dan Jurnal Akuntansi otomatis ditarik (Auto-Reversal).');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi pada sistem Akuntansi.');
        }
    }

    /**
     * Cetak Kwitansi Pembayaran (FIX 404)
     */
    public function cetak($id)
    {
        // 1. Ambil Data Pembayaran Lengkap
        $pembayaran = $this->pembayaranModel
            ->select('pembayaran.*, 
                      tagihan.deskripsi as deskripsi_tagihan, 
                      tagihan.jumlah as total_tagihan,
                      siswa.nama_lengkap, siswa.nis, siswa.kode_jenjang, 
                      kelas.nama_kelas, 
                      jenis_pembayaran.nama_pembayaran')
            ->join('tagihan', 'tagihan.id = pembayaran.id_tagihan')
            ->join('siswa', 'siswa.id = tagihan.id_siswa')
            ->join('siswa_enrollment', 'siswa.id = siswa_enrollment.id_siswa AND siswa_enrollment.status_akademik = "Aktif"', 'left') 
            ->join('kelas', 'kelas.id = siswa_enrollment.id_kelas', 'left')
            ->join('jenis_pembayaran', 'jenis_pembayaran.id = tagihan.id_jenis_pembayaran')
            ->find($id);

        if (!$pembayaran) {
            return "Data pembayaran tidak ditemukan.";
        }

        // Security Check
        $isSuperAdmin = $this->checkSuperAdmin();
        $kodeJenjangSession = session('kode_jenjang');
        
        if (!$isSuperAdmin && !empty($kodeJenjangSession) && $pembayaran['kode_jenjang'] !== $kodeJenjangSession) {
             return "Akses Ditolak: Anda tidak memiliki hak untuk mencetak kwitansi unit ini.";
        }

        // 2. Ambil Identitas Sekolah (Kop Surat)
        $jenjang = $pembayaran['kode_jenjang'] ?? 'Global';
        $settings = $this->settingsModel->getSettingsAsArray($jenjang);
        
        // Fallback ke Global
        if (empty($settings) && $jenjang !== 'Global') {
            $settings = $this->settingsModel->getSettingsAsArray('Global');
        }

        $identitas = [
            'nama'   => $settings['nama_sekolah'] ?? $settings['nama_yayasan'] ?? 'YAYASAN PENDIDIKAN GENERASI JUARA',
            'alamat' => $settings['alamat_sekolah'] ?? $settings['alamat'] ?? 'Jl. Pendidikan No. 123, Kota Harapan Indah',
            'kontak' => 'Telp: ' . ($settings['telepon_sekolah'] ?? $settings['telepon'] ?? '-')
        ];

        // 3. Terbilang
        $terbilang = $this->terbilang($pembayaran['jumlah_bayar']) . ' Rupiah';

        $data = [
            'pembayaran'    => $pembayaran,
            'identitas'     => $identitas,
            'terbilang'     => $terbilang,
            'user_pencetak' => session()->get('username') ?? 'Admin Keuangan'
        ];

        return view('keuangan/pembayaran/cetak_kwitansi', $data);
    }

    // Helper Terbilang Sederhana
    private function terbilang($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai <20) {
            $temp = $this->terbilang($nilai - 10). " Belas";
        } else if ($nilai < 100) {
            $temp = $this->terbilang($nilai/10)." Puluh". $this->terbilang($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . $this->terbilang($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = $this->terbilang($nilai/100) . " Ratus" . $this->terbilang($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . $this->terbilang($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = $this->terbilang($nilai/1000) . " Ribu" . $this->terbilang($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = $this->terbilang($nilai/1000000) . " Juta" . $this->terbilang($nilai % 1000000);
        } 
        return $temp;
    }

    private function getNavigation()
    {
        return [
            'dashboard'   => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
            'budget'      => ['label' => 'Anggaran (Budget)', 'icon' => 'pie-chart', 'url' => 'app/keuangan/budget'],
            'tagihan'     => ['label' => 'Tagihan & Piutang', 'icon' => 'file-text', 'url' => 'app/keuangan/tagihan'],
            'pemasukan'   => ['label' => 'Pemasukan', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/laporan/pemasukan'],
            'pengeluaran' => ['label' => 'Pengeluaran', 'icon' => 'arrow-up-circle', 'url' => 'app/keuangan/laporan/pengeluaran'],
            // Jika Anda ingin menyembunyikan Akuntansi di sini, Anda bisa menghapusnya atau membiarkannya sesuai selera.
            'Akuntansi'   => ['label' => 'Akuntansi', 'icon' => 'printer', 'url' => 'app/keuangan/akuntansi'],
        ];
    }
}