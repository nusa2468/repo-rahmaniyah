<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * SaprasSeeder
 * Digunakan untuk mengisi data awal (dummy data) pada modul Sarana & Prasarana.
 * Pastikan Seeder JenjangSekolah sudah dijalankan sebelumnya agar Foreign Key aman.
 */
class SaprasSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now();
        $db = \Config\Database::connect();

        // 1. Bersihkan data lama (Truncate dengan aman)
        // Urutan truncate tidak terlalu masalah karena FK check dimatikan
        $tables = ['sapras_inventaris', 'sapras_peralatan', 'sapras_ruangan', 'sapras_gedung', 'sapras_tanah'];
        
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($tables as $table) {
            $db->table($table)->truncate();
        }
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        echo "Cleaning old data... Done.\n";

        // 2. Seed Data Tanah
        // Asumsi: Kode Jenjang 'SD', 'SMP', 'SMA' sudah ada di tabel jenjang_sekolah
        $tanah = [
            [
                'kode_jenjang' => 'SD',
                'nama'         => 'Lahan Kampus SD Pratama',
                'luas'         => 2000.00,
                'sertifikat'   => 'HM-SD-101',
                'keterangan'   => 'Lahan unit SD',
                'created_at'   => $now, 'updated_at' => $now,
            ],
            [
                'kode_jenjang' => 'SMP',
                'nama'         => 'Lahan Kampus SMP Pratama',
                'luas'         => 3000.00,
                'sertifikat'   => 'HM-SMP-202',
                'keterangan'   => 'Lahan unit SMP',
                'created_at'   => $now, 'updated_at' => $now,
            ],
            [
                'kode_jenjang' => 'SMA',
                'nama'         => 'Lahan Kampus SMA Pratama',
                'luas'         => 4500.00,
                'sertifikat'   => 'HM-SMA-303',
                'keterangan'   => 'Lahan unit SMA',
                'created_at'   => $now, 'updated_at' => $now,
            ],
        ];
        $db->table('sapras_tanah')->insertBatch($tanah);

        // 3. Seed Data Gedung
        $gedung = [
            [
                'kode_jenjang' => 'SD',
                'nama'         => 'Gedung Merah (SD)',
                'tahun'        => '2012',
                'luas'         => 500.00,
                'keterangan'   => 'Gedung Belajar SD',
                'created_at'   => $now, 'updated_at' => $now,
            ],
            [
                'kode_jenjang' => 'SMP',
                'nama'         => 'Gedung Biru (SMP)',
                'tahun'        => '2015',
                'luas'         => 800.00,
                'keterangan'   => 'Gedung Belajar SMP',
                'created_at'   => $now, 'updated_at' => $now,
            ],
            [
                'kode_jenjang' => 'SMA',
                'nama'         => 'Gedung Hijau (SMA)',
                'tahun'        => '2018',
                'luas'         => 1200.00,
                'keterangan'   => 'Gedung Belajar SMA',
                'created_at'   => $now, 'updated_at' => $now,
            ],
        ];
        $db->table('sapras_gedung')->insertBatch($gedung);

        // 4. Ambil ID Gedung untuk Relasi Ruangan
        // Menggunakan getRow() karena kita yakin datanya baru saja diinsert
        $gedungSD  = $db->table('sapras_gedung')->where('kode_jenjang', 'SD')->get()->getRow();
        $gedungSMP = $db->table('sapras_gedung')->where('kode_jenjang', 'SMP')->get()->getRow();
        $gedungSMA = $db->table('sapras_gedung')->where('kode_jenjang', 'SMA')->get()->getRow();

        // Validasi sederhana agar tidak error jika insert gedung gagal
        if (!$gedungSD || !$gedungSMP || !$gedungSMA) {
            echo "ERROR: Data Gedung tidak ditemukan. Pastikan insert batch gedung berhasil.\n";
            return;
        }

        // 5. Seed Data Ruangan
        $ruangan = [
            // SD
            ['kode_jenjang' => 'SD', 'id_gedung' => $gedungSD->id, 'nama' => 'Ruang Kelas 1-A', 'kapasitas' => 28, 'keterangan' => 'Lantai 1 Sayap Kiri', 'created_at' => $now, 'updated_at' => $now],
            ['kode_jenjang' => 'SD', 'id_gedung' => $gedungSD->id, 'nama' => 'Ruang Guru SD', 'kapasitas' => 15, 'keterangan' => 'Lantai 1 Utama', 'created_at' => $now, 'updated_at' => $now],
            // SMP
            ['kode_jenjang' => 'SMP', 'id_gedung' => $gedungSMP->id, 'nama' => 'Lab IPA SMP', 'kapasitas' => 32, 'keterangan' => 'Lantai 2', 'created_at' => $now, 'updated_at' => $now],
            ['kode_jenjang' => 'SMP', 'id_gedung' => $gedungSMP->id, 'nama' => 'Perpustakaan SMP', 'kapasitas' => 50, 'keterangan' => 'Gedung Belakang', 'created_at' => $now, 'updated_at' => $now],
            // SMA
            ['kode_jenjang' => 'SMA', 'id_gedung' => $gedungSMA->id, 'nama' => 'Aula Pertemuan SMA', 'kapasitas' => 200, 'keterangan' => 'Lantai Utama', 'created_at' => $now, 'updated_at' => $now],
            ['kode_jenjang' => 'SMA', 'id_gedung' => $gedungSMA->id, 'nama' => 'Lab Komputer SMA', 'kapasitas' => 36, 'keterangan' => 'Full AC', 'created_at' => $now, 'updated_at' => $now],
        ];
        $db->table('sapras_ruangan')->insertBatch($ruangan);

        // 6. Seed Data Peralatan
        $peralatan = [
            [
                'kode_jenjang' => 'SD',
                'nama'         => 'Set Alat Peraga Matematika',
                'kondisi'      => 'Baik',
                'jumlah'       => 5,
                'keterangan'   => 'Alat peraga balok kayu',
                'created_at'   => $now, 'updated_at' => $now,
            ],
            [
                'kode_jenjang' => 'SMP',
                'nama'         => 'Mikroskop Binokuler',
                'kondisi'      => 'Baik',
                'jumlah'       => 10,
                'keterangan'   => 'Inventaris Lab IPA',
                'created_at'   => $now, 'updated_at' => $now,
            ],
            [
                'kode_jenjang' => 'SMA',
                'nama'         => 'Server E-Learning',
                'kondisi'      => 'Baik',
                'jumlah'       => 2,
                'keterangan'   => 'Server Dell PowerEdge',
                'created_at'   => $now, 'updated_at' => $now,
            ],
             [
                'kode_jenjang' => 'SD',
                'nama'         => 'Proyektor Epson',
                'kondisi'      => 'Rusak Ringan',
                'jumlah'       => 1,
                'keterangan'   => 'Lampu redup',
                'created_at'   => $now, 'updated_at' => $now,
            ],
        ];
        $db->table('sapras_peralatan')->insertBatch($peralatan);

        // 7. Seed Data Inventaris
        $inventaris = [
            [
                'kode_jenjang' => 'SD',
                'nama'         => 'Meja Siswa Kayu',
                'kondisi'      => 'Baik',
                'jumlah'       => 120,
                'keterangan'   => 'Kayu Jati',
                'created_at'   => $now, 'updated_at' => $now,
            ],
            [
                'kode_jenjang' => 'SMP',
                'nama'         => 'Kursi Kuliah',
                'kondisi'      => 'Baik',
                'jumlah'       => 150,
                'keterangan'   => 'Chitose',
                'created_at'   => $now, 'updated_at' => $now,
            ],
            [
                'kode_jenjang' => 'SMA',
                'nama'         => 'Loker Siswa',
                'kondisi'      => 'Baik',
                'jumlah'       => 200,
                'keterangan'   => 'Plat Besi',
                'created_at'   => $now, 'updated_at' => $now,
            ],
        ];
        $db->table('sapras_inventaris')->insertBatch($inventaris);

        echo "SUCCESS: SaprasSeeder selesai. Data Dummy SD, SMP, SMA berhasil diimport.\n";
    }
}