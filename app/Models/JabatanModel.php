<?php

namespace App\Models;

use CodeIgniter\Model;

class JabatanModel extends Model
{
    protected $table            = 'jabatan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Diubah ke array agar konsisten dengan Controller
    protected $useSoftDeletes   = true;
    
    // Disinkronkan menggunakan 'kode_jenjang' agar sama dengan master jenjang
    protected $allowedFields = [
        'nama_jabatan', 
        'level', 
        'atasan', // Ini akan menjadi parent_id
        'kode_jenjang' 
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mendapatkan semua jabatan beserta nama jabatannya, 
     * nama jabatan atasannya (Self-Join), dan diurutkan.
     */
    public function getJabatanWithAtasan()
    {
        return $this->select('
                        jabatan.*, 
                        p.nama_jabatan as nama_atasan,
                        jabatan.atasan as parent_id  -- ALIAS PENTING: Untuk kompatibilitas dengan script Visual Tree
                    ')
                    ->join('jabatan p', 'p.id = jabatan.atasan', 'left')
                    
                    // Urutan Logis: Level tertinggi (angka kecil) duluan, misal Yayasan (1) baru Kepsek (2)
                    ->orderBy('jabatan.level', 'ASC') 
                    
                    // Kemudian urutkan per unit agar rapi
                    ->orderBy('jabatan.kode_jenjang', 'ASC') 
                    
                    ->findAll();
    }

    /**
     * Helper untuk mengambil opsi atasan yang valid
     * Mencegah jabatan memilih dirinya sendiri sebagai atasan (Circular Logic Prevention)
     */
    public function getValidParents($currentId = null)
    {
        $builder = $this->select('id, nama_jabatan, kode_jenjang, level')
                        ->orderBy('level', 'ASC')
                        ->orderBy('kode_jenjang', 'ASC');

        if ($currentId) {
            $builder->where('id !=', $currentId);
            // Opsional: Bisa ditambahkan logika untuk mencegah memilih bawahan sebagai atasan
        }

        return $builder->findAll();
    }
}