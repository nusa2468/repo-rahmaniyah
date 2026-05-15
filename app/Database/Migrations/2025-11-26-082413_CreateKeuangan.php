<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKeuangan extends Migration
{
    public function up()
    {
        // 1. Tabel: jenis_pembayaran (Kategori Pemasukan Operasional)
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], 
            'nama_pembayaran' => ['type' => 'VARCHAR', 'constraint' => '255'],
            'tipe'            => ['type' => 'ENUM', 'constraint' => ['sekali_bayar', 'bulanan', 'tahunan', 'opsional'], 'default' => 'sekali_bayar'],
            'nominal'         => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'keterangan'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->createTable('jenis_pembayaran');

        // 2. Tabel: tagihan (Piutang Siswa)
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'id_siswa'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_jenis_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'deskripsi'           => ['type' => 'VARCHAR', 'constraint' => '255'],
            'jumlah'              => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'tanggal_tagihan'     => ['type' => 'DATE'],
            'tanggal_jatuh_tempo' => ['type' => 'DATE', 'null' => true],
            'status'              => ['type' => 'ENUM', 'constraint' => ['belum_lunas', 'lunas', 'mencicil', 'sebagian'], 'default' => 'belum_lunas'],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_jenis_pembayaran', 'jenis_pembayaran', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tagihan');

        // 3. Tabel: pembayaran (Realisasi Kas Masuk)
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'id_tagihan'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_user_admin'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jumlah_bayar'      => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'tanggal_bayar'     => ['type' => 'DATE'],
            'metode_pembayaran' => ['type' => 'VARCHAR', 'constraint' => '50'],
            'bukti_bayar'       => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
            'keterangan'        => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tagihan', 'tagihan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_user_admin', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pembayaran');

        // 4. Tabel: kategori_anggaran (Chart of Accounts / COA merujuk ISAK 35)
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_kategori'  => ['type' => 'VARCHAR', 'constraint' => 20], // Contoh: 4-1100 (Penghasilan SPP), 5-1100 (Beban Gaji)
            'nama_kategori'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'kelompok'       => ['type' => 'ENUM', 'constraint' => ['penghasilan', 'beban', 'aset', 'liabilitas'], 'default' => 'beban'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode_kategori');
        $this->forge->createTable('kategori_anggaran');

        // 5. Tabel: pengeluaran (Kas Keluar)
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'      => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'id_kategori'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'tanggal'           => ['type' => 'DATE'],
            'kategori_manual'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true], 
            'keterangan'        => ['type' => 'TEXT', 'null' => true],
            'jumlah'            => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'metode_pembayaran' => ['type' => 'VARCHAR', 'constraint' => 50], 
            'bukti_transaksi'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'id_user_input'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kategori', 'kategori_anggaran', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('id_user_input', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pengeluaran');

        // 6. Tabel: anggaran_unit (Target Anggaran Tahunan Berbasis Kategori COA)
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'id_kategori'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tahun'        => ['type' => 'VARCHAR', 'constraint' => 9], // Format: 2025/2026
            'nominal'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'keterangan'   => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('kode_jenjang', 'jenjang_sekolah', 'kode_jenjang', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kategori', 'kategori_anggaran', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('anggaran_unit');
    }

    public function down()
    {
        // Drop dengan urutan terbalik untuk menjaga integritas foreign key
        $this->forge->dropTable('anggaran_unit', true);
        $this->forge->dropTable('pengeluaran', true);
        $this->forge->dropTable('kategori_anggaran', true);
        $this->forge->dropTable('pembayaran', true);
        $this->forge->dropTable('tagihan', true);
        $this->forge->dropTable('jenis_pembayaran', true);
    }
}