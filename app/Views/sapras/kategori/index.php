<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-6 space-y-6">

    <!-- HEADER SECTION -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/sapras/dashboard') ?>" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">SAPRAS</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600 dark:text-slate-300">KATEGORI ASET</li>
                </ol>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic">
                    <?= esc($title) ?>
                </h1>
                
                <!-- BADGE UNIT AKTIF -->
                <?php if($isGlobal && $filterJenjang === 'GLOBAL'): ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 uppercase tracking-wide">
                        Global View
                    </span>
                <?php else: ?>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase tracking-wide">
                        Unit <?= esc($filterJenjang) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Kelola master klasifikasi dan jenis barang inventaris sekolah.
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            
            <!-- DROPDOWN FILTER UNIT (Khusus Superadmin/Global) -->
            <?php if ($isGlobal): ?>
                <form action="" method="get" class="relative group z-50">
                    <div class="relative">
                        <select name="jenjang" onchange="this.form.submit()" 
                                class="pl-10 pr-10 py-2.5 text-[11px] font-black bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-xl appearance-none cursor-pointer focus:border-indigo-500 shadow-sm uppercase tracking-wider min-w-[160px] transition-all outline-none text-slate-700 dark:text-slate-200 hover:border-indigo-300">
                            <option value="GLOBAL" <?= ($filterJenjang === 'GLOBAL') ? 'selected' : '' ?>>SEMUA UNIT</option>
                            <?php foreach ($daftarUnit as $kode => $nama): ?>
                                <option value="<?= $kode ?>" <?= ($filterJenjang == $kode) ? 'selected' : '' ?>>
                                    UNIT <?= strtoupper($kode) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 dark:text-slate-500 text-[10px] pointer-events-none"></i>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 dark:text-slate-500 text-[9px] pointer-events-none"></i>
                    </div>
                </form>
            <?php endif; ?>

            <a href="<?= base_url('app/sapras/kategori/new') ?>" 
               class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-[11px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-0.5 active:scale-95 whitespace-nowrap border-b-4 border-indigo-800">
                <i class="fas fa-plus text-xs"></i> <span>Tambah Kategori</span>
            </a>
        </div>
    </div>

    <!-- ALERT HANDLER -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center justify-between animate-fade-in-down">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-tight"><?= session()->getFlashdata('success') ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif ?>
    
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 p-4 shadow-sm flex items-center justify-between animate-fade-in-down">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm font-bold text-rose-800 dark:text-rose-300 uppercase tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif ?>

    <!-- MAIN TABLE CARD -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-lg overflow-hidden flex flex-col relative z-0">
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-extrabold uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="px-6 py-4 w-10 text-center">No</th>
                        <th class="px-6 py-4">Kode Kategori</th>
                        <th class="px-6 py-4">Nama Klasifikasi</th>
                        <th class="px-6 py-4">Tipe Aset</th>
                        <th class="px-6 py-4 text-center">Unit Milik</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php 
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $startNo = ($page - 1) * 10 + 1;
                    if(empty($kategori)): ?>
                         <tr>
                             <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50/30 dark:bg-gray-800/20">
                                 <i class="fas fa-folder-open text-3xl mb-3 opacity-50 block"></i>
                                 Belum ada data kategori aset.
                             </td>
                         </tr>
                    <?php else:
                        foreach ($kategori as $row): 
                            // Tentukan Badge Tipe Aset
                            $bgBadge = 'bg-slate-100 text-slate-700';
                            switch($row['tipe_aset']) {
                                case 'Bangunan/Tanah': $bgBadge = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'; break;
                                case 'Elektronik':     $bgBadge = 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'; break;
                                case 'Furniture':      $bgBadge = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'; break;
                                case 'Kendaraan':      $bgBadge = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'; break;
                            }
                    ?>
                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $startNo++ ?></td>
                            <td class="px-6 py-4">
                                <span class="font-mono font-black text-indigo-600 dark:text-indigo-400 tracking-wider bg-indigo-50 dark:bg-indigo-900/20 px-2 py-1 rounded">
                                    <?= esc($row['kode_kategori']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-white"><?= esc($row['nama_kategori']) ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-wider <?= $bgBadge ?>">
                                    <?= esc($row['tipe_aset']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-black text-slate-500 dark:text-slate-400">
                                <?= strtoupper(esc($row['kode_jenjang'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php 
                                    // Pengecekan Otoritas Edit/Delete
                                    $canEdit = $isGlobal || strtoupper($row['kode_jenjang']) === $sessionJenjang;
                                ?>
                                <?php if($canEdit): ?>
                                    <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <a href="<?= base_url('app/sapras/kategori/edit/' . $row['id']) ?>" 
                                           class="w-8 h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-amber-500 hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg shadow-sm transition-all" 
                                           title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?= $row['id'] ?>, '<?= esc($row['nama_kategori']) ?>')" 
                                                class="w-8 h-8 inline-flex items-center justify-center bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-rose-500 hover:border-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg shadow-sm transition-all" 
                                                title="Hapus Permanen">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <i class="fas fa-lock text-slate-300 dark:text-slate-600" title="Terkunci (Milik Unit Lain)"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div class="border-t border-slate-200 dark:border-slate-800 px-6 py-4 bg-slate-50/50 dark:bg-slate-900/50 flex justify-center">
            <?= $pager->links('default', 'tailwind_pagination') ?>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-t-8 border-rose-600">
            <div class="bg-white dark:bg-slate-800 px-6 pt-6 pb-4 sm:p-8">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 dark:bg-rose-900/30 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-trash-alt text-rose-600 dark:text-rose-400"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-black text-slate-900 dark:text-white uppercase italic tracking-tight">Hapus Kategori?</h3>
                        <div class="mt-2">
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                                Anda akan menghapus permanen kategori: <strong id="deleteTargetName" class="text-slate-800 dark:text-slate-200 underline"></strong>.<br>
                                Peringatan: Proses ini akan digagalkan oleh sistem jika masih ada barang yang menggunakan kategori ini.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-2">
                <a id="btnConfirmDelete" href="#" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:w-auto transition-all">
                    Ya, Hapus
                </a>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm px-4 py-2 bg-white dark:bg-slate-800 text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none sm:mt-0 sm:w-auto transition-all">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, nama) {
        const modal = document.getElementById('deleteModal');
        const nameSpan = document.getElementById('deleteTargetName');
        const btnConfirm = document.getElementById('btnConfirmDelete');
        
        nameSpan.textContent = nama;
        btnConfirm.href = '<?= base_url('app/sapras/kategori/delete/') ?>/' + id;
        
        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>

<?= $this->endSection() ?>