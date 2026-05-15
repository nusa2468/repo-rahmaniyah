<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class KerjasamaSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $now = Time::now();

        // Bersihkan data lama untuk menghindari duplikasi saat seeding ulang
        $db->table('kerjasama')->truncate();

        $data = [
            [
                'jenjang'       => 'Global',
                'nama_mitra'    => 'Bank Syariah Indonesia (BSI)',
                'logo'          => null,
                'kategori'      => 'Layanan Keuangan',
                'alamat'        => 'Jl. Merdeka Barat No. 1, Jakarta Pusat',
                'kontak_person' => 'Ibu Siti Aminah (Manager Edukasi)',
                'no_telp'       => '021-1234567',
                'website'       => 'https://www.bankbsi.co.id',
                'tgl_mulai'     => '2023-01-01',
                'tgl_akhir'     => '2026-01-01',
                'file_mou'      => 'mou_bsi_2023.pdf',
                'program'       => 'Tabungan Siswa, Payroll Karyawan, Beasiswa Prestasi',
                'target_capaian'=> 'Digitalisasi pembayaran SPP 100% dan pengelolaan payroll seluruh karyawan.',
                'deskripsi'     => 'Kerjasama pengelolaan payroll karyawan, tabungan siswa, dan sistem pembayaran SPP terintegrasi.',
                'status'        => 'aktif',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'jenjang'       => 'Global',
                'nama_mitra'    => 'Google for Education',
                'logo'          => null,
                'kategori'      => 'Teknologi Pendidikan',
                'alamat'        => 'Pacific Century Place, Lt. 45, Jakarta',
                'kontak_person' => 'Bpk. David (Cloud Consultant)',
                'no_telp'       => '0812-9876-5432',
                'website'       => 'https://edu.google.com',
                'tgl_mulai'     => '2024-05-10',
                'tgl_akhir'     => '2029-05-10',
                'file_mou'      => 'google_edu_agreement.pdf',
                'program'       => 'Google Workspace, Pelatihan Guru, Sertifikasi Siswa',
                'target_capaian'=> 'Implementasi Google Classroom di seluruh kelas dan sertifikasi internasional untuk 50 guru.',
                'deskripsi'     => 'Penyediaan lisensi Google Workspace for Education untuk mendukung pembelajaran digital di seluruh unit.',
                'status'        => 'aktif',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'jenjang'       => 'SD',
                'nama_mitra'    => 'Kebun Binatang Ragunan',
                'logo'          => null,
                'kategori'      => 'Wisata Edukasi',
                'alamat'        => 'Jl. Harsono RM No. 1, Ragunan, Jakarta Selatan',
                'kontak_person' => 'Humas Ragunan',
                'no_telp'       => '021-78847114',
                'website'       => 'https://ragunanzoo.jakarta.go.id',
                'tgl_mulai'     => '2025-01-01',
                'tgl_akhir'     => '2025-12-31',
                'file_mou'      => 'pks_ragunan_sd.pdf',
                'program'       => 'Field Trip, Edukasi Konservasi Satwa',
                'target_capaian'=> 'Pelaksanaan kegiatan field trip edukatif untuk minimal 300 siswa SD per tahun.',
                'deskripsi'     => 'Kemitraan untuk kegiatan field trip tahunan dan edukasi konservasi satwa bagi siswa SD.',
                'status'        => 'aktif',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'jenjang'       => 'SMP',
                'nama_mitra'    => 'British Council Indonesia',
                'logo'          => null,
                'kategori'      => 'Bahasa & Budaya',
                'alamat'        => 'Office 8, Lt. 9, Sudirman Central Business District',
                'kontak_person' => 'Ms. Sarah (Language Dept)',
                'no_telp'       => '021-29333470',
                'website'       => 'https://www.britishcouncil.id',
                'tgl_mulai'     => '2023-08-15',
                'tgl_akhir'     => '2026-08-15',
                'file_mou'      => 'bc_english_cert.pdf',
                'program'       => 'Sertifikasi Bahasa, Guru Tamu (Native Speaker)',
                'target_capaian'=> 'Peningkatan skor rata-rata literasi bahasa Inggris siswa kelas 9 sebesar 20%.',
                'deskripsi'     => 'Program peningkatan literasi bahasa Inggris dan ujian sertifikasi internasional untuk siswa SMP.',
                'status'        => 'aktif',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'jenjang'       => 'SMA',
                'nama_mitra'    => 'Universitas Indonesia (UI)',
                'logo'          => null,
                'kategori'      => 'Pendidikan Tinggi',
                'alamat'        => 'Kampus UI Depok, Jawa Barat',
                'kontak_person' => 'Bpk. Dr. Heru (Direktorat Pendidikan)',
                'no_telp'       => '021-7867222',
                'website'       => 'https://www.ui.ac.id',
                'tgl_mulai'     => '2022-10-20',
                'tgl_akhir'     => '2027-10-20',
                'file_mou'      => 'mou_ui_pendidikan.pdf',
                'program'       => 'Sosialisasi Kampus, Penelitian Bersama, Guru Magang',
                'target_capaian'=> 'Peningkatan jumlah lulusan yang diterima di perguruan tinggi negeri (PTN) sebesar 15%.',
                'deskripsi'     => 'Kerjasama bimbingan masuk perguruan tinggi, sosialisasi jalur masuk, dan penelitian bersama guru SMA.',
                'status'        => 'aktif',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'jenjang'       => 'SMA',
                'nama_mitra'    => 'PT. Telkom Indonesia',
                'logo'          => null,
                'kategori'      => 'Teknologi & CSR',
                'alamat'        => 'Telkom Landmark Tower, Jakarta',
                'kontak_person' => 'Bpk. Ahmad (CSR Division)',
                'no_telp'       => '0811-000-000',
                'website'       => 'https://www.telkom.co.id',
                'tgl_mulai'     => '2025-01-01',
                'tgl_akhir'     => '2026-01-01',
                'file_mou'      => 'telkom_digital_lab.pdf',
                'program'       => 'PKL / Prakerin, Laboratorium Fiber Optik, Donasi Infrastruktur',
                'target_capaian'=> 'Penyediaan 1 unit Lab Fiber Optik dan penyerapan magang untuk 10 siswa per tahun.',
                'deskripsi'     => 'Penyediaan fasilitas laboratorium fiber optik dan beasiswa bagi siswa berprestasi di unit SMA.',
                'status'        => 'aktif',
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        // Insert Batch
        $db->table('kerjasama')->insertBatch($data);

        echo "SUCCESS: 6 Data Kerjasama lengkap dengan Target Capaian berhasil di-seed.\n";
    }
}