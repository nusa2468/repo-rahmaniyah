<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8 no-print">
        <a href="<?= base_url('app/pembelajaran/bahan-ajar') ?>" class="text-gray-500 font-bold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
        <div class="flex gap-2">
            <a href="<?= base_url('app/pembelajaran/bahan-ajar/edit/' . $bahan['id']) ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-bold shadow-sm transition-colors">Edit</a>
            <button onclick="window.print()" class="bg-gray-800 hover:bg-black text-white px-4 py-2 rounded-lg font-bold shadow-sm transition-colors">Cetak</button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-10 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-black rounded-full uppercase tracking-widest mb-4 inline-block">
                Unit <?= esc($bahan['kode_jenjang']) ?> | <?= esc($bahan['jenis_file']) ?>
            </span>
            <h1 class="text-4xl font-black text-gray-900 dark:text-white leading-tight mb-2"><?= esc($bahan['judul_bahan']) ?></h1>
            <p class="text-gray-400 font-bold uppercase text-xs">Mata Pelajaran: <?= esc($bahan['nama_mapel'] ?? 'N/A') ?></p>
        </div>

        <div class="p-10 space-y-8">
            <section>
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3 border-b pb-1">Deskripsi & Instruksi</h3>
                <div class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm">
                    <?php 
                        // FIXED: Gunakan isset atau ?? untuk menghindari Undefined Array Key
                        $deskripsi = $bahan['deskripsi'] ?? null;
                        echo $deskripsi ? $deskripsi : '<p class="italic text-gray-400">Tidak ada deskripsi tambahan.</p>'; 
                    ?>
                </div>
            </section>

            <section class="bg-indigo-50 dark:bg-indigo-900/20 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-900/30">
                <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-4">Akses Materi Pembelajaran</h3>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm font-bold text-gray-600 dark:text-gray-400">
                        Lokasi: <span class="text-indigo-800 dark:text-indigo-300 italic truncate max-w-xs inline-block align-middle"><?= esc($bahan['file_path']) ?></span>
                    </div>
                    <a href="<?= esc($bahan['file_path']) ?>" target="_blank" class="w-full sm:w-auto bg-indigo-600 hover:bg-black text-white px-8 py-3 rounded-xl font-black text-center transition-all shadow-lg shadow-indigo-200 dark:shadow-none">
                        BUKA / DOWNLOAD MATERI
                    </a>
                </div>
            </section>

            <div class="pt-8 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 text-[10px] text-gray-400 font-bold uppercase">
                <div>Dibuat Pada: <?= esc($bahan['created_at']) ?></div>
                <div class="text-right">ID RPP: <?= esc($bahan['rpp_id'] ?? '-') ?></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>