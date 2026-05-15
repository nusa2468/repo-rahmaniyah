<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800 uppercase italic"><?= esc($title) ?></h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li class="inline-flex items-center uppercase italic">Sapras</li>
                    <li aria-current="page"><div class="flex items-center text-indigo-600"><i class="fas fa-chevron-right mx-2 text-[8px]"></i> <span class="uppercase italic underline decoration-2">Inventaris</span></div></li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= base_url('app/sapras/inventaris/new') ?>" class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-indigo-600 text-white hover:bg-indigo-700 transition-all shadow-lg border-b-4 border-indigo-800"><i class="fas fa-plus mr-2 text-[10px]"></i> Tambah Inventaris</a>
        </div>
    </div>

    <!-- NAVIGASI TAB SAPRAS -->
    <div class="border-b border-slate-200 mb-8 overflow-x-auto">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="<?= base_url('app/sapras/tanah') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all"><i class="fas fa-map-marked-alt mr-2"></i> Tanah</a>
            <a href="<?= base_url('app/sapras/gedung') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all"><i class="fas fa-building mr-2"></i> Gedung</a>
            <a href="<?= base_url('app/sapras/ruangan') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all"><i class="fas fa-door-open mr-2"></i> Ruangan</a>
            <a href="<?= base_url('app/sapras/peralatan') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all"><i class="fas fa-tools mr-2"></i> Peralatan</a>
            <a href="<?= base_url('app/sapras/inventaris') ?>" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest"><i class="fas fa-boxes mr-2"></i> Inventaris</a>
        </nav>
    </div>

    <?php
        $sessUnit = session('kode_jenjang');
        $amIUnitAdmin = isset($isUnitAdmin) ? $isUnitAdmin : false;
        $currentFilter = isset($filterJenjang) ? $filterJenjang : '';
        $listUnit = isset($daftarUnit) ? $daftarUnit : [];
    ?>

    <!-- Info Bar -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="md:col-span-2 bg-slate-900 p-4 border-l-4 border-indigo-500 shadow-md flex items-center justify-between overflow-hidden relative group">
            <i class="fas fa-boxes absolute -right-4 -bottom-4 text-white/5 text-6xl transform rotate-12"></i>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-10 h-10 bg-indigo-500/20 flex items-center justify-center text-indigo-400"><i class="fas fa-shield-alt text-lg"></i></div>
                <div class="flex-1">
                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest leading-none">Status Scope Unit</p>
                    <h3 class="text-sm font-black text-white uppercase italic mt-1 leading-none"><?= !empty($sessUnit) ? 'UNIT: ' . esc($sessUnit) : 'AKSES SELURUH UNIT' ?></h3>
                    <?php if (!$amIUnitAdmin) : ?>
                        <form action="<?= current_url() ?>" method="get" class="mt-2">
                            <select name="jenjang" onchange="this.form.submit()" class="bg-slate-800 text-white text-[10px] font-bold uppercase border border-slate-700 rounded-sm py-1 px-2 focus:ring-0 focus:border-indigo-500">
                                <option value="" <?= empty($currentFilter) ? 'selected' : '' ?>>- SEMUA UNIT -</option>
                                <?php foreach ($listUnit as $kode => $label) : ?>
                                    <option value="<?= esc($kode) ?>" <?= $currentFilter == $kode ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="bg-indigo-600 p-4 border-b-4 border-indigo-800 shadow-md flex items-center justify-center group text-center">
            <div>
                <p class="text-[9px] font-black text-indigo-100 uppercase tracking-widest leading-none">Total Kuantitas</p>
                <h3 class="text-2xl font-black text-white mt-1 italic leading-none"><?php $totalQ = 0; foreach($inventaris as $i) { $totalQ += (int)($i['jumlah'] ?? 0); } echo number_format($totalQ, 0, ',', '.'); ?> <span class="text-[10px] opacity-70 uppercase italic">Pcs</span></h3>
            </div>
        </div>

        <div class="bg-emerald-600 p-4 border-b-4 border-emerald-800 shadow-md flex items-center justify-center group text-center">
            <div>
                <p class="text-[9px] font-black text-emerald-100 uppercase tracking-widest leading-none">Kondisi Baik</p>
                <h3 class="text-2xl font-black text-white mt-1 italic leading-none"><?php $totalBaik = 0; foreach($inventaris as $i) { if( isset($i['kondisi']) && $i['kondisi'] == 'Baik') $totalBaik += (int)($i['jumlah'] ?? 0); } echo number_format($totalBaik, 0, ',', '.'); ?><span class="text-[10px] opacity-70 uppercase italic">Ready</span></h3>
            </div>
        </div>
    </div>

    <!-- Tabel -->
    <div class="bg-white border-2 border-slate-200 shadow-xl overflow-hidden mb-6">
        <div class="bg-slate-50 border-b-2 border-slate-200 px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-table text-slate-400 text-xs"></i>
                <h2 class="text-[11px] font-black text-slate-700 uppercase tracking-widest italic">Data Inventaris</h2>
            </div>
            <?php if(!empty($currentFilter)): ?><span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-[9px] font-bold uppercase rounded-sm border border-indigo-200">Filter: <?= esc($currentFilter) ?></span><?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b-2 border-slate-100 uppercase">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50 w-16 text-center italic">#</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50">Identitas</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50 text-center">Kondisi</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50 text-center">Qty</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50">Keterangan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <?php if (empty($inventaris)) : ?>
                        <tr><td colspan="6" class="px-6 py-20 text-center bg-slate-50/50"><p class="text-xs font-black text-slate-400 uppercase italic">Data Kosong</p></td></tr>
                    <?php else : ?>
                        <?php $no = 1; foreach ($inventaris as $i) : ?>
                            <tr class="hover:bg-indigo-50/30 transition-colors group border-b border-slate-50">
                                <td class="px-6 py-4 border-r border-slate-50 text-[11px] font-black text-slate-400 text-center italic"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                <td class="px-6 py-4 border-r border-slate-50">
                                    <div class="text-[11px] font-black text-slate-800 uppercase italic leading-none mb-1"><?= esc($i['nama']) ?></div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-sky-100 text-sky-700"><?= esc($i['kode_jenjang'] ?? 'GLOBAL') ?></span>
                                </td>
                                <td class="px-6 py-4 border-r border-slate-50 text-center"><span class="px-2 py-1 bg-white border border-slate-200 text-[9px] font-bold rounded-sm uppercase"><?= esc($i['kondisi'] ?? 'BAIK') ?></span></td>
                                <td class="px-6 py-4 border-r border-slate-50 text-center"><?= number_format($i['jumlah'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 border-r border-slate-50 max-w-[200px]"><p class="text-[10px] font-medium text-slate-500 truncate italic"><?= esc($i['keterangan']) ?></p></td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= base_url('app/sapras/inventaris/edit/' . $i['id']) ?>" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-amber-500 hover:bg-amber-500 hover:text-white shadow-sm rounded-sm"><i class="fas fa-pencil-alt text-[10px]"></i></a>
                                        <button onclick="confirmDelete('<?= $i['id'] ?>', '<?= esc($i['nama']) ?>')" class="w-7 h-7 flex items-center justify-center bg-white border border-slate-200 text-rose-500 hover:bg-rose-500 hover:text-white shadow-sm rounded-sm"><i class="fas fa-trash-alt text-[10px]"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($inventaris)) : ?><div class="bg-slate-50 px-6 py-4 border-t border-slate-200"><?= $pager->links('default', 'tailwind_pagination') ?></div><?php endif; ?>
    </div>
</div>
<!-- Modal reused -->
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
                        <div class="mt-3 bg-slate-50 p-4 border-l-4 border-slate-300"><p class="text-[11px] font-bold text-slate-500 uppercase leading-relaxed">Hapus: <span id="modal-delete-info" class="block text-slate-900 mt-1 font-black underline italic"></span></p></div>
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
    function confirmDelete(id, nama) { document.getElementById('modal-delete-info').innerText = nama; document.getElementById('deleteBtn').href = `<?= base_url('app/sapras/inventaris/delete/') ?>` + id; document.getElementById('deleteModal').classList.remove('hidden'); }
    function closeModal() { document.getElementById('deleteModal').classList.add('hidden'); }
</script>
<?= $this->endSection() ?>