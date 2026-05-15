<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8 no-print">
        <a href="<?= base_url('app/pembelajaran/bank-soal') ?>" class="text-gray-500 font-bold flex items-center gap-2 transition-colors hover:text-indigo-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
        <div class="flex gap-2">
            <a href="<?= base_url('app/pembelajaran/bank-soal/edit/' . $soal['id']) ?>" class="bg-yellow-500 text-white px-4 py-2 rounded-xl font-bold shadow-sm">Edit</a>
            <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-xl font-bold shadow-sm">Pratinjau Cetak</button>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
        <div class="p-10 border-b border-gray-50 bg-gray-50/30">
            <div class="flex justify-between items-start mb-4">
                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-black rounded-full uppercase tracking-widest">
                    Unit <?= $soal['kode_jenjang'] ?> | <?= $soal['jenis_soal'] ?>
                </span>
                <span class="text-xs font-black text-gray-400 uppercase tracking-widest">KOD: <?= $soal['kode_soal'] ?></span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 leading-tight mb-2">Topik: <?= esc($soal['topik']) ?></h1>
            <p class="text-gray-400 font-bold uppercase text-[10px]">Materi: <?= esc($soal['materi_pokok'] ?? 'N/A') ?></p>
        </div>

        <div class="p-10 space-y-10">
            <!-- Soal -->
            <section>
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 border-l-4 border-indigo-500 pl-3">Pertanyaan</h3>
                <div class="text-gray-800 text-lg leading-relaxed">
                    <?= $soal['pertanyaan'] ?>
                </div>
            </section>

            <!-- Opsyen jika PG -->
            <?php if ($soal['jenis_soal'] == 'PG'): ?>
                <section class="grid grid-cols-1 gap-4">
                    <?php 
                        $opsi = json_decode($soal['opsi_jawaban'] ?? '{}', true);
                        foreach($opsi as $key => $val): if(empty($val)) continue;
                    ?>
                        <div class="flex items-center gap-4 p-4 rounded-2xl border-2 border-gray-50 hover:border-indigo-100 transition-all">
                            <span class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-xl font-black uppercase"><?= $key ?></span>
                            <span class="text-gray-700 font-bold"><?= esc($val) ?></span>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <!-- Kunci Jawaban -->
            <section class="bg-emerald-50 p-6 rounded-2xl border border-emerald-100">
                <h3 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2">Kunci Jawaban Sah</h3>
                <p class="text-2xl font-black text-emerald-700 uppercase"><?= esc($soal['kunci_jawaban']) ?></p>
            </section>
        </div>
    </div>
</div>
<?= $this->endSection() ?>