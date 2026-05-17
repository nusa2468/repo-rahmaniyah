<?php

namespace App\Models\Sapras;

use CodeIgniter\Model;

class AsetPengadaanModel extends Model
{
    protected $table            = 'aset_pengadaan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; 

    protected $allowedFields = [
        'kode_jenjang', 
        'no_pengajuan', 
        'judul_pengajuan', 
        'id_kategori', 
        'jumlah_diminta', 
        'estimasi_biaya', 
        'alasan_kebutuhan', 
        'id_pemohon', 
        'status', 
        'catatan_reviewer'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'id'              => 'permit_empty|is_natural_no_zero', // <--- FIX ERROR PLACEHOLDER {id}
        'kode_jenjang'    => 'required|max_length[20]',
        'no_pengajuan'    => 'required|max_length[50]|is_unique[aset_pengadaan.no_pengajuan,id,{id}]',
        'judul_pengajuan' => 'required|max_length[255]',
        'id_kategori'     => 'required|integer',
        'jumlah_diminta'  => 'required|integer|greater_than[0]',
        'estimasi_biaya'  => 'permit_empty|numeric',
        'alasan_kebutuhan'=> 'required|string',
        'status'          => 'in_list[Draft,Menunggu Approval,Disetujui,Ditolak,Selesai/Dibeli]'
    ];

    public function getPengadaanBuilder(?string $kodeJenjang = null)
    {
        $builder = $this->db->table($this->table)
            ->select('
                aset_pengadaan.*, 
                aset_kategori.nama_kategori, 
                pegawai.nama_lengkap as nama_pemohon
            ')
            ->join('aset_kategori', 'aset_kategori.id = aset_pengadaan.id_kategori', 'left')
            ->join('pegawai', 'pegawai.id = aset_pengadaan.id_pemohon', 'left');

        if (!empty($kodeJenjang) && strtoupper($kodeJenjang) !== 'GLOBAL') {
            $builder->where('aset_pengadaan.kode_jenjang', $kodeJenjang);
        }

        return $builder;
    }
}