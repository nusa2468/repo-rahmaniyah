<?php

namespace App\Models\Pembelajaran;

use CodeIgniter\Model;

/**
 * Model Silabus (Hybrid Edition)
 * Mendukung Kurikulum 2013 dan Kurikulum Merdeka dalam satu tabel.
 * Terintegrasi dengan fitur Unit Scoping untuk isolasi data antar jenjang.
 */
class SilabusModel extends Model
{
    protected $table            = 'pembelajaran_silabus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Aktifkan Soft Deletes untuk keamanan data
    protected $useSoftDeletes   = true; 
    protected $protectFields    = true;

    /**
     * Field yang diizinkan untuk manipulasi data (Strict Mapping)
     * Disinkronkan dengan Database Migration
     */
    protected $allowedFields    = [
        'kode_jenjang', 
        'kurikulum_id', 
        'mata_pelajaran_id', 
        'tingkat_kelas', 
        'semester', 
        'tahun_ajaran',
        'jenis_kurikulum',          // Enum: 'K13', 'Merdeka'
        
        // --- Kolom Spesifik Kurikulum Merdeka ---
        'fase',                     // Enum: A, B, C, D, E, F
        'capaian_pembelajaran',     // CP
        'alur_tujuan_pembelajaran', // ATP
        'profil_pelajar_pancasila', // P5

        // --- Kolom Spesifik Kurikulum 2013 ---
        'tema',
        'subtema',
        'kompetensi_inti',          // KI
        'kompetensi_dasar',         // KD
        'indikator',                // IPK

        // --- Kolom Umum (UPDATED) ---
        'materi_pokok', 
        'kegiatan_pembelajaran',    // [BARU] Kolom tambahan
        'penilaian',                // [BARU] Kolom tambahan
        'alokasi_waktu', 
        'sumber_belajar', 
        'created_by',
        'status'                    // Enum: 'Draft', 'Final'
    ];

    // Otomatisasi Waktu
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Scope untuk memfilter data berdasarkan pencarian, jenjang, dan kurikulum.
     * Digunakan untuk mengunci data agar tidak bocor antar unit sekolah.
     */
    public function getFilteredData($keyword = null, $kodeJenjang = null, $jenisKurikulum = null)
    {
        // Join dengan tabel mata pelajaran untuk mendapatkan detail tambahan
        // SELECT spesifik untuk menghindari bentrok nama kolom (misal: id, created_at)
        $this->select('
            pembelajaran_silabus.*, 
            mata_pelajaran.kode_mapel, 
            mata_pelajaran.nama_mapel, 
            mata_pelajaran.tingkat
        ');
        
        $this->join('mata_pelajaran', 'mata_pelajaran.id = pembelajaran_silabus.mata_pelajaran_id', 'left');

        // Filter berdasarkan Jenjang (SD, SMP, SMA) - Kunci Utama Isolasi
        if ($kodeJenjang && $kodeJenjang !== 'ALL') {
            $this->where('pembelajaran_silabus.kode_jenjang', $kodeJenjang);
        }

        // Filter berdasarkan Jenis Kurikulum (K13, Merdeka)
        if ($jenisKurikulum) {
            $this->where('pembelajaran_silabus.jenis_kurikulum', $jenisKurikulum);
        }

        // Filter Pencarian (Mencakup kolom lintas kurikulum dan atribut mapel)
        if ($keyword) {
            $this->groupStart()
                ->like('pembelajaran_silabus.materi_pokok', $keyword)
                ->orLike('pembelajaran_silabus.kompetensi_dasar', $keyword)
                ->orLike('pembelajaran_silabus.capaian_pembelajaran', $keyword)
                ->orLike('pembelajaran_silabus.alur_tujuan_pembelajaran', $keyword)
                ->orLike('pembelajaran_silabus.tema', $keyword)
                ->orLike('mata_pelajaran.nama_mapel', $keyword)
                ->orLike('mata_pelajaran.kode_mapel', $keyword)
            ->groupEnd();
        }

        return $this;
    }

    /**
     * Mengambil statistik KPI yang sudah difilter berdasarkan unit kerja.
     */
    public function getStatistics($kodeJenjang = null)
    {
        // Inisialisasi builder agar filter permanen (deleted_at IS NULL)
        $builder = $this->db->table($this->table)->where('deleted_at', null);

        // Jika user dibatasi unitnya, maka statistik HANYA menghitung unit tersebut
        if ($kodeJenjang) {
            $builder->where('kode_jenjang', $kodeJenjang);
        }

        // Gunakan clone untuk mendapatkan hitungan berbeda dari satu builder dasar
        return [
            'total'   => (clone $builder)->countAllResults(),
            'merdeka' => (clone $builder)->where('jenis_kurikulum', 'Merdeka')->countAllResults(),
            'k13'     => (clone $builder)->where('jenis_kurikulum', 'K13')->countAllResults(),
            'final'   => (clone $builder)->where('status', 'Final')->countAllResults(),
            'draft'   => (clone $builder)->where('status', 'Draft')->countAllResults(),
            
            // Detail per jenjang (Opsional, berguna jika $kodeJenjang = null / Superadmin)
            'sd'      => (clone $builder)->where('kode_jenjang', 'SD')->countAllResults(),
            'smp'     => (clone $builder)->where('kode_jenjang', 'SMP')->countAllResults(),
            'sma'     => (clone $builder)->where('kode_jenjang', 'SMA')->countAllResults(),
        ];
    }
}