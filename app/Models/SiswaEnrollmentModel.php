<?php 

namespace App\Models;

use CodeIgniter\Model;

class SiswaEnrollmentModel extends Model
{
    protected $table = 'siswa_enrollment';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = false; 

    protected $allowedFields = [
        'id_siswa', 
        'id_kelas', 
        'id_tahun_ajaran', 
        'id_grup_siswa',  // Wajib diizinkan
        'id_kurikulum',   // Wajib diizinkan
        'status_akademik', 
        'tanggal_masuk', 
        'tanggal_keluar'
    ];
    
    // VALIDASI DASAR: Penting agar data yang dimasukkan ke DB terjamin.
    protected $validationRules = [
        'id_siswa'        => 'required|integer',
        'id_kelas'        => 'required|integer',
        'id_tahun_ajaran' => 'required|integer',
        
        // FIX KRITIS: Mengubah dari permit_empty menjadi REQUIRED untuk memastikan data penting ada, 
        // yang mana ini sering menjadi penyebab NOT NULL error di DB.
        'id_grup_siswa'   => 'required|integer', // ASUMSI: Wajib diisi (NOT NULL)
        'id_kurikulum'    => 'required|integer', // ASUMSI: Wajib diisi (NOT NULL)
        
        'status_akademik' => 'required|in_list[Aktif,Selesai]',
        
        // FIX KRITIS: Tanggal masuk hampir selalu wajib diisi (NOT NULL)
        'tanggal_masuk'   => 'required|valid_date', 
        'tanggal_keluar'  => 'permit_empty|valid_date', // Tanggal keluar boleh kosong (NULL) saat enrollment aktif
    ];
    
    protected $validationMessages = [
        'id_grup_siswa' => [
            'required' => 'Grup Siswa (id_grup_siswa) wajib diisi untuk enrollment baru. Data tidak terambil dari enrollment lama.'
        ],
        'id_kurikulum' => [
            'required' => 'Kurikulum (id_kurikulum) wajib diisi untuk enrollment baru. Data tidak terambil dari enrollment lama.'
        ],
        'tanggal_masuk' => [
            'required' => 'Tanggal Masuk wajib diisi.'
        ]
    ];
}