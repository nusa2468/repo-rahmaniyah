<?php
/**
 * Topbar Component - Optimized for Main Layout Shell
 * Menghapus tag <header> ganda untuk mencegah layout bertumpuk
 */
?>
<!-- Left Side: Mobile Toggle & Welcome -->
<div class="flex items-center gap-3">
    <!-- Button memicu variabel 'sidebarOpen' di layout utama -->
    <button @click="sidebarOpen = !sidebarOpen" 
            class="lg:hidden p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors focus:outline-none"
            aria-label="Buka Menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
    
    <div class="hidden md:block">
        <h2 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">
            Selamat datang, <span class="text-gray-900 dark:text-white font-black"><?= esc(session()->get('name') ?? 'Pengguna') ?></span>
        </h2>
    </div>
</div>

<!-- Right Side: User Menu & Status -->
<div class="flex items-center space-x-3 sm:space-x-4">
    
    <!-- Status Badge (Mobile Hidden) -->
    <div class="hidden sm:flex items-center px-3 py-1 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-full">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-2"></span>
        <span class="text-[9px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Online</span>
    </div>

    <div class="h-6 w-px bg-gray-200 dark:bg-gray-800 hidden xs:block"></div>

    <!-- User Dropdown -->
    <div x-data="{ open: false }" @click.away="open = false" class="relative">
        <button @click="open = !open" 
                class="flex items-center space-x-2 sm:space-x-3 p-1 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition-all focus:outline-none group">
            
            <div class="text-right hidden sm:block">
                <p class="text-xs font-bold text-gray-900 dark:text-white leading-none group-hover:text-sky-500 transition-colors">
                    <?= esc(session()->get('name') ?? 'User') ?>
                </p>
                <p class="text-[9px] text-gray-400 uppercase tracking-widest mt-1 font-medium">
                    <?= session()->get('role_id') == 1 ? 'Administrator' : 'Staff' ?>
                </p>
            </div>
            
            <!-- Avatar -->
            <div class="w-9 h-9 rounded-lg bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-600 dark:text-sky-400 font-bold shadow-sm group-hover:scale-105 transition-transform">
                <?= strtoupper(substr(session()->get('name') ?? 'A', 0, 1)) ?>
            </div>
        </button>

        <!-- Dropdown Menu -->
        <div x-show="open" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-75"
             class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-2xl z-[60] overflow-hidden">
            
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/5">
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-[0.15em]">E-mail Terdaftar</p>
                <p class="text-xs font-bold text-gray-900 dark:text-white truncate mt-0.5">
                    <?= esc(session()->get('email') ?? 'user@erp-property.com') ?>
                </p>
            </div>

            <div class="p-1.5 space-y-0.5">
                <a href="<?= base_url('app/pengaturan/pengguna/profil') ?>" class="flex items-center px-3 py-2 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-sky-500 rounded-xl transition-all group">
                    <i class="fas fa-user-circle w-5 text-gray-400 group-hover:text-sky-500"></i>
                    Profil Saya
                </a>
            </div>

            <div class="p-1.5 border-t border-gray-100 dark:border-gray-800">
                <a href="<?= base_url('logout') ?>" class="flex items-center px-3 py-2 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all group">
                    <i class="fas fa-sign-out-alt w-5 text-red-500"></i>
                    <span class="font-bold uppercase tracking-wider">Keluar</span>
                </a>
            </div>
        </div>
    </div>
</div>