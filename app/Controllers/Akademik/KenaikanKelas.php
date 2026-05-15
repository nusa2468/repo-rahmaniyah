<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseAkademikController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;
use Throwable;

/**
 * Controller KenaikanKelas
 * Mengelola proses transisi tahunan (Kenaikan Kelas, Tinggal Kelas, dan Kelulusan).
 * REFAKTOR: Menggunakan standar Role Scoping 'GLOBAL' (Anti-Hardcode & Anti-Bocor).
 */
class KenaikanKelas extends BaseAkademikController
{
    /**
     * Halaman Dashboard Kenaikan Kelas.
     */
    public function index(): string
    {
        // 1. Identifikasi Otoritas Berdasarkan Standar HakAksesModel
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');

        // 2. Tangkap Filter dari Request GET
        $unitParam = $this->request->getVar('unit');
        $keyword   = $this->request->getVar('keyword');
        $tahunAjaranAktif = $this->tahunAjaranAktif;
        $idTahunAktif = $tahunAjaranAktif['id'] ?? null;

        // 3. Penentuan Scope Jenjang (Otoritas Unit)
        if (!$isGlobal) {
            $kodeJenjang = strtoupper($sessionUnit);
        } else {
            $kodeJenjang = (!empty($unitParam) && strtoupper($unitParam) !== 'GLOBAL') ? strtoupper($unitParam) : null;
        }

        // 4. Query Riwayat Kenaikan
        // PERBAIKAN: Memanggil paginate() agar hasil query berupa ARRAY (Menghindari TypeError count)
        $historyQuery = $this->kenaikanKelasModel->getKenaikanPaginated($kodeJenjang ?? 'Global', 20, $keyword, $idTahunAktif);
        $listHistory = $historyQuery->paginate(20, 'default');

        // 5. Siapkan Daftar Kelas untuk Proses Baru (Hanya yang sesuai Scope)
        $kelasBuilder = $this->kelasModel->where('id_tahun_ajaran', $idTahunAktif);
        if ($kodeJenjang) {
            $kelasBuilder->where('kode_jenjang', $kodeJenjang);
        }
        $kelasList = $kelasBuilder->orderBy('nama_kelas', 'ASC')->findAll();

        $data = $this->loadViewData([
            'title'              => 'Manajemen Kenaikan Kelas',
            'current_module'     => 'akademik',
            'tahun_ajaran_aktif' => $tahunAjaranAktif,
            'list_history'       => $listHistory,
            'pager'              => $this->kenaikanKelasModel->pager,
            'kelas_list'         => $kelasList,
            'current_unit'       => $unitParam ?? ($kodeJenjang ?? 'Global'),
            'session_unit'       => $sessionUnit,
            'keyword'            => $keyword
        ]);

        return view('akademik/kenaikan_kelas/index', $data);
    }

    /**
     * Halaman Kelola Keputusan Kenaikan per Kelas.
     */
    public function kelola()
    {
        $id_kelas_lama = $this->request->getVar('id_kelas');
        $tahunAjaranAktif = $this->tahunAjaranAktif;
        $id_ta_lama = $tahunAjaranAktif['id'] ?? null;

        if (!$id_kelas_lama || !$id_ta_lama) {
            return redirect()->to(base_url('app/akademik/kenaikan_kelas'))->with('error', 'Parameter tidak lengkap.');
        }

        // --- PROTEKSI KEAMANAN AKSES (ANTI-BOCOR) ---
        $sessionUnit = session()->get('kode_jenjang');
        $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
        $kelasLama = $this->kelasModel->find($id_kelas_lama);

        if (!$isGlobal) {
            if (!$kelasLama || strtoupper($kelasLama['kode_jenjang']) !== strtoupper($sessionUnit)) {
                return redirect()->to(base_url('app/akademik/kenaikan_kelas'))
                                 ->with('error', 'Akses Ditolak: Unit dilarang mengelola data di luar otoritasnya.');
            }
        }

        // Tentukan Logika Tingkat & Tahun Depan
        $tingkatBaru = (int)$kelasLama['tingkat'] + 1;
        $maxTingkat = match (strtoupper($kelasLama['kode_jenjang'])) {
            'SD' => 6,
            'SMP' => 9,
            default => 12
        };
        $isKelasAkhir = ((int)$kelasLama['tingkat'] >= $maxTingkat);

        // Cari Tahun Ajaran Berikutnya (Target Kenaikan)
        $taBaru = $this->tahunAjaranModel->where('id >', $id_ta_lama)->orderBy('id', 'ASC')->first();
        $id_ta_baru = $taBaru['id'] ?? null;

        // Ambil Daftar Siswa di Kelas tersebut via Enrollment Aktif
        $db = \Config\Database::connect();
        $siswaDiKelas = $db->table('siswa_enrollment se')
            ->select('se.id as enrollment_id, s.id as siswa_id, s.nama_lengkap as nama_siswa, s.nis')
            ->join('siswa s', 's.id = se.id_siswa')
            ->where('se.id_kelas', $id_kelas_lama)
            ->where('se.id_tahun_ajaran', $id_ta_lama)
            ->where('se.status_akademik', 'Aktif')
            ->get()->getResultArray();

        // Cari Opsi Kelas Tujuan (Hanya di unit yang sama pada TA Baru)
        $kelasTujuanList = [];
        if (!$isKelasAkhir && $id_ta_baru) {
            $kelasTujuanList = $this->kelasModel->where('tingkat', $tingkatBaru)
                                                ->where('kode_jenjang', $kelasLama['kode_jenjang'])
                                                ->where('id_tahun_ajaran', $id_ta_baru)
                                                ->orderBy('nama_kelas', 'ASC')
                                                ->findAll();
        }

        $data = $this->loadViewData([
            'title'                => 'Keputusan Kenaikan: ' . ($kelasLama['nama_kelas'] ?? 'N/A'),
            'current_module'       => 'akademik',
            'kelas_lama'           => $kelasLama,
            'is_kelas_akhir'       => $isKelasAkhir,
            'siswa_list'           => $siswaDiKelas,
            'kelas_tujuan_list'    => $kelasTujuanList,
            'id_tahun_ajaran_lama' => $id_ta_lama, 
            'id_tahun_ajaran_baru' => $id_ta_baru,
            'ta_lama'              => $tahunAjaranAktif,
            'ta_baru'              => $taBaru
        ]);

        return view('akademik/kenaikan_kelas/kelola', $data);
    }

    /**
     * Proses Simpan Keputusan Kenaikan Massal.
     */
    public function simpan(): RedirectResponse
    {
        $data_keputusan = $this->request->getPost('keputusan');
        $id_kelas_lama  = $this->request->getPost('id_kelas_lama');
        $id_ta_lama     = $this->request->getPost('id_tahun_ajaran_lama');
        $id_ta_baru     = $this->request->getPost('id_tahun_ajaran_baru');
        $tgl_keputusan  = $this->request->getPost('tanggal_keputusan') ?? Time::now()->toDateString();
        
        if (empty($data_keputusan)) {
            return redirect()->back()->with('error', 'Tidak ada data siswa yang diproses.');
        }

        $db = \Config\Database::connect();
        $id_operator = session()->get('id_pegawai') ?? 1;

        // Memulai Transaksi Database
        $db->transStart();

        try {
            foreach ($data_keputusan as $id_siswa => $kep) {
                $status = $kep['status'];
                $id_enrollment_lama = $kep['id_enrollment_lama'];
                
                $id_enrollment_baru = null;

                // 1. LOGIKA MUTASI DATA (Daftarkan Siswa ke Tahun Ajaran Baru jika Naik/Tinggal)
                if (($status === 'Naik' || $status === 'Tinggal') && $id_ta_baru) {
                    // Jika Naik, gunakan kelas baru. Jika Tinggal, gunakan kelas lama.
                    $targetKelas = ($status === 'Naik') ? ($kep['id_kelas_baru'] ?? null) : $id_kelas_lama;
                    
                    if ($targetKelas) {
                        // Cek apakah sudah ada enrollment di tahun baru (untuk re-proses/update)
                        $existingEnroll = $db->table('siswa_enrollment')
                            ->where(['id_siswa' => $id_siswa, 'id_tahun_ajaran' => $id_ta_baru])
                            ->get()->getRowArray();

                        $enrollData = [
                            'id_siswa'        => $id_siswa,
                            'id_kelas'        => $targetKelas,
                            'id_tahun_ajaran' => $id_ta_baru,
                            'semester'        => 'Ganjil',
                            'status_akademik' => 'Aktif',
                            'updated_at'      => Time::now()->toDateTimeString()
                        ];

                        if ($existingEnroll) {
                            $db->table('siswa_enrollment')->where('id', $existingEnroll['id'])->update($enrollData);
                            $id_enrollment_baru = $existingEnroll['id'];
                        } else {
                            $enrollData['created_at'] = Time::now()->toDateTimeString();
                            $db->table('siswa_enrollment')->insert($enrollData);
                            $id_enrollment_baru = $db->insertID();
                        }
                    }

                } elseif (in_array($status, ['Lulus', 'Mutasi', 'Dikeluarkan'])) {
                    // Update status di Master Data Siswa
                    $db->table('siswa')->where('id', $id_siswa)->update(['status' => strtolower($status)]);
                }

                // 2. SIMPAN RIWAYAT KE TABEL kenaikan_kelas (UPSERT)
                $saveHistory = [
                    'id_siswa'           => $id_siswa,
                    'id_enrollment_lama' => $id_enrollment_lama,
                    'id_enrollment_baru' => $id_enrollment_baru,
                    'status_kenaikan'    => $status,
                    'tanggal_keputusan'  => $tgl_keputusan,
                    'catatan_guru'       => $kep['catatan'] ?? null,
                    'id_operator'        => $id_operator,
                    'updated_at'         => Time::now()->toDateTimeString()
                ];

                // Cek apakah history dengan enrollment lama ini sudah ada?
                $existingHistory = $this->kenaikanKelasModel->where('id_enrollment_lama', $id_enrollment_lama)->first();

                if ($existingHistory) {
                    $this->kenaikanKelasModel->update($existingHistory['id'], $saveHistory);
                } else {
                    $saveHistory['created_at'] = Time::now()->toDateTimeString();
                    $this->kenaikanKelasModel->insert($saveHistory);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException("Gagal melakukan commit transaksi ke database.");
            }

            return redirect()->to(base_url('app/akademik/kenaikan_kelas'))->with('success', 'Keputusan transisi akademik berhasil diproses.');

        } catch (Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Kesalahan Sistem: ' . $e->getMessage());
        }
    }
}