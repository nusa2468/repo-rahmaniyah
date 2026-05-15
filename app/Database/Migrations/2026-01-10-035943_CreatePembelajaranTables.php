<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePembelajaranTables extends Migration
{
    public function up()
    {
        // Matikan Check FK sementara
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        // ==========================================
        // 1. TABEL SILABUS (Support K13 & Kurikulum Merdeka)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'kurikulum_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'mata_pelajaran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tingkat_kelas' => ['type' => 'VARCHAR', 'constraint' => 10],
            'semester' => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap']],
            'tahun_ajaran' => ['type' => 'VARCHAR', 'constraint' => 20],
            'jenis_kurikulum' => ['type' => 'ENUM', 'constraint' => ['K13', 'Merdeka'], 'default' => 'Merdeka'],
            'fase' => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C', 'D', 'E', 'F'], 'null' => true],
            'tema'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'subtema' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'kompetensi_inti' => ['type' => 'TEXT', 'null' => true],
            'kompetensi_dasar' => ['type' => 'TEXT', 'null' => true],
            'indikator' => ['type' => 'TEXT', 'null' => true],
            'capaian_pembelajaran' => ['type' => 'TEXT', 'null' => true],
            'alur_tujuan_pembelajaran' => ['type' => 'TEXT', 'null' => true],
            'profil_pelajar_pancasila' => ['type' => 'TEXT', 'null' => true],
            'materi_pokok' => ['type' => 'TEXT'],
            'kegiatan_pembelajaran' => ['type' => 'TEXT', 'null' => true],
            'penilaian' => ['type' => 'TEXT', 'null' => true],
            'alokasi_waktu' => ['type' => 'VARCHAR', 'constraint' => 50],
            'sumber_belajar' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Draft', 'Final'], 'default' => 'Draft'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Index untuk performa pencarian
        $this->forge->addKey(['kode_jenjang', 'mata_pelajaran_id', 'tingkat_kelas', 'tahun_ajaran']);
        // FK ke mata_pelajaran (pastikan tabel ini ada)
        $this->forge->addForeignKey('mata_pelajaran_id', 'mata_pelajaran', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pembelajaran_silabus', true);

        // ==========================================
        // 2. TABEL RPP / MODUL AJAR
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'silabus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'guru_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'jenis_kurikulum' => ['type' => 'ENUM', 'constraint' => ['K13', 'Merdeka'], 'default' => 'Merdeka'],
            'fase'    => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C', 'D', 'E', 'F'], 'null' => true],
            'tema'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'subtema' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'pertemuan_ke' => ['type' => 'INT', 'constraint' => 3],
            'topik' => ['type' => 'VARCHAR', 'constraint' => 255],
            'tujuan_pembelajaran' => ['type' => 'TEXT'], 
            'metode_pembelajaran' => ['type' => 'VARCHAR', 'constraint' => 255],
            'langkah_pembelajaran' => ['type' => 'TEXT'], 
            'pemahaman_bermakna' => ['type' => 'TEXT', 'null' => true],
            'pertanyaan_pemantik' => ['type' => 'TEXT', 'null' => true],
            'media_alat' => ['type' => 'TEXT', 'null' => true],
            'penilaian' => ['type' => 'TEXT', 'null' => true], 
            
            'status' => ['type' => 'ENUM', 'constraint' => ['Draft', 'Final'], 'default' => 'Draft'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned'    => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->addForeignKey('silabus_id', 'pembelajaran_silabus', 'id', 'CASCADE', 'CASCADE');
        // $this->forge->addForeignKey('guru_id', 'pegawai', 'id', 'SET NULL', 'CASCADE'); // Opsional jika tabel pegawai ada
        $this->forge->createTable('pembelajaran_rpp', true);

        // ==========================================
        // 3. TABEL BAHAN AJAR
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'rpp_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'mata_pelajaran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'judul_bahan' => ['type' => 'VARCHAR', 'constraint' => 255],
            'jenis_file' => ['type' => 'ENUM', 'constraint' => ['PDF', 'PPT', 'Video', 'Link', 'Doc']],
            'file_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Draft', 'Final'], 'default' => 'Final'],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->addForeignKey('rpp_id', 'pembelajaran_rpp', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('pembelajaran_bahan_ajar', true);

        // ==========================================
        // 4. TABEL BANK SOAL
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'jenis_kurikulum' => ['type' => 'ENUM', 'constraint' => ['K13', 'Merdeka'], 'default' => 'Merdeka'],
            'fase' => ['type' => 'ENUM', 'constraint' => ['A', 'B', 'C', 'D', 'E', 'F'], 'null' => true],
            'mata_pelajaran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'silabus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'kode_soal' => ['type' => 'VARCHAR', 'constraint' => 50],
            'topik' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'jenis_soal' => ['type' => 'ENUM', 'constraint' => ['PG', 'Essay', 'Isian', 'TF']],
            'tingkat_kesulitan' => ['type' => 'ENUM', 'constraint' => ['Mudah', 'Sedang', 'Sukar'], 'default' => 'Sedang'],
            'level_kognitif' => ['type' => 'ENUM', 'constraint' => ['L1', 'L2', 'L3'], 'default' => 'L1', 'null' => true],
            'pertanyaan' => ['type' => 'TEXT'],
            'opsi_jawaban' => ['type' => 'JSON', 'null' => true],
            'kunci_jawaban' => ['type' => 'TEXT'],
            'is_acak_opsi' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'bobot' => ['type' => 'INT', 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['kode_jenjang', 'topik', 'tingkat_kesulitan', 'level_kognitif', 'fase']);
        $this->forge->createTable('pembelajaran_bank_soal', true);

        // ==========================================
        // 5. TABEL EVALUASI BELAJAR (UJIAN/TUGAS)
        // ==========================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'mata_pelajaran_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'silabus_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'judul_evaluasi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'jenis_evaluasi' => ['type' => 'ENUM', 'constraint' => ['Tugas', 'Kuis', 'UTS', 'UAS', 'Tryout'], 'default' => 'Tugas'],
            'tanggal_mulai' => ['type' => 'DATETIME', 'null' => true],
            'tanggal_selesai' => ['type' => 'DATETIME', 'null' => true],
            'durasi' => ['type' => 'INT', 'constraint' => 5, 'default' => 0], // dalam menit
            'kkm' => ['type' => 'INT', 'constraint' => 3, 'default' => 75],
            'status' => ['type' => 'ENUM', 'constraint' => ['Draft', 'Published'], 'default' => 'Draft'],
            'instruksi' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['kode_jenjang', 'jenis_evaluasi', 'status']);
        $this->forge->addForeignKey('silabus_id', 'pembelajaran_silabus', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('pembelajaran_evaluasi_belajar', true);

        // Hidupkan Kembali FK Check
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->forge->dropTable('pembelajaran_evaluasi_belajar', true);
        $this->forge->dropTable('pembelajaran_bank_soal', true);
        $this->forge->dropTable('pembelajaran_bahan_ajar', true);
        $this->forge->dropTable('pembelajaran_rpp', true);
        $this->forge->dropTable('pembelajaran_silabus', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}