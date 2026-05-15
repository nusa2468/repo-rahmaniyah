<?php 

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola penugasan mengajar dan tugas tambahan Guru (tabel: penugasan_mengajar).
 * Data ini digunakan untuk menentukan beban kerja guru di tahun ajaran tertentu.
 */
class PenugasanMengajarModel extends Model
{
    protected $table             = 'penugasan_mengajar';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'array'; 
    protected $useSoftDeletes    = false; 
    protected $protectFields     = true;

    // Berdasarkan struktur tabel 'penugasan_mengajar'
    protected $allowedFields = [
        'guru_id',                   // Kunci asing ke tabel guru
        'tahun_ajaran',              // Contoh: 2025/2026 (string)
        'mapel_diampu',              // Mapel utama yang diampu (bisa berupa daftar string)
        'ijm_total',                 // FIX: Mengganti jam_total menjadi ijm_total (Jumlah Jam Mengajar Total per minggu)
        'tugas_tambahan',            // Contoh: Wakil Kurikulum, Kepala Lab
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation (Dasar)
    protected $validationRules = [
        'guru_id'           => 'required|is_natural_no_zero',
        'tahun_ajaran'      => 'required|max_length[10]',
        'ijm_total'         => 'required|is_natural', // FIX: Menggunakan ijm_total
        'mapel_diampu'      => 'max_length[255]|permit_empty',
        'tugas_tambahan'    => 'max_length[255]|permit_empty',
    ];

    protected $validationMessages = [
        'ijm_total' => [
            'required' => 'Jumlah jam mengajar (IJM Total) wajib diisi.',
            'is_natural' => 'IJM Total harus berupa angka non-negatif.',
        ],
        'tahun_ajaran' => [
             'max_length' => 'Format Tahun Ajaran tidak boleh lebih dari 10 karakter (cth: 2024/2025).',
        ],
    ];
    
    protected $skipValidation      = false;

    // --- CUSTOM METHODS ---

    /**
     * Mendapatkan penugasan mengajar terbaru (terkini) guru berdasarkan Tahun Ajaran.
     * Metode ini juga berfungsi sebagai pengecek duplikasi unik komposit.
     * * @param int $guruId ID Guru.
     * @param string $tahunAjaranString Tahun Ajaran (e.g., '2025/2026').
     * @return array|null Data penugasan mengajar.
     */
    public function getLatestPenugasan(int $guruId, string $tahunAjaranString): ?array
    {
        return $this->where('guru_id', $guruId)
                    // Mencari berdasarkan kolom tahun_ajaran (string)
                    ->where('tahun_ajaran', $tahunAjaranString) 
                    ->first();
    }
    
    /**
     * Pengecekan duplikasi manual untuk mencegah dua penugasan di TA yang sama
     * untuk satu guru.
     * Dipanggil sebelum insert/update oleh Controller (jika diperlukan validasi lebih ketat).
     * * @param int $guruId
     * @param string $tahunAjaranString
     * @param int|null $exceptId ID Penugasan yang dikecualikan (untuk operasi update).
     * @return bool True jika TIDAK ada duplikasi, False jika ada duplikasi.
     */
    public function isUnique(int $guruId, string $tahunAjaranString, ?int $exceptId = null): bool
    {
        $builder = $this->builder();
        $builder->where('guru_id', $guruId)
                ->where('tahun_ajaran', $tahunAjaranString);

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() === 0;
    }
}