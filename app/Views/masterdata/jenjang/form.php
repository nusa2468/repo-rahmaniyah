<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
// Fallback array
$data = $jenjang ?? []; 
$id = $data['id'] ?? null;
$is_edit = !empty($id);

// URL Action Form (Cukup arahkan ke save, biarkan controller menangani ID)
$url = $is_edit
    ? base_url('app/masterdata/jenjang/save/' . $id)
    : base_url('app/masterdata/jenjang/save');

$validation = \Config\Services::validation();
?>

<div class="container-fluid mb-10 px-4 font-jakarta">
    <!-- Top Header & Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="<?= base_url('app/masterdata/jenjang') ?>" 
               class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm group">
                <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight"><?= $is_edit ? 'Edit' : 'Tambah' ?> Jenjang</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-none">Manajemen Struktur Pendidikan</p>
            </div>
        </div>
        
        <!-- Tampilkan Error Global -->
        <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-3 rounded-r-xl flex items-center gap-3 animate-pulse">
            <i class="fas fa-exclamation-circle text-rose-500 text-xs"></i>
            <div>
                <span class="text-[10px] font-black text-rose-700 uppercase tracking-tight block">Terjadi Kesalahan</span>
                <span class="text-xs text-rose-600"><?= session()->getFlashdata('error') ?></span>
                <?php if(session()->getFlashdata('errors')): ?>
                    <ul class="list-disc pl-4 mt-1 text-xs text-rose-600">
                        <?php foreach(session()->getFlashdata('errors') as $e): ?>
                            <li><?= esc($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Card Form -->
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            
            <div class="bg-slate-50/50 px-8 py-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-1.5 bg-indigo-600 rounded-full"></div>
                    <h6 class="text-xs font-black text-slate-700 uppercase tracking-[0.15em]">
                        <?= $is_edit ? 'Modifikasi' : 'Registrasi' ?> Data Unit
                    </h6>
                </div>
                <?php if($is_edit): ?>
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-[9px] font-black uppercase tracking-widest">
                        ID: #<?= esc($id) ?>
                    </span>
                <?php endif; ?>
            </div>

            <form action="<?= $url ?>" method="post" class="p-8">
                <?= csrf_field() ?>
                
                <?php if ($is_edit) : ?>
                    <!-- Input ID Hidden -->
                    <input type="hidden" name="id" value="<?= esc($id) ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    
                    <!-- Nama Jenjang -->
                    <div class="md:col-span-12">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Nama Jenjang Pendidikan <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="relative group">
                            <input type="text" name="nama_jenjang" required autofocus
                                   value="<?= old('nama_jenjang', $data['nama_jenjang'] ?? '') ?>"
                                   placeholder="Contoh: Sekolah Menengah Atas"
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50 border <?= ($validation->hasError('nama_jenjang')) ? 'border-rose-400 ring-2 ring-rose-50' : 'border-slate-200' ?> rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 focus:bg-white transition-all outline-none">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-university text-slate-300 text-xs group-focus-within:text-indigo-500 transition-colors"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Kode Jenjang -->
                    <div class="md:col-span-6">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Kode Unit <span class="text-slate-300 font-normal italic">(Shortcode)</span>
                        </label>
                        <div class="relative group">
                            <input type="text" name="kode_jenjang" maxlength="10"
                                   value="<?= old('kode_jenjang', $data['kode_jenjang'] ?? '') ?>"
                                   placeholder="Contoh: SMA"
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50 border <?= ($validation->hasError('kode_jenjang')) ? 'border-rose-400' : 'border-slate-200' ?> rounded-xl text-xs font-black text-slate-700 uppercase tracking-widest focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 focus:bg-white transition-all outline-none">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-tag text-slate-300 text-xs group-focus-within:text-indigo-500 transition-colors"></i>
                            </div>
                        </div>
                        <?php if($validation->hasError('kode_jenjang')): ?>
                            <p class="mt-1 text-[10px] text-rose-500 font-bold"><?= $validation->getError('kode_jenjang') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Urutan Tampil -->
                    <div class="md:col-span-6">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">
                            Urutan Tampilan
                        </label>
                        <div class="relative group">
                            <input type="number" name="urutan" min="1"
                                   value="<?= old('urutan', $data['urutan'] ?? '1') ?>"
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 focus:bg-white transition-all outline-none">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-sort-numeric-down text-slate-300 text-xs group-focus-within:text-indigo-500 transition-colors"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div class="md:col-span-12">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Deskripsi Tambahan</label>
                        <textarea name="keterangan" rows="3" placeholder="Informasi tambahan mengenai unit pendidikan ini..."
                                  class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium text-slate-700 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-400 focus:bg-white transition-all resize-none outline-none"><?= old('keterangan', $data['keterangan'] ?? '') ?></textarea>
                    </div>

                    <!-- Status Selection -->
                    <div class="md:col-span-12">
                        <div class="bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h4 class="text-[11px] font-black text-indigo-900 uppercase tracking-tight leading-none mb-1">Status Visibilitas</h4>
                                <p class="text-[9px] text-indigo-500 font-bold uppercase tracking-tighter">Mengontrol apakah unit muncul dalam pilihan sistem.</p>
                            </div>
                            <div class="flex bg-white p-1 rounded-xl border border-indigo-100 shadow-sm shrink-0">
                                <?php $curr_stat = old('status', $data['status'] ?? 'aktif'); ?>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="aktif" class="peer hidden" <?= ($curr_stat == 'aktif') ? 'checked' : '' ?>>
                                    <span class="inline-block px-6 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-emerald-500/20">AKTIF</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="nonaktif" class="peer hidden" <?= ($curr_stat == 'nonaktif') ? 'checked' : '' ?>>
                                    <span class="inline-block px-6 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 peer-checked:bg-rose-500 peer-checked:text-white peer-checked:shadow-lg peer-checked:shadow-rose-500/20">OFF</span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Action -->
                <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <button type="reset" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                        Reset Form
                    </button>
                    <div class="flex items-center gap-3">
                        <a href="<?= base_url('app/masterdata/jenjang') ?>" 
                           class="px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-100 transition-all no-underline">
                            Batal
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center gap-2 px-10 py-3 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-700 shadow-xl shadow-indigo-600/20 active:scale-95 transition-all">
                            <i class="fas fa-save"></i>
                            <?= $is_edit ? 'Simpan Perubahan' : 'Finalisasi Data' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Helpful Tips -->
        <div class="mt-6 flex items-start gap-3 px-4 py-3 bg-amber-50 rounded-xl border border-amber-100/50">
            <i class="fas fa-lightbulb text-amber-500 mt-1"></i>
            <p class="text-[10px] font-bold text-amber-700 leading-relaxed uppercase tracking-tight">
                <span class="font-black">Tips:</span> Pastikan kode jenjang unik agar tidak terjadi konflik data saat import/export.
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>