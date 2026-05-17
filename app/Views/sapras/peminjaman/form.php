<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
$isGlobalUser = in_array($sessionJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT', 'ALL']);

$isEdit = !empty($peminjaman);

// Tanggal Default (Hari ini untuk Pinjam, Besok untuk Estimasi)
$defaultPinjam = date('Y-m-d\TH:i');
$defaultKembali = date('Y-m-d\TH:i', strtotime('+1 days'));

// Nilai existing
$valPinjam = $peminjaman->tanggal_pinjam ?? $defaultPinjam;
$valEstKembali = $peminjaman->estimasi_kembali ?? $defaultKembali;
$valTglKembali = $peminjaman->tanggal_kembali ?? '';
$valStatus = $peminjaman->status ?? 'Menunggu';
?>

<div class="px-4 sm:px-6 py-6 max-w-5xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                <?= $isEdit ? 'Ubah status atau detail peminjaman aset.' : 'Catat log pengeluaran/peminjaman aset dari ruang logistik.' ?>
            </p>
        </div>
        <a href="<?= base_url('app/sapras/peminjaman') ?>" 
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
    <!-- Memanfaatkan AlpineJS untuk interaktivitas Dropdown Siswa/Pegawai -->
    <div x-data="{ 
            tipePeminjam: '<?= old('tipe_peminjam', $peminjaman->tipe_peminjam ?? 'Pegawai') ?>',
            status: '<?= old('status', $valStatus) ?>'
         }" 
         class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden relative">
        
        <!-- Form Accent Line -->
        <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 to-amber-500"></div>

        <form action="<?= base_url('app/sapras/peminjaman/save') ?>" method="post" class="p-5 md:p-8 space-y-8">
            <?= csrf_field() ?>
            
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= esc($peminjaman->id) ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- KOLOM KIRI: ASET & PEMINJAM -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <i class="fas fa-box w-5"></i> Objek Aset & Subjek
                    </h3>

                    <!-- Filter Unit Internal (Hanya Global yang butuh ini utk switch dropdown aset) -->
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
                            Pilih Aset yang Dipinjam <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="id_aset" required <?= $isEdit ? 'disabled' : '' ?>
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none <?= $isEdit ? 'bg-slate-50 opacity-70 cursor-not-allowed' : '' ?>">
                                <option value="" disabled selected>-- Cari dan Pilih Aset Tersedia --</option>
                                <?php foreach ($barangList as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= old('id_aset', $peminjaman->id_aset ?? '') == $b['id'] ? 'selected' : '' ?>>
                                        [<?= esc($b['kode_aset']) ?>] <?= esc($b['nama_aset']) ?> (<?= esc($b['kode_jenjang']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        <?php if($isEdit): ?>
                            <input type="hidden" name="id_aset" value="<?= $peminjaman->id_aset ?>">
                            <p class="text-[9px] text-slate-500 mt-1 italic">Objek aset yang sudah dipinjam tidak dapat diubah (Buat transaksi baru).</p>
                        <?php endif; ?>
                    </div>

                    <!-- Tipe Peminjam (Toggle Pegawai / Siswa) -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Tipe Peminjam <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="tipe_peminjam" x-model="tipePeminjam" required <?= $isEdit ? 'disabled' : '' ?>
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none <?= $isEdit ? 'bg-slate-50 opacity-70 cursor-not-allowed' : '' ?>">
                                <option value="Pegawai">Pegawai / Staf / Guru</option>
                                <option value="Siswa">Siswa / Murid</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        <?php if($isEdit): ?>
                            <input type="hidden" name="tipe_peminjam" value="<?= $peminjaman->tipe_peminjam ?>">
                        <?php endif; ?>
                    </div>

                    <!-- Peminjam (Tergantung Tipe) -->
                    <div x-show="tipePeminjam === 'Pegawai'">
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Nama Peminjam (Pegawai) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <!-- x-bind:disabled agar yang disubmit hanya dropdown yang terlihat -->
                            <select name="id_peminjam" :disabled="tipePeminjam !== 'Pegawai' <?= $isEdit ? '|| true' : '' ?>" required 
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none <?= $isEdit ? 'bg-slate-50 opacity-70 cursor-not-allowed' : '' ?>">
                                <option value="" disabled selected>-- Pilih Pegawai --</option>
                                <?php foreach ($pegawaiList as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= old('id_peminjam', $peminjaman->id_peminjam ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= esc($p['nama_lengkap']) ?> (<?= esc($p['jabatan_fungsional'] ?? 'Staf') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <div x-show="tipePeminjam === 'Siswa'" x-cloak>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Nama Peminjam (Siswa) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="id_peminjam" :disabled="tipePeminjam !== 'Siswa' <?= $isEdit ? '|| true' : '' ?>" required 
                                    class="w-full pl-4 pr-10 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none <?= $isEdit ? 'bg-slate-50 opacity-70 cursor-not-allowed' : '' ?>">
                                <option value="" disabled selected>-- Pilih Siswa Aktif --</option>
                                <?php foreach ($siswaList as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= old('id_peminjam', $peminjaman->id_peminjam ?? '') == $s['id'] ? 'selected' : '' ?>>
                                        <?= esc($s['nama_lengkap']) ?> (NIS: <?= esc($s['nis'] ?? '-') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                    <?php if($isEdit): ?>
                        <!-- Mempertahankan ID peminjam lama -->
                        <input type="hidden" name="id_peminjam" value="<?= $peminjaman->id_peminjam ?>">
                    <?php endif; ?>

                </div>

                <!-- KOLOM KANAN: WAKTU & STATUS -->
                <div class="space-y-6">
                    <h3 class="text-xs font-black text-amber-500 dark:text-amber-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                        <i class="fas fa-clock w-5"></i> Durasi & Status
                    </h3>

                    <!-- GRID Waktu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Tanggal Diambil <span class="text-rose-500">*</span></label>
                            <input type="datetime-local" name="tanggal_pinjam" required 
                                   class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500" 
                                   value="<?= date('Y-m-d\TH:i', strtotime($valPinjam)) ?>">
                        </div>
                        <div>
                            <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Estimasi Kembali <span class="text-rose-500">*</span></label>
                            <input type="datetime-local" name="estimasi_kembali" required 
                                   class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl outline-none text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500" 
                                   value="<?= date('Y-m-d\TH:i', strtotime($valEstKembali)) ?>">
                        </div>
                    </div>

                    <!-- Keperluan -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Alasan / Keperluan Peminjaman
                        </label>
                        <textarea name="keperluan" rows="3" 
                                  class="w-full px-4 py-3.5 md:py-3 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-sm font-medium text-slate-700 dark:text-slate-300 resize-none"
                                  placeholder="Contoh: Dipinjam untuk acara persami di lapangan..."><?= old('keperluan', $peminjaman->keperluan ?? '') ?></textarea>
                    </div>

                    <!-- STATUS WORKFLOW -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-5 md:p-6 rounded-2xl border border-slate-200 dark:border-slate-700 mt-6">
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Status Terkini <span class="text-rose-500">*</span>
                        </label>
                        
                        <div class="relative mb-4">
                            <select name="status" x-model="status" required class="w-full px-4 py-3 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-xl text-sm font-bold text-slate-800 dark:text-white outline-none appearance-none cursor-pointer focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <?php foreach(['Menunggu', 'Dipinjam', 'Dikembalikan', 'Terlambat'] as $s): ?>
                                    <option value="<?= $s ?>"><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        </div>

                        <!-- Opsi Tanggal Aktual Kembali (Hanya tampil jika status 'Dikembalikan') -->
                        <div x-show="status === 'Dikembalikan'" x-collapse>
                            <label class="block text-[10px] md:text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-2">
                                Waktu Aktual Kembali
                            </label>
                            <input type="datetime-local" name="tanggal_kembali" 
                                   class="w-full px-4 py-3 bg-white dark:bg-slate-950 border border-emerald-300 dark:border-emerald-700 rounded-xl outline-none text-sm font-bold text-emerald-700 dark:text-emerald-400 focus:ring-2 focus:ring-emerald-500" 
                                   value="<?= !empty($valTglKembali) ? date('Y-m-d\TH:i', strtotime($valTglKembali)) : date('Y-m-d\TH:i') ?>">
                            <p class="text-[9px] text-emerald-600/70 mt-1 italic">Jika dikosongkan, sistem otomatis mencatat waktu saat ini.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- BUTTONS -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                <a href="<?= base_url('app/sapras/peminjaman') ?>" class="w-full sm:w-auto px-6 py-4 sm:py-3 text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest">
                    Batal
                </a>
                
                <button type="submit" class="w-full sm:w-auto px-8 py-4 sm:py-3 justify-center bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center gap-2 uppercase tracking-widest border-b-4 border-indigo-800">
                    <i class="fas fa-save"></i> Simpan Transaksi
                </button>
            </div>
            
        </form>
    </div>
</div>

<script>
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