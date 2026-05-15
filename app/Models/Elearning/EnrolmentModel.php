<?php

namespace App\Models\Elearning;

use CodeIgniter\Model;

class EnrolmentModel extends Model
{
    // FIX: Sesuaikan nama tabel dengan Migration terbaru (singular 'enrollment')
    protected $table            = 'el_enrollment'; 
    protected $primaryKey       = 'id';
    
    // FIX: Tambahkan kolom baru (id_kelas, status, timestamps)
    protected $allowedFields    = [
        'id_kelas', 'id_siswa', 'role', 'status', 
        'joined_at', 'created_at', 'updated_at', 'deleted_at'
    ];

    // Aktifkan fitur otomatis CI4
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    /**
     * Mendapatkan daftar siswa lengkap di kelas tertentu
     */
    public function getStudentsInCourse($courseId, $jenjang = null)
    {
        $builder = $this->select('el_enrollment.*, siswa.nama_lengkap as nama, siswa.nis, siswa.nisn')
                        ->join('siswa', 'siswa.id = el_enrollment.id_siswa')
                        ->join('el_courses', 'el_courses.id = el_enrollment.id_kelas') // Join ke parent course
                        ->where('el_enrollment.id_kelas', $courseId);

        // Filter jenjang opsional (jika diperlukan validasi keamanan)
        if ($jenjang) {
            $builder->where('el_courses.kode_jenjang', $jenjang);
        }

        return $builder->findAll();
    }
}