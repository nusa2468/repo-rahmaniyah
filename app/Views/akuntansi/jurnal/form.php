<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div x-data="jurnalForm()" class="px-4 sm:px-6 py-6 max-w-6xl mx-auto space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic flex items-center gap-3">
                <i class="fas fa-edit text-amber-500"></i> <?= esc($title) ?>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Catat transaksi akuntansi secara manual menggunakan metode Double-Entry.
            </p>
        </div>
        <a href="<?= base_url('app/akuntansi/jurnal') ?>" 
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 sm:py-2.5 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- ERROR VALIDATION ALERT -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-rose-500 mt-0.5"></i>
                <div class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-widest">
                    <?= session()->getFlashdata('error') ?>
                </div>
            </div>
        </div>
    <?php endif ?>

    <!-- FORM CARD -->
    <form action="<?= base_url('app/akuntansi/jurnal/save') ?>" method="post" id="jurnalFormSubmit" @submit="validateSubmit($event)">
        <?= csrf_field() ?>

        <!-- HEADER TRANSAKSI -->
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-lg border border-slate-200 dark:border-slate-800 overflow-hidden mb-6 relative">
            <div class="h-2 w-full bg-gradient-to-r from-amber-400 to-orange-500 absolute top-0 left-0"></div>
            
            <div class="p-6 md:p-8 mt-2">
                <h3 class="text-xs font-black text-amber-600 dark:text-amber-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 pb-2 mb-6">
                    <i class="fas fa-info-circle w-5"></i> Informasi Transaksi
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Unit Pemilik -->
                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Unit Jurnal <span class="text-rose-500">*</span>
                        </label>
                        <?php if ($isGlobal): ?>
                            <div class="relative">
                                <select name="kode_jenjang" required onchange="ubahUnitForm(this.value)"
                                        class="w-full pl-4 pr-10 py-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition-all text-sm font-bold text-slate-700 dark:text-slate-200 appearance-none uppercase shadow-sm">
                                    <option value="GLOBAL" <?= $filterJenjang == 'GLOBAL' ? 'selected' : '' ?>>YAYASAN / PUSAT</option>
                                    <?php foreach ($daftarUnit as $kode => $nama): ?>
                                        <option value="<?= $kode ?>" <?= $filterJenjang == $kode ? 'selected' : '' ?>>
                                            UNIT <?= strtoupper($kode) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="kode_jenjang" value="<?= esc($filterJenjang) ?>">
                            <div class="w-full px-4 py-3.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-bold text-slate-500 dark:text-slate-400 uppercase flex items-center gap-2 cursor-not-allowed">
                                <i class="fas fa-lock text-slate-400"></i> Unit <?= esc($filterJenjang) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Tanggal Transaksi <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>"
                               class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition-all text-sm font-bold text-slate-900 dark:text-white shadow-sm">
                    </div>

                    <div>
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Nomor Referensi/Bukti
                        </label>
                        <input type="text" name="referensi"
                               class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition-all text-sm font-bold text-slate-900 dark:text-white shadow-sm"
                               placeholder="Cth: INV-2026/01, KW-123">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-[10px] md:text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">
                            Deskripsi Singkat Jurnal <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="deskripsi" required
                               class="w-full px-4 py-3.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition-all text-sm font-bold text-slate-900 dark:text-white shadow-sm"
                               placeholder="Contoh: Pembayaran listrik bulan Maret 2026">
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS JURNAL (DETAIL TRANSAKSI) -->
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="p-4 md:p-6 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-list-ol text-amber-500"></i> Rincian Akun (Buku Besar)
                </h3>
                <button type="button" @click="addRow()" class="px-4 py-2 bg-emerald-100 text-emerald-700 border border-emerald-200 hover:bg-emerald-200 text-[10px] font-black uppercase tracking-widest rounded-lg transition-colors flex items-center gap-1 shadow-sm">
                    <i class="fas fa-plus"></i> Baris Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-black uppercase tracking-widest text-[10px] border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">#</th>
                            <th class="px-4 py-3 w-[30%]">Kode / Nama Akun (COA) <span class="text-rose-500">*</span></th>
                            <th class="px-4 py-3 w-[25%]">Keterangan Baris (Opsional)</th>
                            <th class="px-4 py-3 w-[20%] text-right">Debit (Rp)</th>
                            <th class="px-4 py-3 w-[20%] text-right">Kredit (Rp)</th>
                            <th class="px-4 py-3 w-12 text-center">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 group">
                                <td class="px-4 py-3 text-center text-slate-400 font-bold" x-text="index + 1"></td>
                                <td class="px-4 py-3">
                                    <div class="relative">
                                        <select x-model="row.id_coa" :name="`id_coa[]`" required class="w-full pl-3 pr-8 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg outline-none text-xs font-bold text-slate-800 dark:text-white appearance-none focus:ring-1 focus:ring-amber-500">
                                            <option value="" disabled selected>-- Pilih Akun --</option>
                                            <?php foreach ($coaList as $coa): ?>
                                                <option value="<?= $coa['id'] ?>">
                                                    [<?= esc($coa['kode_akun']) ?>] <?= esc($coa['nama_akun']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" x-model="row.keterangan" :name="`keterangan_baris[]`" class="w-full px-3 py-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 rounded-lg outline-none text-xs font-medium text-slate-800 dark:text-white focus:ring-1 focus:ring-amber-500" placeholder="Keterangan Spesifik...">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" x-model="row.debit" :name="`debit[]`" @input="formatInput(index, 'debit')" @focus="handleFocus(index, 'debit')" @blur="calculateTotal()" class="w-full px-3 py-2.5 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800 rounded-lg outline-none text-xs font-black text-emerald-700 dark:text-emerald-400 text-right focus:ring-1 focus:ring-emerald-500" placeholder="0">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" x-model="row.kredit" :name="`kredit[]`" @input="formatInput(index, 'kredit')" @focus="handleFocus(index, 'kredit')" @blur="calculateTotal()" class="w-full px-3 py-2.5 bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800 rounded-lg outline-none text-xs font-black text-rose-700 dark:text-rose-400 text-right focus:ring-1 focus:ring-rose-500" placeholder="0">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeRow(index)" class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors shadow-sm" x-show="rows.length > 2">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-slate-100 dark:bg-slate-950 border-t-2 border-slate-200 dark:border-slate-800">
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-right font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 text-xs">Total Balance (Seimbang) :</td>
                            <td class="px-4 py-4 text-right">
                                <div class="px-3 py-2 rounded-lg font-black text-sm tracking-tight" :class="isBalanced ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-rose-100 text-rose-700 border border-rose-200 animate-pulse'" x-text="'Rp ' + formatToNumberString(sumDebit)"></div>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="px-3 py-2 rounded-lg font-black text-sm tracking-tight" :class="isBalanced ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-rose-100 text-rose-700 border border-rose-200 animate-pulse'" x-text="'Rp ' + formatToNumberString(sumKredit)"></div>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- PESAN WARNING JIKA TIDAK SEIMBANG -->
            <div x-show="!isBalanced && (sumDebit > 0 || sumKredit > 0)" x-cloak class="bg-rose-50 border-t border-rose-200 p-3 text-center">
                <p class="text-xs font-bold text-rose-600 uppercase tracking-widest"><i class="fas fa-exclamation-triangle mr-1"></i> Peringatan: Total Debit & Kredit belum seimbang (Selisih: Rp <span x-text="formatToNumberString(Math.abs(sumDebit - sumKredit))"></span>).</p>
            </div>
            
            <!-- BUTTON ACTIONS (Sticky Bottom Style) -->
            <div class="bg-slate-50 dark:bg-slate-900 px-6 py-5 flex flex-col sm:flex-row justify-end items-center gap-4 border-t border-slate-200 dark:border-slate-800">
                <a href="<?= base_url('app/akuntansi/jurnal') ?>" class="w-full sm:w-auto px-6 py-3.5 sm:py-3 text-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-black rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors uppercase tracking-widest shadow-sm">
                    Batal
                </a>
                
                <button type="submit" :disabled="!isBalanced || sumDebit === 0" class="w-full sm:w-auto px-8 py-3.5 sm:py-3 justify-center bg-amber-500 hover:bg-amber-600 text-white text-xs font-black rounded-xl shadow-lg shadow-amber-500/30 transition-all active:scale-95 flex items-center gap-2 uppercase tracking-widest border-b-4 border-amber-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:active:scale-100">
                    <i class="fas fa-save"></i> Posting Jurnal
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('jurnalForm', () => ({
            rows: [
                { id: 1, id_coa: '', keterangan: '', debit: '', kredit: '' },
                { id: 2, id_coa: '', keterangan: '', debit: '', kredit: '' }
            ],
            nextId: 3,
            sumDebit: 0,
            sumKredit: 0,

            get isBalanced() {
                return this.sumDebit === this.sumKredit && this.sumDebit > 0;
            },

            addRow() {
                this.rows.push({ id: this.nextId++, id_coa: '', keterangan: '', debit: '', kredit: '' });
            },

            removeRow(index) {
                if (this.rows.length > 2) {
                    this.rows.splice(index, 1);
                    this.calculateTotal();
                }
            },

            handleFocus(index, type) {
                // Hapus angka 0 saat difokus agar gampang ngetik
                if (this.rows[index][type] === '0') {
                    this.rows[index][type] = '';
                }
                // Jika isi debit, reset kredit, dan sebaliknya (mencegah 1 baris diisi D & K bersamaan)
                if (type === 'debit') this.rows[index]['kredit'] = '';
                if (type === 'kredit') this.rows[index]['debit'] = '';
            },

            formatInput(index, type) {
                let val = this.rows[index][type].replace(/[^0-9]/g, '');
                if (val) {
                    this.rows[index][type] = this.formatToNumberString(parseInt(val));
                } else {
                    this.rows[index][type] = '';
                }
                this.calculateTotal();
            },

            formatToNumberString(angka) {
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },

            calculateTotal() {
                let totalD = 0;
                let totalK = 0;

                this.rows.forEach(row => {
                    let d = parseInt(row.debit.replace(/[^0-9]/g, '')) || 0;
                    let k = parseInt(row.kredit.replace(/[^0-9]/g, '')) || 0;
                    totalD += d;
                    totalK += k;
                });

                this.sumDebit = totalD;
                this.sumKredit = totalK;
            },

            validateSubmit(e) {
                this.calculateTotal();
                if (!this.isBalanced || this.sumDebit === 0) {
                    e.preventDefault();
                    alert("Total Debit dan Kredit tidak seimbang atau masih nol! Silakan periksa kembali nominalnya.");
                }
            }
        }))
    });

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