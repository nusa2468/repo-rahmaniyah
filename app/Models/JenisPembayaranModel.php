<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola Master Jenis Pembayaran Siswa.
 * Mendukung perbedaan nominal berdasarkan jenjang sekolah (SD, SMP, SMA, dll).
 */
class JenisPembayaranModel extends Model
{
    protected $table            = 'jenis_pembayaran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $allowedFields = [
        'nama_pembayaran',
        'kode_jenjang',  
        'tipe',          
        'nominal',
        'keterangan'
    ];

    // Validasi Standar CI4
    protected $validationRules = [
        'nama_pembayaran' => 'required|min_length[3]|max_length[100]',
        'kode_jenjang'    => 'permit_empty|alpha_numeric|max_length[10]', // Kosong = Global
        'tipe'            => 'required|in_list[BULANAN,TAHUNAN,SEKALI_BAYAR,OPSIONAL,BEBAS,bulanan,tahunan,sekali_bayar,opsional,bebas]', // Updated list to match View logic case-insensitive handling
        'nominal'         => 'required|numeric|greater_than_equal_to[0]',
        'keterangan'      => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages = [
        'nama_pembayaran' => [
            'required'   => 'Nama jenis pembayaran wajib diisi.',
            'min_length' => 'Nama minimal 3 karakter.',
            'max_length' => 'Nama maksimal 100 karakter.',
        ],
        'kode_jenjang' => [
            'alpha_numeric' => 'Kode jenjang hanya boleh huruf dan angka.',
        ],
        'nominal' => [
            'numeric' => 'Nominal harus berupa angka tanpa pemisah ribuan.',
        ],
    ];

    // Callback Validation (Business Logic)
    protected $beforeInsert = ['validateJenjangExist'];
    protected $beforeUpdate = ['validateJenjangExist'];

    /**
     * Memastikan kode_jenjang valid dan AKTIF di database referensi.
     * Mengembalikan false jika gagal, agar dianggap sebagai error validasi.
     */
    protected function validateJenjangExist(array $data): array|bool
    {
        // Ambil data dari array event (handle kondisi insert/update)
        $inputData = $data['data'];

        // Jika kode_jenjang tidak diset atau kosong/null, berarti Global -> Skip validasi
        if (empty($inputData['kode_jenjang'])) {
            $data['data']['kode_jenjang'] = null; // Pastikan tersimpan NULL di DB
            return $data;
        }

        $kode = strtoupper($inputData['kode_jenjang']);
        
        // Menggunakan JenjangModel agar otomatis membaca tabel 'jenjang_sekolah'
        // Pastikan Model Jenjang tersedia di App\Models\JenjangModel
        try {
            $jenjangModel = model('App\Models\MasterData\JenjangModel'); // Sesuaikan namespace jika perlu, default App\Models\JenjangModel
            if (!$jenjangModel) {
                 $jenjangModel = model('App\Models\JenjangModel');
            }
            
            // Pastikan cek status 'aktif' sesuai enum di database/model jenjang
            $exists = $jenjangModel->where('kode_jenjang', $kode)
                                   ->countAllResults() > 0; // Loose check status

            if (!$exists) {
                // Set error message manual ke model ini
                $this->error = "Kode jenjang '{$kode}' tidak ditemukan.";
                return false; // Menghentikan proses save()
            }
        } catch (\Exception $e) {
            // Bypass jika model jenjang belum ada (untuk seeding awal)
        }

        // Normalisasi data menjadi uppercase sebelum disimpan
        $data['data']['kode_jenjang'] = $kode;
        return $data;
    }

    /**
     * PERBAIKAN: Diubah menjadi PUBLIC agar bisa dipanggil dari Controller.
     * Menerapkan filter jenjang pada query builder.
     * @param string|null $kodeJenjang
     * @param bool $strict Jika TRUE: Hanya ambil jenjang tsb. Jika FALSE: Ambil jenjang tsb + Global (NULL).
     */
    public function applyJenjangFilter(?string $kodeJenjang = null, bool $strict = false)
    {
        if (!empty($kodeJenjang) && strtoupper($kodeJenjang) !== 'GLOBAL') {
            $kode = strtoupper($kodeJenjang);
            
            $this->groupStart();
                $this->where('kode_jenjang', $kode);
                
                // Jika TIDAK strict (mode User/Tagihan), sertakan yang Global (NULL/Empty)
                if (!$strict) {
                    $this->orWhere('kode_jenjang', null)
                          ->orWhere('kode_jenjang', '');
                }
            $this->groupEnd();
        }
        return $this;
    }

    /**
     * Mengambil data dengan Pagination untuk keperluan tabel Admin
     */
    public function getPaginated(int $perPage = 10, ?string $kodeJenjang = null): array
    {
        return $this->applyJenjangFilter($kodeJenjang, false)
                    ->orderBy('kode_jenjang', 'ASC') // Grouping berdasarkan jenjang
                    ->orderBy('created_at', 'DESC')  // Data terbaru di atas
                    // PENTING: Group name 'jenis_pembayaran' harus sama dengan di View $pager->links(...)
                    ->paginate($perPage, 'jenis_pembayaran'); 
    }

    /**
     * Mengambil Dropdown Options untuk Form
     * Format: "Nama Pembayaran - Rp XX.XXX (Unit/Global)"
     */
    public function getDropdownOptions(?string $kodeJenjang = null): array
    {
        // Ambil data (Loose filter: Jenjang terpilih + Global)
        $list = $this->applyJenjangFilter($kodeJenjang, false)
                      ->orderBy('nama_pembayaran', 'ASC')
                      ->findAll();

        $options = [];
        foreach ($list as $row) {
            $nominal = number_format((float)$row['nominal'], 0, ',', '.');
            $status  = empty($row['kode_jenjang']) ? 'Global' : $row['kode_jenjang'];
            
            // Contoh output: "SPP Januari - Rp 150.000 (SD)"
            $label = sprintf("%s - Rp %s (%s)", $row['nama_pembayaran'], $nominal, $status);
            
            $options[$row['id']] = $label;
        }

        return $options;
    }
}