<!-- Mobile Topbar / Nav Toggle -->
<!-- Hanya muncul di layar kecil (lg:hidden), fixed di atas -->
<div class="lg:hidden fixed top-0 left-0 w-full z-50 bg-indigo-600 text-white shadow-lg overflow-hidden transition-all duration-300">
    
    <!-- Background Decoration (Sama dengan Header Dashboard) -->
    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full mix-blend-overlay filter blur-2xl -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-24 h-24 bg-indigo-400/20 rounded-full mix-blend-overlay filter blur-xl translate-y-1/2 -translate-x-1/2 pointer-events-none"></div>

    <!-- Content Container -->
    <div class="relative z-10 px-4 py-3 flex items-center justify-between">
        
        <!-- Brand / Logo Area -->
        <div class="flex items-center gap-3 text-lg font-bold truncate max-w-[75%]">
            <!-- Logo Container dengan efek Glass -->
            <div class="w-9 h-9 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center shrink-0 border border-white/10 shadow-sm">
                <?php if(!empty($sekolah->logo) && file_exists(FCPATH . 'uploads/logo/' . $sekolah->logo)): ?>
                    <img src="<?= base_url('uploads/logo/' . $sekolah->logo) ?>" class="w-full h-full object-contain p-1.5" alt="Logo">
                <?php else: ?>
                    <i class="fas fa-graduation-cap text-white text-sm"></i> 
                <?php endif; ?>
            </div>
            
            <!-- Nama & Greeting -->
            <div class="flex flex-col justify-center overflow-hidden">
                <span class="truncate leading-tight"><?= esc($sekolah->nama_sekolah ?? 'Portal Siswa') ?></span>
                <?php if(isset($siswa['nama_lengkap'])): ?>
                    <span class="text-[10px] font-normal text-indigo-100 truncate leading-tight">
                        Hi, <?= esc(strtok($siswa['nama_lengkap'], " ")) ?> 👋
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tombol Menu Mobile -->
        <button id="mobile-menu-btn" class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-white/10 active:bg-white/20 transition-colors focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>
</div>