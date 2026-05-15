<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * Seeder ini mengisi data dummy absensi harian siswa ke tabel 'absensi_siswa'.
 * Sudah mendukung kolom kode_jenjang dan sinkronisasi id_grup_siswa.
 */
class AbsensiSiswaSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $faker = \Faker\Factory::create('id_ID');
        $now = Time::now()->toDateTimeString();

        echo ">>> Memulai Seeder Absensi Siswa...\n";

        // 1. Ambil ID Tahun Ajaran aktif
        $tahunAjaranAktif = $db->table('tahun_ajaran')->where('status', 'aktif')->get(1)->getRowArray();
        if (empty($tahunAjaranAktif)) {
            echo " [!] Tahun Ajaran Aktif tidak ditemukan. Seeder dilewati.\n";
            return;
        }

        // 2. Ambil jadwal pelajaran (SINKRON: ambil id_grup_siswa dan kode_jenjang)
        $jadwal_records = $db->table('jadwal_pelajaran')
                             ->select('id, id_grup_siswa, kode_jenjang') 
                             ->where('id_tahun_ajaran', $tahunAjaranAktif['id'])
                             ->get()
                             ->getResultArray();

        if (empty($jadwal_records)) {
            echo " [!] Jadwal pelajaran tidak ditemukan. Seeder dilewati.\n";
            return;
        }

        $absensi_batch = [];
        $status_options = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'sakit', 'izin', 'alpa'];

        // Pengaturan rentang tanggal
        $db_start_date = $tahunAjaranAktif['tanggal_mulai'] ?? Time::now()->toDateString();
        $db_end_date   = $tahunAjaranAktif['tanggal_selesai'] ?? Time::parse($db_start_date)->modify('+6 months')->toDateString();

        $start_timestamp = Time::parse($db_start_date)->modify('+1 week')->getTimestamp();
        $end_timestamp   = Time::now()->getTimestamp(); // Absensi hanya sampai hari ini

        if ($start_timestamp >= $end_timestamp) {
            $end_timestamp = Time::parse($db_end_date)->getTimestamp();
        }

        // 3. Loop Jadwal
        foreach ($jadwal_records as $jadwal) {
            $id_jadwal = $jadwal['id'];
            $id_grup   = $jadwal['id_grup_siswa'];
            $unit      = $jadwal['kode_jenjang'];

            // 4. Ambil siswa yang terdaftar di Grup/Rombel ini
            $siswa_records = $db->table('siswa_enrollment')
                                ->select('id_siswa')
                                ->where('id_grup_siswa', $id_grup)
                                ->get()
                                ->getResultArray();

            if (empty($siswa_records)) continue;

            // Generate 2-3 tanggal absensi acak per jadwal agar data bervariasi
            for ($i = 0; $i < 2; $i++) {
                $random_date = Time::createFromTimestamp($faker->numberBetween($start_timestamp, $end_timestamp))->toDateString();

                foreach ($siswa_records as $siswa) {
                    $status = $faker->randomElement($status_options);
                    
                    $absensi_batch[] = [
                        'kode_jenjang' => $unit, // WAJIB ADA untuk filter unit scope
                        'id_jadwal'    => $id_jadwal,
                        'id_siswa'     => $siswa['id_siswa'],
                        'tanggal'      => $random_date,
                        'status'       => $status,
                        'keterangan'   => ($status !== 'hadir') ? $faker->sentence(2) : null,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                        'deleted_at'   => null,
                    ];
                }
            }
        }

        // 5. Insert data dengan proteksi Batch Chunk
        if (!empty($absensi_batch)) {
            $db->table('absensi_siswa')->truncate();
            
            // Chunk 500 data per baris untuk menghindari error database packet
            $chunks = array_chunk($absensi_batch, 500);
            foreach ($chunks as $chunk) {
                $db->table('absensi_siswa')->insertBatch($chunk);
            }
            
            echo " [OK] " . count($absensi_batch) . " data absensi siswa berhasil di-seed.\n";
        } else {
            echo " [!] Tidak ada data yang diproses.\n";
        }
    }
}