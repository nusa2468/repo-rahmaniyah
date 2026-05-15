<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class KenaikanKelasSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $faker = \Faker\Factory::create('id_ID');

        echo "\n>>> Menjalankan Seeder Kenaikan Kelas (Ref: Enrollment)...\n";

        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        $db->table('kenaikan_kelas')->truncate();
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil data enrollment yang ada
        $enrollments = $db->table('siswa_enrollment')->limit(50)->get()->getResultArray();

        if (empty($enrollments)) {
            echo " [SKIP] Tidak ada data enrollment.\n";
            return;
        }

        $batch = [];
        foreach ($enrollments as $row) {
            $batch[] = [
                'id_siswa'           => $row['id_siswa'],
                'id_enrollment_lama' => $row['id'],
                'status_kenaikan'    => $faker->randomElement(['Naik', 'Tinggal', 'Lulus']),
                'tanggal_keputusan'  => date('Y-m-d'),
                'catatan_guru'       => $faker->sentence(3),
                'id_operator'        => 1,
                'created_at'         => Time::now()->toDateTimeString(),
            ];
        }

        $db->table('kenaikan_kelas')->insertBatch($batch);
        echo " [OK] Berhasil memasukkan " . count($batch) . " data.\n";
    }
}