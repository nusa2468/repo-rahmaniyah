<?= $this->extend('auth/auth_layout') ?>

<?= $this->section('content') ?>

<!-- Sisi Visual (Hanya muncul di Layar Besar) -->
<div class="hidden lg:block lg:w-1/2 relative bg-gray-100">
    
    <!-- Overlay dikurangi kegelapannya -->
    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/60 z-10"></div>
    
    <!-- 
        PERBAIKAN FINAL (Berdasarkan file):
        1. Nama file: login-yaji.jpg (ada tanda hubung dan ekstensi jpg)
        2. Path: uploads/lain2x/
        3. Jika masih tidak muncul, pastikan file 'login-yaji.jpg' benar-benar ada di folder 'public/uploads/lain2x/'
    -->
    <img src="<?= base_url('uploads/lain2x/login-rahmaniyah.png') ?>" 
         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1598556828105-06b66d9c0205?q=80&w=1974&auto=format&fit=crop';"
         class="w-full h-full object-cover object-center" 
         alt="Lingkungan Sekolah Asri">
    
    <!-- Overlay Info Sekolah -->
    <div class="absolute bottom-0 left-0 right-0 p-12 text-white z-20">
        <div class="bg-white/10 backdrop-blur-md p-8 rounded-3xl border border-white/20 shadow-2xl animate-fade-in-up">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <i class="fas fa-mosque text-xl"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase tracking-widest text-emerald-300">Pusat Pendidikan</h4>
                    <h3 class="text-xl font-black tracking-tight text-white">Rahmaniyah</h3>
                </div>
            </div>
            <p class="text-sm text-slate-100 leading-relaxed font-medium opacity-90">
                Membangun Peradaban dengan Ilmu dan Amal
            </p>
        </div>
    </div>
</div>

<!-- Sisi Form Login -->
<div class="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-16 bg-white">
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="mb-10 text-center lg:text-left">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 text-3xl mb-6 shadow-sm border border-emerald-100 transform transition-transform hover:scale-105">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Selamat Datang!</h1>
            <p class="text-slate-500 font-medium">Silakan masuk ke Portal Akademik Terpadu.</p>
        </div>

        <!-- Notifikasi Error -->
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-xl flex items-center gap-3 animate-in fade-in slide-in-from-top-2 shadow-sm">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm text-rose-700 font-bold"><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="<?= base_url('login') ?>" method="post" class="space-y-6">
            <?= csrf_field() ?>
            
            <div class="space-y-2">
                <label for="login" class="block text-xs font-black text-slate-500 uppercase tracking-widest ml-1">ID Pengguna</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-emerald-600 transition-colors">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" 
                           id="login" 
                           name="login" 
                           required
                           class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-600 focus:bg-white transition-all outline-none text-slate-900 placeholder:text-slate-400 font-bold text-sm" 
                           placeholder="NIP / NISN / Username" 
                           value="<?= old('login') ?>">
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between items-center ml-1">
                    <label for="password" class="block text-xs font-black text-slate-500 uppercase tracking-widest">Kata Sandi</label>
                    <a href="#" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">Lupa Sandi?</a>
                </div>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 group-focus-within:text-emerald-600 transition-colors">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-600 focus:bg-white transition-all outline-none text-slate-900 placeholder:text-slate-400 font-bold text-sm" 
                           placeholder="••••••••">
                </div>
            </div>

            <div class="flex items-center ml-1">
                <input type="checkbox" id="remember" class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500 cursor-pointer">
                <label for="remember" class="ml-2 text-sm text-slate-600 font-bold cursor-pointer select-none">Ingat saya di perangkat ini</label>
            </div>

            <button type="submit" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-emerald-600/30 transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 uppercase tracking-wider text-xs group">
                <span>Masuk Dashboard</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-100"></div></div>
            <div class="relative flex justify-center text-[10px] uppercase"><span class="bg-white px-4 text-slate-400 font-black tracking-widest">Atau</span></div>
        </div>

        <!-- Help Link -->
        <div class="text-center">
            <p class="text-slate-500 text-sm font-medium">
                Ada kendala akses? 
                <a href="#" class="text-emerald-600 font-black hover:underline decoration-2">Hubungi Admin IT</a>
            </p>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.8s ease-out forwards;
    }
</style>

<?= $this->endSection() ?>