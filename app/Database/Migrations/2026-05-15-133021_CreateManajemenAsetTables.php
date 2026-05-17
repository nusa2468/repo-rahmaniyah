<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManajemenAsetTables extends Migration
{
    public function up()
    {
        // -------------------------------------------------------------------
        // CATATAN: Jika sebelumnya sudah ada tabel sapras lama, kita hapus 
        // saat migrasi dijalankan ulang agar tergantikan dengan skema baru.
        // -------------------------------------------------------------------
        $this->forge->dropTable('sapras_inventaris', true);
        $this->forge->dropTable('sapras_peralatan', true);
        $this->forge->dropTable('sapras_ruangan', true);
        $this->forge->dropTable('sapras_gedung', true);
        $this->forge->dropTable('sapras_tanah', true);

        // ===================================================================
        // 1. TABEL: ASET KATEGORI (Master Klasifikasi Barang)
        // ===================================================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], // <--- UPDATE: Pemisah antar unit
            'kode_kategori' => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true], // Contoh: ELK, MBL, TNH
            'nama_kategori' => ['type' => 'VARCHAR', 'constraint' => 255],
            'tipe_aset'     => ['type' => 'ENUM', 'constraint' => ['Bangunan/Tanah', 'Elektronik', 'Furniture', 'Kendaraan', 'Lainnya'], 'default' => 'Lainnya'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('aset_kategori', true);

        // ===================================================================
        // 2. TABEL: ASET LOKASI (Master Gedung / Ruangan / Lab)
        // ===================================================================
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true], 
            'jenis_lokasi'  => ['type' => 'ENUM', 'constraint' => ['Gedung', 'Ruang Kelas', 'Laboratorium', 'Gudang', 'Lainnya'], 'default' => 'Ruang Kelas'],
            'nama_lokasi'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'kapasitas'     => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'keterangan'    => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang'); 
        $this->forge->createTable('aset_lokasi', true);

        // ===================================================================
        // 3. TABEL: ASET BARANG (Katalog Utama Aset Digital) -> Single Source
        // ===================================================================
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'        => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'id_kategori'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_lokasi'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'id_penanggung_jawab' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true], // Relasi ke tabel Pegawai
            
            // Integrasi QR/Barcode Labeling
            'kode_aset'           => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true], 
            'nama_aset'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'merk_spesifikasi'    => ['type' => 'TEXT', 'null' => true],
            
            // Financial & Procurement Tracking
            'sumber_dana'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true], // BOS, Yayasan, Hibah
            'status_kepemilikan'  => ['type' => 'ENUM', 'constraint' => ['Milik Sendiri', 'Sewa', 'Hibah/Wakaf', 'Pinjam Pakai'], 'default' => 'Milik Sendiri'], // <--- UPDATE: Status Kepemilikan (Sewa/Milik)
            'tanggal_perolehan'   => ['type' => 'DATE', 'null' => true],
            'harga_perolehan'     => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            
            // Status & Lifecycle Tracking
            'kondisi'             => ['type' => 'ENUM', 'constraint' => ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Afkir/Dihapus'], 'default' => 'Baik'],
            'status_ketersediaan' => ['type' => 'ENUM', 'constraint' => ['Tersedia', 'Dipinjam', 'Diperbaiki', 'Hilang'], 'default' => 'Tersedia'],
            
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->addForeignKey('id_kategori', 'aset_kategori', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('id_lokasi', 'aset_lokasi', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('aset_barang', true);

        // ===================================================================
        // 4. TABEL: PENGADAAN (Requisition & Approval Workflow)
        // ===================================================================
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_jenjang'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'no_pengajuan'    => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'judul_pengajuan' => ['type' => 'VARCHAR', 'constraint' => 255],
            'id_kategori'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jumlah_diminta'  => ['type' => 'INT', 'constraint' => 5],
            'estimasi_biaya'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'alasan_kebutuhan'=> ['type' => 'TEXT'],
            'id_pemohon'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true], // Relasi Pegawai
            'status'          => ['type' => 'ENUM', 'constraint' => ['Draft', 'Menunggu Approval', 'Disetujui', 'Ditolak', 'Selesai/Dibeli'], 'default' => 'Draft'],
            'catatan_reviewer'=> ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kode_jenjang');
        $this->forge->createTable('aset_pengadaan', true);

        // ===================================================================
        // 5. TABEL: PEMINJAMAN (Logistik & Booking Internal)
        // ===================================================================
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_aset'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tipe_peminjam'    => ['type' => 'ENUM', 'constraint' => ['Pegawai', 'Siswa'], 'default' => 'Pegawai'],
            'id_peminjam'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal_pinjam'   => ['type' => 'DATETIME'],
            'estimasi_kembali' => ['type' => 'DATETIME'],
            'tanggal_kembali'  => ['type' => 'DATETIME', 'null' => true],
            'keperluan'        => ['type' => 'TEXT', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['Menunggu', 'Dipinjam', 'Dikembalikan', 'Terlambat'], 'default' => 'Menunggu'],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_aset', 'aset_barang', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('aset_peminjaman', true);

        // ===================================================================
        // 6. TABEL: PEMELIHARAAN (Preventive & Corrective Maintenance)
        // ===================================================================
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_aset'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jenis_pemeliharaan' => ['type' => 'ENUM', 'constraint' => ['Rutin/Preventif', 'Perbaikan/Kerusakan'], 'default' => 'Rutin/Preventif'],
            'tanggal_mulai'      => ['type' => 'DATE'],
            'tanggal_selesai'    => ['type' => 'DATE', 'null' => true],
            'pelaksana'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], // Internal Teknisi atau Vendor Luar
            'biaya'              => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'keterangan'         => ['type' => 'TEXT'],
            'status'             => ['type' => 'ENUM', 'constraint' => ['Direncanakan', 'Sedang Proses', 'Selesai', 'Batal'], 'default' => 'Sedang Proses'],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('id_aset', 'aset_barang', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('aset_pemeliharaan', true);
    }

    public function down()
    {
        // Rollback dengan urutan terbalik agar tidak bentrok Foreign Key
        $this->forge->dropTable('aset_pemeliharaan', true);
        $this->forge->dropTable('aset_peminjaman', true);
        $this->forge->dropTable('aset_pengadaan', true);
        $this->forge->dropTable('aset_barang', true);
        $this->forge->dropTable('aset_lokasi', true);
        $this->forge->dropTable('aset_kategori', true);
    }
}