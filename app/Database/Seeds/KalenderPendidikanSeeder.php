<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * Seeder Kalender Pendidikan - Versi Unit-Centric
 * Global (null) dikosongkan dari aktivitas karena bersifat Agregat/Formalitas Yayasan.
 */
class KalenderPendidikanSeeder extends Seeder
{
    public function run()
    {
        $tableName = 'kalender_pendidikan'; 
        $db = $this->db;
        $time = Time::now()->toDateTimeString();

        if (!$db->tableExists($tableName)) {
            echo "Peringatan: Tabel '{$tableName}' tidak ditemukan.\n";
            return;
        }
        
        $tahun_ajaran_id = 1;

        // Semua aktivitas kini dipetakan langsung ke Unit Teknis
        $data = [
            // UNIT: SD
            [
                'kode_jenjang'    => 'SD',
                'title'           => 'MPLS Siswa Baru SD',
                'start'           => '2025-07-14 07:00:00',
                'end'             => '2025-07-16 16:00:00', 
                'color'           => '#4e73df',
                'keterangan'      => 'Masa Pengenalan Lingkungan Sekolah khusus jenjang SD.',
                'tahun_ajaran_id' => $tahun_ajaran_id,
                'created_at'      => $time, 'updated_at' => $time,
            ],
            // UNIT: SMP
            [
                'kode_jenjang'    => 'SMP',
                'title'           => 'MPLS Siswa Baru SMP',
                'start'           => '2025-07-14 07:00:00',
                'end'             => '2025-07-16 16:00:00', 
                'color'           => '#1cc88a',
                'keterangan'      => 'Masa Pengenalan Lingkungan Sekolah khusus jenjang SMP.',
                'tahun_ajaran_id' => $tahun_ajaran_id,
                'created_at'      => $time, 'updated_at' => $time,
            ],
            // UNIT: SMA
            [
                'kode_jenjang'    => 'SMA',
                'title'           => 'MPLS Siswa Baru SMA',
                'start'           => '2025-07-14 07:00:00',
                'end'             => '2025-07-16 16:00:00', 
                'color'           => '#f6c23e',
                'keterangan'      => 'Masa Pengenalan Lingkungan Sekolah khusus jenjang SMA.',
                'tahun_ajaran_id' => $tahun_ajaran_id,
                'created_at'      => $time, 'updated_at' => $time,
            ],
            // HARI LIBUR (Tetap diset per unit karena Global hanya agregat)
            [
                'kode_jenjang'    => 'SD',
                'title'           => 'Libur Kemerdekaan RI',
                'start'           => '2025-08-17 00:00:00',
                'end'             => null,
                'color'           => '#e74a3b',
                'keterangan'      => 'Libur Nasional.',
                'tahun_ajaran_id' => $tahun_ajaran_id,
                'created_at'      => $time, 'updated_at' => $time,
            ],
            [
                'kode_jenjang'    => 'SMP',
                'title'           => 'Libur Kemerdekaan RI',
                'start'           => '2025-08-17 00:00:00',
                'end'             => null,
                'color'           => '#e74a3b',
                'keterangan'      => 'Libur Nasional.',
                'tahun_ajaran_id' => $tahun_ajaran_id,
                'created_at'      => $time, 'updated_at' => $time,
            ],
            [
                'kode_jenjang'    => 'SMA',
                'title'           => 'Libur Kemerdekaan RI',
                'start'           => '2025-08-17 00:00:00',
                'end'             => null,
                'color'           => '#e74a3b',
                'keterangan'      => 'Libur Nasional.',
                'tahun_ajaran_id' => $tahun_ajaran_id,
                'created_at'      => $time, 'updated_at' => $time,
            ],
        ];

        $fieldNames = $db->getFieldNames($tableName);
        $dataToInsert = array_map(function($item) use ($fieldNames) {
            foreach ($item as $key => $val) {
                if (!in_array($key, $fieldNames)) unset($item[$key]);
            }
            return $item;
        }, $data);

        $this->db->table($tableName)->insertBatch($dataToInsert);
        echo "Seeding Berhasil: Aktivitas dipetakan ke unit teknis (SD/SMP/SMA).\n";
    }
}