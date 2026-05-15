<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseAkademikController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;

/**
 * Controller KalenderPendidikan
 * Mengelola agenda akademik, hari libur, dan jadwal ujian.
 * REFAKTOR: Menggunakan aturan Role Scoping 'GLOBAL' (Anti-Bocor).
 */
class KalenderPendidikan extends BaseAkademikController
{
    /**
     * Tampilkan Daftar Kalender (Table View)
     */
    public function index(): string
    {
        // 1. Identifikasi Otoritas Berdasarkan Standar HakAksesModel
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        
        // 2. Tangkap Filter dari Request GET
        $unitParam     = $this->request->getGet('jenjang');
        $tahunAjaranId = $this->tahunAjaranAktif['id'] ?? null;

        // 3. Penentuan Scope Jenjang
        if (!$isGlobal) {
            // Admin Unit: Kunci ke unit miliknya
            $kodeJenjang = strtoupper($sessionUnit);
            $isRestricted = true;
        } else {
            // Superadmin: Bebas filter, default ke NULL (Semua Unit)
            $kodeJenjang = (!empty($unitParam) && strtoupper($unitParam) !== 'ALL' && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null;
            $isRestricted = false;
        }

        // 4. Query Data Kalender
        $query = $this->kalenderPendidikanModel
            ->select('kalender_pendidikan.*, tahun_ajaran.tahun_ajaran as nama_ta')
            ->join('tahun_ajaran', 'tahun_ajaran.id = kalender_pendidikan.tahun_ajaran_id', 'left');

        if ($kodeJenjang) {
            $query->where('kalender_pendidikan.kode_jenjang', $kodeJenjang);
        }

        if ($tahunAjaranId) {
            $query->where('kalender_pendidikan.tahun_ajaran_id', $tahunAjaranId);
        }

        $dataKalender = $query->orderBy('kalender_pendidikan.start', 'ASC')->paginate(20, 'default');
        
        // 5. Siapkan List Jenjang untuk Filter (Hanya untuk Superadmin)
        $listJenjang = [];
        if ($isGlobal) {
            $listJenjang = model('App\Models\JenjangModel')->where('status', 'Aktif')->orderBy('urutan', 'ASC')->findAll();
        }

        // 6. Label Unit Aktif
        $activeUnitLabel = 'SEMUA UNIT';
        if ($kodeJenjang) {
            $activeUnitLabel = 'UNIT ' . $kodeJenjang;
        }

        $data = [
            'title'              => 'Kalender Pendidikan',
            'tahun_ajaran_aktif' => $this->tahunAjaranAktif,
            'kalender'           => $dataKalender,
            'pager'              => $this->kalenderPendidikanModel->pager,
            'is_restricted'      => $isRestricted,
            'active_unit_label'  => $activeUnitLabel,
            'filter_selected'    => $unitParam ?? ($kodeJenjang ?? 'ALL'),
            'list_jenjang'       => $listJenjang,
            'session_unit'       => $sessionUnit
        ];

        return view('akademik/kalender/index', $this->loadViewData($data));
    }

    /**
     * Tampilan Visual Kalender (FullCalendar)
     */
    public function calendar(): string
    {
        $data = [
            'title'             => 'Visual Kalender Pendidikan',
            'tahun_ajaran_id'   => $this->tahunAjaranAktif['id'] ?? null,
            'tahun_ajaran_nama' => $this->tahunAjaranAktif['tahun_ajaran'] ?? 'Tidak Ada TA Aktif',
        ];

        return view('akademik/kalender/view_calendar', $this->loadViewData($data));
    }

    /**
     * Form Tambah Acara
     */
    public function new(): string
    {
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        
        $jenjangModel = model('App\Models\JenjangModel');
        
        $data = [
            'title'              => 'Tambah Acara Baru',
            'tahun_ajaran_aktif' => $this->tahunAjaranAktif,
            'kalender'           => [
                'tahun_ajaran_id' => $this->tahunAjaranAktif['id'] ?? '',
                'color'           => '#4e73df',
                'kode_jenjang'    => !$isGlobal ? $sessionUnit : 'GLOBAL'
            ],
            'list_jenjang'       => $jenjangModel->where('status', 'Aktif')->orderBy('urutan', 'ASC')->findAll(),
            'is_global'          => $isGlobal,
            'session_unit'       => $sessionUnit
        ];

        return view('akademik/kalender/form', $this->loadViewData($data));
    }

    /**
     * Eksekusi Simpan Acara
     */
    public function create(): RedirectResponse
    {
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        
        $postData = $this->request->getPost();
        
        // Penentuan Unit: Jika bukan global, paksa ke unit user.
        $unitTarget = !$isGlobal ? strtoupper($sessionUnit) : ($postData['kode_jenjang'] ?? 'GLOBAL');

        $payload = [
            'kode_jenjang'    => $unitTarget,
            'tahun_ajaran_id' => $postData['tahun_ajaran_id'] ?? ($this->tahunAjaranAktif['id'] ?? null),
            'title'           => $postData['title'] ?? null, 
            'start'           => $postData['start'] ?? null,
            'end'             => !empty($postData['end']) ? $postData['end'] : $postData['start'],
            'keterangan'      => $postData['keterangan'] ?? null,
            'color'           => $postData['color'] ?? '#4e73df',
        ];
        
        if (!$this->kalenderPendidikanModel->insert($payload)) {
            return redirect()->back()->withInput()->with('errors', $this->kalenderPendidikanModel->errors());
        }

        return redirect()->to(base_url('app/akademik/kalender'))->with('success', 'Acara berhasil ditambahkan.');
    }

    /**
     * Form Edit Acara
     */
    public function edit($id = null): string|RedirectResponse
    {
        $kalender    = $this->kalenderPendidikanModel->find($id);
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal    = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        if (!$kalender) {
            return redirect()->to(base_url('app/akademik/kalender'))->with('error', 'Data tidak ditemukan.');
        }

        // Proteksi Scope Edit
        if (!$isGlobal && $kalender['kode_jenjang'] !== strtoupper($sessionUnit)) {
            return redirect()->to(base_url('app/akademik/kalender'))->with('error', 'Akses Ditolak: Anda tidak berhak mengubah acara unit lain.');
        }
        
        $data = [
            'title'              => 'Edit Acara',
            'kalender'           => $kalender,
            'tahun_ajaran_aktif' => $this->tahunAjaranAktif,
            'list_jenjang'       => model('App\Models\JenjangModel')->where('status', 'Aktif')->orderBy('urutan', 'ASC')->findAll(),
            'is_global'          => $isGlobal,
            'session_unit'       => $sessionUnit
        ];

        return view('akademik/kalender/form', $this->loadViewData($data));
    }

    /**
     * Update Acara
     */
    public function update($id = null): RedirectResponse
    {
        $existing    = $this->kalenderPendidikanModel->find($id);
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal    = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        if (!$existing) {
            return redirect()->to(base_url('app/akademik/kalender'))->with('error', 'Data tidak valid.');
        }

        if (!$isGlobal && $existing['kode_jenjang'] !== strtoupper($sessionUnit)) {
            return redirect()->to(base_url('app/akademik/kalender'))->with('error', 'Akses Ditolak.');
        }
        
        $postData = $this->request->getPost();
        $unitTarget = !$isGlobal ? strtoupper($sessionUnit) : ($postData['kode_jenjang'] ?? 'GLOBAL');

        $payload = [
            'kode_jenjang'    => $unitTarget,
            'tahun_ajaran_id' => $postData['tahun_ajaran_id'] ?? $existing['tahun_ajaran_id'],
            'title'           => $postData['title'] ?? null,
            'start'           => $postData['start'] ?? null,
            'end'             => !empty($postData['end']) ? $postData['end'] : $postData['start'],
            'keterangan'      => $postData['keterangan'] ?? null,
            'color'           => $postData['color'] ?? '#4e73df',
        ];

        if (!$this->kalenderPendidikanModel->update($id, $payload)) {
            return redirect()->back()->withInput()->with('errors', $this->kalenderPendidikanModel->errors());
        }

        return redirect()->to(base_url('app/akademik/kalender'))->with('success', 'Perubahan berhasil disimpan.');
    }

    /**
     * Hapus Acara
     */
    public function delete($id = null): RedirectResponse
    {
        $existing    = $this->kalenderPendidikanModel->find($id);
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal    = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        if (!$id || !$existing) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        if (!$isGlobal && $existing['kode_jenjang'] !== strtoupper($sessionUnit)) {
            return redirect()->back()->with('error', 'Akses Ditolak.');
        }

        $this->kalenderPendidikanModel->delete($id);
        return redirect()->to(base_url('app/akademik/kalender'))->with('success', 'Acara berhasil dihapus.');
    }
    
    /**
     * API untuk FullCalendar (JSON)
     */
    public function getEvents()
    {
        $startRange  = $this->request->getGet('start'); 
        $endRange    = $this->request->getGet('end');
        $tahunId     = $this->tahunAjaranAktif['id'] ?? null;
        
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal    = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        
        $builder = $this->kalenderPendidikanModel->select('id, title, start, end, color, keterangan, kode_jenjang');
        
        if ($tahunId) {
            $builder->where('tahun_ajaran_id', $tahunId);
        }

        // Proteksi API: Jangan tampilkan acara unit lain ke Admin Unit
        if (!$isGlobal) {
            $builder->where('kode_jenjang', strtoupper($sessionUnit));
        }

        if ($startRange && $endRange) {
            $builder->where('start >=', date('Y-m-d', strtotime($startRange)))
                    ->where('start <=', date('Y-m-d', strtotime($endRange)));
        }
        
        $events = $builder->findAll();
        
        $formattedEvents = array_map(function($e) {
            $endDate = !empty($e['end']) ? $e['end'] : $e['start'];
            // FullCalendar butuh end date eksklusif (+1 hari untuk all-day event)
            $finalEnd = Time::parse($endDate)->addDays(1)->toDateString();

            return [
                'id'          => $e['id'],
                'title'       => '[' . ($e['kode_jenjang'] ?? 'GLOBAL') . '] ' . $e['title'], 
                'start'       => $e['start'],
                'end'         => $finalEnd,
                'color'       => $e['color'] ?? '#4e73df',
                'description' => $e['keterangan'] ?? '',
                'allDay'      => true
            ];
        }, $events);
        
        return $this->response->setJSON($formattedEvents);
    }
}