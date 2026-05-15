<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-4xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase italic">
                <?= esc($title) ?>
            </h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li class="inline-flex items-center uppercase italic">Sapras</li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase">Fasilitas</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase italic underline decoration-2 font-black italic"><?= (isset($ruangan) && $ruangan) ? 'Perbarui' : 'Registrasi' ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('app/sapras/ruangan') ?>" 
           class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
            <i class="fas fa-arrow-left mr-2 text-slate-400"></i> Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-600 p-4 shadow-sm">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle text-rose-600 mr-2"></i>
                <h3 class="text-sm font-black text-rose-800 uppercase tracking-tight italic">Kesalahan Validasi</h3>
            </div>
            <div class="text-[11px] font-bold text-rose-700 space-y-1 ml-6 uppercase tracking-tight">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <p>• <?= $error ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Form Card -->
    <div class="bg-white shadow-2xl border-2 border-slate-200 overflow-hidden rounded-none">
        <div class="bg-slate-900 border-b-2 border-slate-800 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-door-open text-indigo-400 mr-3"></i>
                <h2 class="text-xs font-black text-white uppercase tracking-widest italic">
                    <?= (isset($ruangan) && $ruangan) ? 'Ubah Informasi Ruangan' : 'Registrasi Ruangan Baru' ?>
                </h2>
            </div>
            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Aset Ruangan v4.0</span>
        </div>

        <div class="p-6 sm:p-10">
            <?php 
                $is_edit = (isset($ruangan) && $ruangan);
                $url = $is_edit ? base_url('app/sapras/ruangan/update/' . $ruangan->id) : base_url('app/sapras/ruangan/save');
                $validation = \Config\Services::validation();
            ?>
            <form action="<?= $url ?>" method="post" class="space-y-8">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $is_edit ? $ruangan->id : '' ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- KODE JENJANG (Scope Unit) -->
                    <div class="md:col-span-2">
                        <label for="kode_jenjang" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Unit Pengelola <span class="text-rose-600">*</span>
                        </label>
                        <?php if (session('unit_kerja')): ?>
                            <div class="flex items-center p-4 bg-slate-50 border-2 border-slate-200 border-l-4 border-l-indigo-500">
                                <i class="fas fa-shield-alt text-indigo-500 mr-4"></i>
                                <div>
                                    <p class="text-sm font-black text-slate-800 uppercase italic leading-none"><?= esc(strtoupper(session('unit_kerja'))) ?></p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic">Unit Terkunci Sesuai Otoritas Login</p>
                                </div>
                            </div>
                            <input type="hidden" name="kode_jenjang" value="<?= esc(session('unit_kerja')) ?>">
                        <?php else: ?>
                            <select name="kode_jenjang" id="kode_jenjang" required
                                    class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black uppercase tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none <?= $validation->hasError('kode_jenjang') ? 'border-rose-500' : '' ?>">
                                <option value="" disabled <?= !isset($ruangan->kode_jenjang) ? 'selected' : '' ?>>-- Pilih Unit Kerja --</option>
                                <option value="SD" <?= (old('kode_jenjang', $ruangan->kode_jenjang ?? '') == 'SD') ? 'selected' : '' ?>>SD PRATAMA</option>
                                <option value="SMP" <?= (old('kode_jenjang', $ruangan->kode_jenjang ?? '') == 'SMP') ? 'selected' : '' ?>>SMP PRATAMA</option>
                                <option value="SMA" <?= (old('kode_jenjang', $ruangan->kode_jenjang ?? '') == 'SMA') ? 'selected' : '' ?>>SMA PRATAMA</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- NAMA RUANGAN -->
                    <div class="md:col-span-2">
                        <label for="nama" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Identitas / Nama Ruangan <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" name="nama" id="nama" required
                               placeholder="Contoh: RUANG KELAS X-A / LAB KOMPUTER"
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none uppercase italic <?= $validation->hasError('nama') ? 'border-rose-500' : '' ?>"
                               value="<?= old('nama', $ruangan->nama ?? '') ?>">
                        <p class="mt-2 text-[9px] font-bold text-slate-400 uppercase italic tracking-tighter italic">Nama unik membantu dalam manajemen jadwal dan absensi.</p>
                    </div>

                    <!-- LOKASI GEDUNG -->
                    <div>
                        <label for="id_gedung" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Lokasi Gedung / Bangunan <span class="text-rose-600">*</span>
                        </label>
                        <select name="id_gedung" id="id_gedung" required
                                class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black uppercase tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none <?= $validation->hasError('id_gedung') ? 'border-rose-500' : '' ?>">
                            <option value="" disabled <?= !isset($ruangan->id_gedung) ? 'selected' : '' ?>>-- Pilih Lokasi --</option>
                            <?php foreach($gedung as $g): ?>
                                <option value="<?= $g->id ?>" <?= (old('id_gedung', $ruangan->id_gedung ?? '') == $g->id) ? 'selected' : '' ?>>
                                    <?= esc($g->nama) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- KAPASITAS -->
                    <div>
                        <label for="kapasitas" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Kapasitas Maksimal (Orang)
                        </label>
                        <div class="flex gap-0 ring-2 ring-slate-100">
                            <input type="number" name="kapasitas" id="kapasitas" min="0"
                                   placeholder="0"
                                   class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black tracking-tighter focus:ring-0 focus:border-indigo-600 transition-all rounded-none italic"
                                   value="<?= old('kapasitas', $ruangan->kapasitas ?? '0') ?>">
                            <div class="flex items-center px-4 bg-slate-100 border-y-2 border-r-2 border-slate-200 text-[10px] font-black text-slate-400 uppercase italic">
                                Siswa
                            </div>
                        </div>
                    </div>

                    <!-- KETERANGAN -->
                    <div class="md:col-span-2">
                        <label for="keterangan" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Keterangan Fasilitas / Catatan
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="4"
                                  placeholder="Tuliskan detail fasilitas ruangan (Contoh: AC, Proyektor, Papan Tulis)..."
                                  class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-medium tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none italic"><?= old('keterangan', $ruangan->keterangan ?? '') ?></textarea>
                    </div>

                </div>

                <!-- Action Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-10 border-t-2 border-slate-100 mt-10">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                        <span class="text-rose-600">*</span> Field wajib diisi untuk integritas data sapras.
                    </p>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="<?= base_url('app/sapras/ruangan') ?>" 
                           class="flex-1 sm:flex-none text-center px-6 py-3 bg-white border-2 border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
                            Batal
                        </a>
                        <button type="submit" 
                                class="flex-1 sm:flex-none px-10 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all border-b-4 border-indigo-900">
                            <i class="fas fa-save mr-2 text-[10px]"></i> <?= $is_edit ? 'Simpan Perubahan' : 'Registrasi Ruangan' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .font-black { font-weight: 900; }
</style>

<?= $this->endSection() ?>