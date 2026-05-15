<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Manajemen Karyawan
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // --- 1. SESSION & ACCESS CONTROL ---
    $session = session();
    $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
    $globalRoles = ['GLOBAL', 'YAYASAN', 'PUSAT'];
    $isSuperAdmin = in_array($userJenjang, $globalRoles);

    // --- 2. DATA PROCESSING ---
    $karyawanData = $karyawan_data ?? [];
    $totalKaryawan = $pager->getTotal('karyawan'); 
    
    // Hitung statistik halaman aktif
    $countAktif = 0;
    $countL = 0;
    $countP = 0;
    foreach ($karyawanData as $row) {
        $rowArray = (array)$row;
        if (strtolower($rowArray['status'] ?? '') === 'aktif') $countAktif++;
        if (($rowArray['jenis_kelamin'] ?? '') === 'L') $countL++;
        if (($rowArray['jenis_kelamin'] ?? '') === 'P') $countP++;
    }

    // --- 3. HELPER FUNCTIONS ---
    if (!function_exists('getJenjangBadge')) {
        function getJenjangBadge($kode) {
            $kode = strtoupper($kode ?? '');
            switch ($kode) {
                case 'SD': case 'MI': return 'bg-red-100 text-red-800 border-red-200';
                case 'SMP': case 'MTS': return 'bg-blue-100 text-blue-800 border-blue-200';
                case 'SMA': case 'SMK': case 'MA': return 'bg-slate-200 text-slate-800 border-slate-300';
                case 'TK': case 'PAUD': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
                default: return 'bg-gray-100 text-gray-700 border-gray-200';
            }
        }
    }

    // URL Builder untuk Pagination tetap membawa filter
    $currentQueryParams = $_GET;
    $buildPageUrl = function($pageNum) use ($currentQueryParams) {
        $params = $currentQueryParams;
        $params['page_karyawan'] = $pageNum; 
        return current_url() . '?' . http_build_query($params);
    };
?>

<div class="space-y-6 animate-fade-in">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                <i class="fas fa-id-badge text-blue-600"></i> Master Database Karyawan
            </h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">
                Manajemen operasional, struktur jabatan, dan administrasi personil non-akademik.
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter & Search Block -->
            <form action="" method="get" class="flex flex-wrap items-center gap-2">
                <?php if ($current_filter['search']): ?>
                    <input type="hidden" name="search" value="<?= esc($current_filter['search']) ?>">
                <?php endif; ?>
                
                <div class="relative group">
                    <select name="unit" onchange="this.form.submit()" 
                            class="pl-9 pr-10 py-2.5 text-xs font-black bg-white border border-slate-200 rounded-xl appearance-none cursor-pointer focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-sm uppercase tracking-wider">
                        <?php if ($isSuperAdmin): ?>
                            <option value="GLOBAL">Seluruh Unit (Global)</option>
                        <?php endif; ?>
                        <?php foreach ($jenjang_list as $j): 
                            $j_kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                            if (strtoupper($j_kode) === 'GLOBAL') continue;
                        ?>
                            <option value="<?= $j_kode ?>" <?= (($current_filter['unit'] ?? '') == $j_kode) ? 'selected' : '' ?>>
                                UNIT <?= strtoupper($j_kode) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-filter absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-500 text-[10px]"></i>
                    <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none group-hover:text-blue-500 transition-colors"></i>
                </div>

                <select name="per_page" onchange="this.form.submit()"
                        class="px-4 py-2.5 text-xs font-bold bg-white border border-slate-200 rounded-xl focus:ring-4 focus:ring-blue-500/10 shadow-sm appearance-none cursor-pointer">
                    <option value="10" <?= ($current_filter['per_page'] == 10) ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= ($current_filter['per_page'] == 25) ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($current_filter['per_page'] == 50) ? 'selected' : '' ?>>50</option>
                </select>
            </form>

            <a href="<?= base_url('app/masterdata/karyawan/new') ?>" 
               class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 uppercase tracking-widest active:scale-95">
                <i class="fas fa-user-plus mr-2"></i> Tambah Baru
            </a>
        </div>
    </div>

    <!-- SOLID ANALYTICS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Data - Solid Indigo -->
        <div class="bg-indigo-600 rounded-2xl p-5 shadow-xl shadow-indigo-500/20 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-indigo-100 uppercase tracking-[0.2em] mb-1">Total Database</p>
                <h3 class="text-3xl font-black text-white"><?= number_format($totalKaryawan) ?></h3>
                <p class="text-[10px] text-indigo-200 mt-1 font-bold italic">Personil Terdaftar</p>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-users-viewfinder text-xl"></i>
            </div>
        </div>

        <!-- Aktif - Solid Emerald -->
        <div class="bg-emerald-600 rounded-2xl p-5 shadow-xl shadow-emerald-500/20 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-emerald-100 uppercase tracking-[0.2em] mb-1">Status Aktif</p>
                <h3 class="text-3xl font-black text-white"><?= $countAktif ?></h3>
                <p class="text-[10px] text-emerald-200 mt-1 font-bold italic">Sedang Bertugas</p>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-user-check text-xl"></i>
            </div>
        </div>

        <!-- Gender - Solid Rose/Pink -->
        <div class="bg-rose-600 rounded-2xl p-5 shadow-xl shadow-rose-500/20 flex items-center justify-between relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-rose-100 uppercase tracking-[0.2em] mb-1">Komposisi Gender</p>
                <h3 class="text-3xl font-black text-white"><?= $countL ?><span class="text-sm opacity-50 mx-1">L</span> <span class="text-sm opacity-30">/</span> <?= $countP ?><span class="text-sm opacity-50 ml-1">P</span></h3>
                <p class="text-[10px] text-rose-200 mt-1 font-bold italic">Distribusi Kelamin</p>
            </div>
            <div class="relative z-10 w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-white backdrop-blur-sm">
                <i class="fas fa-venus-mars text-xl"></i>
            </div>
        </div>

        <!-- Chart Ratio - Solid Slate -->
        <div class="bg-slate-900 rounded-2xl p-5 shadow-xl shadow-slate-900/20 flex items-center gap-5">
            <div class="w-14 h-14 relative shrink-0">
                <canvas id="chartStatusKaryawan"></canvas>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none">Rasio Aktif</p>
                <p class="text-2xl font-black text-white mt-1"><?= count($karyawanData) > 0 ? round(($countAktif / count($karyawanData)) * 100) : 0 ?>%</p>
                <p class="text-[9px] text-blue-400 font-bold mt-1 uppercase tracking-wider">Tingkat Retensi</p>
            </div>
        </div>
    </div>

    <!-- Alert Flashdata -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center justify-between shadow-sm animate-fade-in-down">
            <div class="flex items-center text-emerald-800 text-sm font-bold">
                <i class="fas fa-check-circle mr-3 text-lg text-emerald-500"></i> <?= session()->getFlashdata('success') ?>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600 transition-colors"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- Table Section -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <!-- Search Bar -->
        <div class="p-5 border-b border-slate-100 bg-slate-50/40">
            <form action="" method="get" class="relative max-w-md">
                <input type="hidden" name="per_page" value="<?= esc($current_filter['per_page']) ?>">
                <input type="hidden" name="unit" value="<?= esc($current_filter['unit']) ?>">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="<?= esc($current_filter['search']) ?>" 
                       placeholder="Cari berdasarkan nama, NIP, atau jabatan..." 
                       class="w-full pl-11 pr-4 py-3 text-sm border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all shadow-sm font-medium">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-[11px] font-black uppercase tracking-[0.15em] border-b border-slate-100">
                        <th class="px-6 py-4 text-center w-14">#</th>
                        <th class="px-6 py-4">Informasi Pegawai</th>
                        <th class="px-6 py-4 text-center">Unit</th>
                        <th class="px-6 py-4">Struktural & JK</th>
                        <th class="px-6 py-4">Kontak & TTL</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php 
                    $pageCount = $current_filter['per_page'] ?? 10;
                    $currentPage = $pager->getCurrentPage('karyawan');
                    $no = ($currentPage - 1) * $pageCount + 1;
                    
                    if (empty($karyawanData)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <i class="fas fa-folder-open text-4xl text-slate-200 mb-3"></i>
                                <p class="text-slate-400 font-bold italic text-sm">Data karyawan tidak ditemukan dalam unit ini.</p>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($karyawanData as $item) : ?>
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-6 py-5 text-center text-slate-400 font-bold text-xs"><?= $no++ ?></td>
                            <td class="px-6 py-5">
                                <div class="font-black text-slate-900 text-sm leading-tight"><?= esc($item['nama_lengkap']) ?></div>
                                <div class="text-[10px] text-blue-600 font-black mt-1 uppercase tracking-widest">NIP: <?= esc($item['nip'] ?: 'BELUM DIATUR') ?></div>
                                <div class="text-[9px] text-slate-400 mt-0.5 font-mono">NIK: <?= esc($item['nik'] ?: '-') ?></div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <?php $kj = strtoupper($item['kode_jenjang'] ?? '-'); ?>
                                <span class="px-3 py-1 rounded-lg border text-[10px] font-black tracking-widest uppercase <?= getJenjangBadge($kj) ?> shadow-sm">
                                    <?= esc($kj) ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-xs font-black text-slate-800 tracking-tight"><?= esc($item['jabatan']) ?></div>
                                <div class="text-[10px] text-slate-500 mt-1 font-bold"><?= $item['jenis_kelamin'] == 'L' ? 'LAKI-LAKI' : 'PEREMPUAN' ?></div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-xs text-slate-700 font-bold flex items-center gap-2">
                                    <i class="fab fa-whatsapp text-emerald-500"></i> <?= esc($item['telepon'] ?: '-') ?>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 font-medium truncate max-w-[150px]">
                                    <?= esc($item['tempat_lahir'] ?? '-') ?>, <?= !empty($item['tanggal_lahir']) ? date('d M Y', strtotime($item['tanggal_lahir'])) : '-' ?>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <?php 
                                    $st = strtolower($item['status'] ?? 'aktif');
                                    $stClass = match($st) {
                                        'aktif' => 'bg-emerald-500 text-white shadow-emerald-500/20',
                                        'cuti' => 'bg-amber-500 text-white shadow-amber-500/20',
                                        default => 'bg-rose-500 text-white shadow-rose-500/20'
                                    };
                                ?>
                                <span class="px-3 py-1 rounded-lg text-[9px] font-black uppercase shadow-lg <?= $stClass ?> tracking-[0.1em]">
                                    <?= esc($st) ?>
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center justify-end gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                    <a href="<?= base_url('app/masterdata/karyawan/show/'.$item['id']) ?>" 
                                       class="w-8 h-8 flex items-center justify-center bg-white text-slate-500 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-md border border-slate-100 hover:scale-110" title="Detail Profil">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="<?= base_url('app/masterdata/karyawan/edit/'.$item['id']) ?>" 
                                       class="w-8 h-8 flex items-center justify-center bg-white text-slate-500 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-md border border-slate-100 hover:scale-110" title="Edit Data">
                                        <i class="fas fa-pencil-alt text-xs"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $item['id'] ?>, '<?= esc($item['nama_lengkap']) ?>')" 
                                            class="w-8 h-8 flex items-center justify-center bg-white text-slate-500 rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-md border border-slate-100 hover:scale-110" title="Hapus Data">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls Footer -->
        <div class="px-8 py-5 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100">
            <?php 
                $startRecord = ($currentPage - 1) * $pageCount + 1;
                $endRecord = min($currentPage * $pageCount, $totalKaryawan);
                $totalPages = $pager->getPageCount('karyawan');
                if ($totalKaryawan == 0) { $startRecord = 0; $endRecord = 0; }

                // Pagination window
                $delta = 2;
                $range = [];
                for ($i = max(1, $currentPage - $delta); $i <= min($totalPages, $currentPage + $delta); $i++) {
                    $range[] = $i;
                }
                if ($range && $range[0] > 1) {
                    if ($range[0] > 2) array_unshift($range, '...');
                    array_unshift($range, 1);
                }
                if ($range && end($range) < $totalPages) {
                    if (end($range) < $totalPages - 1) $range[] = '...';
                    $range[] = $totalPages;
                }
            ?>
            <div class="text-[11px] font-black text-slate-500 uppercase tracking-widest">
                Menampilkan <span class="text-slate-900"><?= $startRecord ?> - <?= $endRecord ?></span> dari <span class="text-slate-900"><?= number_format($totalKaryawan) ?></span>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Prev -->
                <?php if ($currentPage > 1): ?>
                    <a href="<?= $buildPageUrl($currentPage - 1) ?>" 
                       class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm hover:scale-105 active:scale-95">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>
                <?php else: ?>
                    <button disabled class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                <?php endif; ?>
                
                <div class="hidden sm:flex items-center gap-2">
                    <?php foreach ($range as $p): ?>
                        <?php if ($p === '...'): ?>
                            <span class="px-2 text-slate-400 font-bold">...</span>
                        <?php else: ?>
                            <a href="<?= $buildPageUrl($p) ?>" 
                               class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-black border transition-all shadow-sm active:scale-90
                               <?= $currentPage == $p 
                                   ? 'bg-blue-600 text-white border-blue-600 shadow-blue-500/20' 
                                   : 'bg-white text-slate-600 border-slate-200 hover:bg-blue-50 hover:text-blue-600' 
                               ?>">
                                <?= $p ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Next -->
                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= $buildPageUrl($currentPage + 1) ?>" 
                       class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm hover:scale-105 active:scale-95">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                <?php else: ?>
                    <button disabled class="w-9 h-9 flex items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-300 cursor-not-allowed">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="modalHapus" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden animate-scale-in">
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <i class="fas fa-trash-can text-3xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900">Arsipkan Karyawan?</h3>
                <p class="text-sm text-slate-500 mt-3 leading-relaxed">Anda akan menghapus data <span id="targetName" class="font-black text-slate-900"></span>. Data ini akan dipindahkan ke folder sampah/arsip.</p>
            </div>
            <div class="p-6 bg-slate-50 flex gap-3">
                <button onclick="closeModal()" class="flex-1 py-3 text-xs font-black text-slate-500 bg-white border border-slate-200 rounded-2xl hover:bg-slate-100 transition-all uppercase tracking-widest">Batal</button>
                <form id="formHapus" method="post" class="flex-1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="w-full py-3 text-xs font-black text-white bg-rose-600 rounded-2xl hover:bg-rose-700 transition-all shadow-xl shadow-rose-500/30 uppercase tracking-widest">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- Logic Modal Hapus ---
    function confirmDelete(id, name) {
        document.getElementById('targetName').innerText = name;
        document.getElementById('formHapus').action = "<?= base_url('app/masterdata/karyawan/delete') ?>/" + id;
        document.getElementById('modalHapus').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modalHapus').classList.add('hidden');
    }

    // --- Chart Rasio Aktif (Solid Optimized) ---
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('chartStatusKaryawan');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [<?= $countAktif ?>, <?= max(0, count($karyawanData) - $countAktif) ?>],
                        backgroundColor: ['#3b82f6', 'rgba(255,255,255,0.05)'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '80%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
        }
    });
</script>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes scale-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    @keyframes fade-in-down { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    
    .animate-fade-in { animation: fade-in 0.4s ease-out forwards; }
    .animate-scale-in { animation: scale-in 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    .animate-fade-in-down { animation: fade-in-down 0.4s ease-out forwards; }

    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

<?= $this->endSection() ?>