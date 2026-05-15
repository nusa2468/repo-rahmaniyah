<?php

namespace App\Controllers\Pengaturan;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\HakAksesModel;
use App\Models\JenjangModel;
use App\Models\SiswaModel;
use App\Models\Portal\PortalPegawaiModel; 
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Controller Pengguna (Pengaturan Pengguna)
 * Mengelola pendaftaran, pembaruan, penghapusan, dan manajemen profil.
 * STATUS: ROBUST V31 (Fixed: Logic Username Pegawai NIP/NIPY)
 */
class Pengguna extends BaseController
{
    protected $userModel;
    protected $hakAksesModel;
    protected $jenjangModel;
    protected $siswaModel;
    protected $pegawaiModel; 

    public function __construct()
    {
        $this->userModel     = new UserModel();
        $this->hakAksesModel = class_exists(HakAksesModel::class) ? new HakAksesModel() : model('App\Models\RoleModel');
        $this->jenjangModel  = model('App\Models\MasterData\JenjangModel') ?? new JenjangModel();
        $this->siswaModel    = new SiswaModel();
        $this->pegawaiModel  = new PortalPegawaiModel();
        
        helper(['form', 'text', 'url']);
    }

    // =========================================================================
    // 1. FITUR UTAMA: INDEX USER LIST (ADMIN)
    // =========================================================================

    public function index(): string
    {
        // 0. Ambil Input Filter & Pagination
        $perPage    = $this->request->getVar('per_page') ?? 10;
        $search     = $this->request->getVar('search');
        $filterUnit = $this->request->getVar('unit');
        $filterRole = $this->request->getVar('role');
        
        $currentPage = $this->request->getVar('page_users') ? (int)$this->request->getVar('page_users') : 1;
        $offset      = ($currentPage - 1) * (int)$perPage;
        
        $session     = session();
        $userJenjang = $session->get('kode_jenjang');
        $isGlobal    = in_array(strtoupper($userJenjang ?? ''), ['GLOBAL', 'YAYASAN', 'PUSAT']);
        $targetUnit  = $isGlobal ? $filterUnit : $userJenjang;

        // 1. SIAPKAN DATA KPI (STATISTIK)
        $builderSys = $this->userModel->builder();
        if ($targetUnit) $builderSys->where('kode_jenjang', $targetUnit);
        $countSys = $builderSys->where('deleted_at', null)->countAllResults();

        $builderSiswa = $this->siswaModel->builder();
        if ($targetUnit) $builderSiswa->where('kode_jenjang', $targetUnit);
        $countSiswa = $builderSiswa->whereIn('status', ['Aktif', 'active', '1'])->countAllResults();

        $builderPegawai = $this->pegawaiModel->builder();
        if ($targetUnit) $builderPegawai->where('kode_jenjang', $targetUnit);
        $countGuru = (clone $builderPegawai)->where('jenis_pegawai', 'guru')
                                            ->where('status_aktif', 'aktif')
                                            ->where('deleted_at', null)
                                            ->countAllResults();
        
        $countStaff = (clone $builderPegawai)->whereIn('jenis_pegawai', ['staff', 'penunjang', 'admin'])
                                             ->where('status_aktif', 'aktif')
                                             ->where('deleted_at', null)
                                             ->countAllResults();

        $kpi = [
            'total_system'  => $countSys,
            'total_siswa'   => $countSiswa,
            'total_guru'    => $countGuru,
            'total_staff'   => $countStaff,
            'total_all'     => $countSys + $countSiswa + $countGuru + $countStaff
        ];

        // 2. DATA COLLECTION
        $allData = [];

        // --- A. SYSTEM USERS ---
        if (empty($filterRole) || $filterRole === 'ADMIN') {
            $this->userModel->select('users.id, users.nama_lengkap, users.username, users.email, users.kode_jenjang, users.is_active, users.last_login, roles.name as role_name')
                            ->join('roles', 'roles.id = users.id_role', 'left');
            
            if ($targetUnit) $this->userModel->where('users.kode_jenjang', $targetUnit);
            if ($search) {
                $this->userModel->groupStart()
                                ->like('users.username', $search)
                                ->orLike('users.nama_lengkap', $search)
                                ->orLike('users.email', $search)
                                ->groupEnd();
            }
            $rawUsers = $this->userModel->where('users.deleted_at', null)->findAll();
            
            foreach($rawUsers as $u) {
                $allData[] = [
                    'id'            => $u['id'],
                    'display_id'    => $u['id'],
                    'nama_lengkap'  => $u['nama_lengkap'],
                    'username'      => $u['username'],
                    'email'         => $u['email'],
                    'role_name'     => $u['role_name'] ?? 'USER',
                    'kode_jenjang'  => $u['kode_jenjang'],
                    'is_active'     => $u['is_active'],
                    'last_login'    => $u['last_login'],
                    'foto'          => null,
                    'type'          => 'system',
                    'raw_role'      => 'SYSTEM'
                ];
            }
        }

        // --- B. PEGAWAI ---
        $fetchPegawai = false;
        $jenisPegawai = [];

        if (empty($filterRole)) {
            $fetchPegawai = true;
        } elseif ($filterRole === 'GURU') {
            $fetchPegawai = true;
            $jenisPegawai = ['guru'];
        } elseif ($filterRole === 'KARYAWAN') {
            $fetchPegawai = true;
            $jenisPegawai = ['staff', 'penunjang', 'admin'];
        }

        if ($fetchPegawai) {
            // FIX: Tambahkan 'nipy' ke select agar bisa jadi fallback username
            $this->pegawaiModel->select('id, nama_lengkap, nip, nipy, email, kode_jenjang, status_aktif, jenis_pegawai, foto');
            if ($targetUnit) $this->pegawaiModel->where('kode_jenjang', $targetUnit);
            if (!empty($jenisPegawai)) $this->pegawaiModel->whereIn('jenis_pegawai', $jenisPegawai);
            if ($search) {
                $this->pegawaiModel->groupStart()
                                   ->like('nama_lengkap', $search)
                                   ->orLike('nip', $search)
                                   ->orLike('nipy', $search) // Support search by NIPY
                                   ->groupEnd();
            }
            
            $rawPegawai = $this->pegawaiModel->where('deleted_at', null)->findAll();
            foreach($rawPegawai as $p) {
                $isActive = (in_array(strtolower($p['status_aktif'] ?? ''), ['aktif', 'active'])) ? 1 : 0;
                
                // LOGIC USERNAME: NIP > NIPY > '-'
                $usernameDisplay = !empty($p['nip']) ? $p['nip'] : (!empty($p['nipy']) ? $p['nipy'] : '-');
                
                $allData[] = [
                    'id'            => 'P-' . $p['id'],
                    'display_id'    => 'P-' . $p['id'],
                    'nama_lengkap'  => $p['nama_lengkap'],
                    'username'      => $usernameDisplay,
                    'email'         => $p['email'],
                    'role_name'     => strtoupper($p['jenis_pegawai'] ?? 'PEGAWAI'),
                    'kode_jenjang'  => $p['kode_jenjang'],
                    'is_active'     => $isActive,
                    'last_login'    => null,
                    'foto'          => $p['foto'],
                    'type'          => 'pegawai',
                    'raw_role'      => strtoupper($p['jenis_pegawai'] ?? 'PEGAWAI')
                ];
            }
        }

        // --- C. SISWA ---
        if (empty($filterRole) || $filterRole === 'SISWA') {
            $this->siswaModel->select('id, nama_lengkap, nis, email, kode_jenjang, status, foto');
            if ($targetUnit) $this->siswaModel->where('kode_jenjang', $targetUnit);
            if ($search) {
                $this->siswaModel->groupStart()->like('nama_lengkap', $search)->orLike('nis', $search)->groupEnd();
            }
            $rawSiswa = $this->siswaModel->where('deleted_at', null)->findAll();
            foreach($rawSiswa as $s) {
                $domain = !empty($s['kode_jenjang']) ? strtolower($s['kode_jenjang']) : 'sekolah';
                $isActive = (in_array(strtolower($s['status'] ?? ''), ['aktif', 'active', '1'])) ? 1 : 0;
                
                // LOGIC USERNAME: NIS
                $usernameDisplay = !empty($s['nis']) ? $s['nis'] : '-';

                $allData[] = [
                    'id'            => 'S-' . $s['id'],
                    'display_id'    => 'S-' . $s['id'],
                    'nama_lengkap'  => $s['nama_lengkap'],
                    'username'      => $usernameDisplay,
                    'email'         => !empty($s['email']) ? $s['email'] : $usernameDisplay . '@' . $domain . '.test',
                    'role_name'     => 'SISWA',
                    'kode_jenjang'  => $s['kode_jenjang'],
                    'is_active'     => $isActive,
                    'last_login'    => null,
                    'foto'          => $s['foto'],
                    'type'          => 'siswa',
                    'raw_role'      => 'SISWA'
                ];
            }
        }

        // 3. SORTING GABUNGAN
        usort($allData, function($a, $b) {
            return strcasecmp($a['nama_lengkap'], $b['nama_lengkap']);
        });

        // 4. PAGINATION LOGIC
        $totalRows = count($allData);
        $offset    = ($currentPage - 1) * (int)$perPage;
        $pagedData = array_slice($allData, $offset, (int)$perPage);
        $pager     = \Config\Services::pager();
        $pagerLinks = $pager->makeLinks($currentPage, (int)$perPage, $totalRows, 'tailwind_pagination');

        $jenjangList = $this->jenjangModel->where('status', 'aktif')
                                          ->orderBy('urutan', 'ASC')
                                          ->findAll();

        return view('pengaturan/pengguna/index', [
            'title'          => 'Manajemen Pengguna',
            'current_module' => 'pengaturan',
            'users'          => $pagedData, 
            'pager_links'    => $pagerLinks,
            'kpi'            => $kpi,
            'jenjang_list'   => $jenjangList,
            'filters'        => [
                'unit'     => $filterUnit,
                'search'   => $search,
                'role'     => $filterRole,
                'per_page' => $perPage
            ]
        ]);
    }

    // =========================================================================
    // 2. FITUR PROFIL & PASSWORD
    // =========================================================================

    public function profil()
    {
        $session = session();
        $userId = $session->get('user_id'); 
        $dbUser = $this->userModel->getPenggunaWithRole($userId);

        if ($dbUser) {
            $user = $dbUser;
            $user['nama']         = $dbUser['nama_lengkap'];
            $user['role']         = $dbUser['role_name'] ?? 'Pengguna';
            $user['jenjang']      = $dbUser['kode_jenjang'] ?? 'GLOBAL';
            $user['foto']         = 'https://ui-avatars.com/api/?name=' . urlencode($dbUser['nama_lengkap']) . '&background=0ea5e9&color=fff&size=256';
        } else {
            $user = [
                'id'            => $userId,
                'nama'          => $session->get('nama_lengkap') ?? 'User',
                'nama_lengkap'  => $session->get('nama_lengkap') ?? 'User',
                'role'          => $session->get('role_display') ?? 'Pengguna',
                'foto'          => 'https://ui-avatars.com/api/?name=User&background=0ea5e9&color=fff'
            ];
        }

        return view('pengaturan/pengguna/profil', [
            'title'          => 'Profil Akun Saya',
            'current_module' => 'pengaturan',
            'user'           => $user,
            'avatar'         => $user['foto']
        ]);
    }

    public function updatePassword()
    {
        $session = session();
        $userId  = $session->get('user_id');
        $rules   = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = $this->userModel->find($userId);
        if (!$user || !password_verify($this->request->getPost('current_password'), $user['password_hash'])) {
            return redirect()->back()->with('error', 'Kata sandi saat ini salah.');
        }

        $this->userModel->skipValidation(true)->update($userId, [
            'password_hash' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT)
        ]);

        return redirect()->to(base_url('app/pengaturan/pengguna/profil'))->with('success', 'Kata sandi berhasil diperbarui.');
    }

    // =========================================================================
    // 3. CRUD METHODS
    // =========================================================================

    public function new()
    {
        if ($this->hakAksesModel) {
            $roles = $this->hakAksesModel->orderBy('name', 'ASC')->findAll();
        } else {
            $roles = [];
        }
        $jenjangList = $this->jenjangModel->where('status', 'aktif')->orderBy('kode_jenjang', 'ASC')->findAll();
        return view('pengaturan/pengguna/form', [
            'title'          => 'Tambah Pengguna Baru',
            'current_module' => 'pengaturan',
            'roles'          => $roles,
            'jenjang_list'   => $jenjangList,
            'validation'     => \Config\Services::validation(),
        ]);
    }
    
    public function store()
    {
        $rules = [
            'nama_lengkap' => 'required|min_length[3]',
            'username'     => 'required|alpha_dash|min_length[3]|is_unique[users.username]', 
            'email'        => 'required|valid_email|is_unique[users.email]',
            'id_role'      => 'required',
            'password'     => 'required|min_length[6]',
            'confirm_pass' => 'matches[password]', 
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $data = [
            'id_role'      => $post['id_role'],
            'username'     => strtolower($post['username']), 
            'nama_lengkap' => $post['nama_lengkap'],
            'email'        => strtolower($post['email']),
            'kode_jenjang' => strtoupper($post['kode_jenjang']),
            'is_active'    => $post['is_active'] ?? 1,
            'password_hash'=> password_hash($post['password'], PASSWORD_DEFAULT)
        ];

        if ($this->userModel->skipValidation(true)->insert($data) === false) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        return redirect()->to('app/pengaturan/pengguna')->with('success', 'Akun pengguna berhasil didaftarkan.');
    }

    public function edit($id)
    {
        $isSiswa   = false;
        $isPegawai = false;
        $user      = [];

        if (strpos($id, 'S-') === 0) {
            // Edit Siswa
            $isSiswa = true;
            $realId  = substr($id, 2); 
            $data    = $this->siswaModel->find($realId);
            if (!$data) throw PageNotFoundException::forPageNotFound('Data siswa tidak ditemukan.');

            $domain = !empty($data['kode_jenjang']) ? strtolower($data['kode_jenjang']) : 'sekolah';
            $email  = !empty($data['email']) ? $data['email'] : $data['nis'] . '@' . $domain . '.test';

            $user = [
                'id'           => $id, 
                'nama_lengkap' => $data['nama_lengkap'],
                'username'     => $data['nis'], 
                'email'        => $email,     
                'id_role'      => 'SISWA', 
                'role_name'    => 'SISWA',
                'role_id'      => 'SISWA', 
                'kode_jenjang' => $data['kode_jenjang'],
                'is_active'    => (in_array(strtolower($data['status']), ['aktif', 'active'])) ? 1 : 0,
            ];

        } elseif (strpos($id, 'P-') === 0) {
            // Edit Pegawai
            $isPegawai = true;
            $realId    = substr($id, 2);
            $data      = $this->pegawaiModel->find($realId);
            if (!$data) throw PageNotFoundException::forPageNotFound('Data pegawai tidak ditemukan.');

            $user = [
                'id'           => $id,
                'nama_lengkap' => $data['nama_lengkap'],
                'username'     => !empty($data['nip']) ? $data['nip'] : ($data['nipy'] ?? '-'), 
                'email'        => $data['email'],
                'id_role'      => 'PEGAWAI',
                'role_name'    => strtoupper($data['jenis_pegawai'] ?? 'PEGAWAI'),
                'role_id'      => 'PEGAWAI',
                'kode_jenjang' => $data['kode_jenjang'],
                'is_active'    => (in_array(strtolower($data['status_aktif'] ?? 'aktif'), ['aktif', 'active'])) ? 1 : 0,
            ];

        } else {
            // Edit User System
            $user = $this->userModel->getPenggunaWithRole($id);
            if (empty($user)) {
                $userObj = $this->userModel->find($id);
                if (!$userObj) throw PageNotFoundException::forPageNotFound('Pengguna tidak ditemukan.');
                $user = is_object($userObj) ? (array) $userObj : $userObj;
            }
            if (isset($user['is_active'])) $user['is_active'] = (int) $user['is_active'];
        }

        $roles = $this->hakAksesModel ? $this->hakAksesModel->orderBy('name', 'ASC')->findAll() : [];
        if ($isSiswa) {
            $roles = [(object)['id' => 'SISWA', 'name' => 'SISWA (Peserta Didik)']];
        } elseif ($isPegawai) {
            $roles = [(object)['id' => 'PEGAWAI', 'name' => 'PEGAWAI (Guru & Staff)']];
        }

        $jenjangList = $this->jenjangModel->where('status', 'aktif')->orderBy('kode_jenjang', 'ASC')->findAll();

        return view('pengaturan/pengguna/form', [
            'title'          => 'Edit Pengguna',
            'current_module' => 'pengaturan',
            'user'           => $user,
            'roles'          => $roles,
            'jenjang_list'   => $jenjangList,
            'validation'     => \Config\Services::validation(),
        ]);
    }
    
    public function update($id)
    {
        // 1. UPDATE SISWA
        if (strpos($id, 'S-') === 0) {
            $realId = substr($id, 2);
            $current = $this->siswaModel->find($realId);
            if (!$current) return redirect()->back()->with('error', 'Siswa tidak ditemukan.');
            
            $newEmail = strtolower($this->request->getPost('email'));
            $isActive = ($this->request->getPost('is_active') == '1') ? 'Aktif' : 'Non-Aktif';
            $password = trim((string)$this->request->getPost('password'));

            $rules = ['email' => "permit_empty|valid_email|is_unique[siswa.email,id,{$realId}]"];
            if (!empty($password)) {
                 $rules['password']     = 'min_length[6]';
                 $rules['confirm_pass'] = 'matches[password]';
            }
            if (! $this->validate($rules)) {
                 return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $updateData = ['email' => $newEmail, 'status' => $isActive];
            if (!empty($password)) {
                $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
            } elseif (empty($current['password'])) {
                $updateData['password'] = password_hash($current['nis'] . '.' . ($current['kode_jenjang'] ?? 'SEKOLAH'), PASSWORD_DEFAULT);
            }

            $this->siswaModel->allowCallbacks(false)->update($realId, $updateData);
            return redirect()->to('app/pengaturan/pengguna')->with('success', 'Akun siswa berhasil diperbarui.');
        }

        // 2. UPDATE PEGAWAI
        if (strpos($id, 'P-') === 0) {
            $realId = substr($id, 2);
            $current = $this->pegawaiModel->find($realId);
            if (!$current) return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');

            $newEmail = strtolower($this->request->getPost('email'));
            $isActive = ($this->request->getPost('is_active') == '1') ? 'aktif' : 'nonaktif';
            $password = trim((string)$this->request->getPost('password'));

            $rules = ['email' => "permit_empty|valid_email|is_unique[pegawai.email,id,{$realId}]"];
            if (!empty($password)) {
                 $rules['password']     = 'min_length[6]';
                 $rules['confirm_pass'] = 'matches[password]';
            }
            if (! $this->validate($rules)) {
                 return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $updateData = ['email' => $newEmail, 'status_aktif' => $isActive];
            if(!empty($password)) {
                $updateData['password'] = $password; 
            }

            $this->pegawaiModel->update($realId, $updateData);
            return redirect()->to('app/pengaturan/pengguna')->with('success', 'Akun pegawai berhasil diperbarui.');
        }

        // 3. UPDATE USER SYSTEM
        $post = $this->request->getPost();
        $rules = [
            'nama_lengkap' => 'required|min_length[3]',
            'username'     => "required|alpha_dash|min_length[3]|is_unique[users.username,id,{$id}]",
            'email'        => "required|valid_email|is_unique[users.email,id,{$id}]",
            'id_role'      => 'required',
        ];
        $data = [
            'id_role'      => $post['id_role'],
            'username'     => strtolower($post['username']),
            'nama_lengkap' => $post['nama_lengkap'],
            'email'        => strtolower($post['email']),
            'kode_jenjang' => strtoupper($post['kode_jenjang']),
            'is_active'    => isset($post['is_active']) ? (int)$post['is_active'] : 1,
        ];
        $newPass = trim($post['password'] ?? '');
        if (!empty($newPass)) {
            $rules['password']      = 'required|min_length[6]';
            $rules['confirm_pass'] = 'matches[password]';
            $data['password_hash'] = password_hash($newPass, PASSWORD_DEFAULT);
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($this->userModel->skipValidation(true)->update($id, $data) === false) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        return redirect()->to('app/pengaturan/pengguna')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (strpos($id, 'S-') === 0) {
            return redirect()->back()->with('error', 'Data siswa hanya bisa dihapus melalui menu Kesiswaan.');
        }
        if (strpos($id, 'P-') === 0) {
            return redirect()->back()->with('error', 'Data pegawai hanya bisa dihapus melalui menu Kepegawaian.');
        }
        if ($id == session()->get('user_id')) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        if ($this->userModel->delete($id)) {
            return redirect()->to('app/pengaturan/pengguna')->with('success', 'Pengguna telah dinonaktifkan.');
        }
        return redirect()->back()->with('error', 'Gagal menghapus pengguna.');
    }
}