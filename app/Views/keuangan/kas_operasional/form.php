<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$isEdit = !empty($transaksi);
$jenis = $isEdit ? $transaksi['jenis_transaksi'] : ($jenis_default ?? 'keluar');

// Setup Aksesibilitas Warna & Judul
if ($jenis === 'masuk') {
    $colorTheme = 'emerald';
    $iconTheme = 'fa-arrow-down';
    $titlePrefix = 'Penerimaan';
    $kategoriList = $kategori_masuk ?? [];
} else {
    $colorTheme = 'rose';
    $iconTheme = 'fa-arrow-up';
    $titlePrefix = 'Pengeluaran';
    $kategoriList = $kategori_keluar ?? [];
}

$titleText = $isEdit ? 'Ubah Data ' . $titlePrefix : 'Catat Kas ' . ucfirst($jenis);
$nominalFormatted = $isEdit ? number_format($transaksi['nominal'], 0, ',', '.') : '';
?>

<div class="px-4 sm:px-6 py-6 max-w-4xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic flex items-center gap-3">
                <i class="fas <?= $iconTheme ?> text-<?= $colorTheme ?>-500"></i> <?= esc($titleText) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Isi form di bawah ini. Sistem secara otomatis akan mencatatnya sebagai Jurnal Akuntansi.
            </p>
        </div>
        <a href="<?= base_url('app/keuangan/kas-operasional') ?>" 
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 sm:py-2.5 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- ERROR VALIDATION ALERT -->
    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-rose-500 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-widest mb-1">Penyimpanan Gagal</h3>
                    <ul class="list-disc list-inside text-xs text-rose-700 dark:text-rose-400 space-y-1">
                        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif ?>

    <!-- FORM CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden relative">
        <div class="h-2 w-full bg-gradient-to-r from-<?= $colorTheme ?>-400 to-<?= $colorTheme ?>-600 absolute top-0 left-0"></div>

        <form action="<?= base_url('app/keuangan/kas-operasional/store') ?>" method="post" class="p-6 md:p-10 mt-2 space-y-8">
            <?= csrf_field() ?>
            
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= esc($transaksi['id']) ?>">
            <?php endif; ?>
            <input type="hidden" name="jenis_transaksi" value="<?= esc($jenis) ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- KOLOM KIRI -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Tanggal Transaksi <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal" required 
                               class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-<?= $colorTheme ?>-500 outline-none transition-all text-sm font-bold text-slate-900 dark:text-white shadow-sm"
                               value="<?= old('tanggal', $transaksi['tanggal'] ?? date('Y-m-d')) ?>">
                    </div>

                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Unit Pengelola <span class="text-rose-500">*</span>
                        </label>
                        <?php if ($isSuperAdmin): ?>
                            <div class="relative">
                                <select name="kode_jenjang" required onchange="ubahUnitForm(this.value)"
                                        class="w-full pl-4 pr-10 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-<?= $colorTheme ?>-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none uppercase shadow-sm">
                                    <option value="GLOBAL" <?= old('kode_jenjang', $transaksi['kode_jenjang'] ?? $filter_jenjang) == 'GLOBAL' ? 'selected' : '' ?>>YAYASAN / PUSAT</option>
                                    <?php foreach ($jenjang_list as $j): ?>
                                        <option value="<?= $j['kode_jenjang'] ?>" <?= old('kode_jenjang', $transaksi['kode_jenjang'] ?? $filter_jenjang) == $j['kode_jenjang'] ? 'selected' : '' ?>>
                                            UNIT <?= strtoupper($j['kode_jenjang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="kode_jenjang" value="<?= esc($filter_jenjang) ?>">
                            <div class="w-full px-4 py-3.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center gap-2 cursor-not-allowed">
                                <i class="fas fa-lock text-slate-400"></i> Unit <?= esc($filter_jenjang) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Kategori (Buku Besar) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="id_kategori" required
                                    class="w-full pl-4 pr-10 py-3.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-<?= $colorTheme ?>-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none shadow-sm">
                                <option value="" disabled <?= empty($transaksi['id_kategori']) ? 'selected' : '' ?>>-- Pilih Kategori Akun --</option>
                                <?php foreach ($kategoriList as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= old('id_kategori', $transaksi['id_kategori'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                        <?= esc($k['nama_akun']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Nomor Bukti / Referensi
                        </label>
                        <input type="text" name="referensi"
                               class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-<?= $colorTheme ?>-500 outline-none transition-all text-sm font-bold text-slate-900 dark:text-white shadow-sm"
                               placeholder="Contoh: INV/001 atau Kosongkan"
                               value="<?= old('referensi', $transaksi['referensi'] ?? '') ?>">
                    </div>

                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Keterangan / Deskripsi Transaksi <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="keterangan" rows="3" required
                                  class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-<?= $colorTheme ?>-500 outline-none transition-all text-sm font-medium text-slate-700 dark:text-slate-300 resize-none shadow-sm"
                                  placeholder="Jelaskan secara singkat untuk keperluan apa uang ini digunakan..."><?= old('keterangan', $transaksi['keterangan'] ?? '') ?></textarea>
                    </div>

                    <div class="p-5 bg-<?= $colorTheme ?>-50 dark:bg-<?= $colorTheme ?>-900/10 border-2 border-<?= $colorTheme ?>-200 dark:border-<?= $colorTheme ?>-800 rounded-2xl">
                        <label class="block text-[10px] md:text-xs font-black text-<?= $colorTheme ?>-700 dark:text-<?= $colorTheme ?>-400 uppercase tracking-widest mb-2">
                            Nominal (Rp) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nominal" id="rupiahInput" required
                               class="w-full px-4 py-4 bg-white dark:bg-slate-950 border-none rounded-xl focus:ring-2 focus:ring-<?= $colorTheme ?>-500 outline-none transition-all text-2xl font-black text-slate-900 dark:text-white shadow-inner text-right tracking-tighter"
                               placeholder="0"
                               value="<?= old('nominal', $nominalFormatted) ?>">
                    </div>
                </div>

            </div>

            <!-- BUTTONS -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="<?= base_url('app/keuangan/kas-operasional') ?>" class="w-full sm:w-auto px-6 py-4 sm:py-3.5 text-center bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest shadow-sm">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto px-8 py-4 sm:py-3.5 justify-center bg-<?= $colorTheme ?>-600 hover:bg-<?= $colorTheme ?>-700 text-white text-xs font-black rounded-xl shadow-lg shadow-<?= $colorTheme ?>-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 uppercase tracking-widest border-b-4 border-<?= $colorTheme ?>-800">
                    <i class="fas fa-save"></i> Simpan Transaksi
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
    // AUTO FORMAT RUPIAH REAL-TIME
    const rupiahInput = document.getElementById('rupiahInput');
    if (rupiahInput) {
        rupiahInput.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
        });
    }

    function formatRupiah(angka) {
        let number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Refresh Form saat ganti Unit (Agar Pilihan COA menyesuaikan unit bersangkutan)
    function ubahUnitForm(val) {
        if(val !== '') {
            let url = new URL(window.location.href);
            url.searchParams.set('jenjang', val);
            window.location.href = url.toString();
        }
    }
</script>

<?= $this->endSection() ?>