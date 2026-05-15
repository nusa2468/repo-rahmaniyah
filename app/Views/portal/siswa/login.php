<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Mengambil Data Sekolah untuk Title -->
    <?php
        $db = \Config\Database::connect();
        $sekolah = (object) ['nama_sekolah' => 'SIAKAD v2.0', 'logo' => null, 'alamat' => null];
        try {
            if ($db->tableExists('sekolah')) {
                $query = $db->table('sekolah')->limit(1)->get()->getRow();
                if ($query) $sekolah = $query;
            }
        } catch (\Throwable $e) {}
        
        $logo_path = 'uploads/logo/' . ($sekolah->logo ?? '');
        $logo_url = (!empty($sekolah->logo) && file_exists(FCPATH . $logo_path)) ? base_url($logo_path) : null;
    ?>
    <title><?= esc($title ?? 'Login Portal ' . $sekolah->nama_sekolah) ?></title>
    
    <!-- Tailwind CSS (CDN for Development) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .bg-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .animate-float { animation: float 6s ease-in-out infinite; }
    </style>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-4xl bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left Side: Visual / Branding -->
        <div class="w-full md:w-1/2 bg-slate-900 text-white p-12 flex flex-col justify-between relative overflow-hidden group">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600 rounded-full blur-[100px] opacity-20 group-hover:opacity-30 transition-opacity duration-700"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500 rounded-full blur-[100px] opacity-20 group-hover:opacity-30 transition-opacity duration-700"></div>
            
            <div class="relative z-10">
                <!-- Identitas Sekolah Header -->
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center shadow-inner border border-white/20 overflow-hidden shrink-0">
                        <?php if ($logo_url): ?>
                            <img src="<?= $logo_url ?>" alt="Logo" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-graduation-cap text-2xl text-blue-300"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="text-[10px] font-bold text-blue-300 uppercase tracking-[0.2em] mb-1">Official Portal</div>
                        <h2 class="font-black text-lg leading-none uppercase tracking-wide text-white">
                            <?= esc($sekolah->nama_sekolah) ?>
                        </h2>
                    </div>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-black leading-tight mb-6 tracking-tight">
                    Portal <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-emerald-400">Akademik</span> <br>
                    Siswa.
                </h1>
                <p class="text-slate-400 font-medium text-sm leading-relaxed max-w-xs">
                    Akses jadwal pelajaran, nilai ujian, tagihan, dan informasi akademik <?= esc($sekolah->nama_sekolah) ?> dalam satu genggaman.
                </p>
            </div>

            <div class="relative z-10 mt-12 md:mt-0">
                <div class="flex items-center gap-4 p-4 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 animate-float">
                    <div class="w-12 h-12 bg-emerald-500 rounded-full flex items-center justify-center text-white shadow-lg shrink-0">
                        <i class="fas fa-check text-xl"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-1">Status Sistem</div>
                        <div class="font-bold text-white text-sm">Server Online & Stabil</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-white relative">
            <div class="max-w-md mx-auto w-full">
                
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-slate-900 mb-2">Selamat Datang! 👋</h2>
                    <p class="text-slate-500 font-medium text-sm">Silakan masuk menggunakan NIS Anda.</p>
                </div>

                <!-- Alert Messages -->
                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl animate-pulse">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-circle-exclamation text-rose-500"></i>
                            <p class="text-xs font-bold text-rose-700 uppercase tracking-wide">
                                <?= session()->getFlashdata('error') ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')) : ?>
                    <div class="mb-6 bg-rose-50 border border-rose-100 p-4 rounded-xl">
                        <ul class="list-disc list-inside text-xs font-bold text-rose-600 space-y-1">
                            <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <p class="text-xs font-bold text-emerald-700 uppercase tracking-wide">
                                <?= session()->getFlashdata('success') ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <form id="loginForm" action="<?= base_url('portal/siswa/login/auth') ?>" method="post" class="space-y-6">
                    <?= csrf_field() ?>
                    
                    <div class="space-y-2">
                        <label for="nis" class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Nomor Induk Siswa (NIS)</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-id-card text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                            </div>
                            <input type="text" name="nis" id="nis" required 
                                   class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 text-sm focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none placeholder:text-slate-300 placeholder:font-medium"
                                   placeholder="Contoh: 20241050" 
                                   value="<?= old('nis') ?>"
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-[10px] font-black uppercase tracking-widest text-slate-400">Kata Sandi</label>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-300 group-focus-within:text-blue-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password" id="password" required 
                                   class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-800 text-sm focus:bg-white focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none placeholder:text-slate-300 placeholder:font-medium"
                                   placeholder="••••••••">
                            
                            <!-- Toggle Password -->
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer focus:outline-none transition-colors" tabindex="-1">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2 font-medium">
                            <i class="fas fa-circle-info mr-1 text-blue-400"></i> 
                            Default: NIS Anda
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-lg shadow-blue-500/30 transition-all active:scale-[0.98] uppercase tracking-widest text-xs flex items-center justify-center gap-2 group">
                        <span>Masuk Portal</span>
                        <i class="fas fa-arrow-right opacity-50 group-hover:translate-x-1 transition-transform"></i>
                    </button>

                </form>

                <!-- Footer Actions & Reset Button -->
                <div class="mt-8 text-center pt-6 border-t border-slate-100">
                    <p class="text-xs text-slate-400 font-medium mb-3">Mengalami kendala login?</p>
                    
                    <button type="button" onclick="triggerResetAccount()" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-colors mb-4">
                        <i class="fas fa-key"></i> Reset Password (Ke Default)
                    </button>
                    
                    <div>
                        <a href="<?= base_url() ?>" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Mobile Warning (Optional) -->
    <div class="fixed bottom-4 left-0 right-0 text-center md:hidden px-6 pointer-events-none">
        <p class="text-[10px] font-bold text-slate-400 bg-white/80 backdrop-blur-sm py-2 px-4 rounded-full shadow-sm inline-block">
            <?= esc($sekolah->nama_sekolah) ?> &copy; <?= date('Y') ?>
        </p>
    </div>

    <!-- Scripts -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Fitur Reset Akun Otomatis
        function triggerResetAccount() {
            const nis = document.getElementById('nis').value.trim();
            const passwordInput = document.getElementById('password');
            const form = document.getElementById('loginForm');

            if (!nis) {
                alert('Mohon isi NIS terlebih dahulu pada kolom input di atas untuk melakukan reset password.');
                document.getElementById('nis').focus();
                return;
            }

            if (confirm('Apakah Anda yakin ingin mereset password untuk NIS ' + nis + ' kembali ke default (sama dengan NIS)?\n\nPassword Anda akan diubah menjadi NIS Anda.')) {
                // Isi password dengan kode rahasia controller 'RESET_ME'
                passwordInput.value = 'RESET_ME';
                form.submit();
            }
        }
    </script>

</body>
</html>