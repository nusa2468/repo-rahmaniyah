<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\PengeluaranModel;
use App\Models\JenjangModel;
use App\Models\HakAksesModel;

/**
 * PengeluaranController
 * Menangani CRUD Transaksi Pengeluaran Operasional Harian
 * URL: /app/keuangan/pengeluaran
 */
class PengeluaranController extends BaseController
{
    protected $pengeluaranModel;
    protected $jenjangModel;
    protected $hakAksesModel;
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'number']);
        $this->pengeluaranModel = new PengeluaranModel();
        $this->jenjangModel     = new JenjangModel();
        $this->hakAksesModel    = new HakAksesModel();
        $this->db               = \Config\Database::connect();
    }

    /**
     * Helper: Cek Status Superadmin (Dinamis via Database)
     */
    private function checkSuperAdmin()
    {
        $session  = session();
        $userRole = $session->get('role');
        $userUnit = strtoupper($session->get('kode_jenjang') ?? '');

        // 1. Cek Unit Global
        if (in_array($userUnit, ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL'])) {
            return true;
        }
        
        // 2. Cek Role Database
        $roleData = $this->hakAksesModel->where('name', $userRole)->first();
        if ($roleData && in_array(strtoupper($roleData['kode_jenjang'] ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT'])) {
            return true;
        }

        return false;
    }

    /**
     * Halaman Utama: Daftar Pengeluaran
     */
    public function index()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        
        // 1. Filter Unit (Anti Bocor)
        $jenjangFilter = $this->request->getGet('jenjang');
        if (!$isSuperAdmin) {
            $jenjangFilter = $session->get('kode_jenjang'); // Paksa unit sendiri
        }

        // 2. Filter Tanggal
        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        // 3. Pagination Logic
        $page      = $this->request->getVar('page_default') ? (int)$this->request->getVar('page_default') : 1;
        $perPage   = 20;
        $nomorUrut = ($page - 1) * $perPage;

        // 4. Query Data (Reset Query Model dulu untuk memastikan bersih)
        $this->pengeluaranModel->resetQuery();
        
        $this->pengeluaranModel->select('pengeluaran.*, kategori_anggaran.nama_kategori')
                               ->join('kategori_anggaran', 'kategori_anggaran.id = pengeluaran.id_kategori', 'left')
                               ->where('pengeluaran.deleted_at', null);

        if (!empty($jenjangFilter)) {
            $this->pengeluaranModel->where('pengeluaran.kode_jenjang', $jenjangFilter);
        }
        if ($startDate && $endDate) {
            $this->pengeluaranModel->where('pengeluaran.tanggal >=', $startDate)
                                   ->where('pengeluaran.tanggal <=', $endDate);
        }

        $this->pengeluaranModel->orderBy('pengeluaran.tanggal', 'DESC');
        $this->pengeluaranModel->orderBy('pengeluaran.created_at', 'DESC');

        // Eksekusi Pagination
        $dataPengeluaran = $this->pengeluaranModel->paginate($perPage, 'default');

        // 5. Data Pendukung
        $jenjangList = $this->jenjangModel->getDropdownOptions();
        
        // Ambil Kategori Beban saja
        $kategoriList = $this->db->table('kategori_anggaran')
                                 ->where('kelompok', 'beban') 
                                 ->orderBy('nama_kategori', 'ASC')
                                 ->get()->getResultArray();

        $data = [
            'title'          => 'Transaksi Pengeluaran Operasional',
            'current_module' => 'keuangan',
            'pengeluaran'    => $dataPengeluaran,
            'pager'          => $this->pengeluaranModel->pager,
            'nomor_urut'     => $nomorUrut,
            'jenjang_list'   => $jenjangList,
            'filter_jenjang' => $jenjangFilter,
            'start_date'     => $startDate,
            'end_date'       => $endDate,
            'isSuperAdmin'   => $isSuperAdmin,
            'kategori_list'  => $kategoriList,
            'navigation'     => $this->getNavigation()
        ];

        return view('keuangan/pengeluaran/index', $data);
    }

    /**
     * Proses Simpan Data (Insert/Update)
     */
    public function store()
    {
        $session = session();
        $isSuperAdmin = $this->checkSuperAdmin();
        $id = $this->request->getPost('id');

        // Validasi Input
        $rules = [
            'id_kategori' => 'required',
            'tanggal'     => 'required|valid_date',
            'jumlah'      => 'required|numeric|greater_than[0]',
            'keterangan'  => 'required',
            'bukti'       => 'max_size[bukti,2048]|is_image[bukti]|mime_in[bukti,image/jpg,image/jpeg,image/png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Tentukan Unit (Security Check)
        $kodeJenjang = $this->request->getPost('kode_jenjang');
        if (!$isSuperAdmin) {
            $kodeJenjang = $session->get('kode_jenjang'); // Paksa unit sendiri
        }

        // Handle Upload Bukti
        $namaFile = null;
        $fileBukti = $this->request->getFile('bukti');
        
        // Jika Edit, cek data lama
        if ($id) {
            $existing = $this->pengeluaranModel->find($id);
            if ($existing) {
                // Security Check: Pastikan data milik unit user
                if (!$isSuperAdmin && $existing['kode_jenjang'] !== $kodeJenjang) {
                    return redirect()->back()->with('error', 'Akses ditolak: Data unit lain.');
                }
                $namaFile = $existing['bukti'];
            }
        }

        if ($fileBukti && $fileBukti->isValid() && !$fileBukti->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/pengeluaran';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);
            
            $newName = $fileBukti->getRandomName();
            $fileBukti->move($uploadPath, $newName);
            
            // Hapus file lama jika ada penggantian
            if ($namaFile && file_exists($uploadPath . '/' . $namaFile)) {
                unlink($uploadPath . '/' . $namaFile);
            }
            $namaFile = $newName;
        }

        // Data untuk disimpan
        $data = [
            'id'           => $id ?: null,
            'kode_jenjang' => $kodeJenjang,
            'id_kategori'  => $this->request->getPost('id_kategori'),
            'tanggal'      => $this->request->getPost('tanggal'),
            'jumlah'       => $this->request->getPost('jumlah'),
            'keterangan'   => $this->request->getPost('keterangan'),
            'bukti'        => $namaFile,
            'id_user'      => $session->get('id') ?? $session->get('user_id'),
        ];

        if (!$this->pengeluaranModel->save($data)) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
        }

        $msg = $id ? 'Pengeluaran berhasil diperbarui.' : 'Pengeluaran baru berhasil dicatat.';
        return redirect()->to(base_url('app/keuangan/pengeluaran'))->with('success', $msg);
    }

    /**
     * Hapus Data Pengeluaran
     */
    public function delete($id = null)
    {
        if (!$id) return redirect()->back();

        $isSuperAdmin = $this->checkSuperAdmin();
        $kodeJenjang  = session('kode_jenjang');

        $data = $this->pengeluaranModel->find($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        // Security Check Delete
        if (!$isSuperAdmin && $data['kode_jenjang'] !== $kodeJenjang) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Hapus file fisik jika ada
        if ($data['bukti'] && file_exists(FCPATH . 'uploads/pengeluaran/' . $data['bukti'])) {
            unlink(FCPATH . 'uploads/pengeluaran/' . $data['bukti']);
        }

        $this->pengeluaranModel->delete($id);
        return redirect()->to(base_url('app/keuangan/pengeluaran'))->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Navigasi Modul Keuangan
     */
    private function getNavigation()
    {
        return [
            'dashboard'   => ['label' => 'Dashboard', 'icon' => 'home', 'url' => 'app/keuangan/dashboard'],
            'budget'      => ['label' => 'Anggaran (Budget)', 'icon' => 'pie-chart', 'url' => 'app/keuangan/budget'],
            'tagihan'     => ['label' => 'Tagihan & Piutang', 'icon' => 'file-text', 'url' => 'app/keuangan/tagihan'],
            'pembayaran'  => ['label' => 'Pemasukan', 'icon' => 'arrow-down-circle', 'url' => 'app/keuangan/pembayaran'],
            'pengeluaran' => ['label' => 'Pengeluaran', 'icon' => 'arrow-up-circle', 'url' => 'app/keuangan/pengeluaran'],
            'laporan'     => ['label' => 'Laporan', 'icon' => 'printer', 'url' => 'app/keuangan/laporan/pengeluaran'],
        ];
    }
}