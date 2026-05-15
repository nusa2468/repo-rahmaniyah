<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use Throwable;

class MataPelajaranSeeder extends Seeder
{
    public function run()
    {
        // Menggunakan tag <pre> agar jika dijalankan di browser, format log tetap rapi
        echo "<pre>\n=======================================================\n";
        echo ">>> MENJALANKAN MATA PELAJARAN SEEDER (FIX KURIKULUM)\n";
        echo "=======================================================\n";

        $dataMapel = [];
        $now = Time::now()->format('Y-m-d H:i:s');

        // ====================================================================
        // 1. AMBIL DATA KURIKULUM (WAJIB MENGGUNAKAN YANG AKTIF / MERDEKA)
        // ====================================================================
        $kurikulumList = $this->db->table('kurikulum')->get()->getResultArray();
        $kurikulumMap = [];
        
        // Prioritas 1: Cari Kurikulum Merdeka (KM) yang AKTIF
        foreach ($kurikulumList as $k) {
            $jenjang = strtoupper($k['kode_jenjang']);
            if ($k['status'] === 'aktif' && strpos(strtoupper($k['kode_kurikulum']), 'KM-') !== false) {
                $kurikulumMap[$jenjang] = $k['id'];
            }
        }

        // Prioritas 2: Jika KM tidak ada, ambil Kurikulum apapun yang AKTIF
        foreach ($kurikulumList as $k) {
            $jenjang = strtoupper($k['kode_jenjang']);
            if (!isset($kurikulumMap[$jenjang]) && $k['status'] === 'aktif') {
                $kurikulumMap[$jenjang] = $k['id'];
            }
        }
        
        // Prioritas 3 (Fallback): Jika tidak ada yang aktif, ambil apapun yang tersedia
        foreach ($kurikulumList as $k) {
            $jenjang = strtoupper($k['kode_jenjang']);
            if (!isset($kurikulumMap[$jenjang])) {
                $kurikulumMap[$jenjang] = $k['id'];
            }
        }

        echo " [INFO] Pemetaan Kurikulum Aktif:\n";
        foreach ($kurikulumMap as $jnj => $kId) {
            echo "        - Jenjang {$jnj} ditautkan ke Kurikulum ID: {$kId}\n";
        }
        echo "\n";

        // ====================================================================
        // 2. DEFINISI MATA PELAJARAN SDIT RAHMANIYAH (Fase A, B, C)
        // ====================================================================
        for ($lvl = 1; $lvl <= 6; $lvl++) {
            $mapelSD = [
                ['Pendidikan Agama Islam & Budi Pekerti', 'PAI', 'A'],
                ['Pendidikan Pancasila', 'PKN', 'A'],
                ['Bahasa Indonesia', 'BIN', 'A'],
                ['Matematika', 'MAT', 'A'],
                ['Seni dan Budaya', 'SBDP', 'B'],
                ['Pendidikan Jasmani Olahraga dan Kesehatan', 'PJOK', 'B']
            ];

            if ($lvl >= 3) {
                $mapelSD[] = ['Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'IPAS', 'A'];
            }

            // Muatan Khas SIT
            $mapelSD[] = ['Tahfidz Al-Qur\'an', 'THF', 'C'];

            if ($lvl <= 2) {
                $mapelSD[] = ['Tilawah / Umi', 'UMI', 'C'];
                $mapelSD[] = ['Bahasa Arab Dasar', 'BAR', 'C'];
            } elseif ($lvl <= 4) {
                $mapelSD[] = ['Bahasa Arab', 'BAR', 'C'];
                $mapelSD[] = ['Bahasa Inggris Dasar', 'BIG', 'C'];
                $mapelSD[] = ['Bina Pribadi Islam (BPI)', 'BPI', 'C'];
            } else {
                $mapelSD[] = ['Bahasa Arab', 'BAR', 'C'];
                $mapelSD[] = ['Teknologi Informasi & Komunikasi (TIK)', 'TIK', 'C'];
                $mapelSD[] = ['Sirah Nabawiyah', 'SIR', 'C'];
            }

            foreach ($mapelSD as $m) {
                $dataMapel[] = $this->_formatData('SD', $kurikulumMap['SD'] ?? null, $m[1], $lvl, $m[0], $m[2], $now);
            }
        }

        // ====================================================================
        // 3. DEFINISI MATA PELAJARAN SMPIT RAHMANIYAH (Tingkat 7 - 9)
        // ====================================================================
        $mapelSMP = [
            ['Pendidikan Agama Islam & Budi Pekerti', 'PAI', 'A'],
            ['Pendidikan Pancasila', 'PKN', 'A'],
            ['Bahasa Indonesia', 'BIN', 'A'],
            ['Matematika', 'MAT', 'A'],
            ['Ilmu Pengetahuan Alam (IPA)', 'IPA', 'A'],
            ['Ilmu Pengetahuan Sosial (IPS)', 'IPS', 'A'],
            ['Bahasa Inggris', 'BIG', 'A'],
            ['Pendidikan Jasmani, Olahraga, dan Kesehatan', 'PJOK', 'B'],
            ['Informatika', 'INF', 'B'],
            ['Seni & Prakarya', 'SBP', 'B'],
            // Khas SIT
            ['Al-Qur\'an (Tahfidz & Tahsin)', 'QUR', 'C'],
            ['Bahasa Arab', 'BAR', 'C'],
            ['Bina Pribadi Islam (BPI)', 'BPI', 'C'],
            ['Sirah Nabawiyah & Tarikh Islam', 'SIR', 'C'],
            ['Hadits Pilihan', 'HAD', 'C']
        ];

        foreach (range(7, 9) as $lvl) {
            foreach ($mapelSMP as $m) {
                $dataMapel[] = $this->_formatData('SMP', $kurikulumMap['SMP'] ?? null, $m[1], $lvl, $m[0], $m[2], $now);
            }
        }

        // ====================================================================
        // 4. DEFINISI MATA PELAJARAN SMAIT RAHMANIYAH (Tingkat 10 - 12)
        // ====================================================================
        for ($lvl = 10; $lvl <= 12; $lvl++) {
            $mapelSMA = [
                ['Pendidikan Agama Islam & Budi Pekerti', 'PAI', 'A'],
                ['Pendidikan Pancasila', 'PKN', 'A'],
                ['Bahasa Indonesia', 'BIN', 'A'],
                ['Matematika', 'MAT', 'A'],
                ['Bahasa Inggris', 'BIG', 'A'],
                ['Pendidikan Jasmani, Olahraga, dan Kesehatan', 'PJOK', 'B'],
                ['Seni & Prakarya', 'SBP', 'B']
            ];

            if ($lvl == 10) {
                // Fase E: Eksplorasi (Terpadu)
                $mapelSMA[] = ['Ilmu Pengetahuan Alam (IPA)', 'IPA', 'A'];
                $mapelSMA[] = ['Ilmu Pengetahuan Sosial (IPS)', 'IPS', 'A'];
                $mapelSMA[] = ['Informatika', 'INF', 'B'];
            } else {
                // Fase F: Peminatan (Spesialisasi)
                $mapelSMA[] = ['Sejarah', 'SEJ', 'A'];
                $mapelSMA[] = ['Matematika Tingkat Lanjut', 'MAT-L', 'D'];
                $mapelSMA[] = ['Fisika', 'FIS', 'D'];
                $mapelSMA[] = ['Kimia', 'KIM', 'D'];
                $mapelSMA[] = ['Biologi', 'BIO', 'D'];
                $mapelSMA[] = ['Sosiologi', 'SOS', 'D'];
                $mapelSMA[] = ['Geografi', 'GEO', 'D'];
                $mapelSMA[] = ['Ekonomi', 'EKO', 'D'];
            }

            // Khas SIT
            $mapelSMA[] = ['Al-Qur\'an (Tahfidz & Tafsir)', 'QUR', 'C'];
            $mapelSMA[] = ['Bina Pribadi Islam (BPI)', 'BPI', 'C'];
            $mapelSMA[] = ['Bahasa Arab Literasi', 'BAR', 'C'];
            $mapelSMA[] = ['Kitab Kuning / Dirasah Islamiyah', 'KTB', 'C'];
            $mapelSMA[] = ['Leadership & Entrepreneurship', 'LDR', 'C'];

            foreach ($mapelSMA as $m) {
                $dataMapel[] = $this->_formatData('SMA', $kurikulumMap['SMA'] ?? null, $m[1], $lvl, $m[0], $m[2], $now);
            }
        }

        // ====================================================================
        // 5. EKSEKUSI SINKRONISASI KE DATABASE (UPSERT LOGIC)
        // ====================================================================
        if (!empty($dataMapel)) {
            $this->db->disableForeignKeyChecks();
            
            $inserted = 0;
            $updated  = 0;
            $errors   = 0;

            foreach ($dataMapel as $row) {
                try {
                    $existing = $this->db->table('mata_pelajaran')
                                         ->where('kode_mapel', $row['kode_mapel'])
                                         ->get()->getRow();

                    if ($existing) {
                        // Jika kode mapel sudah ada, perbarui ID Kurikulumnya agar masuk ke Kurikulum Aktif!
                        $updateData = $row;
                        unset($updateData['created_at']); // Jangan ubah tanggal pembuatan asli
                        
                        $this->db->table('mata_pelajaran')
                                 ->where('id', $existing->id)
                                 ->update($updateData);
                        $updated++;
                    } else {
                        // Jika belum ada, masukkan baru
                        $this->db->table('mata_pelajaran')->insert($row);
                        $inserted++;
                    }
                } catch (Throwable $e) {
                    echo " [ERROR] Gagal memproses mapel {$row['kode_mapel']}: " . $e->getMessage() . "\n";
                    $errors++;
                }
            }
            
            $this->db->enableForeignKeyChecks();
            
            echo "\n [SUCCESS] Proses Seeding Mata Pelajaran Selesai!\n";
            echo "           - Mapel Baru Ditambahkan     : " . $inserted . " data\n";
            echo "           - Mapel Diperbaiki (Pindah)  : " . $updated . " data\n";
            if ($errors > 0) {
                echo "           - Gagal Diproses             : " . $errors . " data\n";
            }
            echo "=======================================================\n</pre>\n";
        } else {
            echo " [INFO] Tidak ada data Mata Pelajaran yang di-generate.\n</pre>";
        }
    }

    /**
     * Helper Fungsi untuk menyamakan format array insert database
     */
    private function _formatData($jenjang, $kurikulumId, $kodeDasar, $lvl, $namaMapel, $kelompok, $now)
    {
        return [
            'kode_jenjang'  => $jenjang,
            'kurikulum_id'  => $kurikulumId, 
            'kode_mapel'    => $kodeDasar . '.' . $jenjang . '.' . $lvl, // Contoh: PAI.SD.1
            'nama_mapel'    => $namaMapel,
            'kelompok'      => $kelompok,
            'tingkat'       => $lvl,
            'semester'      => null, // Null = Berlaku seluruh semester
            'status'        => 'aktif',
            'jumlah_jp'     => 4,
            'bobot_tugas'   => 0.30,
            'bobot_uts'     => 0.30,
            'bobot_uas'     => 0.30, // Total Bobot: 0.3 + 0.3 + 0.3 = 0.9 (0.1 lagi untuk Absensi Default)
            'bobot_absensi' => 0.10,
            'created_at'    => $now,
            'updated_at'    => $now
        ];
    }
}