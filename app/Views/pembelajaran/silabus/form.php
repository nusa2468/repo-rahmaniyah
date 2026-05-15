<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
    $isEdit = isset($silabus);
    
    // DATA KUNCI: Convert array PHP ke JSON agar bisa dibaca JavaScript
    // Ini berisi semua mapel (SD, SMP, SMA) yang nanti akan difilter
    $jsonMapel = json_encode($mapel ?? []);
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                <?= $isEdit ? 'Edit Silabus' : 'Input Silabus Baru' ?>
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                <?= $isEdit ? 'Mode Edit: Data dikunci.' : 'Pilih Jenjang terlebih dahulu untuk memfilter Kelas & Mapel.' ?>
            </p>
        </div>
        <a href="<?= base_url('app/pembelajaran/silabus') ?>" class="text-gray-500 hover:text-gray-700 font-medium flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>

    <!-- Error Message -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded relative mb-6 shadow-sm">
            <span class="block sm:inline font-bold"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= $isEdit ? base_url('app/pembelajaran/silabus/update-bulk/'.$silabus['id']) : base_url('app/pembelajaran/silabus/create-bulk') ?>" method="post" id="silabusForm">
        <?= csrf_field() ?>
        <?php if($isEdit): ?>
            <input type="hidden" name="header_id" value="<?= $silabus['id'] ?>">
        <?php endif; ?>

        <!-- BAGIAN 1: IDENTITAS (Auto Filter Area) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-bold text-indigo-800 dark:text-indigo-300 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                A. Identitas Umum
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- 1. JENJANG (Pemicu Filter) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jenjang <span class="text-red-500">*</span></label>
                    <?php if($isEdit): ?>
                        <input type="text" value="<?= $silabus['kode_jenjang'] ?>" class="w-full px-4 py-2 bg-gray-100 border rounded-lg text-gray-600 font-bold" readonly>
                        <input type="hidden" name="kode_jenjang" id="kode_jenjang" value="<?= $silabus['kode_jenjang'] ?>">
                    <?php else: ?>
                        <!-- PERHATIKAN: onchange="handleJenjangChange()" -->
                        <select name="kode_jenjang" id="kode_jenjang" onchange="handleJenjangChange()" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                            <option value="" disabled selected>-- Pilih Jenjang --</option>
                            <option value="SD" <?= (old('kode_jenjang') == 'SD') ? 'selected' : '' ?>>SD / MI</option>
                            <option value="SMP" <?= (old('kode_jenjang') == 'SMP') ? 'selected' : '' ?>>SMP / MTS</option>
                            <option value="SMA" <?= (old('kode_jenjang') == 'SMA') ? 'selected' : '' ?>>SMA / SMK / MA</option>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- 2. TINGKAT KELAS (Otomatis berubah) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tingkat Kelas <span class="text-red-500">*</span></label>
                    <?php if($isEdit): ?>
                        <input type="text" value="Kelas <?= $silabus['tingkat_kelas'] ?>" class="w-full px-4 py-2 bg-gray-100 border rounded-lg text-gray-600 font-bold" readonly>
                        <input type="hidden" name="tingkat_kelas" id="tingkat_kelas" value="<?= $silabus['tingkat_kelas'] ?>">
                    <?php else: ?>
                        <select name="tingkat_kelas" id="tingkat_kelas" onchange="autoDetectFase()" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none bg-gray-50" required>
                            <option value="">-- Pilih Jenjang Dahulu --</option>
                        </select>
                    <?php endif; ?>
                </div>

                <!-- 3. MATA PELAJARAN (Otomatis berubah) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <?php if($isEdit): ?>
                        <input type="text" value="<?= $silabus['nama_mapel'] ?? '-' ?>" class="w-full px-4 py-2 bg-gray-100 border rounded-lg text-gray-600 font-bold" readonly>
                        <input type="hidden" name="mata_pelajaran_id" value="<?= $silabus['mata_pelajaran_id'] ?>">
                    <?php else: ?>
                        <select name="mata_pelajaran_id" id="mata_pelajaran_id" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none bg-gray-50" required>
                            <option value="">-- Pilih Jenjang Dahulu --</option>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ... Sisa Form Header (Tahun, Semester, Kurikulum) Tetap Sama ... -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kurikulum</label>
                    <?php if($isEdit): ?>
                        <input type="text" value="<?= $silabus['jenis_kurikulum'] ?>" class="w-full px-4 py-2 bg-gray-100 border rounded-lg" readonly>
                        <input type="hidden" name="jenis_kurikulum" id="jenis_kurikulum" value="<?= $silabus['jenis_kurikulum'] ?>">
                    <?php else: ?>
                        <select name="jenis_kurikulum" id="jenis_kurikulum" onchange="toggleCurriculumUI()" class="w-full px-4 py-2 rounded-lg border border-gray-300 outline-none">
                            <option value="Merdeka">Kurikulum Merdeka</option>
                            <option value="K13">Kurikulum 2013</option>
                        </select>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" value="<?= $isEdit ? $silabus['tahun_ajaran'] : '2024/2025' ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 outline-none" <?= $isEdit ? 'readonly' : '' ?>>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                    <select name="semester" class="w-full px-4 py-2 rounded-lg border border-gray-300 outline-none" <?= $isEdit ? 'disabled' : '' ?>>
                        <option value="Ganjil" <?= ($isEdit && $silabus['semester']=='Ganjil') ? 'selected' : '' ?>>Ganjil</option>
                        <option value="Genap" <?= ($isEdit && $silabus['semester']=='Genap') ? 'selected' : '' ?>>Genap</option>
                    </select>
                    <?php if($isEdit): ?><input type="hidden" name="semester" value="<?= $silabus['semester'] ?>"><?php endif; ?>
                </div>
            </div>

            <!-- FASE (Otomatis) & TEMA (K13) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="field-merdeka">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fase (Otomatis)</label>
                    <input type="text" name="fase" id="fase" value="<?= $isEdit ? $silabus['fase'] : '' ?>" class="w-full px-4 py-2 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 font-bold outline-none" readonly placeholder="-">
                </div>
                
                <div class="field-k13 hidden grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tema</label>
                        <input type="text" name="tema" value="<?= old('tema', $isEdit ? $silabus['tema'] : '') ?>" class="w-full px-4 py-2 rounded-lg border border-amber-300 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subtema</label>
                        <input type="text" name="subtema" value="<?= old('subtema', $isEdit ? $silabus['subtema'] : '') ?>" class="w-full px-4 py-2 rounded-lg border border-amber-300 outline-none">
                    </div>
                </div>
            </div>

            <!-- CAPAIAN PEMBELAJARAN / KI -->
            <div class="mt-6 space-y-4">
                <div class="field-merdeka">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Capaian Pembelajaran (CP) - <small>Lingkup Materi</small></label>
                    <textarea name="capaian_pembelajaran" rows="2" class="w-full px-4 py-2 rounded-lg border border-gray-300 outline-none"><?= old('capaian_pembelajaran', $isEdit ? $silabus['capaian_pembelajaran'] : '') ?></textarea>
                </div>
                <div class="field-merdeka">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Profil Pelajar Pancasila</label>
                    <input type="text" name="profil_pelajar_pancasila" value="<?= old('profil_pelajar_pancasila', $isEdit ? $silabus['profil_pelajar_pancasila'] : '') ?>" class="w-full px-4 py-2 rounded-lg border border-gray-300 outline-none">
                </div>
                <div class="field-k13 hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kompetensi Inti (KI)</label>
                    <textarea name="kompetensi_inti" rows="3" class="w-full px-4 py-2 rounded-lg border border-amber-300 outline-none"><?= old('kompetensi_inti', $isEdit ? $silabus['kompetensi_inti'] : '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: DETAIL KOMPETENSI (Repeater) -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">B. Rincian Materi & Kegiatan</h3>
                <button type="button" onclick="addNewRow()" class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow font-bold text-sm flex items-center hover:bg-emerald-700">
                    <span class="text-xl mr-1">+</span> Tambah Kompetensi
                </button>
            </div>

            <div id="rows-container" class="space-y-4"></div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 shadow-lg z-50">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <span class="text-sm text-gray-500 font-bold" id="row-counter">Total: 0 Kompetensi</span>
                    <div class="flex gap-4">
                        <a href="<?= base_url('app/pembelajaran/silabus') ?>" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700">Simpan Semua</button>
                    </div>
                </div>
            </div>
            <div class="h-24"></div>
        </div>
    </form>
</div>

<!-- JAVASCRIPT LOGIC (THE FIX) -->
<script>
    // 1. Data Mapel dari PHP (JSON)
    const allMapel = <?= $jsonMapel ?>; 
    let rowCount = 0;

    // 2. Mapping Kelas per Jenjang
    const kelasMapping = {
        'SD':  [1, 2, 3, 4, 5, 6],
        'SMP': [7, 8, 9],
        'SMA': [10, 11, 12]
    };

    // -----------------------------------------------------------
    // FUNGSI UTAMA: FILTER DROPDOWN BERDASARKAN JENJANG
    // -----------------------------------------------------------
    function handleJenjangChange() {
        const jenjangSelect = document.getElementById('kode_jenjang');
        const kelasSelect = document.getElementById('tingkat_kelas');
        const mapelSelect = document.getElementById('mata_pelajaran_id');
        
        if (!jenjangSelect || !kelasSelect || !mapelSelect) return;

        const selectedJenjang = jenjangSelect.value;

        // A. Reset & Isi Ulang Kelas
        kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
        kelasSelect.style.backgroundColor = '#fff'; // Reset warna

        if (kelasMapping[selectedJenjang]) {
            kelasMapping[selectedJenjang].forEach(kelas => {
                const option = new Option(`Kelas ${kelas}`, kelas);
                kelasSelect.add(option);
            });
        }

        // B. Reset & Isi Ulang Mapel (Filter by kode_jenjang)
        mapelSelect.innerHTML = '<option value="">-- Pilih Mata Pelajaran --</option>';
        mapelSelect.style.backgroundColor = '#fff';

        // Filter array 'allMapel' yang kode_jenjang-nya cocok
        const filteredMapel = allMapel.filter(m => m.kode_jenjang === selectedJenjang);
        
        if (filteredMapel.length > 0) {
            filteredMapel.forEach(m => {
                // Tampilkan Nama Mapel + Kode Mapel
                const label = `${m.nama_mapel} (${m.kode_mapel || '-'})`;
                const option = new Option(label, m.id);
                mapelSelect.add(option);
            });
        } else {
            const option = new Option(`Tidak ada mapel untuk ${selectedJenjang}`, '');
            mapelSelect.add(option);
        }
    }

    // -----------------------------------------------------------
    // FUNGSI FASE OTOMATIS
    // -----------------------------------------------------------
    function autoDetectFase() {
        const kelasInput = document.getElementById('tingkat_kelas');
        const faseInput = document.getElementById('fase');
        if (!kelasInput || !faseInput) return;

        let kelas = parseInt(kelasInput.value);
        let fase = "-";

        if (kelas >= 1 && kelas <= 2) fase = "A";
        else if (kelas >= 3 && kelas <= 4) fase = "B";
        else if (kelas >= 5 && kelas <= 6) fase = "C";
        else if (kelas >= 7 && kelas <= 9) fase = "D";
        else if (kelas == 10) fase = "E";
        else if (kelas >= 11 && kelas <= 12) fase = "F";

        faseInput.value = fase;
    }

    // -----------------------------------------------------------
    // FUNGSI LAINNYA (Repeater & Kurikulum UI)
    // -----------------------------------------------------------
    function toggleCurriculumUI() {
        const type = document.getElementById('jenis_kurikulum')?.value || 'Merdeka';
        const merdekaEls = document.querySelectorAll('.field-merdeka, .merdeka-label, .field-merdeka-row');
        const k13Els = document.querySelectorAll('.field-k13, .k13-label, .field-k13-row');

        if (type === 'Merdeka') {
            merdekaEls.forEach(el => el.classList.remove('hidden'));
            k13Els.forEach(el => el.classList.add('hidden'));
        } else {
            merdekaEls.forEach(el => el.classList.add('hidden'));
            k13Els.forEach(el => el.classList.remove('hidden'));
        }
    }

    function addNewRow(data = null) {
        const container = document.getElementById('rows-container');
        const idx = rowCount++;
        
        const html = `
        <div class="silabus-row bg-white border border-gray-200 rounded-xl mb-4 overflow-hidden shadow-sm">
            <div class="bg-gray-50 px-6 py-3 border-b flex justify-between items-center">
                <span class="font-bold text-gray-700">Kompetensi #${idx + 1}</span>
                <button type="button" onclick="this.closest('.silabus-row').remove(); updateCounter();" class="text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="col-span-1">
                    <label class="block text-xs font-bold text-gray-500 mb-1">
                        <span class="merdeka-label">Tujuan Pembelajaran (ATP)</span>
                        <span class="k13-label hidden">Kompetensi Dasar (KD)</span>
                    </label>
                    <div class="field-merdeka-row">
                        <textarea name="items[${idx}][alur_tujuan_pembelajaran]" rows="4" class="w-full p-2 border rounded text-sm">${data?.alur_tujuan_pembelajaran || data?.atp || ''}</textarea>
                    </div>
                    <div class="field-k13-row hidden">
                        <textarea name="items[${idx}][kompetensi_dasar]" rows="4" class="w-full p-2 border border-amber-300 rounded text-sm">${data?.kompetensi_dasar || data?.kd || ''}</textarea>
                    </div>
                </div>
                <div class="col-span-1 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Materi Pokok</label>
                        <input type="text" name="items[${idx}][materi_pokok]" value="${data?.materi_pokok || ''}" class="w-full p-2 border rounded text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">
                            <span class="merdeka-label">Indikator (IKTP)</span>
                            <span class="k13-label hidden">Indikator (IPK)</span>
                        </label>
                        <textarea name="items[${idx}][indikator]" rows="2" class="w-full p-2 border rounded text-sm">${data?.indikator || ''}</textarea>
                    </div>
                </div>
                <div class="col-span-1">
                     <label class="block text-xs font-bold text-gray-500 mb-1">Kegiatan Pembelajaran</label>
                     <textarea name="items[${idx}][kegiatan_pembelajaran]" rows="4" class="w-full p-2 border rounded text-sm">${data?.kegiatan_pembelajaran || ''}</textarea>
                </div>
                <div class="col-span-1 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1">Penilaian</label>
                        <textarea name="items[${idx}][penilaian]" rows="2" class="w-full p-2 border rounded text-sm">${data?.penilaian || ''}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 mb-1">Waktu</label>
                            <input type="text" name="items[${idx}][alokasi_waktu]" value="${data?.alokasi_waktu || ''}" class="w-full p-2 border rounded text-sm" placeholder="4 JP">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 mb-1">Sumber</label>
                            <input type="text" name="items[${idx}][sumber_belajar]" value="${data?.sumber_belajar || ''}" class="w-full p-2 border rounded text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>`;

        container.insertAdjacentHTML('beforeend', html);
        toggleCurriculumUI();
        updateCounter();
    }

    function updateCounter() {
        const count = document.querySelectorAll('.silabus-row').length;
        document.getElementById('row-counter').innerText = `Total: ${count} Kompetensi`;
    }

    // INIT
    document.addEventListener('DOMContentLoaded', () => {
        // Load data jika edit atau previous input
        <?php if(!empty($silabus_details)): ?>
            <?= json_encode($silabus_details) ?>.forEach(item => addNewRow(item));
        <?php else: ?>
            addNewRow();
        <?php endif; ?>

        // Setup UI
        toggleCurriculumUI();
        <?php if($isEdit): ?> autoDetectFase(); <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>