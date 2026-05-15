<?php

namespace App\Controllers\Pengaturan;

use App\Controllers\BaseController;
use App\Models\UserModel;
// use App\Models\RoleModel; // Pastikan Anda punya model ini nanti
use CodeIgniter\Exceptions\PageNotFoundException;

class User extends BaseController
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        // $this->roleModel = new RoleModel(); 
        helper(['form', 'text']);
    }

    /**
     * Menampilkan daftar semua pengguna.
     */
    public function index(): string
    {
        // Gunakan method khusus di Model untuk join dengan tabel Role
        // Jika belum ada pagination di method tsb, gunakan findAll dulu sementara
        // Idealnya: $users = $this->userModel->getPenggunaWithRole(null, 10);
        
        // Fallback jika getPenggunaWithRole belum support pagination di model Anda:
        $users = $this->userModel->select('users.*, roles.name as role_name')
                                 ->join('roles', 'roles.id = users.id_role', 'left')
                                 ->orderBy('users.created_at', 'DESC')
                                 ->paginate(10, 'users');

        $data = [
            'title'          => 'Manajemen Pengguna',
            'current_module' => 'pengaturan', // Agar menu sidebar 'Pengaturan' aktif
            'users'          => $users,
            'pager'          => $this->userModel->pager,
        ];
        
        return view('pengaturan/pengguna/index', $data);
    }
    
    /**
     * Menampilkan form tambah pengguna baru.
     */
    public function new()
    {
        // Mockup data roles jika RoleModel belum ada
        // Nanti ganti dengan: $this->roleModel->findAll();
        $roles = [
            ['id' => 1, 'name' => 'Superadmin'],
            ['id' => 2, 'name' => 'Admin Unit'],
            ['id' => 3, 'name' => 'Guru'],
            ['id' => 4, 'name' => 'Siswa'],
        ];

        // Ambil daftar unit/jenjang untuk dropdown
        $jenjang = [
            'GLOBAL' => 'Global (Pusat)',
            'SD'     => 'Unit SD',
            'SMP'    => 'Unit SMP',
            'SMA'    => 'Unit SMA'
        ];

        $data = [
            'title'          => 'Tambah Pengguna Baru',
            'current_module' => 'pengaturan',
            'validation'     => \Config\Services::validation(),
            'roles'          => $roles,
            'jenjang_list'   => $jenjang
        ];

        return view('pengaturan/pengguna/create', $data);
    }
    
    /**
     * Menyimpan data pengguna baru.
     */
    public function store()
    {
        // Validasi input
        if (! $this->validate($this->userModel->getInsertRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        
        // Data yang akan disimpan
        $data = [
            'id_role'      => $post['id_role'],
            'username'     => $post['username'],
            'password'     => $post['password'], // Model akan otomatis hash ini via callback beforeInsert
            'nama_lengkap' => $post['nama_lengkap'],
            'email'        => $post['email'],
            'kode_jenjang' => $post['kode_jenjang'], // Wajib sesuai Model
            'is_active'    => $post['is_active'] ?? 1,
        ];

        if ($this->userModel->insert($data) === false) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        return redirect()->to('app/pengaturan/pengguna')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit pengguna.
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (empty($user)) {
            throw PageNotFoundException::forPageNotFound('Pengguna tidak ditemukan: ' . $id);
        }

        // Mockup roles (Ganti dengan DB call nanti)
        $roles = [
            ['id' => 1, 'name' => 'Superadmin'],
            ['id' => 2, 'name' => 'Admin Unit'],
            ['id' => 3, 'name' => 'Guru'],
            ['id' => 4, 'name' => 'Siswa'],
        ];

        $jenjang = [
            'GLOBAL' => 'Global (Pusat)',
            'SD'     => 'Unit SD',
            'SMP'    => 'Unit SMP',
            'SMA'    => 'Unit SMA'
        ];

        $data = [
            'title'          => 'Edit Pengguna: ' . $user['username'],
            'current_module' => 'pengaturan',
            'user'           => $user,
            'validation'     => \Config\Services::validation(),
            'roles'          => $roles,
            'jenjang_list'   => $jenjang
        ];

        return view('pengaturan/pengguna/edit', $data);
    }
    
    /**
     * Update data pengguna.
     */
    public function update($id)
    {
        // Validasi
        // Note: getUpdateRules biasanya password optional
        $rules = $this->userModel->getValidationRules(['id' => $id]); 
        // Modifikasi rule password agar optional saat update
        $rules['password'] = 'permit_empty|min_length[8]';
        
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        
        $data = [
            'id_role'      => $post['id_role'],
            'username'     => $post['username'],
            'nama_lengkap' => $post['nama_lengkap'],
            'email'        => $post['email'],
            'kode_jenjang' => $post['kode_jenjang'],
            'is_active'    => $post['is_active'] ?? 0,
        ];

        // Hanya update password jika diisi
        if (!empty($post['password'])) {
            $data['password'] = $post['password']; // Model akan hash via beforeUpdate
        }

        if ($this->userModel->update($id, $data) === false) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        return redirect()->to('app/pengaturan/pengguna')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function delete($id)
    {
        $this->userModel->delete($id);
        return redirect()->to('app/pengaturan/pengguna')->with('success', 'Pengguna berhasil dihapus.');
    }
}