<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Kurikulum (Enterprise Edition)
 * Mengelola standar kurikulum pendidikan per unit.
 * Fitur: Scope Unit (Sinkron dengan Modul Pembelajaran), Status Eksklusif per Unit, & Soft Deletes.
 */
class KurikulumModel extends Model
{
    protected $table            = 'kurikulum';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Sinkronisasi Enterprise: Mengaktifkan Soft Deletes agar data tidak hilang permanen
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'kode_jenjang', 
        'kode_kurikulum',
        'nama_kurikulum', 
        'deskripsi', 
        'keterangan', 
        'status'
    ];

    // Otomatisasi Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Callbacks untuk Integritas Data
    protected $beforeInsert  = ['setExclusiveStatus'];
    protected $beforeUpdate  = ['setExclusiveStatus'];

    /**
     * Aturan Validasi Sinkron dengan Database
     */
    protected $validationRules = [
        'id'             => 'permit_empty|integer', 
        'kode_jenjang'   => 'required|max_length[20]',
        'kode_kurikulum' => 'required|max_length[20]|is_unique[kurikulum.kode_kurikulum,id,{id}]',
        'nama_kurikulum' => 'required|max_length[100]',
        'status'         => 'required|in_list[aktif,tidak aktif]', 
    ];

    protected $validationMessages = [
        'kode_kurikulum' => [
            'is_unique' => 'Kode kurikulum ini sudah terdaftar dalam sistem.'
        ],
        'kode_jenjang'   => [
            'required' => 'Unit jenjang sekolah wajib ditentukan.'
        ]
    ];

    /**
     * Scope Unit (kode_jenjang)
     * FIX LOGIKA: Menampilkan Kurikulum milik Unit tersebut DITAMBAH Kurikulum milik Badan Penyelenggara (Yayasan).
     */
    public function filterByUnit(?string $kode_jenjang)
    {
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), ['GLOBAL', 'YAYASAN', 'PUSAT'])) {
            $this->groupStart()
                 ->where('kurikulum.kode_jenjang', strtoupper($kode_jenjang))
                 ->orWhereIn('kurikulum.kode_jenjang', ['GLOBAL', 'YAYASAN', 'PUSAT']) // Tampilkan juga milik Yayasan
                 ->groupEnd();
        }
        return $this;
    }

    /**
     * Mengambil data kurikulum lengkap dengan informasi nama unit/jenjang.
     * @param string|null $kode_jenjang Filter unit
     */
    public function getKurikulumWithJenjang(?string $kode_jenjang = null)
    {
        // FIX TAMPILAN: Jika tidak ada di tabel jenjang_sekolah (karena milik GLOBAL/YAYASAN), berikan label yang sesuai
        $builder = $this->select('kurikulum.*, COALESCE(js.nama_jenjang, "Yayasan / Badan Penyelenggara") as unit_sekolah');
        $builder->join('jenjang_sekolah js', 'js.kode_jenjang = kurikulum.kode_jenjang', 'left');
        
        $this->filterByUnit($kode_jenjang);
        
        // Urutkan berdasarkan unit dan status aktif terlebih dahulu
        return $builder->orderBy('kurikulum.kode_jenjang', 'ASC')
                       ->orderBy('kurikulum.status', 'ASC') 
                       ->orderBy('kurikulum.nama_kurikulum', 'ASC')
                       ->findAll();
    }

    /**
     * Mendapatkan kurikulum yang sedang aktif untuk unit tertentu.
     * FIX LOGIKA: Fallback ke seluruh variasi kode Badan Penyelenggara
     */
    public function getAktifByUnit(string $kode_jenjang)
    {
        $kurikulum = $this->where('kode_jenjang', strtoupper($kode_jenjang))
                          ->where('status', 'aktif')
                          ->first();
                          
        // Fallback: Jika unit operasional (misal SMP) tidak punya kurikulum aktif, pakai standar Yayasan
        if (!$kurikulum) {
            $kurikulum = $this->whereIn('kode_jenjang', ['GLOBAL', 'YAYASAN', 'PUSAT'])
                              ->where('status', 'aktif')
                              ->first();
        }
        
        return $kurikulum;
    }

    /**
     * Business Logic Callback: 
     * Memastikan hanya ada SATU kurikulum berstatus 'aktif' untuk setiap Jenjang.
     * FIX: Menggunakan Query Builder langsung (Bypass Model Callback) dan mengecualikan ID saat ini.
     */
    protected function setExclusiveStatus(array $data): array
    {
        // Hanya proses jika data yang masuk menyetel status menjadi 'aktif'
        if (!isset($data['data']['status']) || $data['data']['status'] !== 'aktif') {
            return $data;
        }

        $currentId  = $data['id'][0] ?? null;
        $targetUnit = $data['data']['kode_jenjang'] ?? null;

        // Jika kode_jenjang tidak terkirim di payload (biasanya pada proses Update), ambil dari DB
        if (!$targetUnit && $currentId) {
            $dbRow = $this->db->table($this->table)->select('kode_jenjang')->where('id', $currentId)->get()->getRow();
            if ($dbRow) {
                $targetUnit = $dbRow->kode_jenjang;
            }
        }

        // Matikan status 'aktif' pada kurikulum lain di jenjang yang sama
        if ($targetUnit) {
            $builder = $this->db->table($this->table)
                                ->where('kode_jenjang', $targetUnit)
                                ->where('status', 'aktif')
                                ->where('deleted_at', null);
            
            // Kecualikan ID yang sedang diproses agar tidak termatikan oleh dirinya sendiri
            if ($currentId) {
                $builder->where('id !=', $currentId);
            }
            
            $builder->update([
                'status'     => 'tidak aktif', 
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $data;
    }
}