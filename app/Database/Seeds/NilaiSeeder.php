<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * NilaiSeeder
 * Mengisi data nilai siswa (PH, UTS, UAS) secara dummy.
 * UPDATED: Menggunakan 'SHOW COLUMNS' langsung untuk bypass Cache Metadata.
 */
class NilaiSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // PENTING: Reset cache metadata agar membaca struktur tabel terbaru
        $db->resetDataCache(); 

        $faker = \Faker\Factory::create('id_ID');
        
        echo "\n>>> Menjalankan Seeder Nilai Siswa...\n";
        
        // 1. Cek Eksistensi Tabel (Direct Query)
        $tableExists = $db->query("SHOW TABLES LIKE 'nilai_siswa'")->getRow();
        if (!$tableExists) {
            echo " [SKIP] Tabel 'nilai_siswa' belum dibuat. Jalankan migrate:refresh dulu.\n";
            return;
        }

        // 2. Matikan FK Check untuk truncate aman
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        $db->table('nilai_siswa')->truncate();
        $db->query('SET FOREIGN_KEY_CHECKS=1;');
        
        // 3. Ambil Data Master (Enrollment)
        if (!$db->tableExists('siswa_enrollment')) {
            echo " [SKIP] Tabel 'siswa_enrollment' tidak ditemukan.\n";
            return;
        }

        // Pastikan kita hanya mengambil kolom yang ada di enrollment
        // Cek kolom enrollment dulu untuk menghindari error select
        $enrollCols = $this->getRealColumns($db, 'siswa_enrollment');
        $selectCols = ['id', 'id_siswa', 'id_kelas'];
        
        if (in_array('id_tahun_ajaran', $enrollCols)) $selectCols[] = 'id_tahun_ajaran';
        if (in_array('semester', $enrollCols)) $selectCols[] = 'semester';
        
        $enrollments = $db->table('siswa_enrollment')
                          ->select(implode(',', $selectCols))
                          ->limit(50) 
                          ->get()
                          ->getResultArray();

        $mapelList = $db->table('mata_pelajaran')->select('id')->get()->getResultArray();
        
        $guruList = $db->table('pegawai')
                        ->select('id')
                        ->where('jenis_pegawai', 'guru')
                        ->where('status_aktif', 'aktif')
                        ->get()
                        ->getResultArray();

        if (empty($enrollments) || empty($mapelList) || empty($guruList)) {
            echo " [SKIP] Data Enrollment, Mapel, atau Guru tidak lengkap.\n";
            return;
        }

        // --- DETEKSI KOLOM TARGET (BYPASS CACHE) ---
        // Menggunakan fungsi helper custom di bawah
        $cols = $this->getRealColumns($db, 'nilai_siswa');
        
        $hasCol = fn($c) => in_array($c, $cols);

        // Cek Kolom Kunci
        $hasIdKelas         = $hasCol('id_kelas');
        $hasIdSiswa         = $hasCol('id_siswa');
        $hasIdTahunAjaran   = $hasCol('id_tahun_ajaran');
        $hasIdEnrollment    = $hasCol('id_enrollment');
        $hasKodeJenjang     = $hasCol('kode_jenjang');
        
        // Cek Kolom Lain
        $hasKeterangan      = $hasCol('keterangan');
        $hasNilaiHuruf      = $hasCol('nilai_huruf');
        
        // Cek nama kolom kategori
        $colKategori = $hasCol('jenis_nilai') ? 'jenis_nilai' : 'kategori_nilai';
        
        // Detail Nilai
        $hasAbsensi = $hasCol('nilai_absensi');
        $hasTugas   = $hasCol('nilai_tugas');
        $hasUts     = $hasCol('nilai_uts');
        $hasUas     = $hasCol('nilai_uas');
        
        $nilaiBatch = [];
        $jenisList = ['PH', 'UTS', 'UAS']; 

        foreach ($enrollments as $enroll) {
            $targetMapel = $faker->randomElements($mapelList, 3);
            
            foreach ($targetMapel as $mapel) {
                $guru = $faker->randomElement($guruList);

                foreach ($jenisList as $jenis) {
                    $nilaiAngka = $faker->numberBetween(70, 95);
                    $nilaiAkhir = $nilaiAngka; 
                    if ($jenis === 'UAS') {
                        $nilaiAkhir = ($nilaiAngka * 0.4) + ($faker->numberBetween(70, 90) * 0.6); 
                    }
                    $predikat = ($nilaiAkhir >= 90) ? 'A' : (($nilaiAkhir >= 80) ? 'B' : 'C');

                    // --- INISIALISASI DATA DASAR ---
                    $data = [
                        'id_mata_pelajaran' => $mapel['id'],
                        'id_guru'           => $guru['id'],
                        'semester'          => $enroll['semester'] ?? 'Ganjil',
                        'nilai_akhir'       => $nilaiAkhir,
                        'created_at'        => Time::now()->toDateTimeString(),
                        'updated_at'        => Time::now()->toDateTimeString()
                    ];

                    // Isi Kolom Kategori jika ada
                    if ($hasCol($colKategori)) {
                        $data[$colKategori] = $jenis;
                    }

                    // --- ISI FOREIGN KEY (STRICT CHECK) ---
                    if ($hasIdKelas && isset($enroll['id_kelas'])) {
                        $data['id_kelas'] = $enroll['id_kelas'];
                    }
                    if ($hasIdSiswa && isset($enroll['id_siswa'])) {
                        $data['id_siswa'] = $enroll['id_siswa'];
                    }
                    if ($hasIdTahunAjaran && isset($enroll['id_tahun_ajaran'])) {
                        $data['id_tahun_ajaran'] = $enroll['id_tahun_ajaran'];
                    }
                    if ($hasIdEnrollment && isset($enroll['id'])) {
                        $data['id_enrollment'] = $enroll['id'];
                    }
                    if ($hasKodeJenjang) {
                        $data['kode_jenjang'] = 'SD'; // Default
                    }

                    // --- ISI KOLOM OPSIONAL ---
                    if ($hasKeterangan) {
                        $data['keterangan'] = $faker->sentence(3);
                    }
                    if ($hasNilaiHuruf) {
                        $data['nilai_huruf'] = $predikat;
                    }

                    // --- ISI DETAIL NILAI (DEFAULT 0) ---
                    if ($hasTugas)   $data['nilai_tugas']   = 0;
                    if ($hasUts)     $data['nilai_uts']     = 0;
                    if ($hasUas)     $data['nilai_uas']     = 0;
                    if ($hasAbsensi) $data['nilai_absensi'] = 0;

                    // --- UPDATE NILAI SPESIFIK ---
                    if ($hasTugas && $jenis == 'PH')   $data['nilai_tugas'] = $nilaiAngka;
                    if ($hasUts && $jenis == 'UTS')    $data['nilai_uts'] = $nilaiAngka;
                    if ($hasUas && $jenis == 'UAS')    $data['nilai_uas'] = $nilaiAngka;
                    if ($hasAbsensi)                   $data['nilai_absensi'] = 100;

                    $nilaiBatch[] = $data;
                }
            }
        }

        // 4. Insert Batch
        if (!empty($nilaiBatch)) {
            // Validasi akhir: pastikan semua elemen batch punya keys yang sama persis
            // Ambil keys dari elemen pertama sebagai acuan master
            $masterKeys = array_keys($nilaiBatch[0]);
            
            // Filter setiap row agar hanya berisi key yang ada di master
            // Ini mencegah error "Column count doesn't match" jika ada logic if yang bocor
            $cleanBatch = array_map(function($row) use ($masterKeys) {
                $cleanRow = [];
                foreach ($masterKeys as $k) {
                    $cleanRow[$k] = $row[$k] ?? null;
                }
                return $cleanRow;
            }, $nilaiBatch);

            $chunks = array_chunk($cleanBatch, 100);
            $total = 0;
            foreach ($chunks as $chunk) {
                $db->table('nilai_siswa')->insertBatch($chunk);
                $total += count($chunk);
            }
            echo " [OK] $total data nilai berhasil di-seed ke tabel 'nilai_siswa'.\n";
        } else {
            echo " [INFO] Tidak ada data nilai yang di-generate.\n";
        }
    }

    /**
     * Helper untuk mengambil nama kolom secara REAL-TIME dari database
     * Menghindari masalah Cache Metadata CodeIgniter
     */
    private function getRealColumns($db, $table)
    {
        $query = $db->query("SHOW COLUMNS FROM " . $db->protectIdentifiers($table));
        $results = $query->getResultArray();
        return array_column($results, 'Field');
    }
}