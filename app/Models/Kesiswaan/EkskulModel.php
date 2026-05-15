<?php

namespace App\Models\Kesiswaan;

use CodeIgniter\Model;

class EkskulModel extends Model
{
    // Konfigurasi Tabel Utama (Master Ekskul)
    protected $table            = 'kesiswaan_ekskul';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'kode_jenjang', 'guru_pembina_id', 'nama_ekskul', 
        'kategori', 'hari_latihan', 'jam_mulai', 'jam_selesai', 'deskripsi'
    ];
    protected $useTimestamps    = true;
    protected $deletedField     = 'deleted_at';

    // =========================================================================
    // 1. LOGIKA MASTER EKSKUL
    // =========================================================================
    
    public function getEkskul($filters = [], $sort = null)
    {
        $builder = $this->select('kesiswaan_ekskul.*, p.nama_lengkap as nama_pembina')
                        ->join('pegawai p', 'p.id = kesiswaan_ekskul.guru_pembina_id', 'left')
                        ->where('kesiswaan_ekskul.deleted_at', null);

        if (!empty($filters['kategori'])) {
            $builder->where('kesiswaan_ekskul.kategori', $filters['kategori']);
        }
        
        // Sorting sederhana
        if ($sort == 'name_asc') $builder->orderBy('nama_ekskul', 'ASC');
        else $builder->orderBy('kesiswaan_ekskul.id', 'DESC');

        return $builder->findAll();
    }

    public function saveEkskul($data)
    {
        return $this->save($data);
    }

    // =========================================================================
    // 2. LOGIKA ANGGOTA EKSKUL (Tabel: kesiswaan_ekskul_anggota)
    // =========================================================================

    public function getAllAnggota()
    {
        return $this->db->table('kesiswaan_ekskul_anggota')
            ->select('kesiswaan_ekskul_anggota.*, s.nama_lengkap as nama_siswa, s.nis, s.kode_jenjang, e.nama_ekskul')
            ->join('siswa s', 's.id = kesiswaan_ekskul_anggota.siswa_id')
            ->join('kesiswaan_ekskul e', 'e.id = kesiswaan_ekskul_anggota.ekskul_id')
            ->where('kesiswaan_ekskul_anggota.deleted_at', null)
            ->orderBy('kesiswaan_ekskul_anggota.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * METHOD BARU: Untuk memperbaiki error 'Call to undefined method'
     * Digunakan di Controller Siswa (profile siswa)
     */
    public function getKegiatanEkskulBySiswa($idSiswa)
    {
        return $this->db->table('kesiswaan_ekskul_anggota')
            ->select('
                kesiswaan_ekskul.nama_ekskul,
                kesiswaan_ekskul.hari_latihan,
                kesiswaan_ekskul.jam_mulai,
                kesiswaan_ekskul.jam_selesai,
                pegawai.nama_lengkap as nama_pembina,
                kesiswaan_ekskul_anggota.nilai_huruf,
                kesiswaan_ekskul_anggota.deskripsi_nilai
            ')
            ->join('kesiswaan_ekskul', 'kesiswaan_ekskul.id = kesiswaan_ekskul_anggota.ekskul_id')
            ->join('pegawai', 'pegawai.id = kesiswaan_ekskul.guru_pembina_id', 'left')
            ->where('kesiswaan_ekskul_anggota.siswa_id', $idSiswa)
            ->where('kesiswaan_ekskul_anggota.deleted_at', null)
            ->get()
            ->getResultArray();
    }

    public function saveAnggota($data)
    {
        $builder = $this->db->table('kesiswaan_ekskul_anggota');
        
        $saveData = [
            'ekskul_id'   => $data['ekskul_id'],
            'siswa_id'    => $data['siswa_id'],
            'tahun_ajar_id' => $data['tahun_ajar_id'] ?? 1,
            'nilai_huruf' => $data['nilai_huruf'] ?? null,
            'deskripsi_nilai' => $data['deskripsi_nilai'] ?? null,
            'updated_at'  => date('Y-m-d H:i:s')
        ];

        // Jika ada ID, lakukan Update
        if (!empty($data['id'])) {
            return $builder->where('id', $data['id'])->update($saveData);
        }
        
        // Jika Baru, Insert
        $saveData['created_at'] = date('Y-m-d H:i:s');
        return $builder->insert($saveData);
    }

    public function deleteAnggota($id)
    {
        // Soft Delete manual karena bukan tabel utama model ini
        return $this->db->table('kesiswaan_ekskul_anggota')
            ->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    // =========================================================================
    // 3. LOGIKA PRESENSI EKSKUL (Tabel: kesiswaan_ekskul_presensi)
    // =========================================================================

    public function getAllPresensi()
    {
        return $this->db->table('kesiswaan_ekskul_presensi')
            ->select('kesiswaan_ekskul_presensi.*, e.nama_ekskul, e.kode_jenjang')
            ->join('kesiswaan_ekskul e', 'e.id = kesiswaan_ekskul_presensi.ekskul_id')
            ->where('kesiswaan_ekskul_presensi.deleted_at', null)
            ->orderBy('kesiswaan_ekskul_presensi.tanggal', 'DESC')
            ->get()->getResultArray();
    }

    public function savePresensi($data)
    {
        $builder = $this->db->table('kesiswaan_ekskul_presensi');
        
        $saveData = [
            'ekskul_id'       => $data['ekskul_id'],
            'tanggal'         => $data['tanggal'],
            'materi_kegiatan' => $data['materi_kegiatan'],
            'data_presensi'   => $data['data_presensi'], // JSON String
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        if (!empty($data['id'])) {
            return $builder->where('id', $data['id'])->update($saveData);
        }

        $saveData['created_at'] = date('Y-m-d H:i:s');
        return $builder->insert($saveData);
    }

    public function deletePresensi($id)
    {
        return $this->db->table('kesiswaan_ekskul_presensi')
            ->where('id', $id)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }
}