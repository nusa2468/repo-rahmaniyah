<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk tabel relasi 1-ke-1 'siswa_demografi'.
 * Mengelola data profil mendalam siswa termasuk informasi orang tua dasar.
 */
class SiswaDemografiModel extends Model
{
    protected $table            = 'siswa_demografi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true; 
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    /**
     * Kolom-kolom yang diperbolehkan untuk diisi atau diperbarui.
     * Menambahkan kolom Ayah dan Ibu untuk mendukung sinkronisasi data profil.
     */
    protected $allowedFields = [
        'id_siswa', // Foreign Key ke tabel siswa (WAJIB)
        'nama_panggilan', 
        'tempat_lahir', 
        'tanggal_lahir', 
        'agama', 
        'kewarganegaraan', 
        'no_akta_lahir', 
        'status_anak', 
        'alamat', 
        'rt', 
        'rw', 
        'dusun', 
        'kelurahan', 
        'kecamatan', 
        'kode_pos', 
        'lintang', 
        'bujur', 
        'telepon', 
        'email_pribadi', 
        'no_kk', 
        
        // --- DATA ORANG TUA (TAMBAHAN) ---
        'nama_ayah',
        'nik_ayah',
        'nama_ibu',
        'nik_ibu',
        // ---------------------------------

        'jenis_pendaftaran', 
        'asal_sekolah', 
        'no_seri_ijazah', 
        'nomor_ijazah', 
        'tanggal_lulus',
        'alasan_keluar', 
        'penerimaan_kps', 
        'no_kps', 
        'penerimaan_kip', 
        'no_kip', 
        'penerimaan_kks', 
        'no_kks'
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Mendapatkan data demografi berdasarkan ID Siswa
     */
    public function getBySiswaId(int $idSiswa)
    {
        return $this->where('id_siswa', $idSiswa)->first();
    }
}