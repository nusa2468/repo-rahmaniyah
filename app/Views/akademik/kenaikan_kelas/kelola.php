<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-900">
    
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 bg-white dark:bg-slate-800 px-4 py-2 rounded-2xl shadow-sm border border-slate-200 dark:border-white/10">
                    <li class="inline-flex items-center">
                        <a href="<?= base_url('app/dashboard') ?>" class="text-xs font-bold text-slate-500 hover:text-indigo-600 dark:text-slate-400 transition-colors uppercase tracking-widest">
                            <i class="fas fa-home mr-2 text-[10px]"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center text-slate-400">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="text-xs font-bold uppercase tracking-widest hover:text-indigo-600 transition-colors">Kenaikan Kelas</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="text-xs font-black uppercase tracking-widest italic">Kelola Keputusan</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight uppercase italic leading-none">
                Manajemen Kenaikan: <span class="text-indigo-600 dark:text-indigo-400"><?= esc($kelas_lama['nama_kelas'] ?? 'N/A') ?></span>
            </h1>
        </div>

        <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-white/10 rounded-2xl text-xs font-black text-slate-600 dark:text-slate-300 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm group">
            <i class="fas fa-arrow-left mr-2 transition-transform group-hover:-translate-x-1"></i> KEMBALI
        </a>
    </div>

    <!-- 1. INFORMASI TRANSISI (SOLID STYLE) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Rombel Asal -->
        <div class="bg-indigo-600 rounded-3xl shadow-xl shadow-indigo-200 dark:shadow-none p-6 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200 opacity-80 leading-none">Rombel Asal</p>
                <h3 class="text-xl font-black mt-3 italic tracking-tight uppercase"><?= esc($kelas_lama['nama_kelas']) ?></h3>
                <p class="text-[10px] font-bold text-indigo-100 uppercase tracking-widest mt-1 italic"><?= esc($ta_lama['tahun_ajaran']) ?></p>
            </div>
            <i class="fas fa-door-open absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- Target Promosi -->
        <div class="bg-emerald-600 rounded-3xl shadow-xl shadow-emerald-200 dark:shadow-none p-6 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-100 opacity-80 leading-none">Target Promosi</p>
                <?php if ($is_kelas_akhir): ?>
                    <h3 class="text-xl font-black mt-3 italic tracking-tight uppercase">LULUS / TAMAT</h3>
                    <p class="text-[10px] font-bold text-emerald-50 uppercase mt-1 italic">Jenjang Akhir <?= esc($kelas_lama['kode_jenjang']) ?></p>
                <?php else: ?>
                    <h3 class="text-xl font-black mt-3 italic tracking-tight uppercase">TINGKAT <?= esc($kelas_lama['tingkat'] + 1) ?></h3>
                    <p class="text-[10px] font-bold text-emerald-50 uppercase mt-1 italic">Naik Jenjang Pendidikan</p>
                <?php endif; ?>
            </div>
            <i class="fas fa-rocket absolute -right-4 -bottom-4 text-white/10 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <!-- TA Baru -->
        <div class="bg-slate-900 rounded-3xl shadow-xl p-6 text-white relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 opacity-80 leading-none">Tahun Ajaran Baru</p>
                <h3 class="text-xl font-black mt-3 italic tracking-tight"><?= esc($ta_baru['tahun_ajaran'] ?? 'BELUM DIBUAT') ?></h3>
                <div class="mt-3">
                    <span class="px-3 py-1 bg-white/10 rounded-lg text-[9px] font-black uppercase tracking-widest border border-white/10">Data Destinasi</span>
                </div>
            </div>
            <i class="fas fa-calendar-check absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:scale-110 transition-transform duration-500"></i>
        </div>
    </div>

    <!-- 2. FORM INPUT UTAMA -->
    <?php if (empty($siswa_list)): ?>
        <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 p-24 text-center shadow-xl">
            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-user-slash text-4xl text-slate-300"></i>
            </div>
            <p class="text-sm font-black uppercase tracking-widest text-slate-400 italic">Data siswa tidak tersedia untuk diproses.</p>
        </div>
    <?php else: ?>
        <form action="<?= site_url('app/akademik/kenaikan_kelas/simpan') ?>" method="post" id="formKenaikan">
            <?= csrf_field() ?>
            <input type="hidden" name="id_kelas_lama" value="<?= esc($kelas_lama['id']) ?>">
            <input type="hidden" name="id_tahun_ajaran_lama" value="<?= esc($id_tahun_ajaran_lama) ?>">
            <input type="hidden" name="id_tahun_ajaran_baru" value="<?= esc($id_tahun_ajaran_baru) ?>">

            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden mb-10">
                
                <div class="px-8 py-6 border-b border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tgl Keputusan:</label>
                        <input type="date" name="tanggal_keputusan" value="<?= date('Y-m-d') ?>" required
                               class="px-5 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-black focus:border-indigo-500 outline-none transition-all shadow-sm">
                    </div>
                    <?php if (empty($kelas_tujuan_list) && !$is_kelas_akhir): ?>
                        <div class="bg-rose-50 dark:bg-rose-900/10 border-2 border-rose-100 dark:border-rose-900/30 p-4 rounded-2xl flex items-center gap-4 animate-pulse">
                            <i class="fas fa-exclamation-triangle text-rose-500 text-lg"></i>
                            <p class="text-[9px] font-black text-rose-700 dark:text-rose-400 uppercase tracking-tight leading-tight">
                                Peringatan: Rombel Tujuan tingkat <?= esc($kelas_lama['tingkat'] + 1) ?> belum dibuat di TA Baru!
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-white/5 border-b-2 border-slate-100 dark:border-white/10">
                                <th class="px-6 py-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest w-16">No</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Identitas Siswa</th>
                                <th class="px-6 py-6 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest w-48">Keputusan Akademik</th>
                                <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest min-w-[220px]">Target Rombel Baru (TA Baru)</th>
                                <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            <?php $no = 1; foreach ($siswa_list as $siswa) : ?>
                                <tr class="hover:bg-indigo-50/30 dark:hover:bg-white/[0.02] transition-colors group">
                                    <td class="px-6 py-5 text-center border-r border-slate-50 dark:border-white/5 bg-slate-50/30 dark:bg-white/[0.01]">
                                        <span class="text-xs font-black text-slate-300 italic"><?= $no++ ?></span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <input type="hidden" name="keputusan[<?= $siswa['siswa_id'] ?>][id_enrollment_lama]" value="<?= $siswa['enrollment_id'] ?>">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-black text-slate-800 dark:text-slate-100 tracking-tight uppercase italic group-hover:text-indigo-600 transition-colors"><?= esc($siswa['nama_siswa']) ?></span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.15em] mt-1">NIS: <?= esc($siswa['nis']) ?></span>
                                        </div>
                                    </td>

                                    <!-- Keputusan -->
                                    <td class="px-6 py-5">
                                        <select name="keputusan[<?= $siswa['siswa_id'] ?>][status]" 
                                                class="status-toggle w-full px-5 py-4 bg-slate-50 dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-2xl text-[10px] font-black uppercase tracking-widest focus:border-indigo-500 transition-all appearance-none cursor-pointer"
                                                data-siswa-id="<?= $siswa['siswa_id'] ?>">
                                            <?php if ($is_kelas_akhir): ?>
                                                <option value="Lulus" class="text-emerald-600 font-black" selected>LULUS / TAMAT</option>
                                                <option value="Tinggal" class="text-rose-600 font-black">TINGGAL KELAS</option>
                                                <option value="Mutasi" class="text-slate-500">MUTASI KELUAR</option>
                                            <?php else: ?>
                                                <option value="Naik" class="text-emerald-600 font-black" selected>NAIK KELAS</option>
                                                <option value="Tinggal" class="text-rose-600 font-black">TINGGAL KELAS</option>
                                                <option value="Lulus" class="text-indigo-600 font-black">LULUS / TAMAT</option>
                                                <option value="Mutasi" class="text-slate-500">MUTASI KELUAR</option>
                                            <?php endif; ?>
                                        </select>
                                    </td>

                                    <!-- Target Rombel (Dinamis via JS) -->
                                    <td class="px-6 py-5">
                                        <div class="relative target-container" data-siswa-id="<?= $siswa['siswa_id'] ?>">
                                            
                                            <!-- Dropdown: Disediakan oleh PHP, diatur visibility oleh JS -->
                                            <?php if (!empty($kelas_tujuan_list)): ?>
                                                <select name="keputusan[<?= $siswa['siswa_id'] ?>][id_kelas_baru]" 
                                                        class="kelas-baru-dropdown w-full px-5 py-4 bg-white dark:bg-slate-900 border-2 border-indigo-100 dark:border-indigo-900/30 rounded-2xl text-[10px] font-black uppercase tracking-widest text-indigo-700 dark:text-indigo-400 focus:border-indigo-500 appearance-none transition-all shadow-sm">
                                                    <option value="">-- Pilih Rombel Baru --</option>
                                                    <?php foreach ($kelas_tujuan_list as $kt) : ?>
                                                        <option value="<?= esc($kt['id']) ?>"><?= esc($kt['nama_kelas']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-indigo-300 icon-arrow">
                                                    <i class="fas fa-chevron-down text-[8px]"></i>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Label Otomatis (Hidden by default, used for Non-Naik status) -->
                                            <div class="dynamic-label hidden w-full px-5 py-4 bg-slate-100 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl text-center">
                                                <span class="label-text text-[9px] font-black text-slate-400 uppercase italic tracking-widest leading-none">Memproses...</span>
                                            </div>

                                            <!-- Hidden Fallback (Guaranteeing key 'id_kelas_baru' for Controller & Transactions) -->
                                            <input type="hidden" class="hidden-fallback" name="keputusan[<?= $siswa['siswa_id'] ?>][id_kelas_baru]" value="">
                                        </div>
                                    </td>

                                    <td class="px-8 py-5">
                                        <input type="text" name="keputusan[<?= $siswa['siswa_id'] ?>][catatan]" 
                                               placeholder="Catatan..."
                                               class="w-full px-5 py-4 bg-slate-50 dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-700 rounded-2xl text-[10px] font-bold text-slate-600 focus:border-indigo-500 outline-none transition-all italic shadow-inner">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Action -->
                <div class="px-8 py-8 bg-slate-50 dark:bg-white/5 border-t-2 border-slate-100 dark:border-white/10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-white dark:bg-slate-900 rounded-3xl flex items-center justify-center shadow-lg border-2 border-slate-100 dark:border-white/5">
                            <i class="fas fa-shield-check text-indigo-500 text-xl"></i>
                        </div>
                        <p class="text-[10px] font-bold text-slate-500 uppercase leading-relaxed max-w-sm tracking-tight italic">
                            Data akan segera dimigrasikan ke periode akademik baru. Mohon periksa kembali kelas tujuan agar tidak terjadi kesalahan plotting.
                        </p>
                    </div>
                    
                    <button type="submit" class="w-full md:w-auto px-16 py-5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-[0.2em] rounded-2xl shadow-xl shadow-indigo-100 dark:shadow-none transition-all transform hover:scale-[1.02] active:scale-95 border-b-4 border-indigo-800 flex items-center justify-center">
                        <i class="fas fa-save mr-3"></i> SIMPAN KEPUTUSAN MASSAL
                    </button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>

<!-- LOGIKA INTERAKTIF (PERBAIKAN FINAL 100%) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusToggles = document.querySelectorAll('.status-toggle');
    const idKelasAsal = "<?= $kelas_lama['id'] ?>"; 

    const syncTransitionUI = (el) => {
        const sid = el.dataset.siswaId;
        const status = el.value; // 'Naik', 'Tinggal', 'Lulus', 'Mutasi'
        const container = document.querySelector(`.target-container[data-siswa-id="${sid}"]`);
        
        if (!container) return;

        const dropdown = container.querySelector('.kelas-baru-dropdown');
        const labelBox = container.querySelector('.dynamic-label');
        const labelText = container.querySelector('.label-text');
        const hiddenInput = container.querySelector('.hidden-fallback');
        const iconArrow = container.querySelector('.icon-arrow');

        // Reset state awal (Sembunyikan semua & Matikan semua input tujuan)
        if(dropdown) {
            dropdown.classList.add('hidden');
            dropdown.disabled = true;
            dropdown.value = "";
        }
        if(iconArrow) iconArrow.classList.add('hidden');
        if(labelBox) labelBox.classList.add('hidden');
        
        hiddenInput.disabled = true;
        hiddenInput.value = "";

        // LOGIKA KONDISI BERDASARKAN STATUS
        if (status === 'Naik') {
            // JIKA NAIK: Wajib menampilkan pilihan Rombel Baru
            if (dropdown) {
                dropdown.classList.remove('hidden');
                dropdown.disabled = false;
                dropdown.required = true;
                if(iconArrow) iconArrow.classList.remove('hidden');
                // Hidden input dimatikan agar tidak menimpa dropdown
                hiddenInput.disabled = true;
            } else {
                // Kasus jika rombel tujuan di TA Baru belum dibuat di database
                labelBox.classList.remove('hidden');
                labelText.innerText = "ROMBEL TUJUAN BELUM DIBUAT";
                labelText.style.color = "#f43f5e"; // Rose-500
                hiddenInput.disabled = false;
                hiddenInput.value = ""; 
            }

        } else if (status === 'Tinggal') {
            // JIKA TINGGAL: Kunci ke rombel asal
            labelBox.classList.remove('hidden');
            labelText.innerText = "TETAP DI ROMBEL ASAL";
            labelText.style.color = "#f43f5e"; // Rose-500
            
            // Masukkan ID Kelas Asal ke fallback agar tetap terkirim (PENTING!)
            hiddenInput.disabled = false;
            hiddenInput.value = idKelasAsal; 

        } else if (status === 'Lulus') {
            // JIKA LULUS: Status Alumni
            labelBox.classList.remove('hidden');
            labelText.innerText = "SELESAI / ALUMNI";
            labelText.style.color = "#10b981"; // Emerald-500
            
            // Value kosong (mencegah error key di Controller)
            hiddenInput.disabled = false;
            hiddenInput.value = ""; 

        } else {
            // MUTASI / DLL
            labelBox.classList.remove('hidden');
            labelText.innerText = "MUTASI KELUAR";
            labelText.style.color = "#64748b"; // Slate-400
            
            hiddenInput.disabled = false;
            hiddenInput.value = ""; 
        }
    };

    // Jalankan awal dan pasang listener
    statusToggles.forEach(toggle => {
        syncTransitionUI(toggle);
        toggle.addEventListener('change', () => syncTransitionUI(toggle));
    });

    // Validasi Akhir sebelum Form Submit
    document.getElementById('formKenaikan')?.addEventListener('submit', function(e) {
        let hasValidationError = false;
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            if (toggle.value === 'Naik') {
                const sid = toggle.dataset.siswaId;
                const dropdown = document.querySelector(`.target-container[data-siswa-id="${sid}"] .kelas-baru-dropdown`);
                // Jika pilih 'Naik' tapi rombel baru kosong
                if (dropdown && !dropdown.value) {
                    hasValidationError = true;
                    dropdown.style.borderColor = "#f43f5e";
                    dropdown.style.boxShadow = "0 0 0 4px rgba(244, 63, 94, 0.1)";
                }
            }
        });

        if (hasValidationError) {
            e.preventDefault();
            alert('Perhatian: Rombel baru wajib dipilih bagi semua siswa yang berstatus NAIK KELAS!');
        }
    });
});
</script>

<?= $this->endSection() ?>