<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseAkademikController;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller Nilai
 * Mengelola input nilai akademik siswa (Tugas, UTS, UAS, Absensi).
 * REFAKTOR: Menggunakan standar Role Scoping 'GLOBAL' (Anti-Hardcode & Anti-Bocor).
 */
class Nilai extends BaseAkademikController
{
    /**
     * Halaman Utama: Daftar Nilai dengan Filter Unit Dinamis.
     */
    public function index(): string
    {
        // 1. Identifikasi Otoritas (Sesuai standar HakAksesModel)
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        // 2. Tangkap Filter dari Request GET
        $unitParam = $this->request->getVar('unit');
        $keyword   = $this->request->getVar('keyword');
        $tahunAjaranAktif = $this->tahunAjaranAktif;

        // 3. Penentuan Scope Jenjang (Otoritas Unit)
        if (!$isGlobal) {
            // Admin Unit: Paksa filter ke unit sendiri (Anti-Bocor)
            $kodeJenjang = strtoupper($sessionUnit);
        } else {
            // Superadmin: Bebas filter, default ke NULL (Tampilkan Semua)
            $kodeJenjang = (!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null;
        }

        // 4. Siapkan Data Kelas untuk Dropdown Filter di View
        $kelasBuilder = $this->kelasModel->where('id_tahun_ajaran', $tahunAjaranAktif['id'] ?? 0);
        if ($kodeJenjang) {
            $kelasBuilder->where('kode_jenjang', $kodeJenjang);
        }
        $listKelas = $kelasBuilder->orderBy('nama_kelas', 'ASC')->findAll();

        // 5. Query Data Nilai via Model (Mendukung Paginasi)
        // Jika $kodeJenjang null, maka model menampilkan semua unit (Global Access)
        $listNilai = $this->nilaiModel->getNilaiPaginated($kodeJenjang ?? 'Global', 20, $keyword);

        // 6. Siapkan Data untuk View
        $data = $this->loadViewData([
            'title'              => 'Manajemen Nilai Siswa',
            'current_module'     => 'akademik',
            'kelas'              => $listKelas,
            'mapel'              => $this->mapelModel->findAll(),
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
            'list_nilai'         => $listNilai,
            'pager'              => $this->nilaiModel->pager,
            'current_unit'       => $unitParam ?? ($kodeJenjang ?? 'Global'),
            'session_unit'       => $sessionUnit, // Penting untuk lock UI di View
            'keyword'            => $keyword
        ]);

        return view('akademik/nilai/index', $data);
    }

    /**
     * Halaman Input Nilai: Form per Kelas, Mapel, dan Semester.
     */
    public function kelola()
    {
        $id_kelas = $this->request->getVar('id_kelas');
        $id_mapel = $this->request->getVar('id_mata_pelajaran');
        $semester = $this->request->getVar('semester');
        $tahunAjaranAktif = $this->tahunAjaranAktif;

        if (!$id_kelas || !$id_mapel || !$semester) {
            return redirect()->to(site_url('app/akademik/nilai'))->with('error', 'Parameter input tidak lengkap.');
        }

        // --- PROTEKSI KEAMANAN AKSES (ANTI-MANIPULASI URL) ---
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        $detailKelas = $this->kelasModel->find($id_kelas);

        if (!$isGlobal) {
            if (!$detailKelas || $detailKelas['kode_jenjang'] !== strtoupper($sessionUnit)) {
                return redirect()->to(site_url('app/akademik/nilai'))->with('error', 'Akses Ditolak: Anda tidak berhak mengelola nilai unit ini.');
            }
        }

        // Ambil data pendukung
        $mapelInfo = $this->mapelModel->find($id_mapel);
        // Fallback ID Guru: Idealnya diambil dari relasi jadwal atau session pegawai
        $idGuruAktif = session()->get('id_pegawai') ?? 1; 
        $guruInfo = $this->guruModel->select('id, nama_lengkap')->find($idGuruAktif);

        if (!$mapelInfo) {
            return redirect()->to(site_url('app/akademik/nilai'))->with('error', 'Data Mata Pelajaran tidak ditemukan.');
        }

        // Ambil daftar siswa aktif di kelas tersebut
        $siswaDiKelas = $this->siswaModel->getSiswaByKelas($id_kelas);
        
        // Ambil nilai yang sudah tersimpan sebelumnya
        $nilaiTersimpan = $this->nilaiModel->getNilaiByKelasAndMapel($id_kelas, $id_mapel, $semester);

        // Ambil skor kehadiran otomatis dari modul absensi
        $calculatedAbsensi = $this->absensiSiswaModel->getAttendanceScoreByClassMapelSemester($id_kelas, $id_mapel, $semester);

        // Gabungkan data (Merge)
        $mergedNilai = [];
        foreach ($siswaDiKelas as $siswa) {
            $siswaId = $siswa['id'];
            $stored  = $nilaiTersimpan[$siswaId] ?? [];
            
            // Jika nilai absensi belum pernah diinput manual, gunakan hitungan otomatis
            if (!isset($stored['nilai_absensi']) || $stored['nilai_absensi'] === null) {
                $stored['nilai_absensi'] = round($calculatedAbsensi[$siswaId] ?? 0);
            }
            $mergedNilai[$siswaId] = $stored;
        }

        $data = $this->loadViewData([
            'title'              => 'Input Nilai: ' . ($detailKelas['nama_kelas'] ?? ''),
            'current_module'     => 'akademik',
            'siswa_di_kelas'     => $siswaDiKelas,
            'kelas_info'         => $detailKelas,
            'mapel_info'         => $mapelInfo,
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
            'nilai_tersimpan'    => $mergedNilai,
            'id_kelas'           => $id_kelas,
            'id_mapel'           => $id_mapel,
            'semester'           => $semester,
            'guru_info'          => $guruInfo,
        ]);

        return view('akademik/nilai/kelola', $data);
    }

    /**
     * Proses Simpan/Update Nilai Massal.
     */
    public function simpan(): RedirectResponse
    {
        $nilai_data      = $this->request->getPost('nilai');
        $id_kelas        = $this->request->getPost('id_kelas');
        $id_mapel        = $this->request->getPost('id_mapel');
        $id_tahun_ajaran = $this->request->getPost('id_tahun_ajaran');
        $semester        = $this->request->getPost('semester');
        $idGuruAktif     = session()->get('id_pegawai') ?? 1;

        if (empty($nilai_data) || !$id_kelas || !$id_mapel) {
            return redirect()->back()->with('error', 'Data pengiriman tidak valid.');
        }

        $kelasInfo   = $this->kelasModel->find($id_kelas);
        $kodeJenjang = $kelasInfo['kode_jenjang'] ?? null;

        if (!$kodeJenjang) {
            return redirect()->back()->with('error', 'Konfigurasi unit pada kelas ini bermasalah.');
        }

        // Ambil Bobot Nilai dari Master Mapel
        $bobot = $this->mapelModel->getBobotByMapelId($id_mapel);
        $bTugas   = (float)($bobot['bobot_tugas'] ?? 0.25);
        $bUts     = (float)($bobot['bobot_uts'] ?? 0.35);
        $bUas     = (float)($bobot['bobot_uas'] ?? 0.40);
        $bAbsensi = (float)($bobot['bobot_absensi'] ?? 0);

        $this->nilaiModel->transStart();

        try {
            foreach ($nilai_data as $id_siswa => $nilai) {
                // Sanitasi input (Range 0-100)
                $tugas   = min(100, max(0, (float)($nilai['tugas'] ?? 0)));
                $uts     = min(100, max(0, (float)($nilai['uts'] ?? 0)));
                $uas     = min(100, max(0, (float)($nilai['uas'] ?? 0)));
                $absensi = min(100, max(0, (float)($nilai['absensi'] ?? 0)));

                // Hitung Nilai Akhir berdasarkan bobot
                $nilai_akhir = ($tugas * $bTugas) + ($uts * $bUts) + ($uas * $bUas) + ($absensi * $bAbsensi);

                $row_data = [
                    'id_tahun_ajaran'   => $id_tahun_ajaran,
                    'id_kelas'          => $id_kelas,
                    'id_siswa'          => $id_siswa,
                    'id_mata_pelajaran' => $id_mapel,
                    'id_guru'           => $idGuruAktif,
                    'semester'          => $semester,
                    'kode_jenjang'      => $kodeJenjang,
                    'nilai_tugas'       => $tugas,
                    'nilai_uts'         => $uts,
                    'nilai_uas'         => $uas,
                    'nilai_absensi'     => $absensi,
                    'nilai_akhir'       => round($nilai_akhir, 2),
                    'updated_at'        => date('Y-m-d H:i:s')
                ];

                // Upsert data menggunakan method cerdas di Model
                if (!$this->nilaiModel->saveNilaiLengkap($row_data)) {
                    throw new \RuntimeException("Gagal menyimpan nilai untuk siswa ID: $id_siswa");
                }
            }

            $this->nilaiModel->transComplete();

            if ($this->nilaiModel->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Sistem gagal melakukan commit data ke database.');
            }

            return redirect()->to(site_url('app/akademik/nilai'))->with('success', 'Data nilai berhasil diperbarui.');

        } catch (\Exception $e) {
            $this->nilaiModel->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}