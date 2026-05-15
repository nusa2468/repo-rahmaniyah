<?php

namespace App\Models\Kesiswaan;

use CodeIgniter\Model;

class PrestasiSiswaModel extends Model
{
    protected $table            = 'kesiswaan_prestasi'; 
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    
    // FIX: Menggunakan 'tahun_ajar_id' (bukan id_tahun_ajaran) sesuai konvensi siswa_id
    protected $allowedFields    = [
        'siswa_id', 'tahun_ajar_id', 'jenis_prestasi', 'nama_prestasi', 
        'tingkat', 'peringkat', 'tanggal_prestasi', 'keterangan'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; 

    // Aturan Validasi (FIX: tahun_ajar_id)
    protected $validationRules = [
        'siswa_id'          => 'required|integer',
        'tahun_ajar_id'     => 'permit_empty|integer',
        'tanggal_prestasi'    => 'required|valid_date',
        'jenis_prestasi'    => 'required|max_length[100]',
        'nama_prestasi'     => 'required|max_length[255]',
        'tingkat'           => 'required|in_list[Sekolah,Kabupaten/Kota,Provinsi,Nasional,Internasional]',
        'peringkat'          => 'permit_empty|max_length[50]',
        'keterangan'        => 'permit_empty',
    ];
    
    protected $validationMessages = [
        'siswa_id' => ['required' => 'Siswa wajib dipilih.'],
        'nama_prestasi' => ['required' => 'Nama prestasi wajib diisi.'],
        'tingkat' => ['required' => 'Tingkat prestasi wajib dipilih.'],
        'tanggal_prestasi' => ['required' => 'Tanggal penerimaan wajib diisi.'],
    ];

    /**
     * Mengambil daftar prestasi dengan detail Siswa dan Kelas.
     */
    public function getPrestasiDetail($tahun_ajar_id = null)
    {
        // UPDATE: Join menggunakan kesiswaan_prestasi.tahun_ajar_id
        // NOTE: Diasumsikan tabel siswa_enrollment menggunakan kolom 'id_tahun_ajaran' atau 'tahun_ajar_id'. 
        // Jika error berlanjut di 'se.id_tahun_ajaran', ganti juga menjadi 'se.tahun_ajar_id'.
        $builder = $this->select('kesiswaan_prestasi.*, s.nama_lengkap as nama_siswa, s.nisn, s.kode_jenjang, k.nama_kelas')
            ->join('siswa s', 's.id = kesiswaan_prestasi.siswa_id')
            // JOIN FIX: kesiswaan_prestasi.tahun_ajar_id
            ->join('siswa_enrollment se', 'se.id_siswa = s.id AND se.id_tahun_ajaran = kesiswaan_prestasi.tahun_ajar_id', 'left') 
            ->join('kelas k', 'k.id = se.id_kelas', 'left');
            
        if ($tahun_ajar_id) {
            $builder->where('kesiswaan_prestasi.tahun_ajar_id', $tahun_ajar_id);
        }
        
        $builder->groupBy('kesiswaan_prestasi.id');
            
        return $builder->orderBy('kesiswaan_prestasi.tanggal_prestasi', 'DESC')->findAll();
    }
    
    /**
     * Mengambil detail satu prestasi dengan join ke tabel siswa dan kelas.
     */
    public function getPrestasiDetailById($id)
    {
        $builder = $this->select('kesiswaan_prestasi.*, s.nama_lengkap as nama_siswa, s.nisn, s.kode_jenjang, k.nama_kelas')
            ->join('siswa s', 's.id = kesiswaan_prestasi.siswa_id')
            // JOIN FIX: kesiswaan_prestasi.tahun_ajar_id
            ->join('siswa_enrollment se', 'se.id_siswa = s.id AND se.id_tahun_ajaran = kesiswaan_prestasi.tahun_ajar_id', 'left') 
            ->join('kelas k', 'k.id = se.id_kelas', 'left')
            ->where('kesiswaan_prestasi.id', $id);
            
        if ($this->useSoftDeletes) {
            $builder->where('kesiswaan_prestasi.' . $this->deletedField, null);
        }
        
        $builder->groupBy('kesiswaan_prestasi.id');
        
        return $builder->get()->getRowArray();
    }
}