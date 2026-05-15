<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrasi untuk tabel detail akademik.
 * * UPDATE: Logika pembuatan tabel di file ini telah DINONAKTIFKAN / DIKOSONGKAN.
 * Alasannya: Tabel 'nilai_siswa', 'absensi_siswa', 'raport', dan 'kenaikan_kelas' 
 * sudah diintegrasikan dan dibuat di dalam migrasi 'CreatePrimaryAcademicData.php' 
 * untuk menangani urutan relasi (Foreign Key) yang lebih baik.
 * * File ini dipertahankan kosong agar tidak merusak urutan versioning migrasi yang sudah berjalan.
 */
class CreateAcademicDetail extends Migration
{
    public function up()
    {
        // KOSONG - Tabel sudah dibuat di CreatePrimaryAcademicData.php
        // Logika di bawah ini dikomentari untuk mencegah error "Table already exists"
        
        /* // --- 1. TABEL NILAI SISWA ---
        if (! $this->db->tableExists('nilai_siswa')) {
             // ... kode lama ...
        }
        */
    }

    public function down()
    {
        // KOSONG - Penghapusan tabel ditangani oleh CreatePrimaryAcademicData.php
        // untuk mencegah penghapusan ganda atau error constraint.
    }
}