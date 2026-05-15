<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model NilaiModel
 * Mengelola data nilai akademik siswa (PH, UTS, UAS, Rapor).
 * Sinkron dengan Migrasi: CreatePrimaryAcademicData
 */
class NilaiModel extends Model
{
    // =========================================================================
    // 1. KONFIGURASI MODEL UTAMA
    // =========================================================================
    protected $table             = 'nilai_siswa';
    protected $primaryKey        = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType        = 'array';
    protected $useSoftDeletes    = true; 
    protected $protectFields     = true;

    // =========================================================================
    // 2. JEJAK WAKTU (TIMESTAMPS)
    // =========================================================================
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // =========================================================================
    // 3. KOLOM YANG DIIZINKAN (Allowed Fields)
    // =========================================================================
    protected $allowedFields = [
        'id_enrollment',        // Referensi pendaftaran siswa
        'id_kelas',
        'id_siswa',
        'id_mata_pelajaran',
        'id_guru',
        'id_tahun_ajaran',      // Kolom wajib untuk integritas data per periode
        'semester',             // Ganjil/Genap
        'kode_jenjang',         // Unit (SD/SMP/SMA)
        'kategori_nilai',       // Kategori: PH, UTS, UAS, dll
        'nilai_absensi',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'nilai_huruf',
        'keterangan',
        'is_deleted_key',       // Penanda ID untuk memecah unique constraint soft delete
        'deleted_at'
    ];

    // =========================================================================
    // 4. ATURAN VALIDASI
    // =========================================================================
    protected $validationRules = [
        'id_kelas'           => 'required|integer',
        'id_siswa'           => 'required|integer',
        'id_mata_pelajaran'  => 'required|integer',
        'id_tahun_ajaran'    => 'required|integer',
        'semester'           => 'required|in_list[Ganjil,Genap]',
        'kode_jenjang'       => 'permit_empty|max_length[20]',
        
        // Validasi Range Nilai 0-100
        'nilai_absensi'      => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'nilai_tugas'        => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'nilai_uts'          => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'nilai_uas'          => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        'nilai_akhir'        => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        
        'nilai_huruf'        => 'permit_empty|max_length[2]',
    ];

    protected $validationMessages = []; 
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // =========================================================================
    // 5. HOOKS (PEMICU OTOMATIS)
    // =========================================================================
    protected $beforeDelete = ['setDeleteKey'];

    /**
     * Mengatur is_deleted_key saat data di-soft delete agar data baru 
     * dengan kombinasi yang sama tidak terkena error duplicate entry.
     */
    protected function setDeleteKey(array $data)
    {
        if ($this->useSoftDeletes && !($data['purge'] ?? false) && !empty($data['id'])) {
            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
            foreach ($ids as $id) {
                $this->db->table($this->table)
                    ->where('id', $id)
                    ->set('is_deleted_key', $id) 
                    ->update();
            }
        }
        return $data;
    }

    // =========================================================================
    // 6. METODE PENGAMBILAN DATA (QUERIES)
    // =========================================================================

    /**
     * Mengambil daftar nilai dengan Pagination dan Scope Jenjang.
     */
    public function getNilaiPaginated(string $jenjang, int $perPage = 20, ?string $keyword = null): array
    {
        // Pastikan menggunakan $this secara langsung agar tersambung dengan paginate() di Controller
        $this->select($this->table . '.*, 
                       siswa.nama_lengkap as nama_siswa, 
                       siswa.nis, 
                       kelas.nama_kelas, 
                       mata_pelajaran.nama_mapel, 
                       pegawai.nama_lengkap as nama_guru');
        
        $this->join('siswa', 'siswa.id = ' . $this->table . '.id_siswa', 'left');
        $this->join('kelas', 'kelas.id = ' . $this->table . '.id_kelas', 'left');
        $this->join('mata_pelajaran', 'mata_pelajaran.id = ' . $this->table . '.id_mata_pelajaran', 'left');
        $this->join('pegawai', 'pegawai.id = ' . $this->table . '.id_guru', 'left');

        // Filter Scope Unit
        if ($jenjang !== 'Global' && !empty($jenjang)) {
            $this->where('kelas.kode_jenjang', $jenjang);
        }

        // Pencarian
        if ($keyword) {
            $this->groupStart()
                ->like('siswa.nama_lengkap', $keyword)
                ->orLike('siswa.nis', $keyword)
                ->orLike('mata_pelajaran.nama_mapel', $keyword)
                ->orLike('kelas.nama_kelas', $keyword)
            ->groupEnd();
        }

        return $this->orderBy($this->table . '.updated_at', 'DESC')->paginate($perPage);
    }

    /**
     * Mengambil data nilai yang sudah ada berdasarkan kelas, mapel, dan semester.
     * Digunakan untuk memetakan data ke dalam form input (kelola).
     */
    public function getNilaiByKelasAndMapel(int $id_kelas, int $id_mata_pelajaran, string $semester): array
    {
        $result = $this->where([
            'id_kelas'          => $id_kelas, 
            'id_mata_pelajaran' => $id_mata_pelajaran, 
            'semester'          => $semester
        ])->findAll();

        $nilai_map = [];
        foreach ($result as $item) {
            $nilai_map[$item['id_siswa']] = $item;
        }
        return $nilai_map;
    }

    /**
     * Mengambil riwayat nilai (leger) untuk siswa tertentu.
     */
    public function getLegerNilaiSiswa(int $id_siswa, string $semesterFilter = 'Semua'): array
    {
        $this->select($this->table . '.*, mata_pelajaran.nama_mapel, mata_pelajaran.kode_mapel, pegawai.nama_lengkap AS nama_guru');
        $this->join('mata_pelajaran', 'mata_pelajaran.id = ' . $this->table . '.id_mata_pelajaran', 'left');
        $this->join('pegawai', 'pegawai.id = ' . $this->table . '.id_guru', 'left');
        $this->where($this->table . '.id_siswa', $id_siswa);
        
        if ($semesterFilter !== 'Semua') {
            $this->where($this->table . '.semester', $semesterFilter); 
        }

        return $this->orderBy($this->table . '.semester', 'ASC')
                    ->orderBy('mata_pelajaran.kode_mapel', 'ASC')
                    ->findAll();
    }

    // =========================================================================
    // 7. METODE SIMPAN (SMART UPSERT)
    // =========================================================================

    /**
     * Simpan data nilai secara batch/tunggal dengan logika deteksi duplikasi.
     * Jika data sudah ada (termasuk yang di-soft delete), maka akan di-update/restore.
     */
    public function saveNilaiLengkap(array $data): bool
    {
        // Kunci unik untuk pengecekan data ganda
        $unique_keys = [
            'id_siswa'          => $data['id_siswa'],
            'id_mata_pelajaran' => $data['id_mata_pelajaran'],
            'id_tahun_ajaran'   => $data['id_tahun_ajaran'],
            'semester'          => $data['semester']
        ];

        // Cari data yang sudah ada (termasuk data terhapus/soft-deleted)
        $existing = $this->withDeleted()->where($unique_keys)->first();

        if ($existing) {
            // Update data yang ada dan pastikan is_deleted_key serta deleted_at di-reset (restore)
            $data['deleted_at']     = null;
            $data['is_deleted_key'] = null;
            return $this->update($existing['id'], $data);
        }

        // Insert jika data benar-benar baru
        return (bool)$this->insert($data);
    }

    // =========================================================================
    // 8. DATABASE TRANSACTION PROXY
    // =========================================================================
    public function transStart() { return $this->db->transStart(); }
    public function transComplete() { return $this->db->transComplete(); }
    public function transStatus() { return $this->db->transStatus(); }
    public function transRollback() { return $this->db->transRollback(); }
    public function error() { return $this->db->error(); }
}