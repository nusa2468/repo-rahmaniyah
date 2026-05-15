<?php
    // FIX: Re-init variabel untuk scope include
    $jenjang    = $jenjang ?? ''; 
    $isGlobal   = in_array(strtoupper($jenjang), ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']);
    $filterUnit = service('request')->getGet('filter_unit');
?>

<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Daftar Ekstrakurikuler</h2>
            <p class="text-sm text-slate-500">Kelola data kegiatan siswa</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <!-- TOMBOL TAMBAH (Dipindahkan ke sini agar pasti muncul dan berfungsi) -->
            <button onclick="openModal('modalEkskul')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                Buat Ekskul Baru
            </button>

            <!-- Filters form -->
            <form action="" method="get" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="ekskul">
                <?php if($filterUnit): ?><input type="hidden" name="filter_unit" value="<?= $filterUnit ?>"><?php endif; ?>
                <select name="kategori" onchange="this.form.submit()" class="px-4 py-2 bg-slate-50 border border-slate-200 text-slate-600 text-sm rounded-xl focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    <option value="Olahraga" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'Olahraga') ? 'selected' : '' ?>>Olahraga</option>
                    <option value="Seni" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'Seni') ? 'selected' : '' ?>>Seni</option>
                    <option value="Sains" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'Sains') ? 'selected' : '' ?>>Sains</option>
                    <option value="Religi" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'Religi') ? 'selected' : '' ?>>Religi</option>
                    <option value="Lainnya" <?= (isset($_GET['kategori']) && $_GET['kategori'] == 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse paginated-table" id="table-ekskul" data-per-page="10">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                    <th class="pb-4 pl-4 w-12 cursor-pointer hover:text-slate-600 sortable" data-sort="number">No <span class="sort-icon"></span></th>
                    <?php if($isGlobal): ?><th class="pb-4 pl-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Unit <span class="sort-icon"></span></th><?php endif; ?>
                    <th class="pb-4 pl-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Nama Ekskul <span class="sort-icon"></span></th>
                    <th class="pb-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Pembina <span class="sort-icon"></span></th>
                    <th class="pb-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Kategori <span class="sort-icon"></span></th>
                    <th class="pb-4">Jadwal</th>
                    <th class="pb-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($ekskul_list)): ?>
                    <tr><td colspan="<?= $isGlobal ? 7 : 6 ?>" class="py-8 text-center text-slate-400 italic">Belum ada data ekskul.</td></tr>
                <?php else: ?>
                    <?php $no = 1; foreach($ekskul_list as $row): ?>
                    <?php if($isGlobal && $filterUnit && $row['kode_jenjang'] !== $filterUnit) continue; ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors group">
                        <td class="py-4 pl-4 text-slate-500 font-medium"><?= $no++ ?></td>
                        <?php if($isGlobal): ?>
                            <td class="py-4 pl-4">
                                <span class="px-2 py-1 bg-slate-200 text-slate-700 rounded text-xs font-bold"><?= $row['kode_jenjang'] ?></span>
                            </td>
                        <?php endif; ?>
                        <td class="py-4 pl-4">
                            <div class="font-bold text-slate-700"><?= $row['nama_ekskul'] ?></div>
                            <div class="text-xs text-slate-400 truncate max-w-[200px]"><?= $row['deskripsi'] ?></div>
                        </td>
                        <td class="py-4 text-slate-600 font-medium"><?= $row['nama_pembina'] ?? '-' ?></td>
                        <td class="py-4"><span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-semibold border border-indigo-100"><?= $row['kategori'] ?></span></td>
                        <td class="py-4 text-slate-600"><?= $row['hari_latihan'] ?>, <?= substr($row['jam_mulai'], 0, 5) ?></td>
                        <td class="py-4 text-center flex justify-center gap-2">
                            <!-- FIX TOMBOL EDIT: Menggunakan HEX escaping agar aman dari karakter petik/newline -->
                            <button onclick="editEkskul(<?= htmlspecialchars(json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition" title="Edit Data">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            </button>
                            <a href="<?= base_url('app/kesiswaan/delete_ekskul/'.$row['id']) ?>" onclick="return confirm('Hapus ekskul ini?')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus Data">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination Controls -->
        <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4" id="pagination-table-ekskul">
            <div class="text-sm text-slate-500" id="info-table-ekskul">Menampilkan 0 dari 0 data</div>
            <div class="flex gap-1" id="controls-table-ekskul"></div>
        </div>
    </div>
</div>