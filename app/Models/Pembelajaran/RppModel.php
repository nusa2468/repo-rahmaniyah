<?php

namespace App\Models\Pembelajaran;

use CodeIgniter\Model;

class RppModel extends Model
{
    protected $table            = 'pembelajaran_rpp';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    // Pastikan semua field ini ada agar tidak difilter kosong saat update
    protected $allowedFields = [
        'kode_jenjang', 
        'silabus_id', 
        'guru_id', 
        'tingkat_kelas', 
        'semester', 
        'tahun_ajaran',
        'jenis_kurikulum', 
        'created_by',
        'status', 
        
        // Header
        'pertemuan_ke', 
        'alokasi_waktu', 
        'topik', 
        
        // Merdeka
        'fase', 
        'pemahaman_bermakna', 
        'pertanyaan_pemantik',
        'profil_pelajar_pancasila', 

        // K13
        'tema',
        'subtema',
        
        // Konten
        'tujuan_pembelajaran',
        'metode_pembelajaran', 
        'langkah_pembelajaran', 
        'media_alat', 
        'penilaian'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Filter data RPP secara komprehensif.
     */
    public function getFilteredData($keyword = null, $kodeJenjang = null, $jenisKurikulum = null)
    {
        $this->select('
            pembelajaran_rpp.*, 
            pembelajaran_silabus.materi_pokok as silabus_materi,
            mata_pelajaran.nama_mapel,
            mata_pelajaran.kode_mapel
        ');

        $this->join('pembelajaran_silabus', 'pembelajaran_silabus.id = pembelajaran_rpp.silabus_id', 'left');
        $this->join('mata_pelajaran', 'mata_pelajaran.id = pembelajaran_silabus.mata_pelajaran_id', 'left');

        if ($kodeJenjang) {
            $this->where('pembelajaran_rpp.kode_jenjang', $kodeJenjang);
        }

        if ($jenisKurikulum) {
            $this->where('pembelajaran_rpp.jenis_kurikulum', $jenisKurikulum);
        }

        if ($keyword) {
            $this->groupStart()
                ->like('pembelajaran_rpp.topik', $keyword)
                ->orLike('pembelajaran_rpp.tujuan_pembelajaran', $keyword)
                ->orLike('mata_pelajaran.nama_mapel', $keyword)
            ->groupEnd();
        }

        return $this;
    }

    public function getStatistics($kodeJenjang = null)
    {
        $builder = $this->db->table($this->table)->where('deleted_at', null);
        if ($kodeJenjang) {
            $builder->where('kode_jenjang', $kodeJenjang);
        }
        return [
            'total'   => (clone $builder)->countAllResults(),
            'merdeka' => (clone $builder)->where('jenis_kurikulum', 'Merdeka')->countAllResults(),
            'k13'     => (clone $builder)->where('jenis_kurikulum', 'K13')->countAllResults(),
        ];
    }
}