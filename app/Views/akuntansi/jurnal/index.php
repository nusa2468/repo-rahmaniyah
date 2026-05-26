<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div x-data="jurnalManager()" class="px-4 sm:px-6 py-6 max-w-7xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/akuntansi') ?>" class="hover:text-amber-500 transition-colors">AKUNTANSI</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600 dark:text-slate-300">JURNAL UMUM</li>
                </ol>
            </nav>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic flex items-center gap-3">
                <i class="fas fa-book text-amber-500"></i> <?= esc($title) ?>
            </h1>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <?php if ($isGlobal): ?>
                <form action="" method="get" class="w-full sm:w-auto relative">
                    <select name="jenjang" onchange="this.form.submit()" class="w-full pl-4 pr-10 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition-all text-xs font-black uppercase text-slate-700 dark:text-slate-200 shadow-sm cursor-pointer">
                        <option value="MULTI" <?= $filterJenjang === 'MULTI' ? 'selected' : '' ?> class="text-indigo-600 font-black">📊 KONSOLIDASI (SEMUA UNIT)</option>
                        <option value="GLOBAL" <?= $filterJenjang === 'GLOBAL' ? 'selected' : '' ?>>🏢 PUSAT (YAYASAN)</option>
                        <?php foreach ($daftarUnit as $kode => $nama): ?>
                            <option value="<?= $kode ?>" <?= $filterJenjang === $kode ? 'selected' : '' ?>>🏫 UNIT <?= strtoupper($kode) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                </form>
            <?php else: ?>
                <div class="px-4 py-3 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800 rounded-xl text-xs font-black uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-lock"></i> Unit <?= esc($filterJenjang) ?>
                </div>
            <?php endif; ?>

            <a href="<?= base_url('app/akuntansi/jurnal/new' . ($isGlobal && $filterJenjang !== 'GLOBAL' ? '?jenjang='.$filterJenjang : '')) ?>" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-[11px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-amber-500/30 transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap border-b-4 border-orange-700">
                <i class="fas fa-plus"></i> Entri Jurnal
            </a>
        </div>
    </div>

    <!-- ALERTS -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-tight"><?= session()->getFlashdata('success') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- MAIN TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden relative">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-black uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="px-6 py-4 w-10 text-center">No</th>
                        <th class="px-6 py-4">Tanggal & Nomor</th>
                        <th class="px-6 py-4">Keterangan Jurnal</th>
                        <th class="px-6 py-4 text-center">Unit</th>
                        <th class="px-6 py-4 text-right">Nilai Transaksi (Rp)</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($jurnal)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400 dark:text-slate-500 italic bg-slate-50/50 dark:bg-slate-800/30 border-dashed border-2 border-slate-200 dark:border-slate-700 m-4 rounded-xl">
                                <i class="fas fa-book-open text-4xl mb-3 block opacity-50"></i>
                                Belum ada transaksi jurnal tercatat.
                            </td>
                        </tr>
                    <?php else: $no = 1; foreach ($jurnal as $j): ?>
                        <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-900/10 transition-colors group">
                            <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-800 dark:text-white mb-1"><?= date('d M Y', strtotime($j['tanggal'])) ?></div>
                                <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                    <i class="fas fa-hashtag mr-1 text-amber-500"></i><?= esc($j['nomor_jurnal']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700 dark:text-slate-200 truncate max-w-xs" title="<?= esc($j['deskripsi']) ?>">
                                    <?= esc($j['deskripsi']) ?>
                                </div>
                                <?php if($j['referensi']): ?>
                                    <div class="text-[10px] text-slate-400 font-medium mt-1">Ref: <?= esc($j['referensi']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-[10px] font-black uppercase text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded border border-amber-200 dark:border-amber-800/50">
                                    <?= esc($j['kode_jenjang']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-black text-emerald-600 dark:text-emerald-400 italic tracking-tight">
                                    <?= number_format($j['total_debit'], 0, ',', '.') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800">
                                    <i class="fas fa-check-circle mr-1"></i><?= esc($j['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button @click="openDetail(<?= $j['id'] ?>)" class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:border-blue-300 transition-all shadow-sm">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DETAIL JURNAL (ALPINE JS) -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" @click="modalOpen = false"></div>
            
            <div class="inline-block bg-white dark:bg-slate-900 rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-3xl w-full border border-slate-200 dark:border-slate-700 relative">
                
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-6 flex justify-between items-start relative overflow-hidden">
                    <i class="fas fa-book-open absolute -right-4 -bottom-4 text-white/10 text-8xl transform -rotate-12"></i>
                    <div class="relative z-10 text-white">
                        <div class="text-[10px] font-black uppercase tracking-widest text-amber-100 mb-1" x-text="jurnal.kode_jenjang"></div>
                        <h3 class="text-2xl font-black tracking-tight leading-tight mb-2">Detail Jurnal Umum</h3>
                        <div class="inline-flex items-center gap-2 font-mono text-xs bg-black/20 px-3 py-1.5 rounded-lg border border-white/20">
                            <i class="fas fa-hashtag text-amber-200"></i>
                            <span x-text="jurnal.nomor_jurnal" class="font-bold"></span>
                        </div>
                    </div>
                    <button @click="modalOpen = false" class="relative z-10 text-white/70 hover:text-white bg-black/20 hover:bg-black/40 w-8 h-8 rounded-full transition-colors flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6 md:p-8">
                    <div class="flex flex-wrap gap-6 mb-6">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="formatDate(jurnal.tanggal)"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Referensi</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white" x-text="jurnal.referensi || '-'"></p>
                        </div>
                        <div class="w-full">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Keterangan / Uraian</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-white bg-slate-50 dark:bg-slate-800 p-3 rounded-xl border border-slate-100 dark:border-slate-700" x-text="jurnal.deskripsi"></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-xl">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-black uppercase tracking-widest text-[10px]">
                                <tr>
                                    <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">Kode Akun</th>
                                    <th class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">Nama Akun & Keterangan</th>
                                    <th class="px-4 py-3 text-right border-b border-slate-200 dark:border-slate-700">Debit (Rp)</th>
                                    <th class="px-4 py-3 text-right border-b border-slate-200 dark:border-slate-700">Kredit (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template x-for="row in details" :key="row.id">
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-4 py-3 font-mono font-bold text-slate-600 dark:text-slate-300" x-text="row.kode_akun"></td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-slate-800 dark:text-white" :class="parseFloat(row.kredit) > 0 ? 'ml-6' : ''" x-text="row.nama_akun"></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5" :class="parseFloat(row.kredit) > 0 ? 'ml-6' : ''" x-text="row.keterangan || ''"></div>
                                        </td>
                                        <td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400" x-text="parseFloat(row.debit) > 0 ? formatRupiah(row.debit) : '-'"></td>
                                        <td class="px-4 py-3 text-right font-black text-rose-500 dark:text-rose-400" x-text="parseFloat(row.kredit) > 0 ? formatRupiah(row.kredit) : '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-slate-50 dark:bg-slate-800 font-black">
                                <tr>
                                    <td colspan="2" class="px-4 py-3 text-right uppercase tracking-widest text-slate-600 dark:text-slate-300 text-xs">Total Transaksi</td>
                                    <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400" x-text="formatRupiah(jurnal.total_debit)"></td>
                                    <td class="px-4 py-3 text-right text-rose-500 dark:text-rose-400" x-text="formatRupiah(jurnal.total_kredit)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('jurnalManager', () => ({
            modalOpen: false,
            jurnal: {},
            details: [],

            async openDetail(id) {
                // Fetch data via AJAX
                try {
                    const response = await fetch(`<?= base_url('app/akuntansi/jurnal/detail') ?>/${id}`);
                    const data = await response.json();
                    if (data.status) {
                        this.jurnal = data.jurnal;
                        this.details = data.detail;
                        this.modalOpen = true;
                    }
                } catch (error) {
                    alert('Gagal memuat detail jurnal.');
                }
            },

            formatRupiah(angka) {
                if (!angka) return '0';
                return parseFloat(angka).toLocaleString('id-ID');
            },

            formatDate(dateStr) {
                if(!dateStr) return '-';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateStr).toLocaleDateString('id-ID', options);
            }
        }))
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<?= $this->endSection() ?>