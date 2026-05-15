<?php namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengambil data izin (permissions) dari tabel 'permissions'.
 */
class PermissionModel extends Model
{
    protected $table      = 'permissions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    
    // Asumsi kolom tabel permissions adalah: id, permission_key, description, group_name, created_at, updated_at
    protected $allowedFields = ['permission_key', 'description', 'group_name'];

    /**
     * Mengambil daftar izin yang dikelompokkan berdasarkan group_name.
     * Ini digunakan untuk membuat tampilan checklist yang terstruktur di form Role.
     * @return array
     */
    public function getGroupedPermissions()
    {
        // Mengambil semua data izin
        $permissions = $this->findAll();
        $grouped = [];
        
        foreach ($permissions as $perm) {
            // Mengelompokkan berdasarkan group_name. Jika group_name null, dimasukkan ke 'Lain-lain'.
            $groupName = $perm['group_name'] ?? 'Lain-lain';
            
            if (!isset($grouped[$groupName])) {
                $grouped[$groupName] = [];
            }
            
            // Simpan izin dalam bentuk array
            $grouped[$groupName][] = $perm;
        }
        
        return $grouped;
    }
}
