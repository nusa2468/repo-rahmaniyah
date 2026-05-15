<?php

namespace App\Models;

use CodeIgniter\Model;

class JurusanModel extends Model
{
    protected $table            = 'jurusan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    
    // PERBAIKAN UTAMA: Ubah ke 'object' agar bisa dipanggil $item->id
    protected $returnType       = 'object'; 
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        // 'id', // Biasanya ID tidak perlu dimasukkan ke allowedFields jika Auto Increment, tapi tidak apa-apa jika ingin eksplisit
        'kode_jenjang',
        'kode_jurusan',
        'nama_jurusan',
        'status',
        'keterangan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Rules Dasar (Validasi dinamis ditangani di method save)
    protected $validationRules = [
        'id'           => 'permit_empty|is_natural_no_zero',
        'kode_jenjang' => 'required',
    ];

    protected $validationMessages = [
        'kode_jurusan' => [
            'is_unique' => 'Kode jurusan ini sudah digunakan di unit lain.',
        ],
        'nama_jurusan' => [
            'is_unique' => 'Nama jurusan ini sudah terdaftar.',
        ]
    ];

    /**
     * Override save() dengan Smart Validation (Fix Duplicate Issue)
     */
    public function save($data = null): bool
    {
        // 1. Ambil ID (Handle baik input Array maupun Object)
        $id = is_array($data) ? ($data['id'] ?? null) : ($data->id ?? null);

        // 2. Normalisasi Status (1/0 -> Aktif/Non-Aktif)
        // Kita perlu handle akses array vs object di sini juga
        if (is_array($data)) {
            if (isset($data['status'])) {
                if ($data['status'] == '1') $data['status'] = 'Aktif';
                if ($data['status'] == '0') $data['status'] = 'Non-Aktif';
            }
        } else if (is_object($data)) {
            if (isset($data->status)) {
                if ($data->status == '1') $data->status = 'Aktif';
                if ($data->status == '0') $data->status = 'Non-Aktif';
            }
        }

        // 3. Smart Validation Logic
        if (!empty($id)) {
            // Karena returnType sekarang object, find() akan mengembalikan object
            $original = $this->find($id);
            
            // Konversi ke array sebentar untuk pengecekan atau akses sebagai object
            // Cara aman akses property original (karena returnType='object')
            $origKode = $original->kode_jurusan ?? '';
            $origNama = $original->nama_jurusan ?? '';

            // Ambil Input
            $inputKode = is_array($data) ? ($data['kode_jurusan'] ?? '') : ($data->kode_jurusan ?? '');
            $inputNama = is_array($data) ? ($data['nama_jurusan'] ?? '') : ($data->nama_jurusan ?? '');

            // Cek Kode
            if ($original && $inputKode === $origKode) {
                $this->validationRules['kode_jurusan'] = 'required';
            } else {
                $this->validationRules['kode_jurusan'] = "required|is_unique[jurusan.kode_jurusan,id,{$id}]";
            }

            // Cek Nama
            if ($original && $inputNama === $origNama) {
                $this->validationRules['nama_jurusan'] = 'required';
            } else {
                $this->validationRules['nama_jurusan'] = "required|is_unique[jurusan.nama_jurusan,id,{$id}]";
            }
        } else {
            // Mode Tambah
            $this->validationRules['kode_jurusan'] = 'required|is_unique[jurusan.kode_jurusan]';
            $this->validationRules['nama_jurusan'] = 'required|is_unique[jurusan.nama_jurusan]';
        }

        $this->validationRules['status'] = 'required|in_list[Aktif,Non-Aktif]';

        return parent::save($data);
    }

    /**
     * Scope Data: Menjamin Superadmin melihat SEMUA data
     */
    public function getScopedData($role, $userJenjang)
    {
        // Pastikan role lowercase untuk perbandingan
        $r = strtolower($role ?? '');

        if ($r === 'superadmin' || $r === 'yayasan') {
            // Superadmin & Yayasan melihat SEMUA data
            return $this->orderBy('kode_jenjang', 'ASC')
                        ->orderBy('nama_jurusan', 'ASC')
                        ->findAll();
        }

        // Admin Unit hanya melihat unitnya sendiri
        if (empty($userJenjang)) return [];

        return $this->where('kode_jenjang', $userJenjang)
                    ->orderBy('nama_jurusan', 'ASC')
                    ->findAll();
    }

    /**
     * Security Check
     */
    public function checkAccess($id, $role, $userJenjang)
    {
        $r = strtolower($role ?? '');
        if ($r === 'superadmin' || $r === 'yayasan') return true;
        
        $data = $this->find($id);
        
        // Karena returnType='object', akses dengan panah (->)
        if (!$data) return false;
        
        // Handle jika $data ternyata array (jaga-jaga) atau object
        $dataJenjang = is_array($data) ? $data['kode_jenjang'] : $data->kode_jenjang;
        
        return ($dataJenjang === $userJenjang);
    }
}