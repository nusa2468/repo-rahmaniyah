<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800 uppercase italic"><?= esc($title) ?></h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li class="inline-flex items-center uppercase italic">Sapras</li>
                    <li aria-current="page"><div class="flex items-center text-indigo-600"><i class="fas fa-chevron-right mx-2 text-[8px]"></i> <span class="uppercase italic underline decoration-2">Gedung</span></div></li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= base_url('app/sapras/gedung/new') ?>" class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-indigo-600 text-white hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 active:scale-95 border-b-4 border-indigo-800"><i class="fas fa-plus mr-2 text-[10px]"></i> Tambah Gedung</a>
        </div>
    </div>

    <!-- NAVIGASI TAB SAPRAS -->
    <div class="border-b border-slate-200 mb-8 overflow-x-auto">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="<?= base_url('app/sapras/tanah') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all"><i class="fas fa-map-marked-alt mr-2"></i> Tanah</a>
            <a href="<?= base_url('app/sapras/gedung') ?>" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest"><i class="fas fa-building mr-2"></i> Gedung</a>
            <a href="<?= base_url('app/sapras/ruangan') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all"><i class="fas fa-door-open mr-2"></i> Ruangan</a>
            <a href="<?= base_url('app/sapras/peralatan') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all"><i class="fas fa-tools mr-2"></i> Peralatan</a>
            <a href="<?= base_url('app/sapras/inventaris') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all"><i class="fas fa-boxes mr-2"></i> Inventaris</a>
        </nav>
    </div>

    <?php
        $sessUnit = session('kode_jenjang');
        $amIUnitAdmin = isset($isUnitAdmin) ? $isUnitAdmin : false;
        $currentFilter = isset($filterJenjang) ? $filterJenjang : '';
        $listUnit = isset($daftarUnit) ? $daftarUnit : [];
    ?>

    <!-- Info Bar & Filter -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="md:col-span-2 bg-slate-800 p-5 border-b-4 <?= $amIUnitAdmin ? 'border-indigo-500' : 'border-emerald-500' ?> shadow-lg flex flex-col sm:flex-row items-center justify-between relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity"><i class="fas <?= $amIUnitAdmin ? 'fa-building-lock' : 'fa-globe' ?> text-9xl text-white transform rotate-12"></i></div>
            <div class="flex items-center gap-5 z-10 w-full">
                <div class="w-12 h-12 <?= $amIUnitAdmin ? 'bg-indigo-500' : 'bg-emerald-500' ?> flex items-center justify-center text-white shadow-lg rounded-sm"><i class="fas <?= $amIUnitAdmin ? 'fa-lock' : 'fa-globe-asia' ?> text-xl"></i></div>
                <div class="flex-1">
                    <p class="text-[10px] font-black <?= $amIUnitAdmin ? 'text-indigo-300' : 'text-emerald-300' ?> uppercase tracking-widest leading-none mb-1"><?= $amIUnitAdmin ? 'MODE AKSES TERBATAS' : 'MODE SUPERADMIN / YAYASAN' ?></p>
                    <h3 class="text-xl font-black text-white uppercase italic leading-none tracking-tight"><?= $amIUnitAdmin ? 'UNIT: ' . esc($sessUnit) : 'AKSES SELURUH UNIT' ?></h3>
                </div>
                <?php if (!$amIUnitAdmin) : ?>
                    <form action="<?= current_url() ?>" method="get" class="z-20 w-full sm:w-auto mt-3 sm:mt-0">
                        <div class="flex items-center bg-slate-700/50 p-1 rounded-sm border border-slate-600">
                            <select name="jenjang" onchange="this.form.submit()" class="bg-transparent text-white text-xs font-bold uppercase tracking-wide border-none focus:ring-0 cursor-pointer w-full sm:w-40 appearance-none pl-3 pr-8">
                                <option value="" <?= empty($currentFilter) ? 'selected' : '' ?>>- SEMUA UNIT -</option>
                                <?php foreach ($listUnit as $kode => $label) : ?>
                                    <option value="<?= esc($kode) ?>" <?= $currentFilter == $kode ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute right-3 pointer-events-none text-slate-400"><i class="fas fa-filter text-xs"></i></div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="bg-sky-600 p-5 border-b-4 border-sky-800 shadow-lg flex items-center justify-between relative overflow-hidden">
            <div class="absolute -bottom-4 -right-4 text-sky-500 opacity-30"><i class="fas fa-city text-8xl"></i></div>
            <div class="z-10">
                <p class="text-[10px] font-black text-sky-200 uppercase tracking-widest leading-none mb-1">Total Gedung</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-3xl font-black text-white"><?= count($gedung) ?></h3>
                    <span class="text-[10px] text-sky-100 font-bold uppercase italic">Bangunan</span>
                </div>
            </div>
            <div class="z-10 bg-sky-500/30 p-2 rounded-sm backdrop-blur-sm"><i class="fas fa-chart-bar text-white text-xl"></i></div>
        </div>
    </div>

    <!-- Tabel Gedung -->
    <div class="bg-white border-t-4 border-slate-800 shadow-2xl overflow-hidden mb-6">
        <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-table text-slate-400 text-xs"></i>
                <h2 class="text-[11px] font-black text-slate-700 uppercase tracking-widest italic">Data Gedung / Bangunan</h2>
            </div>
            <?php if(!empty($currentFilter)): ?>
                <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-[9px] font-bold uppercase rounded-sm border border-indigo-200">Filter Aktif: <?= esc($currentFilter) ?></span>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-slate-200 uppercase">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 tracking-widest border-r border-slate-200 w-16 text-center italic">#</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 tracking-widest border-r border-slate-200">Nama Gedung</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 tracking-widest border-r border-slate-200 text-center">Tahun</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 tracking-widest border-r border-slate-200 text-right">Luas</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 tracking-widest border-r border-slate-200">Keterangan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-500 tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (empty($gedung)) : ?>
                        <tr><td colspan="6" class="px-6 py-20 text-center bg-slate-50/50"><p class="text-xs font-black text-slate-400 uppercase italic">Data Kosong</p></td></tr>
                    <?php else : ?>
                        <?php 
                        $page = $pager->getCurrentPage('default') ?: 1; $perPage = $pager->getPerPage('default') ?: 10; $no = 1 + ($page - 1) * $perPage; 
                        foreach ($gedung as $g) : ?>
                            <tr class="hover:bg-indigo-50/50 transition-colors group border-b border-slate-50">
                                <td class="px-6 py-4 border-r border-slate-50 text-[11px] font-black text-slate-400 text-center italic"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4 border-r border-slate-50">
                                    <div class="text-[11px] font-black text-slate-800 uppercase italic leading-none mb-1"><?= esc($g['nama']) ?></div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-sky-100 text-sky-700"><?= esc($g['kode_jenjang'] ?? 'GLOBAL') ?></span>
                                </td>
                                <td class="px-6 py-4 border-r border-slate-50 text-center"><?= esc($g['tahun'] ?: '-') ?></td>
                                <td class="px-6 py-4 border-r border-slate-50 text-right"><?= number_format($g['luas'], 2, ',', '.') ?> m²</td>
                                <td class="px-6 py-4 border-r border-slate-50 max-w-[250px]"><p class="text-[10px] font-medium text-slate-500 truncate italic"><?= esc($g['keterangan']) ?></p></td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= base_url('app/sapras/gedung/edit/' . $g['id']) ?>" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-amber-500 hover:bg-amber-500 hover:text-white shadow-sm rounded-sm"><i class="fas fa-pencil-alt text-[10px]"></i></a>
                                        <button onclick="confirmDelete('<?= $g['id'] ?>', '<?= esc($g['nama']) ?>')" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-500 hover:text-white shadow-sm rounded-sm"><i class="fas fa-trash-alt text-[10px]"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($gedung)) : ?><div class="bg-slate-50 px-6 py-4 border-t border-slate-200"><?= $pager->links('default', 'tailwind_pagination') ?></div><?php endif; ?>
    </div>
</div>
<!-- Modal Delete reused script -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 font-sans">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-none text-left shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-t-8 border-rose-600">
            <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 bg-rose-50 border-2 border-rose-100 text-rose-600"><i class="fas fa-trash-alt text-sm"></i></div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest italic">Konfirmasi Hapus</h3>
                        <div class="mt-3 bg-slate-50 p-4 border-l-4 border-slate-300"><p class="text-[11px] font-bold text-slate-500 uppercase leading-relaxed">Hapus gedung: <span id="modal-delete-info" class="block text-slate-900 mt-1 font-black underline italic"></span></p></div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-2">
                <a id="deleteBtn" href="#" class="w-full inline-flex justify-center px-6 py-2 bg-rose-600 text-[10px] font-black text-white uppercase tracking-widest hover:bg-rose-700 shadow-lg border-b-4 border-rose-800 transition-all">Hapus</a>
                <button type="button" onclick="closeModal()" class="mt-3 sm:mt-0 w-full inline-flex justify-center px-6 py-2 bg-white border-2 border-slate-200 text-[10px] font-black text-slate-700 uppercase tracking-widest hover:bg-slate-50 transition-all">Batal</button>
            </div>
        </div>
    </div>
</div>
<script>
    function confirmDelete(id, nama) { document.getElementById('modal-delete-info').innerText = nama; document.getElementById('deleteBtn').href = `<?= base_url('app/sapras/gedung/delete/') ?>` + id; document.getElementById('deleteModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('deleteModal').classList.add('hidden'); }
</script>
<?= $this->endSection() ?>