<?php

namespace App\Controllers\Cms;

use App\Controllers\BaseController;
use App\Models\Cms\AgendaModel; // Pastikan Model ini ada

/**
 * Controller Agenda
 * Mengelola jadwal kegiatan sekolah dengan dukungan sistem Multi-Unit (SD/SMP/SMA/Global).
 */
class Agenda extends BaseController
{
    protected $agendaModel;
    protected $db;

    public function __construct()
    {
        $this->agendaModel = new AgendaModel();
        $this->db          = \Config\Database::connect();
    }

    /**
     * Helper: Ambil Daftar Unit dari Database (Dinamis)
     */
    private function getDaftarUnit()
    {
        $daftarUnit = [];
        try {
            if ($this->db->tableExists('jenjang_sekolah')) {
                $query = $this->db->table('jenjang_sekolah')->get();
                foreach ($query->getResultArray() as $row) {
                    $val = $row['kode_jenjang'];
                    $label = $row['nama'] ?? $row['nama_jenjang'] ?? $row['kode_jenjang'];
                    $daftarUnit[$val] = $label;
                }
            }
        } catch (\Exception $e) { }
        
        // Fallback jika tabel kosong
        if (empty($daftarUnit)) {
            $daftarUnit = ['TK' => 'TK', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA'];
        }
        return $daftarUnit;
    }

    /**
     * Helper: Cek Akses Global
     */
    private function isGlobalAccess(?string $context): bool
    {
        $globalScopes = ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT'];
        return empty($context) || in_array(strtoupper($context), $globalScopes);
    }

    /**
     * Menampilkan daftar agenda.
     */
    public function index()
    {
        // 1. Setup Data Dinamis
        $daftarUnit = $this->getDaftarUnit();
        $sessionJenjang = session('kode_jenjang');
        $isGlobal = $this->isGlobalAccess($sessionJenjang);

        // 2. Filter Logic
        $filterJenjang = $this->request->getGet('jenjang');
        
        // Tentukan Scope Query
        $scopeQuery = $isGlobal ? $filterJenjang : $sessionJenjang;

        // Terapkan Filter pada Model
        if (!empty($scopeQuery)) {
            $this->agendaModel->where('kode_jenjang', $scopeQuery);
        }

        // Ambil Data (Pagination)
        $agenda = $this->agendaModel->orderBy('tanggal_mulai', 'DESC')->paginate(10);

        $data = [
            'title'          => 'Kelola Agenda Kegiatan',
            'agenda'         => $agenda,
            'pager'          => $this->agendaModel->pager,
            
            // UI Helpers
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $daftarUnit
        ];

        return view('cms/agenda/index', $data);
    }

    /**
     * Form untuk menambah agenda baru.
     */
    public function new()
    {
        $data = [
            'title'      => 'Tambah Agenda Baru',
            'agenda'     => null,
            'daftarUnit' => $this->getDaftarUnit() // Kirim unit ke form
        ];
        return view('cms/agenda/form', $data);
    }

    /**
     * Form untuk mengedit agenda.
     */
    public function edit($id)
    {
        $agenda = $this->agendaModel->find($id);
        if (!$agenda) {
            return redirect()->to(base_url('app/cms/agenda'))->with('error', 'Data agenda tidak ditemukan.');
        }

        // Cek Hak Akses Edit
        $sessionJenjang = session('kode_jenjang');
        if (!$this->isGlobalAccess($sessionJenjang)) {
            // Jika agenda punya unit dan unitnya beda dengan user, tolak
            $agendaUnit = is_array($agenda) ? $agenda['kode_jenjang'] : $agenda->kode_jenjang;
            if (!empty($agendaUnit) && $agendaUnit !== $sessionJenjang) {
                return redirect()->to(base_url('app/cms/agenda'))->with('error', 'Anda tidak memiliki akses mengubah agenda unit lain.');
            }
        }

        $data = [
            'title'      => 'Edit Agenda Kegiatan',
            'agenda'     => (object) $agenda,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('cms/agenda/form', $data);
    }

    /**
     * Proses simpan data (Insert/Update).
     */
    public function save()
    {
        $id = $this->request->getPost('id');
        
        // Logika Penentuan Unit (Kode Jenjang)
        $sessionJenjang = session('kode_jenjang');
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $isGlobal       = $this->isGlobalAccess($sessionJenjang);

        // Jika Global, ambil dari input form. Jika Admin Unit, paksa dari session.
        $finalJenjang = $isGlobal ? $inputJenjang : $sessionJenjang;
        
        // Pastikan 'Global' disimpan sebagai NULL
        if (empty($finalJenjang) || strtoupper($finalJenjang) === 'GLOBAL') {
            $finalJenjang = null;
        }

        $data = [
            'kode_jenjang'    => $finalJenjang,
            'nama_kegiatan'   => $this->request->getPost('nama_kegiatan'),
            'slug'            => url_title($this->request->getPost('nama_kegiatan'), '-', true),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai') ?: null,
            'tempat'          => $this->request->getPost('tempat'),
            'keterangan'      => $this->request->getPost('keterangan'),
            'status'          => $this->request->getPost('status') ?? 'published',
        ];

        // Validasi Sederhana
        if (empty($data['nama_kegiatan']) || empty($data['tanggal_mulai'])) {
            return redirect()->back()->withInput()->with('error', 'Nama kegiatan dan tanggal mulai wajib diisi.');
        }

        try {
            if ($id) {
                $this->agendaModel->update($id, $data);
                $message = 'Agenda berhasil diperbarui.';
            } else {
                $this->agendaModel->insert($data);
                $message = 'Agenda baru berhasil diterbitkan.';
            }

            return redirect()->to(base_url('app/cms/agenda'))->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan agenda: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus data agenda.
     */
    public function delete($id)
    {
        $agenda = $this->agendaModel->find($id);

        if ($agenda) {
             // Cek Hak Akses Hapus
             $sessionJenjang = session('kode_jenjang');
             if (!$this->isGlobalAccess($sessionJenjang)) {
                 $agendaUnit = is_array($agenda) ? $agenda['kode_jenjang'] : $agenda->kode_jenjang;
                 if (!empty($agendaUnit) && $agendaUnit !== $sessionJenjang) {
                     return redirect()->to(base_url('app/cms/agenda'))->with('error', 'Akses ditolak.');
                 }
             }

            $this->agendaModel->delete($id);
            return redirect()->to(base_url('app/cms/agenda'))->with('success', 'Agenda berhasil dihapus.');
        }
        
        return redirect()->to(base_url('app/cms/agenda'))->with('error', 'Gagal menghapus data. ID tidak ditemukan.');
    }
}