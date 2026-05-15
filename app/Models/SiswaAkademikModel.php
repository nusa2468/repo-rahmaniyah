<?php namespace App\Models;

use CodeIgniter\Model;

/**
 * Model ini menangani logika akademik yang terkait dengan enrollment siswa (kelas, tahun ajaran).
 * Model ini merujuk ke tabel 'siswa_enrollment'.
 */
class SiswaAkademikModel extends Model
{
    // Menggunakan nama tabel yang benar: 'siswa_enrollment' 
    protected $table = 'siswa_enrollment';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false; 
    protected $protectFields = true;
    
    // Daftar kolom yang diizinkan untuk diisi oleh user
    protected $allowedFields = [
        'id_siswa', 
        'id_kelas',
        'id_tahun_ajaran', 
        'status_akademik', // FIX: Menggunakan nama kolom yang benar
        'tanggal_masuk', 
        
        // Field Nilai (asumsi jika model ini menangani nilai juga)
        'id_mata_pelajaran',
        'id_guru',
        'semester', 
        'jenis_nilai', 
        'nilai', 
        'deskripsi_nilai', 
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; 

    // --- METHOD UNTUK DASHBOARD AKADEMIK ---
    /**
     * Mengambil jumlah siswa per kelas yang terdaftar dalam tahun ajaran aktif.
     */
    public function getSiswaCountPerKelas(int $id_tahun_ajaran): array
    {
        // FIX: Menggunakan kolom 'status_akademik'
        return $this->select('id_kelas, COUNT(DISTINCT id_siswa) as total_siswa')
                    ->where('id_tahun_ajaran', $id_tahun_ajaran)
                    ->where('status_akademik', 'Aktif') 
                    ->groupBy('id_kelas')
                    ->findAll();
    }
    
    // --- METHOD UNTUK CONTROLLER NILAI.PHP (Memuat Daftar Siswa) ---
    /**
     * Mengambil daftar unik siswa yang terdaftar di kelas dan tahun ajaran tertentu.
     * Filter Kritis: Memastikan hanya siswa dengan status_akademik 'Aktif' yang dimuat.
     */
    public function getSiswaDiKelas(int $id_kelas, int $id_tahun_ajaran): array
    {
        return $this->db->table($this->table)
                    ->select('siswa.id as id_siswa_master, siswa.nama_lengkap, siswa.nisn')
                    ->join('siswa', "siswa.id = {$this->table}.id_siswa")
                    ->where("{$this->table}.id_kelas", $id_kelas)
                    ->where("{$this->table}.id_tahun_ajaran", $id_tahun_ajaran)
                    // FIX KRITIS: Tambahkan filter status_akademik 'Aktif'
                    ->where("{$this->table}.status_akademik", 'Aktif') 
                    ->groupBy('siswa.id') 
                    ->orderBy('siswa.nama_lengkap', 'ASC')
                    ->get()
                    ->getResultArray();
    }
    
    // --- METHOD UNTUK MENGAMBIL NILAI RAPOR (Dipertahankan) ---
    public function getNilaiRaporSiswa(int $id_siswa, int $id_tahun_ajaran, string $semester): array
    {
        return $this->select("{$this->table}.*, mp.nama_mapel, g.nama_lengkap as nama_guru")
                    ->join('mata_pelajaran mp', "mp.id = {$this->table}.id_mata_pelajaran", 'left')
                    ->join('guru g', "g.id = {$this->table}.id_guru", 'left') 
                    ->where("{$this->table}.id_siswa", $id_siswa)
                    ->where("{$this->table}.id_tahun_ajaran", $id_tahun_ajaran)
                    ->where("{$this->table}.semester", $semester)
                    ->where("{$this->table}.jenis_nilai", 'Rapor')  
                    ->findAll();
    }

    // --- METHOD BARU UNTUK UPSERT DATA NILAI (Dipertahankan) ---
    public function upsertNilai(array $data): bool
    {
        $kondisi = [
            'id_siswa'          => $data['id_siswa'],
            'id_mata_pelajaran' => $data['id_mata_pelajaran'],
            'id_tahun_ajaran'   => $data['id_tahun_ajaran'],
            'semester'          => $data['semester'],
            'jenis_nilai'       => $data['jenis_nilai'],
        ];

        $data_nilai = [
            'nilai'             => $data['nilai'], 
            'deskripsi_nilai'   => $data['deskripsi_nilai'] ?? null,
            'id_guru'           => $data['id_guru'] ?? null,
            'id_kelas'          => $data['id_kelas'] ?? null, 
        ];
        
        $existing = $this->where($kondisi)->first();

        if ($existing) {
            return $this->update($existing['id'], $data_nilai);
        } else {
            $data_insert = array_merge($kondisi, $data_nilai);
            return $this->insert($data_insert);
        }
    }
}