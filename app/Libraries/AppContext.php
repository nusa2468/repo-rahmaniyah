<?php namespace App\Libraries;

use CodeIgniter\Model;

/**
 * AppContext Library
 * Bertanggung jawab untuk menyediakan konteks aplikasi global,
 * seperti Tahun Ajaran (TA) aktif, yang diperlukan di berbagai Controller
 * dan Model untuk memfilter data.
 */
class AppContext
{
    /**
     * Mengambil ID dan nama Tahun Ajaran Aktif dari database.
     * * @return array Mengembalikan array ['id_tahun_ajaran', 'tahun_ajaran_nama'].
     */
    public function get(): array
    {
        // 1. Dapatkan instance dari TahunAjaranModel
        try {
            /** @var \App\Models\TahunAjaranModel $tahunAjaranModel */
            $tahunAjaranModel = model('TahunAjaranModel');
        } catch (\CodeIgniter\Exceptions\ModelException $e) {
            log_message('error', 'TahunAjaranModel not found: ' . $e->getMessage());
            return [
                'id_tahun_ajaran'   => null,
                'tahun_ajaran_nama' => 'MODEL TA TIDAK DITEMUKAN',
            ];
        }

        // 2. Query untuk mengambil Tahun Ajaran Aktif
        // PERBAIKAN: Disesuaikan dengan skema tabel 'tahun_ajaran' Anda:
        // - Kolom status adalah 'status' dengan nilai 'aktif'.
        // - Kolom nama tahun ajaran adalah 'tahun_ajaran'.
        $activeTa = $tahunAjaranModel
                    ->where('status', 'aktif') // Kriteria: Menggunakan kolom 'status' dan nilai string 'aktif'
                    ->select('id, tahun_ajaran') // Kolom yang diambil: 'id' (PK) dan 'tahun_ajaran' (Nama TA)
                    ->first();

        if ($activeTa) {
            return [
                // Menggunakan kolom 'id' dan tetap mengembalikannya sebagai key 'id_tahun_ajaran'
                'id_tahun_ajaran'   => (int) ($activeTa['id'] ?? null),
                // Menggunakan kolom 'tahun_ajaran' dan mengembalikannya sebagai key 'tahun_ajaran_nama'
                'tahun_ajaran_nama' => $activeTa['tahun_ajaran'] ?? 'N/A',
            ];
        }

        // 3. Fallback jika tidak ada Tahun Ajaran aktif yang ditemukan di DB
        return [
            'id_tahun_ajaran'   => null,
            'tahun_ajaran_nama' => 'Tidak Ada Tahun Ajaran Aktif',
        ];
    }
}