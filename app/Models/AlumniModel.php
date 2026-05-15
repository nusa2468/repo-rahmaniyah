<?php

namespace App\Models;

use CodeIgniter\Model;

class AlumniModel extends Model
{
    protected $table             = 'alumni';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'array';
    protected $useSoftDeletes    = true; 
    protected $protectFields     = true;
    
    // ** FIELD YANG DIPERBOLEHKAN (SINKRONISASI DENGAN DB TERBARU) **
    protected $allowedFields     = [
        'id_siswa',               
        'level_alumni',           
        'tahun_lulus',            
        'jurusan',                  
        'telepon',                  
        'email',                    
        'kategori_lanjutan',        // Status Lanjutan 
        'detail_lanjutan',          // Keterangan Detail
        'alamat_kontak',            
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; 
    
    // Validation
    protected $validationRules = [
        'id'                     => 'permit_empty|integer', 
        'id_siswa'               => 'required|integer|is_unique[alumni.id_siswa,id,{id}]', 
        'level_alumni'           => 'required|in_list[SD,SMP,SMA/SMK]',
        'tahun_lulus'            => 'required|numeric|min_length[4]|max_length[4]',
        'email'                  => 'permit_empty|valid_email|max_length[100]', 
        'telepon'                => 'permit_empty|max_length[25]',
        'jurusan'                => 'permit_empty|max_length[100]',
        
        // FIX KRITIS: Validasi custom di kategori_lanjutan
        'kategori_lanjutan'      => 'required|validation_level_specific_status[level_alumni]', 
        
        // Mengubah detail_lanjutan menjadi permit_empty (Keterangan bersifat opsional)
        'detail_lanjutan'        => 'permit_empty', 
        
        // Kolom lama dihapus sepenuhnya dari rules
    ];
    
    protected $validationMessages = [
        'id_siswa' => [
            'required'  => 'ID Siswa harus diisi (siswa belum terpilih).',
            'is_unique' => 'Siswa ini sudah terdaftar sebagai Alumni.',
        ],
        'level_alumni' => [
            'required'  => 'Tingkatan Alumni (SD/SMP/SMA/SMK) harus dipilih.',
            'in_list'   => 'Tingkatan Alumni tidak valid.',
        ],
        'tahun_lulus' => [
            'required'      => 'Tahun Lulus harus diisi.',
            'min_length'    => 'Tahun Lulus harus 4 digit.',
            'max_length'    => 'Tahun Lulus harus 4 digit.',
        ],
        // FIX: Menggunakan kategori_lanjutan sebagai target pesan error
        'kategori_lanjutan' => [ 
            'required'  => 'Status Lanjutan harus diisi.',
            'validation_level_specific_status' => 'Status Lanjutan yang dipilih tidak sesuai dengan tingkatan alumni (SD/SMP hanya boleh Studi Lanjut).',
        ],
        'email' => [
            'valid_email' => 'Format email tidak valid.',
            'max_length'  => 'Email maksimal 100 karakter.',
        ],
    ];
    protected $skipValidation        = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;

    /**
     * Callback validasi kustom untuk memastikan status lanjutan sesuai dengan level alumni.
     */
    public function validation_level_specific_status(string $value, string $levelField, array $data): bool
    {
        // Pastikan field level_alumni ada dalam data
        if (!isset($data[$levelField])) {
            return false;
        }

        $level = $data[$levelField];

        // Status yang diizinkan untuk semua level
        $allowedAll = ['Studi Lanjut', 'Bekerja', 'Lainnya']; 
        
        if ($level === 'SMA/SMK') {
            // SMA/SMK diizinkan semua
            return in_array($value, $allowedAll);
        } elseif ($level === 'SD' || $level === 'SMP') {
            // SD/SMP hanya diizinkan 'Studi Lanjut'
            return $value === 'Studi Lanjut';
        }

        return false;
    }

    /**
     * Mengambil data alumni dengan join ke tabel siswa dan siswa_demografi.
     */
    public function getAlumniWithSiswa($id = null)
    {
        $builder = $this->db->table($this->table);
        
        $builder->select('alumni.*');
        
        // --- DATA DARI TABEL SISWA (s) ---
        $builder->select('s.nama_lengkap AS nama, s.jenis_kelamin, s.nis, s.nisn'); 
        
        // --- DATA DARI TABEL SISWA_DEMOGRAFI (sd) ---
        $builder->select('sd.email AS email_siswa_demografi, sd.telepon AS telepon_siswa_demografi');
        
        // --- ALIAS YANG DIMINTA VIEW (Untuk tampilan Index) ---
        $builder->select('alumni.kategori_lanjutan AS lanjut_studi'); 
        $builder->select('alumni.detail_lanjutan AS pekerjaan'); 
        $builder->select('alumni.alamat_kontak AS kontak_alumni'); 

        // LEFT JOIN ke siswa
        $builder->join('siswa s', 's.id = alumni.id_siswa', 'left'); 
        $builder->join('siswa_demografi sd', 'sd.id_siswa = s.id', 'left');

        // Mengembalikan filter soft delete karena ini adalah daftar AKTIF.
        if ($this->useSoftDeletes) {
            $builder->where('alumni.' . $this->deletedField, null);
        }
        
        $builder->orderBy('alumni.tahun_lulus', 'DESC');
        $builder->orderBy('s.nama_lengkap', 'ASC');

        if ($id) {
            return $builder->where('alumni.id', $id)->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }
    
    /**
     * Mengambil daftar siswa yang berstatus 'lulus' dan BELUM terdaftar di tabel alumni.
     */
    public function getSiswaLulusNotAlumni()
    {
        $db = \Config\Database::connect();
        $builderSiswa = $db->table('siswa');
        
        // FIX KRITIS: Memastikan subquery dibuat dari instance DB yang bersih
        $subqueryBuilder = $db->table('alumni');
        
        // Subquery: Cari ID siswa yang sudah ada di tabel alumni (yang aktif / belum di soft delete)
        $subquery = $subqueryBuilder
                             ->select('id_siswa')
                             ->where($this->deletedField, null)
                             ->getCompiledSelect();

        // Query Utama: Ambil data dari tabel siswa
        $builderSiswa->select('id, nama_lengkap, nisn, tahun_keluar'); 
        $builderSiswa->where('status', 'lulus'); 
        
        // Filter: Siswa.id TIDAK BOLEH ada di hasil subquery (tabel alumni)
        $builderSiswa->whereNotIn('id', [$subquery], false);
        $builderSiswa->orderBy('nama_lengkap', 'ASC');

        return $builderSiswa->get()->getResultArray();
    }
}