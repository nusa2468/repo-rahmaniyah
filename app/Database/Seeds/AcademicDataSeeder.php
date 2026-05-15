<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * ============================================================================
 * SEEDER DATA AKADEMIK (TRANSAKSIONAL & MASTER)
 * ============================================================================
 * Mengisi: Jadwal, Nilai, Absensi, Raport, Kesiswaan.
 */
class AcademicDataSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');
        $db = \Config\Database::connect();
        
        // Matikan FK Check untuk bulk insert/truncate agar tidak membentur konstrain
        $db->query('SET FOREIGN_KEY_CHECKS=0;');

        echo "\n>>> Menjalankan AcademicDataSeeder...\n";

        // --------------------------------------------------------------------
        // 1. CLEANUP TABLE (Bersihkan data lama agar bersih)
        // --------------------------------------------------------------------
        $tables = [
            'siswa_kesiswaan',
            'kenaikan_kelas',
            'raport',
            'absensi_siswa',
            'nilai_siswa',
            'jadwal_pelajaran'
            // KUNCI: Hapus 'mata_pelajaran' dari sini agar 162 data dari MataPelajaranSeeder TIDAK TERHAPUS
        ];

        foreach ($tables as $t) {
            if ($db->tableExists($t)) {
                $db->table($t)->truncate();
            }
        }

        // --------------------------------------------------------------------
        // 2. PERSIAPAN DATA PENDUKUNG (MAPEL & GURU)
        // --------------------------------------------------------------------
        $this->_ensureMataPelajaran($db);
        
        // Ambil Data Guru (Pastikan ada data di tabel pegawai)
        $guruList = $db->table('pegawai')->where('jenis_pegawai', 'guru')->get()->getResultArray();
        $guruIds  = !empty($guruList) ? array_column($guruList, 'id') : [1];

        // Ambil Data Enrollment (Siswa Aktif) + Info Kelas + Semester Aktif
        if ($db->tableExists('siswa_enrollment')) {
            $enrollments = $db->table('siswa_enrollment')
                              ->select('siswa_enrollment.*, kelas.kode_jenjang, kelas.tingkat, ta.semester')
                              ->join('kelas', 'kelas.id = siswa_enrollment.id_kelas')
                              ->join('tahun_ajaran ta', 'ta.id = siswa_enrollment.id_tahun_ajaran')
                              ->where('status_akademik', 'Aktif')
                              ->get()->getResultArray();
        } else {
            $enrollments = [];
        }

        if (empty($enrollments)) {
            echo " [SKIP] Tidak ada data enrollment siswa. Jalankan SiswaSeeder terlebih dahulu (atau tabel belum ada).\n";
            $db->query('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        // Group Enrollment by Kelas untuk efisiensi jadwal
        $kelasMap = [];
        foreach ($enrollments as $enr) {
            $kelasMap[$enr['id_kelas']][] = $enr;
        }

        // --------------------------------------------------------------------
        // 3. GENERATE JADWAL & NILAI PER KELAS
        // --------------------------------------------------------------------
        foreach ($kelasMap as $idKelas => $siswaDiKelas) {
            echo " Processing Kelas ID: $idKelas (" . count($siswaDiKelas) . " siswa)...\n";
            
            $firstSiswa = $siswaDiKelas[0];
            $idTA       = $firstSiswa['id_tahun_ajaran'];
            $jenjang    = $firstSiswa['kode_jenjang'];
            $tingkat    = $firstSiswa['tingkat'];
            $smtAktif   = $firstSiswa['semester'] ?? 'Ganjil';

            // Ambil Mapel berdasarkan jenjang dan tingkat
            $mapelResult = $db->table('mata_pelajaran')
                              ->select('id')
                              ->where('kode_jenjang', $jenjang)
                              ->where('tingkat', $tingkat)
                              ->get()->getResultArray();
            $mapelIds = array_column($mapelResult, 'id');

            // Fallback jika tidak ada mapel spesifik tingkat tersebut, ambil random dari jenjangnya
            if (empty($mapelIds)) {
                $mapelResult = $db->table('mata_pelajaran')
                                  ->select('id')
                                  ->where('kode_jenjang', $jenjang)
                                  ->limit(10)->get()->getResultArray();
                $mapelIds = array_column($mapelResult, 'id');
            }
            if (empty($mapelIds)) continue; // Skip jika tetap tidak ada mapel

            // A. GENERATE JADWAL PELAJARAN (Senin-Jumat)
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            $jadwalIds = [];

            foreach ($hariList as $hari) {
                // Generate 2 mapel per hari (Jam 1 & 2)
                for ($jam = 1; $jam <= 2; $jam++) {
                    $idMapel = $faker->randomElement($mapelIds);
                    $idGuru  = $faker->randomElement($guruIds);
                    
                    $jamMulai   = ($jam == 1) ? '07:30:00' : '09:00:00';
                    $jamSelesai = ($jam == 1) ? '09:00:00' : '10:30:00';

                    $db->table('jadwal_pelajaran')->insert([
                        'kode_jenjang'      => $jenjang,
                        'id_kelas'          => $idKelas,
                        'id_mata_pelajaran' => $idMapel,
                        'id_guru'           => $idGuru,
                        'id_tahun_ajaran'   => $idTA,
                        'hari'              => $hari,
                        'jam_mulai'         => $jamMulai,
                        'jam_selesai'       => $jamSelesai,
                        'is_aktif'          => 1,
                        'created_at'        => Time::now()
                    ]);
                    $jadwalIds[] = $db->insertID();

                    // B. GENERATE NILAI SISWA (Per Mapel di Jadwal ini)
                    foreach ($siswaDiKelas as $siswa) {
                        $nilaiAkhir = $faker->randomFloat(2, 70, 98);
                        
                        // Cek duplicate nilai (agar tidak double insert per mapel)
                        $cekNilai = $db->table('nilai_siswa')
                                       ->where('id_enrollment', $siswa['id'])
                                       ->where('id_mata_pelajaran', $idMapel)
                                       ->countAllResults();
                                       
                        if ($cekNilai == 0) {
                            $db->table('nilai_siswa')->insert([
                                'id_enrollment'     => $siswa['id'],
                                'id_kelas'          => $idKelas,
                                'id_siswa'          => $siswa['id_siswa'],
                                'id_mata_pelajaran' => $idMapel,
                                'id_guru'           => $idGuru,
                                'id_tahun_ajaran'   => $idTA,
                                'semester'          => $smtAktif,
                                'kode_jenjang'      => $jenjang,
                                'kategori_nilai'    => 'PH',
                                'nilai_tugas'       => $faker->numberBetween(70, 95),
                                'nilai_uts'         => $faker->numberBetween(65, 90),
                                'nilai_uas'         => $faker->numberBetween(70, 95),
                                'nilai_akhir'       => $nilaiAkhir,
                                'nilai_huruf'       => $this->_getPredikat($nilaiAkhir),
                                'keterangan'        => 'Tuntas',
                                'created_at'        => Time::now()
                            ]);
                        }
                    }
                }
            }

            // C. GENERATE DATA PER SISWA (Absensi, Raport, Kesiswaan)
            foreach ($siswaDiKelas as $siswa) {
                
                // 1. Absensi (Random 3 hari terakhir)
                if (!empty($jadwalIds)) {
                    for ($i = 0; $i < 3; $i++) {
                        $targetJadwal = $faker->randomElement($jadwalIds);
                        $status = $faker->randomElement(['hadir', 'hadir', 'hadir', 'sakit', 'izin']);
                        
                        $db->table('absensi_siswa')->insert([
                            'kode_jenjang' => $jenjang,
                            'id_jadwal'    => $targetJadwal,
                            'id_siswa'     => $siswa['id_siswa'],
                            'tanggal'      => Time::now()->subDays($i)->toDateString(),
                            'status'       => $status,
                            'keterangan'   => ($status != 'hadir') ? 'Keterangan otomatis' : null,
                            'created_at'   => Time::now()
                        ]);
                    }
                }

                // 2. Raport (Semester Ini)
                $rataRataRapor = $faker->randomFloat(2, 75, 95);
                $db->table('raport')->insert([
                    'id_enrollment'       => $siswa['id'],
                    'semester'            => $smtAktif,
                    'rata_rata'           => $rataRataRapor,
                    'total_sakit'         => $faker->numberBetween(0, 3),
                    'total_izin'          => $faker->numberBetween(0, 2),
                    'total_alpa'          => 0,
                    'catatan_wali_kelas'  => "Pertahankan prestasimu dan teruslah belajar dengan giat.",
                    'catatan_karakter'    => "Menunjukkan sikap yang baik dan sopan.",
                    'predikat_spiritual'  => 'SB',
                    'predikat_sosial'     => 'B',
                    'status_raport'       => 'Final',
                    'status_kenaikan'     => 'Naik Kelas',
                    'created_at'          => Time::now()
                ]);

                // 3. Kesiswaan (Hanya 10% siswa yang punya catatan)
                if ($faker->boolean(10)) {
                    $jenis = $faker->randomElement(['Prestasi', 'Pelanggaran']);
                    $poin = ($jenis == 'Prestasi') ? 10 : -5;
                    $ket = ($jenis == 'Prestasi') ? 'Juara Kelas' : 'Terlambat Masuk';

                    $db->table('siswa_kesiswaan')->insert([
                        'id_siswa'        => $siswa['id_siswa'],
                        'id_tahun_ajaran' => $idTA,
                        'jenis_data'      => $jenis,
                        'tanggal_kejadian'=> Time::now()->subDays(rand(1, 30)),
                        'keterangan'      => $ket,
                        'poin'            => $poin,
                        'level_prestasi'  => ($jenis == 'Prestasi') ? 'Sekolah' : null,
                        'created_at'      => Time::now()
                    ]);
                }
            }
        }

        $db->query('SET FOREIGN_KEY_CHECKS=1;');
        echo ">>> AcademicDataSeeder SELESAI.\n";
    }

    // --- HELPER FUNCTIONS ---

    private function _ensureMataPelajaran($db)
    {
        // KUNCI: Kosongkan fungsi ini! 
        // Tanggung jawab seeding mapel SEPENUHNYA ada di MataPelajaranSeeder.
        // Kita hanya akan memberi peringatan jika tabel ternyata benar-benar kosong.
        if ($db->table('mata_pelajaran')->countAllResults() == 0) {
            echo " [WARNING] Tabel mapel kosong! Pastikan Anda sudah menjalankan MataPelajaranSeeder.\n";
        }
    }

    private function _getPredikat($nilai)
    {
        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        return 'D';
    }
}