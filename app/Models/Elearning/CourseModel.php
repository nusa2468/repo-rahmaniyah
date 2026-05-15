<?php

namespace App\Models\Elearning;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table            = 'el_courses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true; 
    protected $protectFields    = true;
    
    // Field yang boleh diisi (Sesuai Schema)
    protected $allowedFields    = [
        'kode_jenjang', 
        'nama_kelas', 
        'id_jadwal_pelajaran', 
        'id_guru', 
        'kode_gabung',         
        'deskripsi', 
        'banner_color',        
        'cover_image',
        'is_active',
        'created_at', 'updated_at', 'deleted_at' // Tambahan agar aman saat seeding manual
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation Rules (Penting untuk keamanan data)
    protected $validationRules = [
        'nama_kelas'   => 'required|min_length[3]',
        'kode_jenjang' => 'required',
        'id_guru'      => 'required|integer',
        'kode_gabung'  => 'required|is_unique[el_courses.kode_gabung,id,{id}]', // Cek unik kecuali diri sendiri saat update
    ];

    protected $validationMessages = [
        'kode_gabung' => [
            'is_unique' => 'Kode gabung ini sudah digunakan oleh kelas lain. Silakan generate ulang.'
        ]
    ];

    /**
     * Mengambil detail kelas beserta nama guru pengajarnya.
     * Berguna untuk Header Kelas.
     */
    public function getCourseDetail($id)
    {
        return $this->select('el_courses.*, guru.nama as nama_guru') // Asumsi kolom nama di tabel guru adalah 'nama'
                    ->join('guru', 'guru.id = el_courses.id_guru', 'left')
                    ->where('el_courses.id', $id)
                    ->first();
    }

    /**
     * Helper untuk filter berdasarkan jenjang (SD/SMP/SMA)
     * Penggunaan: $model->filterJenjang('SD')->findAll();
     */
    public function filterJenjang($jenjang)
    {
        return $this->where('kode_jenjang', $jenjang);
    }
}