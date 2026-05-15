<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="max-w-4xl mx-auto">
    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">Konfigurasi Skema Komisi</h1>
            <p class="text-sm text-gray-500">Atur gelombang pendaftaran dan besaran bonus target.</p>
        </div>
        <a href="<?= base_url('app/ppdb/affiliate') ?>" class="px-4 py-2 bg-gray-200 rounded-lg text-sm font-bold">Kembali</a>
    </div>

    <form action="<?= base_url('app/ppdb/affiliate/saveKonfigurasi') ?>" method="post">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 gap-6">
            
            <!-- Gelombang 1 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-emerald-600 mb-4">Gelombang 1 (Early Bird)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold uppercase">Fee (Rp)</label>
                        <input type="number" name="ppdb_wave1_fee" value="<?= $settings['ppdb_wave1_fee'] ?>" class="w-full border rounded-lg p-2 mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase">Berakhir Tanggal</label>
                        <input type="date" name="ppdb_wave1_end" value="<?= $settings['ppdb_wave1_end'] ?>" class="w-full border rounded-lg p-2 mt-1">
                    </div>
                </div>
            </div>

            <!-- Gelombang 2 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-blue-600 mb-4">Gelombang 2 (Reguler)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold uppercase">Fee (Rp)</label>
                        <input type="number" name="ppdb_wave2_fee" value="<?= $settings['ppdb_wave2_fee'] ?>" class="w-full border rounded-lg p-2 mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase">Berakhir Tanggal</label>
                        <input type="date" name="ppdb_wave2_end" value="<?= $settings['ppdb_wave2_end'] ?>" class="w-full border rounded-lg p-2 mt-1">
                    </div>
                </div>
            </div>

            <!-- Gelombang 3 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-orange-600 mb-4">Gelombang 3 (Last Call)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold uppercase">Fee (Rp)</label>
                        <input type="number" name="ppdb_wave3_fee" value="<?= $settings['ppdb_wave3_fee'] ?>" class="w-full border rounded-lg p-2 mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase">Berakhir Tanggal</label>
                        <input type="date" name="ppdb_wave3_end" value="<?= $settings['ppdb_wave3_end'] ?>" class="w-full border rounded-lg p-2 mt-1">
                    </div>
                </div>
            </div>

            <!-- Bonus Target -->
            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-200">
                <h3 class="font-bold text-indigo-700 mb-4"><i class="fas fa-trophy mr-2"></i>Bonus Target Progresif</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold uppercase">Setiap Kelipatan (Siswa)</label>
                        <input type="number" name="ppdb_target_step" value="<?= $settings['ppdb_target_step'] ?>" class="w-full border rounded-lg p-2 mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase">Nominal Bonus (Rp)</label>
                        <input type="number" name="ppdb_bonus_amt" value="<?= $settings['ppdb_bonus_amt'] ?>" class="w-full border rounded-lg p-2 mt-1">
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-8 text-right">
            <button type="submit" class="px-8 py-3 bg-gray-900 text-white font-bold rounded-xl hover:bg-black transition-colors">
                Simpan Konfigurasi
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>