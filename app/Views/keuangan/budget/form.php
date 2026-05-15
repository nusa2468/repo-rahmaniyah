<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full max-w-3xl mx-auto" x-data="{ 
    nominal: '<?= isset($budget['nominal']) ? number_format($budget['nominal'], 0, ',', '.') : '' ?>',
    formatRupiah(val) {
        let number = val.replace(/[^,\d]/g, '').toString();
        let split = number.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        this.nominal = rupiah;
    }
}">
    <!-- Breadcrumb & Navigation -->
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">
        <a href="<?= base_url('app/keuangan/laporan/budget') ?>" class="hover:text-primary transition-colors">Anggaran</a>
        <i class="fas fa-chevron-right text-[8px]"></i>
        <span class="text-gray-800 dark:text-white"><?= isset($budget) ? 'Edit Anggaran' : 'Tambah Baru' ?></span>
    </div>

    <!-- Header Card -->
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tighter flex items-center uppercase">
                <i class="fas <?= isset($budget) ? 'fa-edit' : 'fa-plus-circle' ?> mr-3 text-primary"></i> 
                <?= isset($budget) ? 'Edit Alokasi Anggaran' : 'Alokasi Anggaran Baru' ?>
            </h2>
            <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                Referensi Standar <span class="text-primary text-xs italic">ISAK 35 / SAK EP</span>
            </p>
        </div>
        <a href="<?= base_url('app/keuangan/laporan/budget') ?>" class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded-xl hover:bg-gray-200 transition-all font-black text-[10px] uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl overflow-hidden shadow-xl shadow-gray-200/50 dark:shadow-none">
        <form action="<?= base_url('app/keuangan/laporan/budget/save') ?>" method="post" class="divide-y divide-gray-100 dark:divide-white/5">
            <?= csrf_field() ?>
            <?php if(isset($budget['id'])): ?>
                <input type="hidden" name="id" value="<?= $budget['id'] ?>">
            <?php endif; ?>

            <!-- Section 1: Identitas Anggaran -->
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tahun Ajaran -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                            <i class="far fa-calendar-alt mr-2 text-primary"></i> Tahun Ajaran
                        </label>
                        <input type="text" name="tahun" required placeholder="Contoh: 2025/2026"
                               value="<?= old('tahun', $budget['tahun'] ?? date('Y').'/'.(date('Y')+1)) ?>"
                               class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-primary transition-all">
                    </div>

                    <!-- Unit Monitoring -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                            <i class="fas fa-university mr-2 text-primary"></i> Unit Monitoring
                        </label>
                        <select name="kode_jenjang" class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-primary transition-all">
                            <option value="">GLOBAL / YAYASAN</option>
                            <?php foreach($jenjang as $j): ?>
                                <option value="<?= $j['kode_jenjang'] ?>" <?= (old('kode_jenjang', $budget['kode_jenjang'] ?? '') == $j['kode_jenjang']) ? 'selected' : '' ?>>
                                    <?= $j['nama_jenjang'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Kategori COA -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                        <i class="fas fa-tags mr-2 text-primary"></i> Akun / Kategori (ISAK 35)
                    </label>
                    <select name="id_kategori" required class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-primary transition-all">
                        <option value="" disabled <?= !isset($budget) ? 'selected' : '' ?>>-- Pilih Akun Anggaran --</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (old('id_kategori', $budget['id_kategori'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                [<?= $cat['kode_kategori'] ?>] <?= strtoupper($cat['nama_kategori']) ?> (<?= strtoupper($cat['kelompok']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Section 2: Nominal & Keterangan -->
            <div class="p-8 bg-gray-50/50 dark:bg-gray-800/20 space-y-6">
                <!-- Nominal -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                        <i class="fas fa-coins mr-2 text-primary"></i> Target Nominal Anggaran
                    </label>
                    <div class="relative group">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-primary font-black text-sm">Rp</span>
                        <input type="text" name="nominal" x-model="nominal" @input="formatRupiah($event.target.value)" required
                               placeholder="0"
                               class="w-full bg-white dark:bg-gray-900 border-2 border-transparent focus:border-primary rounded-2xl pl-14 pr-6 py-5 text-xl font-black text-gray-800 dark:text-white shadow-sm transition-all outline-none">
                    </div>
                    <p class="mt-2 text-[10px] text-gray-400 font-bold uppercase tracking-tighter italic">Pastikan nominal sesuai dengan rencana kerja tahunan (RKT).</p>
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                        <i class="fas fa-align-left mr-2 text-primary"></i> Deskripsi / Justifikasi
                    </label>
                    <textarea name="keterangan" rows="4" 
                              class="w-full bg-white dark:bg-gray-900 border-none rounded-2xl px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-primary transition-all"
                              placeholder="Berikan alasan atau detail mengenai alokasi anggaran ini..."><?= old('keterangan', $budget['keterangan'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Footer: Actions -->
            <div class="px-8 py-6 bg-white dark:bg-gray-900 flex flex-col sm:flex-row-reverse items-center gap-4">
                <button type="submit" class="w-full sm:w-auto px-10 py-4 bg-primary hover:bg-primary-dark text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-primary/30 transition-all hover:scale-[1.02] active:scale-95">
                    <i class="fas fa-save mr-2"></i> <?= isset($budget) ? 'Perbarui Anggaran' : 'Simpan Anggaran' ?>
                </button>
                <a href="<?= base_url('app/keuangan/laporan/budget') ?>" class="w-full sm:w-auto px-10 py-4 bg-gray-50 dark:bg-gray-800 text-gray-400 hover:text-gray-600 dark:hover:text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Info Tip -->
    <div class="mt-8 p-6 bg-indigo-50 dark:bg-indigo-500/5 border border-indigo-100 dark:border-indigo-500/20 rounded-3xl flex items-start gap-4">
        <div class="bg-indigo-600 text-white p-2 rounded-xl shadow-lg shadow-indigo-600/20">
            <i class="fas fa-info-circle"></i>
        </div>
        <div>
            <h4 class="text-xs font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-widest mb-1">Pentingnya Anggaran Berbasis COA</h4>
            <p class="text-[11px] text-indigo-700/70 dark:text-indigo-400/70 leading-relaxed font-medium">
                Pencatatan anggaran yang tepat membantu Yayasan dalam memantau **Analisis Varian**. Dengan memecah anggaran berdasarkan kategori ISAK 35, sistem dapat memberikan laporan real-time apakah unit tertentu mengalami penghematan atau pemborosan (Over-budget).
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>