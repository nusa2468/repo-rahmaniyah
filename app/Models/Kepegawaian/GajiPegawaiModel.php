<?php

namespace App\Models\Kepegawaian;

use CodeIgniter\Model;

/**
 * GajiPegawaiModel (Enterprise Edition)
 * Mengelola setting gaji individu pegawai (Mapping Pegawai -> Komponen Gaji).
 * Tabel: gaji_pegawai
 */
class GajiPegawaiModel extends Model
{
    protected $table            = 'gaji_pegawai';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // UPDATED: Mengaktifkan Soft Deletes karena kolom deleted_at sudah tersedia
    protected $useSoftDeletes   = true; 
    
    protected $protectFields    = true;

    protected $allowedFields    = [
        'id_pegawai', 
        'kode_jenjang', 
        'id_komponen', 
        'jumlah_set', 
        'is_active',
        'deleted_at' // Menambahkan field deleted_at agar bisa diakses jika perlu
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    // Aturan Validasi
    protected $validationRules = [
        'id_pegawai'   => 'required|integer',
        'kode_jenjang' => 'required|max_length[10]',
        'id_komponen'  => 'required|integer',
        'jumlah_set'   => 'required|numeric',
        'is_active'    => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'id_pegawai' => [
            'required' => 'Pegawai wajib dipilih.'
        ],
        'id_komponen' => [
            'required' => 'Komponen gaji wajib dipilih.'
        ]
    ];

    /**
     * Helper Scoping Data berdasarkan Unit Kerja User (Anti-Bocor).
     */
    protected function scopeData($builder)
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);

        if (!$isGlobal) {
            $builder->where('gaji_pegawai.kode_jenjang', $userJenjang);
        }
    }

    /**
     * Mengambil daftar komponen gaji yang di-set untuk pegawai tertentu.
     * Join ke tabel komponen_gaji dan pegawai.
     */
    public function getGajiByPegawai($id_pegawai)
    {
        $builder = $this->builder();
        $builder->select('
            gaji_pegawai.*,
            kg.nama_komponen,
            kg.tipe as tipe_komponen,
            kg.kode_komponen,
            p.nama_lengkap as nama_pegawai,
            p.nip as nip_pegawai,
            p.kode_jenjang as unit_pegawai
        ');

        $builder->join('komponen_gaji kg', 'kg.id = gaji_pegawai.id_komponen');
        $builder->join('pegawai p', 'p.id = gaji_pegawai.id_pegawai');
        
        // HAPUS JOIN riwayat_kepegawaian karena NIP sudah ada di tabel pegawai
        
        $this->scopeData($builder);
        
        $builder->where('gaji_pegawai.id_pegawai', $id_pegawai);
        $builder->where('gaji_pegawai.is_active', 1);
        
        // Pastikan hanya mengambil data yang belum dihapus (Soft Delete Check)
        $builder->where('gaji_pegawai.deleted_at', null);

        $result = $builder->get()->getResultArray();

        // Mapping tipe agar lebih mudah dibaca (1=Pendapatan, 2=Potongan)
        return array_map(function($item) {
            $item['jenis'] = ($item['tipe_komponen'] == 1) ? 'pendapatan' : 'potongan';
            return $item;
        }, $result);
    }

    /**
     * Menghitung total gaji berdasarkan tipe (Pendapatan/Potongan) untuk pegawai tertentu.
     */
    public function sumGajiByType($id_pegawai, $tipe = 'pendapatan')
    {
        // Konversi string ke ID Tipe sesuai database
        $tipeId = ($tipe === 'pendapatan') ? 1 : 2;

        $builder = $this->builder();
        $builder->selectSum('gaji_pegawai.jumlah_set');
        $builder->join('komponen_gaji kg', 'kg.id = gaji_pegawai.id_komponen');
        
        $this->scopeData($builder);

        $builder->where('gaji_pegawai.id_pegawai', $id_pegawai);
        $builder->where('gaji_pegawai.is_active', 1);
        $builder->where('gaji_pegawai.deleted_at', null); // Soft delete check
        $builder->where('kg.tipe', $tipeId);

        $row = $builder->get()->getRow();
        return (float) ($row->jumlah_set ?? 0);
    }
}