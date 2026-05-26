<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 sm:px-6 py-6 max-w-5xl mx-auto space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic flex items-center gap-3">
                <i class="fas fa-file-contract text-indigo-500"></i> CALK (ISAK 35)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catatan Atas Laporan Keuangan sesuai PSAK / ISAK 35.
            </p>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-slate-700 transition-all shadow-md active:scale-95 border-b-4 border-slate-900 no-print">
            <i class="fas fa-print"></i> Cetak CALK
        </button>
    </div>

    <!-- GLOBAL NAVIGATION TABS -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar shadow-inner no-print max-w-full">
        <a href="<?= base_url('app/akuntansi') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50 whitespace-nowrap">Bagan Akun</a>
        <a href="<?= base_url('app/akuntansi/jurnal') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50 whitespace-nowrap">Jurnal Umum</a>
        <a href="<?= base_url('app/akuntansi/buku-besar') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50 whitespace-nowrap">Buku Besar</a>
        <a href="<?= base_url('app/akuntansi/laporan/posisi-keuangan') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50 whitespace-nowrap">Neraca</a>
        <a href="<?= base_url('app/akuntansi/laporan/aktivitas') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50 whitespace-nowrap">Aktivitas</a>
        <a href="<?= base_url('app/akuntansi/laporan/perubahan-aset-neto') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50 whitespace-nowrap">Aset Neto</a>
        <a href="<?= base_url('app/akuntansi/laporan/arus-kas') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50 whitespace-nowrap">Arus Kas</a>
        <a href="<?= base_url('app/akuntansi/laporan/calk') ?>" class="px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md whitespace-nowrap">CALK</a>
    </div>

    <!-- FILTER CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 p-6 md:p-8 no-print">
        <form action="" method="get" class="flex flex-col md:flex-row gap-6 items-end">
            <div class="space-y-2 flex-grow">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Unit Konsolidasi</label>
                <div class="relative">
                    <select name="jenjang" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 uppercase appearance-none outline-none">
                        <option value="GLOBAL" <?= $filterJenjang === 'GLOBAL' ? 'selected' : '' ?>>🏢 KONSOLIDASI (SEMUA UNIT)</option>
                        <?php foreach ($daftarUnit as $kode => $nama): ?>
                            <option value="<?= $kode ?>" <?= $filterJenjang === $kode ? 'selected' : '' ?>>🏫 UNIT <?= strtoupper($kode) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tahun Laporan</label>
                <select name="tahun" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-white outline-none">
                    <?php for($i = date('Y'); $i >= 2023; $i--): ?>
                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-lg border-b-4 border-indigo-800">Tampilkan</button>
        </form>
    </div>

    <!-- MAIN REPORT DOCUMENT -->
    <div class="bg-white dark:bg-slate-900 shadow-xl border border-slate-200 dark:border-slate-800 print-container" style="border-radius: 0;">
        <div class="p-10 md:p-16 max-w-4xl mx-auto space-y-8 font-serif text-slate-800 dark:text-slate-200 leading-relaxed text-sm md:text-base">
            
            <div class="text-center border-b-2 border-slate-800 pb-6 mb-10">
                <h1 class="text-xl md:text-2xl font-bold uppercase tracking-wider"><?= esc($sekolah['nama_yayasan'] ?? 'YAYASAN PENDIDIKAN') ?></h1>
                <h2 class="text-lg font-bold uppercase tracking-widest mt-2">Catatan Atas Laporan Keuangan</h2>
                <p class="font-bold mt-1">Untuk Tahun yang Berakhir pada 31 Desember <?= esc($tahun) ?></p>
            </div>

            <div class="space-y-6">
                <!-- 1. Umum -->
                <div>
                    <h3 class="font-bold text-lg mb-2">1. GAMBARAN UMUM YAYASAN</h3>
                    <p class="mb-2 text-justify indent-8">
                        <strong><?= esc($sekolah['nama_yayasan'] ?? 'Yayasan Pendidikan') ?></strong> didirikan berdasarkan Akta Notaris dan disahkan oleh Kementerian Hukum dan HAM Republik Indonesia. Yayasan ini berkedudukan di <?= esc($sekolah['alamat_instansi'] ?? 'alamat yang terdaftar') ?>.
                    </p>
                    <p class="mb-2 text-justify indent-8">
                        Tujuan utama Yayasan adalah menyelenggarakan pendidikan, kegiatan sosial, dan keagamaan. Laporan keuangan ini mencakup entitas pelaporan yang meliputi Unit <?= esc($filterJenjang === 'GLOBAL' ? 'Konsolidasi Seluruh Unit' : $filterJenjang) ?>.
                    </p>
                </div>

                <!-- 2. Kebijakan Akuntansi -->
                <div>
                    <h3 class="font-bold text-lg mb-2">2. IKHTISAR KEBIJAKAN AKUNTANSI PENTING</h3>
                    <p class="mb-2 font-bold italic">Pernyataan Kepatuhan</p>
                    <p class="mb-4 text-justify indent-8">
                        Laporan Keuangan disusun sesuai dengan <strong>Standar Akuntansi Keuangan Entitas Privat (SAK EP)</strong> dan <strong>Interpretasi Standar Akuntansi Keuangan (ISAK) 35</strong> tentang Penyajian Laporan Keuangan Entitas Berorientasi Nonlaba di Indonesia.
                    </p>
                    
                    <p class="mb-2 font-bold italic">Dasar Pengukuran dan Penyusunan</p>
                    <p class="mb-4 text-justify indent-8">
                        Laporan keuangan disusun berdasarkan konsep harga perolehan (*historical cost*), kecuali untuk beberapa akun tertentu yang diukur menggunakan nilai wajar. Dasar akuntansi yang digunakan adalah dasar akrual (*accrual basis*), kecuali untuk Laporan Arus Kas.
                    </p>

                    <p class="mb-2 font-bold italic">Klasifikasi Aset Neto</p>
                    <p class="mb-2 text-justify indent-8">
                        Berdasarkan ISAK 35, aset neto Yayasan diklasifikasikan menjadi dua kelompok:
                    </p>
                    <ul class="list-disc pl-12 mb-4 space-y-2">
                        <li><strong>Aset Neto Tanpa Pembatasan:</strong> Bagian aset neto yang penggunaannya tidak dibatasi oleh persyaratan atau donatur, dan dapat digunakan untuk operasional umum Yayasan.</li>
                        <li><strong>Aset Neto Dengan Pembatasan:</strong> Bagian aset neto yang penggunaannya dibatasi oleh donatur untuk tujuan spesifik atau periode tertentu (misal: Wakaf bangunan, Beasiswa khusus).</li>
                    </ul>

                    <p class="mb-2 font-bold italic">Pengakuan Pendapatan dan Beban</p>
                    <p class="mb-4 text-justify indent-8">
                        Pendapatan dari SPP dan uang pangkal diakui secara proporsional selama masa manfaat atau saat jasa pendidikan diberikan. Sumbangan dan hibah diakui sebagai pendapatan pada saat diterima atau saat hak untuk menagih timbul. Beban diakui pada saat terjadinya (*accrual basis*).
                    </p>
                    
                    <p class="mb-2 font-bold italic">Aset Tetap dan Penyusutan</p>
                    <p class="text-justify indent-8">
                        Aset tetap dicatat sebesar harga perolehan dikurangi akumulasi penyusutan. Penyusutan dihitung menggunakan metode garis lurus (*straight-line method*) berdasarkan estimasi masa manfaat ekonomis aset tersebut. Biaya perbaikan dan pemeliharaan rutin dibebankan ke dalam Laporan Aktivitas pada saat terjadinya.
                    </p>
                </div>

                <!-- 3. Rincian Tambahan -->
                <div>
                    <h3 class="font-bold text-lg mb-2">3. INFORMASI TAMBAHAN</h3>
                    <p class="mb-2 text-justify indent-8">
                        Rincian saldo kas, piutang, dan liabilitas telah sesuai dengan Buku Besar (*General Ledger*) yang dihasilkan oleh NusantaraERP per tanggal 31 Desember <?= esc($tahun) ?>. Tidak ada kejadian luar biasa setelah tanggal pelaporan yang mempengaruhi kewajaran laporan keuangan ini secara material.
                    </p>
                </div>
            </div>

            <div class="mt-20 flex justify-end no-print-bg">
                <div class="text-center w-64">
                    <p>Diterbitkan di Depok,</p>
                    <p class="mb-24 font-bold">Ketua Pengurus Yayasan</p>
                    <p class="font-bold underline">( ........................................ )</p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @media print {
        .no-print { display: none !important; }
        .print-container { box-shadow: none !important; border: none !important; margin: 0 !important; }
        body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<?= $this->endSection() ?>