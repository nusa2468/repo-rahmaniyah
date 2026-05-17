<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
$isGlobalUser = in_array($sessionJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL']);

// Helper formatting Harga
$hargaFormatted = '';
if ($barang && !empty($barang->harga_perolehan)) {
    $hargaFormatted = number_format($barang->harga_perolehan, 0, ',', '.');
}
?>

<div class="px-4 sm:px-6 py-6 max-w-5xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Lengkapi metadata dan spesifikasi fisik barang inventaris.
            </p>
        </div>
        <a href="<?= base_url('app/sapras/barang') ?>" 
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
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 to-emerald-500"></div>

        <form action="<?= base_url('app/sapras/barang/save') ?>" method="post" class="p-5 md:p-8 space-y-8">
            <?= csrf_field() ?>
            
            <?php if ($barang): ?>
                <input type="hidden" name="id" value="<?= esc($barang->id) ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- KOLOM KIRI: LOKASI & KLASIFIKASI -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <i class="fas fa-map-marker-alt w-5"></i> Penempatan & Klasifikasi
                    </h3>

                    <!-- Unit Pemilik -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Unit Kepemilikan <span class="text-rose-500">*</span>
                        </label>
                        <?php if ($isGlobalUser): ?>
                            <div class="relative">
                                <select name="kode_jenjang" id="kode_jenjang" required onchange="ubahUnitForm(this.value)"
                                        class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none uppercase">
                                    <?php foreach ($daftarUnit as $kode => $nama): ?>
                                        <option value="<?= $kode ?>" <?= old('kode_jenjang', $barang->kode_jenjang ?? $filterJenjang) == $kode ? 'selected' : '' ?>>
                                            UNIT <?= strtoupper($kode) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                            <p class="text-[9px] text-slate-500 mt-1.5 italic">Mengubah unit akan me-refresh daftar lokasi dan pegawai di bawah.</p>
                        <?php else: ?>
                            <input type="hidden" name="kode_jenjang" value="<?= esc($sessionJenjang) ?>">
                            <div class="w-full px-4 py-3.5 md:py-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center gap-2 cursor-not-allowed">
                                <i class="fas fa-lock text-slate-400"></i> Unit <?= esc($sessionJenjang) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kategori Master -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Kategori Aset <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="id_kategori" required
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none">
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php foreach ($kategoriList as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= old('id_kategori', $barang->id_kategori ?? '') == $k['id'] ? 'selected' : '' ?>>
                                        [<?= esc($k['kode_kategori']) ?>] <?= esc($k['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Lokasi / Ruangan -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Lokasi Penempatan
                        </label>
                        <div class="relative">
                            <select name="id_lokasi"
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none">
                                <option value="">-- Pilih Lokasi (Opsional) --</option>
                                <?php foreach ($lokasiList as $l): ?>
                                    <option value="<?= $l['id'] ?>" <?= old('id_lokasi', $barang->id_lokasi ?? '') == $l['id'] ? 'selected' : '' ?>>
                                        <?= esc($l['nama_lokasi']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Penanggung Jawab -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Penanggung Jawab (Pegawai)
                        </label>
                        <div class="relative">
                            <select name="id_penanggung_jawab"
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none">
                                <option value="">-- Tanpa Penanggung Jawab --</option>
                                <?php foreach ($pegawaiList as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= old('id_penanggung_jawab', $barang->id_penanggung_jawab ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= esc($p['nama_lengkap']) ?> (<?= esc($p['jabatan_fungsional'] ?? 'Guru/Staf') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: IDENTITAS & FISIK -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <i class="fas fa-boxes w-5"></i> Identitas & Fisik Barang
                    </h3>

                    <!-- Kode Aset -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Kode Barcode Aset
                        </label>
                        <input type="text" name="kode_aset"
                               class="w-full px-4 py-3.5 md:py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-mono font-black text-slate-600 dark:text-slate-300 placeholder-slate-400 uppercase"
                               placeholder="<?= $barang ? '' : 'Dikosongkan untuk Auto-Generate' ?>"
                               value="<?= old('kode_aset', $barang->kode_aset ?? '') ?>" <?= $barang ? 'readonly' : '' ?>>
                        <?php if($barang): ?>
                            <p class="text-[9px] text-slate-500 mt-1.5 italic">Kode aset yang sudah terbuat tidak dapat diubah (Unik).</p>
                        <?php endif; ?>
                    </div>

                    <!-- Nama Aset -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Nama Barang / Aset <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_aset" required
                               class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-900 dark:text-white"
                               placeholder="Contoh: Proyektor Epson, Lemari Jati, dll"
                               value="<?= old('nama_aset', $barang->nama_aset ?? '') ?>">
                    </div>

                    <!-- Merek & Spesifikasi -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Merk & Spesifikasi Detail
                        </label>
                        <textarea name="merk_spesifikasi" rows="3"
                                  class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-medium text-slate-700 dark:text-slate-300 resize-none"
                                  placeholder="Tuliskan spesifikasi, warna, no seri, dll..."><?= old('merk_spesifikasi', $barang->merk_spesifikasi ?? '') ?></textarea>
                    </div>

                    <!-- GRID Kondisi & Ketersediaan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Kondisi <span class="text-rose-500">*</span></label>
                            <select name="kondisi" required class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 outline-none appearance-none">
                                <?php foreach(['Baik', 'Rusak Ringan', 'Rusak Berat', 'Afkir/Dihapus'] as $k): ?>
                                    <option value="<?= $k ?>" <?= old('kondisi', $barang->kondisi ?? 'Baik') == $k ? 'selected' : '' ?>><?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Ketersediaan <span class="text-rose-500">*</span></label>
                            <select name="status_ketersediaan" required class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 outline-none appearance-none">
                                <?php foreach(['Tersedia', 'Dipinjam', 'Diperbaiki', 'Hilang'] as $k): ?>
                                    <option value="<?= $k ?>" <?= old('status_ketersediaan', $barang->status_ketersediaan ?? 'Tersedia') == $k ? 'selected' : '' ?>><?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SEPARATOR / FINANCIAL SECTION -->
                <div class="lg:col-span-2 border-t border-slate-100 dark:border-slate-800 pt-6 mt-2">
                    <h3 class="text-xs font-black text-amber-500 dark:text-amber-400 uppercase tracking-widest mb-4">
                        <i class="fas fa-file-invoice-dollar w-5"></i> Nilai Ekonomi & Kepemilikan
                    </h3>
                    
                    <!-- GRID 4 KOLOM DI DESKTOP, 2 DI TABLET, 1 DI HP -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6 bg-slate-50 dark:bg-slate-800/50 p-5 md:p-6 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Kepemilikan</label>
                            <select name="status_kepemilikan" class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-700 dark:text-slate-200 outline-none appearance-none">
                                <?php foreach(['Milik Sendiri', 'Sewa', 'Hibah/Wakaf', 'Pinjam Pakai'] as $k): ?>
                                    <option value="<?= $k ?>" <?= old('status_kepemilikan', $barang->status_kepemilikan ?? 'Milik Sendiri') == $k ? 'selected' : '' ?>><?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Sumber Dana</label>
                            <input type="text" name="sumber_dana" class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-sm font-bold text-slate-900 dark:text-white" placeholder="BOS/Yayasan" value="<?= old('sumber_dana', $barang->sumber_dana ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Tgl Perolehan</label>
                            <input type="date" name="tanggal_perolehan" class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-sm font-bold text-slate-900 dark:text-white" value="<?= old('tanggal_perolehan', $barang->tanggal_perolehan ?? '') ?>">
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nilai Perolehan (Rp)</label>
                            <input type="text" name="harga_perolehan" id="rupiahInput" class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-sm font-bold text-slate-900 dark:text-white" placeholder="Contoh: 1.500.000" value="<?= old('harga_perolehan', $hargaFormatted) ?>">
                        </div>
                    </div>
                </div>

            </div>

            <!-- BUTTONS -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="<?= base_url('app/sapras/barang') ?>" class="w-full sm:w-auto px-6 py-4 sm:py-3 text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto px-8 py-4 sm:py-3 justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 uppercase tracking-widest border-b-4 border-indigo-800">
                    <i class="fas fa-save"></i> Simpan Inventaris
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
    // AUTO FORMAT RUPIAH REAL-TIME (Anti Error Huruf)
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

    // Fungsi Submit Ulang untuk Refresh Relasi (Lokasi & Guru) Sesuai Unit
    function ubahUnitForm(val) {
        if(val !== '') {
            let url = new URL(window.location.href);
            url.searchParams.set('jenjang', val);
            window.location.href = url.toString();
        }
    }
</script>

<?= $this->endSection() ?>