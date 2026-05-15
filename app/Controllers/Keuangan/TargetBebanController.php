<?php

namespace App\Controllers\Keuangan;

use App\Controllers\BaseController;
use App\Models\Keuangan\BudgetModel;

/**
 * TargetBebanController
 * Mengelola penetapan pagu anggaran beban unit (Tetap vs Variabel)
 * Terintegrasi dengan Komponen Gaji & Scope Unit (Jenjang)
 */
class TargetBebanController extends BaseController
{
    protected $budgetModel;
    protected $db;

    public function __construct()
    {
        $this->budgetModel = new BudgetModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Tampilan Kalkulator Anggaran Beban dengan Scope Unit
     */
    public function index()
    {
        // 1. Ambil Scope Unit dari Query String (Filter)
        $jenjangFilter = $this->request->getGet('jenjang');

        // 2. Ambil komponen gaji yang aktif
        // Jika jenjang dipilih, kita bisa memfilter komponen yang spesifik untuk jenjang tersebut atau Global
        $komponenQuery = $this->db->table('komponen_gaji')
            ->select('id, nama_komponen, metode_hitung, nominal_default, kode_jenjang')
            ->where('deleted_at', null)
            ->where('is_aktif', 1);

        if ($jenjangFilter) {
            $komponenQuery->groupStart()
                ->where('kode_jenjang', $jenjangFilter)
                ->orWhere('kode_jenjang', 'GLOBAL')
                ->orWhere('kode_jenjang', null)
            ->groupEnd();
        }

        $komponenGaji = $komponenQuery->get()->getResultArray();

        // 3. Siapkan data untuk View
        $data = [
            'title'          => 'Kalkulator Anggaran Beban',
            'jenjang'        => $this->db->table('jenjang_sekolah')->get()->getResultArray(),
            'categories'     => $this->db->table('kategori_anggaran')
                                    ->where('kelompok', 'beban')
                                    ->orderBy('kode_kategori', 'ASC')
                                    ->get()->getResultArray(),
            'komponen_gaji'  => $komponenGaji,
            'filter_jenjang' => $jenjangFilter,
            'budgets'        => [] // Digunakan jika ingin menampilkan history input di bawah form
        ];

        return view('keuangan/budget/target_beban', $data);
    }

    /**
     * Proses Simpan Target Anggaran Beban
     */
    public function save()
    {
        $idKategori  = $this->request->getPost('id_kategori');
        $kodeJenjang = $this->request->getPost('kode_jenjang'); // Null jika Global
        $nominalRaw  = $this->request->getPost('nominal_final');
        $nominal     = (float) $nominalRaw;
        $calcMode    = $this->request->getPost('calc_mode');
        $tahun       = $this->request->getPost('tahun');
        
        // Metadata untuk keterangan otomatis
        $meta = [
            'mode'     => $calcMode,
            'komponen' => $this->request->getPost('nama_komponen_hidden'),
            'siswa'    => $this->request->getPost('jumlah_siswa'),
            'siklus'   => $this->request->getPost('siklus')
        ];

        // Membangun deskripsi audit trail
        $ketDetail = "Gaji/Beban: " . $meta['komponen'];
        if ($calcMode === 'per_siswa' || $calcMode === 'variabel') {
            $ketDetail .= " (VARIABEL) - Berbasis " . ($meta['siswa'] ?: 0) . " Siswa x " . $meta['siklus'] . " Bln";
        } else {
            $ketDetail .= " (TETAP) - Alokasi " . $meta['siklus'] . " Bln";
        }

        $data = [
            'kode_jenjang' => $kodeJenjang ?: null,
            'id_kategori'  => $idKategori,
            'tahun'        => $tahun,
            'nominal'      => $nominal,
            'keterangan'   => $ketDetail
        ];

        // Cek duplikasi untuk update data yang sama (Unit + Kategori + Tahun + Komponen Nama)
        $existing = $this->budgetModel->where([
            'kode_jenjang' => $data['kode_jenjang'],
            'id_kategori'  => $idKategori,
            'tahun'        => $tahun,
            'keterangan'   => $ketDetail
        ])->first();

        if ($existing) {
            $this->budgetModel->update($existing['id'], $data);
            $message = "Anggaran beban berhasil diperbarui.";
        } else {
            $this->budgetModel->insert($data);
            $message = "Pagu anggaran beban baru berhasil disimpan.";
        }

        // Redirect kembali ke manajemen anggaran utama
        return redirect()->to(base_url('app/keuangan/budget'))
                        ->with('success', $message);
    }
}