<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full flex flex-col gap-8">
    
    <!-- 1. HEADER -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-[2px] bg-emerald-500"></span>
                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-600">Info Center</span>
            </div>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Pengumuman & <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">Informasi</span>
            </h1>
        </div>
        <a href="<?= base_url('app/cms/pengumuman/new') ?>" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-emerald-500/30 transition-all transform hover:scale-105">
            <i class="fas fa-plus mr-2"></i> Buat Pengumuman
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
               class="border-emerald-500 text-emerald-600 whitespace-nowrap py-4 px-1 border-b-2 font-black text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-bullhorn mr-2"></i> Pengumuman
            </a>
            <a href="<?= base_url('app/cms/agenda') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-calendar-alt mr-2"></i> Agenda
            </a>
            <a href="<?= base_url('app/cms/album') ?>" 
               class="border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-xs uppercase tracking-widest transition-all">
                <i class="fas fa-images mr-2"></i> Galeri
            </a>
        </nav>
    </div>

    <!-- 3. STATISTIK KPI (PHP Logic Calculation) -->
    <?php
    $total = count($pengumuman);
    $active = 0; $expired = 0;
    $sd = 0; $smp = 0; $sma = 0; $global = 0;
    
    foreach ($pengumuman as $p) {
        // Cek Expired
        $isExp = $p['tanggal_berakhir'] && strtotime($p['tanggal_berakhir']) < time();
        $isExp ? $expired++ : $active++;

        // Hitung Unit
        $j = $p['jenjang'] ?? $p['kode_jenjang'] ?? 'Global'; 
        if ($j == 'SD') $sd++;
        if ($j == 'SMP') $smp++;
        if ($j == 'SMA') $sma++;
        if ($j == 'Global' || $j == null) $global++;
    }
    
    $cards = [
        ['label' => 'Total Info',   'val' => $total,   'bg' => 'bg-emerald-500', 'border' => 'border-emerald-700', 'icon' => 'bullhorn'],
        ['label' => 'Info Aktif',   'val' => $active,  'bg' => 'bg-sky-500',     'border' => 'border-sky-700',     'icon' => 'check-circle'],
        ['label' => 'Kadaluarsa',   'val' => $expired, 'bg' => 'bg-slate-500',   'border' => 'border-slate-700',   'icon' => 'history'],
        ['label' => 'Info Global',  'val' => $global,  'bg' => 'bg-amber-500',   'border' => 'border-amber-700',   'icon' => 'globe'],
    ];
    ?>

    <!-- Stats Row -->
    <div class="flex flex-row flex-wrap lg:flex-nowrap gap-4 w-full">
        <?php foreach($cards as $c): ?>
        <div class="basis-[calc(50%-0.5rem)] lg:basis-1/4 flex-grow <?= $c['bg'] ?> p-4 rounded-2xl border-b-4 <?= $c['border'] ?> shadow-lg relative overflow-hidden group">
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
        // Logika Tampilan Scope (Sama dengan Berita & Agenda)
        $request = \Config\Services::request();
        $filterJenjang = $request->getGet('jenjang');
        
        $sessionJenjang = session('kode_jenjang');
        $isGlobal = isset($isGlobal) ? $isGlobal : (empty($sessionJenjang) || in_array(strtoupper($sessionJenjang), ['GLOBAL', 'YAYASAN', 'ALL']));
        
        $listUnit = isset($daftarUnit) ? $daftarUnit : ['TK'=>'TK', 'SD'=>'SD', 'SMP'=>'SMP', 'SMA'=>'SMA'];
        $userLabel = $isGlobal ? 'GLOBAL / YAYASAN' : ($listUnit[$sessionJenjang] ?? $sessionJenjang);
    ?>

    <!-- Filter & Scope Bar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
        <div class="md:col-span-2 bg-slate-900 p-5 border-l-4 <?= $isGlobal ? 'border-emerald-500' : 'border-sky-500' ?> shadow-lg flex flex-col sm:flex-row items-center justify-between relative overflow-hidden group rounded-2xl">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas <?= $isGlobal ? 'fa-globe' : 'fa-building-lock' ?> text-9xl text-white transform rotate-12"></i>
            </div>
            <div class="flex items-center gap-5 z-10 w-full">
                <div class="w-12 h-12 <?= $isGlobal ? 'bg-emerald-500' : 'bg-sky-500' ?> flex items-center justify-center text-white shadow-lg rounded-xl">
                    <i class="fas <?= $isGlobal ? 'fa-globe-asia' : 'fa-lock' ?> text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black <?= $isGlobal ? 'text-emerald-300' : 'text-sky-300' ?> uppercase tracking-widest leading-none mb-1">
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

    <!-- 4. CONTENT GRID (Table & Chart) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Tabel Pengumuman (Kiri - Span 2) -->
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

            <!-- Table Card -->
            <div class="bg-white dark:bg-gray-900 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-50 dark:border-white/5 flex items-center justify-between">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-3">
                        <i class="fas fa-list text-emerald-500"></i> Daftar Pengumuman
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-white/5">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-12">No</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Judul & Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Unit</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            <?php if (!empty($pengumuman)) : ?>
                                <?php $no = 1; foreach ($pengumuman as $p) : 
                                    $isExp = $p['tanggal_berakhir'] && strtotime($p['tanggal_berakhir']) < time();
                                ?>
                                    <tr class="hover:bg-emerald-50/30 dark:hover:bg-white/5 transition-colors group">
                                        <td class="px-6 py-4 text-center text-xs font-bold text-gray-500"><?= $no++ ?></td>
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-800 dark:text-gray-200 mb-1 line-clamp-1 group-hover:text-emerald-600 transition-colors">
                                                <?= esc($p['judul']) ?>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider flex items-center gap-1">
                                                    <i class="far fa-clock"></i> Exp: <?= $p['tanggal_berakhir'] ? date('d M Y', strtotime($p['tanggal_berakhir'])) : '∞' ?>
                                                </span>
                                                <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                                <span class="px-1.5 py-0.5 text-[8px] font-bold uppercase rounded border <?= $isExp ? 'bg-gray-100 text-gray-500 border-gray-200' : 'bg-emerald-100 text-emerald-600 border-emerald-200' ?>">
                                                    <?= $isExp ? 'Kedaluwarsa' : 'Aktif' ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <?php 
                                                $j = $p['jenjang'] ?? $p['kode_jenjang'] ?? 'Global';
                                                $badgeColor = match($j) {
                                                    'SD' => 'bg-rose-100 text-rose-600 border-rose-200',
                                                    'SMP' => 'bg-emerald-100 text-emerald-600 border-emerald-200',
                                                    'SMA' => 'bg-sky-100 text-sky-600 border-sky-200',
                                                    default => 'bg-amber-100 text-amber-600 border-amber-200'
                                                };
                                            ?>
                                            <span class="px-2 py-1 text-[9px] font-black uppercase rounded border <?= $badgeColor ?>">
                                                <?= $j ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                                <a href="<?= base_url('app/cms/pengumuman/edit/' . $p['id']) ?>" 
                                                   class="w-8 h-8 flex items-center justify-center bg-white border border-gray-200 text-amber-500 hover:bg-amber-500 hover:text-white hover:border-amber-500 rounded-lg shadow-sm transition-all"
                                                   title="Edit">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </a>
                                                <button onclick="confirmDelete(<?= $p['id'] ?>, '<?= esc($p['judul']) ?>')" 
                                                        class="w-8 h-8 flex items-center justify-center bg-white border border-gray-200 text-rose-500 hover:bg-rose-500 hover:text-white hover:border-rose-500 rounded-lg shadow-sm transition-all"
                                                        title="Hapus">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <i class="fas fa-bullhorn text-4xl opacity-20"></i>
                                            <span class="text-xs font-bold uppercase tracking-wider">Belum ada pengumuman</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                 <!-- Pagination Placeholder -->
                 <?php if (isset($pager)): ?>
                    <div class="px-6 py-4 border-t border-gray-50 dark:border-white/5">
                        <?= $pager->links('default', 'tailwind_pagination') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Grafik & Info (Kanan - Span 1) -->
        <div class="flex flex-col gap-6">
            
            <!-- Chart Card -->
            <div class="bg-white dark:bg-gray-900 p-6 rounded-[2rem] border border-gray-100 dark:border-white/5 shadow-sm">
                <h3 class="text-sm font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2 uppercase tracking-wider">
                    <span class="w-1.5 h-4 bg-emerald-500 rounded-full"></span>
                    Distribusi Unit
                </h3>
                <div class="h-[200px] w-full relative flex items-center justify-center">
                    <canvas id="announcementChart"></canvas>
                </div>
            </div>

            <!-- Info Card -->
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-6 rounded-3xl shadow-lg shadow-emerald-500/20 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fas fa-lightbulb text-6xl transform rotate-45"></i>
                </div>
                <div class="relative z-10">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                            <i class="fas fa-info text-xs"></i>
                        </div>
                        <div>
                            <h4 class="font-black text-sm uppercase tracking-wider mb-1">Tips Publikasi</h4>
                            <p class="text-xs text-emerald-100 leading-relaxed font-medium">
                                Pengumuman akan otomatis hilang dari halaman depan jika tanggal berakhir sudah terlewati.
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
                        <h3 class="text-lg leading-6 font-black text-gray-900 uppercase italic tracking-tight">Hapus Pengumuman?</h3>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Hapus: <strong id="deleteTargetName"></strong><br>
                                Data yang dihapus tidak dapat dipulihkan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-2">
                <a href="#" id="btnConfirmDelete" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-xs font-black text-white uppercase tracking-widest hover:bg-rose-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-xs transition-colors">
                    Ya, Hapus
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
    const ctx = document.getElementById('announcementChart').getContext('2d');
    
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
        btn.href = '<?= base_url('app/cms/pengumuman/delete/') ?>/' + id;
        
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