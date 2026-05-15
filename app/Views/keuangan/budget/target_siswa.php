<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- Alpine.js Data Initialization -->
<div class="w-full max-w-4xl mx-auto" x-data='{
    jmlSiswa: 0,
    nominalPerSiswa: 0,
    total: 0,
    multiplier: 1,
    labelTipe: "Sekali Bayar",
    paymentTypes: <?= json_encode($payment_types) ?>,
    
    updatePayment(e) {
        const id = e.target.value;
        const selected = this.paymentTypes.find(p => p.id == id);
        if (selected) {
            // Set Multiplier based on "tipe" from JenisPembayaranModel
            this.multiplier = (selected.tipe === "bulanan") ? 12 : 1;
            this.labelTipe = selected.tipe.replace("_", " ").toUpperCase();
            
            // Auto-fill nominal from master data if current input is empty
            let currentNominal = String(this.nominalPerSiswa).replace(/\./g, "");
            if (!currentNominal || currentNominal == 0) {
                this.nominalPerSiswa = parseInt(selected.nominal).toLocaleString("id-ID");
            }
        }
        this.calculate();
    },
    calculate() {
        let cleanNominal = String(this.nominalPerSiswa).replace(/\./g, "").replace(/[^0-9]/g, "");
        const nominal = parseInt(cleanNominal) || 0;
        const siswa = parseInt(this.jmlSiswa) || 0;
        this.total = siswa * nominal * this.multiplier;
    },
    formatRupiah(e) {
        let val = e.target.value.replace(/[^0-9]/g, "");
        this.nominalPerSiswa = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        this.calculate();
    }
}'>
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">
        <a href="<?= base_url('app/keuangan/budget') ?>" class="hover:text-primary transition-colors">Manajemen Anggaran</a>
        <i class="fas fa-chevron-right text-[8px]"></i>
        <span class="text-gray-800 dark:text-white">Kalkulator Target Pendapatan</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tighter flex items-center uppercase">
                <i class="fas fa-calculator mr-3 text-indigo-600"></i> 
                PROYEKSI PENDAPATAN SISWA
            </h2>
            <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                Sinkronisasi <span class="text-indigo-600">Master Pembayaran</span> ke <span class="text-primary">Anggaran ISAK 35</span>
            </p>
        </div>
        <a href="<?= base_url('app/keuangan/budget') ?>" class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-xl hover:bg-gray-200 transition-all font-black text-[10px] uppercase tracking-widest">
            <i class="fas fa-arrow-left mr-2"></i> Batal
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Input Form -->
        <div class="lg:col-span-7 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-[2.5rem] shadow-xl overflow-hidden shadow-indigo-100/50 dark:shadow-none">
            <form action="<?= base_url('app/keuangan/budget/target-siswa/save') ?>" method="post">
                <?= csrf_field() ?>
                <div class="p-10 space-y-6">
                    <!-- Unit & Tahun -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Unit Sekolah</label>
                            <select name="kode_jenjang" required class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-primary transition-all">
                                <option value="">-- Pilih Unit --</option>
                                <?php foreach($jenjang as $j): ?>
                                    <option value="<?= $j['kode_jenjang'] ?>" <?= (isset($user_unit) && $user_unit == $j['kode_jenjang']) ? 'selected' : '' ?>>
                                        <?= strtoupper($j['nama_jenjang']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tahun Ajaran</label>
                            <input type="text" name="tahun" required value="<?= $tahun_aktif ?>"
                                   class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <!-- Master Item Pembayaran -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center justify-between">
                            <span>Item Pembayaran Siswa (Master)</span>
                            <span class="text-[8px] bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded" x-text="labelTipe">SEKALI BAYAR</span>
                        </label>
                        <select name="id_jenis_pembayaran" @change="updatePayment($event)" required class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            <option value="" disabled selected>-- Pilih Item Operasional --</option>
                            <?php foreach($payment_types as $pt): ?>
                                <option value="<?= $pt['id'] ?>">
                                    <?= strtoupper($pt['nama_pembayaran']) ?> (<?= $pt['tipe'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Akun ISAK 35 -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Mapping ke Akun Anggaran (ISAK 35)</label>
                        <select name="id_kategori" required class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500">
                            <option value="" disabled selected>-- Pilih Akun COA --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">[<?= $cat['kode_kategori'] ?>] <?= strtoupper($cat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jml & Nominal -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Estimasi Siswa</label>
                            <input type="number" name="jumlah_siswa" x-model="jmlSiswa" @input="calculate()" required placeholder="0"
                                   class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-5 py-4 text-base font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nominal per Siswa</label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-indigo-600 font-black text-xs">Rp</span>
                                <input type="text" name="nominal_per_siswa" x-model="nominalPerSiswa" @input="formatRupiah($event)" required placeholder="0"
                                       class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl pl-12 pr-5 py-4 text-base font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-primary">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/50 dark:bg-gray-800/20 px-10 py-8">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl py-5 font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-200 transition-all hover:scale-[1.01] active:scale-95">
                        <i class="fas fa-save mr-2"></i> Sinkronkan ke Anggaran
                    </button>
                </div>
            </form>
        </div>

        <!-- Result Card -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-[3rem] p-10 shadow-2xl relative overflow-hidden group">
                <i class="fas fa-calculator absolute -right-6 -bottom-6 text-9xl text-white opacity-5 transform group-hover:scale-110 transition-transform duration-700"></i>
                <div class="relative z-10 text-white">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-2 border-b border-white/10 pb-2 w-fit">Estimasi Proyeksi Tahunan</p>
                    <h3 class="text-4xl font-black tracking-tighter mb-8" x-text="'Rp ' + total.toLocaleString('id-ID')">Rp 0</h3>
                    
                    <div class="grid grid-cols-2 gap-6 pt-6 border-t border-white/10">
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-widest opacity-50 mb-1">Siklus</p>
                            <p class="text-xs font-black uppercase tracking-tight" x-text="multiplier == 12 ? 'Bulanan (x12)' : 'Sekali Bayar (x1)'"></p>
                        </div>
                        <div>
                            <p class="text-[8px] font-black uppercase tracking-widest opacity-50 mb-1">Kapasitas</p>
                            <p class="text-xs font-black uppercase tracking-tight" x-text="jmlSiswa + ' Siswa'"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mekanisme Info -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-[2.5rem] p-8 shadow-sm">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-indigo-500"></i> Informasi Mekanisme
                </h4>
                <div class="space-y-5">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-[10px]">1</div>
                        <p class="text-[11px] font-bold text-gray-500 leading-relaxed uppercase tracking-tight">Pilih <span class="text-gray-800 dark:text-white">Item Master</span> untuk mendeteksi frekuensi bayar secara otomatis.</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-[10px]">2</div>
                        <p class="text-[11px] font-bold text-gray-500 leading-relaxed uppercase tracking-tight">Hasil kalkulasi akan disimpan ke tabel <span class="text-gray-800 dark:text-white">Anggaran Unit</span> sebagai plafon target.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>