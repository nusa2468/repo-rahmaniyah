<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model Settings
 * Mengelola konfigurasi identitas sekolah per unit (Multi-Jenjang)
 * Mendukung Soft Deletes sesuai dengan struktur migrasi terbaru.
 */
class SettingsModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    
    // Fitur Soft Deletes
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';

    // Fitur Timestamps
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // PERBAIKAN: Tambahkan 'deleted_at' agar fitur restore (un-delete) manual berfungsi
    protected $allowedFields    = ['jenjang', 'key', 'value', 'deleted_at'];

    /**
     * Mengambil semua pengaturan berdasarkan jenjang tertentu dan mengubahnya menjadi format array asosiatif.
     * Digunakan oleh Landing Page Unit untuk memuat Visi, Misi, dan Identitas Unit.
     * @param string $jenjang ('Global', 'SD', 'SMP', 'SMA')
     * @return array Output: ['nama_sekolah' => 'SMA IT Solusi', 'motto' => '...', ...]
     */
    public function getSettingsAsArray(string $jenjang = 'Global'): array
    {
        // Mencari data berdasarkan jenjang yang diberikan
        $settings = $this->where('jenjang', $jenjang)->findAll();
        
        if (empty($settings)) {
            return [];
        }

        // Mengubah array multidimensi menjadi array asosiatif key => value
        return array_column($settings, 'value', 'key');
    }

    /**
     * Update atau Insert setting per key per jenjang secara cerdas.
     * Mencegah duplikasi data dengan memeriksa keberadaan kombinasi jenjang + key.
     * @param string $jenjang Unit sekolah terkait
     * @param string $key Kunci pengaturan (e.g. 'motto')
     * @param mixed $value Nilai pengaturan
     * @return bool|int|string
     */
    public function updateSetting(string $jenjang, string $key, $value)
    {
        // Mencari data yang ada termasuk data yang sudah di-soft-delete untuk di-restore jika perlu
        $existing = $this->withDeleted()->where(['jenjang' => $jenjang, 'key' => $key])->first();

        if ($existing) {
            $data = [
                'value'      => $value,
                'deleted_at' => null // Me-restore jika sebelumnya pernah di-soft-delete
            ];
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert([
                'jenjang' => $jenjang,
                'key'     => $key,
                'value'   => $value
            ]);
        }
    }
}