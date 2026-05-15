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
                    <li class="inline-flex items-center uppercase italic">Keuangan</li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <a href="<?= base_url('app/keuangan/tagihan') ?>" class="uppercase hover:text-indigo-600 transition-colors">Tagihan</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase italic underline decoration-2 font-black italic"><?= (isset($tagihan) && $tagihan) ? 'Edit Data' : 'Entry Baru' ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <a href="<?= base_url('app/keuangan/tagihan') ?>" 
           class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
            <i class="fas fa-arrow-left mr-2 text-slate-400"></i> Kembali
        </a>
    </div>

    <!-- Alert Messages (Validation Errors) -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-600 p-4 shadow-sm">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-circle text-rose-600 mr-2"></i>
                <h3 class="text-sm font-black text-rose-800 uppercase tracking-tight italic">Kesalahan Validasi Data</h3>
            </div>
            <div class="text-[11px] font-bold text-rose-700 space-y-1 ml-6 uppercase tracking-tight italic">
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
                <i class="fas fa-file-invoice-dollar text-indigo-400 mr-3"></i>
                <h2 class="text-xs font-black text-white uppercase tracking-widest italic">
                    <?= (isset($tagihan) && $tagihan) ? 'Form Edit Tagihan' : 'Form Tagihan Manual' ?>
                </h2>
            </div>
            <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest italic leading-none">Keuangan v4.0</span>
        </div>

        <div class="p-6 sm:p-10">
            <?php 
                $is_edit = (isset($tagihan) && $tagihan);
                $url = base_url('app/keuangan/tagihan/save');
            ?>
            <form action="<?= $url ?>" method="post" class="space-y-8">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $is_edit ? $tagihan['id'] : '' ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <!-- SCOPE UNIT INFO (Read Only) -->
                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Unit / Jenjang Pengelola
                        </label>
                        <div class="flex items-center p-3 bg-slate-100 border-2 border-slate-200 border-l-4 border-l-indigo-500">
                            <i class="fas fa-shield-alt text-indigo-500 mr-4"></i>
                            <div>
                                <p class="text-sm font-black text-slate-800 uppercase italic leading-none">
                                    <?= !empty(session('unit_kerja')) ? esc(session('unit_kerja')) : 'GLOBAL' ?>
                                </p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1 italic leading-none">
                                    Tagihan akan tercatat untuk unit ini
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- PILIH SISWA -->
                    <div class="md:col-span-2">
                        <label for="id_siswa" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Siswa Penerima Tagihan <span class="text-rose-600">*</span>
                        </label>
                        <?php if ($is_edit): ?>
                            <!-- Jika Edit, Siswa Readonly -->
                            <div class="block w-full px-4 py-3 bg-slate-100 border-2 border-slate-200 text-sm font-black text-slate-500 tracking-tight uppercase italic rounded-none">
                                <?= esc($tagihan['nis']) ?> - <?= esc($tagihan['nama_lengkap']) ?>
                            </div>
                            <input type="hidden" name="id_siswa" value="<?= $tagihan['id_siswa'] ?>">
                        <?php else: ?>
                            <!-- Jika Baru, Pilih Siswa -->
                            <select name="id_siswa" id="id_siswa" required class="form-select w-full bg-white border-2 border-slate-200 px-4 py-3 text-sm font-bold focus:border-indigo-600 focus:ring-0 outline-none rounded-none uppercase select2">
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach($siswa_list as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= (old('id_siswa') == $s['id']) ? 'selected' : '' ?>>
                                        <?= esc($s['nis']) ?> - <?= esc($s['nama_lengkap']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- JENIS PEMBAYARAN -->
                    <div class="md:col-span-1">
                        <label for="id_jenis_pembayaran" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Kategori Pembayaran <span class="text-rose-600">*</span>
                        </label>
                        <select name="id_jenis_pembayaran" id="id_jenis_pembayaran" required 
                                onchange="updateNominal(this)"
                                class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black uppercase tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none italic">
                            <option value="" data-nominal="0" data-nama="">-- Pilih Kategori --</option>
                            <?php foreach($jenis_pembayaran as $jp): ?>
                                <option value="<?= $jp['id'] ?>" 
                                        data-nominal="<?= $jp['nominal'] ?>" 
                                        data-nama="<?= $jp['nama_pembayaran'] ?>"
                                        <?= (old('id_jenis_pembayaran', $tagihan['id_jenis_pembayaran'] ?? '') == $jp['id']) ? 'selected' : '' ?>>
                                    <?= esc($jp['nama_pembayaran']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- NOMINAL -->
                    <div class="md:col-span-1">
                        <label for="jumlah" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Nominal Tagihan (Rp) <span class="text-rose-600">*</span>
                        </label>
                        <div class="flex gap-0 ring-2 ring-slate-100">
                            <div class="flex items-center px-4 bg-slate-100 border-y-2 border-l-2 border-slate-200 text-[10px] font-black text-slate-500 uppercase italic">
                                Rp
                            </div>
                            <input type="number" name="jumlah" id="jumlah" min="0" required
                                   class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black tracking-tighter focus:ring-0 focus:border-indigo-600 transition-all rounded-none"
                                   value="<?= old('jumlah', $tagihan['jumlah'] ?? '0') ?>">
                        </div>
                    </div>

                    <!-- DESKRIPSI -->
                    <div class="md:col-span-2">
                        <label for="deskripsi" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Deskripsi Tagihan <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" name="deskripsi" id="deskripsi" required
                               placeholder="Contoh: SPP BULAN JANUARI 2024"
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none uppercase italic"
                               value="<?= old('deskripsi', $tagihan['deskripsi'] ?? '') ?>">
                        <p class="mt-2 text-[9px] font-bold text-slate-400 uppercase italic tracking-tighter">Deskripsi akan muncul di kwitansi pembayaran siswa.</p>
                    </div>

                    <!-- JATUH TEMPO -->
                    <div class="md:col-span-1">
                        <label for="tanggal_jatuh_tempo" class="block text-[11px] font-black text-slate-600 uppercase tracking-widest mb-2 italic">
                            Tanggal Jatuh Tempo <span class="text-rose-600">*</span>
                        </label>
                        <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" required
                               class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm font-black tracking-tight focus:ring-0 focus:border-indigo-600 transition-all rounded-none uppercase italic"
                               value="<?= old('tanggal_jatuh_tempo', $tagihan['tanggal_jatuh_tempo'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                    </div>

                </div>

                <!-- Action Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-10 border-t-2 border-slate-100 mt-10">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-tight">
                        <span class="text-rose-600">*</span> Pastikan data benar sebelum disimpan.
                    </p>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="<?= base_url('app/keuangan/tagihan') ?>" 
                           class="flex-1 sm:flex-none text-center px-6 py-3 bg-white border-2 border-slate-200 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
                            Batalkan
                        </a>
                        <button type="submit" 
                                class="flex-1 sm:flex-none px-10 py-3 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all border-b-4 border-indigo-900">
                            <i class="fas fa-save mr-2 text-[10px]"></i> <?= $is_edit ? 'Update Data' : 'Simpan Tagihan' ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Inisialisasi Select2 jika tersedia (untuk pencarian siswa)
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%',
                placeholder: '-- PILIH SISWA --',
                allowClear: true,
                theme: 'classic' // Tema simple agar mudah ditimpa CSS
            });
            
            // Hack kecil untuk menyesuaikan style Select2 dengan Tailwind Brutalist
            $('.select2-selection').css({
                'height': '46px',
                'border': '2px solid #e2e8f0',
                'border-radius': '0',
                'display': 'flex',
                'align-items': 'center',
                'background-color': '#fff'
            });
            $('.select2-selection__rendered').css({
                'font-weight': '900',
                'text-transform': 'uppercase',
                'font-size': '0.875rem',
                'color': '#1e293b',
                'padding-left': '1rem'
            });
            $('.select2-selection__arrow').css({'height': '44px'});
        }
    });

    // Auto-fill Nominal & Deskripsi saat memilih Kategori
    function updateNominal(selectElement) {
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var nominal = selectedOption.getAttribute('data-nominal');
        var nama = selectedOption.getAttribute('data-nama');
        
        var inputJumlah = document.getElementById('jumlah');
        var inputDeskripsi = document.getElementById('deskripsi');

        // Hanya isi otomatis jika input masih kosong atau user memilih kategori baru
        if (nominal && nominal !== "0") {
            inputJumlah.value = nominal;
        }

        if (nama && inputDeskripsi.value === "") {
            // Dapatkan nama bulan saat ini
            const date = new Date();
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            
            inputDeskripsi.value = nama + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
        }
    }
</script>
<?= $this->endSection() ?>