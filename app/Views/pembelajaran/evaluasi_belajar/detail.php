<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8 no-print">
        <a href="<?= base_url('app/pembelajaran/evaluasi-belajar') ?>" class="text-gray-500 font-bold hover:text-indigo-600 transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
        <div class="flex gap-2">
            <a href="<?= base_url('app/pembelajaran/evaluasi-belajar/edit/' . $evaluasi['id']) ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-xl font-bold shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-pen"></i> Edit
            </a>
            <button onclick="window.print()" class="bg-gray-800 hover:bg-black text-white px-5 py-2 rounded-xl font-bold shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-print"></i> Cetak
            </button>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
        <!-- Header Card -->
        <div class="p-10 border-b border-gray-50 bg-indigo-600 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-clipboard-check fa-8x"></i>
            </div>
            
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-4">
                    <span class="px-3 py-1 bg-white/20 text-white text-[10px] font-black rounded-full uppercase tracking-widest border border-white/10">
                        Unit <?= $evaluasi['kode_jenjang'] ?> | <?= $evaluasi['jenis_evaluasi'] ?>
                    </span>
                    <span class="text-[10px] font-black uppercase tracking-widest opacity-70 font-mono">
                        REF: EV-<?= str_pad($evaluasi['id'], 5, '0', STR_PAD_LEFT) ?>
                    </span>
                </div>
                
                <h1 class="text-3xl md:text-4xl font-black leading-tight mb-2"><?= esc($evaluasi['judul_evaluasi']) ?></h1>
                
                <!-- FIX: Data Mata Pelajaran sekarang akan muncul -->
                <p class="font-bold opacity-90 uppercase text-xs tracking-wide flex items-center gap-2">
                    <i class="fas fa-book"></i> Mata Pelajaran: <?= esc($evaluasi['nama_mapel'] ?? 'Belum terhubung Mapel') ?>
                </p>
            </div>
        </div>

        <div class="p-10 space-y-10">
            <!-- Info Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="p-5 bg-gray-50 rounded-2xl text-center border border-gray-200">
                    <p class="text-[9px] font-black text-gray-400 uppercase mb-1 tracking-widest">Durasi</p>
                    <p class="text-xl font-black text-gray-800"><?= $evaluasi['durasi'] ?> <span class="text-xs text-gray-500 font-bold">Min</span></p>
                </div>
                <div class="p-5 bg-gray-50 rounded-2xl text-center border border-gray-200">
                    <p class="text-[9px] font-black text-gray-400 uppercase mb-1 tracking-widest">KKM</p>
                    <p class="text-xl font-black text-emerald-600"><?= $evaluasi['kkm'] ?></p>
                </div>
                <div class="md:col-span-2 p-5 bg-gray-50 rounded-2xl border border-gray-200 flex flex-col justify-center">
                    <p class="text-[9px] font-black text-gray-400 uppercase mb-1 tracking-widest">Materi Pokok (Silabus)</p>
                    <!-- FIX: Data Materi Pokok sekarang akan muncul -->
                    <p class="text-sm font-bold text-gray-800 truncate" title="<?= esc($evaluasi['materi_pokok'] ?? '') ?>">
                        <?= esc($evaluasi['materi_pokok'] ?? 'Data silabus tidak ditemukan') ?>
                    </p>
                </div>
            </div>

            <!-- Jadwal Section -->
            <section class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-indigo-50/50 p-6 rounded-2xl border border-indigo-100">
                <div>
                    <h3 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <i class="far fa-calendar-check"></i> Waktu Mulai
                    </h3>
                    <p class="text-base font-black text-indigo-900">
                        <?= date('l, d F Y', strtotime($evaluasi['tanggal_mulai'])) ?>
                    </p>
                    <p class="text-2xl font-black text-indigo-600">
                        <?= date('H:i', strtotime($evaluasi['tanggal_mulai'])) ?> <span class="text-xs text-indigo-400">WIB</span>
                    </p>
                </div>
                <div>
                    <h3 class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                        <i class="far fa-calendar-times"></i> Batas Selesai
                    </h3>
                    <p class="text-base font-black text-rose-900">
                        <?= date('l, d F Y', strtotime($evaluasi['tanggal_selesai'])) ?>
                    </p>
                    <p class="text-2xl font-black text-rose-600">
                        <?= date('H:i', strtotime($evaluasi['tanggal_selesai'])) ?> <span class="text-xs text-rose-400">WIB</span>
                    </p>
                </div>
            </section>

            <!-- Instruksi -->
            <section>
                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                    <i class="fas fa-align-left text-gray-400"></i> Instruksi Pengerjaan
                </h3>
                <div class="text-gray-700 leading-relaxed text-sm prose max-w-none bg-white p-2">
                    <?= $evaluasi['instruksi'] ?: '<p class="italic text-gray-400">Tidak ada instruksi khusus untuk evaluasi ini.</p>' ?>
                </div>
            </section>

            <div class="pt-8 border-t border-gray-100 flex justify-between text-[9px] text-gray-400 font-bold uppercase tracking-widest">
                <div>Dibuat: <?= date('d M Y H:i', strtotime($evaluasi['created_at'])) ?></div>
                <div class="text-right">Status: <?= $evaluasi['status'] ?></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>