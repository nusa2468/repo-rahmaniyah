<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * Seeder Jadwal Pelajaran - SINKRONISASI UNIT & GRUP SISWA
 * Mengambil data guru dari tabel 'pegawai' (Unified).
 * UPDATED: Mengambil Mata Pelajaran spesifik per jenjang (SD/SMP/SMA) & Cek Kolom Dinamis.
 */
class JadwalPelajaranSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        echo "\n>>> Menjalankan Seeder Jadwal Pelajaran (Sinkronisasi Rombel)...\n";
        
        // 1. Matikan FK Check untuk keamanan insert batch
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        
        // 2. Bersihkan tabel agar tidak ada data sampah (Cegah Error #1452)
        $db->table('jadwal_pelajaran')->truncate();
        
        // 3. AMBIL DATA MASTER (SINKRONISASI TABEL PEGAWAI)
        $guruList = $db->table('pegawai')
                       ->select('id')
                       ->where('jenis_pegawai', 'guru')
                       ->where('status_aktif', 'aktif')
                       ->get()
                       ->getResultArray();
                       
        // Ambil Grup Siswa (Rombel) yang valid
        $listGrup = $db->table('grup_siswa')
                       ->select('id, kode_jenjang, id_kelas')
                       ->get()
                       ->getResultArray();

        // Validasi Kritis
        if (empty($listGrup)) {
            echo " [SKIP] Data Grup Siswa (Rombel) kosong. Jalankan PrimaryAcademicDataSeeder terlebih dahulu.\n";
            $db->query('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }
        if (empty($guruList)) {
            echo " [SKIP] Data Guru (Pegawai) kosong. Jalankan PegawaiSeeder terlebih dahulu.\n";
            $db->query('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        // Ambil Tahun Ajaran Aktif
        $taAktif = $db->table('tahun_ajaran')->where('status', 'aktif')->get()->getRow();
        $idTA = $taAktif ? $taAktif->id : 1;

        // Ambil Ruangan (Opsional)
        $ruangan = [];
        if ($db->tableExists('sapras_ruangan')) {
            $ruangan = $db->table('sapras_ruangan')->select('id')->limit(10)->get()->getResultArray();
        }

        $jadwalBatch = [];
        $hariLokal = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jamMulai = ['07:30:00', '08:15:00', '09:00:00', '10:00:00', '10:45:00'];
        
        $faker = \Faker\Factory::create('id_ID');

        // Cek keberadaan kolom opsional
        $hasGrupColumn      = $db->fieldExists('id_grup_siswa', 'jadwal_pelajaran');
        $hasKurikulumColumn = $db->fieldExists('id_kurikulum', 'jadwal_pelajaran');
        $hasRuanganColumn   = $db->fieldExists('id_ruangan', 'jadwal_pelajaran'); 
        $hasIsAktifColumn   = $db->fieldExists('is_aktif', 'jadwal_pelajaran');

        // 4. GENERATE JADWAL PER ROMBEL
        foreach ($listGrup as $grup) {
            $unit = $grup['kode_jenjang']; 
            
            // SKIP jika unit adalah Global/Agregat
            if (in_array(strtoupper($unit), ['GLOBAL', 'YAYASAN', 'PUSAT'])) continue;

            $idKelas = $grup['id_kelas'];
            
            $kelasInfo = $db->table('kelas')->select('id_kurikulum')->where('id', $idKelas)->get()->getRow();
            $idKurikulum = $kelasInfo ? $kelasInfo->id_kurikulum : 1;

            // FIX UTAMA: Ambil mata pelajaran HANYA yang sesuai dengan jenjang kelasnya
            $mapelList = $db->table('mata_pelajaran')
                            ->select('id')
                            ->where('kode_jenjang', $unit)
                            ->get()
                            ->getResultArray();
                            
            if (empty($mapelList)) continue; // Lewati jika jenjang ini belum punya mapel

            // Generate jadwal sampel untuk 5 hari kerja
            foreach ($hariLokal as $h) {
                // Pilih 2 mapel acak (atau 1 jika mapelnya kurang dari 2)
                $jumlahMapel = min(2, count($mapelList));
                $mapelAcak = $faker->randomElements($mapelList, $jumlahMapel);
                
                foreach ($mapelAcak as $index => $mapel) {
                    $jam_start = $jamMulai[$index];
                    $jam_end   = date('H:i:s', strtotime($jam_start) + (45 * 60)); // +45 menit

                    $guruRandom = $faker->randomElement($guruList);
                    $ruangRandom = !empty($ruangan) ? $faker->randomElement($ruangan)['id'] : null;

                    $row = [
                        'kode_jenjang'      => $unit,
                        'id_kelas'          => $idKelas,
                        'id_mata_pelajaran' => $mapel['id'],
                        'id_guru'           => $guruRandom['id'],
                        'id_tahun_ajaran'   => $idTA,
                        'hari'              => $h,
                        'jam_mulai'         => $jam_start,
                        'jam_selesai'       => $jam_end,
                        'created_at'        => Time::now(),
                        'updated_at'        => Time::now()
                    ];

                    if ($hasGrupColumn) $row['id_grup_siswa'] = $grup['id'];
                    if ($hasKurikulumColumn) $row['id_kurikulum'] = $idKurikulum;
                    if ($hasRuanganColumn) $row['id_ruangan'] = $ruangRandom;
                    if ($hasIsAktifColumn) $row['is_aktif'] = 1;

                    $jadwalBatch[] = $row;
                }
            }
        }

        // 5. Insert Batch
        if (!empty($jadwalBatch)) {
            $chunks = array_chunk($jadwalBatch, 100);
            $totalInserted = 0;
            
            foreach($chunks as $chunk) {
                $db->table('jadwal_pelajaran')->insertBatch($chunk);
                $totalInserted += count($chunk);
            }
            
            echo ">>> SUCCESS: " . $totalInserted . " Baris Jadwal Pelajaran berhasil disinkronkan.\n";
        } else {
            echo " [INFO] Tidak ada jadwal yang dibuat (Mungkin data grup kosong atau mapel belum ada).\n";
        }
        
        $db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}