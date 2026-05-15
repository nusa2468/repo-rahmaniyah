<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    Pengaturan Pengguna
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto space-y-6">

    <!-- (A) HEADER AREA -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-1 italic">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/masterdata/dashboard') ?>" class="hover:text-indigo-600 transition-colors">PENGATURAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50"></i></li>
                    <li class="text-slate-600">MANAJEMEN USER</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Daftar Pengguna Sistem
            </h1>
        </div>

        <div class="flex flex-wrap gap-3 items-center">
            
            <!-- (0) Dropdown Unit (Anti Bocor - Hanya Super Admin) -->
            <?php 
                $sessionJenjang = session()->get('kode_jenjang');
                $isGlobal = in_array(strtoupper($sessionJenjang ?? ''), ['GLOBAL', 'YAYASAN', 'PUSAT']);
            ?>

            <?php if ($isGlobal): ?>
            <form id="filterFormHeader" action="<?= base_url('app/pengaturan/pengguna') ?>" method="get">
                <div class="relative min-w-[150px]">
                    <select name="unit" class="w-full appearance-none bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 py-2.5 pl-3 pr-8 rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-all cursor-pointer" onchange="this.form.submit()">
                        <option value="">Semua Unit</option>
                        <?php 
                        $units = $jenjang_list ?? []; 
                        $selectedUnit = $filters['unit'] ?? '';
                        foreach($units as $u): 
                            $kode = is_array($u) ? $u['kode_jenjang'] : $u;
                        ?>
                            <option value="<?= $kode ?>" <?= ($selectedUnit == $kode) ? 'selected' : '' ?>><?= $kode ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                    
                    <!-- Pertahankan parameter lain saat ganti unit -->
                    <?php if(!empty($filters['search'])): ?><input type="hidden" name="search" value="<?= esc($filters['search']) ?>"><?php endif; ?>
                    <?php if(!empty($filters['role'])): ?><input type="hidden" name="role" value="<?= esc($filters['role']) ?>"><?php endif; ?>
                    <?php if(!empty($filters['per_page'])): ?><input type="hidden" name="per_page" value="<?= esc($filters['per_page']) ?>"><?php endif; ?>
                </div>
            </form>
            <?php endif; ?>

            <!-- 2. Tombol Tambah -->
            <a href="<?= base_url('app/pengaturan/pengguna/new') ?>" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-600/20 transition-all active:scale-95 whitespace-nowrap">
                <i class="fas fa-user-plus"></i> <span class="hidden sm:inline">Tambah</span>
            </a>
        </div>
    </div>

    <!-- (B) KARTU KPI / STATISTIK -->
    <?php 
        $statTotal   = $kpi['total_all'] ?? 0;
        $statSiswa   = $kpi['total_siswa'] ?? 0;
        $statGuru    = $kpi['total_guru'] ?? 0;  
        $statStaff   = $kpi['total_staff'] ?? 0; 
        $statSystem  = $kpi['total_system'] ?? 0;
    ?>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- 1. Total User -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3 hover:border-indigo-300 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Total</p>
                <h3 class="text-xl font-black text-slate-800 dark:text-white"><?= number_format($statTotal) ?></h3>
            </div>
        </div>
        <!-- 2. Siswa -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3 hover:border-sky-300 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Siswa</p>
                <h3 class="text-xl font-black text-slate-800 dark:text-white"><?= number_format($statSiswa) ?></h3>
            </div>
        </div>
        <!-- 3. Guru -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3 hover:border-emerald-300 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Guru</p>
                <h3 class="text-xl font-black text-slate-800 dark:text-white"><?= number_format($statGuru) ?></h3>
            </div>
        </div>
        <!-- 4. Karyawan -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3 hover:border-teal-300 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                <i class="fas fa-id-card-alt"></i>
            </div>
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Karyawan</p>
                <h3 class="text-xl font-black text-slate-800 dark:text-white"><?= number_format($statStaff) ?></h3>
            </div>
        </div>
        <!-- 5. Admin -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-3 hover:border-amber-300 transition-colors">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Admin</p>
                <h3 class="text-xl font-black text-slate-800 dark:text-white"><?= number_format($statSystem) ?></h3>
            </div>
        </div>
    </div>

    <!-- Alert -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-sm flex items-center justify-between animate-fade-in-down">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span class="text-sm font-bold text-emerald-800"><?= session()->getFlashdata('success') ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-600"><i class="fas fa-times"></i></button>
        </div>
    <?php endif ?>

    <!-- (C) TABLE CONTROLS (Search, Filter Role & Limit) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-4 border-b border-slate-100 dark:border-slate-800">
            <form action="<?= base_url('app/pengaturan/pengguna') ?>" method="get" class="flex flex-col sm:flex-row gap-4 w-full justify-between items-center">
                
                <!-- Maintain Unit Filter -->
                <?php if (!empty($filters['unit'])): ?>
                    <input type="hidden" name="unit" value="<?= esc($filters['unit']) ?>">
                <?php endif; ?>

                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    
                    <!-- (2) Pagination Limit -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-500 uppercase whitespace-nowrap">Baris:</label>
                        <div class="relative">
                            <select name="per_page" class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 py-2 pl-3 pr-8 rounded-lg text-xs font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer" onchange="this.form.submit()">
                                <?php 
                                    $limits = [10, 25, 50, 100];
                                    $selectedLimit = $filters['per_page'] ?? 10;
                                    foreach($limits as $l): 
                                ?>
                                    <option value="<?= $l ?>" <?= ($selectedLimit == $l) ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>

                    <!-- (2) Filter Role/Tipe -->
                    <div class="relative min-w-[140px]">
                        <select name="role" class="w-full appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 py-2 pl-3 pr-8 rounded-lg text-xs font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer" onchange="this.form.submit()">
                            <option value="">Semua Tipe User</option>
                            <option value="SISWA" <?= (($filters['role'] ?? '') == 'SISWA') ? 'selected' : '' ?>>Siswa</option>
                            <option value="GURU" <?= (($filters['role'] ?? '') == 'GURU') ? 'selected' : '' ?>>Guru</option>
                            <option value="KARYAWAN" <?= (($filters['role'] ?? '') == 'KARYAWAN') ? 'selected' : '' ?>>Karyawan / Staff</option>
                            <option value="ADMIN" <?= (($filters['role'] ?? '') == 'ADMIN') ? 'selected' : '' ?>>Admin Sistem</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                            <i class="fas fa-filter text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" 
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-slate-400" 
                           placeholder="Cari NIP, NIS, Nama...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-xs"></i>
                    </div>
                </div>

            </form>
        </div>

        <?php 
            // Helper untuk Link Sort
            function getSortLink($col, $label) {
                $params = $_GET;
                $currentSort = $params['sort'] ?? '';
                $currentOrder = $params['order'] ?? 'asc';
                
                // Toggle order jika kolom sama
                $newOrder = ($currentSort == $col && $currentOrder == 'asc') ? 'desc' : 'asc';
                $params['sort'] = $col;
                $params['order'] = $newOrder;
                
                $url = current_url() . '?' . http_build_query($params);
                
                $icon = '';
                if ($currentSort == $col) {
                    $icon = ($currentOrder == 'asc') 
                        ? '<i class="fas fa-sort-up ml-1 text-indigo-500"></i>' 
                        : '<i class="fas fa-sort-down ml-1 text-indigo-500"></i>';
                } else {
                    $icon = '<i class="fas fa-sort ml-1 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity"></i>';
                }
                
                return '<a href="'.$url.'" class="flex items-center cursor-pointer group hover:text-indigo-600 transition-colors">'.$label.$icon.'</a>';
            }
        ?>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-extrabold uppercase tracking-widest text-[10px]">
                    <tr>
                        <th class="px-6 py-4 w-16 text-center">No</th>
                        <th class="px-6 py-4"><?= getSortLink('name', 'Pengguna') ?></th>
                        <th class="px-6 py-4">Email</th> <!-- NEW COLUMN: EMAIL -->
                        <th class="px-6 py-4"><?= getSortLink('role', 'Role & Unit') ?></th>
                        <th class="px-6 py-4 text-center"><?= getSortLink('status', 'Status') ?></th>
                        <th class="px-6 py-4 text-center">Terakhir Login</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if(empty($users)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fas fa-users-slash text-3xl opacity-50"></i>
                                    <span>Tidak ada data pengguna ditemukan.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                            // (1) Perbaikan Nomor Halaman
                            // Gunakan variable page dari controller, fallback ke $_GET
                            $currentPage = isset($pager) ? $pager->getCurrentPage('users') : 1;
                            // Cek jika controller mengirim offset manual via logic V26
                            if(isset($_GET['page_users'])) $currentPage = (int)$_GET['page_users'];
                            
                            $perPage = $filters['per_page'] ?? 10;
                            $no = 1 + ($perPage * ($currentPage - 1)); 
                        ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4 text-center text-slate-400 font-bold"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['nama_lengkap']) ?>&background=random&color=fff&size=64" 
                                         class="w-9 h-9 rounded-full border border-slate-200 shadow-sm" alt="Avatar">
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <?= esc($user['nama_lengkap']) ?>
                                            <?php if(strpos($user['id'], 'P-') === 0): ?>
                                                <i class="fas fa-chalkboard-teacher text-emerald-500 text-[10px]" title="Pegawai/Guru"></i>
                                            <?php elseif(strpos($user['id'], 'S-') === 0): ?>
                                                <i class="fas fa-user-graduate text-indigo-500 text-[10px]" title="Siswa"></i>
                                            <?php else: ?>
                                                <i class="fas fa-user-shield text-slate-400 text-[10px]" title="Admin/User"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-xs text-slate-500 font-mono">
                                            <!-- FIX: Username Logic -->
                                            <?php if(strpos($user['id'], 'S-') === 0): ?>
                                                <span class="text-indigo-400 font-bold">NIS:</span> 
                                            <?php elseif(strpos($user['id'], 'P-') === 0): ?>
                                                <span class="text-emerald-500 font-bold">NIP/Y:</span> 
                                            <?php else: ?>
                                                <span class="text-slate-400">@</span>
                                            <?php endif; ?>
                                            <?= esc($user['username']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <!-- NEW EMAIL COLUMN -->
                            <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400">
                                <?= esc($user['email'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <?php 
                                        $roleName = strtoupper($user['role_name'] ?? 'UNKNOWN');
                                        $roleClass = 'bg-indigo-50 text-indigo-600 border-indigo-100'; // Default Admin
                                        
                                        if ($roleName == 'SISWA') {
                                            $roleClass = 'bg-slate-100 text-slate-600 border-slate-200';
                                        } elseif ($roleName == 'GURU') {
                                            $roleClass = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                        } elseif (in_array($roleName, ['STAFF', 'PENUNJANG', 'KARYAWAN'])) {
                                            $roleClass = 'bg-teal-50 text-teal-600 border-teal-100';
                                        }
                                    ?>
                                    <span class="inline-flex w-fit items-center px-2.5 py-0.5 rounded text-[10px] font-black border uppercase tracking-wider <?= $roleClass ?>">
                                        <?= esc($roleName) ?>
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 flex items-center gap-1">
                                        <i class="fas fa-building text-[9px]"></i> 
                                        <?= esc($user['kode_jenjang'] ?: 'GLOBAL') ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($user['is_active']): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-wider border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 text-[10px] font-black uppercase tracking-wider border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center text-xs text-slate-500 font-mono">
                                <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '-' ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="<?= base_url('app/pengaturan/pengguna/edit/' . $user['id']) ?>" 
                                       class="w-8 h-8 inline-flex items-center justify-center bg-white border-2 border-slate-200 text-amber-500 hover:border-amber-500 hover:bg-amber-50 rounded-lg shadow-sm transition-all"
                                       title="Edit User">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    
                                    <form action="<?= base_url('app/pengaturan/pengguna/delete/' . $user['id']) ?>" method="post" class="contents" onsubmit="return confirm('Hapus pengguna ini? Akses mereka akan dicabut permanen.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" 
                                                class="w-8 h-8 inline-flex items-center justify-center bg-white border-2 border-slate-200 text-rose-500 hover:border-rose-500 hover:bg-rose-50 rounded-lg shadow-sm transition-all"
                                                title="Hapus User">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/30">
            <?php 
                // Gunakan links yang dikirim controller jika ada (V28+), jika tidak fallback ke standard
                echo isset($pager_links) ? $pager_links : $pager->links('users', 'tailwind_pagination'); 
            ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>