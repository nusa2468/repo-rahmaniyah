<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Kelola role/hak akses pengguna sistem dengan izin tertentu.
            </p>
        </div>

        <a href="<?= site_url('app/pengaturan/hak_akses/new') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
            <i class="fas fa-plus"></i>
            <span>Tambah Hak Akses</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Total Roles -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-white/10 shadow-sm flex items-center justify-between col-span-1">
            <div>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Hak Akses</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">
                    <?= number_format($totalRoles ?? 0) ?>
                </h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center text-xl">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 flex flex-col">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/20 rounded-t-2xl">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">
                Daftar Hak Akses
            </h3>
            <span class="text-[10px] font-mono text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                Updated: <?= date('d M Y') ?>
            </span>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-16">No</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nama Role</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-32">Unit</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Deskripsi</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-32">Jumlah Izin</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400 dark:text-gray-600">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-shield-alt text-2xl opacity-40"></i>
                                    </div>
                                    <p class="text-xs font-black uppercase tracking-widest">Belum Ada Role</p>
                                    <p class="text-xs mt-1">Tambahkan hak akses pertama Anda sekarang.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // Menghitung nomor urut absolut jika ada pagination
                        $page = isset($pager) ? $pager->getCurrentPage() : 1;
                        $perPage = isset($pager) ? $pager->getPerPage() : 10; 
                        $startNo = ($page - 1) * $perPage;
                        ?>
                        <?php foreach ($roles as $i => $role): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150 group">
                                <td class="px-5 py-4 text-center text-sm font-bold text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300">
                                    <?= $startNo + $i + 1 ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="font-bold text-sm text-gray-900 dark:text-white">
                                        <?= esc($role['name']) ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <?php 
                                        $jenjang = $role['kode_jenjang'] ?? 'GLOBAL';
                                        $badgeColor = ($jenjang === 'GLOBAL') ? 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 border-purple-200 dark:border-purple-500/30' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600';
                                    ?>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider border <?= $badgeColor ?>">
                                        <?= esc($jenjang) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <?= esc($role['description'] ?? '-') ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-xs font-black bg-sky-100 dark:bg-sky-500/20 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30">
                                        <?= isset($role['permission_count']) ? esc($role['permission_count']) : '0' ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <!-- TOMBOL AKSI DIPERJELAS -->
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="<?= site_url('app/pengaturan/hak_akses/edit/' . $role['id']) ?>"
                                           class="group/btn flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/10 hover:bg-amber-500 text-amber-600 dark:text-amber-400 hover:text-white border border-amber-200 dark:border-amber-500/30 transition-all duration-200 shadow-sm hover:shadow-md active:scale-95"
                                           title="Edit Role">
                                            <i class="fas fa-pen text-xs"></i>
                                            <span class="text-xs font-bold hidden xl:inline">Edit</span>
                                        </a>

                                        <form action="<?= site_url('app/pengaturan/hak_akses/delete/' . $role['id']) ?>"
                                              method="post" 
                                              class="inline-block m-0 p-0"
                                              onsubmit="return confirm('Hapus role \'<?= esc($role['name']) ?>\'? Semua pengguna dengan role ini akan kehilangan akses.');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit"
                                                    class="group/btn flex items-center gap-2 px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-500/10 hover:bg-rose-600 text-rose-600 dark:text-rose-400 hover:text-white border border-rose-200 dark:border-rose-500/30 transition-all duration-200 shadow-sm hover:shadow-md active:scale-95"
                                                    title="Hapus Role">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                                <span class="text-xs font-bold hidden xl:inline">Hapus</span>
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

        <!-- Pagination Section -->
        <?php if (isset($pager)): ?>
            <?= $pager->links('default', 'tailwind_pagination') ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>