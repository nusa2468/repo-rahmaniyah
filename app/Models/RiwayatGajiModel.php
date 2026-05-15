<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Exceptions\PageNotFoundException;

class RiwayatGajiModel extends Model
{
    // Konfigurasi Model
    protected $table         = 'riwayat_gaji';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at'; 
    protected $updatedField  = 'updated_at'; 

    // Daftar kolom yang diizinkan untuk diisi
    protected $allowedFields = [
        'id_guru', 
        'bulan', 
        'tahun', 
        'total_pendapatan', 
        'total_potongan', 
        'gaji_bersih', 
        'detail_json', // FIX: Menggunakan 'detail_json' sesuai konfigurasi Anda
        'status_pembayaran', 
        'tanggal_pembayaran' 
    ];

    /**
     * Men-generate atau memperbarui data gaji untuk semua guru aktif pada periode tertentu.
     * Metode ini sudah terlihat cukup lengkap.
     * @param int $bulan Bulan (1-12)
     * @param int $tahun Tahun (YYYY)
     * @return bool
     */
    public function generateGajiUntukSemuaGuru(int $bulan, int $tahun): bool
    {
        // 1. Inisialisasi Model yang Dibutuhkan
        // ASUMSI: GuruModel dan GajiGuruModel sudah terdefinisi dan berfungsi
        $guruModel = model('GuruModel');
        $gajiGuruModel = model('GajiGuruModel'); 
        
        $semua_guru = $guruModel->getActiveTeachers(); 

        if (empty($semua_guru)) {
            log_message('error', 'GENERATE GAGAL: Tidak ada guru dengan status "aktif" ditemukan.');
            return false; 
        }
        
        $jumlah_guru_diproses = count($semua_guru);
        log_message('info', 'GENERATE DIMULAI: Memproses ' . $jumlah_guru_diproses . ' guru aktif.');

        $this->db->transBegin(); // Mulai transaksi
        $inserted_count = 0;

        try {
            foreach ($semua_guru as $guru) {
                $guru_id = $guru['id'];

                // 2. Ambil Komponen Gaji Guru
                $komponen_gaji = $gajiGuruModel->getGajiByGuru($guru_id);

                if (empty($komponen_gaji)) {
                    log_message('warning', 'GENERATE GAGAL: Guru ID ' . $guru_id . ' (' . ($guru['nama_lengkap'] ?? 'Nama Guru Tidak Diketahui') . ') TIDAK memiliki komponen gaji. Gaji akan dihitung Rp 0.');
                }
                
                $total_pendapatan = 0;
                $total_potongan = 0;
                $detail_komponen = ['pendapatan' => [], 'potongan' => []];

                // 3. Hitung Total Gaji
                foreach ($komponen_gaji as $komponen) {
                    // Pastikan field yang dipanggil ada, gunakan operator null coalescing
                    $nominal = (float) ($komponen['jumlah_set'] ?? 0);
                    $tipe = strtolower($komponen['tipe'] ?? '');

                    $item = [
                        'nama' => $komponen['nama_komponen'] ?? 'Komponen Gaji', 
                        'jumlah' => $nominal,
                    ];

                    if ($tipe === 'pendapatan') {
                        $total_pendapatan += $nominal;
                        $detail_komponen['pendapatan'][] = $item;
                    } else if ($tipe === 'potongan') {
                        $total_potongan += $nominal;
                        $detail_komponen['potongan'][] = $item;
                    }
                }
                
                $gaji_bersih = $total_pendapatan - $total_potongan;

                $data = [
                    'id_guru'          => $guru_id,
                    'bulan'            => $bulan,
                    'tahun'            => $tahun,
                    'total_pendapatan' => $total_pendapatan,
                    'total_potongan'   => $total_potongan,
                    'gaji_bersih'      => $gaji_bersih,
                    
                    // FIX: Menggunakan 'detail_json' dan encoding JSON
                    'detail_json'      => json_encode($detail_komponen), 
                    
                    'status_pembayaran' => 'pending', 
                ];

                // 4. Upsert (Update atau Insert)
                $existing = $this->where('id_guru', $guru_id)
                                 ->where('bulan', $bulan)
                                 ->where('tahun', $tahun)
                                 ->first();

                $result = false;
                if ($existing) {
                    $this->update($existing['id'], $data);
                    $result = $existing['id'];
                    log_message('info', 'GENERATE: UPDATE data gaji (ID Riwayat: ' . $result . ') untuk Guru ID: ' . $guru_id);
                } else {
                    $result = $this->insert($data, true); 
                    if ($result !== false) {
                       $inserted_count++;
                       log_message('info', 'GENERATE: INSERT data gaji (ID Riwayat: ' . $result . ') untuk Guru ID: ' . $guru_id);
                    }
                }

                // 5. Pengecekan Kegagalan Insert/Update Ekstrem
                if ($result === false || $result === null) {
                    // Ambil error dari Model
                    $error = $this->errors() ? json_encode($this->errors()) : 'Error tidak diketahui.';
                    throw new \Exception("Gagal menyimpan data gaji ke database untuk Guru ID: " . $guru_id . ". Detail Error: " . $error);
                }
            }
            
            $this->db->transCommit(); 
            log_message('info', 'GENERATE SELESAI: Total guru diproses: ' . $jumlah_guru_diproses . '. Total data baru dimasukkan: ' . $inserted_count);
            return true;

        } catch (\Exception $e) {
            $this->db->transRollback(); 
            log_message('error', 'TRANSAKSI GAGAL: ' . $e->getMessage() . ' | Query terakhir: ' . $this->db->getLastQuery());
            return false;
        }
    }
    
    /**
     * Mengambil riwayat gaji yang sudah digenerate dengan detail guru untuk daftar riwayat.
     * @return array
     */
    public function getRiwayatDetail(): array
    {
        // ASUMSI: Kolom join 'guru.id' vs 'riwayat_gaji.id_guru' sudah benar.
        return $this->select('riwayat_gaji.*, guru.nama_lengkap, guru.nip')
            ->join('guru', 'guru.id = riwayat_gaji.id_guru')
            ->orderBy('tahun', 'DESC')
            ->orderBy('bulan', 'DESC')
            ->orderBy('guru.nama_lengkap', 'ASC')
            ->findAll(); 
    }

    /**
     * Mengambil detail slip gaji lengkap (Riwayat dan Data Guru) untuk dicetak.
     * FIX KRITIS: Mengganti getSlipById menjadi getSlipGajiDetail
     * @param int $id_riwayat ID Riwayat Gaji
     * @return array|null
     */
    public function getSlipGajiDetail(int $id_riwayat): ?array
    {
        // FIX: Menggunakan 'id_guru' untuk join field yang ada di tabel riwayat_gaji
        $data = $this->select('riwayat_gaji.*, guru.nama_lengkap, guru.nip')
                     ->join('guru', 'guru.id = riwayat_gaji.id_guru') 
                     ->where('riwayat_gaji.id', $id_riwayat)
                     ->first();

        if (empty($data)) {
            return null;
        }

        // Dekode data JSON dari kolom detail_json
        $detail_json = $data['detail_json'] ?? '{"pendapatan":[], "potongan":[]}';
        
        $data['detail_komponen'] = json_decode($detail_json, true);

        // Hapus kolom JSON mentah setelah diproses
        unset($data['detail_json']);

        return $data;
    }
}