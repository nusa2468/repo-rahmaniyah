!-- Jika file lokal (assets/vendor/...)  -->
<link href="<?= base_url('assets/fontawesome/css/all.min.css'); ?>" rel="stylesheet">

<!-- Sidebar Partial -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out lg:static lg:block flex-shrink-0">
    <div class="h-full flex flex-col">
        <!-- Brand -->
        <div class="h-16 flex items-center px-6 border-b border-slate-100">
            <div class="flex items-center gap-3 text-indigo-700 font-extrabold text-lg tracking-tight w-full">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0 overflow-hidden">
                    <?php if(!empty($sekolah->logo) && file_exists(FCPATH . 'uploads/logo/' . $sekolah->logo)): ?>
                        <img src="<?= base_url('uploads/logo/' . $sekolah->logo) ?>" class="w-full h-full object-contain p-1" alt="Logo">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap text-indigo-600 text-sm"></i>
                    <?php endif; ?>
                </div>
                <span class="truncate" title="<?= esc($sekolah->nama_sekolah ?? 'Portal Siswa') ?>">
                    <?= esc($sekolah->nama_sekolah ?? 'Portal Siswa') ?>
                </span>
            </div>
        </div>

        <!-- Profile Brief -->
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($siswa['nama_lengkap'] ?? 'Siswa') ?>&background=4f46e5&color=fff" 
                     class="w-10 h-10 rounded-full border-2 border-white shadow-sm" alt="Avatar">
                <div class="overflow-hidden">
                    <h4 class="text-sm font-bold text-slate-900 truncate"><?= esc($siswa['nama_lengkap'] ?? 'Nama Siswa') ?></h4>
                    <p class="text-xs text-slate-500 truncate">NIS: <?= esc($siswa['nis'] ?? '-') ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <?php $uri = service('uri'); ?>
            
            <a href="<?= base_url('portal/siswa/dashboard') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-colors <?= ($uri->getSegment(3) == 'dashboard') ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' ?>">
                <i class="fas fa-home w-5"></i> Dashboard
            </a>
            
            <a href="<?= base_url('portal/siswa/jadwal') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-colors <?= ($uri->getSegment(3) == 'jadwal') ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' ?>">
                <i class="fas fa-calendar-alt w-5"></i> Jadwal Pelajaran
            </a>
            
            <a href="<?= base_url('portal/siswa/nilai') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-colors <?= ($uri->getSegment(3) == 'nilai' || $uri->getSegment(3) == 'rapor') ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' ?>">
                <i class="fas fa-book-open w-5"></i> Nilai & Rapor
            </a>
            
            <a href="<?= base_url('portal/siswa/keuangan') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-colors <?= ($uri->getSegment(3) == 'keuangan') ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' ?>">
                <i class="fas fa-file-invoice-dollar w-5"></i> Keuangan
            </a>
            
            <a href="<?= base_url('portal/siswa/profil') ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-colors <?= ($uri->getSegment(3) == 'profil') ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-slate-600 hover:text-indigo-600 hover:bg-slate-50' ?>">
                <i class="fas fa-user-circle w-5"></i> Profil Saya
            </a>
        </nav>

        <!-- Footer Logout -->
        <div class="p-4 border-t border-slate-100">
            <a href="<?= base_url('portal/siswa/logout') ?>" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 text-sm font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>
</aside>