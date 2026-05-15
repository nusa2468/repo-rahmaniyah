<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class ElearningSeeder extends Seeder
{
    public function run()
    {
        // --------------------------------------------------------------------
        // 0. PERSIAPAN DATA REFERENSI
        // --------------------------------------------------------------------
        
        // Ambil ID Guru (Dari tabel Pegawai)
        // Note: Kita asumsikan ada kolom 'jenis_pegawai' atau kita ambil sembarang pegawai sebagai default
        $guruBuilder = $this->db->table('pegawai')->select('id');
        
        // Cek apakah kolom jenis_pegawai ada untuk filter (Optional Safety)
        if ($this->db->fieldExists('jenis_pegawai', 'pegawai')) {
            $guruBuilder->where('jenis_pegawai', 'guru');
        }
        
        $guru = $guruBuilder->limit(1)->get()->getRow();
        
        // Fallback: Jika tidak ada guru spesifik, ambil pegawai pertama
        if (!$guru) {
            $guru = $this->db->table('pegawai')->select('id')->limit(1)->get()->getRow();
        }
        
        $idGuru = $guru ? $guru->id : 1; 

        // Ambil ID Siswa
        $siswa = $this->db->table('siswa')->select('id')->limit(1)->get()->getRow();
        $idSiswa = $siswa ? $siswa->id : 1;

        // AMBIL KELAS AKTIF (Hasil dari PrimaryAcademicDataSeeder)
        $existingClasses = $this->db->table('kelas')
                              ->where('is_aktif', 1)
                              ->limit(5)
                              ->get()->getResultArray();

        if (empty($existingClasses)) {
            echo " [SKIP] Tidak ada kelas aktif ditemukan. Jalankan PrimaryAcademicDataSeeder terlebih dahulu.\n";
            return;
        }

        echo ">>> Mengisi konten E-Learning untuk " . count($existingClasses) . " kelas aktif (Guru diambil dari tabel Pegawai)...\n";

        // --------------------------------------------------------------------
        // 1. SINKRONISASI KELAS -> COURSES & ISI KONTEN
        // --------------------------------------------------------------------
        
        foreach ($existingClasses as $kelas) {
            $classId = $kelas['id']; // ID dari tabel akademik 'kelas'
            $jenjang = $kelas['kode_jenjang']; 
            $namaKelas = $kelas['nama_kelas'];

            // A. PASTIKAN COURSE ADA DI TABEL EL_COURSES (Parent Row)
            // Cek apakah course dengan ID ini sudah ada di el_courses?
            // Kita gunakan ID yang sama dengan Kelas agar sinkron (One-to-One Concept)
            $checkCourse = $this->db->table('el_courses')->where('id', $classId)->countAllResults();
            
            // Generate Kode Gabung Unik
            $kodeGabung = strtoupper(substr($jenjang, 0, 2) . '-' . $classId . '-' . rand(100, 999));

            // Ambil warna banner
            $bannerColor = 'gray';
            if ($jenjang == 'SD') $bannerColor = 'green';
            elseif ($jenjang == 'SMP') $bannerColor = 'blue';
            elseif ($jenjang == 'SMA') $bannerColor = 'purple';

            // Logika Wali Kelas sebagai Guru Course (jika ada), jika tidak pakai default $idGuru
            $idGuruKelas = !empty($kelas['id_wali_kelas']) ? $kelas['id_wali_kelas'] : $idGuru;

            $courseData = [
                'id'           => $classId, // Paksa ID sama dengan Kelas Akademik
                'kode_jenjang' => $jenjang,
                'nama_kelas'   => $namaKelas,
                'id_guru'      => $idGuruKelas, // ID Pegawai
                'deskripsi'    => "Ruang Belajar Digital untuk $namaKelas",
                'banner_color' => $bannerColor,
                'is_active'    => 1,
                'created_at'   => Time::now(),
                'updated_at'   => Time::now(),
                'deleted_at'   => null
            ];

            if ($checkCourse == 0) {
                // Tambahkan kode gabung hanya untuk data baru (karena unique constraint)
                $courseData['kode_gabung'] = $kodeGabung;
                $this->smartInsert('el_courses', $courseData);
            } else {
                // Update data yang ada (tanpa kode gabung & ID agar tidak error unique/PK)
                unset($courseData['id']); 
                unset($courseData['kode_gabung']);
                $this->db->table('el_courses')->where('id', $classId)->update(['updated_at' => Time::now()]);
            }

            // ID COURSE YANG VALID UNTUK ANAK TABEL
            $validCourseId = $classId; 

            // ================================================================
            // MULAI SEEDING KONTEN (Topics, Materials, Quizzes)
            // ================================================================

            // B. SEED TOPICS
            $topikList = [];
            if ($jenjang == 'SD') {
                $topikList = ['Tema 1: Indahnya Kebersamaan', 'Tema 2: Selalu Berhemat Energi'];
            } elseif ($jenjang == 'SMP') {
                $topikList = ['BAB 1: Bilangan Bulat', 'BAB 2: Himpunan'];
            } else {
                $topikList = ['BAB 1: Dimensi Tiga', 'BAB 2: Statistika'];
            }

            $existingTopics = $this->db->table('el_topics')->where('id_kelas', $validCourseId)->countAllResults();
            
            if ($existingTopics == 0) {
                foreach ($topikList as $namaTopik) {
                    $dataTopic = [
                        'id_kelas'   => $validCourseId, // FK Aman karena Parent sudah dibuat di atas
                        'nama_topik' => $namaTopik,
                        'created_at' => Time::now(),
                        'updated_at' => Time::now(),
                        'deleted_at' => null 
                    ];
                    $this->smartInsert('el_topics', $dataTopic);
                }
            }

            // Ambil ID Topik pertama untuk diisi konten
            $firstTopic = $this->db->table('el_topics')->where('id_kelas', $validCourseId)->get()->getFirstRow();
            $topicId = $firstTopic ? $firstTopic->id : null;

            if ($topicId) {
                // C. SEED CONTENTS (Materi & Tugas)
                $contents = [
                    [
                        'id_kelas'      => $validCourseId,
                        'id_topic'      => $topicId,
                        'tipe'          => 'materi',
                        'judul'         => "Materi Pengantar {$jenjang}",
                        'isi_teks'      => '<p>Silakan pelajari materi dasar berikut ini.</p>',
                        'file_lampiran' => null,
                        'deadline'      => null,
                        'poin_max'      => 0,
                        'created_at'    => Time::now(),
                        'updated_at'    => Time::now(),
                        'deleted_at'    => null
                    ],
                    [
                        'id_kelas'      => $validCourseId,
                        'id_topic'      => $topicId,
                        'tipe'          => 'tugas',
                        'judul'         => "Tugas Harian 1: Rangkuman",
                        'isi_teks'      => '<p>Buat rangkuman PDF.</p>',
                        'file_lampiran' => 'contoh_format.pdf',
                        'deadline'      => date('Y-m-d 23:59:59', strtotime('+3 days')),
                        'poin_max'      => 100,
                        'created_at'    => Time::now(),
                        'updated_at'    => Time::now(),
                        'deleted_at'    => null
                    ]
                ];

                $cekKonten = $this->db->table('el_contents')->where('id_kelas', $validCourseId)->countAllResults();
                if ($cekKonten == 0) {
                    foreach ($contents as $c) {
                        $this->smartInsert('el_contents', $c);
                    }
                }

                // D. SEED QUIZ
                $cekQuiz = $this->db->table('el_quizzes')->where('id_kelas', $validCourseId)->countAllResults();
                if ($cekQuiz == 0) {
                    $quizData = [
                        'id_kelas'     => $validCourseId,
                        'id_topic'     => $topicId,
                        'judul'        => 'Kuis Evaluasi Bab 1',
                        'deskripsi'    => 'Kerjakan dengan jujur.',
                        'durasi_menit' => 15,
                        'deadline'     => date('Y-m-d 23:59:59', strtotime('+7 days')),
                        'is_published' => 1,
                        'created_at'   => Time::now(),
                        'updated_at'   => Time::now(),
                        'deleted_at'   => null
                    ];
                    $this->smartInsert('el_quizzes', $quizData);
                    
                    $quizId = $this->db->insertID();

                    // Seed Soal
                    if ($quizId) {
                        $soalData = [
                            'id_quiz'       => $quizId,
                            'pertanyaan'    => 'Apakah bumi itu bulat?',
                            'tipe_soal'     => 'pg',
                            'opsi_a'        => 'Ya, bulat',
                            'opsi_b'        => 'Tidak, datar',
                            'opsi_c'        => 'Kotak',
                            'opsi_d'        => 'Segitiga',
                            'opsi_e'        => 'Tidak tahu',
                            'jawaban_benar' => 'A',
                            'bobot_nilai'   => 100,
                            'created_at'    => Time::now(),
                            'updated_at'    => Time::now(),
                            'deleted_at'    => null
                        ];
                        $this->smartInsert('el_questions', $soalData);
                    }
                }
            }

            // E. SEED FORUM POSTS
            if ($this->db->tableExists('elearning_posts')) {
                $cekPost = $this->db->table('elearning_posts')->where('class_id', $validCourseId)->countAllResults();
                if ($cekPost == 0) {
                    $postData = [
                        'class_id'   => $validCourseId,
                        'user_id'    => $idGuruKelas, // Menggunakan Guru/Wali Kelas
                        'content'    => "Selamat datang di kelas {$namaKelas}!",
                        'type'       => 'announcement',
                        'created_at' => Time::now(),
                        'updated_at' => Time::now(),
                        'deleted_at' => null
                    ];
                    $this->smartInsert('elearning_posts', $postData);
                    
                    $lastPostId = $this->db->insertID();
                    
                    if ($lastPostId && $this->db->tableExists('elearning_comments')) {
                        $commentData = [
                            'post_id'    => $lastPostId,
                            'user_id'    => $idSiswa,
                            'comment'    => 'Siap Pak/Bu Guru.',
                            'created_at' => Time::now(),
                            'updated_at' => Time::now(),
                            'deleted_at' => null
                        ];
                        $this->smartInsert('elearning_comments', $commentData);
                    }
                }
            }
        }

        // --------------------------------------------------------------------
        // 2. SEED SUBMISSIONS (Untuk Tugas Pertama yg ditemukan)
        // --------------------------------------------------------------------
        if ($this->db->tableExists('el_submissions')) {
            $tugas = $this->db->table('el_contents')->where('tipe', 'tugas')->limit(1)->get()->getRow();
            
            if ($tugas) {
                $cekSub = $this->db->table('el_submissions')
                             ->where('id_content', $tugas->id)
                             ->where('id_siswa', $idSiswa)
                             ->countAllResults();

                if ($cekSub == 0) {
                    $subData = [
                        'id_content'   => $tugas->id,
                        'id_siswa'     => $idSiswa,
                        'file_path'    => 'dummy_tugas.pdf',
                        'file_name'    => 'Tugas_Saya.pdf',
                        'catatan'      => 'Sudah dikerjakan.',
                        'nilai'        => 85,
                        'status'       => 'graded',
                        'submitted_at' => Time::now(),
                        //'graded_at'  => Time::now(), // Pastikan kolom ini ada di migration jika ingin dipakai
                        'created_at'   => Time::now(),
                        'updated_at'   => Time::now(),
                        'deleted_at'   => null
                    ];
                    $this->smartInsert('el_submissions', $subData);
                }
            }
        }
        
        echo ">>> SEEDER E-LEARNING SELESAI. Semua konten telah disuntikkan.\n";
    }

    /**
     * Helper untuk insert data dengan aman.
     * Hanya memasukkan kolom yang BENAR-BENAR ADA di tabel database.
     */
    private function smartInsert($table, $data)
    {
        if (!$this->db->tableExists($table)) {
            return;
        }

        $fields = $this->db->getFieldNames($table);
        $cleanData = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $cleanData[$key] = $value;
            }
        }

        if (!empty($cleanData)) {
            try {
                $this->db->table($table)->insert($cleanData);
            } catch (\Exception $e) {
                // Ignore duplicate or constraint error
            }
        }
    }
}