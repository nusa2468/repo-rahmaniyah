<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * RaportSeeder
 * Mengisi data rapor semester dummy.
 * SINKRONISASI: Dengan Migrasi 'raport' (Standar Dapodik)
 */
class RaportSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        echo "\n>>> Menjalankan Seeder Raport...\n";

        // 1. Matikan FK Check & Truncate
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        
        if ($db->tableExists('raport')) {
            $db->table('raport')->truncate();
        } else {
            echo " [SKIP] Tabel 'raport' tidak ditemukan.\n";
            return;
        }
        
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        $faker = \Faker\Factory::create('id_ID');

        // 2. Ambil Tahun Ajaran Aktif
        $ta = $db->table('tahun_ajaran')->where('status', 'aktif')->get()->getRow();
        // Fallback ke tahun terakhir jika tidak ada yang aktif
        if (!$ta) {
            $ta = $db->table('tahun_ajaran')->orderBy('id', 'DESC')->get()->getRow();
        }

        if (!$ta) {
            echo " [SKIP] GAGAL: Tabel 'tahun_ajaran' kosong.\n";
            return;
        }
        echo " - Tahun Ajaran target: ID {$ta->id}\n";

        // 3. Ambil Data Enrollment (Basis Data Rapor)
        if (!$db->tableExists('siswa_enrollment')) {
             echo " [SKIP] Tabel 'siswa_enrollment' tidak ditemukan.\n";
             return;
        }

        $enrollments = $db->table('siswa_enrollment')
                          ->select('id, id_siswa, id_kelas, semester')
                          ->where('id_tahun_ajaran', $ta->id)
                          ->get()->getResultArray();

        if (empty($enrollments)) {
            echo " [SKIP] Tidak ada data enrollment siswa pada tahun ajaran ini.\n";
            return;
        }
        echo " - Data Enrollment ditemukan: " . count($enrollments) . " baris\n";

        // 4. Deteksi Kolom (Robust Check)
        $cols = $db->getFieldNames('raport');
        $hasCol = fn($c) => in_array($c, $cols);

        // Cek Kolom Denormalisasi
        $hasIdKelas      = $hasCol('id_kelas');
        $hasIdSiswa      = $hasCol('id_siswa');
        $hasKodeJenjang  = $hasCol('kode_jenjang');
        $hasIdTahun      = $hasCol('id_tahun_ajaran');
        
        // Cek Kolom Baru (Standar Dapodik)
        $hasSikap        = $hasCol('predikat_spiritual');
        $hasKesehatan    = $hasCol('tinggi_badan');
        $hasKenaikan     = $hasCol('status_kenaikan');

        $raportBatch = [];
        $time = Time::now()->toDateTimeString();

        foreach ($enrollments as $row) {
            
            // --- DATA INTI (Wajib Ada) ---
            $data = [
                'id_enrollment'      => $row['id'],
                'semester'           => $row['semester'],
                'rata_rata'          => $faker->randomFloat(2, 75, 98),
                'total_sakit'        => $faker->numberBetween(0, 5),
                'total_izin'         => $faker->numberBetween(0, 3),
                'total_alpa'         => $faker->numberBetween(0, 1),
                'catatan_wali_kelas' => $faker->randomElement(['Tingkatkan prestasi.', 'Pertahankan semangat belajar.', 'Perbaiki kehadiran.']),
                'catatan_akademik'   => 'Kompetensi tercapai dengan baik.',
                'catatan_karakter'   => 'Menunjukkan sikap disiplin dan sopan.',
                'status_raport'      => 'Published',
                'tanggal_cetak'      => date('Y-m-d'),
                'is_locked'          => 1,
                'created_at'         => $time,
                'updated_at'         => $time
            ];

            // --- ISI DATA DAPODIK (Jika Kolom Ada) ---
            if ($hasSikap) {
                $data['predikat_spiritual']  = $faker->randomElement(['SB', 'B', 'B']);
                $data['deskripsi_spiritual'] = 'Selalu berdoa sebelum dan sesudah kegiatan.';
                $data['predikat_sosial']     = $faker->randomElement(['SB', 'B', 'B']);
                $data['deskripsi_sosial']    = 'Memiliki kepedulian tinggi terhadap teman.';
            }

            if ($hasKesehatan) {
                $data['tinggi_badan'] = $faker->numberBetween(120, 170);
                $data['berat_badan']  = $faker->numberBetween(30, 65);
            }

            if ($hasKenaikan) {
                // Status kenaikan hanya relevan untuk Semester Genap
                $data['status_kenaikan'] = ($row['semester'] == 'Genap') ? 'Naik Kelas' : null;
            }

            // --- ISI KOLOM DENORMALISASI (Jika Ada) ---
            if ($hasIdKelas) {
                $data['id_kelas'] = $row['id_kelas'];
            }
            if ($hasIdSiswa) {
                $data['id_siswa'] = $row['id_siswa'];
            }
            if ($hasIdTahun) {
                $data['id_tahun_ajaran'] = $ta->id;
            }
            // Kode jenjang bisa ditambahkan logic query jika diperlukan, atau skip

            $raportBatch[] = $data;
        }

        // 5. Eksekusi Insert Batch
        if (!empty($raportBatch)) {
            // Normalisasi Struktur Array (Pencegahan error "Column count doesn't match")
            $masterKeys = array_keys($raportBatch[0]);
            $cleanBatch = [];
            foreach($raportBatch as $d) {
                $rowClean = [];
                foreach($masterKeys as $k) {
                    $rowClean[$k] = $d[$k] ?? null;
                }
                $cleanBatch[] = $rowClean;
            }

            // Chunking
            $chunks = array_chunk($cleanBatch, 100);
            $total = 0;
            foreach ($chunks as $chunk) {
                $db->table('raport')->insertBatch($chunk);
                $total += count($chunk);
            }
            echo " [OK] Berhasil mengisi $total data raport.\n";
        } else {
            echo " [INFO] Tidak ada data raport yang di-generate.\n";
        }
    }
}