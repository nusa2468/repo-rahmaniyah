<?php
    $jenjang    = $jenjang ?? ''; 
    $isGlobal   = in_array(strtoupper($jenjang), ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']);
    $filterUnit = service('request')->getGet('filter_unit');
?>

<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">Data Prestasi Siswa</h2>
    </div>

    <!-- CONTROL BAR -->
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-4">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <span>Tampil</span>
            <select id="perPage-table-prestasi" class="border border-slate-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span>data</span>
        </div>
        <div class="relative">
            <input type="text" id="search-table-prestasi" placeholder="Cari siswa atau prestasi..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm w-full sm:w-64 focus:ring-2 focus:ring-indigo-500 outline-none transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-2.5 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse advanced-table" id="table-prestasi" data-per-page="10">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                    <th class="pb-4 pl-4 w-12 cursor-pointer hover:text-slate-600 sortable" data-sort="number">No <span class="sort-icon"></span></th>
                    <?php if($isGlobal): ?><th class="pb-4 pl-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Unit <span class="sort-icon"></span></th><?php endif; ?>
                    <th class="pb-4 pl-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Siswa <span class="sort-icon"></span></th>
                    <th class="pb-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Nama Prestasi <span class="sort-icon"></span></th>
                    <th class="pb-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Tingkat <span class="sort-icon"></span></th>
                    <th class="pb-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Peringkat <span class="sort-icon"></span></th>
                    <th class="pb-4 cursor-pointer hover:text-slate-600 sortable" data-sort="string">Tanggal <span class="sort-icon"></span></th>
                    <th class="pb-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($prestasi_list)): ?>
                    <tr><td colspan="<?= $isGlobal ? 8 : 7 ?>" class="py-8 text-center text-slate-400 italic">Belum ada data prestasi.</td></tr>
                <?php else: ?>
                    <?php $no = 1; foreach($prestasi_list as $row): 
                        if($isGlobal && $filterUnit && isset($row['kode_jenjang']) && $row['kode_jenjang'] !== $filterUnit) continue;
                    ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="py-4 pl-4 text-slate-500 font-medium"><?= $no++ ?></td>
                        <?php if($isGlobal): ?>
                            <td class="py-4 pl-4"><span class="px-2 py-1 bg-slate-200 text-slate-700 rounded text-xs font-bold"><?= $row['kode_jenjang'] ?? '-' ?></span></td>
                        <?php endif; ?>
                        <td class="py-4 pl-4">
                            <div class="font-bold text-slate-700"><?= $row['nama_siswa'] ?></div>
                            <div class="text-xs text-slate-400"><?= $row['nisn'] ?? '-' ?></div>
                        </td>
                        <td class="py-4">
                            <div class="text-slate-700 font-medium"><?= $row['nama_prestasi'] ?></div>
                            <div class="text-xs text-slate-500"><?= $row['jenis_prestasi'] ?></div>
                        </td>
                        <td class="py-4"><span class="px-2 py-1 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100"><?= $row['tingkat'] ?></span></td>
                        <td class="py-4 font-bold text-amber-600"><?= $row['peringkat'] ?></td>
                        <td class="py-4 text-slate-600"><?= date('d/m/Y', strtotime($row['tanggal_prestasi'])) ?></td>
                        <td class="py-4 text-center flex justify-center gap-2">
                                <button onclick='editPrestasi(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)' class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg></button>
                            <a href="<?= base_url('app/kesiswaan/delete_prestasi/'.$row['id']) ?>" onclick="return confirm('Hapus data ini?')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- Pagination Controls -->
        <div class="mt-4 flex flex-col sm:flex-row justify-between items-center gap-4" id="pagination-table-prestasi">
            <div class="text-sm text-slate-500" id="info-table-prestasi">Menampilkan 0 dari 0 data</div>
            <div class="flex gap-1" id="controls-table-prestasi"></div>
        </div>
    </div>
</div>