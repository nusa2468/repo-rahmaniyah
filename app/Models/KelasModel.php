<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Kelas (Enterprise Edition - Final Fixed)
 * Mengelola data Rombongan Belajar (Rombel) per Unit.
 * Fitur: Scope Unit (Jenjang), Join Relasi Pegawai, Pagination Support, & Statistik Dashboard.
 */
class KelasModel extends Model
{
    protected $table            = 'kelas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; 
    protected $useSoftDeletes   = true; 
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang',     // Link ke jenjang_sekolah
        'nama_kelas', 
        'tingkat', 
        'id_wali_kelas',    // Relasi ke tabel pegawai
        'id_tahun_ajaran', 
        'id_kurikulum', 
        'id_jurusan', 
        'is_aktif',         // 1 = Aktif, 0 = Tidak Aktif
        'kapasitas',        
        'terisi'            
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Aturan Validasi
    protected $validationRules = [
        'id'              => 'permit_empty|integer', 
        'kode_jenjang'    => 'required|max_length[20]',
        'nama_kelas'      => 'required|min_length[2]|max_length[100]',
        'tingkat'         => 'required|integer', 
        'id_wali_kelas'   => 'permit_empty|integer|is_not_unique[pegawai.id]',
        'id_tahun_ajaran' => 'required|integer|is_not_unique[tahun_ajaran.id]',
        'id_kurikulum'    => 'required|integer|is_not_unique[kurikulum.id]',
        'id_jurusan'      => 'permit_empty|integer|is_not_unique[jurusan.id]',
        'is_aktif'        => 'required|in_list[0,1]',
    ];

    /**
     * Mempersiapkan Query Builder untuk pengambilan data kelas.
     */
    public function getKelasBuilder(?string $kode_jenjang = null, ?string $search = null)
    {
        // FIX ALIAS: Menggunakan 'nama_wali_kelas' agar sinkron dengan pemanggilan di View
        $this->select('kelas.*, 
                       js.nama_jenjang as unit_sekolah,
                       p.nama_lengkap as nama_wali_kelas, 
                       ta.tahun_ajaran, ta.semester,
                       kur.nama_kurikulum,
                       jur.nama_jurusan')
             ->join('jenjang_sekolah js', 'js.kode_jenjang = kelas.kode_jenjang', 'left')
             ->join('pegawai p', 'p.id = kelas.id_wali_kelas', 'left') 
             ->join('tahun_ajaran ta', 'ta.id = kelas.id_tahun_ajaran', 'left')
             ->join('kurikulum kur', 'kur.id = kelas.id_kurikulum', 'left')
             ->join('jurusan jur', 'jur.id = kelas.id_jurusan', 'left');

        // Filter Scope Unit
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $this->where('kelas.kode_jenjang', $kode_jenjang);
        }

        // Fitur Pencarian
        if ($search) {
            $this->groupStart()
                 ->like('kelas.nama_kelas', $search)
                 ->orLike('p.nama_lengkap', $search)
                 ->groupEnd();
        }

        // Default Sorting
        $this->orderBy('js.urutan', 'ASC')
             ->orderBy('kelas.tingkat', 'ASC')
             ->orderBy('kelas.nama_kelas', 'ASC');

        return $this;
    }

    /**
     * Mengambil statistik ringkas rombel (Kembali ke Result Array untuk View).
     */
    public function getStats(?string $kode_jenjang = null): array
    {
        $builder = $this->db->table($this->table);
        $builder->select('is_aktif, COUNT(id) as total');
        
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('kode_jenjang', $kode_jenjang);
        }
        
        $builder->where('deleted_at', null);
        return $builder->groupBy('is_aktif')->get()->getResultArray();
    }
}