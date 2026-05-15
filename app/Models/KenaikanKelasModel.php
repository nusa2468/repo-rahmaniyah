<?php

namespace App\Models;

use CodeIgniter\Model;

class KenaikanKelasModel extends Model
{
    protected $table            = 'kenaikan_kelas'; 
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'id_siswa', 'id_enrollment_lama', 'id_enrollment_baru', 
        'status_kenaikan', 'tanggal_keputusan', 'catatan_guru', 'id_operator'
    ];

    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    /**
     * Membangun Query Riwayat Kenaikan.
     * Mengembalikan $this (Query Builder) agar bisa digunakan untuk paginate() di Controller.
     */
    public function getKenaikanPaginated(string $jenjang, int $perPage = 20, ?string $keyword = null, ?int $tahunLamaId = null)
    {
        // Gunakan $this untuk select dan join agar tersambung ke paginate()
        $this->select('
            kenaikan_kelas.*, 
            s.nama_lengkap as nama_siswa, 
            s.nis, 
            kl.nama_kelas as kelas_asal, 
            ta_l.tahun_ajaran as ta_asal,
            kb.nama_kelas as kelas_tujuan,
            ta_b.tahun_ajaran as ta_tujuan
        ');
        
        $this->join('siswa s', 's.id = kenaikan_kelas.id_siswa');
        $this->join('siswa_enrollment se_l', 'se_l.id = kenaikan_kelas.id_enrollment_lama');
        $this->join('kelas kl', 'kl.id = se_l.id_kelas');
        $this->join('tahun_ajaran ta_l', 'ta_l.id = se_l.id_tahun_ajaran');

        // Join opsional (Left Join) untuk pendaftaran baru
        $this->join('siswa_enrollment se_b', 'se_b.id = kenaikan_kelas.id_enrollment_baru', 'left');
        $this->join('kelas kb', 'kb.id = se_b.id_kelas', 'left');
        $this->join('tahun_ajaran ta_b', 'ta_b.id = se_b.id_tahun_ajaran', 'left');

        // 1. Filter Scope Unit
        if (!empty($jenjang) && strtoupper($jenjang) !== 'GLOBAL') {
            $this->where('kl.kode_jenjang', $jenjang);
        }

        // 2. Filter Tahun Ajaran
        if ($tahunLamaId) {
            $this->where('se_l.id_tahun_ajaran', $tahunLamaId);
        }

        // 3. Pencarian Keyword
        if ($keyword) {
            $this->groupStart()
                ->like('s.nama_lengkap', $keyword)
                ->orLike('s.nis', $keyword)
                ->orLike('kl.nama_kelas', $keyword)
            ->groupEnd();
        }

        // Kembalikan instance model untuk chaining
        return $this->orderBy('kenaikan_kelas.created_at', 'DESC');
    }

    /**
     * Cek status proses siswa.
     */
    public function checkProcessed(int $idSiswa, int $idTahunLama)
    {
        // Gunakan instance DB baru agar tidak mengganggu state builder utama
        return $this->db->table($this->table)
            ->select('kenaikan_kelas.*')
            ->join('siswa_enrollment', 'siswa_enrollment.id = kenaikan_kelas.id_enrollment_lama')
            ->where('kenaikan_kelas.id_siswa', $idSiswa)
            ->where('siswa_enrollment.id_tahun_ajaran', $idTahunLama)
            ->where('kenaikan_kelas.deleted_at', null)
            ->get()->getRowArray() ?? [];
    }
}