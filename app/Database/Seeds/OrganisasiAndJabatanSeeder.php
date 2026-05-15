<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class OrganisasiAndJabatanSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now();
        
        // Matikan check foreign key agar proses truncate lancar
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');

        // ==========================================
        // 1. DATA MASTER JABATAN
        // ==========================================
        $this->db->table('jabatan')->truncate();
        $jabatanData = [
            // Jabatan Level Yayasan / Global
            ['id' => 1, 'nama_jabatan' => 'Ketua Dewan Pembina', 'kode_jenjang' => 'Global', 'level' => 1, 'atasan' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'nama_jabatan' => 'Ketua Umum Yayasan', 'kode_jenjang' => 'Global', 'level' => 2, 'atasan' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'nama_jabatan' => 'Pengawas Internal', 'kode_jenjang' => 'Global', 'level' => 2, 'atasan' => 1, 'created_at' => $now, 'updated_at' => $now],
            
            // Jabatan Level Unit (Contoh: SMA)
            ['id' => 4, 'nama_jabatan' => 'Kepala Sekolah', 'kode_jenjang' => 'SMA', 'level' => 3, 'atasan' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'nama_jabatan' => 'Wakil Kepala Sekolah', 'kode_jenjang' => 'SMA', 'level' => 4, 'atasan' => 4, 'created_at' => $now, 'updated_at' => $now],
        ];
        $this->db->table('jabatan')->insertBatch($jabatanData);

        // ==========================================
        // 2. DATA PERSONEL ORGANISASI
        // ==========================================
        $this->db->table('organisasi')->truncate();
        
        $organisasiData = [
            // --- TINGKAT YAYASAN (Manual Input / Tokoh Eksternal) ---
            [
                'jenis_organisasi' => 'Pembina',
                'kode_jenjang'     => 'Global',
                'jabatan_id'       => 1, // Ketua Dewan Pembina
                'parent_id'        => null,
                'id_pegawai'       => null, // Tidak ada di tabel pegawai
                'nama_pengampu'    => 'H. M. Sulaiman Al-Fatih',
                'nip'              => null,
                'urutan'           => 1,
                'status'           => 'aktif',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'jenis_organisasi' => 'Pengurus',
                'kode_jenjang'     => 'Global',
                'jabatan_id'       => 2, // Ketua Umum
                'parent_id'        => 1, // Bawahan Pembina (Secara Hierarki)
                'id_pegawai'       => null,
                'nama_pengampu'    => 'Ir. H. Bambang Susanto',
                'nip'              => null,
                'urutan'           => 2,
                'status'           => 'aktif',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'jenis_organisasi' => 'Pengawas',
                'kode_jenjang'     => 'Global',
                'jabatan_id'       => 3, // Pengawas
                'parent_id'        => 1,
                'id_pegawai'       => null,
                'nama_pengampu'    => 'Hj. Siti Aminah, M.E.',
                'nip'              => null,
                'urutan'           => 3,
                'status'           => 'aktif',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],

            // --- TINGKAT SEKOLAH (Mengambil dari Tabel Pegawai) ---
            [
                'jenis_organisasi' => 'Sekolah',
                'kode_jenjang'     => 'SMA',
                'jabatan_id'       => 4, // Kepala Sekolah
                'parent_id'        => 2, // Bertanggung jawab ke Ketua Yayasan
                
                // Menggunakan ID Pegawai 1 (Pastikan Pegawai ID 1 ada dari PegawaiSeeder)
                'id_pegawai'       => 1, 
                
                // Kosongkan nama_pengampu agar sistem otomatis mengambil nama dari tabel pegawai
                'nama_pengampu'    => null, 
                'nip'              => null,
                'urutan'           => 10,
                'status'           => 'aktif',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        $this->db->table('organisasi')->insertBatch($organisasiData);
        
        // Aktifkan kembali check foreign key
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');
    }
}