<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
$isGlobalUser = in_array($sessionJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL']);

$isEdit = !empty($pemeliharaan);

// Helper formatting Harga
$biayaFormatted = '';
if ($isEdit && !empty($pemeliharaan->biaya)) {
    $biayaFormatted = number_format($pemeliharaan->biaya, 0, ',', '.');
}

// Tanggal Default (Hari ini)
$defaultMulai = date('Y-m-d');

$valMulai = $pemeliharaan->tanggal_mulai ?? $defaultMulai;
$valSelesai = $pemeliharaan->tanggal_selesai ?? '';
$valStatus = $pemeliharaan->status ?? 'Direncanakan';
?>

<div class="px-4 sm:px-6 py-6 max-w-5xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                <?= $isEdit ? 'Ubah status perbaikan atau rincian biaya servis.' : 'Catat log perawatan rutin atau pelaporan kerusakan aset.' ?>
            </p>
        </div>
        <a href="<?= base_url('app/sapras/pemeliharaan') ?>" 
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
                    <h3 class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-widest mb-1">Gagal Menyimpan</h3>
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
    <!-- Memanfaatkan AlpineJS untuk interaktivitas -->
    <div x-data="{ 
            status: '<?= old('status', $valStatus) ?>'
         }" 
         class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden relative">
        
        <!-- Form Accent Line -->
        <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 to-cyan-500"></div>

        <form action="<?= base_url('app/sapras/pemeliharaan/save') ?>" method="post" class="p-5 md:p-8 space-y-8">
            <?= csrf_field() ?>
            
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= esc($pemeliharaan->id) ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- KOLOM KIRI: ASET & DETAIL SERVIS -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <i class="fas fa-tools w-5"></i> Objek Aset & Pelaksana
                    </h3>

                    <!-- Filter Unit Internal (Hanya Global) -->
                    <?php if ($isGlobalUser && !$isEdit): ?>
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 mb-4">
                            <label class="block text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                                Filter Unit Aset
                            </label>
                            <div class="relative">
                                <select onchange="ubahUnitForm(this.value)"
                                        class="w-full pl-4 pr-10 py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-xs font-bold text-slate-700 dark:text-slate-200 appearance-none uppercase">
                                    <option value="GLOBAL" <?= $filterJenjang == 'GLOBAL' ? 'selected' : '' ?>>SEMUA UNIT (GLOBAL)</option>
                                    <?php foreach ($daftarUnit as $kode => $nama): ?>
                                        <option value="<?= $kode ?>" <?= $filterJenjang == $kode ? 'selected' : '' ?>>UNIT <?= strtoupper($kode) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Barang Aset -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Pilih Aset yang Diservis <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="id_aset" required <?= $isEdit ? 'disabled' : '' ?>
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none <?= $isEdit ? 'bg-slate-50 opacity-70 cursor-not-allowed' : '' ?>">
                                <option value="" disabled selected>-- Cari dan Pilih Aset --</option>
                                <?php foreach ($barangList as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= old('id_aset', $pemeliharaan->id_aset ?? '') == $b['id'] ? 'selected' : '' ?>>
                                        [<?= esc($b['kode_aset']) ?>] <?= esc($b['nama_aset']) ?> (<?= esc($b['kode_jenjang']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        <?php if($isEdit): ?>
                            <input type="hidden" name="id_aset" value="<?= $pemeliharaan->id_aset ?>">
                            <p class="text-[9px] text-slate-500 mt-1 italic">Objek aset tidak dapat diubah pada saat mengedit log.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Jenis Pemeliharaan -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Jenis Pemeliharaan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="jenis_pemeliharaan" required
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none">
                                <option value="Rutin/Preventif" <?= old('jenis_pemeliharaan', $pemeliharaan->jenis_pemeliharaan ?? '') == 'Rutin/Preventif' ? 'selected' : '' ?>>Rutin / Preventif (Pencegahan)</option>
                                <option value="Perbaikan/Kerusakan" <?= old('jenis_pemeliharaan', $pemeliharaan->jenis_pemeliharaan ?? '') == 'Perbaikan/Kerusakan' ? 'selected' : '' ?>>Perbaikan / Kerusakan Insidental</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Pelaksana -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Teknisi / Vendor Pelaksana
                        </label>
                        <input type="text" name="pelaksana" 
                               class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-900 dark:text-white"
                               placeholder="Nama teknisi internal atau perusahaan vendor..."
                               value="<?= old('pelaksana', $pemeliharaan->pelaksana ?? '') ?>">
                    </div>

                </div>

                <!-- KOLOM KANAN: BIAYA, JADWAL & STATUS -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-cyan-600 dark:text-cyan-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <i class="fas fa-clock w-5"></i> Jadwal & Administrasi
                    </h3>

                    <!-- GRID Waktu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" required 
                                   class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-cyan-500" 
                                   value="<?= $valMulai ?>">
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Biaya Servis (Rp)</label>
                            <input type="text" name="biaya" id="rupiahInput" 
                                   class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-sm font-bold text-purple-600 dark:text-purple-400 focus:ring-2 focus:ring-cyan-500" 
                                   placeholder="Contoh: 150.000" 
                                   value="<?= old('biaya', $biayaFormatted) ?>">
                        </div>
                    </div>

                    <!-- Keterangan / Laporan Kerusakan -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Laporan Kerusakan / Tindakan Servis <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="keterangan" rows="3" required
                                  class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-medium text-slate-700 dark:text-slate-300 resize-none"
                                  placeholder="Deskripsikan kerusakan aset atau rincian *sparepart* yang diganti..."><?= old('keterangan', $pemeliharaan->keterangan ?? '') ?></textarea>
                    </div>

                    <!-- STATUS WORKFLOW -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-5 md:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 mt-6">
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Status Pemeliharaan <span class="text-rose-500">*</span>
                        </label>
                        
                        <div class="relative mb-4">
                            <select name="status" x-model="status" required class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-800 dark:text-white outline-none appearance-none cursor-pointer focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <?php foreach(['Direncanakan', 'Sedang Proses', 'Selesai', 'Batal'] as $s): ?>
                                    <option value="<?= $s ?>"><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        </div>

                        <!-- Opsi Tanggal Aktual Kembali (Hanya tampil jika status 'Selesai') -->
                        <div x-show="status === 'Selesai'" x-collapse>
                            <label class="block text-[10px] md:text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-2">
                                Tanggal Selesai Servis
                            </label>
                            <input type="date" name="tanggal_selesai" 
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950 border border-emerald-300 dark:border-emerald-700 rounded-xl outline-none text-sm font-bold text-emerald-700 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-500" 
                                   value="<?= !empty($valSelesai) ? $valSelesai : date('Y-m-d') ?>">
                            <p class="text-[9px] text-emerald-600/70 mt-1 italic">Diperlukan untuk mencatat durasi penyelesaian servis.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- BUTTONS -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="<?= base_url('app/sapras/pemeliharaan') ?>" class="w-full sm:w-auto px-6 py-4 sm:py-3 text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest">
                    Batal
                </a>
                
                <?php if($isEdit): ?>
                <a href="<?= base_url('app/sapras/pemeliharaan/print-label/' . $pemeliharaan->id) ?>" target="_blank" class="w-full sm:w-auto px-6 py-4 sm:py-3 justify-center bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-black rounded-xl shadow-lg shadow-cyan-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 uppercase tracking-widest border-b-4 border-cyan-800">
                    <i class="fas fa-print"></i> Cetak Label
                </a>
                <?php endif; ?>

                <button type="submit" class="w-full sm:w-auto px-8 py-4 sm:py-3 justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 uppercase tracking-widest border-b-4 border-indigo-800">
                    <i class="fas fa-save"></i> Simpan Log Servis
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
    // AUTO FORMAT RUPIAH
    const rupiahInput = document.getElementById('rupiahInput');
    if (rupiahInput && !rupiahInput.readOnly) {
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

    // Refresh Dropdown Unit
    function ubahUnitForm(val) {
        if(val !== '') {
            let url = new URL(window.location.href);
            url.searchParams.set('jenjang', val);
            window.location.href = url.toString();
        }
    }
</script>

<?= $this->endSection() ?>