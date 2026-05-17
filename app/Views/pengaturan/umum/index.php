<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div x-data="{ activeTab: 'profil' }" class="px-4 sm:px-6 py-6 max-w-7xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Manajemen konfigurasi SaaS, Integrasi, dan Identitas Tenant Sekolah.
            </p>
        </div>
        
        <?php if ($isGlobal): ?>
            <!-- Filter Tenant Khusus Yayasan -->
            <form action="" method="get" class="w-full md:w-72 relative z-10">
                <select name="jenjang" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-xs font-black uppercase text-slate-700 dark:text-slate-200 shadow-sm cursor-pointer">
                    <option value="GLOBAL" <?= $targetJenjang === 'GLOBAL' ? 'selected' : '' ?>>🌐 PENGATURAN PUSAT (YAYASAN)</option>
                    <?php foreach ($daftarUnit as $kode => $nama): ?>
                        <option value="<?= $kode ?>" <?= $targetJenjang === $kode ? 'selected' : '' ?>>🏫 PENGATURAN UNIT <?= strtoupper($kode) ?></option>
                    <?php endforeach; ?>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
            </form>
        <?php else: ?>
            <div class="px-5 py-3 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-lock"></i> Konfigurasi Unit <?= esc($targetJenjang) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ALERT HANDLERS -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-tight"><?= session()->getFlashdata('success') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>
    
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-rose-500 text-lg"></i>
                <span class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- MAIN SETTINGS CONTAINER -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col md:flex-row min-h-[650px] relative z-0">
        
        <!-- SIDEBAR TABS (NAVIGATION) -->
        <div class="w-full md:w-72 bg-slate-50 dark:bg-slate-950 border-b md:border-b-0 md:border-r border-slate-200 dark:border-slate-800 p-4 flex flex-col gap-2 shrink-0">
            
            <button @click="activeTab = 'profil'" :class="activeTab === 'profil' ? 'bg-white dark:bg-slate-800 shadow-sm border-indigo-500 text-indigo-700 dark:text-indigo-400' : 'border-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800/50'" class="w-full text-left px-4 py-3.5 rounded-xl border-l-4 text-xs font-black uppercase tracking-widest transition-all">
                <i class="fas fa-school w-5 text-center mr-1"></i> Identitas & Profil
            </button>
            
            <button @click="activeTab = 'nusantaraerp'" :class="activeTab === 'nusantaraerp' ? 'bg-white dark:bg-slate-800 shadow-sm border-indigo-500 text-indigo-700 dark:text-indigo-400' : 'border-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800/50'" class="w-full text-left px-4 py-3.5 rounded-xl border-l-4 text-xs font-black uppercase tracking-widest transition-all">
                <i class="fas fa-network-wired w-5 text-center mr-1"></i> Integrasi NusantaraERP
            </button>

            <!-- Menu SaaS Hanya Untuk Yayasan -->
            <?php if($isGlobal && $targetJenjang === 'GLOBAL'): ?>
                <button @click="activeTab = 'saas'" :class="activeTab === 'saas' ? 'bg-white dark:bg-slate-800 shadow-sm border-emerald-500 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800/50'" class="w-full text-left px-4 py-3.5 rounded-xl border-l-4 text-xs font-black uppercase tracking-widest transition-all">
                    <i class="fas fa-server w-5 text-center mr-1"></i> Manajemen SaaS
                </button>
            <?php endif; ?>

            <div class="my-4 border-t border-slate-200 dark:border-slate-800"></div>

            <div class="px-4 py-2 text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                Pintasan Manajemen User
            </div>

            <!-- Shortcut ke Modul RBAC (Users/Roles) -->
            <a href="<?= base_url('app/pengaturan/pengguna') ?>" class="w-full text-left px-4 py-3 rounded-xl border-l-4 border-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800/50 text-xs font-black uppercase tracking-widest transition-all">
                <i class="fas fa-users-cog w-5 text-center mr-1"></i> Pengguna Sistem
            </a>
            <a href="<?= base_url('app/pengaturan/hak_akses') ?>" class="w-full text-left px-4 py-3 rounded-xl border-l-4 border-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800/50 text-xs font-black uppercase tracking-widest transition-all">
                <i class="fas fa-user-shield w-5 text-center mr-1"></i> Otoritas & Roles
            </a>
        </div>

        <!-- TAB CONTENT AREA -->
        <div class="flex-1 p-6 md:p-10 relative">
            <form action="<?= base_url('app/pengaturan/umum/update') ?>" method="post" class="h-full flex flex-col">
                <?= csrf_field() ?>
                <input type="hidden" name="target_jenjang" value="<?= esc($targetJenjang) ?>">

                <!-- ============================================== -->
                <!-- TAB 1: IDENTITAS & PROFIL -->
                <!-- ============================================== -->
                <div x-show="activeTab === 'profil'" x-transition.opacity class="flex-grow">
                    <div class="mb-8 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight">Profil Instansi / Unit</h2>
                        <p class="text-xs text-slate-500 mt-1">Data ini akan menjadi dasar identitas pada Kop Surat, Invoice, dan Portal.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama Instansi / Institusi</label>
                            <input type="text" name="settings[nama_yayasan]" value="<?= esc($settings['nama_yayasan'] ?? '') ?>" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-bold text-slate-800 dark:text-white" placeholder="Contoh: YAYASAN PENDIDIKAN RAHMANY">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Motto / Slogan</label>
                            <input type="text" name="settings[motto]" value="<?= esc($settings['motto'] ?? '') ?>" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium italic text-slate-800 dark:text-white" placeholder="Membangun Generasi Unggul">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Email Resmi</label>
                            <input type="email" name="settings[email]" value="<?= esc($settings['email'] ?? '') ?>" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-bold text-slate-800 dark:text-white" placeholder="admin@domain.com">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nomor Telepon</label>
                            <input type="text" name="settings[telepon]" value="<?= esc($settings['telepon'] ?? '') ?>" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-bold text-slate-800 dark:text-white" placeholder="(021) 123456">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Alamat Lengkap</label>
                            <textarea name="settings[alamat]" rows="3" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium text-slate-800 dark:text-white resize-none" placeholder="Alamat jalan, kota, provinsi"><?= esc($settings['alamat'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 2: INTEGRASI NUSANTARAERP & KONTAK DISKUSI -->
                <!-- ============================================== -->
                <div x-show="activeTab === 'nusantaraerp'" x-transition.opacity style="display: none;" class="flex-grow">
                    <div class="mb-8 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-network-wired text-indigo-500"></i> Integrasi NusantaraERP
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Konfigurasi Endpoint API dan saluran layanan bantuan (Helpdesk) eksternal.</p>
                    </div>

                    <!-- Blok Konfigurasi API -->
                    <div class="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-800/50 mb-8 relative overflow-hidden">
                        <i class="fas fa-server absolute -right-4 -bottom-4 text-7xl text-indigo-500 opacity-5"></i>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-2">NusantaraERP Base URL Endpoint</label>
                                <input type="url" name="settings[nusantaraerp_url]" value="<?= esc($settings['nusantaraerp_url'] ?? '') ?>" class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-indigo-200 dark:border-indigo-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-mono font-bold text-slate-800 dark:text-white" placeholder="https://erp.sekolah-anda.com">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-2">Database Name</label>
                                <input type="text" name="settings[nusantaraerp_db]" value="<?= esc($settings['nusantaraerp_db'] ?? '') ?>" class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-indigo-200 dark:border-indigo-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-mono font-bold text-slate-800 dark:text-white" placeholder="sekolah_prod">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-2">NusantaraERP API Key / Token</label>
                                <input type="password" name="settings[nusantaraerp_api_key]" value="<?= esc($settings['nusantaraerp_api_key'] ?? '') ?>" class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-indigo-200 dark:border-indigo-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-mono font-bold text-slate-800 dark:text-white" placeholder="••••••••••••••••">
                            </div>
                        </div>
                    </div>

                    <!-- Blok Kontak & Diskusi -->
                    <div class="mb-6">
                        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest mb-4">Saluran Komunikasi Internal</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Link Channel Diskusi NusantaraERP</label>
                            <input type="url" name="settings[nusantaraerp_discuss_link]" value="<?= esc($settings['nusantaraerp_discuss_link'] ?? '') ?>" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-bold text-slate-800 dark:text-white" placeholder="https://nusantaraerp.sekolah.com/discuss/channel/...">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Email Helpdesk / Tiket</label>
                            <input type="email" name="settings[nusantaraerp_helpdesk_email]" value="<?= esc($settings['nusantaraerp_helpdesk_email'] ?? '') ?>" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-bold text-slate-800 dark:text-white" placeholder="support@sekolah.com">
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 3: MANAJEMEN SAAS (HANYA YAYASAN) -->
                <!-- ============================================== -->
                <?php if($isGlobal && $targetJenjang === 'GLOBAL'): ?>
                <div x-show="activeTab === 'saas'" x-transition.opacity style="display: none;" class="flex-grow">
                    <div class="mb-8 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                            <i class="fas fa-server text-emerald-500"></i> Subscription Control
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">Kelola lisensi *Software as a Service* (SaaS), limit kuota server, dan siklus penagihan.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-emerald-50 dark:bg-emerald-900/20 p-6 rounded-2xl border border-emerald-100 dark:border-emerald-800/50">
                        <div>
                            <label class="block text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-2">Status Lisensi ERP</label>
                            <div class="relative">
                                <select name="settings[saas_status]" class="w-full pl-4 pr-10 py-3.5 bg-white dark:bg-slate-950 border border-emerald-200 dark:border-emerald-700 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-bold text-slate-800 dark:text-white appearance-none cursor-pointer shadow-sm">
                                    <option value="Active" <?= ($settings['saas_status'] ?? '') === 'Active' ? 'selected' : '' ?>>🟢 ACTIVE (Pro Plan)</option>
                                    <option value="Trial" <?= ($settings['saas_status'] ?? '') === 'Trial' ? 'selected' : '' ?>>🟡 TRIAL (Evaluasi)</option>
                                    <option value="Suspended" <?= ($settings['saas_status'] ?? '') === 'Suspended' ? 'selected' : '' ?>>🔴 SUSPENDED (Belum Bayar)</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-emerald-500 pointer-events-none"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-2">Valid Until (Batas Waktu)</label>
                            <input type="date" name="settings[saas_expired_date]" value="<?= esc($settings['saas_expired_date'] ?? '') ?>" class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-emerald-200 dark:border-emerald-700 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-bold text-slate-800 dark:text-white shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-2">Kuota Penyimpanan (GB)</label>
                            <input type="number" name="settings[saas_storage_limit]" value="<?= esc($settings['saas_storage_limit'] ?? '10') ?>" class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-emerald-200 dark:border-emerald-700 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-bold text-slate-800 dark:text-white shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest mb-2">Maksimal User (Akun)</label>
                            <input type="number" name="settings[saas_user_limit]" value="<?= esc($settings['saas_user_limit'] ?? '500') ?>" class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-emerald-200 dark:border-emerald-700 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none text-sm font-bold text-slate-800 dark:text-white shadow-sm">
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- SUBMIT BUTTON BOTTOM STICKY -->
                <div class="mt-10 pt-6 border-t border-slate-200 dark:border-slate-800 flex justify-end shrink-0">
                    <button type="submit" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 border-b-4 border-indigo-800 w-full md:w-auto justify-center">
                        <i class="fas fa-save"></i> Simpan Konfigurasi
                    </button>
                </div>
                
            </form>
        </div>
    </div>

</div>

<?= $this->endSection() ?>