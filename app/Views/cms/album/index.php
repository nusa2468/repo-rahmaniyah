<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full flex flex-col gap-8">
    
    <!-- 1. HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-[2px] bg-amber-500"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-amber-600">Visual Gallery</span>
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Galeri & <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-orange-600">Dokumentasi</span>
            </h1>
        </div>
        <a href="<?= base_url('app/cms/album/new') ?>" class="inline-flex items-center px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-amber-500/30 transition-all transform hover:scale-105">
            <i class="fas fa-plus mr-2"></i> Buat Album
        </a>
    </div>

    <!-- 2. NAVIGASI TAB CMS -->
    <div class="border-b border-gray-200 dark:border-white/10 mb-2 overflow-x-auto">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="<?= base_url('app/cms/dashboard') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="<?= base_url('app/cms/berita') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-newspaper mr-2"></i> Berita
            </a>
            <a href="<?= base_url('app/cms/pengumuman') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-bullhorn mr-2"></i> Pengumuman
            </a>
            <a href="<?= base_url('app/cms/agenda') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-calendar-alt mr-2"></i> Agenda
            </a>
            <a href="<?= base_url('app/cms/album') ?>" 
               class="border-amber-500 text-amber-600 whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-images mr-2"></i> Galeri
            </a>
        </nav>
    </div>

    <!-- 3. STATISTIK KPI -->
    <?php
    $total = count($albums);
    $sd = 0; $smp = 0; $sma = 0; $global = 0;
    foreach ($albums as $a) {
        $j = $a['jenjang'] ?? $a['kode_jenjang'] ?? 'Global'; 
        if ($j == 'SD') $sd++;
        if ($j == 'SMP') $smp++;
        if ($j == 'SMA') $sma++;
        if ($j == 'Global' || $j == null) $global++;
    }
    
    $cards = [
        ['label' => 'Total Album',  'val' => $total,   'bg' => 'bg-amber-500',   'border' => 'border-amber-700',   'icon' => 'images'],
        ['label' => 'Unit SD',      'val' => $sd,      'bg' => 'bg-rose-500',    'border' => 'border-rose-700',    'icon' => 'child'],
        ['label' => 'Unit SMP',     'val' => $smp,     'bg' => 'bg-emerald-500', 'border' => 'border-emerald-700', 'icon' => 'user-graduate'],
        ['label' => 'Unit SMA',     'val' => $sma,     'bg' => 'bg-sky-500',     'border' => 'border-sky-700',     'icon' => 'university'],
        ['label' => 'Global',       'val' => $global,  'bg' => 'bg-indigo-500',  'border' => 'border-indigo-700',  'icon' => 'globe'],
    ];
    ?>

    <!-- Stats Row -->
    <div class="flex flex-row flex-wrap lg:flex-nowrap gap-4 w-full">
        <?php foreach($cards as $c): ?>
        <div class="basis-[calc(50%-0.5rem)] lg:basis-1/5 flex-grow <?= $c['bg'] ?> p-4 rounded-2xl border-b-4 <?= $c['border'] ?> shadow-lg relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 text-white opacity-20 transform rotate-12 group-hover:scale-110 transition-transform">
                <i class="fas fa-<?= $c['icon'] ?> text-7xl"></i>
            </div>
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center text-white shadow-inner mb-2">
                    <i class="fas fa-<?= $c['icon'] ?> text-xs"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white leading-none"><?= $c['val'] ?></h3>
                    <p class="text-[9px] font-black text-white/80 uppercase tracking-widest mt-1"><?= $c['label'] ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php
        // Logika Tampilan Scope
        $request = \Config\Services::request();
        $filterJenjang = $request->getGet('jenjang');
        
        $sessionJenjang = session('kode_jenjang');
        $isGlobal = isset($isGlobal) ? $isGlobal : (empty($sessionJenjang) || in_array(strtoupper($sessionJenjang), ['GLOBAL', 'YAYASAN', 'ALL']));
        
        $listUnit = isset($daftarUnit) ? $daftarUnit : ['TK'=>'TK', 'SD'=>'SD', 'SMP'=>'SMP', 'SMA'=>'SMA'];
        $userLabel = $isGlobal ? 'GLOBAL / YAYASAN' : ($listUnit[$sessionJenjang] ?? $sessionJenjang);
    ?>

    <!-- Filter & Scope Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
        <div class="md:col-span-2 bg-slate-900 p-5 border-l-4 <?= $isGlobal ? 'border-amber-500' : 'border-sky-500' ?> shadow-lg flex flex-col sm:flex-row items-center justify-between relative overflow-hidden group rounded-2xl">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas <?= $isGlobal ? 'fa-globe' : 'fa-building-lock' ?> text-9xl text-white transform rotate-12"></i>
            </div>
            <div class="flex items-center gap-5 z-10 w-full">
                <div class="w-12 h-12 <?= $isGlobal ? 'bg-amber-500' : 'bg-sky-500' ?> flex items-center justify-center text-white shadow-lg rounded-xl">
                    <i class="fas <?= $isGlobal ? 'fa-globe-asia' : 'fa-lock' ?> text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black <?= $isGlobal ? 'text-amber-300' : 'text-sky-300' ?> uppercase tracking-widest leading-none mb-1">
                        <?= $isGlobal ? 'MODE SUPERADMIN / YAYASAN' : 'MODE AKSES TERBATAS' ?>
                    </p>
                    <h3 class="text-xl font-black text-white uppercase italic leading-none tracking-tight">
                        <?= esc($userLabel) ?>
                    </h3>
                </div>
                
                <?php if ($isGlobal) : ?>
                    <form action="<?= current_url() ?>" method="get" class="z-20 w-full sm:w-auto mt-3 sm:mt-0">
                        <div class="flex items-center bg-slate-800 p-1 rounded-lg border border-slate-700">
                            <select name="jenjang" onchange="this.form.submit()" class="bg-transparent text-white text-xs font-bold uppercase tracking-wide border-none focus:ring-0 cursor-pointer w-full sm:w-40 appearance-none pl-3 pr-8">
                                <option value="" <?= empty($filterJenjang) ? 'selected' : '' ?>>- SEMUA UNIT -</option>
                                <?php foreach($listUnit as $kode => $label): ?>
                                    <option value="<?= esc($kode) ?>" <?= $filterJenjang == $kode ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute right-3 pointer-events-none text-slate-400"><i class="fas fa-filter text-xs"></i></div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 border-l-4 border-gray-300 dark:border-gray-600 shadow-md flex items-center justify-center rounded-2xl">
            <div class="text-center">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Filter Aktif</p>
                <h3 class="text-xl font-black text-gray-800 dark:text-white mt-1 italic leading-none">
                    <?= !empty($filterJenjang) ? esc($filterJenjang) : 'SEMUA DATA' ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- 4. CONTENT GRID (Album List & Chart) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Daftar Album (Kiri - Span 2) -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Alert Notifikasi -->
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center rounded-r-xl">
                    <div class="bg-emerald-100 p-2 rounded-full mr-3 text-emerald-600"><i class="fas fa-check"></i></div>
                    <p class="text-xs font-bold text-emerald-800 uppercase tracking-wide"><?= session()->getFlashdata('success') ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')) : ?>
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 shadow-sm flex items-center rounded-r-xl">
                    <div class="bg-rose-100 p-2 rounded-full mr-3 text-rose-600"><i class="fas fa-times"></i></div>
                    <p class="text-xs font-bold text-rose-800 uppercase tracking-wide"><?= session()->getFlashdata('error') ?></p>
                </div>
            <?php endif; ?>

            <!-- Grid Album -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (!empty($albums)) : ?>
                    <?php foreach ($albums as $album) : 
                        $coverImg = (strpos($album['cover'], 'http') === 0) ? $album['cover'] : base_url('uploads/galeri/covers/' . $album['cover']);
                        $isPublic = ($album['status'] ?? 'publik') == 'publik';
                        $j = $album['jenjang'] ?? $album['kode_jenjang'] ?? 'Global';
                        $badgeColor = match($j) {
                            'SD' => 'bg-rose-500',
                            'SMP' => 'bg-emerald-500',
                            'SMA' => 'bg-sky-500',
                            default => 'bg-amber-500'
                        };
                    ?>
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden group hover:shadow-lg transition-all duration-300">
                        <div class="relative h-48 bg-gray-100">
                            <?php if ($album['cover']) : ?>
                                <img src="<?= $coverImg ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            <?php else : ?>
                                <div class="flex flex-col items-center justify-center h-full text-gray-300">
                                    <i class="fas fa-images text-4xl mb-2 opacity-50"></i>
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Tanpa Sampul</span>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badges -->
                            <div class="absolute top-3 left-3 flex gap-2">
                                <span class="px-2 py-1 text-[9px] font-black uppercase text-white rounded shadow-sm <?= $badgeColor ?>">
                                    <?= esc($j) ?>
                                </span>
                            </div>
                            <div class="absolute top-3 right-3">
                                <span class="px-2 py-1 text-[9px] font-black uppercase text-white rounded shadow-sm <?= $isPublic ? 'bg-emerald-500' : 'bg-slate-800' ?>">
                                    <i class="fas <?= $isPublic ? 'fa-globe' : 'fa-lock' ?> mr-1"></i> <?= $isPublic ? 'PUBLIK' : 'INTERNAL' ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-5 flex flex-col h-[180px]">
                            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight mb-2 line-clamp-2" title="<?= esc($album['judul']) ?>">
                                <?= esc($album['judul']) ?>
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 mb-4 flex-grow">
                                <?= $album['deskripsi'] ? strip_tags($album['deskripsi']) : 'Tidak ada deskripsi.' ?>
                            </p>
                            
                            <div class="mt-auto flex gap-2">
                                <a href="<?= base_url('app/cms/album/manage/' . $album['id']) ?>" class="flex-1 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-lg text-center transition-colors">
                                    <i class="fas fa-images mr-1"></i> Foto
                                </a>
                                <a href="<?= base_url('app/cms/album/edit/' . $album['id']) ?>" class="w-8 h-8 flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-500 rounded-lg transition-colors" title="Edit">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                <button onclick="confirmDelete(<?= $album['id'] ?>, '<?= esc($album['judul']) ?>')" class="w-8 h-8 flex items-center justify-center bg-rose-50 hover:bg-rose-100 text-rose-500 rounded-lg transition-colors" title="Hapus">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="col-span-2 py-12 text-center text-gray-400 bg-white dark:bg-gray-900 rounded-2xl border border-dashed border-gray-300">
                        <i class="fas fa-images text-5xl mb-3 opacity-20"></i>
                        <p class="text-xs font-bold uppercase tracking-wider">Belum ada album galeri</p>
                        <a href="<?= base_url('app/cms/album/new') ?>" class="inline-block mt-4 text-xs font-black text-amber-500 hover:text-amber-600 uppercase tracking-widest border-b-2 border-amber-200">Buat Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Grafik & Info (Kanan - Span 1) -->
        <div class="flex flex-col gap-6">
            
            <!-- Chart Card -->
            <div class="bg-white dark:bg-gray-900 p-6 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm">
                <h3 class="text-sm font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2 uppercase tracking-wider">
                    <span class="w-1.5 h-4 bg-amber-500 rounded-full"></span>
                    Distribusi Album
                </h3>
                <div class="h-[200px] w-full relative flex items-center justify-center">
                    <canvas id="albumDistributionChart"></canvas>
                </div>
            </div>

            <!-- Info Card -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-6 rounded-3xl shadow-lg shadow-amber-500/20 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-lightbulb text-6xl transform rotate-45"></i>
                </div>
                <div class="relative z-10">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                            <i class="fas fa-info text-xs"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-sm uppercase tracking-wider mb-1">Tips Galeri</h4>
                            <p class="text-xs text-amber-100 leading-relaxed font-medium">
                                Gunakan gambar berkualitas tinggi namun terkompresi agar website tetap cepat saat diakses.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus (Solid Design) -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border-t-8 border-rose-500">
            <div class="bg-white px-6 pt-6 pb-4 sm:p-8">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-trash-alt text-rose-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-black text-gray-900 uppercase italic tracking-tight">Hapus Album?</h3>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Anda akan menghapus album: <strong id="deleteTargetName"></strong><br>
                                Seluruh foto di dalamnya juga akan terhapus permanen.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-2">
                <a href="#" id="btnConfirmDelete" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-xs transition-colors">
                    Ya, Hapus Semua
                </a>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-xs font-black text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-xs transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Konfigurasi Chart
    const ctx = document.getElementById('albumDistributionChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['SD', 'SMP', 'SMA', 'Global'],
            datasets: [{
                data: [<?= $sd ?>, <?= $smp ?>, <?= $sma ?>, <?= $global ?>],
                backgroundColor: ['#f43f5e', '#10b981', '#0ea5e9', '#f59e0b'], 
                borderWidth: 0,
                hoverOffset: 10
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { display: false } },
        },
    });

    // Modal Logic
    function confirmDelete(id, name) {
        const modal = document.getElementById('deleteModal');
        const btn = document.getElementById('btnConfirmDelete');
        const targetName = document.getElementById('deleteTargetName');
        
        targetName.textContent = name;
        btn.href = '<?= base_url('app/cms/album/delete/') ?>/' + id;
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") closeModal();
    });
</script>

<?= $this->endSection() ?>