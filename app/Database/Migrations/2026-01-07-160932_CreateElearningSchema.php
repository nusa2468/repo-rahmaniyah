<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateElearningSchema extends Migration
{
    public function up()
    {
        // ==========================================
        // 1. TABEL COURSES (Kelas E-Learning)
        // ==========================================
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'        => ['type' => 'VARCHAR', 'constraint' => 10], 
            'nama_kelas'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'id_jadwal_pelajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_guru'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // Referensi ke Pegawai
            'kode_gabung'         => ['type' => 'VARCHAR', 'constraint' => 10, 'unique' => true],
            'deskripsi'           => ['type' => 'TEXT', 'null' => true],
            'banner_color'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'blue'],
            'cover_image'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_active'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        
        // UPDATE: FK ke tabel 'pegawai' (karena tabel 'guru' sudah diganti)
        // Logika aplikasi harus memastikan pegawai yang dipilih adalah Guru
        $this->forge->addForeignKey('id_guru', 'pegawai', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('el_courses');

        // ==========================================
        // 2. TABEL TOPICS (Bab/Pertemuan)
        // ==========================================
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_kelas'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_topik' => ['type' => 'VARCHAR', 'constraint' => 100],
            'urutan'     => ['type' => 'INT', 'constraint' => 5, 'default' => 0], // Tambahan untuk sorting
            'status'     => ['type' => 'ENUM', 'constraint' => ['draft', 'published'], 'default' => 'published'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kelas', 'el_courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('el_topics');

        // ==========================================
        // 3. TABEL CONTENTS (Materi/Tugas)
        // ==========================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_kelas'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // Redundansi untuk performa query
            'id_topic'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'tipe'          => ['type' => 'ENUM', 'constraint' => ['materi', 'tugas', 'video', 'file'], 'default' => 'materi'],
            'judul'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'konten'        => ['type' => 'TEXT', 'null' => true, 'comment' => 'Bisa text, link video, atau nama file'],
            'deskripsi'     => ['type' => 'TEXT', 'null' => true],
            'urutan'        => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'status'        => ['type' => 'ENUM', 'constraint' => ['draft', 'published'], 'default' => 'published'],
            'deadline'      => ['type' => 'DATETIME', 'null' => true],
            'poin_max'      => ['type' => 'INT', 'constraint' => 5, 'default' => 100],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kelas', 'el_courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_topic', 'el_topics', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('el_contents');

        // ==========================================
        // 4. TABEL ENROLLMENT (Peserta Kelas)
        // ==========================================
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_kelas'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_siswa'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], 
            'role'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'student'],
            'status'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'joined_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kelas', 'el_courses', 'id', 'CASCADE', 'CASCADE');
        // FK ke Siswa
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('el_enrollment');

        // ==========================================
        // 5. TABEL QUIZZES
        // ==========================================
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_kelas'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_topic'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'judul'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'deskripsi'    => ['type' => 'TEXT', 'null' => true],
            'durasi_menit' => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'deadline'     => ['type' => 'DATETIME', 'null' => true],
            'is_published' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_kelas', 'el_courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_topic', 'el_topics', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('el_quizzes');

        // ==========================================
        // 6. QUESTIONS
        // ==========================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_quiz'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'pertanyaan'    => ['type' => 'TEXT'],
            'tipe_soal'     => ['type' => 'ENUM', 'constraint' => ['pg', 'essay'], 'default' => 'pg'],
            'opsi_a'        => ['type' => 'TEXT', 'null' => true],
            'opsi_b'        => ['type' => 'TEXT', 'null' => true],
            'opsi_c'        => ['type' => 'TEXT', 'null' => true],
            'opsi_d'        => ['type' => 'TEXT', 'null' => true],
            'opsi_e'        => ['type' => 'TEXT', 'null' => true],
            'jawaban_benar' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'bobot_nilai'   => ['type' => 'INT', 'constraint' => 5, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_quiz', 'el_quizzes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('el_questions');

        // ==========================================
        // 7. QUIZ GRADES
        // ==========================================
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_quiz'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_siswa'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jawaban_user' => ['type' => 'JSON', 'null' => true],
            'nilai_total'  => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'status'       => ['type' => 'ENUM', 'constraint' => ['draft', 'submitted', 'graded'], 'default' => 'draft'],
            'started_at'   => ['type' => 'DATETIME', 'null' => true],
            'finished_at'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_quiz', 'el_quizzes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('el_quiz_grades');

        // ==========================================
        // 8. POSTS (Diskusi/Pengumuman)
        // ==========================================
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'class_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // ID User (Auth)
            'content'    => ['type' => 'TEXT'],
            'type'       => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'announcement'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('class_id', 'el_courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('elearning_posts');

        // ==========================================
        // 9. COMMENTS
        // ==========================================
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'post_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // ID User (Auth)
            'comment'    => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('post_id', 'elearning_posts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('elearning_comments');

        // ==========================================
        // 10. SUBMISSIONS (Pengumpulan Tugas)
        // ==========================================
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_content'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], 
            'id_siswa'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'file_path'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'file_name'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'catatan'      => ['type' => 'TEXT', 'null' => true],
            'nilai'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['submitted', 'graded', 'late'], 'default' => 'submitted'],
            'submitted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_content', 'el_contents', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('el_submissions');
    }

    public function down()
    {
        $this->forge->dropTable('el_submissions', true);
        $this->forge->dropTable('elearning_comments', true);
        $this->forge->dropTable('elearning_posts', true);
        $this->forge->dropTable('el_quiz_grades', true);
        $this->forge->dropTable('el_questions', true);
        $this->forge->dropTable('el_quizzes', true);
        $this->forge->dropTable('el_enrollment', true);
        $this->forge->dropTable('el_contents', true);
        $this->forge->dropTable('el_topics', true);
        $this->forge->dropTable('el_courses', true);
    }
}