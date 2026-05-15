<?php namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola relasi antara Hak Akses (Roles) dan Izin (Permissions).
 * Tabel yang digunakan: 'role_permissions'.
 */
class RolePermissionModel extends Model
{
    protected $table      = 'role_permissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true; // Asumsikan Anda memiliki created_at dan updated_at di tabel ini
    
    // Asumsi kolom tabel role_permissions: id, role_id, permission_id, created_at, updated_at
    protected $allowedFields = ['role_id', 'permission_id'];

    /**
     * Menyimpan atau memperbarui semua izin untuk Role tertentu.
     * Ini akan menghapus semua izin yang ada untuk role tersebut, kemudian memasukkan yang baru.
     * @param int $roleId ID Hak Akses (Role)
     * @param array $permissionIds Array of Permission IDs
     * @return bool
     */
    public function updatePermissionsForRole(int $roleId, array $permissionIds)
    {
        // 1. Hapus semua izin yang ada untuk Role ini
        $this->where('role_id', $roleId)->delete();
        
        if (empty($permissionIds)) {
            return true; // Tidak ada izin baru untuk disimpan
        }

        $data = [];
        foreach ($permissionIds as $permId) {
            $data[] = [
                'role_id'       => $roleId,
                'permission_id' => $permId,
                // created_at/updated_at akan diisi otomatis jika useTimestamps = true
            ];
        }

        // 2. Masukkan izin baru
        // gunakan insertBatch() untuk performa yang lebih baik
        return $this->insertBatch($data);
    }
    
    /**
     * Mengambil ID izin yang dimiliki oleh Role tertentu.
     * @param int $roleId ID Hak Akses (Role)
     * @return array Array of permission IDs
     */
    public function getPermissionIdsForRole(int $roleId): array
    {
        $permissions = $this->select('permission_id')
                            ->where('role_id', $roleId)
                            ->findAll();
                            
        // Mengembalikan hanya array berisi nilai ID izin
        return array_column($permissions, 'permission_id');
    }
}