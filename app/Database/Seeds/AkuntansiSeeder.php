<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class AkuntansiSeeder extends Seeder
{
    public function run()
    {
        echo "<pre>\n=======================================================\n";
        echo ">>> MENJALANKAN AKUNTANSI SEEDER (SINGLE COA YAYASAN)\n";
        echo "=======================================================\n";
        
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->db->table('akuntansi_jurnal_detail')->truncate();
        $this->db->table('akuntansi_jurnal')->truncate();
        $this->db->table('akuntansi_coa')->truncate();
        $this->db->table('akuntansi_kategori')->truncate();

        $now = Time::now()->format('Y-m-d H:i:s');

        // 1. SEED KATEGORI AKUN (STANDAR ISAK 35)
        $kategoriData = [
            ['id' => 1, 'kode_jenjang' => 'GLOBAL', 'kode_kategori' => '1', 'nama_kategori' => 'Aset', 'saldo_normal' => 'Debit', 'laporan_tujuan' => 'Neraca'],
            ['id' => 2, 'kode_jenjang' => 'GLOBAL', 'kode_kategori' => '2', 'nama_kategori' => 'Liabilitas', 'saldo_normal' => 'Kredit', 'laporan_tujuan' => 'Neraca'],
            ['id' => 3, 'kode_jenjang' => 'GLOBAL', 'kode_kategori' => '3', 'nama_kategori' => 'Aset Neto', 'saldo_normal' => 'Kredit', 'laporan_tujuan' => 'Neraca'],
            ['id' => 4, 'kode_jenjang' => 'GLOBAL', 'kode_kategori' => '4', 'nama_kategori' => 'Pendapatan', 'saldo_normal' => 'Kredit', 'laporan_tujuan' => 'Aktivitas'],
            ['id' => 5, 'kode_jenjang' => 'GLOBAL', 'kode_kategori' => '5', 'nama_kategori' => 'Beban', 'saldo_normal' => 'Debit', 'laporan_tujuan' => 'Aktivitas'],
        ];
        $this->db->table('akuntansi_kategori')->insertBatch($kategoriData);

        // 2. PEMBUATAN COA TUNGGAL YAYASAN (MURNI GLOBAL & BERSIH)
        // Format Blueprint: [id_kategori, kode_akun, nama_akun, is_parent, index_parent_di_blueprint_ini]
        $blueprint = [
            // Index 0 (Header Aset Lancar)
            [1, '1100', "Aset Lancar", 1, null],
            [1, '1101', "Kas dan setara kas", 0, 0], 
            [1, '1102', "Piutang usaha / Piutang SPP", 0, 0],
            [1, '1103', "Investasi jangka pendek", 0, 0],
            [1, '1104', "Aset lancar lain", 0, 0],

            // Index 5 (Header Aset Tidak Lancar)
            [1, '1200', "Aset Tidak Lancar", 1, null],
            [1, '1201', "Properti investasi", 0, 5], 
            [1, '1202', "Investasi jangka panjang", 0, 5],
            [1, '1203', "Aset tetap (Tanah & Bangunan)", 0, 5],
            [1, '1204', "Akumulasi penyusutan", 0, 5],

            // Index 10 (Header Liabilitas Pendek)
            [2, '2100', "Liabilitas Jangka Pendek", 1, null],
            [2, '2101', "Pendapatan diterima di muka", 0, 10],
            [2, '2102', "Utang jangka pendek / usaha", 0, 10],

            // Index 13 (Header Liabilitas Panjang)
            [2, '2200', "Liabilitas Jangka Panjang", 1, null],
            [2, '2201', "Utang jangka panjang", 0, 13],
            [2, '2202', "Liabilitas imbalan kerja", 0, 13],

            // Index 16 (Header Aset Neto Tanpa Pembatasan)
            [3, '3100', "Aset Neto Tanpa Pembatasan", 1, null],
            [3, '3101', "Surplus akumulasian", 0, 16],
            [3, '3102', "Penghasilan komprehensif lain", 0, 16],

            // Index 19 (Header Aset Neto Dengan Pembatasan)
            [3, '3200', "Aset Neto Dengan Pembatasan", 1, null],
            [3, '3201', "Aset Neto dibatasi tujuan/periode", 0, 19],

            // Index 21 (Header Pendapatan Tanpa Pembatasan)
            [4, '4100', "Pendapatan Tanpa Pembatasan", 1, null],
            [4, '4101', "Pendapatan Jasa Layanan (SPP)", 0, 21],
            [4, '4102', "Sumbangan / Donasi Umum", 0, 21],

            // Index 24 (Header Pendapatan Dengan Pembatasan)
            [4, '4200', "Pendapatan Dengan Pembatasan", 1, null],
            [4, '4201', "Sumbangan Terikat (Wakaf/Beasiswa)", 0, 24],
            
            // Index 26 (Berdiri Sendiri)
            [4, '4300', "Penghasilan investasi", 0, null], 

            // Index 27 (Header Beban Operasional)
            [5, '5100', "Beban Operasional", 1, null],
            [5, '5101', "Beban Gaji & Upah (Kepegawaian)", 0, 27],
            [5, '5102', "Beban Jasa & Profesional", 0, 27],
            [5, '5103', "Beban Administratif & Umum", 0, 27],
            [5, '5104', "Beban Depresiasi / Penyusutan", 0, 27],
            [5, '5105', "Beban Bunga & Lainnya", 0, 27],
        ];

        $allCoa = [];
        $startIndex = 0; // Karena kita menggunakan DB TRUNCATE, id akan dimulai dari 1

        foreach ($blueprint as $item) {
            // Modal Awal Berdiri (Khusus Akun 1101 dan 3101 Yayasan)
            $saldoAwal = 0;
            if ($item[1] === '1101') $saldoAwal = 500000000; // Kas
            if ($item[1] === '3101') $saldoAwal = 500000000; // Ekuitas Awal
            
            // Tentukan Parent ID dinamis berdasarkan Index Database (Auto Increment)
            $parentId = ($item[4] !== null) ? ($startIndex + $item[4] + 1) : null;

            $allCoa[] = [
                'kode_jenjang' => 'GLOBAL', // KUNCI: Paksa HANYA untuk GLOBAL
                'id_kategori'  => $item[0],
                'kode_akun'    => $item[1],
                'nama_akun'    => $item[2], // Nama Akun sudah BERSIH dari nama unit
                'is_parent'    => $item[3],
                'parent_id'    => $parentId,
                'saldo_awal'   => $saldoAwal,
                'is_active'    => 1,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        // Eksekusi Simpan Massal 
        $this->db->table('akuntansi_coa')->insertBatch($allCoa);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
        
        echo " [SUCCESS] BAGAN AKUN (COA) TUNGGAL YAYASAN berhasil di-generate!\n";
        echo "=======================================================\n</pre>\n";
    }
}