<?php

namespace App\Controllers\Cms;

use App\Controllers\BaseController;

/**
 * Dashboard Controller untuk Modul CMS (Humas/Website).
 * Menangani tampilan statistik dan ringkasan konten dengan dukungan Unit Scoping 
 * yang sinkron dengan JenjangModel & HakAksesModel.
 */
class Dashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Helper: Ambil Daftar Unit dari Database (Referensi JenjangModel)
     * Mengambil data unit yang valid untuk validasi scope user.
     */
    private function getDaftarUnit()
    {
        $daftarUnit = [];
        try {
            if ($this->db->tableExists('jenjang_sekolah')) {
                $query = $this->db->table('jenjang_sekolah')->get();
                foreach ($query->getResultArray() as $row) {
                    $val = $row['kode_jenjang'];
                    $label = $row['nama'] ?? $row['nama_jenjang'] ?? $row['kode_jenjang'];
                    $daftarUnit[$val] = $label;
                }
            }
        } catch (\Exception $e) { }
        
        // Fallback jika DB kosong
        if (empty($daftarUnit)) {
            $daftarUnit = ['TK' => 'TK', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA'];
        }
        return $daftarUnit;
    }

    /**
     * Helper Internal: Mendapatkan Konteks Unit/Jenjang Pengguna
     * Mengadopsi logika dari HakAksesModel::getContext()
     */
    private function getContext()
    {
        return session()->get('kode_jenjang') ?? session()->get('kode_unit');
    }

    public function index()
    {
        // 1. Ambil Data Referensi Unit (Dinamis)
        $daftarUnit = $this->getDaftarUnit();

        // 2. Tentukan Scope User (Hak Akses)
        $sessionJenjang = $this->getContext();
        
        // Cek apakah user adalah Admin Unit (Strict Check)
        // User dianggap Admin Unit jika kode_jenjang-nya ada di daftar unit database.
        // Jika kode_jenjang kosong atau 'YAYASAN'/'GLOBAL', maka dianggap Superadmin.
        $strictUnits = array_keys($daftarUnit); 
        $isUnitAdmin = !empty($sessionJenjang) && in_array(strtoupper($sessionJenjang), $strictUnits);

        // 3. Logika Filter (Hanya berlaku jika BUKAN Unit Admin)
        $filterJenjang = $this->request->getGet('jenjang');
        
        // Scope Query Akhir:
        // - Jika Admin Unit: Paksa filter ke unit dia.
        // - Jika Superadmin: Gunakan filter dari URL (jika ada), atau null (semua).
        $unitFilter = $isUnitAdmin ? $sessionJenjang : $filterJenjang;

        // 4. Hitung Statistik dengan Filter Unit
        $stats = [
            'berita'     => $this->countTable('berita', $unitFilter),
            'pengumuman' => $this->countTable('pengumuman', $unitFilter),
            'agenda'     => $this->countTable('agenda', $unitFilter),
            'album'      => $this->countTable('album_foto', $unitFilter),
        ];

        // 5. Data Grafik & Berita Terbaru
        $chartTrend = $this->getPublicationTrend($unitFilter);
        $recentNews = $this->getRecentNews($unitFilter);

        // Label Unit User untuk UI
        $userUnitLabel = $isUnitAdmin 
            ? ($daftarUnit[$sessionJenjang] ?? $sessionJenjang) 
            : 'GLOBAL / YAYASAN';

        $data = [
            'title'          => 'Dashboard CMS',
            'stats'          => $stats,
            'chart_trend'    => $chartTrend,
            'recent_news'    => $recentNews,
            // Data Pendukung UI & Filter
            'daftarUnit'     => $daftarUnit,
            'sessionJenjang' => $sessionJenjang,
            'isUnitAdmin'    => $isUnitAdmin,
            'filterJenjang'  => $filterJenjang,
            'user_unit'      => $userUnitLabel
        ];

        return view('cms/dashboard', $data);
    }

    /**
     * Helper: Menghitung jumlah baris tabel dengan aman dan filter unit optional
     */
    private function countTable($table, ?string $unitFilter = null)
    {
        if ($this->db->tableExists($table)) {
            $builder = $this->db->table($table);
            
            // Terapkan filter unit jika ada, bukan null, dan tabel memiliki kolom yang sesuai
            if (!empty($unitFilter) && $this->db->fieldExists('kode_jenjang', $table)) {
                $builder->where('kode_jenjang', $unitFilter);
            }

            // Fix Error 1054: Pastikan status ada sebelum filter status (opsional)
            if ($this->db->fieldExists('status', $table)) {
                // Opsional: Hitung hanya yang published? 
                // $builder->where('status', 'published');
            }

            return $builder->countAllResults();
        }
        return 0;
    }

    /**
     * Helper: Mendapatkan data chart 6 bulan terakhir
     */
    private function getPublicationTrend(?string $unitFilter = null)
    {
        $labels = [];
        $data   = [];

        // Loop 6 bulan ke belakang
        for ($i = 5; $i >= 0; $i--) {
            $dateObj   = new \DateTime("-$i months");
            $monthName = $dateObj->format('M'); // Jan, Feb, ...
            $yearMonth = $dateObj->format('Y-m');

            $count = 0;
            if ($this->db->tableExists('berita')) {
                $builder = $this->db->table('berita');
                
                // Pastikan kolom created_at ada
                if ($this->db->fieldExists('created_at', 'berita')) {
                    $builder->like('created_at', $yearMonth, 'after');

                    // Filter Unit
                    if (!empty($unitFilter) && $this->db->fieldExists('kode_jenjang', 'berita')) {
                        $builder->where('kode_jenjang', $unitFilter);
                    }

                    $count = $builder->countAllResults();
                }
            }
            
            $labels[] = $monthName;
            $data[]   = $count;
        }

        return [
            'labels' => $labels,
            'data'   => $data
        ];
    }

    /**
     * Helper: Mendapatkan 5 berita terakhir
     */
    private function getRecentNews(?string $unitFilter = null)
    {
        if ($this->db->tableExists('berita')) {
            $builder = $this->db->table('berita');

            // Cek kolom yang tersedia untuk menghindari error 'Unknown column'
            $columns = ['id', 'judul', 'created_at'];
            // Tambahkan status jika ada (Fix Error sebelumnya)
            if ($this->db->fieldExists('status', 'berita')) {
                $columns[] = 'status';
            }
            
            $builder->select(implode(',', $columns));
            
            // Filter Unit
            if (!empty($unitFilter) && $this->db->fieldExists('kode_jenjang', 'berita')) {
                $builder->where('kode_jenjang', $unitFilter);
            }

            // Pastikan created_at ada untuk sorting
            if ($this->db->fieldExists('created_at', 'berita')) {
                $builder->orderBy('created_at', 'DESC');
            }

            return $builder->limit(5)
                           ->get()
                           ->getResultArray();
        }
        return [];
    }
}