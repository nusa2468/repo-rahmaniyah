<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Detail Rombongan Belajar
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-5xl mx-auto space-y-6 font-sans antialiased">
    <!-- Header & Back -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/masterdata/kelas') ?>" class="hover:text-indigo-600 transition-colors">ROMBEL</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600">DETAIL</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-none uppercase italic">
                <?= esc($kelas['nama_kelas']) ?>
            </h1>
        </div>
        <a href="<?= base_url('app/masterdata/kelas') ?>" class="px-5 py-2.5 bg-white border-2 border-slate-200 text-slate-600 text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-50 transition-all shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-[2.5rem] shadow-sm border-2 border-slate-50 overflow-hidden">
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-8 gap-x-12">
                <!-- Info Utama -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Kelas</h3>
                        <p class="text-xl font-black text-slate-900"><?= esc($kelas['nama_kelas']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Unit Sekolah</h3>
                        <span class="inline-block px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black rounded-lg uppercase tracking-wide border border-indigo-100">
                            UNIT <?= esc(strtoupper($kelas['kode_jenjang'])) ?>
                        </span>
                    </div>
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tingkat</h3>
                        <p class="text-sm font-bold text-slate-700">Tingkat <?= esc($kelas['tingkat']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tahun Ajaran</h3>
                        <p class="text-sm font-bold text-slate-700"><?= esc($kelas['tahun_ajaran'] ?? '-') ?></p>
                    </div>
                </div>

                <!-- Info Akademik -->
                <div class="space-y-6">
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Wali Kelas</h3>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900"><?= esc($kelas['nama_wali_kelas'] ?? 'Belum Ditentukan') ?></p>
                                <p class="text-[10px] text-slate-400 font-medium">NIP/Guru ID: -</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kurikulum</h3>
                        <p class="text-sm font-bold text-slate-700"><?= esc($kelas['nama_kurikulum'] ?? '-') ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kapasitas</h3>
                            <p class="text-lg font-black text-slate-800"><?= esc($kelas['kapasitas']) ?> <span class="text-xs font-medium text-slate-400">Siswa</span></p>
                        </div>
                        <div>
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</h3>
                            <?php if (($kelas['is_aktif'] ?? 0) == 1): ?>
                                <span class="text-emerald-600 font-black text-sm flex items-center gap-1"><i class="fas fa-check-circle"></i> AKTIF</span>
                            <?php else: ?>
                                <span class="text-slate-400 font-black text-sm flex items-center gap-1"><i class="fas fa-times-circle"></i> NON-AKTIF</span>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="bg-slate-50 p-6 border-t border-slate-100 flex justify-end gap-3">
            <a href="<?= base_url('app/masterdata/kelas/edit/' . $kelas['id']) ?>" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-amber-200 transition-all active:scale-95 flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Data
            </a>
            <form action="<?= base_url('app/masterdata/kelas/delete/' . $kelas['id']) ?>" method="post" onsubmit="return confirm('Hapus data ini permanen?')">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-rose-200 transition-all active:scale-95 flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>