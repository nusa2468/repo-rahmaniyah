<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola data Roles (Hak Akses).
 * Sesuai tabel 'roles'.
 * UPDATED: Mendukung Unit Scoping (kode_jenjang/kode_unit) & Case Insensitive.
 */
class HakAksesModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    // Field yang bisa diisi
    protected $allowedFields    = ['name', 'description', 'kode_jenjang'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    
    // Konfigurasi Tabel Relasi
    protected $rolePermissionsTable = 'role_permissions';
    protected $permissionsTable     = 'permissions'; 

    // Aturan Validasi
    protected $validationRules = [
        'id'           => 'permit_empty|integer',
        'name'         => 'required|alpha_numeric_space|min_length[3]|is_unique[roles.name,id,{id}]',
        'description'  => 'permit_empty|max_length[255]',
        'kode_jenjang' => 'required|max_length[20]',
    ];

    protected $validationMessages = [
        'name' => [
            'required'            => 'Nama hak akses wajib diisi.',
            'alpha_numeric_space' => 'Nama hak akses hanya boleh berisi huruf, angka, dan spasi.',
            'is_unique'           => 'Nama hak akses ini sudah ada, silakan gunakan nama lain.',
        ],
        'kode_jenjang' => [
            'required' => 'Unit kerja (Jenjang) wajib ditentukan.'
        ]
    ];

    /**
     * Helper Internal: Mendapatkan Konteks Unit/Jenjang
     * Prioritas: Session kode_jenjang -> kode_unit
     */
    private function getContext()
    {
        return session()->get('kode_jenjang') ?? session()->get('kode_unit');
    }

    /**
     * Mengambil semua Role beserta hitungan jumlah Izin (Permissions).
     * Digunakan untuk tampilan index.
     * Mendukung Pagination & Unit Scoping.
     */
    public function getAllRolesWithPermissionCount(int $perPage = 0)
    {
        // Gunakan $this builder agar kompatibel dengan fitur Model
        $this->select('roles.id, roles.name, roles.description, roles.kode_jenjang, roles.created_at, roles.updated_at, COUNT(rp.permission_id) AS permission_count');
        $this->join($this->rolePermissionsTable . ' rp', 'rp.role_id = roles.id', 'left');
        
        // --- LOGIKA SCOPING UNIT (UPDATED) ---
        $context = $this->getContext();
        
        // Cek Case Insensitive untuk GLOBAL/YAYASAN
        $isGlobal = in_array(strtoupper($context ?? ''), ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT', 'PUSAT']);

        // Jika bukan GLOBAL, terapkan filter
        if ($context && !$isGlobal) {
            $this->groupStart()
                 ->where('roles.kode_jenjang', $context)
                 ->orWhere('roles.kode_jenjang', 'GLOBAL') // Admin Unit bisa melihat Role Global (read-only logic di view/controller)
                 ->groupEnd();
        }
        
        // Grouping
        $this->groupBy('roles.id, roles.name, roles.description, roles.kode_jenjang, roles.created_at, roles.updated_at'); 
        $this->orderBy('roles.kode_jenjang', 'ASC'); // Global dulu baru Unit
        $this->orderBy('roles.name', 'ASC');

        // Logika Pagination vs All
        if ($perPage > 0) {
            return $this->paginate($perPage);
        }

        return $this->findAll();
    }

    /**
     * Mengambil Role tunggal beserta daftar Permission ID yang dimiliki.
     * Digunakan untuk form edit.
     */
    public function getRoleWithPermissions($roleId)
    {
        // Ambil data role
        $role = $this->find($roleId);
        
        if (!$role) {
            return null;
        }

        // --- CEK SCOPING EDIT (UPDATED) ---
        $context = $this->getContext();
        $isGlobal = in_array(strtoupper($context ?? ''), ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT', 'PUSAT']);

        if ($context && !$isGlobal) {
            // Jika role ini bukan milik unit dia DAN bukan GLOBAL, tolak akses (return null seolah tidak ada).
            // Admin unit biasanya TIDAK BOLEH mengedit Role GLOBAL (harus dicek lagi di Controller), 
            // tapi minimal di sini kita pastikan dia bisa *melihat* (fetch) Role Global atau Role Unitnya sendiri.
            if ($role['kode_jenjang'] !== $context && strtoupper($role['kode_jenjang']) !== 'GLOBAL') {
                return null; 
            }
        }

        // Ambil ID permissions yang terkait
        $permissions = $this->db->table($this->rolePermissionsTable . ' rp')
                                ->select('p.id') // HANYA ambil ID
                                ->join($this->permissionsTable . ' p', 'p.id = rp.permission_id')
                                ->where('rp.role_id', $roleId)
                                ->get()
                                ->getResultArray();
        
        // Simpan array of ID (misal: [1, 5, 8]) yang dimiliki role ini
        $role['rolePermissions'] = array_column($permissions, 'id'); 
        
        return $role;
    }

    /**
     * Menyinkronkan permissions.
     */
    public function syncPermissions($roleId, array $permissionIds)
    {
        // Hapus permission lama
        $this->db->table($this->rolePermissionsTable)
                 ->where('role_id', $roleId)
                 ->delete();

        if (empty($permissionIds)) {
            return true;
        }
        
        $data = [];
        foreach ($permissionIds as $id) {
            $data[] = [
                'role_id'       => $roleId,
                'permission_id' => (int)$id
            ];
        }

        return $this->db->table($this->rolePermissionsTable)->insertBatch($data);
    }
}