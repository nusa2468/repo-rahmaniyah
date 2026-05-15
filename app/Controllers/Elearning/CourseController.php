<?php

namespace App\Controllers\Elearning;

use App\Controllers\BaseController;
use App\Models\Elearning\CourseModel;
use App\Models\Elearning\TopicModel;
use App\Models\Elearning\ContentModel;
use App\Models\Elearning\EnrolmentModel;
use App\Models\JadwalPelajaranModel;

class CourseController extends BaseController
{
    protected $courseModel;
    protected $topicModel;
    protected $contentModel;
    protected $enrolmentModel;
    protected $jadwalModel;
    protected $db; 

    public function __construct()
    {
        $this->courseModel    = new CourseModel();
        try {
            $this->topicModel     = new TopicModel();
            $this->contentModel   = new ContentModel();
            $this->enrolmentModel = new EnrolmentModel();
            $this->jadwalModel    = new JadwalPelajaranModel();
        } catch (\Throwable $e) {}
        
        $this->db = \Config\Database::connect(); 
        helper(['form', 'text', 'date']);
    }

    // Helper Private untuk Mendapatkan Role
    protected function getCurrentRole()
    {
        $session = session();
        return strtolower(trim(
            $session->get('role') 
            ?? $session->get('role_name') 
            ?? $session->get('level') 
            ?? $session->get('jabatan') 
            ?? ''
        ));
    }

    // --- HALAMAN UTAMA & PEMBUATAN KELAS MANUAL ---

    public function create()
    {
        $role = $this->getCurrentRole(); 
        $userId = session()->get('user_id') ?? session()->get('id_user') ?? session()->get('id');
        
        $isAdmin = (strpos($role, 'admin') !== false) || ($role === 'superadmin') || ($role === 'yayasan');
        $isGuru = (strpos($role, 'guru') !== false) || (strpos($role, 'pengajar') !== false);
        $isSuperUser = ($role === 'superadmin' || session()->get('kode_jenjang') === 'GLOBAL');

        if (!$isAdmin && !$isGuru && !$isSuperUser) {
            return redirect()->back()->with('error', "Akses ditolak. Role Anda ($role) tidak memiliki izin membuat kelas.");
        }

        // Ambil data jadwal untuk dropdown bantuan
        $jadwalGuru = [];
        try {
            $idTahunAjaran = session()->get('id_tahun_ajaran') ?: 1;
            
            // Mapping ID Guru
            $guruId = $userId; 
            if ($this->db->tableExists('guru')) {
                $colUserId = $this->db->fieldExists('user_id', 'guru') ? 'user_id' : 'id_user';
                if ($this->db->fieldExists($colUserId, 'guru')) {
                    $guruData = $this->db->table('guru')->select('id')->where($colUserId, $userId)->get()->getRow();
                    if ($guruData) $guruId = $guruData->id;
                }
            }

            if ($this->jadwalModel && !empty($guruId)) {
                $rawJadwal = $this->jadwalModel->getJadwalByGuruIdAndTa($guruId, $idTahunAjaran);
                $temp = [];
                foreach ($rawJadwal as $j) {
                    $key = ($j['id_mata_pelajaran'] ?? 0) . '-' . ($j['id_grup_siswa'] ?? 0);
                    if (!isset($temp[$key])) {
                        $j['info_jadwal'] = ($j['hari'] ?? '-') . ' (' . substr($j['jam_mulai'] ?? '',0,5) . '-' . substr($j['jam_selesai'] ?? '',0,5) . ')';
                        $temp[$key] = $j;
                        $jadwalGuru[] = $j;
                    }
                }
            }
        } catch (\Throwable $e) {}

        $data = [
            'title'      => 'Buat Kelas Baru',
            'validation' => \Config\Services::validation(),
            'jadwalGuru' => $jadwalGuru
        ];

        return view('elearning/create', $data);
    }

    public function store()
    {
        $rules = ['nama_kelas' => 'required|min_length[3]|max_length[100]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role = $this->getCurrentRole(); 
        $jenjangSession = session()->get('kode_jenjang');
        $isSuperUser = (strpos($role, 'super') !== false || $jenjangSession === 'GLOBAL');

        if ($isSuperUser) {
            $kodeJenjang = $this->request->getPost('kode_jenjang');
            if (empty($kodeJenjang)) return redirect()->back()->withInput()->with('error', 'Superadmin wajib memilih Unit Sekolah.');
        } else {
            $kodeJenjang = $jenjangSession;
        }

        $idGuru = session()->get('user_id') ?? session()->get('id_user') ?? session()->get('id');
        
        // Ambil Data
        $mapel = $this->request->getPost('mata_pelajaran');
        $ruang = $this->request->getPost('ruang');
        $tema  = $this->request->getPost('theme'); 
        $deskripsi = $this->request->getPost('deskripsi');
        $kodeGabung = strtoupper(random_string('alnum', 6));

        // Format Deskripsi Hybrid
        $finalDeskripsi = $deskripsi;
        $infoTambahan = [];
        if (!empty($mapel)) { $infoTambahan[] = "Mapel: $mapel"; }
        if (!empty($ruang)) { $infoTambahan[] = "Ruang: $ruang"; }
        $infoTambahan[] = "Kode: $kodeGabung";
        if (!empty($infoTambahan)) {
            $finalDeskripsi .= "\n[" . implode(' | ', $infoTambahan) . "]";
        }

        $data = [
            'nama_kelas'     => $this->request->getPost('nama_kelas'),
            'kode_jenjang'   => $kodeJenjang,
            'deskripsi'      => $finalDeskripsi,
            'banner_color'   => $tema ?? 'blue', 
            'id_guru'        => $idGuru,
            'kode_gabung'    => $kodeGabung, 
            'is_active'      => 1,
            'cover_image'    => null,
            'created_at'     => date('Y-m-d H:i:s')
        ];

        if ($this->courseModel->insert($data)) {
            return redirect()->to(base_url('app/elearning'))->with('success', 'Kelas berhasil dibuat di unit ' . $kodeJenjang);
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data ke database.');
        }
    }

    // --- FITUR JOIN (GABUNG KELAS) ---

    public function join() 
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $kodeKelas = $this->request->getPost('kode_kelas');
            $userId    = session()->get('user_id') ?? session()->get('id_user') ?? session()->get('id');

            if (empty($kodeKelas) || empty($userId)) {
                return redirect()->back()->with('error', 'Kode kelas wajib diisi atau sesi berakhir.');
            }

            $course = $this->courseModel->where('kode_gabung', $kodeKelas)->first();
            
            if (!$course) {
                return redirect()->back()->withInput()->with('error', 'Kelas tidak ditemukan. Cek kembali kode kelas.');
            }

            if ($course['id_guru'] == $userId) {
                return redirect()->to(base_url('app/elearning/view/' . $course['id']))->with('info', 'Anda adalah pengajar kelas ini.');
            }
            
            // FIX: Gunakan nama tabel yang benar 'el_enrollment' dan kolom 'id_kelas'
            $tableName = 'el_enrollment';
            if (!$this->db->tableExists($tableName)) {
                 return redirect()->back()->with('error', 'Sistem belum siap: Tabel el_enrollment tidak ditemukan.');
            }

            // Cek apakah sudah bergabung
            $existing = $this->enrolmentModel->where('id_kelas', $course['id'])
                                             ->where('id_siswa', $userId)
                                             ->countAllResults();
            
            if ($existing > 0) {
                 return redirect()->to(base_url('app/elearning/view/' . $course['id']))->with('info', 'Anda sudah bergabung di kelas ini.');
            }

            $dataEnrol = [
                'id_kelas'  => $course['id'],
                'id_siswa'  => $userId,
                'role'      => 'student',
                'status'    => 1,
                'joined_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->enrolmentModel->insert($dataEnrol)) {
                return redirect()->to(base_url('app/elearning/view/' . $course['id']))->with('success', 'Berhasil bergabung ke kelas!');
            } else {
                return redirect()->back()->with('error', 'Gagal bergabung ke kelas.');
            }
        }

        $data = [
            'title' => 'Gabung Kelas',
            'validation' => \Config\Services::validation()
        ];
        return view('elearning/join', $data);
    }

    // --- TAMPILAN KELAS & INTERAKSI ---

    public function view($id, $activeTab = 'forum')
    {
        $course = $this->courseModel->find($id);
        if (!$course) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Kelas tidak ditemukan");

        $this->checkAccess($course);

        $posts = [];
        if ($this->db->tableExists('elearning_posts')) {
            $builder = $this->db->table('elearning_posts');
            $builder->select('elearning_posts.*, users.nama_lengkap as author');
            $builder->join('users', 'users.id = elearning_posts.user_id', 'left');
            $builder->where('class_id', $id);
            $builder->orderBy('created_at', 'DESC');
            $posts = $builder->get()->getResultArray();

            if (!empty($posts) && $this->db->tableExists('elearning_comments')) {
                foreach ($posts as &$post) {
                    $post['comments'] = $this->db->table('elearning_comments')
                        ->select('elearning_comments.*, users.nama_lengkap as commenter_name')
                        ->join('users', 'users.id = elearning_comments.user_id', 'left')
                        ->where('post_id', $post['id'])
                        ->orderBy('created_at', 'ASC')
                        ->get()->getResultArray();
                }
            }
        }

        $data = [
            'title'          => $course['nama_kelas'], 
            'course'         => $course,
            'id_kelas'       => $id, 
            'nama_kelas'     => $course['nama_kelas'],
            'mata_pelajaran' => $course['mata_pelajaran'] ?? 'Umum',
            'active_tab'     => $activeTab,
            'posts'          => $posts
        ];

        return view('elearning/view', $data);
    }
    
    public function classwork($id)
    {
        $course = $this->courseModel->find($id);
        if (!$course) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Kelas tidak ditemukan");

        // 1. Ambil Topik
        $topics = $this->topicModel->where('id_kelas', $id)->findAll();

        // 2. Ambil Materi & Tugas (el_contents)
        $contents = $this->contentModel->where('id_kelas', $id)->orderBy('created_at', 'DESC')->findAll();

        // 3. Ambil Kuis (el_quizzes)
        $quizzes = $this->db->table('el_quizzes')->where('id_kelas', $id)->get()->getResultArray();

        // 4. Grouping Semua Konten berdasarkan Topik
        $groupedContents = [];
        foreach ($topics as $topic) {
            $groupedContents[$topic['id']] = ['nama_topik' => $topic['nama_topik'], 'items' => []];
        }
        $groupedContents[0] = ['nama_topik' => 'Tanpa Topik', 'items' => []];

        // Masukkan Materi/Tugas ke Group
        foreach ($contents as $content) {
            $topicId = $content['id_topic'] ?? 0;
            if (!isset($groupedContents[$topicId])) $topicId = 0;
            
            // Tambahkan flag tipe manual
            $content['real_type'] = $content['tipe']; // materi atau tugas
            $groupedContents[$topicId]['items'][] = $content;
        }

        // Masukkan Kuis ke Group
        foreach ($quizzes as $quiz) {
            $topicId = $quiz['id_topic'] ?? 0;
            if (!isset($groupedContents[$topicId])) $topicId = 0;

            // Samakan struktur agar bisa di-loop di view
            $quiz['real_type'] = 'kuis';
            $quiz['tipe'] = 'kuis';
            $groupedContents[$topicId]['items'][] = $quiz;
        }

        $data = [
            'title'           => 'Tugas Kelas - ' . $course['nama_kelas'],
            'course'          => $course,
            'id_kelas'        => $id,
            'nama_kelas'      => $course['nama_kelas'],
            'mata_pelajaran'  => $course['mata_pelajaran'] ?? 'Umum', 
            'active_tab'      => 'classwork',
            'groupedContents' => $groupedContents,
            'topics'          => $topics,
            'validation'      => \Config\Services::validation()
        ];

        return view('elearning/classwork', $data);
    }

    public function people($id)
    {
        $course = $this->courseModel->find($id);
        if (!$course) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Kelas tidak ditemukan");
        
        $role = $this->getCurrentRole(); 
        $userId = session()->get('user_id') ?? session()->get('id_user') ?? session()->get('id');
        $this->checkAccess($course);
        
        $isAdmin = (strpos($role, 'admin') !== false) || ($role === 'superadmin') || ($userId == $course['id_guru']);

        $teacherData = $this->db->table('users')->select('id, nama_lengkap as nama')->where('id', $course['id_guru'])->get()->getRowArray();
        $teachers = $teacherData ? [$teacherData] : [['id' => 0, 'nama' => 'Pengajar Tidak Ditemukan']];

        // FIX: Menggunakan EnrolmentModel yang sudah join dengan tabel Siswa
        // Ini menjamin nama siswa muncul dan tidak kosong
        $students = $this->enrolmentModel->getStudentsInCourse($id);

        $data = [
            'title'          => 'Anggota - ' . $course['nama_kelas'],
            'course'         => $course,
            'id_kelas'       => $id,
            'nama_kelas'     => $course['nama_kelas'],
            'mata_pelajaran' => $course['mata_pelajaran'] ?? 'Umum',
            'active_tab'     => 'people',
            'is_admin'       => $isAdmin,  
            'teachers'       => $teachers, 
            'students'       => $students 
        ];

        return view('elearning/people', $data); 
    }

    public function update_student_status()
    {
        // Fitur Update Status (Aktif/Nonaktif) - Biasanya untuk SPP
        $idEnrollment = $this->request->getPost('id_enrollment');
        $status       = $this->request->getPost('status'); // 1 = Aktif, 0 = Nonaktif
        
        // Cek Permission (Hanya Admin/Superadmin/Guru Pemilik)
        $role = $this->getCurrentRole();
        $isAdmin = (strpos($role, 'admin') !== false) || ($role === 'superadmin') || (strpos($role, 'guru') !== false);

        if (!$isAdmin) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        if ($this->enrolmentModel->update($idEnrollment, ['status' => $status])) {
            $msg = ($status == 1) ? 'Siswa diaktifkan kembali.' : 'Siswa dinonaktifkan (Suspend).';
            return redirect()->back()->with('success', $msg);
        } else {
            return redirect()->back()->with('error', 'Gagal mengubah status.');
        }
    }

    public function add_member()
    {
        $idKelas = $this->request->getPost('id_kelas');
        $role    = $this->request->getPost('role'); // 'student' or 'teacher'
        $ident   = trim($this->request->getPost('identifier')); // Email/NIS/NIP

        // 1. Cek Validitas Input
        if (empty($idKelas) || empty($ident)) {
            return redirect()->back()->with('error', 'Data tidak lengkap.');
        }

        // Cek Permission
        $myRole = $this->getCurrentRole();
        $isAdmin = (strpos($myRole, 'admin') !== false) || ($myRole === 'superadmin') || (strpos($myRole, 'guru') !== false);
        
        if (!$isAdmin) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $userIdTarget = null;

        // 2. Logika Pencarian User
        if ($role === 'student') {
            // Untuk Siswa, cari di tabel 'siswa' karena el_enrollment terhubung ke siswa.id
            $siswa = $this->db->table('siswa')
                          ->select('id, nama_lengkap')
                          ->groupStart()
                              ->where('nis', $ident)
                              ->orWhere('nisn', $ident)
                              ->orWhere('email', $ident)
                          ->groupEnd()
                          ->get()->getRowArray();
                          
            if ($siswa) {
                $userIdTarget = $siswa['id'];
            } else {
                return redirect()->back()->with('error', 'Siswa tidak ditemukan (Cek NIS/NISN/Email).');
            }
        } 
        else {
            // Untuk Guru/Staff, cari di tabel 'users'
            // Note: Jika el_enrollment memiliki FK ke tabel siswa, insert guru mungkin gagal 
            // kecuali struktur tabel mendukung (misal polymorphic atau loose FK).
            $user = $this->db->table('users')
                         ->where('email', $ident)
                         ->orWhere('username', $ident)
                         ->get()->getRowArray();
            
            if ($user) {
                $userIdTarget = $user['id'];
            } else {
                return redirect()->back()->with('error', 'Pengguna tidak ditemukan (Cek Email/Username).');
            }
        }

        // 3. Cek Apakah Sudah Terdaftar
        $isExist = $this->enrolmentModel->where('id_kelas', $idKelas)
                                        ->where('id_siswa', $userIdTarget)
                                        ->countAllResults();

        if ($isExist > 0) {
            return redirect()->back()->with('info', 'Pengguna sudah terdaftar di kelas ini.');
        }

        // 4. Masukkan ke Kelas
        $dataInsert = [
            'id_kelas'   => $idKelas,
            'id_siswa'   => $userIdTarget,
            'role'       => $role,
            'status'     => 1,
            'joined_at'  => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            if ($this->enrolmentModel->insert($dataInsert)) {
                return redirect()->back()->with('success', 'Anggota berhasil ditambahkan.');
            } else {
                return redirect()->back()->with('error', 'Gagal menyimpan data.');
            }
        } catch (\Exception $e) {
            // Menangkap error jika misalnya FK constraint gagal (misal memasukkan ID guru ke kolom id_siswa yang berelasi ke tabel siswa)
            return redirect()->back()->with('error', 'Terjadi kesalahan database: ' . $e->getMessage());
        }
    }

    public function store_topic()
    {
        $idKelas = $this->request->getPost('id_kelas');
        $namaTopik = $this->request->getPost('nama_topik');
        if (empty($namaTopik) || empty($idKelas)) return redirect()->back()->with('error', 'Nama topik tidak boleh kosong.');

        try {
            $this->topicModel->insert(['id_kelas'  => $idKelas, 'nama_topik' => $namaTopik]);
            return redirect()->back()->with('success', 'Topik berhasil ditambahkan.');
        } catch (\Throwable $e) {
             return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function store_content()
    {
        $idKelas = $this->request->getPost('id_kelas');
        $rules = ['judul' => 'required|min_length[3]', 'id_kelas' => 'required'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('error', 'Judul materi wajib diisi.');

        // FIX: Mapping nama input form ke nama kolom database ContentModel yang benar
        $data = [
            'id_kelas'      => $idKelas,
            'id_topic'      => $this->request->getPost('id_topic') ?: null,
            'judul'         => $this->request->getPost('judul'),
            'isi_teks'      => $this->request->getPost('deskripsi'),   // Mapped to isi_teks
            'tipe'          => $this->request->getPost('tipe') ?? 'materi',
            'file_lampiran' => $this->request->getPost('link_materi'), // Mapped to file_lampiran
            'created_at'    => date('Y-m-d H:i:s')
        ];

        try {
            $this->contentModel->insert($data);
            return redirect()->back()->with('success', 'Materi/Tugas berhasil dibuat.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function post_announcement()
    {
        $id_kelas = $this->request->getPost('id_kelas');
        $content  = $this->request->getPost('content');
        $userId = session()->get('user_id') ?? session()->get('id_user') ?? session()->get('id');

        if (!$userId || empty($content) || empty($id_kelas)) {
             return redirect()->back()->with('error', 'Gagal memposting. Pastikan Anda login dan isi konten.');
        }

        $dataToSave = [
            'class_id'   => $id_kelas,
            'user_id'    => $userId,
            'content'    => $content,
            'type'       => 'announcement',
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->db->table('elearning_posts')->insert($dataToSave)) {
            return redirect()->to(base_url('app/elearning/view/' . $id_kelas))->with('success', 'Pengumuman diposting!');
        }
        return redirect()->back()->with('error', 'Gagal menyimpan.');
    }

    public function post_comment()
    {
        $postId  = $this->request->getPost('post_id');
        $comment = $this->request->getPost('comment');
        $idKelas = $this->request->getPost('id_kelas'); 
        $userId  = session()->get('user_id') ?? session()->get('id_user') ?? session()->get('id');

        if (!empty($postId) && !empty($comment) && $userId) {
            $dataToSave = ['post_id' => $postId, 'user_id' => $userId, 'comment' => $comment, 'created_at' => date('Y-m-d H:i:s')];
            if ($this->db->table('elearning_comments')->insert($dataToSave)) {
                if (!empty($idKelas)) return redirect()->to(base_url('app/elearning/view/' . $idKelas));
                return redirect()->back();
            }
        }
        return redirect()->back()->with('error', 'Komentar gagal dikirim.');
    }

    private function checkAccess($course) {
        $role = $this->getCurrentRole();
        $jenjangSession = session()->get('kode_jenjang');
        $isSuperUser = ($role === 'superadmin' || strpos($role, 'super') !== false || $jenjangSession === 'GLOBAL');

        if (!$isSuperUser && $course['kode_jenjang'] !== $jenjangSession) {
             throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akses ditolak ke unit lain.');
        }
    }
}