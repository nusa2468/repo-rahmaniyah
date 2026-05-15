<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAcademicMasterData extends Migration
{
    public function up()
    {
        // PENTING: Matikan pemeriksaan FK sementara untuk menghindari error urutan pembuatan
        $this->db->disableForeignKeyChecks();

        // =================================================================
        // 1. Tabel Tahun Ajaran
        // =================================================================
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Global'],
            'tahun_ajaran'    => ['type' => 'VARCHAR', 'constraint' => 50],
            'semester'        => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap'], 'comment' => 'Semester: Ganjil atau Genap'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['aktif', 'tidak aktif'], 'default' => 'tidak aktif'],
            'tanggal_mulai'   => ['type' => 'DATE', 'null' => true],
            'tanggal_selesai' => ['type' => 'DATE', 'null' => true],
            'keterangan'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); 

        // FK ke jenjang_sekolah
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE', 'tahun_ajaran_kode_jenjang_foreign');
        
        // Parameter kedua 'true' -> CREATE TABLE IF NOT EXISTS
        $this->forge->createTable('tahun_ajaran', true);

        // =================================================================
        // 2. Tabel Jurusan
        // =================================================================
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_jurusan' => ['type' => 'VARCHAR', 'constraint' => 100],
            'kode_jurusan' => ['type' => 'VARCHAR', 'constraint' => 20],
            'status'       => ['type' => 'ENUM', 'constraint' => ['aktif', 'tidak aktif'], 'default' => 'aktif'],
            'keterangan'   => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('jurusan', true);

        // =================================================================
        // 3. Tabel Kurikulum
        // =================================================================
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_kurikulum' => ['type' => 'VARCHAR', 'constraint' => 20],
            'kode_jenjang'   => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_kurikulum' => ['type' => 'VARCHAR', 'constraint' => 100],
            'deskripsi'      => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'keterangan'     => ['type' => 'TEXT', 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['aktif', 'tidak aktif'], 'default' => 'aktif'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_kurikulum');
        
        $this->forge->addKey('kode_jenjang');
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('kurikulum', true);

        // =================================================================
        // 4. Tabel Mata Pelajaran
        // =================================================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'  => [
                'type'       => 'VARCHAR',
                'constraint' => 20, 
                'null'       => true,
                'comment'    => 'Identitas Unit Kerja (FK ke jenjang_sekolah)',
            ],
            'kurikulum_id'  => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Relasi ke tabel kurikulum',
            ],
            'kode_mapel'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_mapel'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'kelompok'      => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['aktif', 'tidak aktif'], 'default' => 'aktif'],
            'tingkat'       => ['type' => 'INT', 'constraint' => 2, 'null' => true],
            'semester'      => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment'    => 'Semester dimana mapel ini diajarkan (Ganjil/Genap atau angka)'
            ],
            'jumlah_jp'     => [
                'type'       => 'INT',
                'constraint' => 3,
                'default'    => 2,
                'comment'    => 'Jam Pelajaran per minggu'
            ],
            'bobot_tugas'   => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.30],
            'bobot_uts'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.30],
            'bobot_uas'     => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.30],
            'bobot_absensi' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.10],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['kurikulum_id', 'kode_mapel']);
        
        // FK ke Kurikulum
        $this->forge->addForeignKey('kurikulum_id', 'kurikulum', 'id', 'SET NULL', 'CASCADE');
        // FK ke Jenjang Sekolah
        $this->forge->addKey('kode_jenjang');
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('mata_pelajaran', true);

        // =================================================================
        // 5. Tabel Kalender Pendidikan
        // =================================================================
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'tahun_ajaran_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'Relasi ke tabel tahun_ajaran'
            ],
            'title'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'start'           => ['type' => 'DATETIME'],
            'end'             => ['type' => 'DATETIME', 'null' => true],
            'color'           => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'keterangan'      => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        
        $this->forge->addKey('kode_jenjang');
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        
        $this->forge->addKey('tahun_ajaran_id'); 
        $this->forge->addForeignKey('tahun_ajaran_id', 'tahun_ajaran', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('kalender_pendidikan', true);

        // Nyalakan kembali pemeriksaan FK
        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->dropTable('kalender_pendidikan', true);
        $this->forge->dropTable('mata_pelajaran', true);
        $this->forge->dropTable('kurikulum', true);
        $this->forge->dropTable('jurusan', true);
        $this->forge->dropTable('tahun_ajaran', true);

        $this->db->enableForeignKeyChecks();
    }
}