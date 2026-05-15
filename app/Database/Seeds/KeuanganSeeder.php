<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * KeuanganSeeder
 * Menghasilkan data dummy keuangan yang merujuk pada ISAK 35 & SAK EP.
 * Menggunakan istilah Aset Neto, Penghasilan, dan Beban sesuai standar Yayasan.
 */
class KeuanganSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // 1. Ambil Data Referensi Dasar
        $jenjangList = $db->table('jenjang_sekolah')->select('kode_jenjang')->get()->getResultArray();
        $admin       = $db->table('users')->select('id')->get(1)->getRowArray();

        if (empty($jenjangList) || empty($admin)) {
            echo "SKIPPED: Data referensi (Jenjang/Admin) tidak ditemukan.\n";
            return;
        }

        // Clean up tables
        $db->query("SET FOREIGN_KEY_CHECKS = 0;");
        $db->table('anggaran_unit')->truncate();
        $db->table('pengeluaran')->truncate();
        $db->table('kategori_anggaran')->truncate();
        $db->table('pembayaran')->truncate();
        $db->table('tagihan')->truncate();
        $db->table('jenis_pembayaran')->truncate();
        $db->query("SET FOREIGN_KEY_CHECKS = 1;");

        // -----------------------------------------------------------------
        // 2. SEED: kategori_anggaran (COA merujuk pada ISAK 35)
        // Perbaikan: Gunakan 'penghasilan' bukan 'pendapatan' agar sesuai ENUM Migration
        // -----------------------------------------------------------------
        $coaData = [
            // KELOMPOK 4: PENGHASILAN (Penerimaan dari Kontrak Pelanggan/Siswa)
            ['kode_kategori' => '4-1100', 'nama_kategori' => 'Penghasilan SPP (Bulanan)', 'kelompok' => 'penghasilan'],
            ['kode_kategori' => '4-1200', 'nama_kategori' => 'Penghasilan Pengembangan (Gedung)', 'kelompok' => 'penghasilan'],
            ['kode_kategori' => '4-1300', 'nama_kategori' => 'Penghasilan Seragam & Atribut', 'kelompok' => 'penghasilan'],
            ['kode_kategori' => '4-2100', 'nama_kategori' => 'Penghasilan Sumbangan/Donasi', 'kelompok' => 'penghasilan'],
            
            // KELOMPOK 5: BEBAN (Beban Operasional Pendidikan)
            ['kode_kategori' => '5-1100', 'nama_kategori' => 'Beban Gaji, Upah & Tunjangan', 'kelompok' => 'beban'],
            ['kode_kategori' => '5-1200', 'nama_kategori' => 'Beban Listrik, Air & Sanitasi', 'kelompok' => 'beban'],
            ['kode_kategori' => '5-1300', 'nama_kategori' => 'Beban Telekomunikasi & Internet', 'kelompok' => 'beban'],
            ['kode_kategori' => '5-1400', 'nama_kategori' => 'Beban Bahan Habis Pakai (ATK)', 'kelompok' => 'beban'],
            ['kode_kategori' => '5-1500', 'nama_kategori' => 'Beban Pemeliharaan Sarana Prasarana', 'kelompok' => 'beban'],
            ['kode_kategori' => '5-2100', 'nama_kategori' => 'Beban Program Kesiswaan & Lomba', 'kelompok' => 'beban'],
            ['kode_kategori' => '5-2200', 'nama_kategori' => 'Beban Ekstrakurikuler & OSIS', 'kelompok' => 'beban'],
            ['kode_kategori' => '5-3100', 'nama_kategori' => 'Beban Administrasi Umum & Kantor', 'kelompok' => 'beban'],
        ];
        $db->table('kategori_anggaran')->insertBatch($coaData);
        $allCoa = $db->table('kategori_anggaran')->get()->getResultArray();
        
        $getCoaId = function($kode) use ($allCoa) {
            foreach($allCoa as $c) if($c['kode_kategori'] == $kode) return $c['id'];
            return null;
        };

        // -----------------------------------------------------------------
        // 3. SEED: anggaran_unit (Target Tahunan Berbasis COA)
        // -----------------------------------------------------------------
        $dataAnggaran = [];
        $tahunSekarang = "2025/2026";
        
        foreach ($jenjangList as $j) {
            $kode = $j['kode_jenjang'];
            
            // --- A. Target Penghasilan (Revenue Target) ---
            $dataAnggaran[] = [
                'kode_jenjang' => $kode,
                'id_kategori'  => $getCoaId('4-1100'),
                'tahun'        => $tahunSekarang,
                'nominal'      => 800000000, // Target 800 Juta
                'keterangan'   => "Target Penghasilan SPP Unit $kode"
            ];
            $dataAnggaran[] = [
                'kode_jenjang' => $kode,
                'id_kategori'  => $getCoaId('4-1200'),
                'tahun'        => $tahunSekarang,
                'nominal'      => 250000000, // Target 250 Juta
                'keterangan'   => "Target Penghasilan Gedung Unit $kode"
            ];
            
            // --- B. Alokasi Beban Utama per Unit (Expense Budget) ---
            $dataAnggaran[] = [
                'kode_jenjang' => $kode,
                'id_kategori'  => $getCoaId('5-1100'),
                'tahun'        => $tahunSekarang,
                'nominal'      => 650000000,
                'keterangan'   => "Anggaran Gaji/Honor Guru Unit $kode"
            ];
            $dataAnggaran[] = [
                'kode_jenjang' => $kode,
                'id_kategori'  => $getCoaId('5-1200'),
                'tahun'        => $tahunSekarang,
                'nominal'      => 45000000,
                'keterangan'   => "Anggaran Utilitas Unit $kode"
            ];
            $dataAnggaran[] = [
                'kode_jenjang' => $kode,
                'id_kategori'  => $getCoaId('5-2100'),
                'tahun'        => $tahunSekarang,
                'nominal'      => 120000000,
                'keterangan'   => "Anggaran Program Kesiswaan Unit $kode"
            ];
        }
        $db->table('anggaran_unit')->insertBatch($dataAnggaran);

        // -----------------------------------------------------------------
        // 4. SEED: jenis_pembayaran (Mapping ke Penghasilan)
        // -----------------------------------------------------------------
        $dataJenis = [];
        $tarif = [
            'SD'  => ['spp' => 150000, 'gedung' => 1200000],
            'SMP' => ['spp' => 275000, 'gedung' => 2500000],
            'SMA' => ['spp' => 450000, 'gedung' => 4000000],
        ];

        foreach ($jenjangList as $j) {
            $kode = $j['kode_jenjang'];
            $t    = $tarif[$kode] ?? ['spp' => 200000, 'gedung' => 1000000];

            $dataJenis[] = [
                'kode_jenjang' => $kode, 
                'nama_pembayaran' => "SPP Bulanan ($kode)", 
                'tipe' => 'bulanan', 
                'nominal' => $t['spp'], 
                'created_at' => Time::now()
            ];
            $dataJenis[] = [
                'kode_jenjang' => $kode, 
                'nama_pembayaran' => "Dana Pembangunan ($kode)", 
                'tipe' => 'sekali_bayar', 
                'nominal' => $t['gedung'], 
                'created_at' => Time::now()
            ];
        }
        $db->table('jenis_pembayaran')->insertBatch($dataJenis);
        $allJenis = $db->table('jenis_pembayaran')->get()->getResultArray();

        // -----------------------------------------------------------------
        // 5. SEED: tagihan & pembayaran (Penghasilan Kontrak Siswa)
        // -----------------------------------------------------------------
        $allSiswa = $db->table('siswa')->select('id, kode_jenjang')->get()->getResultArray();
        if (!empty($allSiswa)) {
            $dataTagihan = [];
            $months = [];
            for ($i = 0; $i < 6; $i++) $months[] = date('Y-m', strtotime("2025-07-01 +$i months"));

            foreach ($allSiswa as $s) {
                foreach ($allJenis as $aj) {
                    if ($aj['kode_jenjang'] === $s['kode_jenjang']) {
                        if ($aj['tipe'] === 'bulanan') {
                            foreach ($months as $m) {
                                $dataTagihan[] = [
                                    'kode_jenjang' => $s['kode_jenjang'],
                                    'id_siswa' => $s['id'],
                                    'id_jenis_pembayaran' => $aj['id'],
                                    'deskripsi' => "Iuran SPP Periode " . date('F Y', strtotime($m . "-01")),
                                    'jumlah' => $aj['nominal'],
                                    'tanggal_tagihan' => $m . '-01',
                                    'status' => 'belum_lunas',
                                    'created_at' => Time::now()
                                ];
                            }
                        }
                    }
                }
            }
            foreach (array_chunk($dataTagihan, 200) as $chunk) $db->table('tagihan')->insertBatch($chunk);

            // Simulasi Penerimaan Kas
            $tagihanCreated = $db->table('tagihan')->get()->getResultArray();
            $dataPembayaran = [];
            foreach ($tagihanCreated as $tg) {
                if (rand(1, 10) <= 7) { 
                    $dataPembayaran[] = [
                        'kode_jenjang' => $tg['kode_jenjang'],
                        'id_tagihan' => $tg['id'],
                        'id_user_admin' => $admin['id'],
                        'jumlah_bayar' => $tg['jumlah'],
                        'tanggal_bayar' => date('Y-m-d', strtotime($tg['tanggal_tagihan'] . " +".rand(1,10)." days")),
                        'metode_pembayaran' => 'Transfer',
                        'created_at' => Time::now()
                    ];
                    $db->table('tagihan')->where('id', $tg['id'])->update(['status' => 'lunas']);
                }
            }
            if(!empty($dataPembayaran)) foreach (array_chunk($dataPembayaran, 200) as $chunk) $db->table('pembayaran')->insertBatch($chunk);
        }

        // -----------------------------------------------------------------
        // 6. SEED: pengeluaran (Beban Berbasis ISAK 35)
        // -----------------------------------------------------------------
        $dataPengeluaran = [];
        $coaBeban = ['5-1100', '5-1200', '5-1300', '5-1500', '5-2100'];
        $items = [
            '5-1100' => ['Honor Guru Honorer', 'Gaji Staff Administrasi', 'Tunjangan Wali Kelas'],
            '5-1200' => ['Listrik PLN Sekolah', 'Air PDAM Unit', 'Pembelian Gas Kantin'],
            '5-1300' => ['Langganan Internet Biznet', 'Pulsa Modem Sekolah'],
            '5-1500' => ['Perbaikan AC Kelas', 'Pengecatan Ruang Guru', 'Perbaikan Pagar'],
            '5-2100' => ['Biaya Lomba FLS2N', 'Konsumsi Rapat OSIS', 'Penyelenggaraan Class Meeting']
        ];

        foreach ($months as $m) {
            foreach ($jenjangList as $j) {
                for ($i = 0; $i < 4; $i++) {
                    $kodeCoa = $coaBeban[array_rand($coaBeban)];
                    $dataPengeluaran[] = [
                        'kode_jenjang'      => $j['kode_jenjang'],
                        'id_kategori'       => $getCoaId($kodeCoa),
                        'tanggal'           => $m . '-' . sprintf("%02d", rand(1, 28)),
                        'kategori_manual'   => null,
                        'keterangan'        => $items[$kodeCoa][array_rand($items[$kodeCoa])] . " unit " . $j['kode_jenjang'],
                        'jumlah'            => rand(30, 300) * 10000, 
                        'metode_pembayaran' => 'Tunai',
                        'id_user_input'     => $admin['id'],
                        'created_at'        => Time::now()
                    ];
                }
            }
        }
        $db->table('pengeluaran')->insertBatch($dataPengeluaran);

        echo "SUCCESS: Seeder Keuangan Terupdate (Referensi ISAK 35 & SAK EP).\n";
    }
}