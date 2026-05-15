<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * PegawaiSeeder (Complete)
 * Menghasilkan data dummy untuk tabel 'pegawai', 'riwayat_pendidikan', dan 'pegawai_dokumen'.
 */
class PegawaiSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');
        $time  = Time::now()->toDateTimeString();
        
        // Disable FK Check untuk Truncate
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->table('riwayat_pendidikan')->truncate();
        $this->db->table('pegawai_dokumen')->truncate();
        $this->db->table('pegawai')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $dataPegawai = [];
        $jenjangs    = ['SD', 'SMP', 'SMA'];
        
        // ==========================================
        // 1. PREPARE DATA PEGAWAI (GURU)
        // ==========================================
        foreach ($jenjangs as $jenjang) {
            for ($i = 0; $i < 5; $i++) {
                $gender = $faker->randomElement(['L', 'P']);
                $name   = $gender === 'L' ? $faker->name('male') : $faker->name('female');
                
                $dataPegawai[] = [
                    'kode_jenjang'        => $jenjang,
                    'nama_lengkap'        => $name,
                    'gelar_depan'         => $faker->randomElement(['', 'Drs.', 'Dra.', 'S.Pd.']),
                    'gelar_belakang'      => $faker->randomElement(['M.Pd', 'M.Si', 'Gr.']),
                    'nik'                 => $faker->nik(),
                    'nuptk'               => $faker->numerify('################'),
                    'nip'                 => $faker->boolean(30) ? $faker->numerify('##################') : null,
                    'nipy'                => $faker->numerify('YYJ#########'),
                    'jenis_kelamin'       => $gender,
                    'tempat_lahir'        => $faker->city,
                    'tanggal_lahir'       => $faker->date('Y-m-d', '-25 years'),
                    'nama_ibu_kandung'    => $faker->name('female'),
                    'agama'               => 'Islam',
                    'status_perkawinan'   => 'Kawin',
                    'email'               => $faker->unique()->email,
                    'no_hp'               => $faker->phoneNumber,
                    'alamat_jalan'        => $faker->streetAddress,
                    'status_kepegawaian'  => 'GTY/PTY',
                    'jenis_ptk'           => 'Guru Mapel',
                    'tugas_tambahan'      => $faker->randomElement([null, 'Wali Kelas', 'Pembina OSIS']),
                    'sk_pengangkatan'     => 'SK/' . $jenjang . '/' . $faker->year . '/' . $faker->randomDigit,
                    'tmt_pengangkatan'    => $faker->date('Y-m-d', '-5 years'),
                    'sumber_gaji'         => 'Yayasan',
                    'pendidikan_terakhir' => 'S1',
                    'jenis_pegawai'       => 'guru',
                    'status_aktif'        => 'aktif',
                    'foto'                => 'default.png',
                    'created_at'          => $time,
                    'updated_at'          => $time,
                ];
            }
        }

        // ==========================================
        // 2. PREPARE DATA PEGAWAI (STAFF)
        // ==========================================
        $staffPositions = [
            ['role' => 'staff', 'ptk' => 'Tenaga Administrasi', 'jenjang' => 'GLOBAL'],
            ['role' => 'staff', 'ptk' => 'Kepala TU', 'jenjang' => 'GLOBAL'],
            ['role' => 'penunjang', 'ptk' => 'Petugas Kebersihan', 'jenjang' => 'SD'],
            ['role' => 'penunjang', 'ptk' => 'Satpam', 'jenjang' => 'SMA'],
        ];

        foreach ($staffPositions as $pos) {
            $gender = $faker->randomElement(['L', 'P']);
            $name   = $gender === 'L' ? $faker->name('male') : $faker->name('female');

            $dataPegawai[] = [
                'kode_jenjang'        => $pos['jenjang'],
                'nama_lengkap'        => $name,
                'gelar_depan'         => '',
                'gelar_belakang'      => '',
                'nik'                 => $faker->nik(),
                'nuptk'               => null,
                'nip'                 => null,
                'nipy'                => $faker->numerify('STF#########'),
                'jenis_kelamin'       => $gender,
                'tempat_lahir'        => $faker->city,
                'tanggal_lahir'       => $faker->date('Y-m-d', '-22 years'),
                'nama_ibu_kandung'    => $faker->name('female'),
                'agama'               => 'Islam',
                'status_perkawinan'   => 'Belum Kawin',
                'email'               => $faker->unique()->email,
                'no_hp'               => $faker->phoneNumber,
                'alamat_jalan'        => $faker->address,
                'status_kepegawaian'  => 'Pegawai Tetap Yayasan',
                'jenis_ptk'           => $pos['ptk'],
                'tugas_tambahan'      => null,
                'sk_pengangkatan'     => 'SK/STF/' . $faker->year,
                'tmt_pengangkatan'    => $faker->date('Y-m-d', '-2 years'),
                'sumber_gaji'         => 'Yayasan',
                'pendidikan_terakhir' => $faker->randomElement(['SMA', 'D3', 'S1']),
                'jenis_pegawai'       => $pos['role'],
                'status_aktif'        => 'aktif',
                'foto'                => 'default.png',
                'created_at'          => $time,
                'updated_at'          => $time,
            ];
        }

        // INSERT BATCH PEGAWAI
        $this->db->table('pegawai')->insertBatch($dataPegawai);
        $jumlahPegawai = count($dataPegawai);
        echo "$jumlahPegawai Data Pegawai berhasil di-seed.\n";

        // ==========================================
        // 3. GENERATE CHILD DATA (PENDIDIKAN & DOKUMEN)
        // ==========================================
        // Ambil semua ID yang baru saja diinsert
        $pegawaiIds = $this->db->table('pegawai')->select('id, jenis_pegawai, nama_lengkap')->get()->getResultArray();
        
        $dataPendidikan = [];
        $dataDokumen    = [];

        foreach ($pegawaiIds as $p) {
            $id = $p['id'];
            
            // --- A. RIWAYAT PENDIDIKAN ---
            // Guru wajib S1, Staff bisa SMA/D3
            if ($p['jenis_pegawai'] == 'guru') {
                // S1
                $thnLulus = $faker->year;
                $dataPendidikan[] = [
                    'id_pegawai'   => $id,
                    'jenjang'      => 'S1',
                    'nama_sekolah' => 'Universitas ' . $faker->city,
                    'jurusan'      => 'Pendidikan ' . $faker->randomElement(['Matematika', 'Bahasa Inggris', 'Biologi', 'Fisika']),
                    'tahun_masuk'  => $thnLulus - 4,
                    'tahun_lulus'  => $thnLulus,
                    'nilai_akhir'  => $faker->randomFloat(2, 3.00, 4.00),
                    'created_at'   => $time,
                    'updated_at'   => $time,
                ];
            } else {
                // SMA/D3
                $thnLulus = $faker->year;
                $dataPendidikan[] = [
                    'id_pegawai'   => $id,
                    'jenjang'      => 'SMA',
                    'nama_sekolah' => 'SMA Negeri ' . $faker->randomDigitNotNull . ' ' . $faker->city,
                    'jurusan'      => 'IPA/IPS',
                    'tahun_masuk'  => $thnLulus - 3,
                    'tahun_lulus'  => $thnLulus,
                    'nilai_akhir'  => $faker->numberBetween(80, 95),
                    'created_at'   => $time,
                    'updated_at'   => $time,
                ];
            }

            // --- B. DOKUMEN PEGAWAI ---
            // Dummy documents
            $docs = ['KTP', 'FOTO_PROFIL'];
            if ($p['jenis_pegawai'] == 'guru') $docs[] = 'IJAZAH'; // Guru tambah ijazah

            foreach ($docs as $jenis) {
                $ext = ($jenis == 'FOTO_PROFIL') ? 'jpg' : 'pdf';
                $dataDokumen[] = [
                    'id_pegawai'    => $id,
                    'jenis_dokumen' => $jenis,
                    'nama_file'     => strtolower($jenis) . '_' . str_replace(' ', '_', $p['nama_lengkap']) . '.' . $ext,
                    'file_path'     => 'dummy/path/' . $id . '/' . strtolower($jenis) . '.' . $ext,
                    'tipe_file'     => $ext,
                    'ukuran_file'   => $faker->numberBetween(100, 2048), // KB
                    'created_at'    => $time,
                    'updated_at'    => $time,
                ];
            }
        }

        // INSERT BATCH CHILD
        if (!empty($dataPendidikan)) {
            $this->db->table('riwayat_pendidikan')->insertBatch($dataPendidikan);
            echo count($dataPendidikan) . " Riwayat Pendidikan berhasil di-seed.\n";
        }

        if (!empty($dataDokumen)) {
            $this->db->table('pegawai_dokumen')->insertBatch($dataDokumen);
            echo count($dataDokumen) . " Dokumen Pegawai berhasil di-seed.\n";
        }
    }
}