<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel (Enterprise Edition)
 * Mengelola data kredensial pengguna, otentikasi, dan kontrol akses (RBAC).
 * Terintegrasi dengan sistem Unit Scoping (kode_jenjang) dari JenjangModel.
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    // Kolom yang diizinkan untuk manipulasi data
    protected $allowedFields = [
        'id_role', 
        'username', 
        'nama_lengkap', 
        'email', 
        'password_hash', 
        'kode_jenjang', // Relasi ke JenjangModel (SD, SMP, SMA, GLOBAL)
        'is_active',
        'last_login'
    ];

    // Automatic Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Callbacks untuk keamanan password
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    // Aturan Validasi Utama
    protected $validationRules = [
        'id'           => 'permit_empty|integer',
        'nama_lengkap' => 'required|min_length[3]|max_length[255]',
        'id_role'      => 'required|integer',
        'kode_jenjang' => 'required|max_length[20]',
        'username'     => 'required|alpha_dash|min_length[4]|max_length[100]|is_unique[users.username,id,{id}]',
        'email'        => 'required|valid_email|max_length[255]|is_unique[users.email,id,{id}]',
        'is_active'    => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'username' => [
            'is_unique'  => 'Username sudah digunakan, silakan pilih yang lain.',
            'alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan underscore.'
        ],
        'email' => [
            'is_unique'   => 'Alamat email ini sudah terdaftar di sistem.',
            'valid_email' => 'Silakan masukkan format email yang benar.'
        ],
        'kode_jenjang' => [
            'required' => 'Unit kerja (Jenjang) wajib ditentukan untuk pengguna ini.'
        ]
    ];

    // =========================================================================
    // METODE OTENTIKASI & PENCARIAN
    // =========================================================================

    /**
     * Digunakan oleh AuthController untuk proses login.
     * Mencari user berdasarkan Username ATAU Email.
     */
    public function findUserByLoginIdentifier(string $identifier)
    {
        return $this->select('users.*, roles.name AS role_name')
                    ->join('roles', 'roles.id = users.id_role', 'left')
                    ->groupStart()
                        ->where('users.username', $identifier)
                        ->orWhere('users.email', $identifier)
                    ->groupEnd()
                    ->where('users.deleted_at', null)
                    ->first();
    }

    /**
     * Scope Helper: Memfilter berdasarkan Kode Jenjang (Anti-Bocor Data)
     * Admin Unit hanya bisa melihat data unitnya sendiri.
     * Admin Global/Yayasan bisa melihat semua.
     */
    public function filterJenjang(?string $kode_jenjang)
    {
        $globalScopes = ['GLOBAL', 'YAYASAN', 'PUSAT'];
        
        if ($kode_jenjang && !in_array(strtoupper($kode_jenjang), $globalScopes)) {
            $this->where('users.kode_jenjang', $kode_jenjang);
        }
        
        return $this;
    }

    /**
     * Mengambil daftar user beserta nama perannya.
     * Disesuaikan untuk tampilan di Table/Index dengan dukungan Pagination.
     * * Update: Menambahkan parameter Search & Filter Unit agar Pagination valid.
     * * @param mixed $id (Optional) Jika diisi, mengembalikan 1 data spesifik
     * @param int $perPage (Optional) Jika > 0, mengembalikan hasil paginasi
     * @param string|null $search Keyword pencarian
     * @param string|null $filterUnit Filter unit spesifik dari dropdown
     */
    public function getPenggunaWithRole($id = null, int $perPage = 0, string $search = null, string $filterUnit = null)
    {
        // Setup Query Builder
        $this->select('users.*, roles.name as role_name') 
             ->join('roles', 'roles.id = users.id_role', 'left');

        // A. Jika ID spesifik diminta (untuk Edit/Show)
        if ($id !== null) {
            return $this->where('users.id', $id)->first();
        }

        // B. Logika Scoping (Filter per Unit - Session Base)
        // Ambil unit dari session user yang sedang login
        $userJenjang = session()->get('kode_jenjang');
        $this->filterJenjang($userJenjang);

        // C. Filter Tambahan dari Input (Dropdown)
        if ($filterUnit) {
            $this->where('users.kode_jenjang', $filterUnit);
        }

        // D. Filter Pencarian
        if ($search) {
            $this->groupStart()
                 ->like('users.username', $search)
                 ->orLike('users.nama_lengkap', $search)
                 ->orLike('users.email', $search)
                 ->groupEnd();
        }

        // Default Sorting
        $this->orderBy('users.nama_lengkap', 'ASC');

        // E. Logika Pengembalian: Pagination vs All
        if ($perPage > 0) {
            // paginate() akan mengeksekusi query dan mengisi $this->pager
            // Group 'users' penting untuk link pagination di view agar query string halaman terjaga
            return $this->paginate($perPage, 'users'); 
        }

        return $this->findAll();
    }

    // =========================================================================
    // INTERNAL CALLBACKS
    // =========================================================================

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }

        // Bersihkan field virtual (plain password & confirm) agar tidak masuk query SQL
        if (isset($data['data']['password'])) unset($data['data']['password']);
        if (isset($data['data']['pass_confirm'])) unset($data['data']['pass_confirm']);
        if (isset($data['data']['confirm_pass'])) unset($data['data']['confirm_pass']); // Alias lain

        return $data;
    }

    // =========================================================================
    // DYNAMIC RULES (Helper untuk Controller)
    // =========================================================================

    public function getInsertRules(): array
    {
        $rules = $this->validationRules;
        $rules['password']     = 'required|min_length[8]';
        $rules['pass_confirm'] = 'required|matches[password]';
        return $rules;
    }

    public function getUpdateRules(): array
    {
        $rules = $this->validationRules;
        $rules['password']     = 'permit_empty|min_length[8]';
        $rules['pass_confirm'] = 'permit_empty|matches[password]';
        return $rules;
    }
}