<?php

namespace App\Controllers;

// Buat controller serupa untuk setiap modul lainnya
// Contoh: Kelembagaan.php, Psb.php, Keuangan.php, dst.

class MasterData extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Master Data',
        ];
        // Mengarahkan ke view di dalam folder master_data
        return view('master_data/index', $data);
    }
}

// Anda perlu membuat file controller lain untuk setiap modul:
/*
 * Kelembagaan.php
 * Psb.php
 * Keuangan.php
 * Kurikulum.php
 * Elearning.php
 * Kesiswaan.php
 * Guru.php
 * Karyawan.php
 * Kehumasan.php
 * Sapras.php
 * Pelaporan.php
 * Pengaturan.php
*/
