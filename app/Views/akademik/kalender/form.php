<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-4xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase italic">
                <?= esc($title) ?>
            </h1>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">
                Manajemen Detail Agenda Akademik
            </p>
        </div>
        <a href="<?= base_url('app/akademik/kalender') ?>" 
           class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
            <i class="fas fa-arrow-left mr-2 text-slate-400"></i> Kembali
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-600 p-4 shadow-sm">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle text-rose-600 mr-2"></i>
                <h3 class="text-sm font-black text-rose-800 uppercase tracking-tight">Terjadi Kesalahan Validasi</h3>
            </div>
            <div class="text-xs font-bold text-rose-700 space-y-1 ml-6">
                <?php if (is_array(session()->getFlashdata('errors'))): ?>
                    <ul class="list-disc pl-2">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <?= session()->getFlashdata('errors') ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Form Card -->
    <div class="bg-white shadow-2xl border-2 border-slate-200 overflow-hidden">
        <div class="bg-slate-50 border-b-2 border-slate-200 px-6 py-4 flex items-center">
            <i class="fas fa-edit text-slate-400 mr-2"></i>
            <h2 class="text-xs font-black text-slate-700 uppercase tracking-widest">Input Data Agenda</h2>
        </div>

        <div class="p-6 sm:p-8">
            <?php
            // Pastikan data diperlakukan sebagai array untuk akses yang konsisten
            $kalender_data = (array) ($kalender ?? []);
            $is_edit = !empty($kalender_data['id']);
            $url = $is_edit ? base_url('app/akademik/kalender/update/' . $kalender_data['id']) : base_url('app/akademik/kalender/create');
            $validation = \Config\Services::validation();

            /** * LOGIKA PEMETAAN DATA (ROBUST MAPPING)
             * Mengecek field database utama (title/start/end) dan field form lama (nama_acara/tanggal_mulai)
             */
            
            // 1. Judul Acara
            $raw_title = $kalender_data['title'] ?? $kalender_data['nama_acara'] ?? '';
            $v_title   = old('title', old('nama_acara', $raw_title));

            // 2. Tanggal Mulai
            $raw_start = $kalender_data['start'] ?? $kalender_data['tanggal_mulai'] ?? '';
            $v_start   = old('start', old('tanggal_mulai', !empty($raw_start) ? date('Y-m-d', strtotime($raw_start)) : ''));

            // 3. Tanggal Selesai
            $raw_end   = $kalender_data['end'] ?? $kalender_data['tanggal_selesai'] ?? '';
            $v_end     = old('end', old('tanggal_selesai', !empty($raw_end) ? date('Y-m-d', strtotime($raw_end)) : ''));

            // 4. Field Lainnya
            $v_ket     = old('keterangan', $kalender_data['keterangan'] ?? '');
            $v_color   = old('color', $kalender_data['color'] ?? '#4e73df');
            $v_jenjang = old('kode_jenjang', $kalender_data['kode_jenjang'] ?? '');
            $ta_id     = $is_edit ? ($kalender_data['tahun_ajaran_id'] ?? '') : ($tahun_ajaran_aktif['id'] ?? '');
            ?>

            <form action="<?= $url ?>" method="post" class="space-y-6">
                <?= csrf_field() ?>
                <?php if ($is_edit) : ?>
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>

                <!-- ID Tahun Ajaran -->
                <input type="hidden" name="tahun_ajaran_id" value="<?= esc($ta_id) ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- KODE JENJANG -->
                    <div class="md:col-span-2">
                        <label for="kode_jenjang" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Unit / Jenjang Kerja <span class="text-rose-600">*</span>
                        </label>
                        <?php if (session('unit_kerja')): ?>
                            <div class="flex items-center p-3 bg-slate-50 border-2 border-slate-200">
                                <i class="fas fa-lock text-slate-400 mr-3"></i>
                                <div>
                                    <p class="text-sm font-black text-slate-800 uppercase"><?= esc(strtoupper(session('unit_kerja'))) ?></p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Unit Terkunci Sesuai Session</p>
                                </div>
                            </div>
                            <input type="hidden" name="kode_jenjang" value="<?= esc(session('unit_kerja')) ?>">
                        <?php else: ?>
                            <select name="kode_jenjang" id="kode_jenjang" required
                                    class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-bold uppercase tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none <?= $validation->hasError('kode_jenjang') ? 'border-rose-500' : '' ?>">
                                <option value="" disabled <?= empty($v_jenjang) ? 'selected' : '' ?>>-- Pilih Unit / Jenjang --</option>
                                <?php if (!empty($list_jenjang)): ?>
                                    <?php foreach($list_jenjang as $jenj): ?>
                                        <option value="<?= esc($jenj['kode_jenjang']) ?>" <?= (string)$v_jenjang === (string)$jenj['kode_jenjang'] ? 'selected' : '' ?>>
                                            <?= esc(strtoupper($jenj['nama_jenjang'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- NAMA ACARA (PENTING: name="title") -->
                    <div class="md:col-span-2">
                        <label for="title" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Nama Acara / Kegiatan <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" name="title" id="title" required
                               placeholder="Masukkan nama acara..."
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-bold tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none uppercase <?= $validation->hasError('title') ? 'border-rose-500' : '' ?>"
                               value="<?= esc($v_title) ?>">
                        <?php if ($validation->hasError('title')): ?>
                            <p class="mt-1 text-[10px] font-bold text-rose-600 uppercase"><?= $validation->getError('title') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- KETERANGAN -->
                    <div class="md:col-span-2">
                        <label for="keterangan" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Keterangan / Deskripsi Acara
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="3"
                                  placeholder="Tuliskan detail agenda di sini..."
                                  class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-medium tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none"><?= esc($v_ket) ?></textarea>
                    </div>

                    <!-- TANGGAL MULAI (PENTING: name="start") -->
                    <div>
                        <label for="start" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Tanggal Mulai <span class="text-rose-600">*</span>
                        </label>
                        <input type="date" name="start" id="start" required
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black focus:ring-0 focus:border-indigo-600 transition-all rounded-none <?= $validation->hasError('start') ? 'border-rose-500' : '' ?>"
                               value="<?= esc($v_start) ?>">
                        <?php if ($validation->hasError('start')): ?>
                            <p class="mt-1 text-[10px] font-bold text-rose-600 uppercase"><?= $validation->getError('start') ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- TANGGAL SELESAI (PENTING: name="end") -->
                    <div>
                        <label for="end" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Tanggal Selesai
                        </label>
                        <input type="date" name="end" id="end"
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black focus:ring-0 focus:border-indigo-600 transition-all rounded-none <?= $validation->hasError('end') ? 'border-rose-500' : '' ?>"
                               value="<?= esc($v_end) ?>">
                        <p class="mt-1 text-[9px] font-bold text-slate-400 uppercase italic leading-tight">
                            Kosongkan jika acara satu hari.
                        </p>
                    </div>

                    <!-- WARNA -->
                    <div class="md:col-span-2">
                        <label for="color" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2">
                            Warna Label Agenda (HEX)
                        </label>
                        <div class="flex gap-0 shadow-sm ring-2 ring-slate-100 ring-offset-2">
                            <input type="color" id="color_picker" 
                                   class="h-12 w-20 cursor-pointer border-none p-0 bg-transparent"
                                   value="<?= esc($v_color) ?>">
                            <input type="text" id="color" name="color"
                                   class="flex-1 px-4 py-3 bg-slate-50 border-y-2 border-r-2 border-slate-200 text-sm font-black text-slate-500 uppercase tracking-widest focus:ring-0 focus:border-indigo-600"
                                   value="<?= esc($v_color) ?>" maxlength="7">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3 pt-8 border-t-2 border-slate-100">
                    <a href="<?= base_url('app/akademik/kalender') ?>" 
                       class="px-6 py-3 text-[11px] font-black uppercase tracking-widest text-slate-500 hover:text-slate-800 transition-colors">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-widest shadow-lg hover:bg-indigo-700 active:scale-95 transition-all">
                        <i class="fas fa-save mr-2"></i> <?= $is_edit ? 'Perbarui Agenda' : 'Simpan Agenda' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colorPicker = document.getElementById('color_picker');
        const colorInput = document.getElementById('color');

        if(colorPicker && colorInput) {
            colorPicker.addEventListener('input', function() {
                colorInput.value = this.value.toUpperCase();
            });
            colorInput.addEventListener('input', function() {
                if(/^#[0-9A-F]{6}$/i.test(this.value)) {
                    colorPicker.value = this.value;
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>