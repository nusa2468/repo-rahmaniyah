<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NextYearSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Ambil Tahun Ajaran Aktif (Contoh: 2025/2026)
        $taAktif = $db->table('tahun_ajaran')->where('status', 'aktif')->get()->getRowArray();

        if (!$taAktif) {
            echo "Error: Tidak ada tahun ajaran aktif.\n";
            return;
        }

        echo "Tahun Ajaran Aktif: " . $taAktif['tahun_ajaran'] . "\n";

        // 2. Tentukan Tahun Depan (Otomatis menjadi 2026/2027)
        // Logic: Ambil 4 digit pertama (2025), tambah 1 untuk awal, tambah 2 untuk akhir
        $parts = explode('/', $taAktif['tahun_ajaran']);
        $startYear = (int)$parts[0];
        $nextYearString = ($startYear + 1) . '/' . ($startYear + 2);

        // 3. Cek/Buat Tahun Ajaran Depan
        $taBaru = $db->table('tahun_ajaran')->where('tahun_ajaran', $nextYearString)->get()->getRowArray();

        if (!$taBaru) {
            $db->table('tahun_ajaran')->insert([
                'tahun_ajaran' => $nextYearString,
                'semester'     => 'Ganjil',
                'status'       => 'tidak aktif', // Default tidak aktif
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            $idTaBaru = $db->insertID();
            echo "Membuat Tahun Ajaran Baru: $nextYearString (ID: $idTaBaru)\n";
        } else {
            $idTaBaru = $taBaru['id'];
            echo "Tahun Ajaran Baru sudah ada: $nextYearString (ID: $idTaBaru)\n";
        }

        // 4. Duplikasi Kelas ke Tahun Ajaran Baru
        // UPDATE: Filter Scope Unit - Kecualikan Global/Yayasan karena bukan unit sekolah
        $kelasLama = $db->table('kelas')
            ->where('id_tahun_ajaran', $taAktif['id'])
            ->whereNotIn('kode_jenjang', ['Global', 'Yayasan']) // Filter Scope Unit
            ->get()->getResultArray();
        
        $count = 0;
        foreach ($kelasLama as $k) {
            // Cek apakah kelas ini sudah ada di tahun baru (hindari duplikat)
            $cek = $db->table('kelas')
                ->where('nama_kelas', $k['nama_kelas'])
                ->where('id_tahun_ajaran', $idTaBaru)
                ->where('kode_jenjang', $k['kode_jenjang']) // Pastikan jenjang sama
                ->countAllResults();

            if ($cek == 0) {
                $dataBaru = $k;
                unset($dataBaru['id']); // Hapus ID lama
                $dataBaru['id_tahun_ajaran'] = $idTaBaru; // Set ke tahun baru
                
                // PERBAIKAN: Menangani constraint NOT NULL pada id_wali_kelas
                // Jika data lama kosong/null, ambil sembarang ID guru yang valid sebagai placeholder
                // agar tidak error database
                if (empty($dataBaru['id_wali_kelas'])) {
                    $guru = $db->table('guru')->select('id')->limit(1)->get()->getRow();
                    // Jika tidak ada guru sama sekali di DB, paksa ID 1 (ini berisiko jika tabel guru kosong, tapi seharusnya tidak di seed penuh)
                    $dataBaru['id_wali_kelas'] = $guru ? $guru->id : 1; 
                }
                
                // Bersihkan field yang mungkin usang atau salah nama
                if(isset($dataBaru['wali_kelas'])) unset($dataBaru['wali_kelas']);

                $dataBaru['created_at'] = date('Y-m-d H:i:s');
                $dataBaru['updated_at'] = date('Y-m-d H:i:s');

                $db->table('kelas')->insert($dataBaru);
                $count++;
            }
        }

        echo "Berhasil menduplikasi $count kelas ke Tahun Ajaran $nextYearString (Global/Yayasan diabaikan).\n";
    }
}