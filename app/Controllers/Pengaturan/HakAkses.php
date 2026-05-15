<?php

namespace App\Controllers\Pengaturan;

use App\Controllers\BaseController;
use App\Models\HakAksesModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class HakAkses extends BaseController
{
    /**
     * @var HakAksesModel
     */
    protected $roleModel;

    // Load helper form dan url secara otomatis untuk class ini
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        // Inisialisasi model
        $this->roleModel = new HakAksesModel();
    }

    /**
     * Helper untuk mengambil semua izin yang tersedia dari tabel 'permissions'.
     * @return array
     */
    protected function _getAvailablePermissions(): array
    {
        return $this->roleModel->db->table('permissions')
            ->select('id, permission_key, description') 
            ->orderBy('permission_key', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Menampilkan daftar semua hak akses (roles) dengan hitungan izin.
     * Rute: GET /app/pengaturan/hak_akses
     * @return string
     */
    public function index(): string
    {
        $perPage = 10;
        
        // 1. Ambil data dengan Pagination & Scoping (ditangani Model)
        $roles = $this->roleModel->getAllRolesWithPermissionCount($perPage);
        
        // 2. Integrasi Kartu Statistik (Manual Scope Count)
        // Kita perlu menerapkan filter yang sama dengan model untuk mendapatkan total yang akurat
        $kodeJenjang = session()->get('kode_jenjang');
        if ($kodeJenjang && $kodeJenjang !== 'GLOBAL') {
             $this->roleModel->groupStart()
                 ->where('kode_jenjang', $kodeJenjang)
                 ->orWhere('kode_jenjang', 'GLOBAL')
                 ->groupEnd();
        }
        $totalRoles = $this->roleModel->countAllResults();

        $data = [
            'title'          => 'Pengaturan Hak Akses',
            'current_module' => 'hak_akses',
            'roles'          => $roles,
            'totalRoles'     => $totalRoles,
            'pager'          => $this->roleModel->pager, // Kirim pager ke view
        ];
        
        return view('pengaturan/hak_akses/index', $data);
    }
    
    /**
     * Menampilkan form tambah hak akses baru.
     * Rute: GET /app/pengaturan/hak_akses/new
     * @return string
     */
    public function new(): string
    {
        $data = [
            'title'           => 'Tambah Hak Akses Baru',
            'current_module'  => 'hak_akses',
            'validation'      => \Config\Services::validation(),
            'is_edit'         => false,
            'role'            => ['name' => '', 'description' => ''], 
            'permissions'     => $this->_getAvailablePermissions(),
            'rolePermissions' => [], 
        ];
        
        return view('pengaturan/hak_akses/form', $data); 
    }
    
    /**
     * Menyimpan data hak akses baru dan menyinkronkan izin.
     * Rute: POST /app/pengaturan/hak_akses
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        // 1. Validasi Controller
        $rules = [
            'name'        => 'required|alpha_numeric_space|min_length[3]|is_unique[roles.name]',
            'description' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $post = $this->request->getPost();

        // 2. Siapkan Data dengan Kode Jenjang (Otomatis dari Session)
        $data = [
            'name'         => $post['name'], 
            'description'  => $post['description'] ?? null,
            'kode_jenjang' => session()->get('kode_jenjang') ?? 'GLOBAL', // Wajib untuk Unit Scoping
        ];

        // 3. Insert & Ambil ID
        // Menggunakan insert(..., true) untuk mendapatkan ID yang baru dibuat
        $roleId = $this->roleModel->insert($data, true); 

        if ($roleId) {
            // 4. Sinkronkan Permissions
            $permissions = $post['permissions'] ?? [];
            if (method_exists($this->roleModel, 'syncPermissions')) {
                $this->roleModel->syncPermissions($roleId, $permissions);
            }
            
            return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('success', 'Hak Akses berhasil ditambahkan.');
        } else {
             // Tangkap error dari model (misal validasi kode_jenjang gagal)
             $errors = implode(', ', $this->roleModel->errors());
             return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data: ' . $errors);
        }
    }

    /**
     * Menampilkan form edit hak akses beserta izin yang dimiliki.
     * Rute: GET /app/pengaturan/hak_akses/edit/{id}
     * @param int|string $id ID Role
     * @return string|RedirectResponse
     */
    public function edit($id)
    {
        // Model sudah melakukan filter visibility (return null jika tidak berhak melihat)
        $role = $this->roleModel->getRoleWithPermissions($id);

        if (empty($role)) {
            return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('error', 'Hak Akses tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // PROTEKSI TAMBAHAN: Admin Unit tidak boleh EDIT role GLOBAL
        // (Meskipun mereka bisa melihatnya untuk di-assign ke user)
        $kodeJenjang = session()->get('kode_jenjang');
        if ($kodeJenjang && $kodeJenjang !== 'GLOBAL') {
            if ($role['kode_jenjang'] !== $kodeJenjang) {
                 return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('error', 'Anda tidak memiliki izin untuk mengedit Hak Akses Global/Unit lain.');
            }
        }
        
        $data = [
            'title'           => 'Edit Hak Akses: ' . esc($role['name']), 
            'current_module'  => 'hak_akses',
            'role'            => $role,
            'permissions'     => $this->_getAvailablePermissions(), 
            'rolePermissions' => $role['rolePermissions'] ?? [], 
            'validation'      => \Config\Services::validation(),
            'is_edit'         => true,
        ];

        return view('pengaturan/hak_akses/form', $data);
    }
    
    /**
     * Menyimpan perubahan data hak akses dan menyinkronkan izin.
     * Rute: PUT /app/pengaturan/hak_akses/update/{id}
     * @param int|string $id ID Role
     * @return RedirectResponse
     */
    public function update($id): RedirectResponse
    {
        $oldRole = $this->roleModel->find($id);
        if (empty($oldRole)) {
            return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('error', 'Hak Akses tidak ditemukan.');
        }

        // PROTEKSI UPDATE: Pastikan user berhak mengubah data ini
        $kodeJenjang = session()->get('kode_jenjang');
        if ($kodeJenjang && $kodeJenjang !== 'GLOBAL') {
            if ($oldRole['kode_jenjang'] !== $kodeJenjang) {
                 return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('error', 'Anda tidak memiliki izin untuk mengubah Hak Akses ini.');
            }
        }
        
        $post = $this->request->getPost();

        // Validasi Unique Name (kecuali diri sendiri)
        $roleNameRule = ($oldRole['name'] !== $post['name']) ? "|is_unique[roles.name,id,{$id}]" : '';

        $rules = [
            'name'        => "required|alpha_numeric_space|min_length[3]" . $roleNameRule,
            'description' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }
        
        $data = [
            'id'          => $id, 
            'name'        => $post['name'], 
            'description' => $post['description'] ?? null,
            // PENTING: kode_jenjang TIDAK diupdate, tetap menggunakan yang lama
        ];

        if ($this->roleModel->save($data)) {
            $permissions = $post['permissions'] ?? [];
            if (method_exists($this->roleModel, 'syncPermissions')) {
                $this->roleModel->syncPermissions($id, $permissions);
            }

            return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('success', 'Hak Akses berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data Hak Akses.');
        }
    }

    /**
     * Menghapus hak akses.
     * Rute: DELETE /app/pengaturan/hak_akses/delete/{id}
     * @param int|string $id ID Role
     * @return RedirectResponse
     */
    public function delete($id): RedirectResponse
    {
        $role = $this->roleModel->find($id);
        if (empty($role)) {
            return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('error', 'Hak Akses tidak ditemukan.');
        }

        // PROTEKSI DELETE
        $kodeJenjang = session()->get('kode_jenjang');
        if ($kodeJenjang && $kodeJenjang !== 'GLOBAL') {
            if ($role['kode_jenjang'] !== $kodeJenjang) {
                 return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('error', 'Anda tidak memiliki izin untuk menghapus Hak Akses ini (Global/Unit Lain).');
            }
        }

        if ($this->roleModel->delete($id)) {
            return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('success', 'Hak Akses berhasil dihapus.');
        } else {
            return redirect()->to(site_url('app/pengaturan/hak_akses'))->with('error', 'Gagal menghapus Hak Akses. Mungkin sedang digunakan oleh pengguna.');
        }
    }
}