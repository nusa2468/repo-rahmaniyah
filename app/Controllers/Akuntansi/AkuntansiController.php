<?php

namespace App\Controllers\Akuntansi;

use App\Controllers\BaseController;
use App\Models\Akuntansi\AkuntansiCoaModel;
use App\Models\Akuntansi\AkuntansiKategoriModel;
use Throwable;

/**
 * Controller AkuntansiController
 * Mengelola Dashboard dan Master Chart of Accounts (COA) untuk Yayasan.
 */
class AkuntansiController extends BaseController
{
    protected $coaModel;
    protected $kategoriModel;
    protected $db;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ROOT'];

    public function __construct()
    {
        $this->coaModel = new AkuntansiCoaModel();
        $this->kategoriModel = new AkuntansiKategoriModel();
        $this->db = \Config\Database::connect();
    }

    private function getDaftarUnit()
    {
        $daftarUnit = [];
        if ($this->db->tableExists('jenjang_sekolah')) {
            $query = $this->db->table('jenjang_sekolah')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get();
            foreach ($query->getResultArray() as $row) {
                $val = strtoupper($row['kode_jenjang']);
                if (!in_array($val, $this->globalIdentifiers)) {
                    $daftarUnit[$val] = $row['nama_jenjang'];
                }
            }
        }
        return $daftarUnit;
    }

    public function index()
    {
        $session = session();
        $userRole = strtolower($session->get('role_name') ?? $session->get('role') ?? '');
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        
        $isGlobal = in_array($userJenjang, $this->globalIdentifiers) || in_array($userRole, ['superadmin', 'yayasan']);

        if (!$isGlobal) {
            return redirect()->to(base_url('app'))->with('error', 'Akses Ditolak! Modul Akuntansi Enterprise hanya dapat diakses oleh Manajemen Yayasan.');
        }

        $filterJenjang = $this->request->getGet('jenjang') ?? 'GLOBAL';
        
        $coaData = $this->coaModel->getCoaBuilder($filterJenjang)->get()->getResultArray();
        
        $groupedCoa = [];
        foreach ($coaData as $c) {
            $kategoriNama = $c['nama_kategori'] ?? 'Tidak Terkategori';
            $groupedCoa[$kategoriNama][] = $c;
        }

        $totalAkun = count($coaData);
        $totalHeader = 0;
        $totalTransaksi = 0;
        foreach ($coaData as $c) {
            if ($c['is_parent'] == 1) $totalHeader++;
            else $totalTransaksi++;
        }

        $data = [
            'title'          => 'Bagan Akun (Chart of Accounts)',
            'current_module' => 'akuntansi',
            'grouped_coa'    => $groupedCoa,
            'stats'          => [
                'total'     => $totalAkun,
                'header'    => $totalHeader,
                'transaksi' => $totalTransaksi
            ],
            'daftarUnit'     => $this->getDaftarUnit(),
            'filterJenjang'  => $filterJenjang,
            'isGlobal'       => $isGlobal
        ];

        return view('akuntansi/index', $data);
    }

    /**
     * Tampilkan Form COA Baru
     */
    public function new()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        $filterJenjang  = $this->request->getGet('jenjang') ?? ($isGlobal ? 'GLOBAL' : $sessionJenjang);

        // Ambil Data Kategori & Parent List
        $kategoriList = $this->kategoriModel->orderBy('kode_kategori', 'ASC')->findAll();
        $parentList   = $this->coaModel->where('is_parent', 1)->orderBy('kode_akun', 'ASC')->findAll();

        $data = [
            'title'         => 'Registrasi Akun Baru',
            'current_module'=> 'akuntansi',
            'coa'           => null,
            'kategoriList'  => $kategoriList,
            'parentList'    => $parentList,
            'daftarUnit'    => $this->getDaftarUnit(),
            'filterJenjang' => $filterJenjang,
            'isGlobal'      => $isGlobal
        ];

        return view('akuntansi/coa/form', $data);
    }

    /**
     * Tampilkan Form Edit COA
     */
    public function edit($id)
    {
        $coa = $this->coaModel->find($id);
        if (!$coa) {
            return redirect()->to(base_url('app/akuntansi'))->with('error', 'Akun tidak ditemukan.');
        }

        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);

        if (!$isGlobal && strtoupper($coa['kode_jenjang']) !== $sessionJenjang) {
            return redirect()->to(base_url('app/akuntansi'))->with('error', 'Akses Ditolak. Akun ini milik unit lain.');
        }

        $kategoriList = $this->kategoriModel->orderBy('kode_kategori', 'ASC')->findAll();
        $parentList   = $this->coaModel->where('is_parent', 1)->where('id !=', $id)->orderBy('kode_akun', 'ASC')->findAll();

        $data = [
            'title'         => 'Konfigurasi Akun',
            'current_module'=> 'akuntansi',
            'coa'           => (object) $coa,
            'kategoriList'  => $kategoriList,
            'parentList'    => $parentList,
            'daftarUnit'    => $this->getDaftarUnit(),
            'filterJenjang' => $coa['kode_jenjang'],
            'isGlobal'      => $isGlobal
        ];

        return view('akuntansi/coa/form', $data);
    }

    /**
     * Proses Simpan Data
     */
    public function save($id = null)
    {
        $id = $id ?? $this->request->getPost('id');
        
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);

        $finalJenjang = $isGlobal ? ($inputJenjang ?: 'GLOBAL') : $sessionJenjang;

        $saldoAwal = preg_replace('/[^0-9]/', '', $this->request->getPost('saldo_awal') ?: '0');
        $isParent  = $this->request->getPost('is_parent') ? 1 : 0;

        $data = [
            'kode_jenjang' => $finalJenjang,
            'id_kategori'  => $this->request->getPost('id_kategori'),
            'kode_akun'    => $this->request->getPost('kode_akun'),
            'nama_akun'    => $this->request->getPost('nama_akun'),
            // Jika akun adalah Header (Parent), saldo awal wajib 0
            'saldo_awal'   => $isParent ? 0 : $saldoAwal,
            'is_parent'    => $isParent,
            'parent_id'    => $this->request->getPost('parent_id') ?: null,
            'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($id) {
            $data['id'] = $id;
            
            // Cek apakah akun ini memiliki child (jika ada child, tidak boleh diubah menjadi detail/transaksi)
            if ($isParent == 0) {
                $hasChild = $this->coaModel->where('parent_id', $id)->countAllResults();
                if ($hasChild > 0) {
                    return redirect()->back()->withInput()->with('error', 'Gagal: Akun ini masih memiliki Sub-Akun. Ubah sub-akun tersebut terlebih dahulu sebelum mengubah status akun ini menjadi Akun Detail.');
                }
            }
        } else {
            // Cek keunikan kode_akun di dalam jenjang yang sama
            $exists = $this->coaModel->where('kode_jenjang', $finalJenjang)
                                     ->where('kode_akun', $data['kode_akun'])
                                     ->countAllResults();
            if ($exists > 0) {
                return redirect()->back()->withInput()->with('error', "Gagal: Kode Akun {$data['kode_akun']} sudah digunakan di Unit {$finalJenjang}.");
            }
        }

        if (!$this->coaModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->coaModel->errors());
        }

        $msg = $id ? 'Data akun berhasil diperbarui.' : 'Bagan Akun (COA) baru berhasil ditambahkan.';
        return redirect()->to(base_url('app/akuntansi?jenjang=' . $finalJenjang))->with('success', $msg);
    }

    /**
     * Hapus COA (Soft Delete)
     */
    public function delete($id)
    {
        $coa = $this->coaModel->find($id);
        
        if (!$id || !$coa) {
            return redirect()->to(base_url('app/akuntansi'))->with('error', 'Akun tidak ditemukan.');
        }

        // Pastikan akun ini tidak memiliki child yang aktif
        $hasChild = $this->coaModel->where('parent_id', $id)->countAllResults();
        if ($hasChild > 0) {
            return redirect()->to(base_url('app/akuntansi'))->with('error', 'Gagal dihapus: Akun ini merupakan Induk dari akun lain. Hapus atau pindahkan sub-akun terlebih dahulu.');
        }

        // Opsional: Cek apakah akun ini sudah pernah digunakan di jurnal
        $db = \Config\Database::connect();
        $isUsed = $db->table('akuntansi_jurnal_detail')->where('id_coa', $id)->countAllResults();
        if ($isUsed > 0) {
            return redirect()->to(base_url('app/akuntansi'))->with('error', 'Gagal dihapus: Akun ini sudah memiliki riwayat transaksi jurnal. Disarankan untuk menonaktifkan (Arsip) akun tersebut daripada menghapusnya.');
        }

        $this->coaModel->delete($id);
        
        return redirect()->to(base_url('app/akuntansi?jenjang=' . $coa['kode_jenjang']))->with('success', 'Akun berhasil dihapus dari sistem.');
    }
}