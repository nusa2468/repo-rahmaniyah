<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PembelajaranSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        
        // 1. Matikan Foreign Key Checks untuk Truncate yang aman
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        
        // Bersihkan tabel Transactional (Anak)
        $db->table('pembelajaran_evaluasi_belajar')->truncate();
        $db->table('pembelajaran_bank_soal')->truncate();
        $db->table('pembelajaran_bahan_ajar')->truncate();
        $db->table('pembelajaran_rpp')->truncate();
        $db->table('pembelajaran_silabus')->truncate();

        // Bersihkan tabel Master yang terkait (Induk)
        $db->table('mata_pelajaran')->truncate();
        $db->table('kurikulum')->truncate();
        
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // ==========================================
        // A. SEED MASTER KURIKULUM
        // ==========================================
        $dataKurikulum = [
            // SD
            ['kode_jenjang' => 'SD', 'kode_kurikulum' => 'K13-SD', 'nama_kurikulum' => 'Kurikulum 2013 SD', 'status' => 'tidak aktif', 'created_at' => $now],
            ['kode_jenjang' => 'SD', 'kode_kurikulum' => 'KM-SD', 'nama_kurikulum' => 'Kurikulum Merdeka SD', 'status' => 'aktif', 'created_at' => $now],
            // SMP
            ['kode_jenjang' => 'SMP', 'kode_kurikulum' => 'K13-SMP', 'nama_kurikulum' => 'Kurikulum 2013 SMP', 'status' => 'tidak aktif', 'created_at' => $now],
            ['kode_jenjang' => 'SMP', 'kode_kurikulum' => 'KM-SMP', 'nama_kurikulum' => 'Kurikulum Merdeka SMP', 'status' => 'aktif', 'created_at' => $now],
            // SMA
            ['kode_jenjang' => 'SMA', 'kode_kurikulum' => 'K13-SMA', 'nama_kurikulum' => 'Kurikulum 2013 SMA', 'status' => 'tidak aktif', 'created_at' => $now],
            ['kode_jenjang' => 'SMA', 'kode_kurikulum' => 'KM-SMA', 'nama_kurikulum' => 'Kurikulum Merdeka SMA', 'status' => 'aktif', 'created_at' => $now],
        ];
        $db->table('kurikulum')->insertBatch($dataKurikulum);

        // Helper: Map ID Kurikulum
        $kurikulumIds = [];
        $kurs = $db->table('kurikulum')->get()->getResultArray();
        foreach ($kurs as $k) {
            $kurikulumIds[$k['kode_kurikulum']] = $k['id'];
        }

        // ==========================================
        // B. SEED MASTER MATA PELAJARAN
        // ==========================================
        $dataMapel = [
            // --- SD ---
            [
                'kurikulum_id' => $kurikulumIds['KM-SD'], 'kode_jenjang' => 'SD', 'kode_mapel' => 'PAIBP-SD', 
                'nama_mapel' => 'Pendidikan Agama Islam dan Budi Pekerti', 'kelompok' => 'A', 'jumlah_jp' => 4, 'status' => 'aktif', 'created_at' => $now
            ],
            [
                'kurikulum_id' => $kurikulumIds['KM-SD'], 'kode_jenjang' => 'SD', 'kode_mapel' => 'IPAS-SD', 
                'nama_mapel' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'kelompok' => 'A', 'jumlah_jp' => 5, 'status' => 'aktif', 'created_at' => $now
            ],
            // --- SMP ---
            [
                'kurikulum_id' => $kurikulumIds['KM-SMP'], 'kode_jenjang' => 'SMP', 'kode_mapel' => 'INF-SMP', 
                'nama_mapel' => 'Informatika', 'kelompok' => 'A', 'jumlah_jp' => 2, 'status' => 'aktif', 'created_at' => $now
            ],
            // --- SMA ---
            [
                'kurikulum_id' => $kurikulumIds['KM-SMA'], 'kode_jenjang' => 'SMA', 'kode_mapel' => 'FIS-L', 
                'nama_mapel' => 'Fisika (Fase F)', 'kelompok' => 'C', 'jumlah_jp' => 5, 'status' => 'aktif', 'created_at' => $now
            ],
        ];

        // Set Default Bobot
        foreach ($dataMapel as &$m) {
            $m['bobot_tugas'] = 0.3; $m['bobot_uts'] = 0.3; $m['bobot_uas'] = 0.4; $m['bobot_absensi'] = 0.0;
        }
        $db->table('mata_pelajaran')->insertBatch($dataMapel);

        // Helper: Map ID Mapel
        $mapelIds = [];
        $maps = $db->table('mata_pelajaran')->get()->getResultArray();
        foreach ($maps as $m) {
            $mapelIds[$m['kode_mapel']] = $m['id'];
        }

        // Helper: Guru ID (Ambil yang ada atau default 1)
        $guru = $db->table('pegawai')->get()->getRow(); 
        $guruId = $guru ? $guru->id : 1; 

        // ==========================================
        // 1. SEED DATA SILABUS
        // ==========================================
        $dataSilabus = [
            // SD Merdeka: IPAS
            [
                'kode_jenjang'       => 'SD',
                'kurikulum_id'       => $kurikulumIds['KM-SD'],
                'mata_pelajaran_id'  => $mapelIds['IPAS-SD'],
                'tingkat_kelas'      => '4',
                'semester'           => 'Ganjil',
                'tahun_ajaran'       => '2024/2025',
                'jenis_kurikulum'    => 'Merdeka',
                'fase'               => 'B',
                'tema'               => 'Tumbuhan Sumber Kehidupan',
                'subtema'            => null,
                'capaian_pembelajaran' => 'Peserta didik menganalisis hubungan antara bentuk dan fungsi bagian tubuh pada hewan dan tumbuhan.',
                'alur_tujuan_pembelajaran' => '4.1 Mengidentifikasi bagian tubuh tumbuhan dan mendeskripsikan fungsinya.',
                'profil_pelajar_pancasila' => 'Bernalar Kritis, Mandiri',
                'materi_pokok'       => 'Bagian Tubuh Tumbuhan',
                'kegiatan_pembelajaran' => 'Mengamati tumbuhan di lingkungan sekolah dan diskusi kelompok.',
                'penilaian'          => 'Formatif: Lembar Kerja',
                'alokasi_waktu'      => '5 JP',
                'sumber_belajar'     => 'Buku Siswa IPAS SD',
                'created_by'         => 1,
                'status'             => 'Final',
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            // SMP Merdeka: Informatika
            [
                'kode_jenjang'       => 'SMP',
                'kurikulum_id'       => $kurikulumIds['KM-SMP'],
                'mata_pelajaran_id'  => $mapelIds['INF-SMP'],
                'tingkat_kelas'      => '7',
                'semester'           => 'Ganjil',
                'tahun_ajaran'       => '2024/2025',
                'jenis_kurikulum'    => 'Merdeka',
                'fase'               => 'D',
                'tema'               => 'Berpikir Komputasional',
                'subtema'            => null,
                'capaian_pembelajaran' => 'Peserta didik mampu menerapkan berpikir komputasional.',
                'alur_tujuan_pembelajaran' => '7.1 Menerapkan berpikir komputasional untuk persoalan optimasi.',
                'profil_pelajar_pancasila' => 'Kreatif',
                'materi_pokok'       => 'Algoritma',
                'kegiatan_pembelajaran' => 'Simulasi antrian.',
                'penilaian'          => 'Proyek',
                'alokasi_waktu'      => '4 JP',
                'sumber_belajar'     => 'Buku Informatika SMP',
                'created_by'         => 1,
                'status'             => 'Final',
                'created_at'         => $now,
                'updated_at'         => $now,
            ],
            // SMA Merdeka: Fisika
            [
                'kode_jenjang'       => 'SMA',
                'kurikulum_id'       => $kurikulumIds['KM-SMA'],
                'mata_pelajaran_id'  => $mapelIds['FIS-L'],
                'tingkat_kelas'      => '11',
                'semester'           => 'Ganjil',
                'tahun_ajaran'       => '2024/2025',
                'jenis_kurikulum'    => 'Merdeka',
                'fase'               => 'F',
                'tema'               => 'Kinematika',
                'subtema'            => null,
                'capaian_pembelajaran' => 'Peserta didik mampu menerapkan konsep kinematika.',
                'alur_tujuan_pembelajaran' => '11.1 Menganalisis GLBB.',
                'profil_pelajar_pancasila' => 'Bernalar Kritis',
                'materi_pokok'       => 'Gerak Lurus',
                'kegiatan_pembelajaran' => 'Praktikum ticker timer.',
                'penilaian'          => 'Laporan Praktikum',
                'alokasi_waktu'      => '10 JP',
                'sumber_belajar'     => 'Buku Fisika SMA',
                'created_by'         => 1,
                'status'             => 'Final',
                'created_at'         => $now,
                'updated_at'         => $now,
            ]
        ];
        
        $db->table('pembelajaran_silabus')->insertBatch($dataSilabus);
        
        // Helper: Map ID Silabus
        // Key kita buat unik kombinasi Jenjang + MapelID
        $silabusDb = $db->table('pembelajaran_silabus')->get()->getResultArray();
        $silabusIds = [];
        foreach ($silabusDb as $row) {
            $key = $row['kode_jenjang'] . '-' . $row['mata_pelajaran_id'];
            $silabusIds[$key] = $row['id'];
        }

        // ==========================================
        // 2. SEED RPP / MODUL AJAR
        // ==========================================
        $dataRPP = [
            // SD IPAS
            [
                'kode_jenjang'        => 'SD',
                'silabus_id'          => $silabusIds['SD-'.$mapelIds['IPAS-SD']] ?? null,
                'guru_id'             => $guruId,
                'jenis_kurikulum'     => 'Merdeka',
                'fase'                => 'B',
                'tema'                => null,
                'subtema'             => null,
                'pertemuan_ke'        => 1,
                'topik'               => 'Mengenal Bagian Tumbuhan',
                'tujuan_pembelajaran' => 'Mengidentifikasi akar, batang, daun.',
                'metode_pembelajaran' => 'Observasi',
                'langkah_pembelajaran'=> "1. Pendahuluan\n2. Inti (Keliling sekolah)\n3. Penutup",
                'pemahaman_bermakna'  => 'Tumbuhan memiliki organ penting.',
                'pertanyaan_pemantik' => 'Apa fungsi akar?',
                'media_alat'          => 'Tanaman, Kaca Pembesar',
                'penilaian'           => 'LKPD',
                'status'              => 'Draft',
                'created_by'          => 1,
                'created_at'          => $now,
                'updated_at'          => $now,
            ],
            // SMA Fisika
            [
                'kode_jenjang'        => 'SMA',
                'silabus_id'          => $silabusIds['SMA-'.$mapelIds['FIS-L']] ?? null,
                'guru_id'             => $guruId,
                'jenis_kurikulum'     => 'Merdeka',
                'fase'                => 'F',
                'tema'                => null,
                'subtema'             => null,
                'pertemuan_ke'        => 1,
                'topik'               => 'Konsep GLBB',
                'tujuan_pembelajaran' => 'Menghitung kecepatan dan percepatan.',
                'metode_pembelajaran' => 'PBL',
                'langkah_pembelajaran'=> 'Diskusi dan Latihan Soal.',
                'pemahaman_bermakna'  => 'Percepatan mengubah kecepatan.',
                'pertanyaan_pemantik' => 'Mengapa benda jatuh semakin cepat?',
                'media_alat'          => 'Mobil mainan, Lintasan',
                'penilaian'           => 'Tes Formatif',
                'status'              => 'Final',
                'created_by'          => 1,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]
        ];
        $db->table('pembelajaran_rpp')->insertBatch($dataRPP);
        
        // Ambil ID RPP untuk bahan ajar
        $rppDb = $db->table('pembelajaran_rpp')->get()->getResultArray();
        $rppMap = []; // Key by Jenjang (Simple approach)
        foreach ($rppDb as $row) {
            $rppMap[$row['kode_jenjang']] = $row['id'];
        }

        // ==========================================
        // 3. SEED BAHAN AJAR
        // ==========================================
        $dataBahan = [
            [
                'kode_jenjang'      => 'SD',
                'rpp_id'            => $rppMap['SD'] ?? null,
                'mata_pelajaran_id' => $mapelIds['IPAS-SD'],
                'judul_bahan'       => 'Poster Bagian Tumbuhan',
                'jenis_file'        => 'PDF',
                'file_path'         => 'uploads/materi/tumbuhan.pdf',
                'deskripsi'         => 'Infografis berwarna.',
                'status'            => 'Final',
                'created_by'        => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'kode_jenjang'      => 'SMA',
                'rpp_id'            => $rppMap['SMA'] ?? null,
                'mata_pelajaran_id' => $mapelIds['FIS-L'],
                'judul_bahan'       => 'Video Simulasi GLBB',
                'jenis_file'        => 'Link',
                'file_path'         => 'https://youtube.com/glbb',
                'deskripsi'         => 'Video pembelajaran.',
                'status'            => 'Final',
                'created_by'        => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        ];
        $db->table('pembelajaran_bahan_ajar')->insertBatch($dataBahan);

        // ==========================================
        // 4. SEED BANK SOAL
        // ==========================================
        $dataSoal = [
            [
                'kode_jenjang'      => 'SD',
                'jenis_kurikulum'   => 'Merdeka',
                'fase'              => 'B',
                'mata_pelajaran_id' => $mapelIds['IPAS-SD'],
                'silabus_id'        => $silabusIds['SD-'.$mapelIds['IPAS-SD']] ?? null,
                'kode_soal'         => 'IPAS-01',
                'topik'             => 'Bagian Tumbuhan',
                'jenis_soal'        => 'PG',
                'tingkat_kesulitan' => 'Mudah',
                'level_kognitif'    => 'L1',
                'pertanyaan'        => 'Bagian tumbuhan yang menyerap air adalah...',
                'opsi_jawaban'      => json_encode(['A'=>'Daun', 'B'=>'Akar', 'C'=>'Batang']),
                'kunci_jawaban'     => 'B',
                'is_acak_opsi'      => 1,
                'bobot'             => 10,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        ];
        $db->table('pembelajaran_bank_soal')->insertBatch($dataSoal);

        // ==========================================
        // 5. SEED EVALUASI
        // ==========================================
        $dataEvaluasi = [
            [
                'kode_jenjang'      => 'SD',
                'mata_pelajaran_id' => $mapelIds['IPAS-SD'],
                'silabus_id'        => $silabusIds['SD-'.$mapelIds['IPAS-SD']] ?? null,
                'judul_evaluasi'    => 'Kuis Tumbuhan',
                'jenis_evaluasi'    => 'Kuis',
                'tanggal_mulai'     => date('Y-m-d 08:00:00'),
                'tanggal_selesai'   => date('Y-m-d 23:59:59'),
                'durasi'            => 30,
                'kkm'               => 70,
                'status'            => 'Published',
                'instruksi'         => 'Kerjakan sendiri.',
                'created_by'        => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        ];
        $db->table('pembelajaran_evaluasi_belajar')->insertBatch($dataEvaluasi);

        echo "Seeding Modul Pembelajaran (Sinkron Database & Migrasi) Selesai.\n";
    }
}