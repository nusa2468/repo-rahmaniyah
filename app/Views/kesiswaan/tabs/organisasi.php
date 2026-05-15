<?php
    $jenjang    = $jenjang ?? ''; 
    $isGlobal   = in_array(strtoupper($jenjang), ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']);
    $filterUnit = service('request')->getGet('filter_unit');
    // Map ID Organisasi untuk Atasan
    $orgMap = [];
    if(isset($organisasi_list)){
        foreach($organisasi_list as $o) $orgMap[$o['id']] = $o['jabatan'] . ' (' . $o['nama_lengkap'] . ')';
    }
?>

<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">Struktur Organisasi</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse paginated-table" id="table-organisasi" data-per-page="10">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                    <th class="pb-4 pl-4 w-12">No</th>
                    <?php if($isGlobal): ?><th class="pb-4 pl-4">Unit</th><?php endif; ?>
                    <th class="pb-4 pl-4">Nama Siswa</th>
                    <th class="pb-4">Jabatan</th>
                    <th class="pb-4">Jenis</th>
                    <th class="pb-4">Atasan Langsung</th>
                    <th class="pb-4 text-center">Urutan</th>
                    <th class="pb-4 text-center">Status</th>
                    <th class="pb-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($organisasi_list)): ?>
                    <tr><td colspan="<?= $isGlobal ? 9 : 8 ?>" class="py-8 text-center text-slate-400 italic">Belum ada data organisasi.</td></tr>
                <?php else: ?>
                    <?php $no = 1; foreach($organisasi_list as $org): 
                        if($isGlobal && $filterUnit && $org['kode_jenjang'] !== $filterUnit) continue;
                    ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="py-4 pl-4 text-slate-500 font-medium"><?= $no++ ?></td>
                        <?php if($isGlobal): ?>
                            <td class="py-4 pl-4"><span class="px-2 py-1 bg-slate-200 text-slate-700 rounded text-xs font-bold"><?= $org['kode_jenjang'] ?></span></td>
                        <?php endif; ?>
                        <td class="py-4 pl-4 font-bold text-slate-700"><?= $org['nama_lengkap'] ?></td>
                        <td class="py-4 text-slate-600"><?= $org['jabatan'] ?></td>
                        <td class="py-4"><span class="text-xs bg-slate-100 px-2 py-1 rounded border border-slate-200"><?= $org['jenis_organisasi'] ?></span></td>
                        <td class="py-4 text-slate-500 italic">
                            <?= isset($orgMap[$org['parent_id'] ?? 0]) ? $orgMap[$org['parent_id']] : '-' ?>
                        </td>
                        <td class="py-4 text-center font-mono text-slate-500"><?= $org['urutan'] ?? '-' ?></td>
                        <td class="py-4 text-center">
                            <?php if($org['status_aktif']): ?>
                                <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full font-bold">Aktif</span>
                            <?php else: ?>
                                <span class="text-xs bg-slate-100 text-slate-500 px-2 py-1 rounded-full">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 text-center flex justify-center gap-2">
                            <button onclick='editOrganisasi(<?= htmlspecialchars(json_encode($org), ENT_QUOTES, 'UTF-8') ?>)' class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg></button>
                            <a href="<?= base_url('app/kesiswaan/delete_organisasi/'.$org['id']) ?>" onclick="return confirm('Hapus pengurus ini?')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="mt-4 flex justify-end" id="pagination-table-organisasi"></div>
    </div>
</div>