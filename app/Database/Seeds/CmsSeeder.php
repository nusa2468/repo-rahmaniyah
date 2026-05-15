<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * CmsSeeder
 * Mengisi data dummy awal untuk modul CMS (Berita, Pengumuman, Agenda, Galeri).
 * Pastikan Migration CreateCmsTables sudah dijalankan.
 */
class CmsSeeder extends Seeder
{
    public function run()
    {
        $db  = \Config\Database::connect();
        $now = Time::now();

        // 1. Bersihkan Data Lama
        // Matikan cek Foreign Key sementara untuk melakukan Truncate
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        
        $tables = ['foto', 'album_foto', 'agenda', 'pengumuman', 'berita'];
        foreach ($tables as $table) {
            $db->table($table)->truncate();
        }
        
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        echo "Cleaning old CMS data... Done.\n";

        // 2. Seed Data Berita
        $berita = [
            [
                'kode_jenjang' => null, // Global / Yayasan
                'judul'        => 'Selamat Datang di Portal Resmi Yayasan',
                'slug'         => 'selamat-datang-di-portal-resmi-yayasan',
                'konten'       => '<p>Selamat datang di website resmi yayasan kami. Kami berkomitmen untuk memberikan layanan pendidikan terbaik yang terintegrasi dari TK hingga SMA. Website ini menjadi pusat informasi kegiatan dan prestasi seluruh unit pendidikan di bawah naungan yayasan.</p>',
                'gambar'       => 'default_welcome.jpg',
                'status'       => 'published',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'kode_jenjang' => 'SD',
                'judul'        => 'Prestasi Membanggakan: Juara 1 Lomba Sains Daerah',
                'slug'         => 'juara-1-lomba-sains-daerah-sd',
                'konten'       => '<p>Selamat kepada ananda Budi Santoso dari kelas 5A yang berhasil meraih medali emas pada Olimpiade Sains tingkat Kabupaten. Semoga prestasi ini menjadi motivasi bagi siswa lainnya.</p>',
                'gambar'       => 'juara_sains.jpg',
                'status'       => 'published',
                'created_at'   => $now->subDays(2), // 2 hari lalu
                'updated_at'   => $now->subDays(2),
            ],
            [
                'kode_jenjang' => 'SMA',
                'judul'        => 'Penerimaan Peserta Didik Baru (PPDB) Telah Dibuka',
                'slug'         => 'ppdb-sma-dibuka',
                'konten'       => '<p>Unit SMA membuka pendaftaran gelombang pertama mulai tanggal 1 Januari. Segera daftarkan diri Anda melalui menu PPDB Online di website ini.</p>',
                'gambar'       => 'ppdb_banner.jpg',
                'status'       => 'published',
                'created_at'   => $now->subDays(5),
                'updated_at'   => $now->subDays(5),
            ],
            [
                'kode_jenjang' => 'SMP',
                'judul'        => 'Jadwal Ujian Tengah Semester Genap',
                'slug'         => 'jadwal-uts-genap-smp',
                'konten'       => '<p>Diberitahukan kepada seluruh siswa SMP bahwa UTS Genap akan dilaksanakan mulai senin depan. Harap persiapkan diri dengan baik.</p>',
                'gambar'       => null,
                'status'       => 'draft', // Draft belum tampil di publik
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];
        $db->table('berita')->insertBatch($berita);

        // 3. Seed Data Pengumuman
        $pengumuman = [
            [
                'kode_jenjang'     => null, // Global
                'judul'            => 'Libur Hari Raya Idul Fitri',
                'slug'             => 'libur-hari-raya-idul-fitri',
                'konten'           => 'Kegiatan belajar mengajar diliburkan mulai tanggal 20 April hingga 30 April dalam rangka Hari Raya Idul Fitri.',
                'status'           => 'published',
                'tanggal_berakhir' => $now->addMonths(1)->toDateString(),
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'kode_jenjang'     => 'TK',
                'judul'            => 'Wajib Membawa Bekal Sehat',
                'slug'             => 'wajib-bekal-sehat-tk',
                'konten'           => 'Mulai besok, seluruh siswa TK diwajibkan membawa bekal 4 sehat 5 sempurna setiap hari Jumat.',
                'status'           => 'published',
                // FIX: Mengganti addWeeks(2) dengan addDays(14)
                'tanggal_berakhir' => $now->addDays(14)->toDateString(),
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];
        $db->table('pengumuman')->insertBatch($pengumuman);

        // 4. Seed Data Agenda
        $agenda = [
            [
                'kode_jenjang'    => null,
                'nama_kegiatan'   => 'Rapat Kerja Tahunan Yayasan',
                'slug'            => 'raker-tahunan-yayasan',
                'tanggal_mulai'   => $now->addDays(10)->toDateTimeString(),
                'tanggal_selesai' => $now->addDays(10)->addHours(4)->toDateTimeString(),
                'tempat'          => 'Aula Utama Gedung A',
                'keterangan'      => 'Wajib dihadiri seluruh kepala sekolah dan staf manajemen.',
                'status'          => 'published',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'kode_jenjang'    => 'SMA',
                'nama_kegiatan'   => 'Seminar Karir & Universitas',
                'slug'            => 'seminar-karir-sma',
                'tanggal_mulai'   => $now->addDays(5)->toDateTimeString(),
                'tanggal_selesai' => $now->addDays(5)->addHours(3)->toDateTimeString(),
                'tempat'          => 'Ruang Audio Visual SMA',
                'keterangan'      => 'Khusus siswa kelas 12.',
                'status'          => 'published',
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];
        $db->table('agenda')->insertBatch($agenda);

        // 5. Seed Data Album Foto
        $album = [
            [
                'kode_jenjang' => null,
                'judul'        => 'Perayaan HUT RI ke-79',
                'slug'         => 'perayaan-hut-ri-79',
                'deskripsi'    => 'Dokumentasi kegiatan upacara dan lomba 17 Agustus di lingkungan yayasan.',
                'cover'        => 'cover_17an.jpg',
                'status'       => 'publik',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'kode_jenjang' => 'SD',
                'judul'        => 'Kegiatan Pramuka Persami',
                'slug'         => 'kegiatan-pramuka-persami',
                'deskripsi'    => 'Perkemahan Sabtu Minggu siswa kelas 4, 5, dan 6 SD.',
                'cover'        => 'cover_pramuka.jpg',
                'status'       => 'publik',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];
        $db->table('album_foto')->insertBatch($album);

        // 6. Seed Data Foto (Relasi ke Album)
        // Ambil ID album yang baru diinsert untuk relasi FK
        $albumHut      = $db->table('album_foto')->where('slug', 'perayaan-hut-ri-79')->get()->getRow();
        $albumPramuka = $db->table('album_foto')->where('slug', 'kegiatan-pramuka-persami')->get()->getRow();

        if ($albumHut && $albumPramuka) {
            $foto = [
                // Foto HUT RI
                [
                    'kode_jenjang' => $albumHut->kode_jenjang, // Insert kode jenjang dari album
                    'id_album'     => $albumHut->id, 
                    'file_foto'    => 'upacara_bendera.jpg', 
                    'caption'      => 'Petugas Paskibraka', 
                    'created_at'   => $now, 
                    'updated_at'   => $now
                ],
                [
                    'kode_jenjang' => $albumHut->kode_jenjang, 
                    'id_album'     => $albumHut->id, 
                    'file_foto'    => 'lomba_tarik_tambang.jpg', 
                    'caption'      => 'Keseruan Lomba Guru', 
                    'created_at'   => $now, 
                    'updated_at'   => $now
                ],
                // Foto Pramuka
                [
                    'kode_jenjang' => $albumPramuka->kode_jenjang, 
                    'id_album'     => $albumPramuka->id, 
                    'file_foto'    => 'api_unggun.jpg', 
                    'caption'      => 'Malam Api Unggun', 
                    'created_at'   => $now, 
                    'updated_at'   => $now
                ],
                [
                    'kode_jenjang' => $albumPramuka->kode_jenjang, 
                    'id_album'     => $albumPramuka->id, 
                    'file_foto'    => 'tenda.jpg', 
                    'caption'      => 'Mendirikan Tenda', 
                    'created_at'   => $now, 
                    'updated_at'   => $now
                ],
            ];
            $db->table('foto')->insertBatch($foto);
        }

        echo "SUCCESS: CmsSeeder berhasil dijalankan. Data dummy Berita, Pengumuman, Agenda, dan Galeri telah dibuat.\n";
    }
}