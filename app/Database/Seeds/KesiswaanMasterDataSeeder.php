<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class KesiswaanMasterDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Pastikan tabel parent (Pegawai & Siswa) memiliki data untuk Foreign Key
        $this->ensureParentsExist();

        // 2. Ambil ID Guru & Siswa nyata dari database
        // Kita ambil beberapa untuk variasi relasi
        $guruList  = $this->db->table('pegawai')->select('id')->limit(5)->get()->getResultArray();
        $siswaList = $this->db->table('siswa')->select('id')->limit(20)->get()->getResultArray();
        
        // Fallback jika fetch return kosong (meski sudah ensureParentsExist, safety net)
        $idGuruDefault  = $guruList[0]['id'] ?? 1;
        $idSiswaDefault = $siswaList[0]['id'] ?? 1;

        // Array Jenjang yang akan di-seed
        $jenjangs = ['SD', 'SMP', 'SMA'];

        foreach ($jenjangs as $jenjang) {
            // ==========================================
            // A. KELOMPOK EKSTRAKURIKULER
            // ==========================================
            
            // Tentukan template nama ekskul spesifik jenjang agar terlihat realistis
            $ekskulTemplates = [
                ['kategori' => 'Olahraga', 'nama' => ($jenjang === 'SD' ? 'Senam Ceria' : 'Futsal Club')],
                ['kategori' => 'Seni',     'nama' => ($jenjang === 'SD' ? 'Menggambar' : 'Teater & Drama')],
                ['kategori' => 'Lainnya',  'nama' => 'Pramuka ' . $jenjang],
            ];

            foreach ($ekskulTemplates as $index => $template) {
                // Pilih Guru Pembina secara bergilir
                $guruPembina = $guruList[$index % count($guruList)]['id'] ?? $idGuruDefault;

                $dataEkskul = [
                    'kode_jenjang'    => $jenjang,
                    'nama_ekskul'     => $template['nama'],
                    'kategori'        => $template['kategori'],
                    'guru_pembina_id' => $guruPembina,
                    'hari_latihan'    => ['Senin', 'Rabu', 'Jumat'][$index % 3], // Variasi hari
                    'jam_mulai'       => '15:00:00',
                    'jam_selesai'     => '17:00:00',
                    'deskripsi'       => "Kegiatan ekstrakurikuler {$template['nama']} untuk mengembangkan bakat siswa {$jenjang}.",
                    'foto_cover'      => null,
                    'created_at'      => Time::now(),
                    'updated_at'      => Time::now(),
                ];

                $this->db->table('kesiswaan_ekskul')->insert($dataEkskul);
                $ekskulId = $this->db->insertID();

                // --- 1.1 Anggota Ekskul ---
                // Ambil 3 siswa acak untuk jadi anggota ekskul ini
                $anggotaCount = 0;
                foreach ($siswaList as $siswa) {
                    if ($anggotaCount >= 3) break; // Cukup 3 anggota per ekskul
                    
                    $this->db->table('kesiswaan_ekskul_anggota')->insert([
                        'kode_jenjang'    => $jenjang,
                        'tahun_ajar_id'   => 1, // Default tahun ajar aktif
                        'ekskul_id'       => $ekskulId,
                        'siswa_id'        => $siswa['id'],
                        'nilai_huruf'     => ['A', 'B', 'A'][$anggotaCount % 3],
                        'deskripsi_nilai' => 'Aktif dan berprestasi dalam kegiatan.',
                        'created_at'      => Time::now(),
                        'updated_at'      => Time::now(),
                    ]);
                    $anggotaCount++;
                }

                // --- 1.2 Presensi Ekskul ---
                // Buat dummy JSON presensi untuk 3 siswa tadi
                $jsonPresensi = [];
                $pCount = 0;
                foreach ($siswaList as $siswa) {
                    if ($pCount >= 3) break;
                    $jsonPresensi[] = [
                        'siswa_id'   => $siswa['id'],
                        'status'     => 'H', // Hadir
                        'keterangan' => ''
                    ];
                    $pCount++;
                }

                $this->db->table('kesiswaan_ekskul_presensi')->insert([
                    'ekskul_id'       => $ekskulId,
                    'tanggal'         => date('Y-m-d', strtotime('-1 week')),
                    'materi_kegiatan' => 'Latihan dasar teknik dan fisik.',
                    'data_presensi'   => json_encode($jsonPresensi),
                    'created_at'      => Time::now(),
                    'updated_at'      => Time::now(),
                ]);
            }

            // ==========================================
            // B. KELOMPOK ORGANISASI (OSIS/MPK)
            // ==========================================
            $jabatanList = [
                ['jenis' => 'OSIS', 'jabatan' => 'Ketua OSIS'],
                ['jenis' => 'OSIS', 'jabatan' => 'Wakil Ketua'],
                ['jenis' => 'MPK',  'jabatan' => 'Ketua MPK'],
            ];

            // Sesuaikan nama organisasi untuk SD (Dokter Kecil)
            if ($jenjang === 'SD') {
                $jabatanList = [
                    ['jenis' => 'LAINNYA', 'jabatan' => 'Ketua Dokter Kecil'],
                    ['jenis' => 'LAINNYA', 'jabatan' => 'Polisi Cilik'],
                ];
            }

            foreach ($jabatanList as $idx => $jab) {
                // Ambil siswa secara bergilir
                $siswaPengurus = $siswaList[$idx % count($siswaList)]['id'] ?? $idSiswaDefault;

                $this->db->table('kesiswaan_organisasi')->insert([
                    'kode_jenjang'     => $jenjang,
                    'tahun_ajar_id'    => 1,
                    'jenis_organisasi' => $jab['jenis'],
                    'jabatan'          => $jab['jabatan'],
                    'siswa_id'         => $siswaPengurus,
                    'status_aktif'     => 1,
                    'created_at'       => Time::now(),
                    'updated_at'       => Time::now(),
                ]);
            }

            // ==========================================
            // C. KELOMPOK BIMBINGAN KONSELING (BK)
            // ==========================================
            
            // 3.1 Master Kategori
            $kategoriData = [
                ['jenis' => 'Pelanggaran', 'nama' => 'Terlambat', 'poin' => 5],
                ['jenis' => 'Pelanggaran', 'nama' => 'Atribut Tidak Lengkap', 'poin' => 3],
                ['jenis' => 'Prestasi',    'nama' => 'Juara Kelas', 'poin' => 10],
            ];

            $kategoriIds = [];
            foreach ($kategoriData as $kat) {
                $this->db->table('kesiswaan_bk_kategori')->insert([
                    'kode_jenjang'          => $jenjang,
                    'jenis'                 => $kat['jenis'],
                    'nama_kasus'            => $kat['nama'] . " ($jenjang)",
                    'poin'                  => $kat['poin'],
                    'tindak_lanjut_default' => 'Pembinaan Wali Kelas',
                    'created_at'            => Time::now(),
                ]);
                $kategoriIds[] = $this->db->insertID();
            }

            // 3.2 Catatan Kasus Siswa
            // Ambil siswa acak untuk diberi kasus
            $siswaKasus = $siswaList[array_rand($siswaList)]['id'] ?? $idSiswaDefault;
            $kategoriKasus = $kategoriIds[0] ?? 1; // Ambil kategori pertama (Terlambat)

            $this->db->table('kesiswaan_bk_catatan')->insert([
                'kode_jenjang'        => $jenjang,
                'tahun_ajar_id'       => 1,
                'siswa_id'            => $siswaKasus,
                'bk_kategori_id'      => $kategoriKasus,
                'tanggal_kejadian'    => date('Y-m-d'),
                'keterangan_detail'   => 'Terlambat saat upacara bendera.',
                'tindak_lanjut'       => 'Diberi peringatan lisan.',
                'status_penyelesaian' => 'Selesai',
                'created_at'          => Time::now(),
                'updated_at'          => Time::now(),
            ]);

            // ==========================================
            // D. KELOMPOK ALUMNI (TRACER STUDY)
            // ==========================================
            $siswaAlumni = $siswaList[array_rand($siswaList)]['id'] ?? $idSiswaDefault;
            
            $this->db->table('kesiswaan_alumni')->insert([
                'kode_jenjang'    => $jenjang,
                'siswa_id'        => $siswaAlumni,
                'tahun_lulus'     => date('Y') - 1, // Lulus tahun lalu
                'status_kegiatan' => ($jenjang === 'SMA') ? 'Kuliah' : 'Belum Ada',
                'nama_instansi'   => ($jenjang === 'SMA') ? 'Universitas Negeri' : '-',
                'jabatan_jurusan' => ($jenjang === 'SMA') ? 'Teknik Informatika' : '-',
                'kontak_alumni'   => '081234567890',
                'testimoni'       => 'Sekolah ini sangat membentuk karakter disiplin saya.',
                'created_at'      => Time::now(),
                'updated_at'      => Time::now(),
            ]);

            // ==========================================
            // E. KELOMPOK PRESTASI (BARU)
            // ==========================================
            // Mengambil siswa acak untuk data prestasi
            $siswaPrestasi = $siswaList[array_rand($siswaList)]['id'] ?? $idSiswaDefault;
            
            $namaPrestasi = ($jenjang === 'SD') ? 'Lomba Mewarnai Tingkat Kecamatan' : 'Olimpiade Sains Nasional (OSN)';
            $tingkat      = ($jenjang === 'SD') ? 'Kecamatan' : 'Nasional';
            
            $this->db->table('kesiswaan_prestasi')->insert([
                'kode_jenjang'     => $jenjang,
                'tahun_ajar_id'    => 1,
                'siswa_id'         => $siswaPrestasi,
                'nama_prestasi'    => $namaPrestasi,
                'jenis_prestasi'   => ($jenjang === 'SD') ? 'Non-Akademik' : 'Akademik',
                'tingkat'          => $tingkat,
                'peringkat'        => 'Juara 2',
                'penyelenggara'    => 'Dinas Pendidikan',
                'tanggal_prestasi' => date('Y-m-d', strtotime('-2 months')),
                'keterangan'       => 'Membawa nama baik sekolah.',
                'created_at'       => Time::now(),
                'updated_at'       => Time::now(),
            ]);
        }
    }

    /**
     * Helper untuk memastikan tabel parent tidak kosong sebelum seeding.
     * Mencegah error Foreign Key Constraint.
     */
    private function ensureParentsExist()
    {
        // 1. Cek Pegawai (Guru)
        if ($this->db->table('pegawai')->countAllResults() == 0) {
            $dataPegawai = [];
            for ($i = 1; $i <= 5; $i++) {
                $dataPegawai[] = [
                    'nama_lengkap' => "Guru Dummy $i",
                    'nip'          => "19900101202301100$i",
                    'created_at'   => Time::now(),
                ];
            }
            $this->db->table('pegawai')->insertBatch($dataPegawai);
        }

        // 2. Cek Siswa
        if ($this->db->table('siswa')->countAllResults() == 0) {
            $dataSiswa = [];
            for ($i = 1; $i <= 10; $i++) {
                $dataSiswa[] = [
                    'nama_lengkap' => "Siswa Dummy $i",
                    'nis'          => "1000$i",
                    'kode_jenjang' => 'SMA', // Default placeholder
                    'created_at'   => Time::now(),
                ];
            }
            $this->db->table('siswa')->insertBatch($dataSiswa);
        }
    }
}