<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCmsTables extends Migration
{
    public function up()
    {
        // 1. Tabel Berita
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], // Null = Global
            'judul'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'konten'       => ['type' => 'TEXT'],
            'gambar'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['published', 'draft', 'archived'], 'default' => 'published'],
            'id_penulis'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('slug'); 
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('berita', true);

        // 2. Tabel Pengumuman
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'judul'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'konten'       => ['type' => 'TEXT'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['published', 'draft', 'archived'], 'default' => 'published'],
            'tanggal_berakhir' => ['type' => 'DATE', 'null' => true],
            'id_penulis'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('pengumuman', true);

        // 3. Tabel Agenda
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'nama_kegiatan'=> ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'tanggal_mulai'=> ['type' => 'DATETIME'],
            'tanggal_selesai' => ['type' => 'DATETIME', 'null' => true],
            'tempat'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'keterangan'   => ['type' => 'TEXT', 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['published', 'draft'], 'default' => 'published'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('agenda', true);

        // 4. Tabel Album Foto
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'judul'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'deskripsi'    => ['type' => 'TEXT', 'null' => true],
            'cover'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['publik', 'internal'], 'default' => 'publik'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('slug');
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('album_foto', true);

        // 5. Tabel Foto (Detail) - UPDATE: Insert kode_jenjang
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], // Added here
            'id_album'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'file_foto'    => ['type' => 'VARCHAR', 'constraint' => 255],
            'caption'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); // Added Key
        $this->forge->addForeignKey('id_album', 'album_foto', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('foto', true);
    }

    public function down()
    {
        $this->forge->dropTable('foto', true);
        $this->forge->dropTable('album_foto', true);
        $this->forge->dropTable('agenda', true);
        $this->forge->dropTable('pengumuman', true);
        $this->forge->dropTable('berita', true);
    }
}