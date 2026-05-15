<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

/**
 * Model MataPelajaran (Enterprise Edition)
 * Mengelola data mata pelajaran, kelompok mapel, dan bobot penilaian.
 * Fitur: Scope Unit (Sinkron dengan Siswa/Kurikulum), Validasi Bobot 100%, & Join Details.
 */
class MataPelajaranModel extends Model
{
    protected $table            = 'mata_pelajaran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // FIX: Ubah menjadi TRUE agar sinkron dengan kolom deleted_at di migrasi
    protected $useSoftDeletes   = true; 
    
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'kurikulum_id',
        'kode_jenjang',
        'kode_mapel',
        'nama_mapel',
        'kelompok',
        'tingkat',  // Tingkat (1-12)
        'semester', // Kolom Baru: Semester (Ganjil/Genap)
        'status',
        'jumlah_jp',
        'bobot_tugas',
        'bobot_uts',
        'bobot_uas',
        'bobot_absensi', 
    ];

    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';
    
    // Callbacks untuk menjaga integritas perhitungan nilai
    protected $beforeInsert     = ['checkTotalBobot'];
    protected $beforeUpdate     = ['checkTotalBobot'];

    /**
     * Aturan Validasi
     */
    protected $validationRules = [
        'id'            => 'permit_empty',
        'kurikulum_id'  => 'required|integer', 
        'kode_jenjang'  => 'required|max_length[20]',
        'kode_mapel'    => 'required|max_length[50]|is_unique[mata_pelajaran.kode_mapel,id,{id}]',
        'nama_mapel'    => 'required|max_length[100]',
        'kelompok'      => 'required|in_list[A,B,C,Nasional,Peminatan,Muatan Lokal]', // Diperluas sesuai Seeder
        'tingkat'       => 'permit_empty|integer',
        'semester'      => 'permit_empty|in_list[Ganjil,Genap]', 
        'status'        => 'required|in_list[aktif,tidak aktif]',
        'jumlah_jp'     => 'required|integer|greater_than[0]',
        'bobot_tugas'   => 'required|numeric|less_than_equal_to[1.0]',
        'bobot_uts'     => 'required|numeric|less_than_equal_to[1.0]',
        'bobot_uas'     => 'required|numeric|less_than_equal_to[1.0]',
        'bobot_absensi' => 'required|numeric|less_than_equal_to[1.0]',
    ];

    protected $validationMessages = [
        'kurikulum_id' => [
            'required'      => 'Kurikulum wajib dipilih.',
            'is_not_unique' => 'Kurikulum yang dipilih tidak valid.'
        ],
        'kode_jenjang' => [
            'required'      => 'Unit jenjang sekolah wajib diisi.',
            'is_not_unique' => 'Unit jenjang tidak valid.'
        ],
        'kode_mapel' => [
            'required'  => 'Kode Mata Pelajaran wajib diisi.',
            'is_unique' => 'Kode Mata Pelajaran ini sudah terdaftar.'
        ],
        'nama_mapel' => [
            'required' => 'Nama Mata Pelajaran wajib diisi.'
        ],
        'jumlah_jp' => [
            'greater_than' => 'Jumlah Jam Pelajaran (JP) harus lebih dari 0.'
        ]
    ];
    
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // --- METOD KHUSUS NILAI (WAJIB ADA UNTUK CONTROLLER NILAI) ---

    /**
     * Mengambil konfigurasi bobot penilaian berdasarkan ID Mapel.
     */
    public function getBobotByMapelId($id)
    {
        return $this->select('bobot_tugas, bobot_uts, bobot_uas, bobot_absensi')
                      ->where('id', $id)
                      ->first();
    }

    /**
     * Mendapatkan daftar mapel aktif (Helper dropdown)
     */
    public function getActiveMapel()
    {
        return $this->where('status', 'aktif')
                      ->orderBy('kode_mapel', 'ASC')
                      ->findAll();
    }

    // --- FITUR UTAMA: DATA RETRIEVAL DENGAN SCOPE ---

    public function getMapelWithDetails($id = null, ?string $kode_jenjang = null, ?string $search = null)
    {
        $builder = $this->select('mata_pelajaran.*, kurikulum.nama_kurikulum, js.nama_jenjang as unit_sekolah');
        $builder->join('kurikulum', 'kurikulum.id = mata_pelajaran.kurikulum_id', 'left');
        $builder->join('jenjang_sekolah js', 'js.kode_jenjang = mata_pelajaran.kode_jenjang', 'left');

        // Scope filter Unit
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('mata_pelajaran.kode_jenjang', $kode_jenjang);
        }

        if ($id) {
            return $builder->where('mata_pelajaran.id', $id)->first();
        }

        if ($search) {
            $builder->groupStart()
                    ->like('mata_pelajaran.nama_mapel', $search)
                    ->orLike('mata_pelajaran.kode_mapel', $search)
                    ->groupEnd();
        }

        return $builder->orderBy('mata_pelajaran.kode_jenjang', 'ASC')
                       ->orderBy('mata_pelajaran.tingkat', 'ASC')
                       ->orderBy('mata_pelajaran.semester', 'ASC')
                       ->orderBy('mata_pelajaran.kelompok', 'ASC')
                       ->orderBy('mata_pelajaran.nama_mapel', 'ASC')
                       ->findAll();
    }

    protected function checkTotalBobot(array $data)
    {
        if (!isset($data['data']['bobot_tugas'], $data['data']['bobot_uts'], $data['data']['bobot_uas'], $data['data']['bobot_absensi'])) {
            return $data;
        }

        $tugas   = (float) $data['data']['bobot_tugas'];
        $uts     = (float) $data['data']['bobot_uts'];
        $uas     = (float) $data['data']['bobot_uas'];
        $absensi = (float) $data['data']['bobot_absensi'];
        
        $total = round($tugas + $uts + $uas + $absensi, 2);
        
        if ($total !== 1.00) {
            throw new RuntimeException('Total bobot nilai (Tugas + UTS + UAS + Absensi) harus berjumlah tepat 1.00 (100%). Total input saat ini: ' . number_format($total, 2));
        }

        return $data;
    }

    public function getJenjangByKurikulum(int $kurikulumId): ?string
    {
        $row = $this->db->table('kurikulum')
                         ->select('kode_jenjang')
                         ->where('id', $kurikulumId)
                         ->get()
                         ->getRow();
        return $row ? $row->kode_jenjang : null;
    }
}