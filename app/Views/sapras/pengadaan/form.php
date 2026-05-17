<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
$isGlobalUser = in_array($sessionJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL']);

// Helper formatting Harga
$biayaFormatted = '';
if ($pengadaan && !empty($pengadaan->estimasi_biaya)) {
    $biayaFormatted = number_format($pengadaan->estimasi_biaya, 0, ',', '.');
}

// Cek status dokumen
$statusDokumen = $pengadaan->status ?? 'Draft';
$isLocked = in_array($statusDokumen, ['Disetujui', 'Selesai/Dibeli']);
?>

<div class="px-4 sm:px-6 py-6 max-w-5xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                <?= $pengadaan ? 'Review atau perbarui proposal pengadaan.' : 'Ajukan proposal pembelian / pengadaan aset sekolah.' ?>
            </p>
        </div>
        <a href="<?= base_url('app/sapras/pengadaan') ?>" 
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

    <?php if ($isLocked): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm flex gap-3 animate-pulse">
            <i class="fas fa-lock text-emerald-500 mt-0.5"></i>
            <div>
                <h3 class="text-sm font-bold text-emerald-800 uppercase tracking-widest mb-1">DOKUMEN TERKUNCI</h3>
                <p class="text-xs text-emerald-700">Proposal ini sudah <strong>Disetujui</strong> atau <strong>Selesai</strong> dan tidak dapat diubah lagi untuk alasan audit (Read-Only).</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- FORM CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden relative">
        <!-- Form Accent Line -->
        <div class="h-1.5 w-full bg-gradient-to-r from-blue-600 to-indigo-600"></div>

        <form action="<?= base_url('app/sapras/pengadaan/save') ?>" method="post" class="p-5 md:p-8 space-y-8">
            <?= csrf_field() ?>
            
            <?php if ($pengadaan): ?>
                <input type="hidden" name="id" value="<?= esc($pengadaan->id) ?>">
                <input type="hidden" name="no_pengajuan" value="<?= esc($pengadaan->no_pengajuan) ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- KOLOM KIRI: DATA PROPOSAL -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <i class="fas fa-file-alt w-5"></i> Rincian Pengajuan
                    </h3>

                    <!-- Unit Pemilik -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Unit Pengaju <span class="text-rose-500">*</span>
                        </label>
                        <?php if ($isGlobalUser && !$isLocked): ?>
                            <div class="relative">
                                <select name="kode_jenjang" id="kode_jenjang" required onchange="ubahUnitForm(this.value)"
                                        class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none uppercase">
                                    
                                    <!-- FIX: Penambahan Opsi Yayasan/Pusat -->
                                    <option value="GLOBAL" <?= old('kode_jenjang', $pengadaan->kode_jenjang ?? $filterJenjang) == 'GLOBAL' ? 'selected' : '' ?>>
                                        YAYASAN / KANTOR PUSAT
                                    </option>
                                    
                                    <?php foreach ($daftarUnit as $kode => $nama): ?>
                                        <option value="<?= $kode ?>" <?= old('kode_jenjang', $pengadaan->kode_jenjang ?? $filterJenjang) == $kode ? 'selected' : '' ?>>
                                            UNIT <?= strtoupper($kode) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        <?php else: ?>
                            <!-- Fix: Label khusus jika milik Yayasan -->
                            <?php $labelUnit = ($pengadaan->kode_jenjang ?? $sessionJenjang) === 'GLOBAL' ? 'YAYASAN / KANTOR PUSAT' : 'Unit ' . esc($pengadaan->kode_jenjang ?? $sessionJenjang); ?>
                            <input type="hidden" name="kode_jenjang" value="<?= esc($pengadaan->kode_jenjang ?? $sessionJenjang) ?>">
                            <div class="w-full px-4 py-3.5 md:py-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center gap-2 cursor-not-allowed">
                                <i class="fas fa-lock text-slate-400"></i> <?= $labelUnit ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Judul Proposal -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Judul / Perihal Pengajuan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="judul_pengajuan" required <?= $isLocked ? 'readonly' : '' ?>
                               class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-900 dark:text-white <?= $isLocked ? 'bg-slate-50 opacity-70' : '' ?>"
                               placeholder="Contoh: Pengadaan 10 Unit PC Laboratorium"
                               value="<?= old('judul_pengajuan', $pengadaan->judul_pengajuan ?? '') ?>">
                    </div>

                    <!-- Kategori Master -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Kategori Barang <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="id_kategori" required <?= $isLocked ? 'disabled' : '' ?>
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none <?= $isLocked ? 'bg-slate-50 opacity-70 cursor-not-allowed' : '' ?>">
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php foreach ($kategoriList as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= old('id_kategori', $pengadaan->id_kategori ?? '') == $k['id'] ? 'selected' : '' ?>>
                                        [<?= esc($k['kode_kategori']) ?>] <?= esc($k['nama_kategori']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        <?php if($isLocked): ?>
                            <input type="hidden" name="id_kategori" value="<?= $pengadaan->id_kategori ?>">
                        <?php endif; ?>
                    </div>

                    <!-- Pemohon -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Nama Pemohon (Pegawai) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="id_pemohon" required <?= $isLocked ? 'disabled' : '' ?>
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none <?= $isLocked ? 'bg-slate-50 opacity-70 cursor-not-allowed' : '' ?>">
                                <option value="" disabled selected>-- Pilih Pegawai --</option>
                                <?php foreach ($pegawaiList as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= old('id_pemohon', $pengadaan->id_pemohon ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= esc($p['nama_lengkap']) ?> (<?= esc($p['jabatan_fungsional'] ?? 'Staf') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        <?php if($isLocked): ?>
                            <input type="hidden" name="id_pemohon" value="<?= $pengadaan->id_pemohon ?>">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- KOLOM KANAN: FINANSIAL & JUSTIFIKASI -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-amber-500 dark:text-amber-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <i class="fas fa-coins w-5"></i> Kebutuhan & Anggaran
                    </h3>

                    <!-- GRID Jumlah & Biaya -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Jumlah Diminta <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input type="number" name="jumlah_diminta" required <?= $isLocked ? 'readonly' : '' ?>
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-amber-500 <?= $isLocked ? 'bg-slate-50 opacity-70' : '' ?>"
                                    value="<?= old('jumlah_diminta', $pengadaan->jumlah_diminta ?? '') ?>" min="1">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Unit</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Estimasi Total Biaya (Rp)</label>
                            <input type="text" name="estimasi_biaya" id="rupiahInput" <?= $isLocked ? 'readonly' : '' ?>
                                   class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-sm font-bold text-emerald-600 dark:text-emerald-400 focus:ring-2 focus:ring-amber-500 <?= $isLocked ? 'bg-slate-50 opacity-70' : '' ?>" 
                                   placeholder="15.000.000" 
                                   value="<?= old('estimasi_biaya', $biayaFormatted) ?>">
                        </div>
                    </div>

                    <!-- Alasan Kebutuhan -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Alasan / Justifikasi Kebutuhan <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alasan_kebutuhan" rows="4" required <?= $isLocked ? 'readonly' : '' ?>
                                  class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-medium text-slate-700 dark:text-slate-300 resize-none <?= $isLocked ? 'bg-slate-50 opacity-70' : '' ?>"
                                  placeholder="Jelaskan secara rinci mengapa barang ini sangat dibutuhkan..."><?= old('alasan_kebutuhan', $pengadaan->alasan_kebutuhan ?? '') ?></textarea>
                    </div>

                    <!-- STATUS & REVIEW (HANYA MUNCUL JIKA SUDAH DISIMPAN ATAU OLEH ADMIN) -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-5 md:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 mt-6">
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Status Approval
                        </label>
                        
                        <?php if ($isGlobalUser && !$isLocked): ?>
                            <div class="relative mb-4">
                                <select name="status" class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-800 dark:text-white outline-none appearance-none cursor-pointer focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                    <?php foreach(['Draft', 'Menunggu Approval', 'Disetujui', 'Ditolak', 'Selesai/Dibeli'] as $s): ?>
                                        <option value="<?= $s ?>" <?= old('status', $statusDokumen) == $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            </div>
                            
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Catatan Reviewer (Opsional)</label>
                            <textarea name="catatan_reviewer" rows="3" class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl outline-none text-sm text-slate-700 dark:text-slate-300 resize-none placeholder-slate-400" placeholder="Ketik catatan revisi atau alasan penolakan/persetujuan di sini..."><?= old('catatan_reviewer', $pengadaan->catatan_reviewer ?? '') ?></textarea>
                        
                        <?php else: ?>
                            <!-- JIKA BUKAN GLOBAL ATAU JIKA DOKUMEN TERKUNCI -->
                            <?php if(!$isLocked): ?>
                                <div class="relative mb-4">
                                    <select name="status" class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-800 dark:text-white outline-none appearance-none cursor-pointer focus:border-indigo-500">
                                        <!-- Unit Admin hanya boleh set ke Draft atau Menunggu -->
                                        <option value="Draft" <?= $statusDokumen == 'Draft' ? 'selected' : '' ?>>Draft (Simpan Sementara)</option>
                                        <option value="Menunggu Approval" <?= $statusDokumen == 'Menunggu Approval' ? 'selected' : '' ?>>Ajukan (Menunggu Approval)</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="status" value="<?= $statusDokumen ?>">
                                <div class="w-full px-4 py-3 bg-slate-200 dark:bg-slate-700 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest mb-4 border border-slate-300">
                                    STATUS: <?= $statusDokumen ?>
                                </div>
                            <?php endif; ?>

                            <!-- Catatan Selalu Readonly bagi Unit Admin jika ada isinya -->
                            <?php if(!empty($pengadaan->catatan_reviewer)): ?>
                                <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-200 dark:border-amber-800/50">
                                    <h4 class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Catatan Manajemen:</h4>
                                    <p class="text-sm text-slate-800 dark:text-slate-200 italic font-medium whitespace-pre-line"><?= esc($pengadaan->catatan_reviewer) ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- BUTTONS -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="<?= base_url('app/sapras/pengadaan') ?>" class="w-full sm:w-auto px-6 py-4 sm:py-3 text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest">
                    Kembali
                </a>
                
                <?php if(!$isLocked): ?>
                <button type="submit" class="w-full sm:w-auto px-8 py-4 sm:py-3 justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 uppercase tracking-widest border-b-4 border-indigo-800">
                    <i class="fas fa-paper-plane"></i> Simpan Dokumen
                </button>
                <?php endif; ?>
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

    // Refresh Form saat ganti Unit (Agar Pegawai menyesuaikan)
    function ubahUnitForm(val) {
        if(val !== '') {
            let url = new URL(window.location.href);
            url.searchParams.set('jenjang', val);
            window.location.href = url.toString();
        }
    }
</script>

<?= $this->endSection() ?>