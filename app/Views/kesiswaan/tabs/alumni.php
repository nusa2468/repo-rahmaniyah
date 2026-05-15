<?php
    $jenjang    = $jenjang ?? ''; 
    $isGlobal   = in_array(strtoupper($jenjang), ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']);
    $filterUnit = service('request')->getGet('filter_unit');
?>

<div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">Tracer Study Alumni</h2>
        <!-- Simple Search for Alumni Grid -->
        <div class="relative">
            <input type="text" id="search-alumni" placeholder="Cari nama alumni..." class="pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-sm w-full sm:w-64 focus:ring-2 focus:ring-emerald-500 outline-none transition" onkeyup="filterAlumniGrid()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-2.5 text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </div>
    </div>

    <?php if(empty($alumni_list)): ?>
        <div class="text-center py-10 text-slate-400 italic">Belum ada data alumni.</div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-4" id="alumni-grid">
            <?php foreach($alumni_list as $alumni): 
                if($isGlobal && $filterUnit && $alumni['kode_jenjang'] !== $filterUnit) continue;
            ?>
            <div class="alumni-card p-6 rounded-2xl border border-slate-100 bg-white group relative transition-all hover:shadow-md" data-name="<?= strtolower($alumni['nama_lengkap']) ?>">
                <div class="flex justify-between items-center">
                    <div class="flex gap-3 items-center">
                        <?php if($isGlobal): ?>
                        <span class="px-2 py-1 bg-slate-200 text-slate-700 rounded text-xs font-bold"><?= $alumni['kode_jenjang'] ?></span>
                        <?php endif; ?>
                        <div>
                            <h4 class="font-bold text-lg text-slate-800"><?= $alumni['nama_lengkap'] ?></h4>
                            <p class="text-sm text-slate-500">Lulusan <?= $alumni['tahun_lulus'] ?></p>
                        </div>
                    </div>
                    <span class="px-4 py-1 rounded-full text-sm font-bold bg-slate-100 text-slate-600"><?= $alumni['status_kegiatan'] ?></span>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-50 text-sm text-slate-600 grid grid-cols-2">
                    <div><?= $alumni['nama_instansi'] ?></div>
                    <div><?= $alumni['jabatan_jurusan'] ?></div>
                </div>
                <div class="absolute top-4 right-4 hidden group-hover:flex gap-2">
                    <button onclick='editAlumni(<?= htmlspecialchars(json_encode($alumni), ENT_QUOTES, 'UTF-8') ?>)' class="text-emerald-600"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg></button>
                    <a href="<?= base_url('app/kesiswaan/delete_alumni/'.$alumni['id']) ?>" onclick="return confirm('Hapus?')" class="text-rose-500"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div id="alumni-no-result" class="hidden text-center py-10 text-slate-400 italic">Tidak ditemukan alumni dengan nama tersebut.</div>
    <?php endif; ?>
</div>

<script>
function filterAlumniGrid() {
    const input = document.getElementById('search-alumni');
    const filter = input.value.toLowerCase();
    const cards = document.querySelectorAll('.alumni-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        if (name.includes(filter)) {
            card.style.display = "";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    const noResult = document.getElementById('alumni-no-result');
    if (visibleCount === 0 && cards.length > 0) {
        noResult.classList.remove('hidden');
    } else {
        noResult.classList.add('hidden');
    }
}
</script>