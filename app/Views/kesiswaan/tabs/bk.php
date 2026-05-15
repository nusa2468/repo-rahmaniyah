<?php
    $jenjang    = $jenjang ?? ''; 
    $isGlobal   = in_array(strtoupper($jenjang), ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']);
    $filterUnit = service('request')->getGet('filter_unit');
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- List -->
    <div class="lg:col-span-2 bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-800">Riwayat Kasus Siswa</h2>
        </div>
        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2 custom-scrollbar">
            <?php if(empty($bk_list)): ?>
                <div class="text-center py-10 text-slate-400 italic">Belum ada catatan kasus.</div>
            <?php else: ?>
                <?php foreach($bk_list as $kasus): 
                    if($isGlobal && $filterUnit && $kasus['kode_jenjang'] !== $filterUnit) continue;
                    $isPrestasi = $kasus['jenis'] == 'Prestasi';
                    $bgClass = $isPrestasi ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100';
                    $poinSign = $isPrestasi ? '+' : '-';
                ?>
                <div class="p-5 rounded-2xl border <?= $bgClass ?> flex justify-between items-start group relative">
                    <div class="flex items-start gap-4">
                        <?php if($isGlobal): ?>
                        <div class="flex-shrink-0">
                            <span class="px-2 py-1 bg-slate-200 text-slate-700 rounded text-xs font-bold"><?= $kasus['kode_jenjang'] ?></span>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div class="font-bold text-slate-800"><?= $kasus['nama_lengkap'] ?></div>
                            <div class="text-sm text-slate-600 mt-1"><?= $kasus['nama_kasus'] ?></div>
                            <div class="text-xs text-slate-400 mt-2"><?= date('d M Y', strtotime($kasus['tanggal_kejadian'])) ?></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-black text-2xl <?= $isPrestasi ? 'text-emerald-700' : 'text-rose-700' ?>"><?= $poinSign ?><?= $kasus['poin'] ?></span>
                    </div>
                    <div class="absolute top-4 right-14 hidden group-hover:block">
                        <button onclick='editKasus(<?= htmlspecialchars(json_encode($kasus), ENT_QUOTES, 'UTF-8') ?>)' class="text-amber-500 hover:bg-amber-100 p-1 rounded"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg></button>
                        <a href="<?= base_url('app/kesiswaan/delete_kasus_bk/'.$kasus['id']) ?>" onclick="return confirm('Hapus?')" class="text-rose-500 hover:bg-rose-100 p-1 rounded"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- Mini View Kategori -->
    <div class="bg-slate-800 text-white p-8 rounded-3xl shadow-xl h-fit">
        <h3 class="text-lg font-bold mb-4">Referensi Poin</h3>
        <div class="space-y-3 text-sm opacity-90">
            <?php foreach($kategori_bk as $kat): ?>
            <li class="flex justify-between border-b border-slate-700 pb-2">
                <span>
                    <?php if($isGlobal): ?>[<?= $kat['kode_jenjang'] ?>] <?php endif; ?>
                    <?= $kat['nama_kasus'] ?>
                </span>
                <span class="bg-slate-700 px-2 rounded font-bold"><?= $kat['poin'] ?></span>
            </li>
            <?php endforeach; ?>
        </div>
    </div>
</div>