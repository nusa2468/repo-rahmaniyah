<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Organisasi (Synchronized Enterprise Version)
 * Mengelola struktur hirarki jabatan dan penempatan personel.
 * Integrasi: Tabel 'pegawai' (Unified) + Fitur Manual Override.
 */
class OrganisasiModel extends Model
{
    protected $table            = 'organisasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // FIX: Diubah ke object agar kompatibel dengan View ($o->urutan)
    protected $useSoftDeletes   = true;
    
    protected $allowedFields = [
        'jenis_organisasi', 
        'kode_jenjang',     
        'parent_id',        
        'id_pegawai',       
        'nama_jabatan',     
        'jabatan_id',       
        'nama_pengampu',    
        'nip',              
        'urutan', 
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Mendapatkan data organisasi lengkap dengan teknik JOIN ke Pegawai dan Jabatan.
     */
    public function getFullOrganisasi(?string $kode_jenjang = null)
    {
        // FIX: Menghapus komentar SQL (-- ...) di dalam string select untuk mencegah error database driver
        $builder = $this->select('
            organisasi.*, 
            pegawai.nama_lengkap as nama_pegawai_db, 
            pegawai.foto, 
            pegawai.nip as nip_pegawai_db, 
            pegawai.jenis_ptk as jabatan_pegawai_db,
            jabatan.level as level_jabatan,
            COALESCE(pegawai.nama_lengkap, organisasi.nama_pengampu) AS nama_display,
            COALESCE(pegawai.nip, organisasi.nip) AS nip_display,
            COALESCE(jabatan.nama_jabatan, organisasi.nama_jabatan) AS nama_jabatan
        ');
        
        // JOIN ke tabel PEGAWAI
        $builder->join('pegawai', 'pegawai.id = organisasi.id_pegawai', 'left');
        
        // JOIN ke tabel JABATAN (Untuk mengambil level dan nama baku)
        $builder->join('jabatan', 'jabatan.id = organisasi.jabatan_id', 'left');
        
        // Filter agar data pegawai yang tampil hanya yang aktif (opsional)
        $builder->groupStart()
                ->where('pegawai.status_aktif', 'aktif')
                ->orWhere('organisasi.id_pegawai', null) 
                ->orWhere('organisasi.id_pegawai', 0)
                ->groupEnd();

        // Scope Protection
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $builder->where('organisasi.kode_jenjang', $kode_jenjang);
        }

        // Sorting
        return $builder->orderBy("CASE WHEN organisasi.kode_jenjang = 'Global' THEN 0 ELSE 1 END", 'ASC')
                       ->orderBy('organisasi.kode_jenjang', 'ASC')
                       ->orderBy('organisasi.urutan', 'ASC')
                       ->findAll();
    }

    /**
     * Mengambil struktur organisasi khusus untuk Yayasan/Pusat
     */
    public function getOrganisasiYayasan()
    {
        return $this->getFullOrganisasi('Global');
    }

    /**
     * Mengambil struktur organisasi khusus untuk unit sekolah tertentu
     */
    public function getOrganisasiUnit($kode_jenjang)
    {
        $this->where('jenis_organisasi', 'Sekolah'); 
        return $this->getFullOrganisasi($kode_jenjang);
    }

    /**
     * Mendapatkan satu baris data organisasi secara detail.
     */
    public function getOrganisasiDetail(int $id)
    {
        $builder = $this->select('
            organisasi.*, 
            pegawai.nama_lengkap as nama_pegawai_db,
            pegawai.foto, 
            pegawai.nip as nip_pegawai_db,
            pegawai.jenis_ptk as jabatan_pegawai_db,
            jabatan.level as level_jabatan,
            COALESCE(pegawai.nama_lengkap, organisasi.nama_pengampu) AS nama_display,
            COALESCE(pegawai.nip, organisasi.nip) AS nip_display,
            COALESCE(jabatan.nama_jabatan, organisasi.nama_jabatan) AS nama_jabatan
        ');

        $builder->join('pegawai', 'pegawai.id = organisasi.id_pegawai', 'left');
        $builder->join('jabatan', 'jabatan.id = organisasi.jabatan_id', 'left');
        $builder->where('organisasi.id', $id);

        return $builder->first();
    }
}