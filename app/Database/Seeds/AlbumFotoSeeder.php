<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class AlbumFotoSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Bersihkan data lama
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        $db->table('foto')->truncate();
        $db->table('album_foto')->truncate();
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            [
                'jenjang'    => 'SD',
                'judul'      => 'Gelar Karya P5 SD',
                'slug'       => 'gelar-karya-p5-sd',
                'deskripsi'  => 'Pameran hasil karya siswa SD.',
                'cover'      => 'https://placehold.co/600x400/28a745/ffffff?text=SD+Gallery',
                'status'     => 'publik',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'jenjang'    => 'SMP',
                'judul'      => 'Latihan Dasar Kepemimpinan SMP',
                'slug'       => 'ldks-smp-2025',
                'deskripsi'  => 'Kegiatan pembentukan karakter OSIS SMP.',
                'cover'      => 'https://placehold.co/600x400/17a2b8/ffffff?text=SMP+Gallery',
                'status'     => 'publik',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'jenjang'    => 'SMA',
                'judul'      => 'Wisuda Angkatan XV SMA',
                'slug'       => 'wisuda-sma-xv',
                'deskripsi'  => 'Pelepasan siswa kelas XII SMA.',
                'cover'      => 'https://placehold.co/600x400/007bff/ffffff?text=SMA+Gallery',
                'status'     => 'publik',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
            [
                'jenjang'    => 'Global',
                'judul'      => 'Rapat Kerja Yayasan',
                'slug'       => 'raker-yayasan-2025',
                'deskripsi'  => 'Agenda tahunan seluruh unit pendidikan.',
                'cover'      => 'https://placehold.co/600x400/6c757d/ffffff?text=Global+Gallery',
                'status'     => 'publik',
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ],
        ];

        $db->table('album_foto')->insertBatch($data);

        echo "SUCCESS: 4 Data Album (SD/SMP/SMA/Global) berhasil di-seed.\n";
    }
}