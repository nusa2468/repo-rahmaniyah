<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * ============================================================================
 * MANAJEMEN ASET SEEDER (ENTERPRISE ERP 1.0)
 * ============================================================================
 */
class ManajemenAsetSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $faker = \Faker\Factory::create('id_ID');
        $now = Time::now()->format('Y-m-d H:i:s');

        echo "\n>>> Menjalankan ManajemenAsetSeeder...\n";

        // 1. Matikan FK Checks & Bersihkan Data Lama
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        
        $tables = [
            'aset_pemeliharaan', 
            'aset_peminjaman', 
            'aset_pengadaan', 
            'aset_barang', 
            'aset_lokasi', 
            'aset_kategori'
        ];

        foreach ($tables as $table) {
            $db->table($table)->truncate();
        }

        // ===================================================================
        // 2. SEED: ASET KATEGORI
        // ===================================================================
        $kategoriData = [
            ['kode_kategori' => 'KTG-BDG', 'nama_kategori' => 'Bangunan & Fasilitas', 'tipe_aset' => 'Bangunan/Tanah'],
            ['kode_kategori' => 'KTG-ELK', 'nama_kategori' => 'Peralatan Elektronik & IT', 'tipe_aset' => 'Elektronik'],
            ['kode_kategori' => 'KTG-FNR', 'nama_kategori' => 'Mebel & Furniture', 'tipe_aset' => 'Furniture'],
            ['kode_kategori' => 'KTG-KND', 'nama_kategori' => 'Kendaraan Operasional', 'tipe_aset' => 'Kendaraan'],
            ['kode_kategori' => 'KTG-LLN', 'nama_kategori' => 'Inventaris Lainnya (Buku, dll)', 'tipe_aset' => 'Lainnya'],
        ];
        
        foreach ($kategoriData as &$k) {
            $k['kode_jenjang'] = 'GLOBAL'; // Karena kategori berlaku lintas unit
            $k['created_at'] = $now;
            $k['updated_at'] = $now;
        }
        $db->table('aset_kategori')->insertBatch($kategoriData);

        $katElk = $db->table('aset_kategori')->where('kode_kategori', 'KTG-ELK')->get()->getRow()->id;
        $katFnr = $db->table('aset_kategori')->where('kode_kategori', 'KTG-FNR')->get()->getRow()->id;
        $katBdg = $db->table('aset_kategori')->where('kode_kategori', 'KTG-BDG')->get()->getRow()->id;

        // ===================================================================
        // 3. SEED: ASET LOKASI (Per Unit: SD, SMP, SMA)
        // ===================================================================
        $units = ['SD', 'SMP', 'SMA'];
        $lokasiData = [];

        foreach ($units as $unit) {
            $lokasiData[] = ['kode_jenjang' => $unit, 'jenis_lokasi' => 'Gedung', 'nama_lokasi' => "Gedung Utama $unit", 'kapasitas' => 500, 'created_at' => $now, 'updated_at' => $now];
            $lokasiData[] = ['kode_jenjang' => $unit, 'jenis_lokasi' => 'Ruang Kelas', 'nama_lokasi' => "Ruang Kelas 1A - $unit", 'kapasitas' => 30, 'created_at' => $now, 'updated_at' => $now];
            $lokasiData[] = ['kode_jenjang' => $unit, 'jenis_lokasi' => 'Ruang Kelas', 'nama_lokasi' => "Ruang Kelas 1B - $unit", 'kapasitas' => 30, 'created_at' => $now, 'updated_at' => $now];
            if ($unit === 'SMP' || $unit === 'SMA') {
                $lokasiData[] = ['kode_jenjang' => $unit, 'jenis_lokasi' => 'Laboratorium', 'nama_lokasi' => "Lab Komputer $unit", 'kapasitas' => 40, 'created_at' => $now, 'updated_at' => $now];
                $lokasiData[] = ['kode_jenjang' => $unit, 'jenis_lokasi' => 'Laboratorium', 'nama_lokasi' => "Lab Sains $unit", 'kapasitas' => 40, 'created_at' => $now, 'updated_at' => $now];
            }
            $lokasiData[] = ['kode_jenjang' => $unit, 'jenis_lokasi' => 'Gudang', 'nama_lokasi' => "Gudang Logistik $unit", 'kapasitas' => 0, 'created_at' => $now, 'updated_at' => $now];
        }
        $db->table('aset_lokasi')->insertBatch($lokasiData);

        // ===================================================================
        // 4. PERSIAPAN DATA PEGAWAI & LOKASI
        // ===================================================================
        $pegawaiList = $db->table('pegawai')->where('status_aktif', 'aktif')->get()->getResultArray();
        $pegawaiIds = !empty($pegawaiList) ? array_column($pegawaiList, 'id') : [null];
        $lokasiList = $db->table('aset_lokasi')->get()->getResultArray();

        // ===================================================================
        // 5. SEED: ASET BARANG (DENGAN STATUS KEPEMILIKAN)
        // ===================================================================
        $barangData = [];
        $asetCounter = 1;

        foreach ($lokasiList as $lokasi) {
            $jenjang = $lokasi['kode_jenjang'];
            $idLokasi = $lokasi['id'];

            if ($lokasi['jenis_lokasi'] === 'Ruang Kelas') {
                $barangData[] = [
                    'kode_jenjang'        => $jenjang,
                    'id_kategori'         => $katElk,
                    'id_lokasi'           => $idLokasi,
                    'id_penanggung_jawab' => $faker->randomElement($pegawaiIds),
                    'kode_aset'           => 'AST-ELK-' . date('Y') . '-' . str_pad($asetCounter++, 4, '0', STR_PAD_LEFT),
                    'nama_aset'           => 'Proyektor Epson EB-X400',
                    'merk_spesifikasi'    => 'Epson / 3300 Lumens / XGA',
                    'sumber_dana'         => 'Dana BOS',
                    'status_kepemilikan'  => 'Milik Sendiri', // <--- MENGGUNAKAN MILIK SENDIRI
                    'tanggal_perolehan'   => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                    'harga_perolehan'     => 5500000,
                    'kondisi'             => 'Baik',
                    'status_ketersediaan' => 'Tersedia',
                    'created_at'          => $now, 'updated_at' => $now
                ];
                
                $barangData[] = [
                    'kode_jenjang'        => $jenjang,
                    'id_kategori'         => $katFnr,
                    'id_lokasi'           => $idLokasi,
                    'id_penanggung_jawab' => $faker->randomElement($pegawaiIds),
                    'kode_aset'           => 'AST-FNR-' . date('Y') . '-' . str_pad($asetCounter++, 4, '0', STR_PAD_LEFT),
                    'nama_aset'           => 'Set Meja Kursi Siswa Kayu Jati (30 Unit)',
                    'merk_spesifikasi'    => 'Custom / Kayu Jati Jepara',
                    'sumber_dana'         => 'Donasi Wali Murid',
                    'status_kepemilikan'  => 'Hibah/Wakaf', // <--- CONTOH HIBAH
                    'tanggal_perolehan'   => $faker->dateTimeBetween('-5 years', '-1 years')->format('Y-m-d'),
                    'harga_perolehan'     => 15000000,
                    'kondisi'             => 'Baik',
                    'status_ketersediaan' => 'Tersedia',
                    'created_at'          => $now, 'updated_at' => $now
                ];
            }

            if ($lokasi['jenis_lokasi'] === 'Laboratorium' && strpos($lokasi['nama_lokasi'], 'Komputer') !== false) {
                // Menambahkan 1 Mesin Fotokopi Sewa di Ruang Komputer
                $barangData[] = [
                    'kode_jenjang'        => $jenjang,
                    'id_kategori'         => $katElk,
                    'id_lokasi'           => $idLokasi,
                    'id_penanggung_jawab' => $faker->randomElement($pegawaiIds),
                    'kode_aset'           => 'AST-ELK-' . date('Y') . '-' . str_pad($asetCounter++, 4, '0', STR_PAD_LEFT),
                    'nama_aset'           => "Mesin Fotokopi Kyocera",
                    'merk_spesifikasi'    => 'Kyocera M2040dn B/W',
                    'sumber_dana'         => 'Biaya Operasional (Sewa)',
                    'status_kepemilikan'  => 'Sewa', // <--- CONTOH SEWA
                    'tanggal_perolehan'   => date('Y-m-d'),
                    'harga_perolehan'     => 1500000, // Harga sewa per tahun
                    'kondisi'             => 'Baik',
                    'status_ketersediaan' => 'Tersedia',
                    'created_at'          => $now, 'updated_at' => $now
                ];

                for ($i=1; $i<=15; $i++) {
                    $kondisi = $faker->randomElement(['Baik', 'Baik', 'Baik', 'Rusak Ringan']);
                    $barangData[] = [
                        'kode_jenjang'        => $jenjang,
                        'id_kategori'         => $katElk,
                        'id_lokasi'           => $idLokasi,
                        'id_penanggung_jawab' => $faker->randomElement($pegawaiIds),
                        'kode_aset'           => 'AST-ELK-' . date('Y') . '-' . str_pad($asetCounter++, 4, '0', STR_PAD_LEFT),
                        'nama_aset'           => "PC Desktop Lenovo OptiPlex #$i",
                        'merk_spesifikasi'    => 'Lenovo / Core i5 10th Gen / 8GB RAM / 256GB SSD',
                        'sumber_dana'         => 'Yayasan',
                        'status_kepemilikan'  => 'Milik Sendiri', // <--- MILIK YAYASAN
                        'tanggal_perolehan'   => '2023-05-10',
                        'harga_perolehan'     => 7500000,
                        'kondisi'             => $kondisi,
                        'status_ketersediaan' => ($kondisi == 'Rusak Berat') ? 'Diperbaiki' : 'Tersedia',
                        'created_at'          => $now, 'updated_at' => $now
                    ];
                }
            }
        }
        $db->table('aset_barang')->insertBatch($barangData);

        // ... (KODE PEMINJAMAN, PEMELIHARAAN, PENGADAAN TETAP SAMA SEPERTI SEBELUMNYA) ...
        $barangElektronik = $db->table('aset_barang')->where('id_kategori', $katElk)->get()->getResultArray();
        $idElektronik = !empty($barangElektronik) ? array_column($barangElektronik, 'id') : [];

        // 6. PEMINJAMAN
        if (!empty($idElektronik) && !empty($pegawaiIds[0])) {
            $peminjamanData = [];
            for ($i=0; $i<5; $i++) {
                $status = $faker->randomElement(['Dipinjam', 'Dikembalikan', 'Menunggu']);
                $tglPinjam = $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d H:i:s');
                $peminjamanData[] = [
                    'id_aset'          => $faker->randomElement($idElektronik),
                    'tipe_peminjam'    => 'Pegawai',
                    'id_peminjam'      => $faker->randomElement($pegawaiIds),
                    'tanggal_pinjam'   => $tglPinjam,
                    'estimasi_kembali' => date('Y-m-d H:i:s', strtotime($tglPinjam . ' +2 days')),
                    'tanggal_kembali'  => ($status === 'Dikembalikan') ? date('Y-m-d H:i:s', strtotime($tglPinjam . ' +1 days')) : null,
                    'keperluan'        => 'Untuk presentasi materi rapat guru di aula.',
                    'status'           => $status,
                    'created_at'       => $now, 'updated_at' => $now
                ];
            }
            $db->table('aset_peminjaman')->insertBatch($peminjamanData);
        }

        // 7. PEMELIHARAAN
        if (!empty($idElektronik)) {
            $pemeliharaanData = [];
            for ($i=0; $i<4; $i++) {
                $pemeliharaanData[] = [
                    'id_aset'            => $faker->randomElement($idElektronik),
                    'jenis_pemeliharaan' => $faker->randomElement(['Rutin/Preventif', 'Perbaikan/Kerusakan']),
                    'tanggal_mulai'      => $faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
                    'tanggal_selesai'    => $faker->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
                    'pelaksana'          => 'Teknisi Internal (Pak ' . $faker->firstNameMale . ')',
                    'biaya'              => $faker->randomElement([0, 150000, 350000, 500000]),
                    'keterangan'         => 'Pembersihan debu dan penggantian thermal paste.',
                    'status'             => $faker->randomElement(['Sedang Proses', 'Selesai']),
                    'created_at'         => $now, 'updated_at' => $now
                ];
            }
            $db->table('aset_pemeliharaan')->insertBatch($pemeliharaanData);
        }

        // 8. PENGADAAN
        if (!empty($pegawaiIds[0])) {
            $pengadaanData = [
                [
                    'kode_jenjang'    => 'SMA',
                    'no_pengajuan'    => 'REQ-' . date('Ym') . '-001',
                    'judul_pengajuan' => 'Pengadaan 5 Unit Smart TV Interaktif',
                    'id_kategori'     => $katElk,
                    'jumlah_diminta'  => 5,
                    'estimasi_biaya'  => 45000000,
                    'alasan_kebutuhan'=> 'Mendukung metode pembelajaran E-Learning interaktif.',
                    'id_pemohon'      => $faker->randomElement($pegawaiIds),
                    'status'          => 'Menunggu Approval',
                    'catatan_reviewer'=> null,
                    'created_at'      => $now, 'updated_at' => $now
                ]
            ];
            $db->table('aset_pengadaan')->insertBatch($pengadaanData);
        }

        $db->query('SET FOREIGN_KEY_CHECKS=1;');
        echo ">>> SUCCESS: ManajemenAsetSeeder selesai. Status Kepemilikan berhasil ditambahkan!\n";
    }
}