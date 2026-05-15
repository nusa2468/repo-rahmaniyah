<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<?php 
    // --- INITIALIZATION ---
    $request = \Config\Services::request();
    
    // --- LOGIKA PROTEKSI SCOPE (Sesuai HakAksesModel) ---
    $sessionUnit = session()->get('kode_jenjang');
    $isGlobal = (empty($sessionUnit) || strtoupper($sessionUnit) === 'GLOBAL');
    
    // Tentukan unit yang sedang aktif untuk label UI
    $displayUnit = ($current_unit === 'Global' || empty($current_unit)) ? 'Semua Unit' : 'Unit ' . esc($current_unit);
    
    // Handle data untuk statistik
    $totalRecords = count($list_nilai ?? []);
    $tahunAjaranAktif = $tahun_ajaran_aktif ?? null;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <div>
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/akademik/dashboard') ?>" class="hover:text-indigo-600 transition-colors">AKADEMIK</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 italic">MANAJEMEN NILAI</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 uppercase italic leading-none">
                Entry <span class="text-indigo-600">Nilai Siswa</span>
            </h1>
        </div>

        <?php if ($tahunAjaranAktif): ?>
        <div class="bg-white dark:bg-slate-800 px-6 py-3 rounded-2xl border-2 border-slate-100 dark:border-white/5 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Periode Aktif</p>
                <p class="text-sm font-black text-slate-800 dark:text-white uppercase italic"><?= esc($tahunAjaranAktif['tahun_ajaran']) ?> (<?= esc($tahunAjaranAktif['semester']) ?>)</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- FITUR NAVIGASI TAB MODUL AKADEMIK -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar mb-8 border border-slate-200 dark:border-white/5 shadow-inner">
        <a href="<?= base_url('app/akademik/kalender') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-calendar-day mr-2 opacity-50"></i> Kalender
        </a>
        <a href="<?= base_url('app/akademik/jadwalpelajaran') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-clock mr-2 opacity-50"></i> Jadwal
        </a>
        <a href="<?= base_url('app/akademik/absensi-siswa') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-user-check mr-2 opacity-50"></i> Presensi
        </a>
        <a href="<?= base_url('app/akademik/nilai') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            <i class="fas fa-star mr-2"></i> Nilai
        </a>
        <a href="<?= base_url('app/akademik/rapor') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-file-contract mr-2 opacity-50"></i> E-Rapor
        </a>
        <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-rocket mr-2 opacity-50"></i> Kenaikan
        </a>
    </div>

    <?php if (!$tahunAjaranAktif) : ?>
        <div class="bg-rose-50 dark:bg-rose-900/20 border-l-8 border-rose-500 p-8 rounded-r-[2rem] shadow-xl mb-6 flex items-start gap-6 animate-pulse">
            <i class="fas fa-exclamation-triangle text-rose-500 text-4xl mt-1"></i>
            <div>
                <h3 class="text-lg font-black text-rose-900 dark:text-rose-400 uppercase tracking-widest italic">Sistem Entry Terkunci</h3>
                <p class="text-sm text-rose-700 dark:text-rose-300 mt-1 font-bold uppercase tracking-tight">
                    Tidak ditemukan Tahun Ajaran yang berstatus <strong>AKTIF</strong>. Harap hubungi Admin Kurikulum.
                </p>
            </div>
        </div>
    <?php else : ?>

        <!-- 1. KARTU STATISTIK (PREMIUM SOLID STYLE) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-indigo-600 rounded-3xl shadow-xl shadow-indigo-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-indigo-900">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200 opacity-80 leading-none">Masa Akademik</p>
                    <h3 class="text-2xl font-black mt-2 italic"><?= esc($tahunAjaranAktif['tahun_ajaran']) ?></h3>
                    <span class="inline-block mt-3 px-3 py-1 bg-white/20 rounded-lg text-[9px] font-black uppercase tracking-widest"><?= esc($tahunAjaranAktif['semester']) ?></span>
                </div>
                <i class="fas fa-calendar-check absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
            </div>

            <div class="bg-emerald-600 rounded-3xl shadow-xl shadow-emerald-100 dark:shadow-none p-6 text-white relative overflow-hidden group border-b-4 border-emerald-900">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-100 opacity-80 leading-none">Otoritas Scope</p>
                    <h3 class="text-2xl font-black mt-2 italic"><?= strtoupper($displayUnit) ?></h3>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                        <span class="text-[10px] font-bold text-emerald-100 uppercase tracking-widest italic"><?= $isGlobal ? 'Global Access' : 'Unit Restricted' ?></span>
                    </div>
                </div>
                <i class="fas fa-shield-alt absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
            </div>

            <div class="bg-slate-900 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group border-b-4 border-indigo-600">
                <div class="relative z-10">
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none">Data Terdaftar</p>
                    <h3 class="text-3xl font-black mt-2 italic"><?= number_format($totalRecords) ?></h3>
                    <p class="mt-4 text-[9px] font-bold text-slate-500 uppercase tracking-widest italic opacity-60">Entri Penilaian</p>
                </div>
                <i class="fas fa-database absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- FORM INPUT (SIDEBAR) -->
            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-sm border-2 border-slate-100 dark:border-white/5 overflow-hidden sticky top-24 transition-all hover:shadow-xl hover:shadow-slate-200/50">
                    <div class="bg-slate-50 dark:bg-white/5 px-8 py-6 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
                        <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest italic leading-none">
                            <i class="fas fa-edit mr-3 text-indigo-600"></i> Kelola Nilai Baru
                        </h3>
                    </div>
                    <form action="<?= site_url('app/akademik/nilai/kelola') ?>" method="get" class="p-8 space-y-8">
                        
                        <!-- SELECT KELAS -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Rombongan Belajar</label>
                            <div class="relative group">
                                <select name="id_kelas" id="pilih_kelas" required 
                                        onchange="filterMapelByTingkat()"
                                        class="w-full pl-10 pr-4 py-4 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-white/10 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-700 dark:text-white focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                    <option value="">-- PILIH KELAS --</option>
                                    <?php foreach ($kelas as $item) : ?>
                                        <?php 
                                            // Proteksi List Kelas sesuai Unit Admin
                                            if (!$isGlobal && strtoupper($item['kode_jenjang']) !== strtoupper($sessionUnit)) continue;
                                        ?>
                                        <option value="<?= esc($item['id']) ?>" 
                                                data-tingkat="<?= esc($item['tingkat']) ?>" 
                                                data-unit="<?= esc($item['kode_jenjang']) ?>">
                                            <?= esc($item['nama_kelas']) ?> [<?= esc($item['kode_jenjang']) ?> - TK.<?= esc($item['tingkat']) ?>]
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                    <i class="fas fa-door-open text-xs"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-300">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- SELECT MAPEL -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Mata Pelajaran</label>
                            <div class="relative group">
                                <select name="id_mata_pelajaran" id="pilih_mapel" required disabled
                                        class="w-full pl-10 pr-4 py-4 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-white/10 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-700 dark:text-white focus:border-indigo-500 transition-all appearance-none cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                    <option value="">-- PILIH KELAS DULU --</option>
                                    <?php foreach ($mapel as $item) : ?>
                                        <?php 
                                            if (!$isGlobal && strtoupper($item['kode_jenjang'] ?? '') !== strtoupper($sessionUnit)) continue;
                                        ?>
                                        <option value="<?= esc($item['id']) ?>" 
                                                data-tingkat="<?= esc($item['tingkat'] ?? '') ?>" 
                                                data-unit="<?= esc($item['kode_jenjang'] ?? '') ?>"
                                                class="mapel-option hidden">
                                            <?= esc($item['nama_mapel']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                    <i class="fas fa-book text-xs"></i>
                                </div>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-300">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            <p id="mapel_hint" class="text-[9px] font-bold text-indigo-500 mt-2 italic ml-1">Otomatis sinkron dengan tingkat kelas.</p>
                        </div>

                        <!-- SELECT SEMESTER -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Periode Semester</label>
                            <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-inner">
                                <label class="flex-1 cursor-pointer group">
                                    <input type="radio" name="semester" value="Ganjil" class="peer hidden" <?= ($tahunAjaranAktif['semester'] == 'Ganjil') ? 'checked' : '' ?>>
                                    <div class="py-3 text-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-indigo-600 peer-checked:shadow-sm">Ganjil</div>
                                </label>
                                <label class="flex-1 cursor-pointer group">
                                    <input type="radio" name="semester" value="Genap" class="peer hidden" <?= ($tahunAjaranAktif['semester'] == 'Genap') ? 'checked' : '' ?>>
                                    <div class="py-3 text-center rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-indigo-600 peer-checked:shadow-sm">Genap</div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-5 px-6 rounded-2xl shadow-xl shadow-indigo-100 dark:shadow-none transition-all transform hover:scale-[1.02] active:scale-95 flex items-center justify-center text-[10px] uppercase tracking-[0.2em] border-b-4 border-indigo-800">
                            BUKA LEMBAR PENILAIAN <i class="fas fa-chevron-right ml-3 text-[8px]"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- RIWAYAT NILAI (MAIN TABLE) -->
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] shadow-sm border-2 border-slate-100 dark:border-white/5 overflow-hidden flex flex-col min-h-[500px]">
                    
                    <div class="px-8 py-6 border-b border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-white/5">
                        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6">
                            
                            <!-- UNIT TABS (RESTRICTED) -->
                            <div class="flex items-center p-1.5 bg-slate-200 dark:bg-slate-900 rounded-2xl w-fit border border-slate-300 dark:border-slate-700">
                                <?php 
                                    $availableUnits = $isGlobal ? ['Global' => 'All Units', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA'] : [$sessionUnit => 'Unit '.strtoupper($sessionUnit)];
                                    foreach($availableUnits as $code => $label): 
                                        $isActive = ($current_unit == $code);
                                ?>
                                    <a href="?unit=<?= $code ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $isActive ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-md' : 'text-slate-500 hover:text-slate-800' ?>">
                                        <?= $label ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <form action="" method="get" class="relative group min-w-[280px]">
                                <input type="hidden" name="unit" value="<?= esc($current_unit) ?>">
                                <input type="text" name="keyword" value="<?= esc($keyword) ?>" 
                                       placeholder="Cari siswa atau mata pelajaran..." 
                                       class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-100 dark:border-white/10 rounded-2xl text-[11px] font-bold uppercase tracking-widest focus:border-indigo-500 transition-all outline-none italic">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-300 group-focus-within:text-indigo-500">
                                    <i class="fas fa-search text-xs"></i>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="flex-grow overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900 text-white italic">
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Identitas Siswa</th>
                                    <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Rombel</th>
                                    <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest">Mata Pelajaran</th>
                                    <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Smt</th>
                                    <th class="px-8 py-5 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Nilai Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-white/5 text-[11px]">
                                <?php if (empty($list_nilai)) : ?>
                                    <tr>
                                        <td colspan="5" class="px-8 py-32 text-center opacity-30">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-folder-open text-6xl mb-4"></i>
                                                <p class="text-sm font-black uppercase tracking-widest italic">Belum Ada Riwayat Penilaian</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($list_nilai as $row) : ?>
                                        <tr class="hover:bg-indigo-50/30 dark:hover:bg-white/[0.02] transition-all group">
                                            <td class="px-8 py-5">
                                                <div class="flex flex-col">
                                                    <span class="text-[13px] font-black text-slate-800 dark:text-slate-100 uppercase italic leading-none group-hover:text-indigo-600 transition-colors"><?= esc($row['nama_siswa']) ?></span>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1.5">NIS: <?= esc($row['nis']) ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <span class="inline-block px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[9px] font-black rounded-lg uppercase tracking-tighter border border-slate-200 dark:border-slate-600 shadow-sm">
                                                    <?= esc($row['nama_kelas']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="text-[11px] font-black text-slate-700 dark:text-slate-300 uppercase leading-none italic"><?= esc($row['nama_mapel']) ?></div>
                                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1.5 flex items-center gap-1.5">
                                                    <i class="fas fa-user-tie text-[8px]"></i> <?= esc($row['nama_guru'] ?? '-') ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <span class="text-[10px] font-black uppercase italic text-slate-500"><?= esc($row['semester']) ?></span>
                                            </td>
                                            <td class="px-8 py-5 text-center">
                                                <?php 
                                                    $nilai = floatval($row['nilai_akhir']);
                                                    $statusColor = ($nilai < 75) ? 'bg-rose-100 text-rose-700 border-rose-200 shadow-rose-100' : (($nilai >= 90) ? 'bg-emerald-100 text-emerald-700 border-emerald-200 shadow-emerald-100' : 'bg-indigo-100 text-indigo-700 border-indigo-200 shadow-indigo-100');
                                                ?>
                                                <div class="inline-flex items-center justify-center w-14 h-12 rounded-2xl <?= $statusColor ?> font-black text-base shadow-lg border-2 transform group-hover:scale-110 transition-transform">
                                                    <?= number_format($nilai, 0) ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($pager)) : ?>
                        <div class="px-10 py-8 bg-slate-50 dark:bg-white/5 border-t border-slate-100 dark:border-white/10 flex flex-col sm:flex-row items-center justify-between gap-6">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic leading-none">
                                Analisis Records: <span class="text-indigo-600"><?= $pager->getCurrentPage() ?></span> of <span class="text-slate-900 dark:text-white"><?= $pager->getPageCount() ?> Pages</span>
                            </span>
                            <div class="custom-pagination">
                                <?= $pager->links('default', 'tailwind_pagination') ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<!-- SMART FILTERING SCRIPT -->
<script>
/**
 * Logika Sinkronisasi Mata Pelajaran dengan Kelas.
 * Memastikan guru tidak salah menginput nilai mapel kelas 1 ke kelas 6.
 */
function filterMapelByTingkat() {
    const kelasSelect = document.getElementById('pilih_kelas');
    const mapelSelect = document.getElementById('pilih_mapel');
    const mapelHint   = document.getElementById('mapel_hint');
    
    const selectedOption = kelasSelect.options[kelasSelect.selectedIndex];
    const tingkat = selectedOption.getAttribute('data-tingkat');
    const unit    = selectedOption.getAttribute('data-unit');

    mapelSelect.innerHTML = '<option value="">-- PILIH MATA PELAJARAN --</option>';
    
    if (!tingkat) {
        mapelSelect.disabled = true;
        mapelHint.innerText = "Pilih kelas untuk memfilter daftar mapel.";
        mapelHint.classList.replace('text-indigo-500', 'text-slate-400');
        return;
    }

    const allMapels = [
        <?php foreach ($mapel as $item): ?>
            {
                id: "<?= $item['id'] ?>",
                nama: "<?= addslashes($item['nama_mapel']) ?>",
                tingkat: "<?= $item['tingkat'] ?? '' ?>",
                unit: "<?= $item['kode_jenjang'] ?? '' ?>"
            },
        <?php endforeach; ?>
    ];

    // Filter mapel yang sesuai Unit (SD/SMP/SMA) DAN sesuai Tingkat (1-12)
    const filteredMapels = allMapels.filter(m => {
        const unitMatch = (m.unit.toUpperCase() === unit.toUpperCase());
        const tingkatMatch = (!m.tingkat || m.tingkat == tingkat);
        return unitMatch && tingkatMatch;
    });

    if (filteredMapels.length > 0) {
        filteredMapels.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.text = m.nama.toUpperCase();
            mapelSelect.add(opt);
        });
        mapelSelect.disabled = false;
        mapelHint.innerHTML = `<i class="fas fa-check-circle text-emerald-500 mr-1"></i> Ditemukan ${filteredMapels.length} Mata Pelajaran valid.`;
        mapelHint.classList.replace('text-slate-400', 'text-emerald-500');
    } else {
        mapelSelect.disabled = true;
        mapelHint.innerHTML = `<i class="fas fa-exclamation-circle text-rose-500 mr-1"></i> Tidak ada mapel terdaftar untuk TK.${tingkat} - ${unit}.`;
        mapelHint.classList.replace('text-indigo-500', 'text-rose-500');
    }
}
</script>

<style>
    /* Premium Styling Scrollbar & Pagination */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    .custom-pagination nav ul { display: flex; gap: 0.35rem; justify-content: center; }
    .custom-pagination nav ul li a, .custom-pagination nav ul li span {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.6rem; height: 2.6rem; font-size: 10px; font-weight: 900;
        text-transform: uppercase; border: 2px solid #e2e8f0; background: white;
        border-radius: 0.85rem; transition: all 0.2s; color: #64748b;
    }
    .custom-pagination nav ul li.active span {
        background: #4f46e5; color: white; border-color: #4f46e5;
        box-shadow: 0 4px 12px -2px rgba(79, 70, 229, 0.4);
    }
    .custom-pagination nav ul li a:hover { border-color: #4f46e5; color: #4f46e5; background: #f8fafc; }
</style>

<?= $this->endSection() ?>