<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

class JadwalPelajaranModel extends Model
{
    protected $table            = 'jadwal_pelajaran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang',
        'id_grup_siswa',     // Boleh Kosong (NULL) -> Artinya Satu Kelas Penuh
        'id_kelas',          // Wajib Diisi
        'id_mata_pelajaran',
        'id_guru',           // Boleh Kosong (NULL) -> Sinkron dgn Migrasi ON DELETE SET NULL
        'id_tahun_ajaran',
        'id_kurikulum',
        'id_ruangan',
        'hari',
        'jam_mulai',
        'jam_selesai'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'kode_jenjang'       => 'required|max_length[10]',
        'id_grup_siswa'      => 'permit_empty|is_natural_no_zero',
        'id_kelas'           => 'required|is_natural_no_zero',      
        'id_mata_pelajaran'  => 'required|is_natural_no_zero',
        
        // FIX: id_guru sekarang permit_empty mengikuti aturan migrasi
        'id_guru'            => 'permit_empty|is_natural_no_zero',
        
        'id_tahun_ajaran'    => 'required|is_natural_no_zero',
        'id_kurikulum'       => 'required|is_natural_no_zero',
        'id_ruangan'         => 'permit_empty|is_natural_no_zero',
        'hari'               => 'required|max_length[10]',
        'jam_mulai'          => 'required',
        'jam_selesai'        => 'required',
    ];

    protected $validationMessages = [
        'kode_jenjang' => [
            'required'   => 'Unit / Jenjang kerja wajib ditentukan.',
            'max_length' => 'Format kode jenjang tidak valid.'
        ],
        'id_kurikulum' => [
            'required'           => 'Kolom Kurikulum wajib diisi.',
            'is_natural_no_zero' => 'Kurikulum tidak valid.'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['checkJadwalConflict'];
    protected $beforeUpdate = ['checkJadwalConflict'];

    /**
     * Callback untuk mencegah konflik jadwal (guru, rombel/kelas, ruangan)
     */
    protected function checkJadwalConflict(array $data): array
    {
        if (!isset($data['data'])) {
            return $data;
        }

        $post = $data['data'];
        $id   = $data['id'][0] ?? null; 

        $idGrup     = $post['id_grup_siswa'] ?? null; 
        $idKelas    = $post['id_kelas'] ?? null;      
        $idGuru     = $post['id_guru'] ?? null;       // Bisa NULL sekarang
        $idRuangan  = $post['id_ruangan'] ?? null;
        $hari       = $post['hari'] ?? null;
        $jamMulai   = $post['jam_mulai'] ?? null;
        $jamSelesai = $post['jam_selesai'] ?? null;
        $idTahun    = $post['id_tahun_ajaran'] ?? null;

        // FIX LOGIKA: Hapus pengecekan !$idGuru di sini agar konflik kelas dan ruangan TETAP DIPROSES 
        // walau id_guru kosong (jadwal belum ada guru).
        if (!$idKelas || !$hari || !$jamMulai || !$jamSelesai || !$idTahun) {
            return $data;
        }

        $builder = $this->db->table($this->table)
            ->where('hari', $hari)
            ->where('id_tahun_ajaran', $idTahun)
            ->where('deleted_at', null)
            ->groupStart()
                // Logika overlap waktu
                ->where('jam_mulai <', $jamSelesai)
                ->where('jam_selesai >', $jamMulai)
            ->groupEnd();

        if ($id) {
            $builder->where('id !=', $id);
        }

        $existing = $builder->get()->getResultArray();

        foreach ($existing as $sched) {
            // 1. Cek Konflik Guru (Hanya cek jika idGuru diinputkan)
            if (!empty($idGuru) && !empty($sched['id_guru']) && $sched['id_guru'] == $idGuru) {
                throw new RuntimeException("Konflik Guru: Guru ini sudah memiliki jadwal mengajar pada jam tersebut.");
            }

            // 2. Cek Konflik Ruangan
            if (!empty($idRuangan) && !empty($sched['id_ruangan']) && $sched['id_ruangan'] == $idRuangan) {
                throw new RuntimeException("Konflik Ruangan: Ruangan ini sedang digunakan oleh kelas lain pada jam tersebut.");
            }

            // 3. Cek Konflik Kelas / Rombel
            if ($sched['id_kelas'] == $idKelas) {
                $existingGrup = $sched['id_grup_siswa'] ?? null;

                if (empty($idGrup)) {
                    throw new RuntimeException("Konflik Kelas: Kelas ini sudah memiliki jadwal pada jam tersebut.");
                }

                if (empty($existingGrup)) {
                    throw new RuntimeException("Konflik Kelas: Kelas ini sedang digunakan secara penuh (Full Class) pada jam tersebut.");
                }

                if ($idGrup == $existingGrup) {
                    throw new RuntimeException("Konflik Rombel: Kelompok siswa ini sudah memiliki jadwal pada jam tersebut.");
                }
            }
        }

        return $data;
    }

    public function scopeJenjang(string $kodeJenjang)
    {
        return $this->where($this->table . '.kode_jenjang', $kodeJenjang);
    }

    /**
     * Query dasar dengan join lengkap.
     */
    public function getJadwalBuilder()
    {
        return $this->db->table($this->table)
            ->select('
                jadwal_pelajaran.*,
                IFNULL(grup_siswa.nama_grup, "Satu Kelas Penuh") as nama_grup,
                kelas.nama_kelas,
                mata_pelajaran.nama_mapel,
                IFNULL(guru.nama_lengkap, "Guru Belum Ditentukan") as nama_guru,
                tahun_ajaran.tahun_ajaran,
                kurikulum.nama_kurikulum,
                IFNULL(sapras_ruangan.nama, "Ruangan Belum Diatur") as nama_ruangan
            ')
            ->join('grup_siswa', 'grup_siswa.id = jadwal_pelajaran.id_grup_siswa', 'left')
            ->join('kelas', 'kelas.id = jadwal_pelajaran.id_kelas', 'left')
            ->join('mata_pelajaran', 'mata_pelajaran.id = jadwal_pelajaran.id_mata_pelajaran', 'left')
            ->join('pegawai as guru', 'guru.id = jadwal_pelajaran.id_guru', 'left')
            ->join('tahun_ajaran', 'tahun_ajaran.id = jadwal_pelajaran.id_tahun_ajaran', 'left')
            ->join('kurikulum', 'kurikulum.id = jadwal_pelajaran.id_kurikulum', 'left')
            ->join('sapras_ruangan', 'sapras_ruangan.id = jadwal_pelajaran.id_ruangan', 'left')
            ->where('jadwal_pelajaran.deleted_at', null);
    }

    public function getJadwalDetail(?int $idGrup = null, ?string $hari = null, ?string $kodeJenjang = null): array
    {
        $builder = $this->getJadwalBuilder();

        if ($idGrup) {
            $builder->where('jadwal_pelajaran.id_grup_siswa', $idGrup);
        }
        if ($hari) {
            $builder->where('jadwal_pelajaran.hari', $hari);
        }
        if ($kodeJenjang) {
            $builder->where('jadwal_pelajaran.kode_jenjang', $kodeJenjang);
        }

        return $builder->orderBy('FIELD(jadwal_pelajaran.hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu")')
                       ->orderBy('jadwal_pelajaran.jam_mulai', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    public function getJadwalDetailByTa(int $idTahunAjaran, ?int $idGrup = null, ?string $hari = null, ?string $kodeJenjang = null): array
    {
        $builder = $this->getJadwalBuilder()
                        ->where('jadwal_pelajaran.id_tahun_ajaran', $idTahunAjaran);

        if ($idGrup) {
            $builder->where('jadwal_pelajaran.id_grup_siswa', $idGrup);
        }
        if ($hari) {
            $builder->where('jadwal_pelajaran.hari', $hari);
        }
        if ($kodeJenjang) {
            $builder->where('jadwal_pelajaran.kode_jenjang', $kodeJenjang);
        }

        return $builder->orderBy('FIELD(jadwal_pelajaran.hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu")')
                       ->orderBy('jadwal_pelajaran.jam_mulai', 'ASC')
                       ->get()
                       ->getResultArray();
    }

    public function getJadwalByGuruIdAndTa(int $guruId, int $idTahunAjaran): array
    {
        return $this->getJadwalBuilder()
                    ->where('jadwal_pelajaran.id_guru', $guruId)
                    ->where('jadwal_pelajaran.id_tahun_ajaran', $idTahunAjaran)
                    ->orderBy('FIELD(jadwal_pelajaran.hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu")')
                    ->orderBy('jadwal_pelajaran.jam_mulai', 'ASC')
                    ->get()
                    ->getResultArray();
    }

    public function getKelasDiajarByGuru(int $idGuru, int $idTahunAjaran): array
    {
        return $this->db->table($this->table)
            ->select('jadwal_pelajaran.id_grup_siswa as grup_id, grup_siswa.nama_grup, kelas.nama_kelas')
            ->join('grup_siswa', 'grup_siswa.id = jadwal_pelajaran.id_grup_siswa', 'left')
            ->join('kelas', 'kelas.id = jadwal_pelajaran.id_kelas', 'left')
            ->where('jadwal_pelajaran.id_guru', $idGuru)
            ->where('jadwal_pelajaran.id_tahun_ajaran', $idTahunAjaran)
            ->where('jadwal_pelajaran.deleted_at', null)
            ->distinct()
            ->orderBy('kelas.nama_kelas', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getJadwalByKelasGuruHari(int $idGrup, int $idGuru, string $hari, int $idTahunAjaran): array
    {
        return $this->db->table($this->table)
            ->select('
                jadwal_pelajaran.id,
                jadwal_pelajaran.id_mata_pelajaran as id_mapel,
                jadwal_pelajaran.id_kurikulum,
                jadwal_pelajaran.id_ruangan,
                grup_siswa.nama_grup,
                kelas.nama_kelas,
                mata_pelajaran.nama_mapel,
                sapras_ruangan.nama as nama_ruangan
            ')
            ->join('grup_siswa', 'grup_siswa.id = jadwal_pelajaran.id_grup_siswa', 'left')
            ->join('kelas', 'kelas.id = jadwal_pelajaran.id_kelas', 'left')
            ->join('mata_pelajaran', 'mata_pelajaran.id = jadwal_pelajaran.id_mata_pelajaran', 'left')
            ->join('sapras_ruangan', 'sapras_ruangan.id = jadwal_pelajaran.id_ruangan', 'left')
            ->where('jadwal_pelajaran.id_grup_siswa', $idGrup)
            ->where('jadwal_pelajaran.id_guru', $idGuru)
            ->where('jadwal_pelajaran.hari', $hari)
            ->where('jadwal_pelajaran.id_tahun_ajaran', $idTahunAjaran)
            ->where('jadwal_pelajaran.deleted_at', null)
            ->get()
            ->getResultArray();
    }
    
    public function getJadwalHarianKelas(int $idKelas, string $hari, int $idTahunAjaran): array
    {
        return $this->getJadwalBuilder()
            ->where('jadwal_pelajaran.id_kelas', $idKelas)
            ->where('jadwal_pelajaran.hari', $hari)
            ->where('jadwal_pelajaran.id_tahun_ajaran', $idTahunAjaran)
            ->orderBy('jadwal_pelajaran.jam_mulai', 'ASC')
            ->get()
            ->getResultArray();
    }
}