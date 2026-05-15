<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- 1. Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800 uppercase italic">
                <?= esc($title) ?>
            </h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li class="inline-flex items-center uppercase italic">Sapras</li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase">Fasilitas</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase italic underline decoration-2">Manajemen Ruangan</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        
        <div class="flex items-center gap-2">
            <button type="button" onclick="openTambahModal()"
               class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-indigo-600 text-white hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 active:scale-95 border-b-4 border-indigo-800">
                <i class="fas fa-plus mr-2 text-[10px]"></i> Tambah Ruangan
            </button>
        </div>
    </div>

    <!-- 2. NAVIGASI TAB SAPRAS (Consistent Navigation) -->
    <div class="border-b border-slate-200 mb-8 overflow-x-auto">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="<?= base_url('app/sapras/tanah') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-map-marked-alt mr-2"></i> Tanah
            </a>
            <a href="<?= base_url('app/sapras/gedung') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-building mr-2"></i> Gedung
            </a>
            <a href="<?= base_url('app/sapras/ruangan') ?>" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest">
                <i class="fas fa-door-open mr-2"></i> Ruangan
            </a>
            <a href="<?= base_url('app/sapras/peralatan') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-tools mr-2"></i> Peralatan
            </a>
            <a href="<?= base_url('app/sapras/inventaris') ?>" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-boxes mr-2"></i> Inventaris
            </a>
        </nav>
    </div>

    <?php
        // --- LOGIKA UI (View Logic Only) ---
        $sessUnit      = session('kode_jenjang');
        
        // Gunakan variabel dari controller
        $amIUnitAdmin  = isset($isUnitAdmin) ? $isUnitAdmin : false;
        $currentFilter = isset($filterJenjang) ? $filterJenjang : '';
        
        // Ambil daftar unit dari controller (Dinamis dari DB)
        $listUnit = isset($daftarUnit) ? $daftarUnit : [];
    ?>

    <!-- 3. Info Bar & Filter (Solid Style) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Kartu Scope -->
        <div class="md:col-span-2 bg-slate-900 p-4 border-l-4 border-indigo-500 shadow-md flex items-center justify-between overflow-hidden relative group">
            <i class="fas fa-door-open absolute -right-4 -bottom-4 text-white/5 text-6xl transform rotate-12"></i>
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-10 h-10 bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <i class="fas fa-shield-alt text-lg"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest leading-none">Status Scope Unit</p>
                    <h3 class="text-sm font-black text-white uppercase italic mt-1 leading-none">
                        <?= !empty($sessUnit) ? 'UNIT: ' . esc($sessUnit) : 'AKSES SELURUH UNIT' ?>
                    </h3>
                </div>
                
                <!-- Filter Dropdown (Superadmin Only) -->
                <?php if (!$amIUnitAdmin) : ?>
                    <form action="<?= current_url() ?>" method="get" class="z-20 w-full sm:w-auto mt-3 sm:mt-0">
                        <div class="flex items-center bg-slate-800 p-1 rounded-sm border border-slate-700">
                            <select name="jenjang" onchange="this.form.submit()" class="bg-transparent text-white text-xs font-bold uppercase tracking-wide border-none focus:ring-0 cursor-pointer w-full sm:w-40 appearance-none pl-3 pr-8">
                                <option value="" <?= empty($currentFilter) ? 'selected' : '' ?>>- SEMUA UNIT -</option>
                                <?php if (!empty($listUnit)): ?>
                                    <?php foreach ($listUnit as $kode => $label) : ?>
                                        <option value="<?= esc($kode) ?>" <?= $currentFilter == $kode ? 'selected' : '' ?>>
                                            <?= esc($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="absolute right-3 pointer-events-none text-slate-400"><i class="fas fa-filter text-xs"></i></div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <span class="hidden sm:block text-[9px] font-black text-slate-500 uppercase tracking-widest italic">Aset Ruangan v4.0</span>
        </div>
        
        <!-- Kartu Total Kapasitas -->
        <div class="bg-indigo-600 p-4 border-b-4 border-indigo-800 shadow-md flex items-center justify-center group">
            <div class="text-center">
                <p class="text-[9px] font-black text-indigo-100 uppercase tracking-widest leading-none">Total Kapasitas</p>
                <h3 class="text-2xl font-black text-white mt-1 italic leading-none">
                    <?php 
                        // Hitung manual karena paginated data
                        $totalKapasitas = 0;
                        // Array access $r['kapasitas'] karena Model return array
                        foreach($ruangan as $r) { $totalKapasitas += (int)($r['kapasitas'] ?? 0); }
                        echo number_format($totalKapasitas, 0, ',', '.');
                    ?> 
                    <span class="text-[10px] opacity-70 uppercase italic">Siswa</span>
                </h3>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center">
            <i class="fas fa-check-circle text-emerald-500 mr-3 text-sm"></i>
            <p class="text-[11px] font-black text-emerald-800 uppercase tracking-tight"><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>

    <!-- 4. Main Table Card -->
    <div class="bg-white border-2 border-slate-200 shadow-xl overflow-hidden mb-6">
        <div class="bg-slate-50 border-b-2 border-slate-200 px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-1 h-4 bg-indigo-600 mr-1"></div>
                <h2 class="text-[11px] font-black text-slate-700 uppercase tracking-widest italic">Matriks Inventaris Ruangan</h2>
            </div>
            <?php if(!empty($currentFilter)): ?>
                <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-[9px] font-bold uppercase rounded-sm border border-indigo-200">
                    Filter: <?= esc($currentFilter) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white border-b-2 border-slate-100 uppercase italic">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50 w-16 text-center">No</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50">Identitas Ruangan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50 text-center">Gedung</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50 text-center">Kapasitas</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest border-r border-slate-50">Keterangan</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($ruangan)) : ?>
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center bg-slate-50/30">
                                <i class="fas fa-door-closed text-slate-200 text-6xl mb-4 block"></i>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic leading-relaxed">
                                    Data ruangan tidak ditemukan.<br>Silakan tambahkan data ruangan baru.
                                </p>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php 
                        $page = $pager->getCurrentPage('default') ?: 1;
                        $perPage = $pager->getPerPage('default') ?: 10;
                        $no = 1 + ($page - 1) * $perPage; 
                        
                        foreach ($ruangan as $r) : ?>
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 border-r border-slate-50 text-[11px] font-black text-slate-300 text-center italic">
                                    <?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?>
                                </td>
                                <td class="px-6 py-4 border-r border-slate-50">
                                    <!-- FIX: Menggunakan Array Syntax ['nama'] bukan Object ->nama -->
                                    <div class="text-[11px] font-black text-slate-800 uppercase italic leading-none"><?= esc($r['nama']) ?></div>
                                    <div class="text-[9px] font-bold text-indigo-500 uppercase tracking-widest mt-1 italic">
                                        Unit: <?= esc($r['kode_jenjang'] ?? 'GLOBAL') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 border-r border-slate-50 text-center whitespace-nowrap">
                                    <span class="px-2 py-1 bg-slate-800 text-white text-[9px] font-black rounded-none border-b-2 border-indigo-500 uppercase tracking-tighter">
                                        <i class="fas fa-building mr-1 text-[8px]"></i> <?= esc($r['nama_gedung'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 border-r border-slate-50 text-center">
                                    <div class="text-[12px] font-black text-slate-700 tracking-tighter italic leading-none">
                                        <?= esc($r['kapasitas']) ?>
                                    </div>
                                    <span class="text-[9px] font-bold text-slate-400 uppercase italic">Orang</span>
                                </td>
                                <td class="px-6 py-4 border-r border-slate-50 max-w-[200px]">
                                    <p class="text-[10px] font-bold text-slate-500 leading-tight uppercase tracking-tighter line-clamp-2 italic">
                                        <?= esc($r['keterangan'] ?: '-') ?>
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" 
                                                onclick="openEditModal(<?= htmlspecialchars(json_encode($r)) ?>)"
                                                class="w-8 h-8 flex items-center justify-center bg-amber-500 text-white shadow-lg border-b-4 border-amber-700 active:scale-90 transition-all"
                                                title="Ubah Data">
                                            <i class="fas fa-edit text-[10px]"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="confirmDelete('<?= $r['id'] ?>', '<?= esc($r['nama']) ?>')"
                                                class="w-8 h-8 flex items-center justify-center bg-rose-600 text-white shadow-lg border-b-4 border-rose-800 active:scale-90 transition-all"
                                                title="Hapus Data">
                                            <i class="fas fa-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        <?php if (isset($pager)) : ?>
            <div class="bg-slate-50 px-6 py-4 border-t-2 border-slate-100">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                        Matriks Ke-<?= $pager->getCurrentPage('default') ?> Dari <?= $pager->getPageCount('default') ?> Halaman
                    </p>
                    <div class="custom-pagination">
                        <?= $pager->links('default', 'tailwind_pagination') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 5. Modal Form CRUD (Solid) -->
<div id="modalRuangan" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 font-sans">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeFormModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-none text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-8 border-indigo-600">
            <form action="<?= base_url('app/sapras/ruangan/save') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="form-id">
                
                <div class="bg-white px-6 pt-6 pb-4 sm:p-8">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest italic flex items-center gap-2">
                            <i class="fas fa-edit text-indigo-500"></i> <span id="modalTitle">Form Ruangan Baru</span>
                        </h3>
                        <button type="button" onclick="closeFormModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="space-y-5">
                        <!-- Unit (Superadmin Selectable) -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Unit Pengelola <span class="text-rose-500">*</span></label>
                            <?php if ($amIUnitAdmin): ?>
                                <input type="text" readonly value="<?= esc($sessUnit) ?>" 
                                       class="w-full bg-slate-100 border-2 border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-tight text-slate-500 cursor-not-allowed">
                                <input type="hidden" name="kode_jenjang" value="<?= esc($sessUnit) ?>">
                            <?php else: ?>
                                <select name="kode_jenjang" id="form-jenjang" required 
                                        class="w-full bg-white border-2 border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-tight focus:border-indigo-500 focus:ring-0 outline-none transition-all cursor-pointer">
                                    <option value="">-- Pilih Unit --</option>
                                    <?php foreach ($listUnit as $kode => $label): ?>
                                        <option value="<?= esc($kode) ?>"><?= esc($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <!-- Gedung -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Lokasi Gedung <span class="text-rose-500">*</span></label>
                            <select name="id_gedung" id="form-gedung" required 
                                    class="w-full bg-white border-2 border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-tight focus:border-indigo-500 focus:ring-0 outline-none transition-all cursor-pointer">
                                <option value="">-- Pilih Gedung --</option>
                                <?php if (!empty($gedung)): ?>
                                    <?php foreach($gedung as $g): ?>
                                        <!-- FIX: Menggunakan Array Syntax untuk $g -->
                                        <option value="<?= is_array($g) ? $g['id'] : $g->id ?>">
                                            <?= is_array($g) ? esc($g['nama']) : esc($g->nama) ?> 
                                            (<?= is_array($g) ? esc($g['kode_jenjang']) : esc($g->kode_jenjang) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="text-[9px] text-slate-400 mt-1 italic">Hanya gedung dalam unit terpilih yang valid.</p>
                        </div>

                        <!-- Nama Ruangan -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Nama Ruangan <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama" id="form-nama" required placeholder="Contoh: R. KELAS 1A"
                                   class="w-full bg-white border-2 border-slate-200 px-4 py-2 text-xs font-black uppercase tracking-tight focus:border-indigo-500 focus:ring-0 outline-none transition-all">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Kapasitas -->
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Kapasitas (Orang)</label>
                                <input type="number" name="kapasitas" id="form-kapasitas" min="0" value="0"
                                       class="w-full bg-white border-2 border-slate-200 px-4 py-2 text-xs font-black tracking-tight focus:border-indigo-500 focus:ring-0 outline-none transition-all">
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Keterangan Tambahan</label>
                            <textarea name="keterangan" id="form-ket" rows="2"
                                      class="w-full bg-white border-2 border-slate-200 px-4 py-2 text-xs font-medium tracking-tight focus:border-indigo-500 focus:ring-0 outline-none transition-all"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 sm:px-8 flex flex-col sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full sm:w-auto px-10 py-2 bg-indigo-600 text-[10px] font-black text-white uppercase tracking-widest hover:bg-indigo-700 shadow-lg border-b-4 border-indigo-900 active:scale-95 transition-all">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                    <button type="button" onclick="closeFormModal()" class="w-full sm:w-auto px-10 py-2 bg-white border-2 border-slate-200 text-[10px] font-black text-slate-700 uppercase tracking-widest hover:bg-slate-50 active:scale-95 transition-all">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 font-sans">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-none text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-t-8 border-rose-600">
            <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 bg-rose-50 border-2 border-rose-100 text-rose-600">
                        <i class="fas fa-trash-alt text-sm"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest italic">Hapus Data?</h3>
                        <div class="mt-3 bg-slate-50 p-4 border-l-4 border-slate-300">
                            <p class="text-[11px] font-bold text-slate-500 uppercase leading-relaxed">
                                Ruangan: <span id="modal-delete-info" class="text-slate-900 font-black underline italic"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-2">
                <a id="deleteBtn" href="#" class="w-full inline-flex justify-center px-6 py-2 bg-rose-600 text-[10px] font-black text-white uppercase tracking-widest hover:bg-rose-700 shadow-lg border-b-4 border-rose-800 active:scale-95 transition-all">Konfirmasi</a>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 sm:mt-0 w-full inline-flex justify-center px-6 py-2 bg-white border-2 border-slate-200 text-[10px] font-black text-slate-700 uppercase tracking-widest hover:bg-slate-50 active:scale-95 transition-all">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openTambahModal() {
        document.getElementById('form-id').value = '';
        document.getElementById('form-nama').value = '';
        document.getElementById('form-gedung').value = '';
        document.getElementById('form-kapasitas').value = '0';
        document.getElementById('form-ket').value = '';
        
        // Reset Jenjang select if exists
        const jenjangSelect = document.getElementById('form-jenjang');
        if(jenjangSelect) jenjangSelect.value = '';

        document.getElementById('modalTitle').innerText = 'Registrasi Ruangan Baru';
        document.getElementById('modalRuangan').classList.remove('hidden');
    }

    function openEditModal(data) {
        document.getElementById('form-id').value = data.id;
        document.getElementById('form-nama').value = data.nama;
        document.getElementById('form-gedung').value = data.id_gedung;
        document.getElementById('form-kapasitas').value = data.kapasitas;
        document.getElementById('form-ket').value = data.keterangan;
        
        // Set Jenjang if exists
        const jenjangSelect = document.getElementById('form-jenjang');
        if(jenjangSelect && data.kode_jenjang) jenjangSelect.value = data.kode_jenjang;

        document.getElementById('modalTitle').innerText = 'Ubah Informasi Ruangan';
        document.getElementById('modalRuangan').classList.remove('hidden');
    }

    function closeFormModal() {
        document.getElementById('modalRuangan').classList.add('hidden');
    }

    function confirmDelete(id, nama) {
        document.getElementById('modal-delete-info').innerText = nama;
        document.getElementById('deleteBtn').href = `<?= base_url('app/sapras/ruangan/delete/') ?>` + id;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
</script>
<style>
    .font-black { font-weight: 900; }
    /* Pagination Nav Styling (Custom Tailwind Wrapper) */
    .custom-pagination nav ul { display: flex; gap: 0.25rem; }
    .custom-pagination nav ul li a, 
    .custom-pagination nav ul li span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; font-size: 10px; font-weight: 900;
        text-transform: uppercase; border: 2px solid #e2e8f0; background: white; transition: all 0.2s;
    }
    .custom-pagination nav ul li.active span { background: #4f46e5; color: white; border-color: #4f46e5; transform: translateY(-1px); }
    .custom-pagination nav ul li a:hover { border-color: #4f46e5; color: #4f46e5; background: #f8fafc; }
</style>

<?= $this->endSection() ?>