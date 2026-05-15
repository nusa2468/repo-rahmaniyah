<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class TahunAjaranSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // 1. Non-aktifkan Foreign Key Check sementara
        $db->query('SET FOREIGN_KEY_CHECKS=0');

        $tableName = 'tahun_ajaran';
        $now = Time::now();
        $timestamp = $now->toDateTimeString();

        // 2. Ambil Daftar Jenjang (Scope Unit)
        // UPDATE: Hapus default 'GLOBAL', hanya ambil dari jenjang_sekolah
        $units = []; 
        
        if ($db->tableExists('jenjang_sekolah')) {
            $queryUnits = $db->table('jenjang_sekolah')->select('kode_jenjang')->get()->getResultArray();
            if (!empty($queryUnits)) {
                $units = array_column($queryUnits, 'kode_jenjang');
            }
        }

        // Fallback jika tabel jenjang kosong, gunakan default umum sekolah (tanpa Global)
        if (empty($units)) {
             $units = ['SD', 'SMP', 'SMA'];
        }

        // 3. Konfigurasi Loop Tahun
        // UPDATE: Hanya Tahun Ajaran 2025/2026 (Loop hanya 1 kali di 2025)
        $startYear = 2025; 
        $endYear   = 2025; 

        $dataToInsert = [];

        echo ">>> Menyiapkan Data Tahun Ajaran (Khusus 2025/2026)...\n";

        // 4. Loop Generate Data (Per Unit x Per Tahun)
        foreach ($units as $jenjang) {
            // Safety check: Pastikan tidak ada GLOBAL yang terselip
            if (strtoupper($jenjang) === 'GLOBAL') continue;

            for ($y = $startYear; $y <= $endYear; $y++) {
                $tahunAjaran = $y . '/' . ($y + 1);

                // --- Semester Ganjil (Juli - Desember) ---
                $startGanjil = "$y-07-01";
                $endGanjil   = "$y-12-31";
                
                // UPDATE: Ganjil diset TIDAK AKTIF
                $isGanjilAktif = false;

                $dataToInsert[] = [
                    'kode_jenjang'    => $jenjang, 
                    'tahun_ajaran'    => $tahunAjaran,
                    'semester'        => 'Ganjil',
                    'status'          => $isGanjilAktif ? 'aktif' : 'tidak aktif',
                    'tanggal_mulai'   => $startGanjil,
                    'tanggal_selesai' => $endGanjil,
                    'keterangan'      => "Semester Ganjil T.A. $tahunAjaran ($jenjang)",
                    'created_at'      => $timestamp,
                    'updated_at'      => $timestamp,
                ];

                // --- Semester Genap (Januari - Juni Tahun Berikutnya) ---
                $nextY = $y + 1;
                $startGenap = "$nextY-01-01";
                $endGenap   = "$nextY-06-30";
                
                // UPDATE: Genap diset AKTIF
                $isGenapAktif = true;

                $dataToInsert[] = [
                    'kode_jenjang'    => $jenjang, 
                    'tahun_ajaran'    => $tahunAjaran,
                    'semester'        => 'Genap',
                    'status'          => $isGenapAktif ? 'aktif' : 'tidak aktif',
                    'tanggal_mulai'   => $startGenap,
                    'tanggal_selesai' => $endGenap,
                    'keterangan'      => "Semester Genap T.A. $tahunAjaran ($jenjang)",
                    'created_at'      => $timestamp,
                    'updated_at'      => $timestamp,
                ];
            }
        }

        // 5. Proses Insert Aman
        if ($db->tableExists($tableName)) {
            
            // Cek struktur kolom tabel untuk keamanan insert
            $fieldNames = $db->getFieldNames($tableName);
            $finalData = [];

            foreach ($dataToInsert as $row) {
                $newRow = [];
                // Hanya masukkan data jika kolomnya ada di tabel database
                foreach ($row as $key => $val) {
                    if (in_array($key, $fieldNames)) {
                        $newRow[$key] = $val;
                    }
                }
                
                if (!empty($newRow)) {
                    $finalData[] = $newRow;
                }
            }

            if (!empty($finalData)) {
                // Opsional: Bersihkan data lama jika ingin reset total (Hati-hati)
                // $db->table($tableName)->truncate(); 

                // Insert Batch
                $count = $db->table($tableName)->insertBatch($finalData);
                echo " [OK] Berhasil memasukkan $count data Tahun Ajaran 2025/2026 untuk unit: " . implode(', ', $units) . " (Semester Genap Aktif).\n";
            }
        } else {
            echo " [ERROR] Tabel '$tableName' tidak ditemukan.\n";
        }

        // 6. Kembalikan Foreign Key Check
        $db->query('SET FOREIGN_KEY_CHECKS=1');
    }
}