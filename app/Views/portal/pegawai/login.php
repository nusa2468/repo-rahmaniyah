<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login Guru & Staff') ?></title>
    
    <!-- Reuse Assets -->
    <?= view('portal/siswa/partials/script'); ?>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
        <!-- Header Image/Logo (Emerald Theme) -->
        <div class="bg-emerald-600 p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full mix-blend-overlay filter blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-emerald-400/20 rounded-full mix-blend-overlay filter blur-xl translate-y-1/2 -translate-x-1/2"></div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 bg-white rounded-2xl mx-auto flex items-center justify-center shadow-lg mb-4">
                    <?php if(!empty($sekolah->logo) && file_exists(FCPATH . 'uploads/logo/' . $sekolah->logo)): ?>
                        <img src="<?= base_url('uploads/logo/' . $sekolah->logo) ?>" class="w-16 h-16 object-contain" alt="Logo">
                    <?php else: ?>
                        <i class="fas fa-chalkboard-teacher text-3xl text-emerald-600"></i>
                    <?php endif; ?>
                </div>
                <h2 class="text-2xl font-bold text-white"><?= esc($sekolah->nama_sekolah ?? 'Portal Pegawai') ?></h2>
                <p class="text-emerald-100 text-sm mt-1">Area Khusus Guru & Tenaga Kependidikan</p>
            </div>
        </div>

        <!-- Form -->
        <div class="p-8">
            <?php if(session()->getFlashdata('error')): ?>
                <div class="bg-rose-50 border border-rose-100 text-rose-600 text-sm p-3 rounded-xl mb-4 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if(session()->getFlashdata('success')): ?>
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 text-sm p-3 rounded-xl mb-4 flex items-center gap-2">
                    <i class="fas fa-check-circle"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('portal/pegawai/login') ?>" method="post" class="space-y-5">
                <?= csrf_field() ?>
                
                <div>
                    <!-- INPUT NAME HARUS 'username' AGAR MATCH CONTROLLER -->
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Username (NIP / NIPY / Email)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-id-badge text-slate-400"></i>
                        </div>
                        <input type="text" name="username" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all font-semibold" placeholder="Masukkan NIP, NIPY, atau Email" value="<?= old('username') ?>" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-slate-400"></i>
                        </div>
                        <input type="password" name="password" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all font-semibold" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-0.5">
                    Masuk Portal
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="<?= base_url('portal/siswa/login') ?>" class="text-xs font-bold text-slate-400 hover:text-emerald-600 transition-colors">
                    Masuk sebagai Siswa?
                </a>
            </div>
        </div>
    </div>

</body>
</html>