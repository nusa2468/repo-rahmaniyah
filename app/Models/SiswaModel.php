<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Master Siswa (Enterprise Edition - Final)
 * Status: UPDATED & FIXED (Termasuk method Dashboard Stats & Relasi Lengkap)
 */
class SiswaModel extends Model
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    
    // Konsisten menggunakan Array agar ringan dan kompatibel dengan Controller
    protected $returnType       = 'array'; 
    
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;
    
    // SINKRONISASI FIELD DENGAN TABEL DATABASE
    // Pastikan 'password' dan 'status' ada di sini agar bisa di-update
    protected $allowedFields = [
        // Identitas Utama (Wajib Dapodik)
        'nis', 
        'nisn', 
        'nik', 
        'nama_lengkap', 
        'jenis_kelamin', 
        'tempat_lahir', 
        'tanggal_lahir', 
        'nama_ibu_kandung', 
        'agama', 
        'alamat',
        'no_telp_ortu', // Tambahan umum
        'nama_ayah',    // Tambahan umum
        'nama_wali',    // Tambahan umum
        
        // Akademik Sekolah
        'kode_jenjang', 
        'angkatan', 
        'tahun_keluar', 
        'status',       // KRUSIAL UNTUK LOGIN
        'id_jurusan', 
        'id_kelas',     // Tambahan relasi kelas
        
        // Portal & Akun
        'id_orang_tua_portal', 
        'email', 
        'password',     // KRUSIAL UNTUK LOGIN
        'foto', 
    ];

    // Validasi
    protected $validationRules = [
        'id'             => 'permit_empty|integer', 
        'kode_jenjang'   => 'required|max_length[20]',
        'nama_lengkap'   => 'required|min_length[3]|max_length[255]',
        'jenis_kelamin'  => 'required|in_list[L,P]',
        'angkatan'       => 'permit_empty|valid_date[Y]', // Validasi tahun (4 digit)
        'status'         => 'permit_empty', // Dilonggarkan agar tidak strict enum saat insert awal
        
        // Validasi Unik (dengan ignore ID saat update)
        'nis'            => 'required|max_length[50]|is_unique[siswa.nis,id,{id}]',
        'nisn'           => 'permit_empty|exact_length[10]|is_unique[siswa.nisn,id,{id}]',
        'nik'            => 'permit_empty|exact_length[16]|is_unique[siswa.nik,id,{id}]',
        'email'          => 'permit_empty|valid_email|is_unique[siswa.email,id,{id}]',
        
        'password'       => 'permit_empty|min_length[6]', 
        'id_jurusan'     => 'permit_empty|integer',
        'id_kelas'       => 'permit_empty|integer', // Validasi untuk kolom baru
        'nama_ibu_kandung' => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages = [
        'kode_jenjang' => ['required' => 'Unit sekolah (Jenjang) wajib dipilih.'],
        'nis'          => [
            'required'  => 'NIS wajib diisi.',
            'is_unique' => 'NIS sudah terdaftar.'
        ]
    ];

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];
    protected $beforeDelete = ['deleteRelatedData']; 

    // --- LOGIC CALLBACKS ---

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Hapus data relasi saat Hard Delete (Purge).
     */
    protected function deleteRelatedData(array $data)
    {
        // Pengecekan apakah ini Hard Delete
        // Jika 'purge' tidak ada atau false, berarti ini Soft Delete -> Skip proses ini
        if (empty($data['purge']) || $data['purge'] !== true) {
            return $data;
        }

        if (empty($data['id'])) return $data;
        $idSiswa = is_array($data['id']) ? $data['id'] : [$data['id']];
        
        try {
            // Hapus Permanen data pendukung
            $this->db->table('siswa_demografi')->whereIn('id_siswa', $idSiswa)->delete();
            $this->db->table('siswa_keluarga')->whereIn('id_siswa', $idSiswa)->delete();
            
            if ($this->db->tableExists('siswa_enrollment')) {
                $this->db->table('siswa_enrollment')->whereIn('id_siswa', $idSiswa)->delete();
            }
            
            // Tambahan: Hapus data akademik juga
            if ($this->db->tableExists('siswa_akademik')) {
                $this->db->table('siswa_akademik')->whereIn('id_siswa', $idSiswa)->delete();
            }
            
            if ($this->db->tableExists('siswa_nilai')) {
                $this->db->table('siswa_nilai')->whereIn('id_siswa', $idSiswa)->delete();
            }
        } catch (\Exception $e) {
            // Log error jika diperlukan
        }
        
        return $data;
    }

    // --- DATA RETRIEVAL ---

    /**
     * Mengambil data siswa lengkap dengan relasi (Jurusan, Jenjang, Enrollment Terkini)
     * Dilengkapi Filter Unit (Scope)
     */
    public function getSiswaDataWithRelations(?string $unit = null, $id = null, $search = null)
    {
        $builder = $this->db->table($this->table . ' s')
            ->select('s.*, j.nama_jurusan, js.nama_jenjang')
            ->join('jurusan j', 'j.id = s.id_jurusan', 'left')
            ->join('jenjang_sekolah js', 'js.kode_jenjang = s.kode_jenjang', 'left')
            ->where('s.deleted_at', null); // Pastikan yang terhapus tidak muncul

        if ($id) $builder->where('s.id', $id);
        
        // --- FITUR SCOPE BERDASAR UNIT ---
        if ($unit && !in_array(strtoupper($unit), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('s.kode_jenjang', $unit);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('s.nama_lengkap', $search)
                    ->orLike('s.nisn', $search)
                    ->orLike('s.nis', $search)
                    ->groupEnd();
        }

        $siswaList = $builder->orderBy('s.nama_lengkap', 'ASC')->get()->getResultArray();
        
        if (empty($siswaList)) return [];

        // Jika hanya minta 1 data (by ID), kembalikan format lengkap
        // Jika list, kita bisa optimasi agar tidak query loop terlalu banyak
        
        $results = [];
        // Menggunakan loop untuk data detail (hati-hati N+1 problem jika data ribuan)
        foreach ($siswaList as $s) {
            $currentId = $s['id'];
            
            $enrollment = [];
            if ($this->db->tableExists('siswa_enrollment')) {
                // Ambil data enrollment aktif terakhir
                $enrollment = $this->db->table('siswa_enrollment se')
                    ->select('se.*, k.nama_kelas, ta.tahun_ajaran as nama_tahun_ajaran')
                    ->join('kelas k', 'k.id = se.id_kelas', 'left')
                    ->join('tahun_ajaran ta', 'ta.id = se.id_tahun_ajaran', 'left')
                    ->where('se.id_siswa', $currentId)
                    ->where('se.status_akademik', 'Aktif')
                    ->orderBy('se.id', 'DESC')
                    ->get()->getRowArray();
            }

            $demografi = [];
            if ($this->db->tableExists('siswa_demografi')) {
                $demografi = $this->db->table('siswa_demografi')->where('id_siswa', $currentId)->get()->getRowArray() ?? [];
            }

            $keluarga = [];
            if ($this->db->tableExists('siswa_keluarga')) {
                $keluarga = $this->db->table('siswa_keluarga')->where('id_siswa', $currentId)->get()->getResultArray() ?? [];
            }

            $results[] = [
                'siswa'              => $s,
                'demografi'          => $demografi,
                'keluarga'           => $keluarga,
                'enrollment_terkini' => $enrollment
            ];
        }

        return $results;
    }

    /**
     * Method untuk mengambil siswa berdasarkan kelas
     */
    public function getSiswaByKelas(int $id_kelas): array
    {
        if (!$this->db->tableExists('siswa_enrollment')) return [];

        return $this->select('siswa.*, se.id as id_enrollment')
            ->join('siswa_enrollment se', 'se.id_siswa = siswa.id')
            ->where('se.id_kelas', $id_kelas)
            ->where('se.status_akademik', 'Aktif')
            ->where('siswa.status', 'Aktif') // Case sensitive sesuai Enum DB biasanya 'Aktif'
            ->orderBy('siswa.nama_lengkap', 'ASC')
            ->findAll();
    }

    // =========================================================================
    //  METHOD KHUSUS DASHBOARD UTAMA (FIX ERROR)
    // =========================================================================

    /**
     * Menghitung total siswa aktif.
     * Digunakan oleh: App\Controllers\Home::index
     */
    public function countAllSiswaAktif(?string $kode_jenjang = null): int
    {
        // Handle variasi status 'Aktif' atau 'aktif'
        $query = $this->groupStart()
                      ->where('status', 'Aktif')
                      ->orWhere('status', 'aktif')
                      ->groupEnd()
                      ->where('deleted_at', null);
        
        // Filter Scope Unit
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $query->where('kode_jenjang', $kode_jenjang);
        }
        
        return $query->countAllResults();
    }

    /**
     * Statistik siswa per tingkat kelas.
     * Digunakan untuk grafik dashboard.
     */
    public function getSiswaAktifGroupedByTingkat(?string $kode_jenjang = null): array
    {
        if (!$this->db->tableExists('siswa_enrollment')) return [];

        $builder = $this->db->table($this->table . ' s')
            ->select('k.tingkat, COUNT(s.id) as total')
            ->join('siswa_enrollment se', 'se.id_siswa = s.id', 'inner')
            ->join('kelas k', 'k.id = se.id_kelas', 'left')
            ->whereIn('s.status', ['Aktif', 'aktif'])
            ->where('se.status_akademik', 'Aktif')
            ->where('s.deleted_at', null);

        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('s.kode_jenjang', $kode_jenjang);
        }

        return $builder->groupBy('k.tingkat')
                       ->orderBy('k.tingkat', 'ASC')
                       ->get()->getResultArray();
    }
}