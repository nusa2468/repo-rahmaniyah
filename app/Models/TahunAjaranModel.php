<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

/**
 * Model TahunAjaran (Scoped per Unit & Terpusat)
 * Menyimpan referensi periode akademik.
 * Fitur: Scope Unit dengan Fallback ke Yayasan (GLOBAL/PUSAT).
 */
class TahunAjaranModel extends Model
{
    protected $table            = 'tahun_ajaran';
    protected $primaryKey       = 'id';
    
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $useSoftDeletes   = false;
    
    protected $allowedFields = [
        'kode_jenjang', 
        'tahun_ajaran', 
        'semester', 
        'status', 
        'tanggal_mulai', 
        'tanggal_selesai', 
        'keterangan'
    ];

    protected $useTimestamps     = true;
    protected $createdField      = 'created_at';
    protected $updatedField      = 'updated_at';

    // Callbacks
    protected $beforeInsert      = ['checkUniqueComposite', 'setExclusiveStatus'];
    protected $beforeUpdate      = ['checkUniqueComposite', 'setExclusiveStatus'];

    /**
     * Aturan Validasi
     */
    protected $validationRules = [
        'id'              => 'permit_empty|integer', 
        'kode_jenjang'    => 'required', // Wajib untuk scoping
        'tahun_ajaran'    => 'required|max_length[50]',
        'semester'        => 'required|in_list[Ganjil,Genap]',
        'status'          => 'required|in_list[aktif,tidak aktif]',
        'tanggal_mulai'   => 'permit_empty|valid_date',
        'tanggal_selesai' => 'permit_empty|valid_date',
        'keterangan'      => 'permit_empty|max_length[255]',
    ];
    
    protected $validationMessages = [
        'tahun_ajaran' => [
            'required' => 'Tahun Ajaran wajib diisi (contoh: 2024/2025).'
        ],
        'semester' => [
            'required' => 'Semester wajib ditentukan.'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Mengambil data Tahun Ajaran dengan opsi filter.
     * FIX LOGIKA: Tampilkan TA spesifik unit DITAMBAH TA terpusat milik Yayasan
     */
    public function getTahunAjaranWithJenjang($id = null, ?string $kode_jenjang = null, ?string $search = null)
    {
        // FIX UI: Tampilkan nama jenjang agar Admin tahu mana TA Yayasan, mana TA spesifik Unit
        $builder = $this->select('tahun_ajaran.*, COALESCE(js.nama_jenjang, "Yayasan / Terpusat") as unit_sekolah')
                        ->join('jenjang_sekolah js', 'js.kode_jenjang = tahun_ajaran.kode_jenjang', 'left');
        
        if ($id) {
            return $builder->where('tahun_ajaran.id', $id)->first();
        }

        // Terapkan Isolasi Data (Termasuk hak untuk melihat data Yayasan)
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->groupStart()
                    ->where('tahun_ajaran.kode_jenjang', strtoupper($kode_jenjang))
                    ->orWhereIn('tahun_ajaran.kode_jenjang', ['GLOBAL', 'YAYASAN', 'PUSAT'])
                    ->groupEnd();
        }

        if ($search) {
            $builder->groupStart()
                    ->like('tahun_ajaran.tahun_ajaran', $search)
                    ->orLike('tahun_ajaran.keterangan', $search)
                    ->groupEnd();
        }

        return $builder->orderBy('tahun_ajaran.status', 'ASC')
                       ->orderBy('tahun_ajaran.tahun_ajaran', 'DESC')
                       ->findAll();
    }

    /**
     * Mendapatkan Tahun Ajaran yang sedang aktif berdasarkan Unit.
     * FIX LOGIKA: Fallback ke Tahun Ajaran GLOBAL jika Unit tidak punya jadwal spesifik.
     */
    public function getAktifByUnit(string $kode_jenjang)
    {
        // 1. Cari TA Aktif khusus untuk Unit tersebut (Misal: SMA sedang ekstensi semester)
        $ta = $this->where('status', 'aktif')
                   ->where('kode_jenjang', strtoupper($kode_jenjang))
                   ->first();
                   
        // 2. Jika tidak ada, ikuti kalender TA Aktif milik Pusat/Yayasan
        if (!$ta) {
            $ta = $this->where('status', 'aktif')
                       ->whereIn('kode_jenjang', ['GLOBAL', 'YAYASAN', 'PUSAT'])
                       ->first();
        }
        
        return $ta;
    }
    
    // Fallback global jika diperlukan oleh Superadmin
    public function getAktif()
    {
        return $this->where('status', 'aktif')->first();
    }

    /**
     * [FIXED] Memastikan kombinasi tahun dan semester tidak ganda PER UNIT.
     */
    protected function checkUniqueComposite(array $data)
    {
        if (!isset($data['data']['tahun_ajaran'], $data['data']['semester'])) {
            return $data;
        }

        $tahunAjaran  = $data['data']['tahun_ajaran'];
        $semester     = $data['data']['semester'];
        
        $id = $data['id'][0] ?? $data['data'][$this->primaryKey] ?? null;

        $check = $this->where('tahun_ajaran', $tahunAjaran)
                      ->where('semester', $semester);

        if (isset($data['data']['kode_jenjang'])) {
            $check->where('kode_jenjang', $data['data']['kode_jenjang']);
        } elseif ($id) {
            $existing = $this->find($id);
            if ($existing) {
                $check->where('kode_jenjang', $existing['kode_jenjang']);
            }
        }

        if ($id) {
            $check->where('id !=', $id);
        }

        if ($check->countAllResults() > 0) {
            $unitMsg = isset($data['data']['kode_jenjang']) ? " di unit " . $data['data']['kode_jenjang'] : "";
            throw new RuntimeException("Data Tahun Ajaran {$tahunAjaran} Semester {$semester} sudah ada{$unitMsg}.");
        }

        return $data;
    }

    /**
     * [FIXED] Memastikan hanya ada SATU status 'aktif' PER UNIT.
     */
    protected function setExclusiveStatus(array $data): array
    {
        if (isset($data['data']['status']) && strtolower($data['data']['status']) === 'aktif') {
            
            $currentId = $data['id'][0] ?? $data['data'][$this->primaryKey] ?? 0;
            $jenjang   = null;

            if (isset($data['data']['kode_jenjang'])) {
                $jenjang = $data['data']['kode_jenjang'];
            } elseif ($currentId) {
                $existing = $this->find($currentId);
                $jenjang  = $existing['kode_jenjang'] ?? null;
            }

            if ($jenjang) {
                // Nonaktifkan tahun ajaran lain HANYA di unit yang sama (termasuk unit GLOBAL)
                $this->builder()
                     ->where('kode_jenjang', $jenjang) 
                     ->where('id !=', $currentId)
                     ->update(['status' => 'tidak aktif', 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
        
        return $data;
    }
}