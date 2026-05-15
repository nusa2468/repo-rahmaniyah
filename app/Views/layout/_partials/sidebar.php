<?php
/**
 * Sidebar Partial (APPPATH/Views/layout/_partials/sidebar.php)
 * UI: Tailwind CSS v4 + Alpine.js
 * Logic: Full Role & Unit Based Filtering
 * Update: Refined Navigation Structure & Direct Links
 */

helper('menu'); // Mengaktifkan pengecekan akses menu

$uri = service('uri');
$segments = $uri->getSegments();
$totalSegments = count($segments);

// Deteksi Module (Segment ke-2 setelah /app/)
$currentModule = $totalSegments >= 2 ? $uri->getSegment(2, '') : 'dashboard';
$segment3 = $totalSegments >= 3 ? $uri->getSegment(3, '') : '';

// Data Session untuk Profil
$session = session();
$userName    = $session->get('nama_lengkap') ?? 'User';
$userRole    = $session->get('role_display') ?? 'System User';
$userJenjang = $session->get('kode_jenjang') ?? 'GLOBAL';

// Grouping Modul
$portalModules = ['portal'];
$masterDataModules = ['masterdata', 'jenjang','identitas','organisasi', 'jabatan','pegawai', 'siswa', 'matapelajaran', 'kelas', 'tahunajaran', 'kurikulum', 'jenispembayaran', 'komponen-gaji'];
$ppdbModules = ['ppdb', 'psb', 'affiliate'];
$keuanganModules = ['keuangan', 'tagihan', 'laporankeuangan', 'pembayaran', 'pengeluaran', 'budget'];
$akademikModules = ['akademik', 'kalender', 'jadwalpelajaran', 'absensi-siswa', 'absensi-otomatis', 'nilai', 'rapor', 'ijazah', 'kenaikan-kelas']; 
$kesiswaanModules = ['kesiswaan', 'osis', 'kesiswaan-report', 'ekskul', 'alumni']; 
$kepegawaianModules = ['kepegawaian', 'absensi-guru', 'absensi-guru-manual', 'gaji-guru', 'absensi-karyawan', 'absensi-karyawan-manual', 'gaji-karyawan'];
$saprasModules = ['sapras', 'tanah', 'gedung', 'ruangan', 'peralatan', 'inventaris'];
$humasModules = ['humas', 'berita', 'pengumuman', 'agenda', 'albumfoto', 'afiliasi', 'cms'];
$laporanModules = ['laporan', 'laporan-akademik', 'laporan-keuangan', 'laporan-kepegawaian', 'laporan-kesiswaan'];
$pengaturanModules = ['pengaturan', 'pengguna', 'hak_akses', 'akademik-setting', 'keuangan-setting', 'notifikasi', 'log', 'kelembagaan'];
$elearningModules = ['elearning'];
$pembelajaranModules = ['pembelajaran', 'bahanajar'];
$databaseModules = ['database'];


// Helper Function Class (Tailwind)
$getLinkClass = function($isActive) {
    $base = "group flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 mb-0.5 no-underline ";
    return $isActive 
        ? $base . "bg-sky-500 text-white shadow-md shadow-sky-500/20 active-nav" 
        : $base . "text-gray-400 hover:text-white hover:bg-white/5";
};

$getSubLinkClass = function($isActive) {
    $base = "flex items-center gap-3 px-4 py-2 rounded-lg text-[13px] transition-all no-underline ";
    return $isActive 
        ? $base . "text-white bg-white/10 font-bold" 
        : $base . "text-gray-500 hover:text-gray-200 hover:bg-white/5";
};

// Penentuan menu yang terbuka secara otomatis (Alpine.js state)
$initialMenu = '';
if (in_array($currentModule, $masterDataModules)) $initialMenu = 'master';
elseif (in_array($currentModule, $ppdbModules)) $initialMenu = 'ppdb';
elseif (in_array($currentModule, $keuanganModules)) $initialMenu = 'keuangan';
elseif (in_array($currentModule, $akademikModules)) $initialMenu = 'akademik';
elseif (in_array($currentModule, $kesiswaanModules)) $initialMenu = 'kesiswaan';
elseif (in_array($currentModule, $kepegawaianModules)) $initialMenu = 'kepegawaian';
elseif (in_array($currentModule, $saprasModules)) $initialMenu = 'sapras';
elseif (in_array($currentModule, $humasModules)) $initialMenu = 'humas';
elseif (in_array($currentModule, $laporanModules)) $initialMenu = 'laporan';
elseif (in_array($currentModule, $pengaturanModules)) $initialMenu = 'pengaturan';
elseif (in_array($currentModule, $elearningModules)) $initialMenu = 'elearning';
elseif (in_array($currentModule, $pembelajaranModules)) $initialMenu = 'pembelajaran';
elseif (in_array($currentModule, $databaseModules)) $initialMenu = 'database';
elseif (in_array($currentModule, $portalModules)) $initialMenu = 'portal';
?>

<div class="flex flex-col h-full bg-gray-900 dark:bg-black text-gray-300" x-data="{ activeMenu: '<?= $initialMenu ?>' }">
    
    <!-- BRANDING -->
    <div class="h-16 flex items-center px-6 shrink-0 border-b border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-sky-500 rounded-lg flex items-center justify-center text-white shadow-lg shadow-sky-500/40 shrink-0">
                <i class="fas fa-layer-group text-sm"></i>
            </div>
            <div class="flex flex-col leading-none">
                <span class="text-white font-black tracking-tight text-base">ERP SIMS</span>
                <span class="text-[9px] font-bold text-sky-500 uppercase tracking-widest mt-0.5"><?= esc($userJenjang) ?> UNIT</span>
            </div>
        </div>
    </div>

    <!-- NAVIGATION -->
    <div class="flex-1 overflow-y-auto custom-scrollbar px-3 py-4 space-y-6">
        
        <!-- CORE -->
        <div>
            <p class="px-4 mb-2 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">Core</p>
            <a href="<?= base_url('app') ?>" class="<?= $getLinkClass($currentModule === 'dashboard' || $currentModule === 'app') ?>">
                <i class="fas fa-home w-5 text-center mr-3"></i>
                <span>Dashboard Utama</span>
            </a>
        </div>

        <!-- DATA FUNDAMENTAL -->
        <?php if (check_menu_access(['superadmin', 'admin', 'yayasan'])): ?>
        <div>
            <p class="px-4 mb-2 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">Data Fundamental</p>
            
            <!-- MASTER DATA -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/masterdata/dashboard') ?>" 
                   class="<?= $getLinkClass(in_array($currentModule, $masterDataModules)) ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-database w-5 text-center mr-3"></i>
                        <span>Master Data</span>
                    </span>
                </a>
            </div>

            <!-- PPDB -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/ppdb') ?>" 
                   class="<?= $getLinkClass(in_array($currentModule, $ppdbModules)) ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-user-plus w-5 text-center mr-3"></i>
                        <span>PPDB / PSB</span>
                    </span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- MODUL PEMBELAJARAN -->
        <?php if (check_menu_access(['superadmin', 'admin', 'guru'])): ?>
        <div>
            <p class="px-4 mb-2 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">Kegiatan Belajar</p>
            
            <!-- PEMBELAJARAN -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/pembelajaran/dashboard_pembelajaran') ?>" 
                   class="<?= $getLinkClass($currentModule == 'pembelajaran') ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-chalkboard-teacher w-5 text-center mr-3"></i>
                        <span>Pembelajaran</span>
                    </span>
                </a>
            </div>

            <!-- E-LEARNING -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/elearning') ?>" 
                   class="<?= $getLinkClass($currentModule == 'elearning') ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-laptop-code w-5 text-center mr-3"></i>
                        <span>E-Learning</span>
                    </span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- ADMINISTRASI -->
        <?php if (check_menu_access(['superadmin', 'admin', 'guru', 'siswa'])): ?>
        <div>
            <p class="px-4 mb-2 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">Administrasi</p>
            
            <!-- AKADEMIK -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/akademik/dashboard') ?>" 
                   class="<?= $getLinkClass(in_array($currentModule, $akademikModules)) ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-graduation-cap w-5 text-center mr-3"></i>
                        <span>Akademik</span>
                    </span>
                </a>
            </div>

            <!-- KESISWAAN -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/kesiswaan/dashboard') ?>" 
                   class="<?= $getLinkClass(in_array($currentModule, $kesiswaanModules)) ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-user-graduate w-5 text-center mr-3"></i>
                        <span>Kesiswaan</span>
                    </span>
                </a>
            </div>

            <!-- SAPRAS -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/sapras/dashboard') ?>" 
                   class="<?= $getLinkClass(in_array($currentModule, $saprasModules)) ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-building w-5 text-center mr-3"></i>
                        <span>Sapras</span>
                    </span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- SDM & FINANCE -->
        <?php if (check_menu_access(['superadmin', 'admin', 'bendahara'])): ?>
        <div>
            <p class="px-4 mb-2 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">SDM & Finance</p>
            
            <!-- KEPEGAWAIAN -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/kepegawaian') ?>" 
                   class="<?= $getLinkClass(in_array($currentModule, $kepegawaianModules)) ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-id-badge w-5 text-center mr-3"></i>
                        <span>Kepegawaian</span>
                    </span>
                </a>
            </div>

            <!-- KEUANGAN -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/keuangan/dashboard') ?>" 
                   class="<?= $getLinkClass(in_array($currentModule, $keuanganModules)) ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-wallet w-5 text-center mr-3"></i>
                        <span>Keuangan</span>
                    </span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- KEHUMASAN -->
        <?php if (check_menu_access(['superadmin', 'admin', 'bendahara'])): ?>
        <div>
            <p class="px-4 mb-2 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">KEHUMASAN</p>
            
            <!-- MANAJEMEN HUMAS (CMS) -->
            <div class="space-y-1 mt-1">
                <a href="<?= base_url('app/cms') ?>" 
                   class="<?= $getLinkClass(in_array($currentModule, $humasModules)) ?> w-full flex justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-bullhorn w-5 text-center mr-3"></i>
                        <span>Manajemen Humas</span>
                    </span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- PORTAL -->
        <?php if (check_menu_access(['superadmin', 'admin'])): ?>
        <div>
            <p class="px-4 mb-2 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">External</p>
            
            <div class="space-y-1">
                <button @click="activeMenu = (activeMenu === 'portal' ? '' : 'portal')" 
                    class="<?= $getLinkClass(in_array($currentModule, $portalModules)) ?> w-full justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-globe w-5 text-center mr-3"></i>
                        <span>Portal Publik</span>
                    </span>
                    <i class="fas fa-chevron-right text-[10px] transition-transform duration-200" :class="activeMenu === 'portal' ? 'rotate-90' : ''"></i>
                </button>

                <div x-show="activeMenu === 'portal'" x-cloak x-collapse class="pl-4 space-y-1 mt-1 border-l border-white/5 ml-6">
                    <a href="<?= base_url('/') ?>" target="_blank" class="<?= $getSubLinkClass(false) ?>">Web Utama</a>
                    <a href="<?= base_url('portal/siswa/login') ?>" target="_blank" class="<?= $getSubLinkClass(false) ?>">Portal Siswa</a>
                    <a href="<?= base_url('portal/ppdb/pegawai') ?>" target="_blank" class="<?= $getSubLinkClass(false) ?>">Portal Pegawai</a>
                    <a href="<?= base_url('portal/ppdb/home') ?>" target="_blank" class="<?= $getSubLinkClass(false) ?>">Portal PPDB</a>
                    <a href="<?= base_url('portal/affiliated/home') ?>" target="_blank" class="<?= $getSubLinkClass(false) ?>">Portal Affiliated</a>
                    <a href="<?= base_url('portal/orang-tua/home') ?>" target="_blank" class="<?= $getSubLinkClass(false) ?>">Portal Ortu</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SYSTEM SETTINGS -->
        <?php if (check_menu_access(['superadmin', 'yayasan' , 'admin'])): ?>
        <div>
            <p class="px-4 mb-2 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">System & Setting</p>
            
            <div class="space-y-1">
                <button @click="activeMenu = (activeMenu === 'pengaturan' ? '' : 'pengaturan')" 
                    class="<?= $getLinkClass(in_array($currentModule, $pengaturanModules)) ?> w-full justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-cog w-5 text-center mr-3"></i>
                        <span>Pengaturan</span>
                    </span>
                    <i class="fas fa-chevron-right text-[10px] transition-transform duration-200" :class="activeMenu === 'pengaturan' ? 'rotate-90' : ''"></i>
                </button>
                <div x-show="activeMenu === 'pengaturan'" x-cloak x-collapse class="pl-4 space-y-1 mt-1 border-l border-white/5 ml-6">
                    <a href="<?= base_url('app/pengaturan/pengguna') ?>" class="<?= $getSubLinkClass($currentModule === 'pengguna') ?>">Manajemen User</a>
                    <a href="<?= base_url('app/pengaturan/hak_akses') ?>" class="<?= $getSubLinkClass($currentModule === 'hak_akses') ?>">Role & Permission</a>
                    <a href="<?= base_url('app/database') ?>" class="<?= $getSubLinkClass($currentModule === 'dashboard') ?>">Backup & Restore ++</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- USER FOOTER -->
    <div class="p-4 border-t border-white/5 bg-black/20">
        <div class="flex items-center gap-3 px-2 mb-4">
            <div class="relative shrink-0">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=0ea5e9&color=fff" class="w-9 h-9 rounded-lg border border-white/10" alt="User">
                <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-gray-900 rounded-full"></div>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-white truncate"><?= esc($userName) ?></p>
                <p class="text-[10px] text-gray-500 truncate font-medium uppercase tracking-tighter"><?= esc($userRole) ?></p>
            </div>
        </div>
        
        <a href="<?= base_url('logout') ?>" class="flex items-center justify-center gap-2 py-2 rounded-lg bg-red-500/10 text-red-500 text-[11px] font-black uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all no-underline border border-red-500/20">
            <i class="fas fa-power-off"></i>
            <span>Logout System</span>
        </a>
    </div>
</div>

<style type="text/tailwindcss">
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
    .active-nav i { @apply text-white !important; }
</style>