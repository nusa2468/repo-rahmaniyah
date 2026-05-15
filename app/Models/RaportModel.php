<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\SettingsModel; // Import Model Settings

class RaportModel extends Model
{
    protected $table            = 'raport';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    // Menyesuaikan Allowed Fields dengan standar Dapodik
    // Catatan: Pastikan kolom-kolom baru ini juga ditambahkan di database Anda
    protected $allowedFields    = [
        'id_enrollment',
        'semester',
        
        // Nilai Akademik
        'rata_rata',
        
        // Absensi (Standar Dapodik)
        'total_sakit',
        'total_izin',
        'total_alpa',
        
        // Catatan
        'catatan_wali_kelas',
        'catatan_akademik',
        'catatan_karakter', // Bisa digunakan untuk catatan P5 (Projek Penguatan Profil Pelajar Pancasila)
        
        // Sikap Spiritual (KI-1) & Sosial (KI-2) - Standar K13/Merdeka
        'predikat_spiritual',   // SB/B/C/K
        'deskripsi_spiritual',
        'predikat_sosial',      // SB/B/C/K
        'deskripsi_sosial',
        
        // Data Periodik Kesehatan (Update per semester)
        'tinggi_badan',
        'berat_badan',
        
        // Keputusan (Khusus Semester Genap)
        'status_kenaikan', // Naik Kelas / Tinggal Kelas / Lulus
        
        // Meta Data
        'status_raport',
        'tanggal_cetak',
        'is_locked'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil data rapor dengan pagination, filter unit, pencarian, dan tahun ajaran.
     */
    public function getRaportPaginated(string $jenjang, int $perPage = 20, ?string $keyword = null, ?int $tahunId = null)
    {
        // Select fields yang relevan untuk list view
        // FIX: Tambahkan s.id as id_siswa
        $this->select('raport.*, 
                       s.id as id_siswa,
                       s.nama_lengkap as nama_siswa, 
                       s.nis, 
                       s.nisn,
                       k.nama_kelas, 
                       ta.tahun_ajaran');

        // Joins
        // Raport -> Enrollment
        $this->join('siswa_enrollment as se', 'se.id = raport.id_enrollment');
        // Enrollment -> Siswa
        $this->join('siswa as s', 's.id = se.id_siswa');
        // Enrollment -> Kelas
        $this->join('kelas as k', 'k.id = se.id_kelas');
        // Enrollment -> Tahun Ajaran
        $this->join('tahun_ajaran as ta', 'ta.id = se.id_tahun_ajaran', 'left');

        // Filter Jenjang
        if ($jenjang !== 'Global') {
            $this->where('k.kode_jenjang', $jenjang);
        }

        // Filter Tahun Ajaran (Filter pada tabel enrollment)
        if ($tahunId) {
            $this->where('se.id_tahun_ajaran', $tahunId);
        }

        // Filter Pencarian (Nama, NIS, atau NISN)
        if ($keyword) {
            $this->groupStart();
                $this->like('s.nama_lengkap', $keyword);
                $this->orLike('s.nis', $keyword);
                $this->orLike('s.nisn', $keyword); // Tambahan pencarian NISN
                $this->orLike('k.nama_kelas', $keyword);
            $this->groupEnd();
        }

        $this->orderBy('raport.updated_at', 'DESC');
        $this->orderBy('k.nama_kelas', 'ASC');

        return $this->paginate($perPage);
    }

    /**
     * Mengambil satu data rapor lengkap dengan detail siswa & kelas.
     * UPDATED: Otomatis mengambil Identitas Sekolah (Kop Surat) berdasarkan Unit.
     */
    public function getRaportDetail(int $id)
    {
        // 1. Ambil Data Dasar Rapor
        $data = $this->select('raport.*, 
                              s.nama_lengkap as nama_siswa, 
                              s.nis, 
                              s.nisn,  
                              s.tempat_lahir,
                              s.tanggal_lahir,
                              k.nama_kelas, 
                              k.tingkat,
                              k.kode_jenjang,
                              ta.tahun_ajaran,
                              se.id_siswa,
                              se.id_kelas,
                              se.id_tahun_ajaran')
                    ->join('siswa_enrollment as se', 'se.id = raport.id_enrollment')
                    ->join('siswa as s', 's.id = se.id_siswa')
                    ->join('kelas as k', 'k.id = se.id_kelas')
                    ->join('tahun_ajaran as ta', 'ta.id = se.id_tahun_ajaran')
                    ->where('raport.id', $id)
                    ->first();

        // 2. Inject Data Identitas Sekolah (Jika data rapor ditemukan)
        if ($data) {
            $unit = $data['kode_jenjang'] ?? 'Global';
            
            // Inisialisasi SettingsModel untuk mengambil data sekolah
            $settingsModel = new SettingsModel();
            
            // Ambil setting spesifik unit (SD/SMP/SMA)
            $identitasSekolah = $settingsModel->getSettingsAsArray($unit);
            
            // Jika kosong (belum disetting per unit), fallback ke Global
            if (empty($identitasSekolah)) {
                $identitasSekolah = $settingsModel->getSettingsAsArray('Global');
            }

            // Lampirkan ke dalam array hasil
            $data['sekolah'] = $identitasSekolah;
        }

        return $data;
    }
}