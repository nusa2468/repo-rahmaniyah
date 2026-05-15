<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * ============================================================================
 * SISWA SEEDER (FINAL VALIDATED)
 * Mengisi: orang_tua, siswa, siswa_akademik, siswa_demografi, siswa_keluarga,
 * siswa_enrollment.
 * * Dependencies: 
 * - Membutuhkan tabel 'jenjang_sekolah', 'jurusan', 'kurikulum' (dari seeder lain).
 * - Membersihkan data lama sebelum mengisi untuk mencegah duplikasi/error FK.
 * ============================================================================
 */
class SiswaSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');
        $db = \Config\Database::connect();
        
        // 1. Matikan pemeriksaan Foreign Key untuk kelancaran truncate & insert
        $db->query('SET FOREIGN_KEY_CHECKS=0;');

        echo "\n>>> Menjalankan SiswaSeeder (Target: 2025/2026 Genap)...\n";

        // --------------------------------------------------------------------
        // 2. CLEANUP DATA (Hapus data lama agar bersih)
        // --------------------------------------------------------------------
        // Urutan penghapusan: Tabel Anak -> Tabel Induk
        $tables = [
            'siswa_enrollment', 
            'siswa_akademik', 
            'siswa_keluarga', 
            'siswa_demografi', 
            'siswa', 
            'orang_tua',
            'grup_siswa',
            'kelas' 
        ];

        foreach ($tables as $t) {
            if ($db->tableExists($t)) {
                $db->table($t)->truncate();
            }
        }

        // --------------------------------------------------------------------
        // 3. SETUP PRASYARAT (TAHUN AJARAN & REFERENSI)
        // --------------------------------------------------------------------
        $targetTA       = '2025/2026';
        $targetSemester = 'Genap';
        $angkatanBaru   = 2025;

        // Setup Tahun Ajaran Aktif
        $taExist = $db->table('tahun_ajaran')->where('tahun_ajaran', $targetTA)->get()->getRow();
        if ($taExist) {
            $idTA = $taExist->id;
            $db->table('tahun_ajaran')->update(['status' => 'nonaktif']); // Reset semua ke nonaktif
            $db->table('tahun_ajaran')->where('id', $idTA)->update(['status' => 'aktif']);
        } else {
            $db->table('tahun_ajaran')->update(['status' => 'nonaktif']);
            $db->table('tahun_ajaran')->insert([
                'tahun_ajaran' => $targetTA,
                'status'       => 'aktif',
                'created_at'   => Time::now()
            ]);
            $idTA = $db->insertID();
        }

        // Ambil Daftar Jenjang Sekolah
        $excludedUnits = ['GLOBAL', 'YAYASAN', 'PUSAT', 'KANTOR'];
        $jenjangs = $db->table('jenjang_sekolah')
                       ->whereNotIn('UPPER(kode_jenjang)', $excludedUnits)
                       ->where('status', 'aktif')
                       ->get()->getResultArray();

        // Ambil ID Guru untuk Wali Kelas (Fallback ke ID 1 jika tabel pegawai kosong)
        $guruIds = $db->table('pegawai')->select('id')->where('jenis_pegawai', 'guru')->get()->getResultArray();
        $guruIds = !empty($guruIds) ? array_column($guruIds, 'id') : [1];

        // --------------------------------------------------------------------
        // 4. GENERATE DATA PER UNIT (JENJANG)
        // --------------------------------------------------------------------
        foreach ($jenjangs as $jenjang) {
            $unit = strtoupper($jenjang['kode_jenjang']);
            echo " Processing Unit: [{$unit}] ...\n";

            // Setup Jurusan & Kurikulum Default jika belum ada
            $this->_ensureJurusanKurikulum($db, $unit);
            
            $jurusanData    = $db->table('jurusan')->where('kode_jenjang', $unit)->get()->getRow();
            $kurikulumData  = $db->table('kurikulum')->where('kode_jenjang', $unit)->get()->getRow();
            
            $idJurusan      = $jurusanData ? $jurusanData->id : null;
            $idKurikulum    = $kurikulumData ? $kurikulumData->id : 1;
            $tingkatBase    = $this->_getTingkatByDefault($unit);
            $jenjangId      = $jenjang['id'] ?? 1; // Fallback ID jika tidak ada

            // A. Generate Kelas & Grup (2 Kelas: A & B)
            $kelasIds = [];
            $grupMap  = []; 

            for ($k = 1; $k <= 2; $k++) {
                $suffix     = ($k == 1 ? 'A' : 'B');
                $namaKelas  = "KELAS " . $tingkatBase . " " . $unit . " " . $suffix;
                $idWali     = $faker->randomElement($guruIds);

                // Insert Kelas
                $kelasData = [
                    'kode_jenjang'    => $unit,
                    'nama_kelas'      => $namaKelas,
                    'tingkat'         => $tingkatBase,
                    'id_jurusan'      => (in_array($unit, ['SMA', 'SMK'])) ? $idJurusan : null,
                    'id_wali_kelas'   => $idWali,
                    'id_tahun_ajaran' => $idTA,
                    'id_kurikulum'    => $idKurikulum,
                    'angkatan'        => $angkatanBaru,
                    'is_aktif'        => 1,
                    'kapasitas'       => 30,
                    'terisi'          => 0,
                    'created_at'      => Time::now()
                ];
                $db->table('kelas')->insert($kelasData);
                $idKelas = $db->insertID();
                $kelasIds[] = $idKelas;

                // Insert Grup Siswa (Rombel)
                $db->table('grup_siswa')->insert([
                    'kode_jenjang' => $unit,
                    'id_kelas'     => $idKelas,
                    'nama_grup'    => "REGULER-" . $suffix,
                    'tahun_ajaran' => $targetTA,
                    'keterangan'   => "Rombel Utama " . $namaKelas,
                    'created_at'   => Time::now()
                ]);
                $grupMap[$idKelas] = $db->insertID();
            }

            // B. Generate Siswa (Total 30 Siswa, dibagi rata ke 2 kelas)
            $jumlahSiswaPerKelas = array_fill_keys($kelasIds, 0);

            for ($s = 1; $s <= 30; $s++) {
                $gender       = ($s % 2 == 0) ? 'P' : 'L';
                $namaLengkap  = $faker->name($gender == 'L' ? 'male' : 'female');
                $targetKelas  = ($s <= 15) ? $kelasIds[0] : $kelasIds[1];
                $namaAyah     = $faker->name('male');

                // 1. Insert ORANG TUA (Portal User)
                $db->table('orang_tua')->insert([
                    'nama_lengkap' => $namaAyah,
                    'email'        => "ortu.{$unit}.{$s}@sekolah.id",
                    'password'     => password_hash('ortu123', PASSWORD_DEFAULT),
                    'no_telepon'   => '08' . $faker->numerify('##########'),
                    'status'       => 'aktif',
                    'created_at'   => Time::now()
                ]);
                $idOrtu = $db->insertID();

                // 2. Insert SISWA
                $db->table('siswa')->insert([
                    'kode_jenjang'        => $unit,
                    'id_jurusan'          => (in_array($unit, ['SMA', 'SMK'])) ? $idJurusan : null,
                    'angkatan'            => $angkatanBaru,
                    // Format NIS: TAHUN + KODE_JENJANG_ID + URUT
                    'nis'                 => $angkatanBaru . str_pad($jenjangId, 2, '0', STR_PAD_LEFT) . str_pad($s, 3, '0', STR_PAD_LEFT),
                    'nisn'                => '00' . $faker->unique()->numerify('########'),
                    'nik'                 => $faker->unique()->numerify('16################'),
                    'nama_lengkap'        => $namaLengkap,
                    'jenis_kelamin'       => $gender,
                    'status'              => 'aktif',
                    'id_orang_tua_portal' => $idOrtu,
                    'password'            => password_hash('siswa123', PASSWORD_DEFAULT),
                    'created_at'          => Time::now()
                ]);
                $idSiswa = $db->insertID();

                // 3. Insert SISWA AKADEMIK (Data Pendaftaran)
                $db->table('siswa_akademik')->insert([
                    'id_siswa'          => $idSiswa,
                    'jalur_penerimaan'  => $faker->randomElement(['Zonasi', 'Prestasi', 'Tes Tertulis']),
                    'nomor_pendaftaran' => 'REG-' . $unit . '-' . date('Y') . '-' . str_pad($s, 4, '0', STR_PAD_LEFT),
                    'tanggal_diterima'  => ($angkatanBaru) . '-07-01',
                    'program_peminatan' => (in_array($unit, ['SMA'])) ? $faker->randomElement(['MIPA', 'IPS']) : 'Umum',
                    'nilai_masuk'       => $faker->numberBetween(70, 95),
                    'created_at'        => Time::now()
                ]);

                // 4. Insert SISWA ENROLLMENT (Data Kelas Aktif)
                $enrollData = [
                    'id_siswa'        => $idSiswa,
                    'id_kelas'        => $targetKelas,
                    'id_grup_siswa'   => $grupMap[$targetKelas],
                    'id_tahun_ajaran' => $idTA,
                    'semester'        => $targetSemester,
                    'status_akademik' => 'Aktif',
                    'tanggal_masuk'   => ($angkatanBaru) . '-07-15',
                    'created_at'      => Time::now()
                ];
                $db->table('siswa_enrollment')->insert($enrollData);
                $jumlahSiswaPerKelas[$targetKelas]++;

                // 5. Insert SISWA DEMOGRAFI
                $db->table('siswa_demografi')->insert([
                    'id_siswa'       => $idSiswa,
                    'nama_panggilan' => explode(' ', $namaLengkap)[0],
                    'tempat_lahir'   => $faker->city,
                    'tanggal_lahir'  => ($angkatanBaru - $tingkatBase - 6) . '-05-' . rand(10, 28),
                    'agama'          => 'Islam',
                    'nama_ayah'      => $namaAyah,
                    'nama_ibu'       => $faker->name('female'),
                    'alamat'         => $faker->address,
                    'created_at'     => Time::now()
                ]);

                // 6. Insert SISWA KELUARGA
                $db->table('siswa_keluarga')->insert([
                    'id_siswa'     => $idSiswa,
                    'hubungan'     => 'Ayah',
                    'nama_lengkap' => $namaAyah,
                    'pekerjaan'    => $faker->jobTitle,
                    'no_telepon'   => '08' . $faker->numerify('##########'),
                    'is_wali'      => 1,
                    'created_at'   => Time::now()
                ]);
            }

            // Update Counter Terisi di Kelas (Real Count)
            foreach ($jumlahSiswaPerKelas as $kId => $jml) {
                $db->table('kelas')->where('id', $kId)->update(['terisi' => $jml]);
            }
        }

        // 5. Aktifkan kembali Foreign Key Check
        $db->query('SET FOREIGN_KEY_CHECKS=1;');
        echo ">>> SiswaSeeder SELESAI.\n";
    }

    // --- HELPER FUNCTIONS ---

    private function _getTingkatByDefault($unit)
    {
        switch ($unit) {
            case 'SD': return 1;
            case 'SMP': return 7;
            case 'SMA': return 10;
            case 'SMK': return 10;
            default: return 1;
        }
    }

    private function _ensureJurusanKurikulum($db, $kode)
    {
        // Pastikan Jurusan Umum ada
        if ($db->table('jurusan')->where('kode_jenjang', $kode)->countAllResults() === 0) {
            $db->table('jurusan')->insert([
                'kode_jenjang' => $kode, 
                'nama_jurusan' => 'UMUM', 
                'kode_jurusan' => 'GEN-'.$kode, 
                'created_at'   => Time::now()
            ]);
        }
        // Pastikan Kurikulum ada
        if ($db->table('kurikulum')->where('kode_jenjang', $kode)->countAllResults() === 0) {
            $db->table('kurikulum')->insert([
                'kode_jenjang'   => $kode, 
                'nama_kurikulum' => 'Merdeka '.$kode, 
                'created_at'     => Time::now()
            ]);
        }
    }
}