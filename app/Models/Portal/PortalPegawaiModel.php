<?php

namespace App\Models\Portal;

use CodeIgniter\Model;

/**
 * PortalPegawaiModel (Enterprise Edition)
 * Menangani data untuk Guru dan Tenaga Kependidikan di halaman Portal.
 * STATUS: FINAL ROBUST V38 (Anti-Crash, Safe Joins, Full Features)
 */
class PortalPegawaiModel extends Model
{
    // --- Konfigurasi Tabel Utama ---
    protected $table            = 'pegawai';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    // --- Kolom yang Diizinkan ---
    protected $allowedFields = [
        'user_id',
        'kode_jenjang',      // Relasi Unit
        
        // Identitas Diri
        'nama_lengkap',
        'gelar_depan',
        'gelar_belakang',
        'nik',
        'nuptk',
        'nip',
        'nipy',
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

        // Kepegawaian & Struktural
        'status_kepegawaian',
        'id_jabatan',        // Relasi ke tabel 'jabatan'
        'jenis_ptk',         // Jabatan fungsional
        'tugas_tambahan',    // Tugas tambahan
        'sk_pengangkatan',
        'tmt_pengangkatan',
        'sumber_gaji',
        'pendidikan_terakhir',

        // Sistem
        'jenis_pegawai',     // 'guru', 'staff', 'penunjang'
        'status_aktif',      // 'aktif', 'nonaktif', dll
        'foto',
        'password'           // Password khusus portal
    ];

    // --- Timestamps ---
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // --- Callbacks (Security) ---
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    // =========================================================================
    // AUTH & DATA RETRIEVAL METHODS
    // =========================================================================

    /**
     * Pencarian Unified untuk Login (NIP / NIPY / Email)
     */
    public function getPegawaiForLogin($identifier)
    {
        return $this->groupStart()
                    ->where('nip', $identifier)
                    ->orWhere('nipy', $identifier)
                    ->orWhere('email', $identifier)
                    ->groupEnd()
                    ->where('deleted_at', null)
                    ->first();
    }

    /**
     * Mengambil data pegawai berdasarkan User ID
     */
    public function getPegawaiByUserId($userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    // =========================================================================
    // FITUR STRUKTURAL (JABATAN) - ANTI CRASH
    // =========================================================================

    /**
     * Mengambil Detail Jabatan Struktural Pegawai
     */
    public function getDetailJabatan($pegawaiId)
    {
        // Anti-Crash: Cek apakah tabel jabatan ada
        if (!$this->db->tableExists('jabatan')) return null;

        // Pastikan kolom id_jabatan ada di tabel pegawai sebelum join
        if (!$this->db->fieldExists('id_jabatan', 'pegawai')) return null;

        return $this->select('
                pegawai.id, 
                pegawai.nama_lengkap, 
                j.nama_jabatan, 
                j.level, 
                parent.nama_jabatan as nama_atasan
            ')
            ->join('jabatan j', 'j.id = pegawai.id_jabatan', 'left')
            ->join('jabatan parent', 'parent.id = j.atasan', 'left') 
            ->where('pegawai.id', $pegawaiId)
            ->first();
    }

    // =========================================================================
    // FITUR AKADEMIK (JADWAL & TAHUN AJARAN) - ANTI CRASH
    // =========================================================================

    /**
     * Mengambil Tahun Ajaran Aktif spesifik untuk Pegawai ini.
     */
    public function getTahunAjaranAktif($pegawaiId)
    {
        if (!$this->db->tableExists('tahun_ajaran')) return null;

        // 1. Ambil Jenjang Pegawai
        $pegawai = $this->select('kode_jenjang')->find($pegawaiId);
        $jenjang = $pegawai['kode_jenjang'] ?? 'GLOBAL';

        // 2. Query Tahun Ajaran Aktif berdasarkan Jenjang
        $ta = $this->db->table('tahun_ajaran')
                       ->where('status', 'aktif')
                       ->where('kode_jenjang', $jenjang)
                       ->get()
                       ->getRowArray();

        // 3. Fallback ke GLOBAL jika unit spesifik tidak ditemukan
        if (!$ta && $jenjang !== 'GLOBAL') {
            $ta = $this->db->table('tahun_ajaran')
                           ->where('status', 'aktif')
                           ->where('kode_jenjang', 'GLOBAL')
                           ->get()
                           ->getRowArray();
        }

        return $ta;
    }

    /**
     * Ambil Jadwal Mengajar (Khusus Guru)
     */
    public function getJadwalMengajar($guruId, $hari)
    {
        if (!$this->db->tableExists('jadwal_pelajaran')) return [];

        $builder = $this->db->table('jadwal_pelajaran jp')
            ->select('
                jp.id, 
                jp.jam_mulai, 
                jp.jam_selesai, 
                k.nama_kelas,
                mp.nama_mapel,
                ta.tahun_ajaran,
                ta.semester
            ');

        // Safe Join Grup Siswa (Rombel)
        if ($this->db->tableExists('grup_siswa')) {
            $builder->select('gs.nama_grup')->join('grup_siswa gs', 'gs.id = jp.id_grup_siswa', 'left');
        } else {
            $builder->select("'' as nama_grup");
        }

        $builder->join('kelas k', 'k.id = jp.id_kelas', 'left')
                ->join('tahun_ajaran ta', 'ta.id = jp.id_tahun_ajaran');
        
        // Safe Join Mapel
        if ($this->db->tableExists('mata_pelajaran')) {
            $builder->join('mata_pelajaran mp', 'mp.id = jp.id_mata_pelajaran', 'left');
        } else {
            $builder->select("'Mapel' as nama_mapel"); // Dummy mapel if table missing
        }

        // Safe Join Ruangan
        if ($this->db->tableExists('sapras_ruangan')) {
            $builder->select('sr.nama as ruangan')->join('sapras_ruangan sr', 'sr.id = jp.id_ruangan', 'left');
        } elseif ($this->db->tableExists('ruangan')) {
            $builder->select('r.nama_ruangan as ruangan')->join('ruangan r', 'r.id = jp.id_ruangan', 'left');
        } else {
            $builder->select("'' as ruangan");
        }

        // Conditions
        $builder->where('ta.status', 'aktif')
                ->where('jp.id_guru', $guruId)
                ->where('jp.hari', $hari)
                ->where('jp.deleted_at', null)
                ->orderBy('jp.jam_mulai', 'ASC');

        return $builder->get()->getResultArray();
    }

    // =========================================================================
    // FITUR OPERASIONAL (PRESENSI & PENGUMUMAN) - ANTI CRASH
    // =========================================================================

    public function getRingkasanPresensi($pegawaiId, $bulan, $tahun)
    {
        if (!$this->db->tableExists('presensi_pegawai')) {
            return ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
        }

        return $this->db->table('presensi_pegawai')
            ->select("
                SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as alpha
            ")
            ->where('id_pegawai', $pegawaiId)
            ->where('MONTH(tanggal)', $bulan)
            ->where('YEAR(tanggal)', $tahun)
            ->get()
            ->getRowArray();
    }
    
    public function getDetailPresensi($pegawaiId, $bulan, $tahun)
    {
        if (!$this->db->tableExists('presensi_pegawai')) return [];

        return $this->db->table('presensi_pegawai')
            ->where('id_pegawai', $pegawaiId)
            ->where('MONTH(tanggal)', $bulan)
            ->where('YEAR(tanggal)', $tahun)
            ->orderBy('tanggal', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPengumumanTerbaru($limit = 3)
    {
        if (!$this->db->tableExists('pengumuman')) return [];

        $builder = $this->db->table('pengumuman');

        // Safe Check Column 'target'
        if ($this->db->fieldExists('target', 'pengumuman')) {
            $builder->groupStart()
                    ->where('target', 'pegawai')
                    ->orWhere('target', 'guru')
                    ->orWhere('target', 'staff')
                    ->orWhere('target', 'semua')
                    ->groupEnd();
        }

        return $builder->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    // =========================================================================
    // CALLBACKS (Security)
    // =========================================================================

    protected function hashPassword(array $data): array
    {
        if (!isset($data['data']['password']) || empty($data['data']['password'])) {
            // Jika kosong, hapus agar tidak mengupdate password jadi kosong/null di DB
            unset($data['data']['password']);
            return $data;
        }

        $password = $data['data']['password'];

        // Cek apakah password sudah di-hash (mencegah double hashing)
        $info = password_get_info($password);
        if ($info['algo'] === 0) {
            // Belum di-hash, lakukan hashing
            $data['data']['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $data;
    }
}