<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola data master Guru.
 * Menggunakan tabel 'pegawai' dengan filter 'jenis_pegawai = guru'.
 * Sinkronisasi dengan Migration: CreatePegawaiTable
 */
class GuruModel extends Model
{
    // KONFIGURASI TABEL (PENTING: Menggunakan 'pegawai', bukan 'guru')
    protected $table            = 'pegawai';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    // KOLOM DIIZINKAN (Sesuai skema tabel 'pegawai' di Migration)
    protected $allowedFields = [
        'user_id',
        'kode_jenjang',      // Relasi Unit (SD/SMP/SMA/GLOBAL)
        
        // Identitas Diri
        'nama_lengkap',
        'gelar_depan',
        'gelar_belakang',
        'nik',               // Nomor Induk Kependudukan
        'nuptk',             // Nomor Unik Pendidik dan Tenaga Kependidikan
        'nip',               // Nomor Induk Pegawai
        'nipy',              // Nomor Induk Pegawai Yayasan
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_ibu_kandung',
        'agama',
        'status_perkawinan',

        // Kontak & Domisili
        'email',
        'no_hp',             // Sesuai migration (bukan 'telepon')
        'alamat_jalan',      // Sesuai migration (bukan 'alamat')
        'rt',
        'rw',
        'nama_dusun',
        'desa_kelurahan',
        'kecamatan',
        'kode_pos',

        // Kepegawaian
        'status_kepegawaian',
        'jenis_ptk',
        'tugas_tambahan',
        'sk_pengangkatan',
        'tmt_pengangkatan',  // Sesuai migration (bukan 'tmt_sekolah_induk')
        'sumber_gaji',
        'pendidikan_terakhir',

        // Sistem
        'jenis_pegawai',     // Filter wajib: 'guru'
        'status_aktif',      // Sesuai migration (bukan 'status')
        'foto',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation Rules (Disesuaikan dengan tabel pegawai)
    protected $validationRules = [
        'user_id'      => 'permit_empty|is_natural_no_zero',
        'nik'          => 'permit_empty|max_length[16]|is_unique[pegawai.nik,id,{id}]',
        'nuptk'        => 'permit_empty|max_length[16]',
        'nama_lengkap' => 'required|min_length[3]|max_length[100]',
        'kode_jenjang' => 'required|max_length[10]',
        'jenis_pegawai'=> 'required|in_list[guru,staff,penunjang]',
    ];

    protected $validationMessages = [
        'nik'          => ['is_unique' => 'NIK ini sudah terdaftar.'],
        'nama_lengkap' => ['required' => 'Nama Lengkap wajib diisi.'],
        'kode_jenjang' => ['required' => 'Unit Jenjang wajib dipilih.'],
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // =========================================================================
    // GLOBAL SCOPE & OVERRIDES
    // =========================================================================

    /**
     * Override findAll untuk selalu memfilter jenis_pegawai = 'guru'
     */
    public function findAll(?int $limit = null, int $offset = 0)
    {
        $this->where('jenis_pegawai', 'guru');
        return parent::findAll($limit, $offset);
    }

    /**
     * Override find untuk memastikan ID yang dicari adalah guru
     */
    public function find($id = null)
    {
        $this->where('jenis_pegawai', 'guru');
        return parent::find($id);
    }

    /**
     * Scope Helper: Memfilter berdasarkan Kode Jenjang jika tidak GLOBAL.
     * Gunakan ini di Controller: $model->filterJenjang($userJenjang)->findAll();
     */
    public function filterJenjang(?string $kode_jenjang)
    {
        if ($kode_jenjang && strtoupper($kode_jenjang) !== 'GLOBAL') {
            $this->where('kode_jenjang', $kode_jenjang);
        }
        return $this;
    }

    // =========================================================================
    // CUSTOM METHODS (Updated with Scope Unit & Correct Columns)
    // =========================================================================
    
    /**
     * Mengambil semua data guru yang berstatus 'aktif'.
     */
    public function getActiveTeachers(?string $kode_jenjang = null): array
    {
        $this->filterJenjang($kode_jenjang);
        
        return $this->where('jenis_pegawai', 'guru')
                    ->where('status_aktif', 'aktif') // Update kolom status
                    ->orderBy('nama_lengkap', 'ASC')
                    ->findAll();
    }
    
    /**
     * Menghitung total guru dengan status 'aktif'.
     * Digunakan di Home Controller.
     */
    public function countAllGuruAktif(?string $kode_jenjang = null): int
    {
        $this->filterJenjang($kode_jenjang);

        return $this->where('jenis_pegawai', 'guru')
                    ->where('status_aktif', 'aktif') // Update kolom status
                    ->countAllResults();
    }

    /**
     * Menghitung total guru berdasarkan status kepegawaian.
     */
    public function countGuruByKepegawaianStatus(string $statusKepegawaian, ?string $kode_jenjang = null): int
    {
        $this->filterJenjang($kode_jenjang);

        return $this->where('jenis_pegawai', 'guru')
                    ->where('status_kepegawaian', $statusKepegawaian)
                    ->where('status_aktif', 'aktif')
                    ->countAllResults();
    }

    /**
     * Mengambil daftar lengkap data guru beserta ringkasan relasinya.
     */
    public function getGuruDataWithRelations(?string $kode_jenjang = null): array
    {
        $this->filterJenjang($kode_jenjang);

        $gurus = $this->whereIn('status_aktif', ['aktif', 'nonaktif']) // Update kolom status
                      ->orderBy('nama_lengkap', 'ASC')
                      ->findAll();

        if (empty($gurus)) {
            return [];
        }

        // Muat Model Relasi secara manual
        try {
            $riwayatPendidikanModel = model('App\Models\RiwayatPendidikanModel', false);
            $penugasanMengajarModel = model('App\Models\PenugasanMengajarModel', false);
        } catch (\Exception $e) {
            return array_map(function($guru) {
                return [
                    'guru'                 => $guru,
                    'pendidikan_tertinggi' => null,
                    'penugasan_terkini'    => null
                ];
            }, $gurus);
        }

        $results = [];

        foreach ($gurus as $guru) {
            $guruId = $guru['id'];
            
            // 1. Pendidikan Tertinggi
            $pendidikanTertinggi = null;
            if ($riwayatPendidikanModel) {
                $pendidikanTertinggi = $riwayatPendidikanModel->where('guru_id', $guruId)
                                                              ->orderBy('tahun_lulus', 'DESC')
                                                              ->first();
            }

            // 2. Penugasan Terkini
            $penugasanTerkini = null;
            if ($penugasanMengajarModel) {
                $penugasanTerkini = $penugasanMengajarModel->where('guru_id', $guruId)
                                                           ->orderBy('id', 'DESC')
                                                           ->first(); 
            }

            $results[] = [
                'guru'                 => $guru,
                'pendidikan_tertinggi' => $pendidikanTertinggi, 
                'penugasan_terkini'    => $penugasanTerkini,
            ];
        }

        return $results;
    }

    /**
     * Mengambil informasi Kelas jika Guru yang bersangkutan adalah Wali Kelas.
     */
    public function getWaliKelasInfo(int $id_guru): ?array
    {
        if (!$this->db->tableExists('kelas')) {
            return null;
        }

        return $this->db->table('kelas')
                    ->select('id as id_kelas, nama_kelas')
                    ->where('id_wali_kelas', $id_guru)
                    ->where('is_aktif', 1)
                    ->get()
                    ->getRowArray();
    }
    
    /**
     * Mengambil data profil guru secara lengkap berdasarkan ID User (untuk Auth).
     */
    public function getGuruProfileByUserId(int $user_id): ?array
    {
        return $this->where('jenis_pegawai', 'guru')
                    ->where('user_id', $user_id)
                    ->first();
    }
}
