<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola data Kalender Pendidikan.
 * Dioptimalkan untuk CodeIgniter 4.6.3.
 * Menggunakan penamaan kolom database asli (title, start, end) untuk mencegah error validasi.
 */
class KalenderPendidikanModel extends Model
{
    protected $table            = 'kalender_pendidikan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    /**
     * FIELD YANG DIIZINKAN
     * Hanya menyertakan kolom yang benar-benar ada di tabel database.
     */
    protected $allowedFields = [
        'kode_jenjang',     // Scope Unit/Jenjang
        'title',            // Nama Acara (Standard FullCalendar)
        'start',            // Tanggal Mulai (Standard FullCalendar)
        'end',              // Tanggal Selesai (Standard FullCalendar)
        'color',            // Warna Label
        'tahun_ajaran_id',  // Relasi ke Tahun Ajaran
        'keterangan'        // Detail Tambahan
    ];

    // Sinkronisasi Waktu Otomatis
    protected $useTimestamps   = true;
    protected $dateFormat      = 'datetime';
    protected $createdField    = 'created_at';
    protected $updatedField    = 'updated_at';

    /**
     * Aturan Validasi
     * Validasi langsung merujuk ke field 'title' dan 'start' sesuai input dari Form.
     */
    protected $validationRules = [
        'kode_jenjang'    => 'required|max_length[10]',
        'tahun_ajaran_id' => 'required|integer',
        'title'           => 'required|min_length[3]|max_length[255]',
        'start'           => 'required|valid_date',
        // 'end' divalidasi menggunakan rule kustom is_after_or_equal terhadap 'start'
        'end'             => 'permit_empty|valid_date|is_after_or_equal[start]', 
        'keterangan'      => 'permit_empty|max_length[500]', 
        'color'           => 'permit_empty|regex_match[/^#([a-fA-F0-9]{6})$/]', 
    ];

    protected $validationMessages = [
        'kode_jenjang' => [
            'required'   => 'Unit kerja wajib ditentukan.',
            'max_length' => 'Kode unit terlalu panjang.'
        ],
        'tahun_ajaran_id' => [
            'required' => 'Tahun ajaran harus terikat.'
        ],
        'title' => [
            'required'   => 'Nama Acara wajib diisi.',
            'min_length' => 'Nama Acara minimal 3 karakter.',
            'max_length' => 'Nama Acara maksimal 255 karakter.'
        ],
        'start' => [
            'required'   => 'Tanggal Mulai wajib diisi.',
            'valid_date' => 'Format tanggal mulai tidak valid.'
        ],
        'end' => [
            'valid_date'        => 'Format tanggal selesai tidak valid.',
            'is_after_or_equal' => 'Tanggal Selesai tidak boleh sebelum Tanggal Mulai.'
        ],
        'color' => [
            'regex_match' => 'Format warna harus kode HEX (misal: #FF0000).'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Rule Validasi Kustom: Memastikan Tanggal Selesai >= Tanggal Mulai.
     * * @param string|null $str Tanggal Selesai
     * @param string $field Nama field pembanding (start)
     * @param array $data Seluruh data post
     * @return bool
     */
    public function is_after_or_equal(?string $str, string $field, array $data): bool
    {
        if (empty($str)) {
            return true;
        }

        $startDate = $data[$field] ?? null;

        if (empty($startDate)) {
            return true; 
        }

        return strtotime($str) >= strtotime($startDate);
    }

    // -------------------------------------------------------------------------
    // CUSTOM SCOPES
    // -------------------------------------------------------------------------

    /**
     * Scope untuk memfilter data berdasarkan Unit (SD/SMP/SMA).
     */
    public function scopeJenjang(string $kodeJenjang)
    {
        return $this->where($this->table . '.kode_jenjang', $kodeJenjang);
    }

    /**
     * Scope untuk memfilter data berdasarkan Tahun Ajaran aktif.
     */
    public function scopeTahun(int $tahunAjaranId)
    {
        return $this->where($this->table . '.tahun_ajaran_id', $tahunAjaranId);
    }
}