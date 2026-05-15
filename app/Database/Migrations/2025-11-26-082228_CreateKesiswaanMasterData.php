<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKesiswaanMasterData extends Migration
{
    public function up()
    {
        // Matikan cek FK sementara
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        // ==========================================
        // 1. KELOMPOK EKSTRAKURIKULER
        // ==========================================

        // Tabel Master Ekskul
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20], // Kunci Anti-Bocor
            'nama_ekskul' => ['type' => 'VARCHAR', 'constraint' => 100],
            'kategori' => ['type' => 'ENUM', 'constraint' => ['Olahraga', 'Seni', 'Sains', 'Religi', 'Lainnya'], 'default' => 'Lainnya'],
            'guru_pembina_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'hari_latihan' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], // Senin, Selasa, dll
            'jam_mulai' => ['type' => 'TIME', 'null' => true],
            'jam_selesai' => ['type' => 'TIME', 'null' => true],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'foto_cover' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('kesiswaan_ekskul', true);

        // Tabel Anggota Ekskul & Nilai
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20],
            'tahun_ajar_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'ekskul_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nilai_huruf' => ['type' => 'VARCHAR', 'constraint' => 2, 'null' => true], // A, B, C
            'deskripsi_nilai' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('ekskul_id', 'kesiswaan_ekskul', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kesiswaan_ekskul_anggota', true);

        // Tabel Presensi Ekskul
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ekskul_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal' => ['type' => 'DATE'],
            'materi_kegiatan' => ['type' => 'TEXT', 'null' => true],
            'data_presensi' => ['type' => 'JSON'], // Format: [{"siswa_id": 1, "status": "H"}, ...] -> Efisiensi tinggi
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('kesiswaan_ekskul_presensi', true);

        // ==========================================
        // 2. KELOMPOK ORGANISASI (OSIS/MPK)
        // ==========================================

        // Tabel Struktur Organisasi
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20],
            'tahun_ajar_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jenis_organisasi' => ['type' => 'ENUM', 'constraint' => ['OSIS', 'MPK', 'LAINNYA'], 'default' => 'OSIS'],
            'jabatan' => ['type' => 'VARCHAR', 'constraint' => 100], // Ketua, Sekretaris, dll
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status_aktif' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('kesiswaan_organisasi', true);

        // ==========================================
        // 3. KELOMPOK BIMBINGAN KONSELING (BK)
        // ==========================================

        // Tabel Master Poin (Kategori Pelanggaran/Prestasi)
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20],
            'jenis' => ['type' => 'ENUM', 'constraint' => ['Pelanggaran', 'Prestasi'], 'default' => 'Pelanggaran'],
            'nama_kasus' => ['type' => 'VARCHAR', 'constraint' => 200],
            'poin' => ['type' => 'INT', 'constraint' => 5, 'default' => 0], // Negatif jika pelanggaran di logic controller, atau simpan unsigned disini
            'tindak_lanjut_default' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('kesiswaan_bk_kategori', true);

        // Tabel Catatan Kasus Siswa
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20],
            'tahun_ajar_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'bk_kategori_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal_kejadian' => ['type' => 'DATE'],
            'keterangan_detail' => ['type' => 'TEXT', 'null' => true],
            'tindak_lanjut' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status_penyelesaian' => ['type' => 'ENUM', 'constraint' => ['Open', 'Proses', 'Selesai'], 'default' => 'Open'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('bk_kategori_id', 'kesiswaan_bk_kategori', 'id');
        $this->forge->createTable('kesiswaan_bk_catatan', true);

        // ==========================================
        // 4. KELOMPOK PRESTASI (BARU)
        // ==========================================
        
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20],
            'tahun_ajar_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_prestasi' => ['type' => 'VARCHAR', 'constraint' => 255],
            'jenis_prestasi' => ['type' => 'ENUM', 'constraint' => ['Akademik', 'Non-Akademik'], 'default' => 'Non-Akademik'],
            'tingkat' => ['type' => 'ENUM', 'constraint' => ['Sekolah', 'Kecamatan', 'Kabupaten', 'Provinsi', 'Nasional', 'Internasional'], 'default' => 'Sekolah'],
            'peringkat' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true], // Juara 1, Finalis, dll
            'penyelenggara' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'tanggal_prestasi' => ['type' => 'DATE', 'null' => true],
            'bukti_sertifikat' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        // Note: Tidak menambahkan Foreign Key ke 'siswa' di sini untuk menghindari ketergantungan urutan migrasi,
        // karena ini adalah file migrasi terpisah. Namun jika 'siswa' dijamin ada, bisa ditambahkan manual.
        $this->forge->createTable('kesiswaan_prestasi', true);

        // ==========================================
        // 5. KELOMPOK ALUMNI (TRACER STUDY)
        // ==========================================

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20],
            'siswa_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // Data siswa lama tetap disimpan
            'tahun_lulus' => ['type' => 'YEAR'],
            'status_kegiatan' => ['type' => 'ENUM', 'constraint' => ['Kuliah', 'Bekerja', 'Wirausaha', 'Belum Ada'], 'default' => 'Belum Ada'],
            'nama_instansi' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true], // Nama Kampus / Kantor
            'jabatan_jurusan' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true], // Jurusan Kuliah / Posisi Kerja
            'kontak_alumni' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'testimoni' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('kesiswaan_alumni', true);

        // Aktifkan kembali cek FK
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');

        $this->forge->dropTable('kesiswaan_alumni', true);
        $this->forge->dropTable('kesiswaan_prestasi', true); // Drop tabel baru
        $this->forge->dropTable('kesiswaan_bk_catatan', true);
        $this->forge->dropTable('kesiswaan_bk_kategori', true);
        $this->forge->dropTable('kesiswaan_organisasi', true);
        $this->forge->dropTable('kesiswaan_ekskul_presensi', true);
        $this->forge->dropTable('kesiswaan_ekskul_anggota', true);
        $this->forge->dropTable('kesiswaan_ekskul', true);

        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
    }
}