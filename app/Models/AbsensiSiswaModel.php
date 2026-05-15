<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiSiswaModel extends Model
{
    protected $table            = 'absensi_siswa';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'kode_jenjang',
        'id_jadwal',
        'id_siswa',
        'tanggal',
        'status',
        'keterangan',
    ];

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // FIX LOGIKA: Callback mutator untuk mencegah Error ENUM MySQL
    protected $beforeInsert = ['normalizeStatus'];
    protected $beforeUpdate = ['normalizeStatus'];

    protected $validationRules = [
        'kode_jenjang' => 'required|max_length[20]',
        'id_jadwal'    => 'required|integer',
        'id_siswa'     => 'required|integer',
        'tanggal'      => 'required|valid_date',
        // Validasi tetap longgar untuk mempermudah API/Import Excel, 
        // tapi nanti akan dinormalkan oleh callback normalizeStatus
        'status'       => 'required|in_list[hadir,sakit,izin,alpa,Hadir,H,Masuk,Present,Sakit,S,Izin,I,Alpa,A]',
        'keterangan'   => 'permit_empty|max_length[255]',
    ];

    /**
     * [FIX BUG ENUM DB]
     * Mutator untuk mengubah variasi input status menjadi standar baku ENUM DB.
     */
    protected function normalizeStatus(array $data): array
    {
        if (isset($data['data']['status'])) {
            $statusInput = strtolower(trim($data['data']['status']));
            
            $mapHadir = ['hadir', 'h', 'masuk', 'present'];
            $mapSakit = ['sakit', 's'];
            $mapIzin  = ['izin', 'i', 'ijin'];
            $mapAlpa  = ['alpa', 'a', 'alpha', 'absen'];

            if (in_array($statusInput, $mapHadir)) {
                $data['data']['status'] = 'hadir';
            } elseif (in_array($statusInput, $mapSakit)) {
                $data['data']['status'] = 'sakit';
            } elseif (in_array($statusInput, $mapIzin)) {
                $data['data']['status'] = 'izin';
            } elseif (in_array($statusInput, $mapAlpa)) {
                $data['data']['status'] = 'alpa';
            } else {
                $data['data']['status'] = 'hadir'; // Fallback aman
            }
        }
        return $data;
    }

    /**
     * Scope: Filter berdasarkan unit/jenjang menggunakan kolom denormalisasi
     */
    public function scopeJenjang(string $kodeJenjang)
    {
        return $this->where($this->table . '.kode_jenjang', strtoupper($kodeJenjang));
    }

    /**
     * Query utama untuk riwayat absensi (return Builder untuk pagination)
     */
    public function getAbsensiLengkap(string $kodeJenjang = null, ?string $tanggal = null, ?int $idGrup = null)
    {
        $builder = $this->select('
                absensi_siswa.*,
                siswa.nama_lengkap as nama_siswa,
                siswa.nis,
                jadwal_pelajaran.hari,
                jadwal_pelajaran.jam_mulai,
                mata_pelajaran.nama_mapel,
                IFNULL(grup_siswa.nama_grup, "Kelas Utuh") as nama_grup,
                kelas.nama_kelas,
                kelas.id as id_kelas
            ')
            ->join('siswa', 'siswa.id = absensi_siswa.id_siswa', 'left')
            ->join('jadwal_pelajaran', 'jadwal_pelajaran.id = absensi_siswa.id_jadwal', 'left')
            ->join('mata_pelajaran', 'mata_pelajaran.id = jadwal_pelajaran.id_mata_pelajaran', 'left')
            ->join('grup_siswa', 'grup_siswa.id = jadwal_pelajaran.id_grup_siswa', 'left')
            ->join('kelas', 'kelas.id = jadwal_pelajaran.id_kelas', 'left')
            ->where('absensi_siswa.deleted_at', null);

        // Scope unit (Kecuali ALL, GLOBAL, YAYASAN)
        if ($kodeJenjang && !in_array(strtoupper($kodeJenjang), ['ALL', 'GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('absensi_siswa.kode_jenjang', strtoupper($kodeJenjang));
        }

        if ($tanggal) {
            $builder->where('absensi_siswa.tanggal', $tanggal);
        }

        if ($idGrup) {
            $builder->where('jadwal_pelajaran.id_grup_siswa', $idGrup);
        }

        return $builder->orderBy('absensi_siswa.tanggal', 'DESC')
                       ->orderBy('jadwal_pelajaran.jam_mulai', 'ASC');
    }

    public function getAbsensiByJadwalAndDate(int $idJadwal, string $tanggal): array
    {
        return $this->select('absensi_siswa.*, siswa.nama_lengkap as nama_siswa')
                    ->join('siswa', 'siswa.id = absensi_siswa.id_siswa', 'left')
                    ->where('absensi_siswa.id_jadwal', $idJadwal)
                    ->where('absensi_siswa.tanggal', $tanggal)
                    ->orderBy('siswa.nama_lengkap', 'ASC') // FIX UI: Urutkan nama siswa abjad
                    ->findAll();
    }

    public function getAttendanceReportByGrup(int $idGrup, int $idTahunAjaran): array
    {
        return $this->select('
                absensi_siswa.id_siswa,
                siswa.nama_lengkap as nama_siswa,
                COUNT(*) as total_pertemuan,
                SUM(CASE WHEN absensi_siswa.status = "hadir" THEN 1 ELSE 0 END) as total_hadir,
                SUM(CASE WHEN absensi_siswa.status = "sakit" THEN 1 ELSE 0 END) as total_sakit,
                SUM(CASE WHEN absensi_siswa.status = "izin"  THEN 1 ELSE 0 END) as total_izin,
                SUM(CASE WHEN absensi_siswa.status = "alpa"  THEN 1 ELSE 0 END) as total_alpa
            ')
            ->join('siswa', 'siswa.id = absensi_siswa.id_siswa', 'left')
            ->join('jadwal_pelajaran', 'jadwal_pelajaran.id = absensi_siswa.id_jadwal', 'left')
            ->where('jadwal_pelajaran.id_grup_siswa', $idGrup)
            ->where('jadwal_pelajaran.id_tahun_ajaran', $idTahunAjaran)
            ->where('absensi_siswa.deleted_at', null)
            ->groupBy('absensi_siswa.id_siswa')
            ->orderBy('siswa.nama_lengkap', 'ASC')
            ->findAll();
    }

    /**
     * COMPATIBILITY METHOD: Menghitung skor kehadiran untuk modul Nilai.
     * UPDATE: Penulisan query COUNT DISTINCT yang lebih aman di CI4
     */
    public function getAttendanceScoreByClassMapelSemester($id_kelas, $id_mapel, $semester)
    {
        // 1. Hitung Kehadiran Siswa
        $builder = $this->db->table($this->table)
            ->select('absensi_siswa.id_siswa, COUNT(absensi_siswa.id) as jumlah_hadir')
            ->join('jadwal_pelajaran', 'jadwal_pelajaran.id = absensi_siswa.id_jadwal')
            ->where('jadwal_pelajaran.id_kelas', $id_kelas)
            ->where('jadwal_pelajaran.id_mata_pelajaran', $id_mapel)
            ->where('absensi_siswa.status', 'hadir') // Cukup "hadir" karena sudah dinormalkan
            ->where('absensi_siswa.deleted_at', null);
            
        $rows = $builder->groupBy('absensi_siswa.id_siswa')->get()->getResultArray();
        
        // 2. Hitung Total Pertemuan (Denominator) berdasarkan Tanggal & Jadwal Unik
        // FIX: Menghitung jumlah kombinasi jadwal dan tanggal yang valid saja
        $builderTotal = $this->db->table($this->table)
            ->join('jadwal_pelajaran', 'jadwal_pelajaran.id = absensi_siswa.id_jadwal')
            ->where('jadwal_pelajaran.id_kelas', $id_kelas)
            ->where('jadwal_pelajaran.id_mata_pelajaran', $id_mapel)
            ->where('absensi_siswa.deleted_at', null)
            ->groupBy('absensi_siswa.tanggal, absensi_siswa.id_jadwal'); // Group by tgl & jadwal
            
        $totalPertemuan = $builderTotal->countAllResults();

        if ($totalPertemuan <= 0) return [];

        $scores = [];
        foreach ($rows as $row) {
            $score = ($row['jumlah_hadir'] / $totalPertemuan) * 100;
            $scores[$row['id_siswa']] = round(max(0, min(100, $score)));
        }
        
        return $scores;
    }
}