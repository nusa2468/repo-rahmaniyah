<?php

namespace App\Controllers\Elearning;

use App\Controllers\BaseController;
use App\Models\Elearning\CourseModel;
use App\Models\JadwalPelajaranModel; // PASTIKAN MODEL DI-IMPORT

class CourseGeneratorController extends BaseController
{
    protected $courseModel;
    protected $jadwalModel; // Properti Model Jadwal
    protected $db; 

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->jadwalModel = new JadwalPelajaranModel(); // Instansiasi Model
        $this->db = \Config\Database::connect(); 
        helper(['form', 'text', 'date']);
    }

    private function getCurrentRole()
    {
        $session = session();
        return strtolower(trim(
            $session->get('role') ?? $session->get('role_name') ?? $session->get('level') ?? $session->get('jabatan') ?? ''
        ));
    }

    // --------------------------------------------------------------------------
    // HALAMAN GENERATE (VIEW)
    // --------------------------------------------------------------------------
    public function generate()
    {
        $role = $this->getCurrentRole();
        $isAdmin = (strpos($role, 'admin') !== false) || ($role === 'superadmin') || ($role === 'yayasan');

        if (!$isAdmin) {
             return redirect()->back()->with('error', "Akses ditolak. Fitur khusus Admin.");
        }

        $jenjangAdmin = session()->get('kode_jenjang') ?? 'GLOBAL';
        
        // ======================================================================
        // 1. KUMPULKAN MULTI-TAHUN AJARAN AKTIF
        // ======================================================================
        $taBuilder = $this->db->table('tahun_ajaran')
                              ->groupStart()
                              ->where('status', '1')
                              ->orWhere('status', 'aktif')
                              ->groupEnd();

        if ($jenjangAdmin !== 'GLOBAL') {
            $taBuilder->where('kode_jenjang', $jenjangAdmin);
        }

        $taAktifList = $taBuilder->get()->getResultArray();
        $idTahunList = !empty($taAktifList) ? array_column($taAktifList, 'id') : [1];
        
        $namaTaArray = [];
        foreach ($taAktifList as $ta) {
            $namaTaArray[] = $ta['tahun_ajaran'] . ' (' . strtoupper($ta['kode_jenjang']) . ')';
        }
        $namaTaDisplay = !empty($namaTaArray) ? implode(', ', array_unique($namaTaArray)) : 'Tidak Diketahui';

        // ======================================================================
        // 2. QUERY BERSIH MENGGUNAKAN MODEL (MVC BEST PRACTICE)
        // Kita panggil getJadwalBuilder() dari model agar JOIN tidak berantakan di Controller
        // ======================================================================
        $builder = $this->jadwalModel->getJadwalBuilder();
        
        // Terapkan Filter Array Multi-Tahun Ajaran
        $builder->whereIn('jadwal_pelajaran.id_tahun_ajaran', $idTahunList);
        
        if ($jenjangAdmin !== 'GLOBAL') {
            $builder->where('jadwal_pelajaran.kode_jenjang', $jenjangAdmin);
        }

        // Terapkan Bypass Soft Delete Bug untuk MySQL
        $builder->orGroupStart()
                ->whereIn('jadwal_pelajaran.id_tahun_ajaran', $idTahunList)
                ->where('jadwal_pelajaran.deleted_at', '0000-00-00 00:00:00')
                ->groupEnd();

        $rawJadwal = $builder->get()->getResultArray();

        // ----------------------------------------------------------------------
        // GROUPING & FILTERING
        // ----------------------------------------------------------------------
        $candidates = [];
        $existingCourses = $this->courseModel->findAll();

        foreach ($rawJadwal as $j) {
            $idKelas    = $j['id_kelas'] ?? 0;
            $idGrup     = $j['id_grup_siswa'] ?? 0;
            
            // Prioritaskan nama_grup, fallback ke nama_kelas
            // Karena dari model, nama_grup jika kosong diset "Satu Kelas Penuh"
            $namaRombel = ($j['nama_grup'] !== 'Satu Kelas Penuh' && !empty($j['nama_grup'])) 
                          ? $j['nama_grup'] 
                          : (!empty($j['nama_kelas']) ? $j['nama_kelas'] : 'Kelas Umum');
                          
            $namaMapel  = $j['nama_mapel'] ?? 'Mapel Tidak Diketahui';
            
            // Wajib ada Guru
            $idGuru = $j['id_guru'] ?? 0;
            if (empty($idGuru)) continue;

            $namaGuru = $j['nama_guru'] ?? 'Guru Tidak Diketahui';

            $key = ($j['id_mata_pelajaran'] ?? 0) . '-' . $idKelas . '-' . $idGrup . '-' . $idGuru;

            if (!isset($candidates[$key])) {
                $isExists = false;
                $targetName = $namaMapel . ' - ' . $namaRombel; 

                foreach ($existingCourses as $ec) {
                    if ($ec['nama_kelas'] === $targetName && $ec['kode_jenjang'] == ($j['kode_jenjang'] ?? '')) {
                        $isExists = true; 
                        break;
                    }
                }

                $candidates[$key] = [
                    'unique_key'    => $key, 
                    'kode_jenjang'  => $j['kode_jenjang'] ?? 'GLOBAL',
                    'mapel'         => $namaMapel,
                    'grup'          => $namaRombel,
                    'guru'          => $namaGuru,
                    'guru_id'       => $idGuru,
                    'hari'          => $j['hari'] ?? '-',
                    'jam_mulai'     => $j['jam_mulai'] ?? '-',
                    'jam_selesai'   => $j['jam_selesai'] ?? '-',
                    'ruang'         => $j['nama_ruangan'] ?? 'Virtual',
                    'is_exists'     => $isExists,
                    'suggested_name'=> $targetName,
                    'id_kelas'      => $idKelas,
                    'id_grup_siswa' => $idGrup,
                    'id_tahun_ajaran'=> $j['id_tahun_ajaran'] ?? $idTahunList[0]
                ];
            }
        }

        $data = [
            'title'      => 'Generate Kelas E-learning',
            'candidates' => $candidates,
            'tahun_ajaran' => $namaTaDisplay
        ];

        return view('elearning/admin_generate', $data);
    }

    // --------------------------------------------------------------------------
    // PROSES EKSEKUSI GENERATE (LOGIC BERAT)
    // --------------------------------------------------------------------------
    public function process_generate()
    {
        $role = $this->getCurrentRole();
        $isAdmin = (strpos($role, 'admin') !== false) || ($role === 'superadmin');

        if (!$isAdmin) return redirect()->back()->with('error', 'Akses ditolak.');

        $items = $this->request->getPost('items');
        if (empty($items)) return redirect()->back()->with('error', 'Tidak ada kelas yang dipilih.');

        $countSuccess = 0;
        $countFail = 0;
        $totalSiswa = 0; 

        foreach ($items as $jsonItem) {
            $item = json_decode($jsonItem, true);
            
            if (!$item || empty($item['suggested_name']) || empty($item['guru_id'])) {
                $countFail++;
                continue;
            }

            // Cek duplikasi lagi untuk keamanan
            $exists = $this->courseModel->where('nama_kelas', $item['suggested_name'])
                                        ->where('kode_jenjang', $item['kode_jenjang'])
                                        ->first();
            
            if (!$exists) {
                // Info Hybrid
                $deskripsiHybrid = "Kelas otomatis untuk mapel: " . $item['mapel'] . "\n";
                $deskripsiHybrid .= "Jadwal: " . ($item['hari'] ?? '-') . ", " . substr($item['jam_mulai']??'',0,5) . "-" . substr($item['jam_selesai']??'',0,5) . "\n";
                $deskripsiHybrid .= "Ruang: " . ($item['ruang'] ?? 'Virtual') . " (Hybrid/Onsite)";
                
                $kodeGabung = strtoupper(random_string('alnum', 6));
                $deskripsiHybrid .= "\nKode: " . $kodeGabung; 

                // Tentukan warna banner
                $banner = 'blue';
                if ($item['kode_jenjang'] == 'SD') $banner = 'green';
                if ($item['kode_jenjang'] == 'SMA') $banner = 'purple';

                $dataInsert = [
                    'nama_kelas'     => $item['suggested_name'],
                    'kode_jenjang'   => $item['kode_jenjang'],
                    'deskripsi'      => $deskripsiHybrid, 
                    'banner_color'   => $banner, 
                    'id_guru'        => $item['guru_id'], 
                    'kode_gabung'    => $kodeGabung, 
                    'is_active'      => 1,
                    'created_at'     => date('Y-m-d H:i:s')
                ];

                if ($this->courseModel->insert($dataInsert)) {
                    $newCourseId = $this->courseModel->getInsertID();
                    $countSuccess++;

                    // AUTO ENROLL SISWA DARI ROMBEL/KELAS
                    $totalSiswa += $this->enrollStudents(
                        $newCourseId, 
                        $item['id_kelas'] ?? 0, 
                        $item['id_grup_siswa'] ?? 0,
                        $item['id_tahun_ajaran'] ?? 0
                    );
                } else {
                    $countFail++;
                }
            } else {
                $countFail++;
            }
        }

        return redirect()->to(base_url('app/elearning'))->with('success', "Proses selesai. Berhasil: $countSuccess Kelas ($totalSiswa Siswa Didaftarkan otomatis), Gagal/Skip: $countFail");
    }

    /**
     * Helper Private: Auto-Enroll Siswa berdasarkan Kelas Aktual dari tabel Enrollment
     */
    private function enrollStudents($courseId, $idKelas, $idGrup, $idTahunAjaran)
    {
        $db = \Config\Database::connect();
        $siswaList = [];

        // 1. Prioritas Pertama: Ambil siswa aktif dari tabel siswa_enrollment (Sangat Valid)
        if ($db->tableExists('siswa_enrollment') && !empty($idKelas)) {
            $qb = $db->table('siswa_enrollment')
                     ->select('id_siswa as siswa_id')
                     ->where('status_akademik', 'Aktif')
                     ->where('id_kelas', $idKelas);
                     
            if (!empty($idTahunAjaran)) {
                $qb->where('id_tahun_ajaran', $idTahunAjaran);
            }
            $siswaList = $qb->get()->getResultArray();
        } 
        // 2. Prioritas Kedua (Fallback): Coba cari dari tabel siswa langsung
        if (empty($siswaList)) {
            $qb = $db->table('siswa')->select('id as siswa_id')->where('status', 'aktif');
            
            if (!empty($idGrup) && $db->fieldExists('id_grup_siswa', 'siswa')) {
                $qb->where('id_grup_siswa', $idGrup);
            } else if (!empty($idKelas) && $db->fieldExists('id_kelas', 'siswa')) {
                $qb->where('id_kelas', $idKelas);
            }
            
            $siswaList = $qb->get()->getResultArray();
        }

        if (empty($siswaList)) return 0;

        // 3. Tentukan nama tabel enrolment E-Learning (el_enrollment)
        $tblEnrol = 'el_enrollment';
        try {
            if (!$db->tableExists($tblEnrol) && $db->tableExists('el_enrolments')) {
                $tblEnrol = 'el_enrolments';
            }
        } catch (\Throwable $e) {}

        // 4. Deteksi Nama Kolom
        $fields = [];
        try { $fields = $db->getFieldNames($tblEnrol); } catch (\Throwable $e) {}

        $colCourse = in_array('id_kelas', $fields) ? 'id_kelas' : 'id_course';
        $colSiswa = in_array('id_siswa', $fields) ? 'id_siswa' : 'user_id';

        $enrolmentData = [];
        $now = date('Y-m-d H:i:s');

        foreach ($siswaList as $s) {
            if (!empty($s['siswa_id'])) {
                $isExist = $db->table($tblEnrol)
                              ->where($colCourse, $courseId)
                              ->where($colSiswa, $s['siswa_id'])
                              ->countAllResults();
                
                if ($isExist == 0) {
                    $enrolmentData[] = [
                        $colCourse   => $courseId,
                        $colSiswa    => $s['siswa_id'],
                        'role'       => 'student',
                        'status'     => 1,
                        'joined_at'  => $now,
                        'created_at' => $now
                    ];
                }
            }
        }

        // 5. Eksekusi Pendaftaran Massal
        if (!empty($enrolmentData)) {
            try {
                $db->table($tblEnrol)->insertBatch($enrolmentData);
                return count($enrolmentData);
            } catch (\Exception $e) {
                return 0;
            }
        }

        return 0;
    }
}