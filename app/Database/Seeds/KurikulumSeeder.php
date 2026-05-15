<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use Throwable;

/**
 * ============================================================================
 * KURIKULUM SEEDER (ENTERPRISE VERSION)
 * ============================================================================
 * Deskripsi: Menyediakan data dasar Kurikulum untuk seluruh unit sekolah.
 * Fitur: Menggunakan logika Upsert berdasarkan kode_kurikulum, 
 * pembacaan unit otomatis (Dynamic Unit Fetching).
 * Update: Integrasi Struktur Kurikulum Lengkap SDIT, SMPIT, & SMAIT Rahmaniyah.
 */
class KurikulumSeeder extends Seeder
{
    public function run()
    {
        // BLOKADE CLI DIHAPUS agar seeder dapat berjalan mulus saat dipanggil dari URL/Browser
        
        echo "<pre>\n=======================================================\n";
        echo ">>> MENJALANKAN KURIKULUM SEEDER (SINKRONISASI SIT)\n";
        echo "=======================================================\n";
        
        $db = $this->db;
        $now = Time::now()->format('Y-m-d H:i:s');

        // --------------------------------------------------------------------
        // 1. Dapatkan Daftar Unit/Jenjang yang Valid dari Database
        // --------------------------------------------------------------------
        $units = ['SD', 'SMP', 'SMA']; // Fallback default
        
        if ($db->tableExists('jenjang_sekolah')) {
            $jenjangs = $db->table('jenjang_sekolah')
                           ->whereNotIn('kode_jenjang', ['GLOBAL', 'YAYASAN', 'PUSAT'])
                           ->get()->getResultArray();
                           
            if (!empty($jenjangs)) {
                $units = array_column($jenjangs, 'kode_jenjang');
            }
        }

        echo " [INFO] Unit yang terdeteksi: " . implode(', ', $units) . "\n\n";

        // --------------------------------------------------------------------
        // 2. Persiapan Data Kurikulum per Unit (Dinamis sesuai Jenjang)
        // --------------------------------------------------------------------
        $data = [];

        foreach ($units as $unit) {
            $unitCode = strtoupper($unit);

            // --- A. Setup Deskripsi Kurikulum Merdeka (Akademik) ---
            $descKM = 'Kurikulum dengan pembelajaran intrakurikuler yang beragam dengan konten esensial.';
            $ketKM  = 'Kurikulum utama yang berlaku aktif untuk seluruh tingkat.';
            
            if ($unitCode === 'SD') {
                $descKM = 'Fokus pada materi esensial akademik dan pengembangan karakter melalui Proyek Penguatan Profil Pelajar Pancasila (P5).';
                $ketKM  = "Struktur Fase:\n- Fase A (Kls 1-2): Transisi PAUD, pengenalan literasi, numerasi.\n- Fase B (Kls 3-4): Logika, IPAS, dan eksplorasi lingkungan.\n- Fase C (Kls 5-6): Pemantapan akademik.\n\nSistem Evaluasi Akademik:\n- Formatif: Observasi perilaku & tugas mandiri.\n- Sumatif: PTS dan PAS.";
            } elseif ($unitCode === 'SMP') {
                $descKM = 'Mengacu pada standar Kemendikdasmen dengan pendekatan pembelajaran berbasis proyek (P5).';
                $ketKM  = "Struktur Fase:\n- Kelas 7 (Transisi & Adaptasi): Adaptasi metode belajar mandiri dan teknologi (Informatika).\n- Kelas 8 (Eksplorasi & Kepemimpinan): Pengembangan logika berpikir melalui proyek IPAS.\n- Kelas 9 (Pemantapan & Kelulusan): Pendalaman materi akademik untuk persiapan SMA/MA.\n\nSistem Evaluasi:\nKognitif (Asesmen Sumatif), Afektif, dan Psikomotorik.";
            } elseif ($unitCode === 'SMA') {
                $descKM = 'Fase Pematangan Kepemimpinan dan Persiapan Masa Depan, memberikan fleksibilitas pilihan minat di Kelas 11-12.';
                $ketKM  = "Struktur Fase:\n- Kelas 10 (Fase E): Eksplorasi & Fondasi penjajakan minat (IPA/IPS terpadu).\n- Kelas 11 (Fase F): Pengembangan & Spesialisasi. Siswa memilih kombinasi mata pelajaran peminatan.\n- Kelas 12 (Fase F): Pemantapan & Persiapan PTN (Intensif UTBK).\n\nSistem Evaluasi: Asesmen Sumatif, Portofolio Proyek (P5), dan Karya Tulis Ilmiah.";
            }

            // --- B. Setup Deskripsi Kurikulum Khas SIT (Agama & Karakter) ---
            $descSIT = 'Kurikulum muatan lokal/khas Yayasan Terpadu. Memuat program pembinaan karakter islami.';
            $ketSIT  = 'Memuat program BPI, Tahfidz, Bahasa Arab, dan Adab harian.';

            if ($unitCode === 'SD') {
                $descSIT = 'Penambahan muatan keislaman, penguatan adab, dan Al-Qur\'an (Tahfidz/Tilawah).';
                $ketSIT  = "Muatan Khas:\n- Al-Qur'an: Tahfidz (Target Juz 30 & 29) dan Tilawah/Umi.\n- Keislaman: Bahasa Arab, BPI, Sirah Nabawiyah.\n\nProgram Unggulan:\n1. Morning Spiritual (Dhuha)\n2. Mabit (Malam Bina Iman)\n3. Market Day\n\nSistem Evaluasi: Imtihan Al-Qur'an (Munaqosyah) & Raport Karakter.";
            } elseif ($unitCode === 'SMP') {
                $descSIT = 'Fokus pada penguatan akidah, kecerdasan intelektual, dan kesiapan kepemimpinan remaja.';
                $ketSIT  = "Muatan Khas:\n- Al-Qur'an: Target hafalan 2-3 Juz saat lulus.\n- Keislaman: Bahasa Arab (Hiwar), BPI, Tarikh Islam, Hadits Pilihan.\n\nProgram Unggulan:\n1. Tahfidz Camp\n2. Mabit & Qiyamul Lail\n3. Social Service & Pramuka SIT\n\nSyarat Lulus (SKK): Tuntas target hafalan, Lulus praktik ibadah, Konsisten BPI.";
            } elseif ($unitCode === 'SMA') {
                $descSIT = 'Kedalaman ilmu agama (Tafaqquh Fiddin) dan pembentukan pemimpin berintegritas islami.';
                $ketSIT  = "Muatan Khas:\n- Al-Qur'an: Tahfidz & Tafsir Tematik.\n- Keislaman: Kitab Kuning (Fikih/Akidah), BPI, Bahasa Arab Literasi.\n- Pengembangan: Leadership & Entrepreneurship.\n\nProgram Unggulan:\n1. Camp Quran & Munaqosyah Akhir\n2. University Visit\n3. Entrepreneur Week & Leadership Training\n\nSKK: Target hafalan Al-Qur'an, Praktik ibadah, Proyek pengabdian masyarakat.";
            }

            // 1. Array Kurikulum Merdeka
            $data[] = [
                'kode_jenjang'   => $unitCode,
                'kode_kurikulum' => 'KM-' . $unitCode, 
                'nama_kurikulum' => 'Kurikulum Merdeka ' . $unitCode,
                'deskripsi'      => $descKM,
                'keterangan'     => $ketKM,
                'status'         => 'aktif',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            // 2. Array Kurikulum Khas SIT
            $data[] = [
                'kode_jenjang'   => $unitCode,
                'kode_kurikulum' => 'SIT-' . $unitCode, 
                'nama_kurikulum' => 'Kurikulum Khas SIT ' . $unitCode,
                'deskripsi'      => $descSIT,
                'keterangan'     => $ketSIT,
                'status'         => 'aktif',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            // 3. Array Kurikulum 2013 (Non-Aktif)
            $data[] = [
                'kode_jenjang'   => $unitCode,
                'kode_kurikulum' => 'K13-' . $unitCode,
                'nama_kurikulum' => 'Kurikulum 2013 ' . $unitCode,
                'deskripsi'      => 'Kurikulum berbasis kompetensi yang menekankan pada aspek kognitif, afektif, dan psikomotor.',
                'keterangan'     => 'Hanya digunakan untuk data transisi atau referensi nilai arsip lama.',
                'status'         => 'tidak aktif',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            // 4. Array KTSP (Non-Aktif)
            $data[] = [
                'kode_jenjang'   => $unitCode,
                'kode_kurikulum' => 'KTSP-' . $unitCode,
                'nama_kurikulum' => 'KTSP ' . $unitCode,
                'deskripsi'      => 'Kurikulum Tingkat Satuan Pendidikan (2006).',
                'keterangan'     => 'Data arsip historis untuk alumni angkatan lama.',
                'status'         => 'tidak aktif',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // --------------------------------------------------------------------
        // 3. Proses Sinkronisasi Database (Upsert Logic)
        // --------------------------------------------------------------------
        $db->disableForeignKeyChecks();
        $inserted = 0;
        $updated  = 0;
        
        foreach ($data as $row) {
            try {
                $builder = $db->table('kurikulum');
                
                $existing = $builder->where('kode_kurikulum', $row['kode_kurikulum'])->countAllResults();
                                               
                if ($existing > 0) {
                    // UPDATE
                    $updateData = $row;
                    unset($updateData['created_at']);
                    
                    $builder->where('kode_kurikulum', $row['kode_kurikulum'])
                            ->update($updateData);
                    $updated++;
                } else {
                    // INSERT
                    $builder->insert($row);
                    $inserted++;
                }
            } catch (Throwable $e) {
                echo " [ERR] Gagal sinkronisasi {$row['kode_kurikulum']}: " . $e->getMessage() . "\n";
            }
        }
        
        $db->enableForeignKeyChecks();
        echo " [SUCCESS] Sinkronisasi Kurikulum Selesai!\n";
        echo "           - Data Baru      : {$inserted} Data\n";
        echo "           - Data Diperbarui: {$updated} Data\n";
        echo "=======================================================\n</pre>\n";
    }
}