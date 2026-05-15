<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    // --- 1. INISIALISASI VARIABEL (ANTI ERROR & SAFETY) ---
    $session = session();
    $userRole = strtolower($session->get('role') ?? '');
    $userUnit = $session->get('kode_jenjang');

    // Cek variabel dari controller, jika tidak ada hitung ulang (Fallback)
    if (!isset($isSuperAdmin)) {
        $isSuperAdmin = in_array($userRole, ['superadmin', 'super_admin', 'yayasan', 'root']) 
                        || in_array(strtoupper($userUnit ?? ''), ['GLOBAL', 'YAYASAN', 'ROOT']);
    }

    $filter_jenjang = $filter_jenjang ?? $userUnit;
?>

<div class="min-h-screen bg-slate-50/50 font-sans text-slate-600 pb-20">
    
    <!-- --- HEADER SECTION --- -->
    <div class="sticky top-0 z-30 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 mb-8 transition-all duration-300">
        <div class="flex flex-col xl:flex-row justify-between items-center gap-4 max-w-screen-2xl mx-auto">
            
            <!-- Title & Context -->
            <div class="flex-1 w-full xl:w-auto text-center xl:text-left">
                <div class="flex items-center justify-center xl:justify-start gap-3">
                    <div class="p-2 bg-indigo-600 rounded-lg shadow-lg shadow-indigo-500/30 text-white">
                        <i class="fas fa-file-invoice text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">Manajemen Tagihan</h1>
                        <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                            <span class="bg-indigo-50 text-indigo-600 border border-indigo-100 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest">
                                SPP & Lainnya
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Controls: Dropdown Unit & Actions -->
            <div class="flex flex-wrap justify-center xl:justify-end items-center gap-3">
                
                <!-- DROPDOWN UNIT CERDAS (ANTI BOCOR) - ADDED HERE -->
                <div class="flex items-center p-1 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <form action="" method="get" class="flex items-center gap-2 m-0 p-0 relative">
                        <div class="relative">
                            <select name="jenjang" onchange="this.form.submit()" 
                                    <?= !$isSuperAdmin ? 'disabled' : '' ?> 
                                    class="pl-9 pr-8 py-2 bg-transparent text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer disabled:text-slate-400 disabled:cursor-not-allowed transition text-slate-600">
                                <?php if ($isSuperAdmin): ?>
                                    <option value="">SEMUA UNIT</option>
                                <?php endif; ?>
                                
                                <?php if(!empty($jenjang_list)): ?>
                                    <?php foreach($jenjang_list as $j): ?>
                                        <option value="<?= $j['kode_jenjang'] ?>" <?= ($filter_jenjang == $j['kode_jenjang']) ? 'selected' : '' ?>>
                                            Unit <?= strtoupper($j['nama_jenjang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            
                            <!-- Icon Lock/Filter -->
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fas <?= !$isSuperAdmin ? 'fa-lock' : 'fa-filter' ?> text-xs"></i>
                            </div>
                            <!-- Chevron -->
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>

                            <!-- Hidden Input untuk menjaga nilai jika disabled -->
                            <?php if(!$isSuperAdmin): ?>
                                <input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Action Buttons -->
                <a href="<?= base_url('app/keuangan/tagihan/mass_form') ?>" class="h-10 px-4 bg-white border border-slate-200 text-emerald-600 hover:text-white hover:bg-emerald-600 hover:border-emerald-600 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2 group">
                    <i class="fas fa-layer-group group-hover:text-white transition-colors"></i> Generate Masal
                </a>
                <a href="<?= base_url('app/keuangan/tagihan/form') ?>" class="h-10 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-200 transition flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Tagihan Manual
                </a>
            </div>
        </div>

        <!-- --- MODUL NAVIGATION TABS --- -->
        <?php if(isset($navigation)): ?>
        <div class="mt-6 overflow-x-auto pb-1 scrollbar-hide max-w-screen-2xl mx-auto">
            <div class="inline-flex p-1 bg-slate-200/60 rounded-xl border border-slate-200/60">
                <?php foreach($navigation as $key => $nav): 
                    $isActive = ($key === 'tagihan'); 
                    $activeClass = $isActive 
                        ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' 
                        : 'text-slate-500 hover:text-slate-700 hover:bg-white/50';
                ?>
                <a href="<?= base_url($nav['url']) ?>" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-2 <?= $activeClass ?>">
                    <i class="fas fa-<?= $nav['icon'] ?>"></i> <?= $nav['label'] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- MAIN CONTENT -->
    <div class="px-6 max-w-screen-2xl mx-auto space-y-8">
        
        <!-- KPI Cards (Modern Solid Gradient) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Scope Unit Card -->
            <div class="bg-slate-900 rounded-3xl p-6 shadow-xl relative overflow-hidden group border-l-4 border-indigo-500">
                <div class="relative z-10">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Unit Kerja Aktif</p>
                    <h3 class="text-xl font-black text-white tracking-tighter uppercase italic">
                         <?= !empty($active_unit) ? $active_unit : 'GLOBAL' ?>
                    </h3>
                </div>
                <i class="fas fa-university absolute -right-4 -bottom-4 text-8xl text-white/5 transform group-hover:scale-110 transition-transform"></i>
            </div>

            <!-- Total Tagihan -->
            <div class="bg-gradient-to-br from-indigo-500 to-blue-700 p-6 rounded-3xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Total Tagihan</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($total_tagihan ?? 0, 0, ',', '.') ?></h3>
                </div>
                <i class="fas fa-file-invoice-dollar absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>
            
            <!-- Total Terbayar -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6 rounded-3xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Sudah Terbayar</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($total_dibayar ?? 0, 0, ',', '.') ?></h3>
                </div>
                <i class="fas fa-check-circle absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>

            <!-- Sisa Piutang -->
            <div class="bg-gradient-to-br from-rose-500 to-pink-600 p-6 rounded-3xl shadow-lg text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Sisa Piutang</p>
                    <h3 class="text-2xl font-black tracking-tighter">Rp <?= number_format($total_terutang ?? 0, 0, ',', '.') ?></h3>
                </div>
                <i class="fas fa-clock absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform"></i>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-6">
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center">
                <i class="fas fa-filter mr-2"></i> Filter Data Tagihan
            </h4>
            <form action="<?= base_url('app/keuangan/tagihan') ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                
                <!-- 1. Filter Unit (Anti Bocor) -->
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Unit Sekolah</label>
                    <div class="relative">
                        <select name="jenjang" 
                                <?= !$isSuperAdmin ? 'disabled' : '' ?> 
                                class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700 disabled:opacity-60 disabled:cursor-not-allowed appearance-none">
                            <?php if ($isSuperAdmin): ?><option value="">SEMUA UNIT</option><?php endif; ?>
                            <?php if(!empty($jenjang_list)): foreach($jenjang_list as $j): ?>
                                <option value="<?= $j['kode_jenjang'] ?>" <?= ($filter_jenjang == $j['kode_jenjang']) ? 'selected' : '' ?>>
                                    <?= strtoupper($j['nama_jenjang']) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fas <?= !$isSuperAdmin ? 'fa-lock' : 'fa-chevron-down' ?> text-xs"></i>
                        </div>
                        <?php if(!$isSuperAdmin): ?><input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>"><?php endif; ?>
                    </div>
                </div>

                <!-- 2. Filter Siswa -->
                <div class="md:col-span-3">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Cari Siswa</label>
                    <select name="id_siswa" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700">
                        <option value="">-- Semua Siswa --</option>
                        <?php foreach($siswa_list as $siswa): ?>
                            <option value="<?= $siswa['id'] ?>" <?= ($selected_siswa == $siswa['id']) ? 'selected' : '' ?>>
                                <?= esc($siswa['nis']) ?> - <?= esc($siswa['nama_lengkap']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 3. Filter Bulan -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Bulan Jatuh Tempo</label>
                    <input type="month" name="bulan_jatuh_tempo" value="<?= $selected_bulan ?>" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700">
                </div>

                <!-- 4. Filter Status -->
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Status</label>
                    <select name="status" class="w-full bg-slate-50 border-none rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700">
                        <option value="">-- Semua --</option>
                        <option value="belum_lunas" <?= ($selected_status == 'belum_lunas') ? 'selected' : '' ?>>BELUM LUNAS</option>
                        <option value="lunas" <?= ($selected_status == 'lunas') ? 'selected' : '' ?>>LUNAS</option>
                    </select>
                </div>

                <!-- 5. Tombol Cari -->
                <div class="md:col-span-2">
                    <button type="submit" class="w-full py-3 bg-slate-800 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-slate-700 shadow-lg transition-all">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white border border-slate-100 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-[10px] uppercase text-slate-400 font-black border-b border-slate-100">
                            <th class="px-6 py-4 w-12 text-center">No</th>
                            <th class="px-6 py-4">Identitas Siswa</th>
                            <th class="px-6 py-4">Detail Tagihan</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-center">Jatuh Tempo</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-600">
                        <?php if (empty($tagihan)) : ?>
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center bg-slate-50/30">
                                    <i class="fas fa-file-invoice-dollar text-slate-200 text-6xl mb-4 block"></i>
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest italic">
                                        Data tagihan tidak ditemukan untuk filter ini.
                                    </p>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php 
                                $currentPage = $pager->getCurrentPage();
                                $perPage = $pager->getPerPage();
                                $no = ($currentPage - 1) * $perPage + 1;
                                
                                foreach ($tagihan as $item) : 
                                    $is_overdue = (strtotime($item['tanggal_jatuh_tempo']) < time() && $item['status_real'] !== 'lunas');
                            ?>
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-6 py-4 text-center font-mono text-slate-400"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                                    <td class="px-6 py-4">
                                        <div class="text-[11px] font-black text-slate-800 uppercase italic leading-none"><?= esc($item['nama_lengkap']) ?></div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[9px] font-bold text-indigo-500 uppercase tracking-widest italic">NIS: <?= esc($item['nis']) ?></span>
                                            <span class="px-1.5 py-0.5 bg-slate-100 border border-slate-200 text-[8px] font-black text-slate-500 uppercase rounded"><?= esc($item['nama_kelas'] ?? '-') ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-[11px] font-black text-indigo-700 uppercase italic leading-none"><?= esc($item['nama_pembayaran']) ?></div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1 italic truncate max-w-[200px]" title="<?= esc($item['deskripsi']) ?>">
                                            <?= esc($item['deskripsi']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="text-[12px] font-black text-slate-700 tracking-tighter italic leading-none">
                                            Rp <?= number_format($item['jumlah'], 0, ',', '.') ?>
                                        </div>
                                        <?php if(isset($item['total_terbayar_real']) && $item['total_terbayar_real'] > 0 && $item['status_real'] != 'lunas'): ?>
                                            <div class="text-[9px] font-bold text-emerald-600 uppercase tracking-tighter mt-1 italic">
                                                Terbayar: Rp <?= number_format($item['total_terbayar_real'], 0, ',', '.') ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="text-[10px] font-black <?= $is_overdue ? 'text-rose-500 animate-pulse' : 'text-slate-600' ?> uppercase italic">
                                            <?= date('d/m/Y', strtotime($item['tanggal_jatuh_tempo'])) ?>
                                        </div>
                                        <?php if($is_overdue): ?>
                                            <span class="text-[8px] font-bold text-rose-400 uppercase tracking-widest">Terlambat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <?php 
                                            $status = $item['status_real'] ?? $item['status'];
                                            $badgeColor = 'bg-slate-100 text-slate-500 border-slate-200';
                                            $statusLabel = 'BELUM LUNAS';

                                            if ($status == 'lunas') {
                                                $badgeColor = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                                $statusLabel = 'LUNAS';
                                            } elseif ($status == 'mencicil' || $status == 'sebagian') {
                                                $badgeColor = 'bg-amber-50 text-amber-600 border-amber-100';
                                                $statusLabel = 'CICILAN';
                                            } else {
                                                $badgeColor = 'bg-rose-50 text-rose-600 border-rose-100';
                                            }
                                        ?>
                                        <span class="px-2.5 py-1 text-[9px] font-black border uppercase italic tracking-tighter rounded-full <?= $badgeColor ?>">
                                            <?= $statusLabel ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <a href="<?= base_url('app/keuangan/tagihan/detail/' . $item['id']) ?>" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <?php if (($item['status_real'] ?? $item['status']) != 'lunas'): ?>
                                                <a href="<?= base_url('app/keuangan/pembayaran/create/' . $item['id']) ?>" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Bayar Sekarang">
                                                    <i class="fas fa-cash-register"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="<?= base_url('app/keuangan/tagihan/form/' . $item['id']) ?>" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition" title="Edit Data">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <button type="button" onclick="confirmDelete('<?= $item['id'] ?>', '<?= esc($item['nama_lengkap']) ?> - <?= esc($item['deskripsi']) ?>')" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Data">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/50">
                <?= $pager->links('default', 'tailwind_pagination') ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-[100] overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
            <div class="bg-white px-8 py-8">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-rose-100 mb-6">
                        <i class="fas fa-trash-alt text-2xl text-rose-600"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-widest">Hapus Tagihan?</h3>
                    <p class="text-sm text-slate-500 mt-2">
                        Anda akan menghapus tagihan: <br>
                        <span id="modal-delete-info" class="font-bold text-slate-800"></span>
                    </p>
                    <p class="text-xs text-rose-500 font-bold mt-2 bg-rose-50 p-2 rounded-lg">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Data yang sudah ada riwayat bayar tidak bisa dihapus.
                    </p>
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 py-3 bg-white border border-slate-200 text-slate-500 rounded-xl text-sm font-bold hover:bg-slate-50 transition">Batal</button>
                    <a id="deleteBtn" href="#" class="flex-1 py-3 bg-rose-600 text-white rounded-xl text-sm font-bold hover:bg-rose-700 shadow-lg shadow-rose-200 text-center transition">Hapus</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, infoText) {
        const modal = document.getElementById('deleteModal');
        const deleteBtn = document.getElementById('deleteBtn');
        const info = document.getElementById('modal-delete-info');
        
        info.innerText = infoText;
        deleteBtn.href = `<?= base_url('app/keuangan/tagihan/delete/') ?>` + id;
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
</script>
<?= $this->endSection() ?>