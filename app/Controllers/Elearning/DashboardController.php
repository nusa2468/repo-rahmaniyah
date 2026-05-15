<?php

namespace App\Controllers\Elearning;

use App\Controllers\BaseController;
use App\Models\Elearning\CourseModel;
use App\Models\Elearning\EnrolmentModel;

class DashboardController extends BaseController
{
    protected $courseModel;
    protected $enrolmentModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->enrolmentModel = new EnrolmentModel();
    }

    public function index()
    {
        // 1. Ambil Data Session
        $jenjangSession = session()->get('kode_jenjang'); 
        $userId         = session()->get('user_id') ?? session()->get('id_user') ?? session()->get('id');
        $roleRaw        = session()->get('role') ?? '';
        $role           = strtolower(trim($roleRaw)); 

        // 2. Mulai Query Builder
        $builder = $this->courseModel->builder();
        
        // [FIX] Tambahkan select untuk nama mapel
        $builder->select('
            el_courses.*, 
            users.nama_lengkap as nama_guru,
            mata_pelajaran.nama_mapel as mata_pelajaran
        ');
        
        // Join ke Guru (Users)
        $builder->join('users', 'users.id = el_courses.id_guru', 'left');
        
        // [FIX] Join ke Jadwal -> Mata Pelajaran
        // Ini kuncinya agar nama mapel muncul otomatis
        $builder->join('jadwal_pelajaran', 'jadwal_pelajaran.id = el_courses.id_jadwal_pelajaran', 'left');
        $builder->join('mata_pelajaran', 'mata_pelajaran.id = jadwal_pelajaran.id_mata_pelajaran', 'left');
        
        $builder->orderBy('el_courses.created_at', 'DESC');
        $builder->where('el_courses.deleted_at', null);

        $displayJenjang = $jenjangSession;

        // 3. LOGIKA FILTER UTAMA
        $isSuperUser = ($role === 'superadmin' || strpos($role, 'super') !== false || $jenjangSession === 'GLOBAL');

        if ($isSuperUser) {
            $displayJenjang = 'SEMUA UNIT (Superadmin)';
        } 
        else {
            $builder->where('el_courses.kode_jenjang', $jenjangSession);
        }

        // 4. FILTER KEPEMILIKAN
        if (strpos($role, 'guru') !== false || strpos($role, 'pengajar') !== false) {
            $builder->where('el_courses.id_guru', $userId);
        } 
        elseif (strpos($role, 'siswa') !== false || strpos($role, 'student') !== false) {
            // Menggunakan tabel enrollment yang benar
            $builder->join('el_enrollment', 'el_enrollment.id_kelas = el_courses.id');
            $builder->where('el_enrollment.id_siswa', $userId);
        }

        // 5. Eksekusi
        $courses = $builder->get()->getResultArray();

        $data = [
            'title'   => 'E-Learning Dashboard',
            'courses' => $courses,
            'jenjang' => $displayJenjang,
            'role'    => $role
        ];

        return view('elearning/dashboard', $data);
    }
}