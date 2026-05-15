<?php
// PerkuliahanModel.php
// Bertanggung jawab untuk mengelola data dan berinteraksi dengan database.

// Menyesuaikan dengan namespace CodeIgniter 4 Anda
namespace App\Models;

use CodeIgniter\Model;

class PerkuliahanModel extends Model {

    // Konfigurasi untuk model CodeIgniter 4
    protected $table            = 'perkuliahan'; // Sesuaikan dengan nama tabel Anda
    protected $primaryKey       = 'id';
    protected $returnType       = 'object'; // Konsisten dengan KrsModel Anda
    protected $useSoftDeletes   = false;

    // Sesuaikan dengan kolom-kolom di tabel 'perkuliahan' Anda
    protected $allowedFields    = [
        'kode_mk', 
        'nama_mk', 
        'dosen_pengampu', 
        'sks', 
        'ruangan'
        // 'dosen_id', 'ruangan_id', dll. (jika menggunakan relasi)
    ];

    // Menggunakan timestamps seperti KrsModel
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dalam CI4, fungsi dasar seperti findAll(), find($id), insert(), update(), delete()
     * sudah tersedia secara otomatis dan tidak perlu ditulis ulang jika
     * kebutuhannya sederhana.
     */

    /**
     * Contoh jika Anda perlu join (mirip dengan KrsModel)
     * (Saat ini tidak digunakan oleh controller index, tapi sebagai referensi)
     */
    public function getPerkuliahanWithDetails()
    {
        return $this->db->table('perkuliahan as p')
            ->select('p.*, d.nama_lengkap as nama_dosen, r.nama_ruangan as ruangan_detail')
            ->join('dosen as d', 'd.id = p.dosen_id', 'left') // Asumsi ada 'dosen_id'
            ->join('ruangan as r', 'r.id = p.ruangan_id', 'left') // Asumsi ada 'ruangan_id'
            ->get()
            ->getResultObject();
    }
}
?>

