<?php
    $jenjang    = $jenjang ?? ''; 
    $isGlobal   = in_array(strtoupper($jenjang), ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']);
    $filterUnit = service('request')->getGet('filter_unit');
?>

<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">Riwayat Presensi Kegiatan</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse paginated-table" id="table-presensi" data-per-page="10">
            <thead>
                <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider font-semibold">
                    <th class="pb-4 pl-4 w-12">No</th>
                    <?php if($isGlobal): ?><th class="pb-4 pl-4">Unit</th><?php endif; ?>
                    <th class="pb-4 pl-4">Tanggal</th>
                    <th class="pb-4">Ekskul</th>
                    <th class="pb-4">Materi Kegiatan</th>
                    <th class="pb-4">Kehadiran (Detail)</th> 
                    <th class="pb-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if(empty($presensi_list)): ?>
                    <tr><td colspan="<?= $isGlobal ? 7 : 6 ?>" class="py-8 text-center text-slate-400 italic">Belum ada data presensi.</td></tr>
                <?php else: ?>
                    <?php $no = 1; foreach($presensi_list as $pre): ?>
                    <?php if($isGlobal && $filterUnit && $pre['kode_jenjang'] !== $filterUnit) continue; ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="py-4 pl-4 text-slate-500 font-medium"><?= $no++ ?></td>
                        <?php if($isGlobal): ?>
                            <td class="py-4 pl-4">
                                <span class="px-2 py-1 bg-slate-200 text-slate-700 rounded text-xs font-bold"><?= $pre['kode_jenjang'] ?></span>
                            </td>
                        <?php endif; ?>
                        <td class="py-4 pl-4 text-slate-600 font-mono"><?= date('d/m/Y', strtotime($pre['tanggal'])) ?></td>
                        <td class="py-4"><span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold"><?= $pre['nama_ekskul'] ?></span></td>
                        <td class="py-4 text-slate-700"><?= $pre['materi_kegiatan'] ?></td>
                        <td class="py-4 text-sm text-slate-600">
                            <?php 
                                $dataPresensi = json_decode($pre['data_presensi'] ?? '[]', true);
                                $stats = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
                                $hadirList = [];
                                if(is_array($dataPresensi)) {
                                    foreach($dataPresensi as $dp) {
                                        $st = $dp['status'] ?? 'A';
                                        if(isset($stats[$st])) $stats[$st]++;
                                        // Variabel $siswaMap tidak tersedia disini jika di-include
                                        // Solusi: Kita pakai ID saja atau logic sederhana
                                        // Untuk nama lengkap, idealnya parsing di controller
                                        if($st == 'H') $hadirList[] = 'Siswa #'.$dp['siswa_id'];
                                    }
                                }
                            ?>
                            <div class="flex gap-2 mb-1">
                                <span class="text-emerald-600 font-bold" title="Hadir">H: <?= $stats['H'] ?></span>
                                <span class="text-blue-600 font-bold" title="Izin">I: <?= $stats['I'] ?></span>
                                <span class="text-amber-600 font-bold" title="Sakit">S: <?= $stats['S'] ?></span>
                                <span class="text-rose-600 font-bold" title="Alpha">A: <?= $stats['A'] ?></span>
                            </div>
                        </td>
                        <td class="py-4 text-center flex justify-center gap-2">
                            <button onclick='editPresensi(<?= htmlspecialchars(json_encode($pre), ENT_QUOTES, 'UTF-8') ?>)' class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg></button>
                            <a href="<?= base_url('app/kesiswaan/delete_presensi_ekskul/'.$pre['id']) ?>" onclick="return confirm('Hapus data presensi ini?')" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="mt-4 flex justify-end" id="pagination-table-presensi"></div>
    </div>
</div>