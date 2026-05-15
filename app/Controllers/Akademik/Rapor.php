<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseAkademikController;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\RaportModel;
use App\Models\NilaiModel;
use App\Models\SettingsModel;

/**
 * Controller Rapor
 * Mengelola hasil akhir belajar siswa, catatan wali kelas, dan pencetakan rapor.
 * REFAKTOR: Menggunakan standar Role Scoping 'GLOBAL' (Anti-Hardcode & Anti-Bocor).
 */
class Rapor extends BaseAkademikController
{
    protected ?RaportModel $raportModel = null;
    protected ?NilaiModel $nilaiModel = null;
    protected ?SettingsModel $settingsModel = null;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        $this->raportModel   = new RaportModel();
        $this->nilaiModel    = new NilaiModel();
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Menampilkan daftar siswa untuk pengelolaan rapor dengan Filter Unit Dinamis.
     */
    public function index(): string
    {
        // 1. Identifikasi Otoritas Berdasarkan Standar HakAksesModel
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        // 2. Tangkap Filter dari Request GET
        $unitParam = $this->request->getGet('unit');
        $keyword   = $this->request->getGet('keyword');
        $tahunAjaranAktif = $this->tahunAjaranAktif;
        $tahunId = $tahunAjaranAktif['id'] ?? null;

        // 3. Penentuan Scope Jenjang (Otoritas Unit)
        if (!$isGlobal) {
            // Admin Unit: Kunci ke unit sendiri
            $kodeJenjang = strtoupper($sessionUnit);
        } else {
            // Superadmin: Bebas filter, default ke NULL/Global
            $kodeJenjang = (!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null;
        }

        // 4. Query Data Rapor via Model (Mendukung Paginasi)
        $listRaport = $this->raportModel->getRaportPaginated($kodeJenjang ?? 'Global', 20, $keyword, $tahunId);

        // 5. Siapkan Data View
        $data = $this->loadViewData([
            'title'              => 'Manajemen Rapor Siswa',
            'current_module'     => 'akademik',
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
            'list_raport'        => $listRaport,
            'pager'              => $this->raportModel->pager,
            'current_unit'       => $unitParam ?? ($kodeJenjang ?? 'Global'),
            'session_unit'       => $sessionUnit, // Digunakan View untuk lock UI
            'keyword'            => $keyword
        ]);

        return view('akademik/rapor/index', $data);
    }

    /**
     * Menampilkan detail rapor siswa (Nilai + Catatan) dengan proteksi unit.
     */
    public function view($id_siswa): string
    {
        try {
            $data = $this->getRaportData($id_siswa);
        } catch (PageNotFoundException $e) {
            throw $e;
        }

        $data['title'] = 'Detail Rapor Siswa';
        return view('akademik/rapor/view_rapor', $this->loadViewData($data));
    }

    /**
     * Menampilkan halaman cetak Rapor (Print Friendly HTML).
     */
    public function cetak($id_siswa)
    {
        try {
            $data = $this->getRaportData($id_siswa);
        } catch (PageNotFoundException $e) {
            return redirect()->to(base_url('app/akademik/rapor'))->with('error', $e->getMessage());
        }

        $data['title'] = 'Cetak Rapor - ' . $data['siswa']['nama_siswa'];
        return view('akademik/rapor/cetak', $this->loadViewData($data));
    }

    /**
     * Ekspor Rapor ke PDF dengan Dompdf.
     */
    public function exportPdf($id_siswa)
    {
        try {
            $data = $this->getRaportData($id_siswa);
        } catch (PageNotFoundException $e) {
            return redirect()->to(base_url('app/akademik/rapor'))->with('error', $e->getMessage());
        }

        $data['title'] = 'Rapor - ' . $data['siswa']['nama_siswa'];
        $html = view('akademik/rapor/cetak_pdf', $data);

        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("Rapor_" . $data['siswa']['nis'] . "_" . $data['semester'] . ".pdf", ["Attachment" => 0]);
            exit;
        } else {
            // Fallback jika dompdf belum terinstall
            return $this->response->setBody($html . '<script>window.print();</script>');
        }
    }
    
    /**
     * Helper privat untuk mengambil data rapor lengkap + PROTEKSI UNIT.
     * Mencegah Admin Unit mengakses data siswa milik jenjang lain.
     */
    private function getRaportData($id_siswa): array
    {
        $semester = $this->request->getGet('semester') ?? 'Ganjil';
        $tahunAjaranAktif = $this->tahunAjaranAktif;

        if (!$tahunAjaranAktif) {
            throw new PageNotFoundException("Tahun Ajaran Aktif tidak ditemukan.");
        }

        // 1. Ambil Data Dasar Siswa & Enrollment
        $db = \Config\Database::connect();
        $builder = $db->table('siswa_enrollment');
        
        $enrollment = $builder->select('
                siswa_enrollment.id as id_enrollment,
                siswa_enrollment.id_kelas,
                k.nama_kelas,
                k.kode_jenjang,
                g.nama_lengkap as nama_wali,
                s.nama_lengkap as nama_siswa,
                s.nis,
                s.nisn,
                s.id as id_siswa
            ')
            ->join('siswa as s', 's.id = siswa_enrollment.id_siswa')
            ->join('kelas as k', 'k.id = siswa_enrollment.id_kelas')
            ->join('pegawai as g', 'g.id = k.id_wali_kelas', 'left')
            ->where('siswa_enrollment.id_siswa', $id_siswa)
            ->where('siswa_enrollment.id_tahun_ajaran', $tahunAjaranAktif['id'])
            ->orderBy('siswa_enrollment.id', 'DESC')
            ->get()->getRowArray();

        if (!$enrollment) {
             throw new PageNotFoundException("Data siswa tidak terdaftar pada periode akademik aktif.");
        }

        // --- PROTEKSI SCOPE (PENTING!) ---
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        if (!$isGlobal) {
            if (strtoupper($enrollment['kode_jenjang']) !== strtoupper($sessionUnit)) {
                throw new PageNotFoundException("Akses Ditolak: Anda dilarang mengakses data rapor dari unit lain.");
            }
        }

        // 2. Ambil Leger Nilai & Data Rapor
        $nilai = $this->nilaiModel->getLegerNilaiSiswa($id_siswa, $semester);

        $raport = $this->raportModel->where('id_enrollment', $enrollment['id_enrollment'])
                                    ->where('semester', $semester)
                                    ->first();

        if (!$raport) {
            $raport = [
                'id' => null,
                'total_sakit' => 0, 'total_izin' => 0, 'total_alpa' => 0,
                'catatan_wali_kelas' => '',
                'status_raport' => 'Draft',
                'tinggi_badan' => null,
                'berat_badan' => null,
                'predikat_spiritual' => '',
                'predikat_sosial' => '',
                'status_kenaikan' => null
            ];
        }

        // 3. Ambil Identitas Sekolah (Kop Surat) sesuai Unit Siswa
        $unit = $enrollment['kode_jenjang'] ?? 'Global';
        $settingsData = $this->settingsModel->getSettingsAsArray($unit);
        
        // Fallback jika setting unit spesifik masih kosong
        if (empty($settingsData) || empty($settingsData['nama_sekolah'])) {
            $settingsData = $this->settingsModel->getSettingsAsArray('Global');
        }

        return [
            'siswa'        => $enrollment,
            'nilai_list'   => $nilai,
            'raport'       => $raport,
            'semester'     => $semester,
            'tahun_ajaran' => $tahunAjaranAktif,
            'sekolah'      => $settingsData,
            'session_unit' => $sessionUnit
        ];
    }
    
    /**
     * Menyimpan/Update data rapor dengan validasi unit.
     */
    public function simpan($id_siswa)
    {
         $semester      = $this->request->getPost('semester');
         $id_enrollment = $this->request->getPost('id_enrollment');
         
         if (!$this->validate([
             'sakit' => 'required|integer',
             'izin'  => 'required|integer',
             'alpa'  => 'required|integer',
         ])) {
             return redirect()->back()->withInput()->with('error', 'Input absensi harus berupa angka.');
         }

         // Validasi Kepemilikan Data
         $db = \Config\Database::connect();
         $enrollInfo = $db->table('siswa_enrollment')
                          ->select('siswa_enrollment.id_kelas, siswa_enrollment.id_tahun_ajaran, k.kode_jenjang')
                          ->join('kelas k', 'k.id = siswa_enrollment.id_kelas')
                          ->where('siswa_enrollment.id', $id_enrollment)
                          ->get()->getRowArray();

         $sessionUnit = session()->get('kode_jenjang');
         $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

         if (!$isGlobal && strtoupper($enrollInfo['kode_jenjang']) !== strtoupper($sessionUnit)) {
             return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak berhak menyimpan data untuk unit ini.');
         }

         $data = [
             'id_enrollment'      => $id_enrollment,
             'semester'           => $semester,
             'total_sakit'        => $this->request->getPost('sakit'),
             'total_izin'         => $this->request->getPost('izin'),
             'total_alpa'         => $this->request->getPost('alpa'),
             'catatan_wali_kelas' => $this->request->getPost('catatan_wali'),
             'status_raport'      => $this->request->getPost('status_raport') ?? 'Draft',
             'tinggi_badan'       => $this->request->getPost('tinggi_badan'),
             'berat_badan'        => $this->request->getPost('berat_badan'),
             'predikat_spiritual' => $this->request->getPost('predikat_spiritual'),
             'deskripsi_spiritual'=> $this->request->getPost('deskripsi_spiritual'),
             'predikat_sosial'    => $this->request->getPost('predikat_sosial'),
             'deskripsi_sosial'   => $this->request->getPost('deskripsi_sosial'),
             'status_kenaikan'    => $this->request->getPost('status_kenaikan'),
             'updated_at'         => date('Y-m-d H:i:s')
         ];

         $existing = $this->raportModel->where('id_enrollment', $id_enrollment)
                                       ->where('semester', $semester)
                                       ->first();

         if ($existing) {
             $this->raportModel->update($existing['id'], $data);
         } else {
             $data['created_at'] = date('Y-m-d H:i:s');
             $this->raportModel->insert($data);
         }

         return redirect()->to(base_url("app/akademik/rapor/view/{$id_siswa}?semester={$semester}"))
                          ->with('message', 'Data rapor berhasil diperbarui.');
    }
}