<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="max-w-4xl mx-auto">
    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                <?= esc($title) ?>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Isi formulir di bawah ini dengan lengkap dan teliti.
            </p>
        </div>
        <a href="<?= base_url('app/masterdata/jenispembayaran') ?>" 
           class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 shadow-sm transition-all">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <?php if (session()->has('errors')) : ?>
        <div class="rounded-xl bg-red-50 border border-red-200 p-4 mb-6 animate-fade-in-down">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada inputan:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <?php foreach (session('errors') as $error) : ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif ?>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        
        <?php
            $is_edit = isset($jenis['id']);
            $url = $is_edit ? base_url('app/masterdata/jenispembayaran/update/' . $jenis['id']) : base_url('app/masterdata/jenispembayaran/create');
        ?>

        <form action="<?= $url ?>" method="post" class="divide-y divide-gray-100 dark:divide-gray-700">
            <?= csrf_field() ?>
            <?php if ($is_edit) : ?>
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="id" value="<?= $jenis['id'] ?>">
            <?php endif; ?>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="col-span-1 md:col-span-2">
                    <label for="nama_pembayaran" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Nama Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama_pembayaran" 
                           name="nama_pembayaran" 
                           value="<?= old('nama_pembayaran', $jenis['nama_pembayaran'] ?? '') ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white transition-colors"
                           placeholder="Contoh: SPP Bulan Juli, Uang Gedung 2024" 
                           required>
                    <p class="mt-1 text-xs text-gray-500">Nama tagihan yang akan muncul di kuitansi siswa.</p>
                </div>

                <div>
                    <label for="tipe" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Tipe Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-tags text-xs"></i>
                        </div>
                        <select id="tipe" name="tipe" required
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none">
                            <option value="" disabled <?= !old('tipe', $jenis['tipe'] ?? '') ? 'selected' : '' ?>>-- Pilih Tipe --</option>
                            <?php 
                                $tipeVal = old('tipe', $jenis['tipe'] ?? '');
                                $options = [
                                    'bulanan' => 'Bulanan (Rutin/SPP)',
                                    'tahunan' => 'Tahunan',
                                    'sekali_bayar' => 'Sekali Bayar (Insidental)',
                                    'opsional' => 'Opsional / Bebas'
                                ];
                            ?>
                            <?php foreach($options as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $tipeVal == $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="nominal" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Nominal Standar (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500 font-bold text-xs">
                            Rp
                        </div>
                        <input type="number" 
                               id="nominal" 
                               name="nominal" 
                               value="<?= old('nominal', $jenis['nominal'] ?? '') ?>"
                               min="0"
                               step="1000"
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block dark:bg-gray-700 dark:border-gray-600 dark:text-white text-right font-mono"
                               placeholder="0" 
                               required>
                    </div>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label for="kode_jenjang" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Scope Unit (Jenjang)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                            <i class="fas fa-sitemap text-xs"></i>
                        </div>
                        <select id="kode_jenjang" name="kode_jenjang"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block dark:bg-gray-700 dark:border-gray-600 dark:text-white appearance-none">
                            
                            <option value="" class="font-bold text-blue-600" <?= empty(old('kode_jenjang', $jenis['kode_jenjang'] ?? '')) ? 'selected' : '' ?>>
                                &#xf0ac; &nbsp; Global - Berlaku untuk Semua Unit/Jenjang
                            </option>
                            
                            <option disabled>----------------------------------------</option>

                            <?php 
                                $selectedJenjang = old('kode_jenjang', $jenis['kode_jenjang'] ?? '');
                                foreach ($jenjang_options as $j): 
                            ?>
                                <option value="<?= $j['kode_jenjang'] ?>" <?= $selectedJenjang == $j['kode_jenjang'] ? 'selected' : '' ?>>
                                    Unit <?= esc($j['nama_jenjang']) ?> (<?= esc($j['kode_jenjang']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Jika dipilih <strong>Global</strong>, jenis pembayaran ini akan muncul untuk semua siswa di semua unit. 
                        Jika unit dipilih, hanya siswa di unit tersebut yang ditagihkan.
                    </p>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label for="keterangan" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Keterangan Tambahan <span class="text-xs font-normal text-gray-400">(Opsional)</span>
                    </label>
                    <textarea id="keterangan" name="keterangan" rows="2"
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                              placeholder="Catatan internal admin..."><?= old('keterangan', $jenis['keterangan'] ?? '') ?></textarea>
                </div>

            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-end gap-3">
                <a href="<?= base_url('app/masterdata/jenispembayaran') ?>" 
                   class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-900 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 transition-all">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-save mr-2"></i>
                    <?= $is_edit ? 'Simpan Perubahan' : 'Simpan Data Baru' ?>
                </button>
            </div>

        </form>
    </div>
</div>

<?= $this->endSection() ?>