<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Karyawan (Enterprise Edition - Updated for Unified Table)
 * Mengelola data operasional Staf Non-Guru via tabel 'pegawai'.
 * Fitur: Soft Deletes, Validasi NIK/NIP, Join Unit Jenjang, & Statistik Dashboard.
 * Update: Sinkronisasi kolom (Add: nipy, tugas_tambahan | Remove: jabatan).
 */
class KaryawanModel extends Model
{
    // --- Konfigurasi Tabel Utama (Unified) ---
    protected $table            = 'pegawai';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    // --- Kolom yang Diizinkan (Sesuai Migrasi CreatePegawaiTable) ---
    protected $allowedFields = [
        'user_id',
        'kode_jenjang',      // Relasi Unit (SD/SMP/SMA/GLOBAL)
        
        // Identitas Diri
        'nama_lengkap',
        'gelar_depan',
        'gelar_belakang',
        'nik',               // Nomor Induk Kependudukan (Wajib)
        'nuptk',             // NUPTK (Opsional untuk staff, tapi struktur tabel ada)
        'nip',               // NIP (PNS)
        'nipy',              // NIP Yayasan (Internal) -> FIXED: Ditambahkan
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_ibu_kandung',
        'agama',
        'status_perkawinan',

        // Kontak & Domisili
        'email',
        'no_hp',
        'alamat_jalan',
        'rt', 'rw', 'nama_dusun', 'desa_kelurahan', 'kecamatan', 'kode_pos',

        // Kepegawaian
        'status_kepegawaian',
        'jenis_ptk',         // Pengganti 'jabatan' secara formal (e.g. Tenaga Admin)
        'tugas_tambahan',    // FIXED: Ditambahkan (e.g. Kepala TU)
        'sk_pengangkatan',
        'tmt_pengangkatan',
        'sumber_gaji',
        'pendidikan_terakhir',

        // Sistem
        'jenis_pegawai',     // Filter wajib: staff/penunjang
        'status_aktif',      // Filter aktif/nonaktif
        'foto',
    ];

    // --- Otomasi Timestamps ---
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // --- Aturan Validasi ---
    protected $validationRules = [
        'id'             => 'permit_empty|integer',
        'kode_jenjang'   => 'required|max_length[10]',
        'nip'            => 'permit_empty|max_length[25]|is_unique[pegawai.nip,id,{id}]',
        'nipy'           => 'permit_empty|max_length[20]',
        'nik'            => 'required|exact_length[16]|numeric|is_unique[pegawai.nik,id,{id}]',
        'nama_lengkap'   => 'required|min_length[3]|max_length[100]',
        'jenis_kelamin'  => 'required|in_list[L,P]',
        'tempat_lahir'   => 'permit_empty|max_length[32]',
        'tanggal_lahir'  => 'permit_empty|valid_date',
        'email'          => 'permit_empty|valid_email|max_length[60]|is_unique[pegawai.email,id,{id}]',
        'status_aktif'   => 'required|in_list[aktif,nonaktif,cuti,pensiun,meninggal]',
        'no_hp'          => 'permit_empty|max_length[20]',
        'tmt_pengangkatan' => 'permit_empty|valid_date',
    ];

    protected $validationMessages = [
        'kode_jenjang' => [
            'required' => 'Unit penempatan kerja (Jenjang) wajib dipilih.'
        ],
        'nip' => [
            'is_unique'  => 'Nomor Induk Pegawai (NIP) tersebut sudah terdaftar.',
        ],
        'nik' => [
            'required'     => 'NIK wajib diisi sesuai KTP.',
            'is_unique'    => 'NIK tersebut sudah terdaftar.',
            'exact_length' => 'NIK harus 16 digit.',
            'numeric'      => 'NIK hanya boleh angka.'
        ],
        'nama_lengkap' => [
            'required' => 'Nama lengkap wajib diisi.',
        ],
        'email' => [
            'is_unique' => 'Email ini sudah digunakan.',
        ]
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // =========================================================================
    // OVERRIDES (Global Scope untuk Karyawan)
    // =========================================================================

    /**
     * Override findAll untuk memfilter hanya Staff & Penunjang
     */
    public function findAll(?int $limit = null, int $offset = 0)
    {
        $this->whereIn('jenis_pegawai', ['staff', 'penunjang']);
        return parent::findAll($limit, $offset);
    }

    /**
     * Override find
     */
    public function find($id = null)
    {
        $this->whereIn('jenis_pegawai', ['staff', 'penunjang']);
        return parent::find($id);
    }

    // =========================================================================
    // FITUR UTAMA: DATA RETRIEVAL DENGAN SCOPE
    // =========================================================================

    /**
     * Helper untuk filter Scope Unit (Kode Jenjang)
     */
    public function filterJenjang(?string $kode_jenjang)
    {
        // Jika user bukan GLOBAL/YAYASAN, paksa filter berdasarkan unit mereka
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $this->where('pegawai.kode_jenjang', $kode_jenjang);
        }
        return $this;
    }

    /**
     * Mengambil data karyawan dengan Join ke Tabel Jenjang (Unit).
     * Dilengkapi logika Scope Unit (Global vs Spesifik).
     */
    public function getKaryawanWithJenjang($id = null, ?string $kode_jenjang = null, ?string $search = null)
    {
        $builder = $this->select('pegawai.*, jenjang_sekolah.nama_jenjang as unit_sekolah');
        $builder->join('jenjang_sekolah', 'jenjang_sekolah.kode_jenjang = pegawai.kode_jenjang', 'left');
        
        // Filter Wajib Karyawan
        $builder->whereIn('pegawai.jenis_pegawai', ['staff', 'penunjang']);
        $builder->where('pegawai.deleted_at', null);
        
        // --- LOGIKA SCOPE ---
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('pegawai.kode_jenjang', $kode_jenjang);
        }
        
        if ($id) {
            return $builder->where('pegawai.id', $id)->first();
        }

        if ($search) {
            $builder->groupStart()
                    ->like('pegawai.nama_lengkap', $search)
                    ->orLike('pegawai.nip', $search)
                    ->orLike('pegawai.nik', $search)
                    ->orLike('pegawai.jenis_ptk', $search) // Update search field (was jabatan)
                    ->groupEnd();
        }

        return $builder->orderBy('pegawai.nama_lengkap', 'ASC')->findAll();
    }

    /**
     * Statistik: Menghitung total karyawan dengan status 'aktif'.
     */
    public function countAllKaryawanAktif(?string $kode_jenjang = null): int
    {
        $this->filterJenjang($kode_jenjang);
        
        return $this->whereIn('jenis_pegawai', ['staff', 'penunjang'])
                    ->where('status_aktif', 'aktif')
                    ->countAllResults();
    }

    /**
     * Agregat: Menghitung jumlah karyawan aktif per unit jenjang.
     */
    public function getKaryawanAktifGroupedByUnit(?string $kode_jenjang = null): array
    {
        $this->filterJenjang($kode_jenjang);

        return $this->select('kode_jenjang, COUNT(id) as total')
                    ->whereIn('jenis_pegawai', ['staff', 'penunjang'])
                    ->where('status_aktif', 'aktif')
                    ->groupBy('kode_jenjang')
                    ->orderBy('kode_jenjang', 'ASC')
                    ->findAll();
    }
}