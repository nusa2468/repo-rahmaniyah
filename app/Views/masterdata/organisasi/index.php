<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    // Helper warna jenjang
    if (!function_exists('getJenjangColor')) {
        function getJenjangColor($kode) {
            $kode = strtoupper($kode ?? '');
            return match ($kode) {
                'GLOBAL', 'PUSAT' => 'bg-gray-800 text-white',
                'SD', 'MI'        => 'bg-rose-500 text-white',
                'SMP', 'MTS'      => 'bg-sky-600 text-white',
                'SMA', 'SMK', 'MA' => 'bg-indigo-500 text-white',
                'TK', 'PAUD'      => 'bg-emerald-500 text-white',
                default           => 'bg-gray-100 text-gray-600 border border-gray-200',
            };
        }
    }
    
    // Safety Variables
    $role           = $role ?? '';
    $jenjang        = $jenjang ?? '';
    $listJenjang    = $listJenjang ?? [];
    $filter_jenjang = $filter_jenjang ?? '';
?>

<!-- CSS Force Visibility untuk Tombol Aksi -->
<style>
    .force-show-btn {
        opacity: 1 !important;
        visibility: visible !important;
        display: flex !important;
        transform: none !important;
    }
    .btn-action-edit { background-color: #f59e0b !important; color: white !important; }
    .btn-action-delete { background-color: #e11d48 !important; color: white !important; }
    .btn-action-edit:hover { background-color: #d97706 !important; }
    .btn-action-delete:hover { background-color: #be123c !important; }

    /* DataTables Customization */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 0.75rem !important; padding: 0.5rem 1rem !important;
        background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important;
        font-size: 11px !important; font-weight: 700 !important; min-width: 250px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.75rem !important; border: none !important;
        background: #f1f5f9 !important; font-size: 10px !important;
        font-weight: 900 !important; text-transform: uppercase; margin: 0 2px !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #4f46e5 !important; color: white !important;
    }
</style>

<div class="container-fluid mb-6 px-4">
    
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
                    <span class="ml-1 font-medium text-slate-800 dark:text-white md:ml-2">Organisasi</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- 2. HERO HEADER & ACTIONS (PERFECT ALIGNMENT) -->
    <div class="relative overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-indigo-600 via-blue-700 to-blue-800 shadow-lg mb-6 group">
        <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-3xl transition-all group-hover:bg-white/20"></div>
        <div class="relative z-10 p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Left Info -->
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md border border-white/30 text-white">
                    <i class="fas fa-sitemap text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight leading-none mb-1"><?= esc($title ?? 'Struktur Organisasi') ?></h1>
                    <p class="text-blue-100 text-[11px] font-medium opacity-80 max-w-sm leading-tight">Kelola struktur hirarki dan penempatan personel.</p>
                    
                    <!-- Indikator Role & Unit -->
                    <div class="flex items-center gap-2 mt-2">
                        <?php if(in_array($role, ['superadmin', 'yayasan'])): ?>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-white/20 text-white border border-white/30 uppercase tracking-wide">
                                Global View
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-500/80 text-white border border-white/30 uppercase tracking-wide">
                                Unit: <?= esc($jenjang) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Actions (Aligned with Inline Styles) -->
            <div class="flex flex-wrap items-center gap-2">
                
                <!-- DROPDOWN FILTER UNIT -->
                <?php if (in_array($role, ['superadmin', 'yayasan']) && !empty($listJenjang)): ?>
                    <!-- FIX: Inline Style height: 40px pada Form -->
                    <form action="" method="get" class="flex items-center m-0 p-0" style="height: 40px;">
                        <div class="relative h-full w-full md:w-48 group"> 
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                <i class="fas fa-filter text-indigo-200 text-xs group-hover:text-white transition-colors"></i>
                            </div>
                            
                            <!-- FIX: Inline Style height: 40px pada Select -->
                            <select name="kode_jenjang" onchange="this.form.submit()" 
                                    class="w-full pl-9 pr-8 bg-white/10 border border-white/20 text-white text-[10px] font-black uppercase tracking-wider rounded-xl shadow-lg focus:ring-2 focus:ring-white/50 focus:border-white/50 outline-none cursor-pointer hover:bg-white/20 transition-colors appearance-none flex items-center"
                                    style="height: 40px !important;">
                                <option value="" class="text-gray-800">Semua Unit</option>
                                <?php foreach ($listJenjang as $j): ?>
                                    <?php 
                                        $val = is_array($j) ? ($j['kode_jenjang'] ?? '-') : ($j->kode_jenjang ?? '-');
                                        $lbl = is_array($j) ? ($j['nama_jenjang'] ?? 'Unit ' . $val) : ($j->nama_jenjang ?? 'Unit ' . $val);
                                        $sel = ($filter_jenjang === $val) ? 'selected' : '';
                                    ?>
                                    <option value="<?= esc($val) ?>" <?= $sel ?> class="text-gray-800">
                                        <?= esc($lbl) ?> (<?= esc($val) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-indigo-200">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- Tombol Visual (Inline Style height: 40px) -->
                <a href="<?= base_url('app/masterdata/organisasi/visual') ?>" 
                   class="inline-flex items-center justify-center gap-2 bg-white/10 border border-white/20 px-4 rounded-xl text-white font-black uppercase tracking-wider text-[10px] hover:bg-white/20 transition-all shadow-lg active:scale-95 no-underline border-box leading-none"
                   style="height: 40px !important;">
                    <i class="fas fa-project-diagram"></i> <span class="hidden sm:inline">Visual Bagan</span>
                </a>
                
                <!-- Tombol Tambah (Inline Style height: 40px) -->
                <a href="<?= base_url('app/masterdata/organisasi/new') ?>" 
                   class="inline-flex items-center justify-center gap-2 bg-white px-5 rounded-xl text-blue-700 font-black uppercase tracking-wider text-[10px] hover:bg-blue-50 transition-all shadow-lg active:scale-95 no-underline border-box leading-none"
                   style="height: 40px !important;">
                    <i class="fas fa-plus-circle text-sm"></i> Tambah Personel
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl mb-6 flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="h-10 w-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <h4 class="text-xs font-black text-emerald-800 uppercase tracking-widest leading-none mb-1">Berhasil</h4>
                <p class="text-[11px] font-bold text-emerald-600 mb-0"><?= session()->getFlashdata('success') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="bg-white dark:bg-gray-900 rounded-[1.5rem] border border-gray-100 dark:border-white/5 shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-white/5 flex items-center justify-between bg-gray-50/30 dark:bg-white/5">
            <h3 class="text-[11px] font-black text-gray-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                <span class="h-4 w-1 bg-indigo-600 rounded-full"></span>
                Daftar Penempatan Struktural
            </h3>
        </div>

        <div class="p-4 overflow-x-auto">
            <table class="w-full text-left border-collapse" id="dataTable">
                <thead>
                    <tr class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest bg-gray-50/50 dark:bg-white/5">
                        <th class="px-4 py-3 text-center" width="60">Urut</th>
                        <th class="px-4 py-3">Unit & Klasifikasi</th>
                        <th class="px-4 py-3">Jabatan & Hirarki</th>
                        <th class="px-4 py-3">Pengampu Personel</th>
                        <th class="px-4 py-3 text-center" width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    <?php foreach ($organisasi as $o) : ?>
                        <tr class="group transition-all hover:bg-gray-50/50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 text-center align-middle">
                                <div class="h-8 w-8 mx-auto rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-[10px] font-black border border-indigo-100 dark:border-indigo-500/20 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                    <?= $o['urutan'] ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="flex flex-col gap-1">
                                    <?php 
                                        $kj = strtoupper($o['kode_jenjang']); 
                                        $badgeColor = getJenjangColor($kj);
                                    ?>
                                    <span class="px-2.5 py-1 rounded-md text-[8px] font-black uppercase tracking-wider shadow-sm w-fit <?= $badgeColor ?>">
                                        <i class="fas <?= ($kj == 'GLOBAL') ? 'fa-university' : 'fa-school' ?> mr-1 text-[7px]"></i>
                                        <?= esc($o['kode_jenjang']) ?>
                                    </span>
                                    <span class="text-[9px] font-black text-indigo-400 uppercase tracking-tighter pl-1">
                                        <?= esc($o['jenis_organisasi']) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="text-[11px] font-black text-slate-700 dark:text-slate-200 leading-none mb-1 uppercase tracking-tight"><?= esc($o['nama_jabatan'] ?? 'N/A') ?></div>
                                <div class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/5 text-slate-400 text-[8px] font-black tracking-widest border border-slate-200 dark:border-white/10 uppercase">
                                    Level <?= $o['level_jabatan'] ?? '0' ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-white dark:bg-gray-800 border border-slate-100 dark:border-white/10 shadow-sm flex items-center justify-center text-slate-400 group-hover:text-indigo-500 transition-colors overflow-hidden shrink-0">
                                        <i class="fas fa-user-tie text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-[11px] font-black text-slate-800 dark:text-white leading-none mb-1 uppercase truncate"><?= esc($o['nama_display']) ?></div>
                                        <div class="text-[9px] font-bold text-slate-400 truncate">
                                            <i class="fas fa-id-card-alt mr-1"></i> <?= $o['nip_display'] ?: 'NIP belum diatur' ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                <div class="flex justify-center gap-2">
                                    <!-- TOMBOL AKSI FORCE VISIBILITY -->
                                    <a href="<?= base_url('app/masterdata/organisasi/edit/' . $o['id']) ?>" 
                                       class="force-show-btn btn-action-edit h-9 w-9 rounded-xl items-center justify-center shadow-lg transition-all active:scale-90 no-underline"
                                       title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <button type="button" 
                                            class="force-show-btn btn-action-delete h-9 w-9 rounded-xl items-center justify-center shadow-lg transition-all active:scale-90"
                                            onclick="confirmDelete('<?= base_url('app/masterdata/organisasi/delete/' . $o['id']) ?>', '<?= esc($o['nama_display']) ?>', '<?= esc($o['nama_jabatan'] ?? 'N/A') ?>')">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modern Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-[2rem] overflow-hidden dark:bg-gray-900">
            <div class="p-8 text-center">
                <div class="h-20 w-20 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-500 flex items-center justify-center text-3xl mx-auto mb-6">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2 uppercase tracking-tighter">Hapus Penempatan?</h3>
                <p class="text-xs font-bold text-slate-400 mb-6 leading-relaxed">
                    Personel ini akan dihapus dari posisi jabatan terpilih dalam struktur organisasi. Tindakan ini tidak dapat dibatalkan.
                </p>
                
                <div class="bg-slate-50 dark:bg-white/5 rounded-2xl p-4 mb-8 border border-slate-100 dark:border-white/10 text-left">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Personel Terkait</p>
                    <div class="text-[12px] font-black text-slate-700 dark:text-white uppercase mb-1" id="delName">-</div>
                    <div class="text-[10px] font-bold text-indigo-500" id="delJabatan">-</div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" class="py-3.5 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition-all" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="btnConfirmDelete" class="py-3.5 rounded-xl btn-action-delete text-white text-[10px] font-black uppercase tracking-widest shadow-lg transition-all flex items-center justify-center gap-2 no-underline">
                        <i class="fas fa-trash-alt"></i> Hapus Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().destroy();
        }

        $('#dataTable').DataTable({
            "language": { 
                "search": "",
                "searchPlaceholder": "Cari Nama atau Jabatan...",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "paginate": { "next": "Next", "previous": "Prev" }
            },
            "order": [[0, "asc"]],
            "pageLength": 10,
            "dom": '<"flex flex-col md:flex-row justify-between items-center gap-4 mb-4"f>rt<"flex flex-col md:flex-row justify-between items-center gap-4 mt-4"ip>',
            "columnDefs": [{ "orderable": false, "targets": 4 }]
        });
    });

    function confirmDelete(url, name, jabatan) {
        $('#delName').text(name);
        $('#delJabatan').text(jabatan);
        $('#btnConfirmDelete').attr('href', url);
        $('#deleteModal').modal('show');
    }
</script>
<?= $this->endSection() ?>