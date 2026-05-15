<?php

namespace App\Models\Cms;

use CodeIgniter\Model;

class AlbumFotoModel extends Model
{
    protected $table            = 'album_foto';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    
    // Mengaktifkan Soft Deletes sesuai migrasi (deleted_at)
    protected $useSoftDeletes   = true;

    // Disesuaikan dengan field di Migration CreateCmsTables
    // Perubahan: 'keterangan' -> 'deskripsi', 'gambar' -> 'cover'
    protected $allowedFields    = [
        'kode_jenjang', 'judul', 'slug', 
        'deskripsi', 'cover', 'status',
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at'; // Sekarang ada updated_at di migrasi
    protected $deletedField  = 'deleted_at';

    /**
     * Mengambil album foto berdasarkan jenjang
     * Hanya menampilkan album dengan status 'publik'
     */
    public function getAlbumsByJenjang($jenjang = null, $limit = 6)
    {
        $builder = $this->where('status', 'publik');

        // Logic Filter Scope Jenjang
        if ($jenjang && $jenjang !== 'Global') {
            $builder->groupStart()
                    ->where('kode_jenjang', $jenjang)
                    ->orWhere('kode_jenjang', null) // Null = Global
                    ->orWhere('kode_jenjang', 'Global')
                    ->groupEnd();
        }

        return $builder->orderBy('created_at', 'DESC')
                       ->findAll($limit);
    }

    /**
     * Mendapatkan detail album beserta foto-fotonya
     */
    public function getAlbumWithPhotos($slug)
    {
        $album = $this->where('slug', $slug)
                      ->where('status', 'publik')
                      ->first();

        if (!$album) {
            return null;
        }

        // Ambil foto-foto terkait (asumsi ada model FotoModel atau query manual)
        // Disini kita gunakan query builder sederhana ke tabel 'foto'
        $db = \Config\Database::connect();
        $album['photos'] = $db->table('foto')
                              ->where('id_album', $album['id'])
                              ->orderBy('created_at', 'DESC')
                              ->get()
                              ->getResultArray();

        return $album;
    }
}