<?php
// app/Helpers/utility_helper.php

/**
 * Mengubah angka bulan (1-12) menjadi nama bulan dalam Bahasa Indonesia.
 *
 * @param int $angka_bulan Angka bulan (1 hingga 12).
 * @return string Nama bulan dalam Bahasa Indonesia.
 */
if (! function_exists('bulan_indo')) {
    function bulan_indo(int $angka_bulan): string
    {
        $bulan_map = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $bulan_map[$angka_bulan] ?? 'Bulan Tidak Valid';
    }
}

/**
 * Memformat angka menjadi format mata uang Rupiah (Rp).
 *
 * @param float|int $angka Nilai uang yang akan diformat.
 * @return string String Rupiah (misal: Rp 1.500.000).
 */
if (! function_exists('format_rupiah')) {
    function format_rupiah($angka): string
    {
        // Pastikan input adalah numerik
        if (!is_numeric($angka)) {
            return 'Rp 0';
        }

        // Format angka dengan pemisah ribuan titik dan tanpa desimal
        // number_format(angka, jumlah desimal, pemisah desimal, pemisah ribuan)
        $format = number_format((float)$angka, 0, ',', '.');
        
        return 'Rp ' . $format;
    }
}