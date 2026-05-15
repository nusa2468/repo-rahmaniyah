<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div x-data="{ deleteModalOpen: false, deleteUrl: '' }">
    
    <!-- 1. FITUR NAVIGASI (BREADCRUMB) -->
    <nav class="flex text-sm text-slate-500 mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
            <li class="inline-flex items-center">
                <a href="<?= base_url('app/masterdata/dashboard') ?>" class="inline-flex items-center hover:text-indigo-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/></svg>
                    Master Data
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 mx-1 text-slate-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                    <span class="ml-1 font-medium text-slate-800 dark:text-white md:ml-2">Jurusan</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- 2. Header & Tombol (Perfect Alignment Fix) -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">Manajemen Jurusan</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <p class="text-sm text-gray-500 dark:text-gray-400">Daftar Program Studi / Peminatan</p>
                <!-- Indikator Role & Unit -->
                <?php if(in_array($role, ['superadmin', 'yayasan'])): ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide">
                        Global View
                    </span>
                <?php else: ?>
                    <span class="ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">
                        Unit: <?= esc($jenjang) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 h-10">
            
            <!-- DROPDOWN FILTER UNIT (Khusus Superadmin/Yayasan) -->
            <?php if (in_array($role, ['superadmin', 'yayasan']) && !empty($listJenjang)): ?>
                <form action="" method="get" class="h-full flex">
                    <!-- Container h-full untuk menyesuaikan tinggi parent h-10 -->
                    <div class="relative h-full w-full md:w-48 group"> 
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <i class="fas fa-filter text-gray-400 text-xs group-hover:text-indigo-500 transition-colors"></i>
                        </div>
                        
                        <select name="kode_jenjang" onchange="this.form.submit()" 
                                class="h-full w-full pl-9 pr-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 text-xs font-bold uppercase tracking-wide rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none cursor-pointer hover:bg-gray-50 transition-colors appearance-none flex items-center">
                            <option value="">Semua Unit</option>
                            <?php foreach ($listJenjang as $j): ?>
                                <?php 
                                    $val = is_array($j) ? ($j['kode_jenjang'] ?? '-') : ($j->kode_jenjang ?? '-');
                                    $lbl = is_array($j) ? ($j['nama_jenjang'] ?? 'Unit ' . $val) : ($j->nama_jenjang ?? 'Unit ' . $val);
                                    $sel = ($filter_jenjang === $val) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($val) ?>" <?= $sel ?>>
                                    <?= esc($lbl) ?> (<?= esc($val) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Tombol Tambah (h-full mengikuti parent h-10) -->
            <a href="<?= base_url('app/masterdata/jurusan/form') ?>" 
               class="h-full inline-flex items-center justify-center gap-2 px-5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all transform active:scale-95 border border-transparent box-border">
                <i class="fas fa-plus"></i>
                <span>Tambah Jurusan</span>
            </a>
        </div>
    </div>

    <!-- 3. Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-xl shadow-sm flex items-center gap-3 animate-fade-in-down">
            <i class="fas fa-check-circle text-xl"></i>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider">Berhasil</p>
                <p class="text-sm font-medium"><?= session()->getFlashdata('success') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-xl shadow-sm flex items-center gap-3 animate-pulse">
            <i class="fas fa-exclamation-triangle text-xl"></i>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider">Perhatian</p>
                <p class="text-sm font-medium"><?= session()->getFlashdata('error') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- 4. Table Card -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs font-bold border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-center w-16">#</th>
                        <th class="px-6 py-4">Identitas Jurusan</th>
                        <th class="px-6 py-4 text-center w-32">Jenjang</th>
                        <th class="px-6 py-4 text-center w-32">Status</th>
                        <th class="px-6 py-4 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php if (empty($jurusan)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-folder-open text-4xl opacity-30"></i>
                                    <p class="font-medium">Belum ada data jurusan yang tersedia.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jurusan as $key => $row): ?>
                            <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors duration-150">
                                <td class="px-6 py-4 text-center font-bold text-gray-500">
                                    <?= $key + 1 ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-base font-bold text-gray-800 dark:text-white">
                                            <?= esc($row['nama_jurusan']) ?>
                                        </span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-black border border-gray-200 dark:border-gray-600">
                                                KODE: <?= esc($row['kode_jurusan']) ?>
                                            </span>
                                            <?php if(!empty($row['keterangan'])): ?>
                                                <span class="text-xs text-gray-400 truncate max-w-xs" title="<?= esc($row['keterangan']) ?>">
                                                    • <?= esc($row['keterangan']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-black bg-indigo-50 text-indigo-600 border border-indigo-100">
                                        <?= esc($row['kode_jenjang']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ($row['status'] === 'Aktif' || $row['status'] == 1): ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black bg-gray-100 text-gray-600 border border-gray-200 uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Non-Aktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= base_url('app/masterdata/jurusan/form/' . $row['id']) ?>" 
                                           class="w-9 h-9 flex items-center justify-center rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all shadow-sm"
                                           title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        
                                        <button @click="deleteModalOpen = true; deleteUrl = '<?= base_url('app/masterdata/jurusan/delete/' . $row['id']) ?>'"
                                                class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm"
                                                title="Hapus">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 text-xs text-gray-500 font-bold uppercase tracking-wide flex justify-between">
            <span>Total Data: <?= count($jurusan) ?></span>
            <span>Master Data Jurusan</span>
        </div>
    </div>

    <!-- Modal Hapus (Alpine.js) -->
    <div x-show="deleteModalOpen" 
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm border border-gray-100 dark:border-gray-700 overflow-hidden transform transition-all"
             @click.away="deleteModalOpen = false">
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-rose-100 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                    <i class="fas fa-trash-alt text-3xl"></i>
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white mb-2">Hapus Data Jurusan?</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
                    Data yang dihapus tidak dapat dikembalikan. Pastikan data ini tidak digunakan di modul lain.
                </p>
                <div class="flex gap-3 justify-center">
                    <button @click="deleteModalOpen = false" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-colors">Batal</button>
                    <a :href="deleteUrl" class="px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-rose-500/30 transition-all">Ya, Hapus</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>