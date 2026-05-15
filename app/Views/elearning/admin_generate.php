<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-6xl mx-auto space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="p-2 bg-emerald-100 text-emerald-600 rounded-lg"><i class="fas fa-magic"></i></span>
                Generator Kelas E-learning
            </h1>
            <p class="text-gray-500 mt-1">
                Tahun Ajaran Aktif: <span class="font-bold text-gray-800 dark:text-gray-300"><?= esc($tahun_ajaran ?? '-') ?></span>
            </p>
        </div>
        <div>
            <a href="<?= base_url('app/elearning') ?>" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
        <div class="text-sm text-blue-700">
            <p class="font-bold">Bagaimana cara kerjanya?</p>
            <p>Sistem mendeteksi jadwal pelajaran yang aktif. Anda dapat membuat kelas E-learning secara massal berdasarkan jadwal tersebut. Kelas yang sudah dibuat ditandai dengan label "Sudah Ada".</p>
        </div>
    </div>

    <!-- Main Form -->
    <form action="<?= base_url('app/elearning/process_generate') ?>" method="post" id="generateForm">
        <?= csrf_field() ?>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
            <!-- Toolbar -->
            <div class="p-4 border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5 flex justify-between items-center flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4 cursor-pointer">
                    <label for="selectAll" class="text-sm font-medium text-gray-700 dark:text-gray-300 select-none cursor-pointer">Pilih Semua yang Belum Ada</label>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg shadow-md transition-all flex items-center gap-2 transform active:scale-95">
                    <i class="fas fa-cogs"></i> Proses Generate Terpilih
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 font-semibold border-b border-gray-200 dark:border-gray-700">
                            <th class="p-4 w-12 text-center">#</th>
                            <th class="p-4">Unit</th>
                            <th class="p-4">Mata Pelajaran</th>
                            <th class="p-4">Kelas (Rombel)</th>
                            <th class="p-4">Pengajar (Owner)</th>
                            <th class="p-4">Usulan Nama Kelas</th>
                            <th class="p-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php if(empty($candidates)): ?>
                            <tr>
                                <td colspan="7" class="p-12 text-center text-gray-500 flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-calendar-times text-2xl text-gray-400"></i>
                                    </div>
                                    <span class="font-medium">Tidak ada data jadwal ditemukan untuk Tahun Ajaran ini.</span>
                                    <span class="text-xs mt-1">Pastikan jadwal pelajaran sudah diinput di menu Akademik.</span>
                                </td>
                            </tr>
                        <?php else: foreach($candidates as $c): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors <?= $c['is_exists'] ? 'opacity-50 bg-gray-50/50' : '' ?>">
                                <td class="p-4 text-center">
                                    <?php if(!$c['is_exists']): ?>
                                        <!-- FIX: Menggunakan guru_id alih-alih guru_uid dan htmlspecialchars agar JSON aman -->
                                        <?php if(!empty($c['guru_id'])): ?>
                                            <input type="checkbox" name="items[]" value='<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>' class="item-checkbox rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4 cursor-pointer">
                                        <?php else: ?>
                                            <i class="fas fa-exclamation-triangle text-amber-500" title="Data Guru Kosong"></i>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-emerald-500"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-sm font-bold text-gray-600"><?= $c['kode_jenjang'] ?></td>
                                <td class="p-4 text-sm font-medium text-gray-900 dark:text-white"><?= $c['mapel'] ?></td>
                                <td class="p-4 text-sm text-gray-600 dark:text-gray-300">
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono">
                                        <?= $c['grup'] ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">
                                            <?= substr($c['guru'], 0, 1) ?>
                                        </div>
                                        <?= $c['guru'] ?>
                                    </div>
                                    <!-- FIX: Menggunakan guru_id -->
                                    <?php if(empty($c['guru_id'])): ?>
                                        <span class="text-[10px] text-red-500 block ml-8 font-bold mt-1">
                                            <i class="fas fa-times-circle"></i> ID Guru Tidak Ditemukan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4">
                                    <div class="text-sm font-mono text-emerald-700 bg-emerald-50 border border-emerald-100 rounded px-2 py-1 inline-block w-fit">
                                        <?= $c['suggested_name'] ?>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <?php if($c['is_exists']): ?>
                                        <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200">
                                            Sudah Ada
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-bold border border-gray-200">
                                            Belum Ada
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
    // Script Sederhana untuk Select All
    const selectAllCb = document.getElementById('selectAll');
    if(selectAllCb) {
        selectAllCb.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
</script>
<?= $this->endSection() ?>