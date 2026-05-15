<?php

namespace App\Controllers\Akademik;

use App\Controllers\BaseAkademikController; 
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\I18n\Time;

/**
 * Controller untuk mengelola Absensi Otomatis/Real-time (Absensi Harian).
 * Ini dapat digunakan untuk simulasi scan fingerprint atau absensi online.
 */
class AbsensiOtomatis extends BaseAkademikController
{
    /**
     * Menampilkan form input NIS/Kode QR untuk absensi real-time.
     */
    public function index(): string
    {
        // Ambil ID Tahun Ajaran Aktif
        $idTahunAjaranAktif = $this->tahunAjaranAktif['id'] ?? null;
        
        // Ambil data Tahun Ajaran Aktif untuk display
        $tahunAjaranInfo = 'Tidak Ada Tahun Ajaran Aktif';
        if ($idTahunAjaranAktif) {
            $ta = $this->tahunAjaranAktif;
            $tahunAjaranInfo = esc($ta['tahun_ajaran']) . ' / ' . esc($ta['semester']);
        }

        $data = $this->loadViewData([
            'title'          => 'Absensi Otomatis Harian',
            'current_module' => 'akademik',
            'tahunAjaranInfo' => $tahunAjaranInfo,
            'tanggalHariIni' => Time::now('Asia/Jakarta')->toLocalizedString('d MMMM yyyy'),
        ]);

        // Menggunakan view baru
        return view('akademik/absensi_otomatis/realtime_index', $data);
    }

    /**
     * Memproses absensi harian (hadir) berdasarkan NIS atau Kode.
     * Asumsi: Absensi ini hanya mencatat status HADIR (masuk sekolah).
     */
    public function proses(): string|RedirectResponse
    {
        $nis_atau_kode = $this->request->getPost('nis_atau_kode');
        $tanggal = date('Y-m-d'); // Tanggal hari ini
        $idTahunAjaranAktif = $this->tahunAjaranAktif['id'] ?? null;

        if (!$idTahunAjaranAktif) {
            session()->setFlashdata('error', 'Gagal: Tidak ada Tahun Ajaran Aktif yang disetel.');
            return redirect()->back();
        }

        // 1. Cari Siswa berdasarkan NIS (Ditingkatkan untuk fleksibilitas padding)
        // nis_clean digunakan jika NIS di DB tersimpan sebagai integer atau tanpa padding ('1')
        $nis_clean = (string) (int) $nis_atau_kode; 
        
        $siswa = $this->siswaModel
                     ->groupStart()
                         ->where('nis', $nis_atau_kode) // Coba cari string penuh ('00001')
                         ->orWhere('nis', $nis_clean)   // Coba cari nilai int non-padded ('1')
                     ->groupEnd()
                     ->first();
        
        // --- LOGIC FALLBACK UNTUK NIS BER-PREFIX ---
        if (!$siswa) {
            // Coba cari NIS yang mungkin memiliki prefix 'NSIS' jika input hanya angka
            $nis_prefix_search = 'NSIS' . str_pad($nis_clean, 4, '0', STR_PAD_LEFT);
            $siswa = $this->siswaModel->where('nis', $nis_prefix_search)->first();
            
            if (!$siswa) {
                session()->setFlashdata('error', 'NIS/Kode tidak ditemukan.');
                return redirect()->back()->withInput();
            }
        }
        // ------------------------------------------
        
        // FIX KRUSIAL: Konversi objek yang dikembalikan oleh Model menjadi array
        $siswa = (array) $siswa; 
        
        $id_siswa = $siswa['id'];
        $nama_siswa = $siswa['nama_lengkap'];

        // 2. Cek apakah siswa terdaftar (enroll) di tahun ajaran aktif
        // Menggunakan tabel siswa_enrollment (alias sa)
        $enrollment = $this->siswaModel
            ->join('siswa_enrollment sa', 'sa.id_siswa = siswa.id')
            ->where('siswa.id', $id_siswa)
            ->where('sa.id_tahun_ajaran', $idTahunAjaranAktif)
            ->where('sa.status_akademik', 'Aktif')
            ->first();

        if (!$enrollment) {
            session()->setFlashdata('error', esc($nama_siswa) . ' tidak terdaftar sebagai siswa aktif di tahun ajaran ini.');
            return redirect()->back()->withInput();
        }
        
        // 3. Cek apakah absensi harian (default ID Jadwal 0) sudah tercatat hari ini
        // Kita menggunakan ID Jadwal = 0 sebagai penanda absensi harian/fingerprint.
        $absensiHarianId = 0; 
        
        $absensi_tersimpan = $this->absensiSiswaModel
            ->where('id_siswa', $id_siswa)
            ->where('tanggal', $tanggal)
            ->where('id_jadwal', $absensiHarianId)
            ->first();
        
        $action = 'tambah';
        $message = '';
        
        if ($absensi_tersimpan) {
            // Jika sudah ada, ini adalah percobaan absensi kedua (atau update)
            $message = 'Absensi ulang berhasil dicatat.';
            $action = 'update';
        } else {
            // Belum ada, cek apakah sudah melewati jam masuk sekolah (opsional, untuk notifikasi terlambat)
            // Untuk saat ini, kita anggap hanya mencatat kehadiran
            $message = 'Absensi masuk berhasil dicatat.';
        }

        // 4. Data yang akan disimpan
        $dataToSave = [
            'id_siswa'   => $id_siswa,
            'id_jadwal'  => $absensiHarianId, 
            'tanggal'    => $tanggal,
            'status'     => 'hadir', // Absensi otomatis selalu hadir
            'keterangan' => 'Absensi Otomatis/Mesin',
            // Update created_at / updated_at akan dihandle oleh Model
        ];
        
        // 5. Simpan/Update data
        if ($action === 'update') {
            $success = $this->absensiSiswaModel->update($absensi_tersimpan['id'], $dataToSave);
        } else {
            $success = $this->absensiSiswaModel->save($dataToSave);
        }
        
        // 6. Siapkan data untuk View Hasil
        $data = $this->loadViewData([
            'title'          => 'Hasil Absensi Otomatis',
            'current_module' => 'akademik',
            'success'        => $success !== false,
            'message'        => $success !== false ? $message : 'Gagal mencatat absensi karena kesalahan sistem.',
            'siswa'          => $siswa,
            'tanggal'        => Time::parse($tanggal)->toLocalizedString('d MMMM yyyy'),
            'waktuAbsen'     => Time::now('Asia/Jakarta')->toLocalizedString('HH:mm:ss'),
        ]);

        // Menggunakan view hasil
        return view('akademik/absensi_otomatis/realtime_result', $data);
    }
}