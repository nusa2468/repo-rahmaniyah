<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    <?= isset($mapel['id']) ? 'Edit' : 'Tambah' ?> Mata Pelajaran
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php 
    $is_edit = isset($mapel['id']);
    $url = $is_edit 
        ? base_url('app/masterdata/matapelajaran/update/' . $mapel['id']) 
        : base_url('app/masterdata/matapelajaran/create');
    
    // LOGIKA DATA & OTORITAS
    $session = session();
    $userJenjang = strtoupper($session->get('kode_jenjang') ?? '');
    $globalRoles = ['GLOBAL', 'YAYASAN', 'PUSAT', ''];
    $isSuperAdmin = in_array($userJenjang, $globalRoles);
    $is_restricted = !$isSuperAdmin;

    // Ambil nilai unit (Prioritas: Old Input > Database > Session User)
    $currentJenjang = old('kode_jenjang', $mapel['kode_jenjang'] ?? ($isSuperAdmin ? '' : $userJenjang));
    $currentTingkat = old('tingkat', $mapel['tingkat'] ?? '');
    $currentSemester = old('semester', $mapel['semester'] ?? ''); 
    $currentKurikulumId = old('kurikulum_id', $mapel['kurikulum_id'] ?? '');

    $getValue = function($data, $key) {
        if (is_object($data)) return $data->{$key} ?? null;
        if (is_array($data)) return $data[$key] ?? null;
        return null;
    };
?>

<div class="max-w-4xl mx-auto space-y-6 pb-10" 
     x-data="{ 
        // State Form - Diambil langsung dari PHP
        unit: '<?= $currentJenjang ?>',
        tingkat: '<?= $currentTingkat ?>',
        semester: '<?= $currentSemester ?>',
        
        // Bobot Penilaian
        tugas: <?= old('bobot_tugas', $mapel['bobot_tugas'] ?? 0.40) ?>,
        uts: <?= old('bobot_uts', $mapel['bobot_uts'] ?? 0.20) ?>,
        uas: <?= old('bobot_uas', $mapel['bobot_uas'] ?? 0.30) ?>,
        absen: <?= old('bobot_absensi', $mapel['bobot_absensi'] ?? 0.10) ?>,
        
        get total() { 
            let sum = (parseFloat(this.tugas || 0) + parseFloat(this.uts || 0) + parseFloat(this.uas || 0) + parseFloat(this.absen || 0));
            return parseFloat(sum.toFixed(2));
        },
        
        // Logika Filter Tingkat
        checkGrade(g) {
            if (!this.unit) return true; 
            const u = this.unit.toUpperCase();
            if (['SD', 'MI', 'SDLB'].some(x => u.includes(x))) return g >= 1 && g <= 6;
            if (['SMP', 'MTS', 'SMPLB'].some(x => u.includes(x))) return g >= 7 && g <= 9;
            if (['SMA', 'SMK', 'MA', 'SMALB'].some(x => u.includes(x))) return g >= 10 && g <= 12;
            if (['TK', 'PAUD', 'RA', 'KB'].some(x => u.includes(x))) return g == 0;
            return true; 
        }
     }"
     x-init="
        // Watcher untuk mereset tingkat jika unit berubah (kecuali inisialisasi awal)
        $watch('unit', (value, oldValue) => {
            if (oldValue !== undefined && tingkat !== '' && !checkGrade(parseInt(tingkat))) {
                tingkat = '';
            }
        })
     ">
    
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                <?= $is_edit ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran' ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Konfigurasi kurikulum, tingkat, dan pembobotan nilai akhir.</p>
        </div>
        <a href="<?= base_url('app/masterdata/matapelajaran') ?>" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 text-slate-600 dark:text-slate-300 text-sm font-bold rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-xs"></i> Kembali
        </a>
    </div>

    <!-- Error Handling -->
    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="p-4 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 rounded-2xl space-y-1">
            <p class="text-xs font-black text-rose-600 uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan
            </p>
            <ul class="text-[10px] text-rose-500 font-bold list-disc list-inside">
                <?php foreach (session()->getFlashdata('errors') as $e) : ?>
                    <li><?= $e ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= $url ?>" method="post" class="space-y-6">
        <?= csrf_field() ?>
        <?php if ($is_edit) : ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Identitas Mapel -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-white/5 shadow-sm space-y-4">
                <h5 class="text-[10px] font-black uppercase tracking-widest text-sky-600 mb-4 flex items-center gap-2">
                    <i class="fas fa-university"></i> Afiliasi & Struktur Jenjang
                </h5>

                <div class="grid grid-cols-12 gap-4">
                    <!-- Unit Jenjang -->
                    <div class="col-span-12 md:col-span-4">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Unit Jenjang</label>
                        <div class="relative">
                            <select name="kode_jenjang" required x-model="unit"
                                    <?= ($is_restricted || $is_edit) ? 'disabled' : '' ?>
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs appearance-none">
                                <option value="">Pilih Unit</option>
                                <?php if (!empty($jenjang_list)): foreach ($jenjang_list as $j) : 
                                    $j_kode = $getValue($j, 'kode_jenjang');
                                ?>
                                    <option value="<?= $j_kode ?>" <?= ($currentJenjang == $j_kode) ? 'selected' : '' ?>><?= strtoupper($j_kode) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                        <?php if($is_restricted || $is_edit): ?>
                            <input type="hidden" name="kode_jenjang" x-bind:value="unit">
                        <?php endif; ?>
                    </div>

                    <!-- Kurikulum -->
                    <div class="col-span-12 md:col-span-8">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kurikulum</label>
                        <div class="relative">
                            <select name="kurikulum_id" required
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs appearance-none">
                                <option value="">Pilih Kurikulum</option>
                                <?php if (isset($kurikulum)) : foreach ($kurikulum as $k) : ?>
                                    <option value="<?= $k['id'] ?>" <?= ($currentKurikulumId == $k['id']) ? 'selected' : '' ?>>
                                        <?= esc($k['nama_kurikulum']) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Tingkat -->
                    <div class="col-span-6">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Tingkat</label>
                        <div class="relative">
                            <select name="tingkat" required x-model="tingkat"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs appearance-none">
                                <option value="">Pilih Tingkat</option>
                                <option value="0" x-show="checkGrade(0)">0 (TK/PAUD)</option>
                                <?php for ($i=1; $i<=12; $i++): ?>
                                    <option value="<?= $i ?>" x-show="checkGrade(<?= $i ?>)">Kelas <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Semester -->
                    <div class="col-span-6">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Semester</label>
                        <div class="relative">
                            <select name="semester" x-model="semester"
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs appearance-none">
                                <option value="">Semua Semester</option>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-white/5 my-2">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kode Mapel</label>
                        <input type="text" name="kode_mapel" value="<?= old('kode_mapel', $mapel['kode_mapel'] ?? '') ?>" required 
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs uppercase">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kelompok</label>
                        <div class="relative">
                            <select name="kelompok" required
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs appearance-none">
                                <option value="A" <?= (old('kelompok', $mapel['kelompok'] ?? '') == 'A') ? 'selected' : '' ?>>Kelompok A</option>
                                <option value="B" <?= (old('kelompok', $mapel['kelompok'] ?? '') == 'B') ? 'selected' : '' ?>>Kelompok B</option>
                                <option value="C" <?= (old('kelompok', $mapel['kelompok'] ?? '') == 'C') ? 'selected' : '' ?>>Kelompok C</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" value="<?= old('nama_mapel', $mapel['nama_mapel'] ?? '') ?>" required 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">JP</label>
                        <input type="number" name="jumlah_jp" value="<?= old('jumlah_jp', $mapel['jumlah_jp'] ?? '') ?>" required
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs text-center">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                        <div class="relative">
                            <select name="status" required
                                    class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-2 focus:ring-sky-500 dark:text-white font-bold text-xs appearance-none">
                                <option value="aktif" <?= (old('status', $mapel['status'] ?? 'aktif') == 'aktif') ? 'selected' : '' ?>>AKTIF</option>
                                <option value="tidak aktif" <?= (old('status', $mapel['status'] ?? '') == 'tidak aktif') ? 'selected' : '' ?>>NON-AKTIF</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bobot Penilaian -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-white/5 shadow-sm space-y-4 flex flex-col">
                <h5 class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-4 flex items-center gap-2">
                    <i class="fas fa-percentage"></i> Bobot Penilaian (100%)
                </h5>

                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-white/10">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[9px] font-black text-slate-400">TOTAL AKUMULASI</span>
                        <span :class="total == 1.00 ? 'text-emerald-500' : 'text-rose-500'" class="text-xl font-black" x-text="(total * 100).toFixed(0) + '%'"></span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-500" :style="`width: ${Math.min(total * 100, 100)}%;`" :class="total == 1.00 ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 flex-1 pt-2">
                    <div class="space-y-1">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase">Tugas</label>
                        <input type="number" step="0.01" name="bobot_tugas" x-model="tugas" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl text-center font-black text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase">UTS</label>
                        <input type="number" step="0.01" name="bobot_uts" x-model="uts" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl text-center font-black text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase">UAS</label>
                        <input type="number" step="0.01" name="bobot_uas" x-model="uas" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl text-center font-black text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[9px] font-bold text-slate-500 uppercase">Absen</label>
                        <input type="number" step="0.01" name="bobot_absensi" x-model="absen" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-xl text-center font-black text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3 pt-6">
            <button type="submit" :disabled="total != 1.00"
                    class="px-10 py-3 bg-sky-600 hover:bg-sky-700 disabled:opacity-30 text-white text-xs font-black rounded-xl shadow-lg transition-all uppercase tracking-wide">
                <i class="fas fa-save mr-2"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Daftarkan Mapel' ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>