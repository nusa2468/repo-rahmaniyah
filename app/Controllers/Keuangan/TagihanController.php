<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\TagihanModel;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\JenisPembayaranModel;
use App\Models\TahunAjaranModel;
use App\Models\Keuangan\PembayaranModel;
use App\Models\JenjangModel;    // ADDED
use App\Models\HakAksesModel;   // ADDED

class TagihanController extends BaseController
{
    protected $tagihanModel;
    protected $siswaModel;
    protected $kelasModel;
    protected $jenisPembayaranModel;
    protected $tahunAjaranModel;
    protected $pembayaranModel;
    protected $jenjangModel;    // ADDED
    protected $hakAksesModel;   // ADDED
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
        $this->jenjangModel         = new JenjangModel();    // ADDED
        $this->hakAksesModel        = new HakAksesModel();   // ADDED
        $this->db                   = \Config\Database::connect();
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
     * Menampilkan daftar tagihan dengan Filter, Scope Unit, dan Pagination
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

        // 2. Ambil Parameter Filter Lain
        $id_siswa        = $this->request->getGet('id_siswa');
        $selected_bulan  = $this->request->getGet('bulan_jatuh_tempo');
        $selected_status = $this->request->getGet('status');

        // 3. Terapkan Query Utama
        // Method scopeJenjang() di Model harus menangani jika $jenjangFilter null (Global)
        $this->tagihanModel->scopeJenjang($jenjangFilter)
                           ->getBaseQueryWithDetails($id_siswa, $selected_bulan);

        if ($selected_status && in_array($selected_status, ['lunas', 'belum_lunas'])) {
            $this->tagihanModel->where('tagihan.status', $selected_status);
        }

        $this->tagihanModel->orderBy('tagihan.tanggal_jatuh_tempo', 'DESC');

        // 4. Eksekusi Pagination
        $dataTagihan = $this->tagihanModel->paginate(20, 'default');
        $dataTagihan = $this->tagihanModel->processStatusReal($dataTagihan); // Update status lunas/belum real-time

        // 5. Hitung Ringkasan Statistik
        $summary = $this->tagihanModel->getGlobalSummary($jenjangFilter, $id_siswa, $selected_bulan);

        // 6. Ambil Data Jenjang untuk Dropdown (Hanya untuk Superadmin)
        $jenjangList = $this->jenjangModel->getDropdownOptions();
        
        // 7. Ambil Data Siswa (Sesuai Jenjang)
        $siswaQuery = $this->siswaModel->select('id, nis, nama_lengkap')->where('status', 'aktif');
        if ($jenjangFilter) {
            $siswaQuery->where('kode_jenjang', $jenjangFilter);
        }
        $siswaList = $siswaQuery->orderBy('nama_lengkap', 'ASC')->findAll();

        $data = [
            'title'           => 'Manajemen Tagihan Siswa',
            'current_module'  => 'keuangan',
            'tagihan'         => $dataTagihan,
            'pager'           => $this->tagihanModel->pager,
            'active_unit'     => $jenjangFilter, // Untuk kompatibilitas view lama
            'filter_jenjang'  => $jenjangFilter, // Untuk view baru
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
        ];

        return view('keuangan/tagihan/index', $data);
    }

    /**
     * Form Tambah/Edit Tagihan Manual
     */
    public function form($id = null)
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        // Tentukan scope default
        $kodeJenjang = $isSuperAdmin ? $this->request->getGet('jenjang') : $session->get('kode_jenjang');

        $tagihan = null;
        if ($id) {
            $query = $this->tagihanModel
                        ->select('tagihan.*, siswa.nama_lengkap, siswa.nis')
                        ->join('siswa', 'siswa.id = tagihan.id_siswa');

            // Security Check: Jika bukan superadmin, pastikan data milik unit sendiri
            if (!$isSuperAdmin) {
                $query->where('tagihan.kode_jenjang', $kodeJenjang);
            }

            $tagihan = $query->find($id);
            
            if (!$tagihan) {
                return redirect()->to(base_url('app/keuangan/tagihan'))->with('error', 'Data tagihan tidak ditemukan atau Anda tidak memiliki akses.');
            }
            
            // Override kodeJenjang dengan data tagihan yang sedang diedit
            $kodeJenjang = $tagihan['kode_jenjang'];
        }

        $jenisPembayaran = $this->jenisPembayaranModel;
        $siswaList       = $this->siswaModel->select('id, nis, nama_lengkap')->where('status', 'aktif');

        // Filter dropdown berdasarkan scope
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
            'jenjang_list'     => $this->jenjangModel->getDropdownOptions(), // Untuk dropdown di form jika Superadmin
            'navigation'       => $this->getNavigation(),
        ];

        return view('keuangan/tagihan/form', $data);
    }

    /**
     * Proses Simpan (Insert/Update)
     */
    public function save()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        $id = $this->request->getPost('id');

        // Scope Unit
        $kodeJenjang = $this->request->getPost('kode_jenjang');
        if (!$isSuperAdmin) {
            $kodeJenjang = $session->get('kode_jenjang');
        }

        if (!$this->validate([
            'id_jenis_pembayaran' => 'required',
            'jumlah'              => 'required|numeric',
            'tanggal_jatuh_tempo' => 'required|valid_date'
        ])) {
             return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $formData = [
            'id'                  => $id ?: null,
            'id_jenis_pembayaran' => $this->request->getPost('id_jenis_pembayaran'),
            'deskripsi'           => $this->request->getPost('deskripsi'),
            'jumlah'              => $this->request->getPost('jumlah'),
            'tanggal_jatuh_tempo' => $this->request->getPost('tanggal_jatuh_tempo'),
        ];

        if (!$id) {
            // INSERT BARU
            $formData['id_siswa']        = $this->request->getPost('id_siswa');
            $formData['status']          = 'belum_lunas';
            $formData['tanggal_tagihan'] = date('Y-m-d');
            
            if (!empty($kodeJenjang)) {
                $formData['kode_jenjang'] = $kodeJenjang;
            } else {
                // Fallback: Ambil jenjang dari siswa
                $siswa = $this->siswaModel->find($this->request->getPost('id_siswa'));
                $formData['kode_jenjang'] = $siswa['kode_jenjang'] ?? null;
            }
        } else {
            // UPDATE
            // Security Check
            if (!$isSuperAdmin) {
                $existing = $this->tagihanModel->find($id);
                if ($existing && $existing['kode_jenjang'] !== $kodeJenjang) {
                    return redirect()->back()->with('error', 'Pelanggaran Akses: Anda tidak berhak mengubah data unit lain.');
                }
            }
        }

        if (!$this->tagihanModel->save($formData)) {
            return redirect()->back()->withInput()->with('errors', $this->tagihanModel->errors());
        }

        $message = $id ? 'Data tagihan berhasil diperbarui.' : 'Tagihan baru berhasil dibuat.';
        return redirect()->to(base_url('app/keuangan/tagihan'))->with('success', $message);
    }

    /**
     * Detail Tagihan
     */
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
        if (!$isSuperAdmin) {
            $query->where('kode_jenjang', $kodeJenjang);
        }
        $tagihan = $query->find($id);

        if (!$tagihan) {
            return redirect()->back()->with('error', 'Akses ditolak atau data tidak ada.');
        }

        $cekPembayaran = $this->pembayaranModel->where('id_tagihan', $id)->countAllResults();
        if ($cekPembayaran > 0) {
            return redirect()->back()->with('error', 'Gagal: Tagihan sudah memiliki riwayat pembayaran.');
        }

        $this->tagihanModel->delete($id);
        return redirect()->to(base_url('app/keuangan/tagihan'))->with('success', 'Tagihan berhasil dihapus.');
    }

    public function mass_form()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        // Scope
        $kodeJenjang = $isSuperAdmin ? $this->request->getGet('jenjang') : $session->get('kode_jenjang');
        
        $jenisQuery = $this->jenisPembayaranModel;
        $kelasQuery = $this->kelasModel;

        if (!empty($kodeJenjang)) {
            $jenisQuery->where('kode_jenjang', $kodeJenjang);
            $kelasQuery->where('kode_jenjang', $kodeJenjang);
        }

        $data = [
            'title'            => 'Generate Tagihan Masal',
            'current_module'   => 'keuangan',
            'jenis_pembayaran' => $jenisQuery->findAll(),
            'kelas'            => $kelasQuery->findAll(),
            'active_unit'      => $kodeJenjang,
            'isSuperAdmin'     => $isSuperAdmin,
            'jenjang_list'     => $this->jenjangModel->getDropdownOptions(),
            'navigation'       => $this->getNavigation()
        ];
        return view('keuangan/tagihan/mass_create', $data);
    }

    public function generate_proses()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        // Ambil input dan scope
        $inputJenjang = $this->request->getPost('kode_jenjang');
        $kodeJenjang  = $isSuperAdmin ? $inputJenjang : $session->get('kode_jenjang');

        $id_jenis_pembayaran = $this->request->getPost('id_jenis_pembayaran');
        $id_kelas            = $this->request->getPost('id_kelas');
        
        $tahunAjaranAktif = $this->tahunAjaranModel->where('status', 'aktif')->first();
        $jenisPembayaran  = $this->jenisPembayaranModel->find($id_jenis_pembayaran);

        if (!$jenisPembayaran || !$tahunAjaranAktif) {
            return redirect()->back()->with('error', 'Konfigurasi tidak valid.');
        }

        // Validasi Akses Silang Unit
        if (!empty($kodeJenjang) && $jenisPembayaran['kode_jenjang'] !== $kodeJenjang && $jenisPembayaran['kode_jenjang'] !== 'GLOBAL') {
             return redirect()->back()->with('error', 'Jenis pembayaran tidak sesuai dengan unit yang dipilih.');
        }

        $querySiswa = $this->siswaModel
            ->select('siswa.id, siswa.kode_jenjang')
            ->join('siswa_enrollment', 'siswa_enrollment.id_siswa = siswa.id')
            ->where([
                'siswa_enrollment.id_kelas'        => $id_kelas, 
                'siswa_enrollment.id_tahun_ajaran' => $tahunAjaranAktif['id'], 
                'siswa.status'                     => 'aktif'
            ]);
        
        if (!empty($kodeJenjang)) {
            $querySiswa->where('siswa.kode_jenjang', $kodeJenjang);
        }

        $siswaDiKelas = $querySiswa->findAll();

        if (empty($siswaDiKelas)) {
            return redirect()->back()->with('error', 'Tidak ada siswa aktif ditemukan di kelas terpilih.');
        }

        $batchData   = [];
        $tahun_mulai = (int)substr($tahunAjaranAktif['tahun_ajaran'], 0, 4);
        
        $bulan_map  = [1 => 7, 2 => 8, 3 => 9, 4 => 10, 5 => 11, 6 => 12, 7 => 1, 8 => 2, 9 => 3, 10 => 4, 11 => 5, 12 => 6];
        $nama_bulan = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];

        foreach ($siswaDiKelas as $siswa) {
            // Gunakan unit siswa jika tidak ada paksaan unit
            $jenjangTagihan = !empty($kodeJenjang) ? $kodeJenjang : $siswa['kode_jenjang'];

            if ($jenisPembayaran['tipe'] == 'bulanan') {
                for ($i = 1; $i <= 12; $i++) {
                    $bulan_angka = $bulan_map[$i];
                    $tahun       = ($bulan_angka >= 7) ? $tahun_mulai : $tahun_mulai + 1;
                    $deskripsi   = $jenisPembayaran['nama_pembayaran'] . ' - ' . $nama_bulan[$i-1] . ' ' . $tahun;
                    
                    $batchData[] = [
                        'kode_jenjang'        => $jenjangTagihan,
                        'id_siswa'            => $siswa['id'],
                        'id_jenis_pembayaran' => $id_jenis_pembayaran,
                        'deskripsi'           => $deskripsi,
                        'jumlah'              => $jenisPembayaran['nominal'],
                        'status'              => 'belum_lunas',
                        'tanggal_tagihan'     => date('Y-m-d'),
                        'tanggal_jatuh_tempo' => "{$tahun}-" . str_pad($bulan_angka, 2, '0', STR_PAD_LEFT) . "-10",
                        'created_at'          => date('Y-m-d H:i:s')
                    ];
                }
            } 
            else {
                $batchData[] = [
                    'kode_jenjang'        => $jenjangTagihan,
                    'id_siswa'            => $siswa['id'],
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
            $chunks = array_chunk($batchData, 100);
            foreach ($chunks as $chunk) {
                $this->tagihanModel->insertBatch($chunk);
            }
            return redirect()->to(base_url('app/keuangan/tagihan'))->with('success', count($batchData) . ' tagihan berhasil digenerate.');
        }

        return redirect()->back()->with('error', 'Gagal memproses pembuatan tagihan.');
    }
    
    public function bayar($id = null)
    {
        if (!$id) return redirect()->to(base_url('app/keuangan/tagihan'));
        return redirect()->to(base_url('app/keuangan/pembayaran/create/' . $id));
    }

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