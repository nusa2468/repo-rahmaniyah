<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// =================================================================
// 1. HALAMAN PUBLIK (Landing Page Multi-Jenjang)
// =================================================================
// Rute ini harus berada di luar group filter 'auth' agar bisa diakses tanpa login

// Portal Utama Yayasan (Pemilihan Unit)
$routes->get('/', 'PublicController::portal');

// Landing Page Spesifik Jenjang
$routes->get('sd', 'PublicController::index/SD');
$routes->get('smp', 'PublicController::index/SMP');
$routes->get('sma', 'PublicController::index/SMA');

// --- FIX 404 PORTAL UNIT: Rute Dinamis untuk Dropdown Navbar ---
$routes->get('portal/unit/(:segment)', 'PublicController::index/$1');
// ---------------------------------------------------------------

// Berita Detail per Jenjang
$routes->get('sd/berita/(:any)', 'PublicController::beritaDetail/SD/$1');
$routes->get('smp/berita/(:any)', 'PublicController::beritaDetail/SMP/$1');
$routes->get('sma/berita/(:any)', 'PublicController::beritaDetail/SMA/$1');

// Berita Global (Fallback)
$routes->get('berita/(:segment)', 'PublicController::beritaDetail/Global/$1');

// =================================================================
// 2. OTENTIKASI (Login/Logout)
// =================================================================
$routes->get('login', 'AuthController::index'); 
$routes->post('login', 'AuthController::login'); 

// FIX: Logout diarahkan ke AuthController yang me-redirect ke Landing Page ('/')
$routes->get('logout', 'AuthController::logout'); 

// =================================================================
// 2. PORTAL PENGGUNA (Tanpa Prefix 'app')
// =================================================================
$routes->group('portal', ['namespace' => 'App\Controllers\Portal'], static function ($routes) {
    
    // PORTAL PPDB
    $routes->group('ppdb', static function ($routes) {
        // --- FIX 404: Tambahkan rute '/' agar base_url('portal/ppdb') bisa diakses ---
        $routes->get('/', 'PortalPpdbController::index');
        // -----------------------------------------------------------------------------
        $routes->get('home', 'PortalPpdbController::index');
        $routes->get('login', 'PortalPpdbController::login');
        $routes->post('login', 'PortalPpdbController::attemptLogin');
        $routes->get('register', 'PortalPpdbController::register'); 
        $routes->post('submit', 'PortalPpdbController::submit');
        $routes->get('success/(:any)', 'PortalPpdbController::sukses/$1');
        $routes->get('cek-status', 'PortalPpdbController::cekStatus');
        $routes->get('logout', 'PortalPpdbController::logout');
    });

    // PORTAL AFILIASI
    $routes->group('affiliated', static function ($routes) {
        $routes->get('/', 'PortalAffiliatedController::index');
        $routes->get('home', 'PortalAffiliatedController::index');
    
    // --- PERBAIKAN LOGIN ---
        $routes->get('login', 'PortalAffiliatedController::login');         // Untuk menampilkan Form
        $routes->post('login', 'PortalAffiliatedController::attemptLogin'); // Untuk Submit Form (Ganti 'auth' jadi 'login')
    // -----------------------

        $routes->get('dashboard', 'PortalAffiliatedController::dashboard');
        $routes->get('logout', 'PortalAffiliatedController::logout');
    
    // --- OPSIONAL: PERBAIKAN REGISTER BIAR LEBIH RAPI ---
        $routes->get('register', 'PortalAffiliatedController::register');
        $routes->post('register', 'PortalAffiliatedController::submitRegistration'); // Disarankan pakai 'register' saja daripada 'register/submit'
    });

    // PORTAL SISWA
    $routes->group('siswa', ['namespace' => 'App\Controllers\Portal'], function($routes) {
        // Redirect root folder ke login
        $routes->get('/', 'PortalSiswaController::login');

        // Halaman Login
        $routes->get('login', 'PortalSiswaController::login');
        
        // Proses Login (POST)
        $routes->post('login/auth', 'PortalSiswaController::attemptLogin');
        
        // Dashboard Area
        $routes->get('dashboard', 'PortalSiswaController::dashboard');
        
        // --- RUTE BARU (UNTUK MENU SIDEBAR) ---
        $routes->get('jadwal', 'PortalSiswaController::jadwal');     // Menu Jadwal
        $routes->get('nilai', 'PortalSiswaController::nilai');       // Menu Nilai & Rapor
        $routes->get('keuangan', 'PortalSiswaController::keuangan'); // Menu Keuangan
        $routes->get('profil', 'PortalSiswaController::profil');     // Menu Profil
        // ---------------------------------------

        $routes->get('logout', 'PortalSiswaController::logout');
    });
    // PORTAL PEGAWAI (GURU & STAFF) - [NEW]
    $routes->group('pegawai', ['namespace' => 'App\Controllers\Portal'], function($routes) {
        // Redirect root folder ke login
        $routes->get('/', 'PortalPegawaiController::login');

        // Authentication
        $routes->get('login', 'PortalPegawaiController::login');
        $routes->post('login', 'PortalPegawaiController::attemptLogin');
        $routes->get('logout', 'PortalPegawaiController::logout');

        // Dashboard
        $routes->get('dashboard', 'PortalPegawaiController::index'); // atau 'dashboard'

        // Akademik (Guru)
        $routes->get('jadwal', 'PortalPegawaiController::jadwal');
        $routes->get('nilai', 'PortalPegawaiController::nilai');
        $routes->get('siswa', 'PortalPegawaiController::siswa');

        // Kepegawaian (Umum)
        $routes->get('presensi', 'PortalPegawaiController::presensi');
        $routes->get('keuangan', 'PortalPegawaiController::keuangan'); // Slip Gaji

        // Profil & Akun
        $routes->get('profil', 'PortalPegawaiController::profil');
        $routes->post('update-password', 'PortalPegawaiController::updatePassword');
    });
});
// =================================================================
// 4. AREA ADMIN - PREFIX '/app' - FILTER 'auth'
// =================================================================
$routes->group('app', ['filter' => 'auth', 'namespace' => 'App\Controllers'], static function ($routes) {

    $routes->get('/', 'Home::index');

    /**
     * MODUL PEMBELAJARAN (NEW REFACTORED STRUCTURE)
     * Alur Kerja: Silabus -> RPP -> Bahan Ajar -> Bank Soal
     */
    $routes->group('pembelajaran', ['namespace' => 'App\Controllers\Pembelajaran'], static function ($routes) {
        $routes->get('/', 'DashboardPembelajaran::index', ['as' => 'pembelajaran_dashboard_root']); 
        $routes->get('dashboard_pembelajaran', 'DashboardPembelajaran::index', ['as' => 'pembelajaran_dashboard']); 
        
        // 1. MODUL SILABUS
        $routes->group('silabus', static function ($routes) {
            $routes->get('/', 'SilabusController::index');
            $routes->get('new', 'SilabusController::new');
            $routes->post('create', 'SilabusController::create');
            
            // --- ROUTE KHUSUS BULK EDITOR ---
            $routes->post('create-bulk', 'SilabusController::createBulk');
            $routes->post('update-bulk/(:num)', 'SilabusController::updateBulk/$1');
            $routes->match(['post', 'put'], 'update-bulk/(:num)', 'SilabusController::updateBulk/$1'); // Added match for PUT support
            // --------------------------------

            // [UPDATE] Rute Print Silabus (Cetak PDF)
            // Diletakkan sebelum (:num) agar tidak dianggap sebagai ID
            $routes->get('print/(:num)', 'SilabusController::print/$1');

            $routes->get('(:num)', 'SilabusController::show/$1');
            $routes->get('edit/(:num)', 'SilabusController::edit/$1');
            $routes->match(['get', 'post', 'put'], 'update/(:num)', 'SilabusController::update/$1'); 
            $routes->match(['get', 'post', 'delete'], 'delete/(:num)', 'SilabusController::delete/$1'); 
            $routes->match(['get', 'post'], 'generate', 'SilabusController::generate');
        });

        // 2. MODUL RPP / MODUL AJAR
        $routes->group('rpp', static function ($routes) {
            $routes->get('/', 'RppController::index');
            $routes->get('new', 'RppController::new');
            $routes->post('create', 'RppController::create');
            
            // --- ROUTE KHUSUS BULK RPP (CRITICAL FIX) ---
            $routes->post('create-bulk', 'RppController::createBulk');
            // --------------------------------------------

            $routes->get('(:num)', 'RppController::show/$1');
            $routes->get('edit/(:num)', 'RppController::edit/$1'); // Target utama tombol Edit
            $routes->match(['get', 'post', 'put'], 'update/(:num)', 'RppController::update/$1'); // FIXED: Tambah GET agar bisa redirect
            $routes->match(['get', 'post', 'delete'], 'delete/(:num)', 'RppController::delete/$1');
            
            $routes->post('generate', 'RppController::generate'); // Generate Massal dari Silabus
            $routes->match(['get', 'post'], 'generate', 'RppController::generate'); // Fallback match
            $routes->post('generate_massal', 'RppController::generateMassal');
            $routes->get('print/(:num)', 'RppController::print/$1'); // Print RPP
        });

        // 3. MODUL BAHAN AJAR
        $routes->group('bahan-ajar', static function ($routes) {
            $routes->get('/', 'BahanAjarController::index');
            $routes->get('new', 'BahanAjarController::new');
            $routes->post('create', 'BahanAjarController::create');
            $routes->get('(:num)', 'BahanAjarController::show/$1');
            $routes->get('edit/(:num)', 'BahanAjarController::edit/$1'); 
            $routes->match(['get', 'post', 'put'], 'update/(:num)', 'BahanAjarController::update/$1'); 
            $routes->match(['get', 'post', 'delete'], 'delete/(:num)', 'BahanAjarController::delete/$1');
            $routes->match(['get', 'post'], 'generate', 'BahanAjarController::generate'); // Single Generate
            // --- ROUTE BARU: Generate Massal ---
            $routes->post('generate_massal', 'BahanAjarController::generateMassal');
            $routes->post('generate/(:num)', 'BahanAjarController::generate/$1'); // Generate specific
        });

        // 4. MODUL BANK SOAL
        $routes->group('bank-soal', static function ($routes) {
            $routes->get('/', 'BankSoalController::index');
            $routes->get('new', 'BankSoalController::new');
            
            // --- NEW: Rute Simpan Massal (Bulk Insert) ---
            $routes->post('save-bulk', 'BankSoalController::saveBulk'); 
            // ---------------------------------------------

            $routes->post('create', 'BankSoalController::create');
            $routes->get('(:num)', 'BankSoalController::show/$1');
            $routes->get('edit/(:num)', 'BankSoalController::edit/$1'); // Target utama tombol Edit
            $routes->match(['get', 'post', 'put'], 'update/(:num)', 'BankSoalController::update/$1'); // FIXED: Tambah GET agar bisa redirect
            $routes->match(['get', 'post', 'delete'], 'delete/(:num)', 'BankSoalController::delete/$1');
            $routes->match(['get', 'post'], 'generate', 'BankSoalController::generate');
            $routes->post('generate_massal', 'BankSoalController::generateMassal');
        });

        // 5. MODUL EVALUASI
        $routes->group('evaluasi-belajar', static function ($routes) {
            $routes->get('/', 'EvaluasiBelajarController::index');
            $routes->get('new', 'EvaluasiBelajarController::new');
            $routes->post('create', 'EvaluasiBelajarController::create');
            $routes->get('(:num)', 'EvaluasiBelajarController::show/$1');
            $routes->get('edit/(:num)', 'EvaluasiBelajarController::edit/$1'); 
            
            // --- FIX 404: Pastikan PUT/POST Update ditangani ---
            $routes->match(['get', 'post', 'put'], 'update/(:num)', 'EvaluasiBelajarController::update/$1'); 
            
            $routes->match(['get', 'post', 'delete'], 'delete/(:num)', 'EvaluasiBelajarController::delete/$1');
            $routes->match(['get', 'post'], 'generate', 'EvaluasiBelajarController::generate');
        });

        // --- ALIAS ROUTE (Support dengan prefix 'app') ---
        $routes->group('evaluasi', static function ($routes) {
            $routes->match(['post', 'put'], 'update/(:num)', 'EvaluasiBelajarController::update/$1');
            $routes->get('edit/(:num)', 'EvaluasiBelajarController::edit/$1');
            $routes->get('delete/(:num)', 'EvaluasiBelajarController::delete/$1');
        });
    });
    // --- MASTER DATA ---
    $routes->group('masterdata', ['namespace' => 'App\Controllers\MasterData'], static function ($routes) {
        $routes->get('/', 'DashboardMasterData::index', ['as' => 'masterdata_dashboard_root']); 
        $routes->get('dashboard', 'DashboardMasterData::index', ['as' => 'masterdata_dashboard']); 
        
        // --- SUB-MODUL: Jenjang Sekolah ---    
        $routes->group('jenjang', static function ($routes) {
            $routes->get('/', 'Jenjang::index');
            $routes->get('dashboard', 'Jenjang::dashboard');
            $routes->get('new', 'Jenjang::new');
            $routes->get('edit/(:num)', 'Jenjang::edit/$1');
    
            // SINKRONISASI DENGAN CONTROLLER
            $routes->post('create', 'Jenjang::save');
    
            // PERBAIKAN: Tambahkan rute save dengan parameter ID
            $routes->post('save', 'Jenjang::save'); 
            $routes->post('save/(:num)', 'Jenjang::save/$1'); // <--- TAMBAHKAN BARIS INI
    
            // Alias update
            $routes->post('update/(:num)', 'Jenjang::save'); 
            $routes->put('update/(:num)', 'Jenjang::save'); 
    
            $routes->get('delete/(:num)', 'Jenjang::delete/$1');
        });
        // --- SUB-MODUL: Indentitas Sekolah ---
        $routes->group('identitas', static function ($routes) {
            $routes->get('/', 'IdentitasSekolah::index');          // URL: domain.com/masterdata/indentitas
            $routes->post('update', 'IdentitasSekolah::update'); // URL: domain.com/masterdata/indentitas/update
        });
        // --- SUB-MODUL: Organisasi (SINKRONISASI 100%) ---
        $routes->group('organisasi', static function ($routes) {
            $routes->get('/', 'Organisasi::index');            // Daftar Tabel
            $routes->get('visual', 'Organisasi::visual');      // Bagan Hirarki
            $routes->get('new', 'Organisasi::new');            // Form Tambah (Fix 404)
            $routes->post('save', 'Organisasi::save');         // Simpan (Insert/Update)
            $routes->get('edit/(:num)', 'Organisasi::edit/$1'); // Form Edit
            $routes->get('delete/(:num)', 'Organisasi::delete/$1'); // Hapus
        });
        // --- SUB-MODUL: Jabatan (SINKRONISASI 100%) ---
        $routes->group('jabatan', static function ($routes) {
            $routes->get('/', 'Jabatan::index');               // Daftar Master Jabatan
            $routes->get('new', 'Jabatan::new');               // Form Tambah Jabatan
            $routes->post('save', 'Jabatan::save');            // Simpan (Insert/Update)
            $routes->get('edit/(:num)', 'Jabatan::edit/$1');    // Form Edit Jabatan
            $routes->get('delete/(:num)', 'Jabatan::delete/$1'); // Hapus Jabatan
        });
        // --- SUB-MODUL: Jurusan (SINKRONISASI 100%) ---
        $routes->group('jurusan', static function ($routes) {
            $routes->get('/', 'Jurusan::index');               // List Jurusan (Filtered by Unit)
            $routes->get('form', 'Jurusan::form');             // Form Tambah
            $routes->get('form/(:num)', 'Jurusan::form/$1');   // Form Edit
            $routes->post('save', 'Jurusan::save');            // Proses Simpan (Insert/Update)
            // Route untuk memproses tambah data
            $routes->post('store', 'Jurusan::store');
            // Route untuk memproses update data (sesuaikan dengan URL di form Anda)
            $routes->post('update/(:num)', 'Jurusan::update/$1');
            // Delete: Support POST (Form) & GET (Link fallback)
            $routes->post('delete/(:num)', 'Jurusan::delete/$1');
            $routes->get('delete/(:num)', 'Jurusan::delete/$1');
        });
        // --- SUB-MODUL: Tahunajaran ---
        $routes->group('tahunajaran', static function ($routes) {
            $routes->get('/', 'TahunAjaran::index', ['as' => 'ta_index']);
            $routes->get('new', 'TahunAjaran::new', ['as' => 'ta_new']);
            
            // Perbaikan Create: Mendukung URL /create agar sesuai dengan View Anda
            $routes->post('create', 'TahunAjaran::create', ['as' => 'ta_create']); 
            $routes->post('/', 'TahunAjaran::create'); 
            
            $routes->get('(:num)', 'TahunAjaran::show/$1', ['as' => 'ta_show']); 
            $routes->get('edit/(:num)', 'TahunAjaran::edit/$1', ['as' => 'ta_edit']);
            
            // Perbaikan Update: Mendukung PUT (Spoofing) & POST (Fallback)
            $routes->put('update/(:num)', 'TahunAjaran::update/$1', ['as' => 'ta_update']); 
            $routes->post('update/(:num)', 'TahunAjaran::update/$1'); 

            // Perbaikan Hapus: Mendukung DELETE (Spoofing) & GET (Fallback Link <a>)
            $routes->delete('delete/(:num)', 'TahunAjaran::delete/$1', ['as' => 'ta_delete']);
            $routes->get('delete/(:num)', 'TahunAjaran::delete/$1'); 
        });
        // ============================================================
        // ROUTES PEGAWAI (UPDATED WITH HISTORY HANDLERS)
        // ============================================================
        $routes->group('pegawai', static function ($routes) {
            $routes->get('/', 'Pegawai::index', ['as' => 'pegawai_index']);
            $routes->get('new', 'Pegawai::new', ['as' => 'pegawai_new']);
            $routes->post('create', 'Pegawai::create', ['as' => 'pegawai_create']);
            $routes->get('(:num)', 'Pegawai::show/$1', ['as' => 'pegawai_show']); 
            $routes->get('edit/(:num)', 'Pegawai::edit/$1', ['as' => 'pegawai_edit']);
            $routes->put('update/(:num)', 'Pegawai::update/$1', ['as' => 'pegawai_update']);
            $routes->delete('delete/(:num)', 'Pegawai::delete/$1', ['as' => 'pegawai_delete']);
            
            // Dokumen Handler
            $routes->post('upload_dokumen', 'Pegawai::upload_dokumen', ['as' => 'pegawai_upload']);
            $routes->get('download_dokumen/(:num)', 'Pegawai::download_dokumen/$1', ['as' => 'pegawai_download']);
            $routes->delete('delete_dokumen/(:num)', 'Pegawai::delete_dokumen/$1', ['as' => 'pegawai_delete_doc']);
            
            // --- Fitur Baru: Riwayat Pendidikan ---
            $routes->post('save_pendidikan', 'Pegawai::save_pendidikan', ['as' => 'pegawai_save_pendidikan']);
            $routes->get('delete_pendidikan/(:num)', 'Pegawai::delete_pendidikan/$1', ['as' => 'pegawai_delete_pendidikan']);
            
            // --- Fitur Baru: Riwayat Karir / Kepegawaian ---
            $routes->post('save_kepegawaian', 'Pegawai::save_kepegawaian', ['as' => 'pegawai_save_kepegawaian']);
            $routes->get('delete_kepegawaian/(:num)', 'Pegawai::delete_kepegawaian/$1', ['as' => 'pegawai_delete_kepegawaian']);
        });
        /// --- SUB-MODUL: SISWA (OPTIMIZED & SYNCHRONIZED) ---
        $routes->group('siswa', static function ($routes) {
            // 1. Menampilkan Daftar Siswa
            // Digunakan di Controller: $this->redirectBaseUrl
            $routes->get('/', 'Siswa::index', ['as' => 'siswa_index']);
    
            // 2. Form Tambah Siswa
            $routes->get('new', 'Siswa::new', ['as' => 'siswa_new']);
    
            // 3. Proses Simpan Data Baru
            // Mendukung POST murni sesuai standar RESTful
            $routes->post('/', 'Siswa::create', ['as' => 'siswa_create']);
            $routes->post('create', 'Siswa::create'); 
    
            // 4. Detail Siswa (Method show($id) di Controller)
            // Ditambahkan segmen 'show/' agar sesuai dengan request: app/masterdata/siswa/show/3
            $routes->get('show/(:num)', 'Siswa::show/$1', ['as' => 'siswa_show']);
    
            // 5. Form Edit Siswa (Method edit($id) di Controller)
            $routes->get('edit/(:num)', 'Siswa::edit/$1', ['as' => 'siswa_edit']);
    
            // 6. Proses Update Data
            // Menggunakan match POST/PUT agar kompatibel dengan Method Spoofing <input type="hidden" name="_method" value="PUT">
            $routes->match(['post', 'put'], 'update/(:num)', 'Siswa::update/$1', ['as' => 'siswa_update']);
    
            // 7. Proses Hapus Data
            // Ditambahkan 'get' agar penghapusan via URL/Tombol simpel tetap bekerja jika diperlukan
            $routes->match(['get', 'post', 'delete'], 'delete/(:num)', 'Siswa::delete/$1', ['as' => 'siswa_delete']);
        });
        // --- SUB-MODUL: kelas (FIXED 404) ---
        $routes->group('kelas', static function ($routes) {
            $routes->get('/', 'Kelas::index', ['as' => 'kelas_index']); 
            $routes->get('new', 'Kelas::new', ['as' => 'kelas_new']);
            $routes->post('create', 'Kelas::create', ['as' => 'kelas_create']);      
            
            // --- FIX UTAMA: Menambahkan Rute Show/Detail ---
            $routes->get('show/(:num)', 'Kelas::show/$1', ['as' => 'kelas_show']);
            // -----------------------------------------------
            
            $routes->get('edit/(:num)', 'Kelas::edit/$1', ['as' => 'kelas_edit']);
            $routes->put('update/(:num)', 'Kelas::update/$1', ['as' => 'kelas_update']);
            $routes->delete('delete/(:num)', 'Kelas::delete/$1', ['as' => 'kelas_delete']); 
        });
        // --- SUB-MODUL: kurikulum ---
        $routes->group('kurikulum', static function ($routes) {
            $routes->get('/', 'Kurikulum::index', ['as' => 'kurikulum_index']);
            $routes->get('new', 'Kurikulum::new', ['as' => 'kurikulum_new']);
            $routes->get('edit/(:num)', 'Kurikulum::edit/$1', ['as' => 'kurikulum_edit']);
    
            // Simpan Data Baru
            $routes->post('/', 'Kurikulum::create', ['as' => 'kurikulum_create']);
            $routes->post('create', 'Kurikulum::create'); 

            // Update Data (POST & PUT)
            $routes->post('update/(:num)', 'Kurikulum::update/$1', ['as' => 'kurikulum_update']);
            $routes->put('update/(:num)', 'Kurikulum::update/$1');

            // Hapus Data (Definisi Eksplisit agar lebih stabil)
            // 1. Fallback: Tangani DELETE ke root (mencegah 404 jika JS gagal set action)
            $routes->delete('/', 'Kurikulum::index');

            // 2. Tangani method DELETE (dari form dengan spoofing <input name="_method" value="DELETE">)
            $routes->delete('delete/(:num)', 'Kurikulum::delete/$1', ['as' => 'kurikulum_delete']);
            
            // 3. Tangani method POST (dari form biasa, jika spoofing gagal)
            $routes->post('delete/(:num)', 'Kurikulum::delete/$1');

            // 4. Tangani method GET (dari link manual <a>)
            $routes->get('delete/(:num)', 'Kurikulum::delete/$1');
        });
        // --- SUB-MODUL: matapelajaran (SINKRONISASI 100%) ---
        $routes->group('matapelajaran', static function ($routes) {
            $routes->get('/', 'MataPelajaran::index', ['as' => 'mapel_index']);
    
            // TAMBAHKAN RUTE NEW INI (Penyebab 404)
            $routes->get('new', 'MataPelajaran::new', ['as' => 'mapel_new']); 
            $routes->post('/', 'MataPelajaran::create', ['as' => 'mapel_create']);
            // Tambahan alias post 'create' agar konsisten dengan modul lain
            $routes->post('create', 'MataPelajaran::create'); 
            $routes->get('edit/(:num)', 'MataPelajaran::edit/$1', ['as' => 'mapel_edit']);
    
            // Update (Mendukung PUT spoofing dan POST fallback)
            $routes->put('update/(:num)', 'MataPelajaran::update/$1', ['as' => 'mapel_update']);
            $routes->post('update/(:num)', 'MataPelajaran::update/$1'); 
            // Delete (Mendukung DELETE spoofing dan GET fallback)
            $routes->delete('delete/(:num)', 'MataPelajaran::delete/$1', ['as' => 'mapel_delete']);
            $routes->get('delete/(:num)', 'MataPelajaran::delete/$1');
            // Show: Arahkan ke method show (redirect ke edit)
            $routes->get('show/(:num)', 'MataPelajaran::show/$1');
        });
        // --- SUB-MODUL: Jenis Pembayaran ---
        $routes->group('jenispembayaran', static function ($routes) {
            $routes->get('/', 'JenisPembayaran::index', ['as' => 'jp_index']);
            $routes->get('new', 'JenisPembayaran::new', ['as' => 'jp_new']);
            
            // PERBAIKAN UTAMA: Tambahkan rute 'create' agar sesuai dengan action form Anda
            $routes->post('create', 'JenisPembayaran::create');
            // Cadangan: POST ke root juga diarahkan ke create
            $routes->post('/', 'JenisPembayaran::create', ['as' => 'jp_create']);
            
            $routes->get('edit/(:num)', 'JenisPembayaran::edit/$1', ['as' => 'jp_edit']);
            
            // Update: Support PUT (Spoofing) & POST (Fallback)
            $routes->put('update/(:num)', 'JenisPembayaran::update/$1', ['as' => 'jp_update']);
            $routes->post('update/(:num)', 'JenisPembayaran::update/$1'); 
            
            // Delete: Support DELETE (Spoofing) & GET (Link biasa)
            $routes->delete('delete/(:num)', 'JenisPembayaran::delete/$1', ['as' => 'jp_delete']);
            $routes->get('delete/(:num)', 'JenisPembayaran::delete/$1');

            // Show: Arahkan ke method show (redirect ke edit)
            $routes->get('show/(:num)', 'JenisPembayaran::show/$1');
        });
        // --- SUB-MODUL: Komponen Gaji ---
        $routes->group('komponen-gaji', static function ($routes) {
            // Menampilkan daftar komponen gaji
            $routes->get('/', 'KomponenGaji::index', ['as' => 'komponen_gaji_index']);
            // Menampilkan form tambah (Opsional jika menggunakan modal, rute ini tetap baik untuk standarisasi)
            $routes->get('new', 'KomponenGaji::new', ['as' => 'komponen_gaji_new']);
            // Menyimpan data baru
            $routes->post('create', 'KomponenGaji::create', ['as' => 'komponen_gaji_create']);
            // Menampilkan form edit berdasarkan ID
            $routes->get('edit/(:num)', 'KomponenGaji::edit/$1', ['as' => 'komponen_gaji_edit']);
            // Update data menggunakan spoofing PUT
            $routes->put('update/(:num)', 'KomponenGaji::update/$1', ['as' => 'komponen_gaji_update']);
            // Backup rute update menggunakan POST (untuk kompatibilitas form lama)
            $routes->post('update/(:num)', 'KomponenGaji::update/$1');
            // Menghapus data
            $routes->delete('delete/(:num)', 'KomponenGaji::delete/$1', ['as' => 'komponen_gaji_delete']);
        });
    });
    // --- MODUL CMS (Agenda, Pengumuman, Berita, Galeri) ---
    // Namespace: App\Controllers\Cms
    $routes->group('cms', ['namespace' => 'App\Controllers\Cms'], static function ($routes) {
        $routes->get('/', 'Dashboard::index');
        $routes->get('dashboard', 'Dashboard::index');

        // Sub-Modul: Berita
        $routes->group('berita', static function ($routes) {
            $routes->get('/', 'Berita::index');
            $routes->get('new', 'Berita::new');
            $routes->get('edit/(:num)', 'Berita::edit/$1');
            $routes->post('save', 'Berita::save');
            $routes->get('delete/(:num)', 'Berita::delete/$1');
        });
        // Sub-Modul: Pengumuman
        $routes->group('pengumuman', static function ($routes) {
            $routes->get('/', 'Pengumuman::index');
            $routes->get('new', 'Pengumuman::new');
            $routes->get('edit/(:num)', 'Pengumuman::edit/$1');
            $routes->post('save', 'Pengumuman::save');
            $routes->get('delete/(:num)', 'Pengumuman::delete/$1');
        });
        // Sub-Modul: Agenda
        $routes->group('agenda', static function ($routes) {
            $routes->get('/', 'Agenda::index');
            $routes->get('new', 'Agenda::new');
            $routes->get('edit/(:num)', 'Agenda::edit/$1');
            $routes->post('save', 'Agenda::save');
            $routes->get('delete/(:num)', 'Agenda::delete/$1');
        });
        // Sub-Modul: Album Foto
        $routes->group('album', static function ($routes) {
            $routes->get('/', "Album::index");
            $routes->get('new', "Album::new");
            $routes->post('save', "Album::save");
            $routes->get('edit/(:num)', "Album::edit/$1");
            $routes->get('delete/(:num)', "Album::delete/$1");
            // Rute Tambahan Khusus Manajemen Foto (FIX 404)
            $routes->get('manage/(:num)', "Album::managePhotos/$1");
            $routes->post('upload-photos', "Album::uploadPhotos");
            $routes->get('delete-photo/(:num)', "Album::deletePhoto/$1");
        });
    });

    // --- MODUL DATABASE (MAINTENANCE & MIGRATION) ---
    // Namespace: App\Controllers\Database
    $routes->group('database', ['namespace' => 'App\Controllers\Database'], static function ($routes) {
        // Dashboard
        $routes->get('/', 'DatabaseController::index');
        
        // Backup & Restore
        $routes->get('backup', 'DatabaseController::backup');
        $routes->post('restore', 'DatabaseController::restore');
        
        // Import Data
        $routes->get('import', 'DatabaseController::import_form');
        $routes->post('import_process', 'DatabaseController::import_process');
        
        // Export Data (FIXED)
        $routes->get('export', 'DatabaseController::export_menu');        // Halaman Pilihan Export
        $routes->get('export/(:segment)', 'DatabaseController::export_data/$1'); // Proses Download Excel
        
        // Template
        $routes->get('template/(:segment)', 'DatabaseController::download_template/$1');
    });

    // --- PPDB (SINKRONISASI MODUL PPDB & AFILIASI) ---
    $routes->group('ppdb', ['namespace' => 'App\Controllers\Ppdb'], function($routes) {
        // Dashboard & List
        $routes->get('/', 'AdminController::index');
        $routes->get('dashboard', 'AdminController::index');
        $routes->get('list', 'AdminController::list');
        
        // Pendaftaran & Detail
        $routes->get('detail/(:num)', 'AdminController::detail/$1');
        $routes->get('add', 'AdminController::add');
        $routes->get('edit/(:num)', 'AdminController::edit/$1');
        $routes->post('save', 'AdminController::save');
        $routes->post('save/(:num)', 'AdminController::save/$1');
        $routes->get('delete/(:num)', 'AdminController::delete/$1');
        
        // Aksi Verifikasi & Cetak
        $routes->get('verifikasi/(:num)/(:segment)', 'AdminController::verifikasi/$1/$2');
        $routes->get('print/(:num)', 'AdminController::print/$1');

        // SUB-MODUL: Afiliasi (Manajemen Agen & Fee)
        // Alias untuk 'affiliate' agar link lama tetap jalan
        $routes->group('affiliate', function($routes) {
            $routes->get('/', 'AffiliateController::index');
            $routes->get('agen', 'AffiliateController::listAgen');
            $routes->get('addAgen', 'AffiliateController::addAgen');
            $routes->get('detail/(:num)', 'AffiliateController::detail/$1');
            $routes->get('editAgen/(:num)', 'AffiliateController::editAgen/$1');
            $routes->post('saveAgen', 'AffiliateController::saveAgen');
            $routes->post('saveAgen/(:num)', 'AffiliateController::saveAgen/$1');
            $routes->get('deleteAgen/(:num)', 'AffiliateController::deleteAgen/$1');
            $routes->get('fee', 'AffiliateController::fee'); 
            
            // FIX: Tambahkan Rute Konfigurasi (Wajib untuk tombol 'Skema Global')
            $routes->get('konfigurasi', 'AffiliateController::konfigurasi');
            $routes->post('saveKonfigurasi', 'AffiliateController::saveKonfigurasi');
        });

        // Ekspor Laporan
        $routes->get('export/excel', 'AdminController::exportExcel');
        $routes->get('export/pdf', 'AdminController::exportPdf');
    });

    // --- AKADEMIK ---
    $routes->group('akademik', ['namespace' => 'App\Controllers\Akademik'], static function ($routes) {
        $routes->get('/', 'DashboardAkademik::index', ['as' => 'akademik_dashboard_root']);  
        $routes->get('dashboard', 'DashboardAkademik::index', ['as' => 'akademik_dashboard']);  
        // --- SUB-MODUL: KALENDER (SINKRONISASI 100%) ---
        $routes->group('kalender', static function ($routes) {
            $routes->get('/', 'KalenderPendidikan::index');       // Daftar Tabel
            $routes->get('new', 'KalenderPendidikan::new');       // Form Tambah
            $routes->post('create', 'KalenderPendidikan::create'); // Proses Simpan
            $routes->post('/', 'KalenderPendidikan::create');      // Fallback Simpan
            // RUTE EDIT (SOLUSI 404 ANDA)
            $routes->get('edit/(:num)', 'KalenderPendidikan::edit/$1'); 
            // RUTE UPDATE (Mendukung Spoofing PUT dari Form)
            $routes->match(['post', 'put'], 'update/(:num)', 'KalenderPendidikan::update/$1');
            // RUTE DELETE
            $routes->delete('delete/(:num)', 'KalenderPendidikan::delete/$1');
            $routes->get('delete/(:num)', 'KalenderPendidikan::delete/$1'); // Fallback link <a>
            // API untuk Mode Visual
            $routes->get('events', 'KalenderPendidikan::getEvents');
            $routes->get('calendar', 'KalenderPendidikan::calendar'); // View FullCalendar
        });
        // --- SUB-MODUL: jadwalpelajaran ---
        $routes->group('jadwalpelajaran', static function ($routes) {
            $routes->get('/', 'JadwalPelajaran::index');
            $routes->get('new', 'JadwalPelajaran::new');
            $routes->post('create', 'JadwalPelajaran::create');
            $routes->get('edit/(:num)', 'JadwalPelajaran::edit/$1');
            $routes->match(['post', 'put'], 'update/(:num)', 'JadwalPelajaran::update/$1');
            $routes->delete('delete/(:num)', 'JadwalPelajaran::delete/$1');
        });
        // --- SUB-MODUL: absensi-siswa ---
        $routes->group('absensi-siswa', static function ($routes) {
            $routes->get('/', 'AbsensiSiswa::index', ['as' => 'absensi_siswa_index']); 
            $routes->get('kelola', 'AbsensiSiswa::kelola', ['as' => 'absensi_siswa_kelola']); 
            $routes->post('simpan', 'AbsensiSiswa::simpan', ['as' => 'absensi_siswa_simpan']); 
        });
        // --- SUB-MODUL: absensi-otomatis ---
        $routes->group('absensi-otomatis', static function ($routes) {
            $routes->get('/', 'AbsensiOtomatis::index', ['as' => 'absensi_otomatis_index']); 
            $routes->post('proses', 'AbsensiOtomatis::proses', ['as' => 'absensi_otomatis_proses']); 
        });
        // --- SUB-MODUL: nilai ---
        $routes->group('nilai', static function ($routes) {
            $routes->get('/', 'Nilai::index');
            $routes->match(['get', 'post'], 'kelola', 'Nilai::kelola');
            $routes->post('simpan', 'Nilai::simpan');
        });
        // --- SUB-MODUL: rapor ---
        $routes->group('rapor', static function ($routes) {
            $routes->get('/', 'Rapor::index');
            $routes->get('view/(:num)', 'Rapor::view/$1');
             // TAMBAHAN: Rute untuk menyimpan rapor yang sebelumnya 404
            $routes->post('simpan/(:num)', 'Rapor::simpan/$1');
            // Menambahkan rute cetak yang sebelumnya hilang
            $routes->get('cetak/(:num)', 'Rapor::cetak/$1');
        });
        // --- SUB-MODUL: kenaikan_kelas ---
        $routes->group('kenaikan_kelas', static function ($routes) {
            $routes->get('/', 'KenaikanKelas::index');
            $routes->get('kelola', 'KenaikanKelas::kelola'); 
            $routes->post('simpan', 'KenaikanKelas::simpan');
            $routes->post('proses', 'KenaikanKelas::proses');
            $routes->get('riwayat', 'KenaikanKelas::riwayat');
        });
        // --- SUB-MODUL: ijazah ---
        $routes->group('ijazah', static function ($routes) {
            $routes->get('/', 'Ijazah::index');
            $routes->post('save', 'Ijazah::save');
        });
    });

    // --- KEUANGAN ---
    $routes->group('keuangan', ['namespace' => 'App\Controllers\Keuangan'], static function ($routes) {
            $routes->get('dashboard', 'DashboardController::index');

        $routes->group('budget', static function ($routes) {
            $routes->get('/', 'LaporanBudgetController::index');
            $routes->post('save', 'LaporanBudgetController::save');
            $routes->get('delete/(:num)', 'LaporanBudgetController::delete/$1');
            $routes->get('target-siswa', 'TargetSiswaController::index');
            $routes->get('target_siswa', 'TargetSiswaController::index');          
            $routes->post('target-siswa/save', 'TargetSiswaController::save_calculation');
            $routes->post('target_siswa/save', 'TargetSiswaController::save_calculation');
            $routes->get('target-siswa/save', 'TargetSiswaController::index');
            $routes->get('target_siswa/save', 'TargetSiswaController::index');
            $routes->get('target-beban', 'TargetBebanController::index');
            $routes->post('target-beban/save', 'TargetBebanController::save');
        });

        $routes->group('tagihan', static function ($routes) {
            $routes->get('/', 'TagihanController::index');                                
            $routes->get('form', 'TagihanController::form');                                
            $routes->get('form/(:num)', 'TagihanController::form/$1');    
            $routes->get('show/(:num)', 'TagihanController::show/$1');
            $routes->get('detail/(:num)', 'TagihanController::detail/$1');
            $routes->post('save', 'TagihanController::save');              
            $routes->delete('delete/(:num)', 'TagihanController::delete/$1'); 
            $routes->get('mass_form', 'TagihanController::mass_form');
            $routes->post('generate_proses', 'TagihanController::generate_proses');
            $routes->post('process_bayar/(:num)', 'PembayaranController::store/$1');
        });

        $routes->group('utang', static function ($routes) {
            $routes->get('/', 'TagihanController::index');                                
            $routes->get('form', 'TagihanController::form');                                
            $routes->get('form/(:num)', 'TagihanController::form/$1');    
            $routes->get('show/(:num)', 'TagihanController::show/$1');
            $routes->get('detail/(:num)', 'TagihanController::detail/$1');
            $routes->post('save', 'TagihanController::save');              
            $routes->delete('delete/(:num)', 'TagihanController::delete/$1'); 
            $routes->get('mass_form', 'TagihanController::mass_form');
            $routes->post('generate_proses', 'TagihanController::generate_proses');
            $routes->post('process_bayar/(:num)', 'PembayaranController::store/$1');
        });

        $routes->group('pembayaran', static function ($routes) {
            $routes->get('/', 'PembayaranController::index');
            $routes->get('create/(:num)', 'PembayaranController::create/$1');
            $routes->post('store', 'PembayaranController::store');
            $routes->get('detail/(:num)', 'PembayaranController::detail/$1');
            $routes->get('invoice/(:num)', 'PembayaranController::invoice/$1');
            
            // --- FIX 404: RUTE CETAK KWITANSI ---
            $routes->get('cetak/(:num)', 'PembayaranController::cetak/$1');
        });

        $routes->group('pengeluaran', static function ($routes) {
            $routes->get('/', 'PengeluaranController::index');
            $routes->get('create', 'PengeluaranController::create');
            $routes->post('store', 'PengeluaranController::store');
            $routes->get('edit/(:num)', 'PengeluaranController::edit/$1');
            $routes->delete('delete/(:num)', 'PengeluaranController::delete/$1');
        });

        $routes->group('laporan', static function ($routes) {
            $routes->get('pemasukan', 'LaporanPemasukanController::index', ['as' => 'laporan_pemasukan']);
            $routes->get('piutang', 'LaporanTunggakanController::index', ['as' => 'laporan_piutang']);
            $routes->get('pengeluaran', 'LaporanPengeluaranController::index', ['as' => 'laporan_pengeluaran']);
            $routes->get('pengeluaran/pdf', 'LaporanPengeluaranController::exportPdf', ['as' => 'laporan_pengeluaran_pdf']);
            $routes->get('pengeluaran/excel', 'LaporanPengeluaranController::exportExcel', ['as' => 'laporan_pengeluaran_excel']);
            $routes->get('rekap_harian', 'LaporanRekapController::harian');
            
            // --- FIX: Ubah route sesuai permintaan ---
            // URL menjadi: app/keuangan/laporan/cetak_pengeluaran
            $routes->get('cetak_pengeluaran', 'LaporanPengeluaranController::cetak'); 
            
            // Dashboard Cetak Pusat (Optional)
            $routes->get('dashboard_cetak', 'CetakController::dashboard');
            // $routes->get('cetak', 'CetakController::index'); // Opsional jika masih dipakai
        });
    });
 
    // --- KESISWAAN ---
    $routes->group('kesiswaan', ['namespace' => 'App\Controllers\Kesiswaan'], static function ($routes) {
        $routes->get('/', 'DashboardKesiswaanController::index');
        $routes->get('dashboard', 'DashboardKesiswaanController::index');
        $routes->get('debug', 'Debug::index');

        // Store Actions
        $routes->post('store_ekskul', 'KesiswaanController::store_ekskul');
        $routes->post('store_anggota_ekskul', 'KesiswaanController::store_anggota_ekskul');
        $routes->post('store_presensi_ekskul', 'KesiswaanController::store_presensi_ekskul');
        $routes->post('store_kasus_bk', 'KesiswaanController::store_kasus_bk');
        $routes->post('store_organisasi', 'KesiswaanController::store_organisasi');
        $routes->post('store_alumni', 'KesiswaanController::store_alumni');
        $routes->post('store_prestasi', 'KesiswaanController::store_prestasi');

        // Delete Actions
        $routes->get('delete_ekskul/(:num)', 'KesiswaanController::delete_ekskul/$1');
        $routes->get('delete_anggota_ekskul/(:num)', 'KesiswaanController::delete_anggota_ekskul/$1');
        $routes->get('delete_presensi_ekskul/(:num)', 'KesiswaanController::delete_presensi_ekskul/$1');
        $routes->get('delete_kasus_bk/(:num)', 'KesiswaanController::delete_kasus_bk/$1');
        $routes->get('delete_organisasi/(:num)', 'KesiswaanController::delete_organisasi/$1');
        $routes->get('delete_alumni/(:num)', 'KesiswaanController::delete_alumni/$1');
        $routes->get('delete_prestasi/(:num)', 'KesiswaanController::delete_prestasi/$1');

        // --- PRINT ACTIONS (NEW) ---
        $routes->group('print', static function ($routes) {
            $routes->post('ekskul', 'PrintController::ekskul');
            $routes->post('bk', 'PrintController::bk');
            $routes->post('prestasi', 'PrintController::prestasi');
            $routes->post('presensi', 'PrintController::presensi');
            $routes->post('alumni', 'PrintController::alumni');
        });
    });

    // --- E-LEARNING (MODUL UTAMA - FIXED) ---
    // Namespace: App\Controllers\Elearning
    $routes->group('elearning', ['namespace' => 'App\Controllers\Elearning'], static function ($routes) {
        
        // 1. Dashboard (Daftar Kelas)
        $routes->get('/', 'DashboardController::index', ['as' => 'elearning_index']);
        $routes->get('dashboard', 'DashboardController::index');   
        // 2. Course Management (Forum & Setup)
        // Menampilkan detail kelas (Stream)
        $routes->get('view/(:num)', 'CourseController::view/$1', ['as' => 'elearning_view']);
        
        // Pembuatan & Pengaturan Kelas
        $routes->get('create', 'CourseController::create', ['as' => 'elearning_create']);
        $routes->post('store', 'CourseController::store', ['as' => 'elearning_store']);
    
        // ============================================================
        // PERBAIKAN: Ubah post menjadi match agar bisa diakses via Link (GET)
        // ============================================================
        $routes->match(['get', 'post'], 'join', 'CourseController::join', ['as' => 'elearning_join']);
        // ============================================================

        $routes->get('settings/(:num)', 'CourseController::settings/$1');
        // Rute Baru untuk Posting
        $routes->post('post_announcement', 'CourseController::post_announcement');
        $routes->post('post_comment', 'CourseController::post_comment');      

        // 3. Activity (Tugas & Kuis)
        // FIX: classwork ada di CourseController
        $routes->get('classwork/(:num)', 'CourseController::classwork/$1', ['as' => 'elearning_classwork']);
        
        $routes->group('task', static function ($routes) {
            $routes->get('detail/(:num)', 'ActivityController::detail/$1');
            $routes->post('submit', 'ActivityController::submit');
        });

        // 4. Content (Materi, Postingan, People)
        $routes->get('people/(:num)', 'CourseController::people/$1', ['as' => 'elearning_people']); // Diarahkan ke CourseController
        // ---> TAMBAHKAN INI UNTUK MEMPERBAIKI 404 <---
        $routes->post('update_student_status', 'CourseController::update_student_status');
        $routes->post('add_member', 'CourseController::add_member'); // <--- ROUTE BARU
        // ------------------------------------------------
        $routes->post('post/save', 'ContentController::savePost');
        $routes->post('post/comment', 'ContentController::saveComment');
        $routes->get('post/delete/(:num)', 'ContentController::deletePost/$1');

        // Rute untuk melihat detail dan submit (ActivityController)
        $routes->get('assignment/(:num)', 'ActivityController::viewAssignment/$1');
        $routes->get('material/(:num)', 'ActivityController::viewMaterial/$1');
    
        // Rute action submit
        $routes->post('submit_assignment', 'ActivityController::submitWork');

        // Admin Generator (Diarahkan ke CourseGeneratorController)
        $routes->get('generate', 'CourseGeneratorController::generate');
        $routes->post('process_generate', 'CourseGeneratorController::process_generate');
        
        // Materi & Topik
        $routes->post('store_topic', 'CourseController::store_topic');
        $routes->post('store_content', 'CourseController::store_content');

        // Group Kuis - Diperbarui untuk mendukung workflow lengkap
        $routes->group('quiz', function($routes) {
            $routes->get('create/(:num)', 'QuizController::create/$1');
            $routes->post('store', 'QuizController::store'); // Endpoint yang bermasalah tadi
            $routes->get('questions/(:num)', 'QuizController::questions/$1');
            $routes->post('add_question', 'QuizController::add_question');
            $routes->get('delete_question/(:num)', 'QuizController::delete_question/$1');
            $routes->get('publish/(:num)', 'QuizController::publish/$1');
        });

        // --- Penilaian & Buku Nilai ---
        $routes->get('grades/(:num)', 'GradeController::index/$1');
        $routes->get('grades/sync/(:num)', 'GradeController::syncToAcademic/$1');
    });
   
    // --- SAPRAS (MANAJEMEN ASET ENTERPRISE 1.0 - FIX 404) ---
    $routes->group('sapras', ['namespace' => 'App\Controllers\Sapras'], static function ($routes) {
        $routes->get('/', 'DashboardController::index');
        $routes->get('dashboard', 'DashboardController::index');
        
        // 1. Sub-Modul: Kategori Aset
        $routes->group('kategori', static function ($routes) {
            $routes->get('/', 'KategoriAset::index');
            $routes->get('new', 'KategoriAset::new');
            $routes->get('edit/(:num)', 'KategoriAset::edit/$1');
            $routes->post('save', 'KategoriAset::save');
            $routes->post('save/(:num)', 'KategoriAset::save/$1');
            $routes->get('delete/(:num)', 'KategoriAset::delete/$1');
        });

        // 2. Sub-Modul: Lokasi Aset
        $routes->group('lokasi', static function ($routes) {
            $routes->get('/', 'LokasiAset::index');
            $routes->get('new', 'LokasiAset::new');
            $routes->get('edit/(:num)', 'LokasiAset::edit/$1');
            $routes->post('save', 'LokasiAset::save');
            $routes->post('save/(:num)', 'LokasiAset::save/$1');
            $routes->get('delete/(:num)', 'LokasiAset::delete/$1');
        });

        // 3. Sub-Modul: Barang Aset (Katalog Master)
        $routes->group('barang', static function ($routes) {
            $routes->get('/', 'BarangAset::index');
            $routes->get('new', 'BarangAset::new');
            $routes->get('edit/(:num)', 'BarangAset::edit/$1');
            $routes->post('save', 'BarangAset::save');
            $routes->post('save/(:num)', 'BarangAset::save/$1');
            $routes->get('delete/(:num)', 'BarangAset::delete/$1');
            
            // --- FITUR BARU: CETAK LABEL & LAPORAN ---
            $routes->get('print-label/(:num)', 'BarangAset::printLabel/$1');
            $routes->get('print-report', 'BarangAset::printReport');
        });

        // 4. Sub-Modul: Pengadaan Aset (Requisition)
        $routes->group('pengadaan', static function ($routes) {
            $routes->get('/', 'PengadaanAset::index');
            $routes->get('new', 'PengadaanAset::new');
            $routes->get('edit/(:num)', 'PengadaanAset::edit/$1');
            $routes->post('save', 'PengadaanAset::save');
            $routes->post('save/(:num)', 'PengadaanAset::save/$1');
            $routes->get('delete/(:num)', 'PengadaanAset::delete/$1');
        });

        // 5. Sub-Modul: Peminjaman Aset (Logistik)
        $routes->group('peminjaman', static function ($routes) {
            $routes->get('/', 'PeminjamanAset::index');
            $routes->get('new', 'PeminjamanAset::new');
            $routes->get('edit/(:num)', 'PeminjamanAset::edit/$1');
            $routes->post('save', 'PeminjamanAset::save');
            $routes->post('save/(:num)', 'PeminjamanAset::save/$1');
            $routes->get('delete/(:num)', 'PeminjamanAset::delete/$1');
        });

        // 6. Sub-Modul: Pemeliharaan Aset (Servis)
        $routes->group('pemeliharaan', static function ($routes) {
            $routes->get('/', 'PemeliharaanAset::index');
            $routes->get('new', 'PemeliharaanAset::new');
            $routes->get('edit/(:num)', 'PemeliharaanAset::edit/$1');
            $routes->post('save', 'PemeliharaanAset::save');
            $routes->post('save/(:num)', 'PemeliharaanAset::save/$1');
            $routes->get('delete/(:num)', 'PemeliharaanAset::delete/$1');

            // --- FITUR BARU: CETAK LABEL, LAPORAN & RIWAYAT PEMELIHARAAN ---
            $routes->get('print-label/(:num)', 'PemeliharaanAset::printLabel/$1');
            $routes->get('print-report', 'PemeliharaanAset::printReport');
            $routes->get('print-riwayat/(:num)', 'PemeliharaanAset::printRiwayat/$1'); // <--- TAMBAHKAN BARIS INI
        });
    });
    
    // --- MODUL: KERJASAMA (KPI, MOU, & MITRA STRATEGIS) ---
    $routes->group('kerjasama', ['namespace' => 'App\Controllers\Kerjasama'], static function ($routes) {
        // Dashboard Analisis & Grafik KPI
        $routes->get('dashboard', 'Kerjasama::dashboard');
        
        // Manajemen Data Mitra
        $routes->get('/', 'Kerjasama::index');             // Tabel Database
        $routes->get('new', 'Kerjasama::new');             // Form Tambah
        $routes->get('edit/(:num)', 'Kerjasama::edit/$1'); // Form Edit
        $routes->post('save', 'Kerjasama::save');          // Simpan (Insert/Update)
        $routes->get('delete/(:num)', 'Kerjasama::delete/$1'); // Hapus Data & File
    });

    // --- KEPEGAWAIAN (KARYAWAN & GURU) ---
    // Namespace: App\Controllers\Kepegawaian
    $routes->group('kepegawaian', ['namespace' => 'App\Controllers\Kepegawaian'], static function ($routes) {
        $routes->get('/', 'DashboardKepegawaianController::index');
        
        // Integrasi Mesin
        $routes->get('otomatis', 'AbsensiKaryawanController::otomatis');
        $routes->post('proses-otomatis', 'AbsensiKaryawanController::proses_otomatis');
        $routes->post('fingerprint-api', 'AbsensiKaryawanController::fingerprint_api'); 
        
        // Modul Presensi Pegawai Terpadu (GURU & STAFF)
        $routes->group('absensi-pegawai', static function ($routes) {
            // Tampilan Utama
            $routes->get('/', 'AbsensiPegawaiController::index');
            
            // Alur 1: Koreksi Status (Koreksi data yang sudah ada via Modal Edit)
            $routes->match(['get', 'post'], 'updateStatus', 'AbsensiPegawaiController::updateStatus');
            
            // Alur 2: Simpan Manual Individu (Input data baru per orang via Modal Individu)
            $routes->post('simpanManual', 'AbsensiPegawaiController::simpanManual');
            
            // Alur 3: Simpan Manual Massal (Input satu unit sekaligus via Modal Massal)
            $routes->post('simpanMassal', 'AbsensiPegawaiController::simpanMassal');
            
            // Alur 4: Real-time Tap Terminal Simulator (Otomatis Check-in/Out)
            $routes->post('prosesTap', 'AbsensiPegawaiController::prosesTap');
            
            // --- FIX 404: TAMBAHKAN RUTE ABSEN ONLINE ---
            // Alur 5: Absen Online Kamera & GPS
            $routes->post('prosesOnline', 'AbsensiPegawaiController::prosesOnline');
            
            // Rekapitulasi (Opsional)
            $routes->get('rekap', 'AbsensiPegawaiController::rekap');
        });

        // Modul Gaji & Payroll
        $routes->group('gaji-pegawai', static function ($routes) {
            $routes->get('/', 'GajiPegawaiController::index');
            
            // Pengaturan Gaji Individu
            $routes->get('kelola/(:num)', 'GajiPegawaiController::kelola/$1');
            $routes->post('simpanKomponen', 'GajiPegawaiController::simpanKomponen');
            $routes->get('hapusKomponen/(:num)', 'GajiPegawaiController::hapusKomponen/$1');
            $routes->get('riwayat/(:num)', 'GajiPegawaiController::riwayat/$1');
            $routes->post('bayar', 'GajiPegawaiController::bayar');
            // Rekapitulasi & Generate
            $routes->get('rekap', 'GajiPegawaiController::rekap');
            // --- FIX: Rute Generate Gaji (POST) ---
            $routes->post('generate', 'GajiPegawaiController::generate'); 
            // --------------------------------------
            // Cetak Slip
            $routes->get('slip/(:num)', 'GajiPegawaiController::slip/$1');
        });
    });

    // --- PENGATURAN ---
    // --- SUB-MODUL: Kelembagaan ---
    $routes->group('kelembagaan', static function ($routes) {
        $routes->get('/', 'Kelembagaan::index');
        $routes->post('update', 'Kelembagaan::update');
    });
    
    // --- MODUL PENGATURAN (SISTEM & AKSES) ---
    $routes->group('pengaturan', ['namespace' => 'App\Controllers\Pengaturan'], static function ($routes) {
        
    $routes->get('/', 'Pengaturan::index');
        
        // --- FITUR BARU: KONFIGURASI SAAS & UMUM ---
        $routes->get('umum', 'UmumSekolah::index');
        $routes->post('umum/update', 'UmumSekolah::update');

        // Sub-Modul: Hak Akses (Roles)
        $routes->group('hak_akses', static function ($routes) {
            $routes->get('/', 'HakAkses::index');              
            $routes->get('new', 'HakAkses::new');              
            $routes->post('create', 'HakAkses::create');        
            $routes->get('edit/(:num)', 'HakAkses::edit/$1');   
            
            // --- PERBAIKAN DI SINI ---
            // Kode lama kamu: $routes->post('update/(:num)', ...);
            
            // Kode BARU (Gunakan 'put' atau 'match'):
            // Ini akan menerima request PUT (dari form spoofing) DAN POST (jika fallback)
            $routes->match(['put', 'post'], 'update/(:num)', 'HakAkses::update/$1'); 

            $routes->get('delete/(:num)', 'HakAkses::delete/$1'); 
        });


        $routes->group('pengguna', static function ($routes) {
            $routes->get('/', 'Pengguna::index');
            $routes->get('new', 'Pengguna::new');
            
            // Simpan Data
            $routes->post('store', 'Pengguna::store'); 
            $routes->post('/', 'Pengguna::store'); 
            
            // === PERBAIKAN DI SINI ===
            // Mengubah (:num) menjadi (:segment) agar mendukung ID 'S-73'
            
            // 1. Edit Form
            $routes->get('edit/(:segment)', 'Pengguna::edit/$1');
            
            // 2. Update Process (Support PUT/POST)
            $routes->match(['put', 'post'], 'update/(:segment)', 'Pengguna::update/$1');
            
            // 3. Delete Process
            $routes->get('delete/(:segment)', 'Pengguna::delete/$1');
            // =========================

            // Profil Shortcut
            $routes->group('profil', ['filter' => 'auth'], function($routes) {
                $routes->get('/', 'Pengguna::profil');
                $routes->match(['get', 'post'], 'update-password', 'Pengguna::updatePassword');
            });
        });

        $routes->get('akademik-setting', 'AkademikSetting::index');
        $routes->get('keuangan-setting', 'KeuanganSetting::index');
        $routes->get('notifikasi', 'Notifikasi::index');
        $routes->get('log', 'Log::index');
    });

});













