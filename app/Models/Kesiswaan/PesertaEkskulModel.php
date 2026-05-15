<?php

namespace App\Models\Kesiswaan;

use CodeIgniter\Model;

/**
 * Model ini menangani tabel 'kesiswaan_ekskul_anggota'.
 * Digunakan untuk mencatat keanggotaan siswa dalam ekskul beserta nilainya.
 */
class PesertaEkskulModel extends Model
{
    // FIX: Nama tabel disesuaikan dengan migrasi CreateKesiswaanMasterData
    protected $table            = 'kesiswaan_ekskul_anggota';
    protected $primaryKey       = 'id';
    
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';
    
    protected $useSoftDeletes   = true;
    
    // FIX: Kolom disesuaikan dengan migrasi (ekskul_id, siswa_id, tahun_ajar_id)
    protected $allowedFields    = [
        'kode_jenjang',
        'ekskul_id',        // Sebelumnya id_ekskul
        'siswa_id',         // Sebelumnya id_siswa
        'tahun_ajar_id',    // Sebelumnya id_tahun_ajaran
        // 'jabatan',       // Kolom jabatan tidak ada di tabel ini (ada di organisasi), opsional bisa dihapus
        'nilai_huruf',      // Sebelumnya nilai_ekskul
        'deskripsi_nilai',  // Sebelumnya keterangan
    ];

    // Rules validasi disesuaikan dengan nama kolom baru
    protected $validationRules  = [
        'ekskul_id'     => 'required|integer',
        'siswa_id'      => 'required|integer',
        'tahun_ajar_id' => 'required|integer',
        'nilai_huruf'   => 'permit_empty|max_length[5]',
    ];
    
    protected $protectFields    = true;
    protected $dateFormat       = 'datetime';
    protected $cleanValidationRules = true;

    /**
     * Mengambil semua peserta dengan filter enrollment yang sinkron
     */
    public function getAllPesertaGrouped()
    {
        // FIX: Join menggunakan kolom baru (ekskul_id, siswa_id, tahun_ajar_id)
        $result = $this->db->table($this->table . ' pe')
            ->select('pe.ekskul_id, s.nis, s.nama_lengkap, k.nama_kelas')
            ->join('siswa s', 's.id = pe.siswa_id')
            // Join enrollment pakai tahun_ajar_id
            ->join('siswa_enrollment se', 'se.id_siswa = s.id AND se.id_tahun_ajaran = pe.tahun_ajar_id', 'left')
            ->join('kelas k', 'k.id = se.id_kelas', 'left')
            ->where('pe.deleted_at', null)
            ->orderBy('s.nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();

        $grouped = [];
        foreach ($result as $row) {
            $grouped[$row['ekskul_id']][] = $row;
        }

        return $grouped;
    }

    /**
     * Mengambil detail peserta per ekskul (Digunakan untuk Form Penilaian)
     */
    public function getPesertaDetailByEkskul($id_ekskul, $id_tahun_ajaran)
    {
        if(!$id_tahun_ajaran) return [];

        // FIX: Mapping kolom select agar kompatibel dengan view lama (aliasing)
        // nilai_huruf -> nilai_ekskul, deskripsi_nilai -> deskripsi_ekskul
        return $this->db->table($this->table)
            ->select('kesiswaan_ekskul_anggota.id, 
                      kesiswaan_ekskul_anggota.nilai_huruf as nilai_ekskul, 
                      kesiswaan_ekskul_anggota.deskripsi_nilai as deskripsi_ekskul, 
                      siswa.nis, siswa.nama_lengkap, kelas.nama_kelas')
            ->join('siswa', 'siswa.id = kesiswaan_ekskul_anggota.siswa_id')
            ->join('siswa_enrollment', 'siswa_enrollment.id_siswa = siswa.id AND siswa_enrollment.id_tahun_ajaran = ' . (int)$id_tahun_ajaran, 'left')
            ->join('kelas', 'kelas.id = siswa_enrollment.id_kelas', 'left')
            ->where('kesiswaan_ekskul_anggota.ekskul_id', $id_ekskul)
            ->where('kesiswaan_ekskul_anggota.tahun_ajar_id', $id_tahun_ajaran)
            ->where('kesiswaan_ekskul_anggota.deleted_at', null)
            ->orderBy('siswa.nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getPesertaIdsByEkskul($id_ekskul, $id_tahun_ajaran)
    {
        if(!$id_tahun_ajaran) return [];
        
        $result = $this->where('ekskul_id', $id_ekskul)
                       ->where('tahun_ajar_id', $id_tahun_ajaran)
                       ->findColumn('siswa_id');
        
        return $result ?? [];
    }
    
    /**
     * Mengambil daftar kegiatan ekskul berdasarkan siswa (Untuk Profil Siswa).
     */
    public function getKegiatanEkskulBySiswa(int $id_siswa): array
    {
        // FIX: Join ke tabel 'kesiswaan_ekskul' (bukan 'ekstrakurikuler' yang lama/salah)
        // FIX: Kolom join (ekskul_id, tahun_ajar_id)
        return $this->db->table($this->table . ' pe')
            ->select('e.nama_ekskul, pe.nilai_huruf as nilai_ekskul, pe.deskripsi_nilai as deskripsi_ekskul, ta.tahun_ajaran')
            ->join('kesiswaan_ekskul e', 'e.id = pe.ekskul_id')
            ->join('tahun_ajaran ta', 'ta.id = pe.tahun_ajar_id')
            ->where('pe.siswa_id', $id_siswa)
            ->where('pe.deleted_at', null)
            ->orderBy('ta.tahun_ajaran', 'DESC')
            ->get()
            ->getResultArray();
    }
}