<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<?php
    // --- 1. INISIALISASI VARIABEL (ANTI ERROR & SAFETY) ---
    $session = session();
    $userRole = strtolower($session->get('role') ?? '');
    $userUnit = $session->get('kode_jenjang');

    // Pastikan variabel dari controller tersedia, jika tidak set default
    $tahun_aktif = $tahun_aktif ?? (date('Y') . '/' . (date('Y') + 1));
    $filter_jenjang = $filter_jenjang ?? $userUnit; // Default ke unit user jika filter kosong
    
    // Cek Superadmin (Fallback logic jika controller tidak mengirim $isSuperAdmin)
    if (!isset($isSuperAdmin)) {
        $isSuperAdmin = in_array($userRole, ['superadmin', 'super_admin', 'yayasan', 'root']) 
                        || in_array(strtoupper($userUnit ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT']);
    }

    // Pastikan Summary Data Aman (Mencegah error array key undefined)
    $sumIncome = $summary['penghasilan'] ?? 0;
    $sumExpense = $summary['beban'] ?? 0;
    $sumSurplus = $summary['surplus'] ?? 0;
?>

<!-- Alpine.js Data Context -->
<div class="min-h-screen bg-slate-50/50 font-sans text-slate-600 pb-20" x-data="{ 
    showModal: false, 
    isEdit: false,
    formData: { 
        id: '', 
        // Menggunakan variabel PHP yang sudah diamankan
        kode_jenjang: '<?= $isSuperAdmin ? "" : $filter_jenjang ?>', 
        id_kategori: '', 
        tahun: '<?= $tahun_aktif ?>', 
        nominal: '', 
        keterangan: '' 
    },
    openModal(data = null) {
        if(data) {
            this.isEdit = true;
            this.formData = { 
                id: data.id, 
                kode_jenjang: data.kode_jenjang || '', 
                id_kategori: data.id_kategori, 
                tahun: data.tahun, 
                nominal: parseInt(data.nominal).toLocaleString('id-ID'), 
                keterangan: data.keterangan 
            };
        } else {
            this.isEdit = false;
            this.formData = { 
                id: '', 
                kode_jenjang: '<?= $isSuperAdmin ? "" : $filter_jenjang ?>', 
                id_kategori: '', 
                tahun: '<?= $tahun_aktif ?>', 
                nominal: '', 
                keterangan: '' 
            };
        }
        this.showModal = true;
    }
}">
    
    <!-- --- HEADER SECTION --- -->
    <div class="sticky top-0 z-30 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 mb-8 transition-all duration-300">
        <div class="flex flex-col xl:flex-row justify-between items-center gap-4 max-w-screen-2xl mx-auto">
            
            <!-- Title & Context -->
            <div class="flex-1 w-full xl:w-auto text-center xl:text-left">
                <div class="flex items-center justify-center xl:justify-start gap-3">
                    <div class="p-2 bg-indigo-600 rounded-lg shadow-lg shadow-indigo-500/30 text-white">
                        <i class="fas fa-file-invoice-dollar text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">Manajemen Anggaran</h1>
                        <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                            <span class="bg-indigo-50 text-indigo-600 border border-indigo-100 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest">
                                ISAK 35
                            </span>
                            <span class="text-slate-300">|</span>
                            <span>Tahun Aktif: <?= $tahun_aktif ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Controls: Dropdown Unit & Actions -->
            <div class="flex flex-wrap justify-center xl:justify-end items-center gap-3">
                
                <!-- DROPDOWN UNIT CERDAS (ANTI BOCOR) -->
                <div class="flex items-center p-1 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <form action="" method="get" class="flex items-center gap-2 m-0 p-0 relative">
                        <div class="relative">
                            <select name="jenjang" onchange="this.form.submit()" 
                                    <?= !$isSuperAdmin ? 'disabled' : '' ?> 
                                    class="pl-9 pr-8 py-2 bg-transparent text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer disabled:text-slate-400 disabled:cursor-not-allowed transition text-slate-600">
                                <?php if ($isSuperAdmin): ?>
                                    <option value="">AGREGAT (GLOBAL)</option>
                                <?php endif; ?>
                                
                                <?php if(!empty($jenjang)): ?>
                                    <?php foreach($jenjang as $j): ?>
                                        <option value="<?= $j['kode_jenjang'] ?>" <?= ($filter_jenjang == $j['kode_jenjang']) ? 'selected' : '' ?>>
                                            Unit <?= strtoupper($j['nama_jenjang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            
                            <!-- Icon Lock/Filter -->
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fas <?= !$isSuperAdmin ? 'fa-lock' : 'fa-filter' ?> text-xs"></i>
                            </div>
                            <!-- Chevron -->
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>

                            <!-- Hidden Input untuk menjaga nilai jika disabled -->
                            <?php if(!$isSuperAdmin): ?>
                                <input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Action Buttons -->
                <a href="<?= base_url('app/keuangan/budget/target-siswa') ?>" class="h-10 px-4 bg-white border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-200 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-chart-line"></i> <span class="hidden sm:inline">Target Income</span>
                </a>
                <button @click="openModal()" class="h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-200 transition flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Input Anggaran
                </button>
            </div>
        </div>

        <!-- --- NAVIGATION TABS --- -->
        <?php if(isset($navigation)): ?>
        <div class="mt-6 overflow-x-auto pb-1 scrollbar-hide max-w-screen-2xl mx-auto">
            <div class="inline-flex p-1 bg-slate-200/60 rounded-xl border border-slate-200/60">
                <?php foreach($navigation as $key => $nav): 
                    
                    // ========================================================
                    // FIX: SEMBUNYIKAN TAB AKUNTANSI SEMENTARA WAKTU (STRATEGI)
                    // ========================================================
                    if (strtolower($key) === 'akuntansi' || strtolower($nav['label']) === 'akuntansi') {
                        continue;
                    }

                    $isActive = ($key === 'budget'); 
                    $activeClass = $isActive 
                        ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' 
                        : 'text-slate-500 hover:text-slate-700 hover:bg-white/50';
                ?>
                <a href="<?= base_url($nav['url']) ?>" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-2 <?= $activeClass ?>">
                    <i class="fas fa-<?= $nav['icon'] ?>"></i> <?= $nav['label'] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>


    <!-- MAIN CONTENT -->
    <div class="px-6 max-w-screen-2xl mx-auto space-y-8">

        <!-- KPI Cards (Dinamis mengikuti Filter Unit) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Penerimaan -->
            <div class="bg-gradient-to-br from-indigo-500 to-blue-700 p-6 rounded-3xl shadow-lg text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Target Penerimaan</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($sumIncome, 0, ',', '.') ?></h3>
                </div>
                <i class="fas fa-hand-holding-usd absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>
            
            <!-- Pengeluaran -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-6 rounded-3xl shadow-lg text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Pagu Pengeluaran</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($sumExpense, 0, ',', '.') ?></h3>
                </div>
                <i class="fas fa-file-invoice absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>

            <!-- Surplus -->
            <?php 
                $isSurplus = $sumSurplus >= 0;
                $bgClass = $isSurplus ? 'from-emerald-500 to-teal-600' : 'from-rose-500 to-pink-600';
            ?>
            <div class="bg-gradient-to-br <?= $bgClass ?> p-6 rounded-3xl shadow-lg text-white relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Estimasi Surplus/Defisit</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format(abs($sumSurplus), 0, ',', '.') ?></h3>
                    <span class="inline-block mt-2 px-2 py-0.5 bg-white/20 rounded text-[10px] font-bold uppercase backdrop-blur-sm">
                        <?= $isSurplus ? 'Surplus' : 'Defisit' ?>
                    </span>
                </div>
                <i class="fas <?= $isSurplus ? 'fa-chart-line' : 'fa-sort-amount-down' ?> absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Komposisi Anggaran</h4>
                <div class="relative h-64">
                    <canvas id="chartPieSummary"></canvas>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Distribusi per Unit</h4>
                <div class="relative h-64">
                    <canvas id="chartBarUnit"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <i class="fas fa-list text-slate-400"></i> Rincian Anggaran
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-[10px] uppercase text-slate-400 font-black border-b border-slate-100">
                            <th class="px-6 py-4 w-16 text-center">No</th>
                            <th class="px-6 py-4">Unit</th>
                            <th class="px-6 py-4">Akun Kategori</th>
                            <th class="px-6 py-4 text-center">Sifat</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs font-medium text-slate-600">
                        <?php if(empty($budgets)): ?>
                            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">Belum ada data anggaran.</td></tr>
                        <?php else: ?>
                            <?php 
                                // LOGIKA PENOMORAN (PAGINATION FIX)
                                $no = isset($nomor_urut) ? $nomor_urut + 1 : 1; 
                            ?>
                            <?php foreach($budgets as $b): ?>
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-6 py-4 text-center font-mono text-slate-400"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[10px] font-bold border border-slate-200">
                                        <?= $b['kode_jenjang'] ?: 'AGREGAT' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-700"><?= esc($b['nama_kategori']) ?></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono"><?= esc($b['kode_kategori']) ?></div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php $isIncome = in_array($b['kelompok'], ['penghasilan', 'pendapatan']); ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase <?= $isIncome ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-amber-50 text-amber-600 border border-amber-100' ?>">
                                        <?= $isIncome ? 'Penerimaan' : 'Pengeluaran' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-slate-800 tracking-tighter">
                                    Rp <?= number_format($b['nominal'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <button @click="openModal(<?= htmlspecialchars(json_encode($b)) ?>)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="<?= base_url('app/keuangan/budget/delete/'.$b['id']) ?>" onclick="return confirm('Hapus data anggaran ini?')" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/50">
                <?= $pager->links('default', 'tailwind_pagination') ?>
            </div>
        </div>

    </div>

    <!-- MODAL FORM (Alpine.js) -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                <div class="bg-white px-8 py-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-slate-800" x-text="isEdit ? 'Edit Anggaran' : 'Tambah Anggaran Baru'"></h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <form action="<?= base_url('app/keuangan/budget/save') ?>" method="post" class="space-y-5">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" x-model="formData.id">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Tahun Ajaran</label>
                                <input type="text" name="tahun" x-model="formData.tahun" required class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Unit</label>
                                <!-- ANTI BOCOR: Select Disabled jika bukan Superadmin -->
                                <select name="kode_jenjang" x-model="formData.kode_jenjang" <?= !$isSuperAdmin ? 'disabled' : '' ?> class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700 disabled:opacity-60 disabled:cursor-not-allowed">
                                    <?php if ($isSuperAdmin): ?><option value="">AGREGAT (GLOBAL)</option><?php endif; ?>
                                    <?php if(!empty($jenjang)): foreach($jenjang as $j): ?>
                                        <option value="<?= $j['kode_jenjang'] ?>"><?= $j['nama_jenjang'] ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                                <?php if (!$isSuperAdmin): ?><input type="hidden" name="kode_jenjang" x-model="formData.kode_jenjang"><?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Kategori Akun</label>
                            <select name="id_kategori" x-model="formData.id_kategori" required class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700">
                                <option value="" disabled>-- Pilih Kategori --</option>
                                <?php if(!empty($categories)): foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>">[<?= $cat['kode_kategori'] ?>] <?= strtoupper($cat['nama_kategori']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Nominal</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-600 font-black text-sm">Rp</span>
                                <input type="text" name="nominal" x-model="formData.nominal" required @input="formData.nominal = $event.target.value.replace(/[^,\d]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" class="w-full bg-slate-50 border-none rounded-xl pl-12 pr-4 py-3 text-lg font-black text-slate-800 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase">Keterangan (Opsional)</label>
                            <textarea name="keterangan" x-model="formData.keterangan" rows="2" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-indigo-500 text-slate-700"></textarea>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="showModal = false" class="flex-1 py-3 bg-white border border-slate-200 text-slate-500 rounded-xl text-sm font-bold hover:bg-slate-50 transition">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.addEventListener('load', function() {
        const summaryData = { penghasilan: <?= (float)($sumIncome) ?>, beban: <?= (float)($sumExpense) ?> };
        const unitLabels = <?= json_encode($chart_unit['labels'] ?? []) ?>;
        const unitIncome = <?= json_encode($chart_unit['income'] ?? []) ?>;
        const unitExpense = <?= json_encode($chart_unit['expense'] ?? []) ?>;

        const pieCtx = document.getElementById('chartPieSummary');
        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Penerimaan', 'Pengeluaran'],
                    datasets: [{ data: [summaryData.penghasilan, summaryData.beban], backgroundColor: ['#6366f1', '#f59e0b'], borderWidth: 0 }]
                },
                options: { maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { font: { size: 10, weight: 'bold' }, padding: 20 } } } }
            });
        }

        const barCtx = document.getElementById('chartBarUnit');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: unitLabels,
                    datasets: [
                        { label: 'Penerimaan', data: unitIncome, backgroundColor: '#6366f1', borderRadius: 6 },
                        { label: 'Pengeluaran', data: unitExpense, backgroundColor: '#f59e0b', borderRadius: 6 }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { 
                        y: { beginAtZero: true, ticks: { font: { size: 9, weight: 'bold' }, callback: (val) => val >= 1000000 ? (val/1000000).toFixed(1) + 'jt' : val.toLocaleString('id-ID') } },
                        x: { ticks: { font: { size: 9, weight: 'bold' } } }
                    }
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>