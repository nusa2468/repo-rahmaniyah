<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * PpdbSeeder - Menghasilkan data dummy untuk modul PPDB & Afiliasi.
 * UPDATED: Menambahkan 'user_id' yang sebelumnya terlewat dan sinkronisasi struktur tabel terbaru.
 */
class PpdbSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now('Asia/Jakarta', 'id_ID');

        // --- 1. MEMBERSIHKAN TABEL (Mencegah Duplicate Entry pada Kolom Unique) ---
        // Nonaktifkan foreign key checks untuk truncate aman
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        $this->db->table('affiliates')->truncate();
        $this->db->table('pendaftar_biodata')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');

        // --- 2. SEEDING DATA AFILIASI (MASTER AGEN MARKETING) ---
        $affiliateData = [
            [
                'nama_agen'        => 'JAKA PERDANA, M.Pd',
                'kode_agen'        => 'MKT-001',
                'kode_jenjang'     => 'GLOBAL', // Bisa lintas unit
                'no_hp'            => '081234567890',
                'email'            => 'budi.marketing@sekolah.sch.id',
                'alamat'           => 'Jl. Merpati No. 45, Jakarta Selatan',
                'status'           => 'Aktif',
                'metode_agen'      => '4P Strategy',
                'target_pendaftar' => 3,
                'fee_per_siswa'    => 500000.00,
                'nama_bank'        => 'BANK MANDIRI',
                'nomor_rekening'   => '1234567890',
                'nama_rekening'    => 'BUDI SANTOSO',
                'catatan'          => 'Top performer tahun lalu',
                'created_at'       => $now->toDateTimeString(),
                'updated_at'       => $now->toDateTimeString(),
            ],
            [
                'nama_agen'        => 'SITI AMINAH',
                'kode_agen'        => 'MKT-002',
                'kode_jenjang'     => 'GLOBAL',
                'no_hp'            => '085711223344',
                'email'            => 'siti.ads@gmail.com',
                'alamat'           => 'Komp. Gading Serpong Blok A1, Tangerang',
                'status'           => 'Aktif',
                'metode_agen'      => 'Digital Ads',
                'target_pendaftar' => 3,
                'fee_per_siswa'    => 500000.00,
                'nama_bank'        => 'BANK BRI',
                'nomor_rekening'   => '0987654321',
                'nama_rekening'    => 'SITI AMINAH',
                'catatan'          => 'Spesialis Facebook & IG Ads',
                'created_at'       => $now->toDateTimeString(),
                'updated_at'       => $now->toDateTimeString(),
            ],
            [
                'nama_agen'        => 'EKO PRASETYO',
                'kode_agen'        => 'MKT-003',
                'kode_jenjang'     => 'SMA', // Khusus rekrut anak SMA
                'no_hp'            => '082199887766',
                'email'            => 'eko.alumni@yahoo.com',
                'alamat'           => 'Jl. Kenanga No. 12, Bekasi',
                'status'           => 'Aktif',
                'metode_agen'      => 'Alumni Network',
                'target_pendaftar' => 3,
                'fee_per_siswa'    => 500000.00,
                'nama_bank'        => 'BANK BCA',
                'nomor_rekening'   => '7788990011',
                'nama_rekening'    => 'EKO PRASETYO',
                'catatan'          => 'Ketua Ikatan Alumni 2010',
                'created_at'       => $now->toDateTimeString(),
                'updated_at'       => $now->toDateTimeString(),
            ],
        ];

        $this->db->table('affiliates')->insertBatch($affiliateData);

        // --- 3. SEEDING DATA PENDAFTAR (BIODATA SISWA) ---
        // Menambahkan 'user_id' untuk relasi akun
        $ppdbData = [
            [
                'no_pendaftaran'    => 'PPDB-2025-001',
                'user_id'           => 101, // ID User Dummy
                'kode_jenjang'      => 'SMA',
                'tahun_ajaran'      => '2025/2026',
                'nama_lengkap'      => 'MUHAMMAD RIFQI',
                'nik'               => '3201010101010011',
                'nisn'              => '0091234511',
                'jenis_kelamin'     => 'L',
                'tempat_lahir'      => 'Jakarta',
                'tanggal_lahir'     => '2009-02-12',
                'alamat_lengkap'    => 'Jl. Kebagusan Raya No. 10, Pasar Minggu, Jakarta Selatan',
                'no_hp_whatsapp'    => '081299887766',
                'asal_sekolah'      => 'SMP NEGERI 1 JAKARTA',
                'nama_ayah'         => 'H. Abdullah',
                'nama_ibu'          => 'Hj. Siti Rohmah',
                'jalur_masuk'       => 'Zonasi',
                'skor_akhir'        => 88.50,
                'status_seleksi'    => 'Lolos',
                'status_pembayaran' => 'Lunas',
                'metode_bayar'      => 'Transfer Bank',
                'bukti_setor'       => 'tf_rifqi_lunas.jpg',
                'kode_afiliasi'     => 'MKT-001',
                'nominal_fee'       => 500000.00,
                'status_fee'        => 'Dibayar',
                'created_at'        => $now->toDateTimeString(),
                'updated_at'        => $now->toDateTimeString(),
            ],
            [
                'no_pendaftaran'    => 'PPDB-2025-002',
                'user_id'           => 102, // ID User Dummy
                'kode_jenjang'      => 'SMA',
                'tahun_ajaran'      => '2025/2026',
                'nama_lengkap'      => 'AISYAH PUTRI',
                'nik'               => '3201010101010022',
                'nisn'              => '0091234522',
                'jenis_kelamin'     => 'P',
                'tempat_lahir'      => 'Bogor',
                'tanggal_lahir'     => '2009-05-20',
                'alamat_lengkap'    => 'Perumahan Sentul City Cluster B, Bogor',
                'no_hp_whatsapp'    => '085677889900',
                'asal_sekolah'      => 'MTS NEGERI 2 BOGOR',
                'nama_ayah'         => 'Ir. Budi Santoso',
                'nama_ibu'          => 'Dr. Ratna Sari',
                'jalur_masuk'       => 'Prestasi',
                'skor_akhir'        => 94.20,
                'status_seleksi'    => 'Lolos',
                'status_pembayaran' => 'Lunas',
                'metode_bayar'      => 'Transfer Bank',
                'bukti_setor'       => 'tf_aisyah_lunas.jpg',
                'kode_afiliasi'     => 'MKT-001',
                'nominal_fee'       => 500000.00,
                'status_fee'        => 'Pending',
                'created_at'        => $now->toDateTimeString(),
                'updated_at'        => $now->toDateTimeString(),
            ],
            [
                'no_pendaftaran'    => 'PPDB-2025-003',
                'user_id'           => 103, // ID User Dummy
                'kode_jenjang'      => 'SMP',
                'tahun_ajaran'      => '2025/2026',
                'nama_lengkap'      => 'DIMAS ADITYA',
                'nik'               => '3201010101010033',
                'nisn'              => '0091234533',
                'jenis_kelamin'     => 'L',
                'tempat_lahir'      => 'Depok',
                'tanggal_lahir'     => '2012-11-05',
                'alamat_lengkap'    => 'Jl. Margonda Raya No. 55, Depok',
                'no_hp_whatsapp'    => '087811223344',
                'asal_sekolah'      => 'SD PELITA BANGSA',
                'nama_ayah'         => 'Joko Susilo',
                'nama_ibu'          => 'Sri Wahyuni',
                'jalur_masuk'       => 'Afirmasi',
                'skor_akhir'        => 79.00,
                'status_seleksi'    => 'Pending',
                'status_pembayaran' => 'Belum Bayar',
                'metode_bayar'      => null,
                'bukti_setor'       => null,
                'kode_afiliasi'     => 'MKT-002',
                'nominal_fee'       => 500000.00,
                'status_fee'        => 'Pending',
                'created_at'        => $now->toDateTimeString(),
                'updated_at'        => $now->toDateTimeString(),
            ],
            [
                'no_pendaftaran'    => 'PPDB-2025-004',
                'user_id'           => 104, // ID User Dummy
                'kode_jenjang'      => 'SMP',
                'tahun_ajaran'      => '2025/2026',
                'nama_lengkap'      => 'SALSABILA KHANSA',
                'nik'               => '3201010101010044',
                'nisn'              => '0091234544',
                'jenis_kelamin'     => 'P',
                'tempat_lahir'      => 'Jakarta',
                'tanggal_lahir'     => '2012-08-15',
                'alamat_lengkap'    => 'Jl. Jatiwaringin No. 8, Bekasi',
                'no_hp_whatsapp'    => '081344556677',
                'asal_sekolah'      => 'SDN 5 BEKASI',
                'nama_ayah'         => 'Rudi Hartono',
                'nama_ibu'          => 'Maya Indah',
                'jalur_masuk'       => 'Umum',
                'skor_akhir'        => 82.50,
                'status_seleksi'    => 'Gagal',
                'status_pembayaran' => 'Lunas',
                'metode_bayar'      => 'Tunai',
                'bukti_setor'       => 'kwitansi_tunai_004.pdf',
                'kode_afiliasi'     => 'MKT-003',
                'nominal_fee'       => 500000.00,
                'status_fee'        => 'Pending',
                'created_at'        => $now->toDateTimeString(),
                'updated_at'        => $now->toDateTimeString(),
            ],
            [
                'no_pendaftaran'    => 'PPDB-2025-005',
                'user_id'           => 105, // ID User Dummy
                'kode_jenjang'      => 'SD',
                'tahun_ajaran'      => '2025/2026',
                'nama_lengkap'      => 'RAYHAN RAMADHAN',
                'nik'               => '3201010101010055',
                'nisn'              => '0091234555',
                'jenis_kelamin'     => 'L',
                'tempat_lahir'      => 'Tangerang',
                'tanggal_lahir'     => '2018-03-30',
                'alamat_lengkap'    => 'Jl. Raya Serpong KM 7, Tangerang Selatan',
                'no_hp_whatsapp'    => '081599887700',
                'asal_sekolah'      => 'TK ISLAM CIKAL',
                'nama_ayah'         => 'Andi Wijaya',
                'nama_ibu'          => 'Lina Marlina',
                'jalur_masuk'       => 'Zonasi',
                'skor_akhir'        => 85.00,
                'status_seleksi'    => 'Pending',
                'status_pembayaran' => 'Belum Bayar',
                'metode_bayar'      => null,
                'bukti_setor'       => null,
                'kode_afiliasi'     => null,
                'nominal_fee'       => 0.00,
                'status_fee'        => 'Pending',
                'created_at'        => $now->toDateTimeString(),
                'updated_at'        => $now->toDateTimeString(),
            ],
            [
                'no_pendaftaran'    => 'PPDB-2025-006',
                'user_id'           => 106, // ID User Dummy
                'kode_jenjang'      => 'TK',
                'tahun_ajaran'      => '2025/2026',
                'nama_lengkap'      => 'FAREL PRAYOGA',
                'nik'               => '3201010101010066',
                'nisn'              => '0091234566',
                'jenis_kelamin'     => 'L',
                'tempat_lahir'      => 'Jakarta',
                'tanggal_lahir'     => '2020-07-12',
                'alamat_lengkap'    => 'Jl. Cilandak KKO No. 22, Jakarta Selatan',
                'no_hp_whatsapp'    => '081288990011',
                'asal_sekolah'      => '-',
                'nama_ayah'         => 'Teguh Prasetyo',
                'nama_ibu'          => 'Dian Sastro',
                'jalur_masuk'       => 'Reguler',
                'skor_akhir'        => 81.20,
                'status_seleksi'    => 'Lolos',
                'status_pembayaran' => 'Lunas',
                'metode_bayar'      => 'Transfer Bank',
                'bukti_setor'       => 'tf_farel_lunas.png',
                'kode_afiliasi'     => 'MKT-002',
                'nominal_fee'       => 500000.00,
                'status_fee'        => 'Dibayar',
                'created_at'        => $now->toDateTimeString(),
                'updated_at'        => $now->toDateTimeString(),
            ],
        ];

        $this->db->table('pendaftar_biodata')->insertBatch($ppdbData);
    }
}