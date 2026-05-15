<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JenjangSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama_jenjang' => 'Taman Kanak-Kanak',
                'kode_jenjang' => 'TK',
                'keterangan'   => 'Unit Sekolah TK (Pre Primary School)',
                'urutan'       => 1,
                'status'       => 'aktif',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'nama_jenjang' => 'Sekolah Dasar',
                'kode_jenjang' => 'SD',
                'keterangan'   => 'Unit Sekolah Dasar (Primary School)',
                'urutan'       => 2,
                'status'       => 'aktif',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'nama_jenjang' => 'Sekolah Menengah Pertama',
                'kode_jenjang' => 'SMP',
                'keterangan'   => 'Unit Sekolah Menengah Pertama (Junior High)',
                'urutan'       => 3,
                'status'       => 'aktif',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'nama_jenjang' => 'Sekolah Menengah Atas',
                'kode_jenjang' => 'SMA',
                'keterangan'   => 'Unit Sekolah Menengah Atas (Senior High)',
                'urutan'       => 4,
                'status'       => 'aktif',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ];

        // Menggunakan replace agar jika seeder dijalankan ulang tidak terjadi duplikat/error
        foreach ($data as $row) {
            $this->db->table('jenjang_sekolah')->replace($row);
        }
        
        echo " [OK] Master Jenjang (SD, SMP, SMA) berhasil disinkronkan.\n";
    }
}