<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    <?= isset($kelas['id']) ? 'Edit' : 'Tambah' ?> Rombongan Belajar
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $is_edit = isset($kelas['id']);
    // Sinkronisasi rute sesuai app/Config/Routes.php
    $url = $is_edit 
        ? base_url('app/masterdata/kelas/update/' . $kelas['id']) 
        : base_url('app/masterdata/kelas/create');
    
    // 1. SCOPE UNIT LOGIC
    $session = session();
    $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
    $globalRoles = ['GLOBAL', 'YAYASAN', 'PUSAT'];
    $isSuperAdmin = in_array($userJenjang, $globalRoles);

    // Filter unit default jika bukan superadmin
    $currentJenjang = old('kode_jenjang', $kelas['kode_jenjang'] ?? ($isSuperAdmin ? '' : $userJenjang));
    
    $errors = session()->get('errors') ?? [];

    // Helper untuk menangani Object vs Array secara aman
    $getValue = function($data, $key) {
        if (is_object($data)) return $data->{$key} ?? null;
        if (is_array($data)) return $data[$key] ?? null;
        return null;
    };
?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="max-w-5xl mx-auto space-y-6 pb-10 font-sans antialiased text-slate-800"
     x-data="{ 
        unit: '<?= $currentJenjang ?>',
        isEdit: <?= $is_edit ? 'true' : 'false' ?>,
        
        // Logika Smart Grade (Tingkat)
        checkGrade(g) {
            if (!this.unit) return true; // Tampilkan semua jika unit belum dipilih
            const u = this.unit.toUpperCase();
            
            // Logika Jenjang (Case Insensitive & Variasi Nama)
            if (['SD', 'MI', 'SDLB'].some(x => u.includes(x))) return g >= 1 && g <= 6;
            if (['SMP', 'MTS', 'SMPLB'].some(x => u.includes(x))) return g >= 7 && g <= 9;
            if (['SMA', 'SMK', 'MA', 'SMALB'].some(x => u.includes(x))) return g >= 10 && g <= 12;
            if (['TK', 'PAUD', 'KB', 'RA'].some(x => u.includes(x))) return g === 0;
            
            return true; // Default tampilkan semua jika jenjang tidak dikenali
        }
     }">

    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/masterdata/kelas') ?>" class="hover:text-indigo-600 transition-colors">MASTER DATA</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600 italic">FORM ROMBEL</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic leading-none">
                <?= $is_edit ? 'Konfigurasi Rombel' : 'Registrasi Rombel Baru' ?>
            </h1>
        </div>
        <a href="<?= base_url('app/masterdata/kelas') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white border-2 border-slate-100 hover:border-indigo-200 text-slate-500 hover:text-indigo-600 text-xs font-black transition-all shadow-sm group">
            <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i> KEMBALI
        </a>
    </div>

    <!-- MAIN FORM CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden">
        
        <!-- Top Status Bar -->
        <div class="px-10 py-5 border-b border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-indigo-500 animate-pulse shadow-lg shadow-indigo-200"></div>
                <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-600 dark:text-slate-400">
                    Parameter Akademik & Struktural
                </h3>
            </div>
            <?php if ($is_edit): ?>
                <span class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest italic shadow-md">UID: #<?= $kelas['id'] ?></span>
            <?php endif; ?>
        </div>

        <form action="<?= $url ?>" method="post" class="p-10 space-y-10">
            <?= csrf_field() ?>
            <?php if ($is_edit): ?>
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="id" value="<?= $kelas['id'] ?>">
            <?php endif ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <!-- KOLOM KIRI: IDENTITAS DASAR -->
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-black shadow-sm border border-indigo-100">01</div>
                        <h4 class="text-sm font-black uppercase text-slate-800 dark:text-slate-200 tracking-widest italic">Detail Rombongan Belajar</h4>
                    </div>

                    <!-- UNIT JENJANG (Locked if Edit OR non-superadmin) -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Unit Pendidikan / Jenjang</label>
                        <div class="relative">
                            <select name="kode_jenjang" required x-model="unit"
                                    <?= (!$isSuperAdmin || $is_edit) ? 'disabled' : '' ?>
                                    class="w-full rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-gray-950 px-5 py-4 text-xs font-black uppercase tracking-widest appearance-none transition-all focus:border-indigo-500 outline-none <?= (!$isSuperAdmin || $is_edit) ? 'opacity-70 cursor-not-allowed' : 'hover:border-slate-200' ?>">
                                <option value="">-- PILIH UNIT --</option>
                                <?php foreach (($jenjang_list ?? []) as $j): 
                                    $val = $getValue($j, 'kode_jenjang');
                                    // Fallback label jika nama_jenjang kosong
                                    $label = $getValue($j, 'nama_jenjang') ?? ('UNIT ' . strtoupper($val));
                                ?>
                                    <option value="<?= $val ?>" <?= ($currentJenjang == $val) ? 'selected' : '' ?>>
                                        <?= strtoupper($label) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                        </div>
                        
                        <!-- Kirim value via hidden input jika disabled -->
                        <?php if (!$isSuperAdmin || $is_edit): ?>
                            <input type="hidden" name="kode_jenjang" :value="unit">
                            <p class="text-[9px] font-bold text-amber-600 uppercase italic mt-2 ml-1">
                                <i class="fas fa-lock mr-1"></i> Unit terkunci (Mode Edit / Otoritas).
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- NAMA KELAS -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Rombel</label>
                        <div class="relative group">
                            <input type="text" name="nama_kelas" required placeholder="E.g: X MIPA 1 atau 2-B"
                                   value="<?= old('nama_kelas', $kelas['nama_kelas'] ?? '') ?>"
                                   class="w-full rounded-2xl border-2 <?= isset($errors['nama_kelas']) ? 'border-rose-500' : 'border-slate-100 dark:border-slate-800' ?> px-5 py-4 text-sm font-black dark:bg-gray-950 shadow-inner focus:border-indigo-500 focus:bg-white transition-all outline-none uppercase italic tracking-tight">
                            <i class="fas fa-edit absolute right-5 top-1/2 -translate-y-1/2 text-slate-200 group-focus-within:text-indigo-400 transition-colors"></i>
                        </div>
                        <?php if(isset($errors['nama_kelas'])): ?>
                            <p class="text-[9px] font-bold text-rose-500 mt-1 ml-1 uppercase"><?= $errors['nama_kelas'] ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- TINGKAT (Smart Grade Selector) -->
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tingkat Pendidikan (Grade)</label>
                        
                        <!-- Grid Angka 1-12 -->
                        <div class="grid grid-cols-6 gap-2">
                            <?php for ($t=1; $t<=12; $t++): 
                                $isActive = old('tingkat', $kelas['tingkat'] ?? '') == $t;
                            ?>
                                <!-- Bungkus dengan x-show untuk filter smart -->
                                <label class="cursor-pointer group" x-show="checkGrade(<?= $t ?>)">
                                    <input type="radio" name="tingkat" value="<?= $t ?>" class="peer hidden" <?= $isActive ? 'checked' : '' ?> required>
                                    <div class="py-3 text-center rounded-xl border-2 border-slate-50 dark:border-slate-800 text-[11px] font-black peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-checked:shadow-lg peer-checked:shadow-indigo-200 transition-all hover:bg-slate-100 dark:hover:bg-slate-800">
                                        <?= $t ?>
                                    </div>
                                </label>
                            <?php endfor; ?>
                            
                            <!-- Opsi TK/PAUD (Level 0) -->
                             <label class="cursor-pointer group col-span-2" x-show="checkGrade(0)">
                                <input type="radio" name="tingkat" value="0" class="peer hidden" <?= (old('tingkat', $kelas['tingkat'] ?? '') === '0') ? 'checked' : '' ?> required>
                                <div class="py-3 text-center rounded-xl border-2 border-slate-50 dark:border-slate-800 text-[11px] font-black peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-checked:shadow-lg peer-checked:shadow-indigo-200 transition-all hover:bg-slate-100 dark:hover:bg-slate-800">
                                    TK/PAUD
                                </div>
                            </label>
                        </div>
                         <!-- Pesan jika belum pilih unit -->
                        <div x-show="!unit" class="text-[10px] text-rose-500 font-bold italic mt-2">
                            * Silakan pilih Unit Jenjang terlebih dahulu untuk melihat pilihan tingkat.
                        </div>
                    </div>

                    <!-- JURUSAN -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Program Studi / Jurusan</label>
                        <div class="relative">
                            <select name="id_jurusan"
                                    class="w-full rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-gray-950 px-5 py-4 text-xs font-black uppercase tracking-widest appearance-none focus:border-indigo-500 outline-none transition-all">
                                <option value="">-- TANPA JURUSAN (UMUM) --</option>
                                <?php foreach (($jurusan_list ?? []) as $jur): ?>
                                    <option value="<?= $getValue($jur, 'id') ?>" <?= (old('id_jurusan', $kelas['id_jurusan'] ?? '') == $getValue($jur, 'id')) ? 'selected' : '' ?>>
                                        <?= esc($getValue($jur, 'nama_jurusan')) ?> [<?= strtoupper(esc($getValue($jur, 'kode_jenjang'))) ?>]
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <i class="fas fa-graduation-cap absolute right-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: PENEMPATAN & AKADEMIK -->
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-black shadow-sm border border-emerald-100">02</div>
                        <h4 class="text-sm font-black uppercase text-slate-800 dark:text-slate-200 tracking-widest italic">Wali Kelas & Periode</h4>
                    </div>

                    <!-- WALI KELAS -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pimpinan Rombel (Wali Kelas)</label>
                        <div class="relative">
                            <select name="id_wali_kelas" required
                                    class="w-full rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-gray-950 px-5 py-4 text-xs font-black uppercase tracking-widest appearance-none focus:border-indigo-500 outline-none transition-all shadow-sm">
                                <option value="">-- PILIH GURU PENGAJAR --</option>
                                <?php foreach (($guru_list ?? []) as $g): 
                                    $gId = $getValue($g, 'id');
                                ?>
                                    <option value="<?= $gId ?>" <?= (old('id_wali_kelas', $kelas['id_wali_kelas'] ?? '') == $gId) ? 'selected' : '' ?>>
                                        <?= esc($getValue($g, 'nama_lengkap')) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <i class="fas fa-user-shield absolute right-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        </div>
                        <p class="text-[9px] text-slate-400 mt-2 italic">* Hanya menampilkan guru yang berstatus aktif pada unit <?= esc($userJenjang) ?>.</p>
                    </div>

                    <!-- TAHUN AJARAN -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tahun Ajaran Aktif</label>
                        <div class="relative">
                            <select name="id_tahun_ajaran" required
                                    class="w-full rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-gray-950 px-5 py-4 text-xs font-black uppercase tracking-widest appearance-none focus:border-indigo-500 outline-none transition-all shadow-sm">
                                <?php foreach (($tahun_ajaran_list ?? []) as $ta): 
                                    $taId = $getValue($ta, 'id');
                                ?>
                                    <option value="<?= $taId ?>" <?= (old('id_tahun_ajaran', $kelas['id_tahun_ajaran'] ?? '') == $taId) ? 'selected' : '' ?>>
                                        PERIODE <?= $getValue($ta, 'tahun_ajaran') ?> (<?= strtoupper($getValue($ta, 'semester')) ?>)
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <i class="fas fa-calendar-alt absolute right-5 top-1/2 -translate-y-1/2 text-slate-300"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- KURIKULUM -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Standar Kurikulum</label>
                            <select name="id_kurikulum" required
                                    class="w-full rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-gray-950 px-5 py-4 text-[10px] font-black uppercase tracking-widest appearance-none focus:border-indigo-500 outline-none transition-all">
                                <?php foreach (($kurikulum_list ?? []) as $kur): 
                                    $kId = $getValue($kur, 'id');
                                ?>
                                    <option value="<?= $kId ?>" <?= (old('id_kurikulum', $kelas['id_kurikulum'] ?? '') == $kId) ? 'selected' : '' ?>>
                                        <?= esc($getValue($kur, 'nama_kurikulum')) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <!-- KAPASITAS -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Kapasitas Maksimal</label>
                            <div class="relative group">
                                <input type="number" name="kapasitas" required min="1" max="100"
                                       value="<?= old('kapasitas', $kelas['kapasitas'] ?? '36') ?>"
                                       class="w-full rounded-2xl border-2 border-slate-100 dark:border-slate-800 bg-white dark:bg-gray-950 px-5 py-3.5 text-sm font-black focus:border-indigo-500 transition-all outline-none">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-[9px] font-bold text-slate-300 uppercase italic">Siswa</div>
                            </div>
                        </div>
                    </div>

                    <!-- STATUS RADIO -->
                    <div class="pt-4">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border-2 border-slate-100 dark:border-white/5 flex items-center justify-between shadow-inner">
                            <div>
                                <h4 class="text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest leading-none mb-1">Status Rombel</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter italic">Aktifkan untuk membuka absensi & nilai.</p>
                            </div>
                            <div class="flex bg-white dark:bg-slate-900 p-1 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm shrink-0">
                                <?php $curr_stat = old('is_aktif', $kelas['is_aktif'] ?? 1); ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="is_aktif" value="1" class="peer hidden" <?= ($curr_stat == 1) ? 'checked' : '' ?>>
                                    <span class="inline-block px-6 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all text-slate-400 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-lg">OPEN</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="is_aktif" value="0" class="peer hidden" <?= ($curr_stat == 0) ? 'checked' : '' ?>>
                                    <span class="inline-block px-6 py-2 rounded-lg text-[9px] font-black uppercase tracking-widest transition-all text-slate-400 peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-lg">LOCK</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-10 border-t-2 border-slate-50 dark:border-white/5">
                <button type="reset" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-700 transition-all">Bersihkan Form</button>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="<?= base_url('app/masterdata/kelas') ?>" 
                       class="flex-1 sm:flex-none px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:bg-slate-100 transition-all text-center">Batal</a>
                    <button type="submit" 
                            class="flex-1 sm:flex-none px-12 py-4 bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-black uppercase tracking-[0.3em] rounded-2xl shadow-2xl shadow-indigo-200 dark:shadow-none transition-all active:scale-95 border-b-4 border-slate-700 dark:border-indigo-800 flex items-center justify-center gap-3">
                        <i class="fas fa-save"></i>
                        <span><?= $is_edit ? 'PERBARUI DATA' : 'SIMPAN ROMBEL' ?></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .animate-in { animation: fadeIn 0.5s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?= $this->endSection() ?>