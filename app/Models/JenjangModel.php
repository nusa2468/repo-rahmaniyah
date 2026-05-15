<?php

namespace App\Models;

use CodeIgniter\Model;

class JenjangModel extends Model
{
    protected $table            = 'jenjang_sekolah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; 
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['nama_jenjang', 'kode_jenjang', 'keterangan', 'urutan', 'status'];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // PERBAIKAN VALIDASI
    protected $validationRules = [
        'id'           => 'permit_empty|integer', 
        'nama_jenjang' => 'required|min_length[2]|max_length[100]',
        'kode_jenjang' => 'required|alpha_numeric|min_length[2]|max_length[10]|is_unique[jenjang_sekolah.kode_jenjang,id,{id}]',
        
        // FIX: Menambahkan 'tidak aktif' ke dalam list untuk menyesuaikan standar ENUM database ERP
        'status'       => 'required|in_list[aktif,tidak aktif,nonaktif]',
        'urutan'       => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'kode_jenjang' => [
            'is_unique' => 'Kode jenjang ini sudah terdaftar. Gunakan kode lain.',
            'alpha_numeric' => 'Kode jenjang hanya boleh berisi huruf dan angka (tanpa spasi).'
        ]
    ];

    /**
     * Mendapatkan statistik untuk Dashboard Controller
     */
    public function getStats(): array
    {
        return [
            'total' => $this->countAllResults(),
            'aktif' => $this->where('status', 'aktif')->countAllResults(),
        ];
    }

    /**
     * Mendapatkan opsi dropdown untuk Portal/Form lain
     * Mengembalikan hanya yang statusnya aktif dan terurut
     */
    public function getDropdownOptions(): array
    {
        return $this->where('status', 'aktif')
                    ->orderBy('urutan', 'ASC')
                    ->orderBy('nama_jenjang', 'ASC')
                    ->findAll();
    }

    /**
     * [FIX ERROR] Alias untuk getDropdownOptions 
     * Method ini dipanggil oleh TahunAjaran Controller
     */
    public function getAktifForIdentitas(): array
    {
        return $this->getDropdownOptions();
    }
}