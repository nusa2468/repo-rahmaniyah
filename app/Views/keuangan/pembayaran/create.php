<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full px-6 py-6 mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Proses Pembayaran</h1>
            <p class="text-sm text-gray-500 mt-1">Validasi kas masuk untuk tagihan siswa.</p>
        </div>
        <a href="<?= base_url('app/keuangan/tagihan') ?>" class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Alerts -->
    <?php if (session()->get('errors')) : ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Terjadi Kesalahan:</h3>
                    <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                        <?php foreach (session()->get('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif ?>

    <?php if (session()->get('error')) : ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r shadow-sm flex items-center" role="alert">
            <i class="fas fa-times-circle mr-2"></i>
            <span class="text-sm"><?= session()->get('error') ?></span>
        </div>
    <?php endif ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Bill Info -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <h6 class="font-bold text-blue-600 text-sm uppercase tracking-wide flex items-center">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Informasi Tagihan
                    </h6>
                </div>
                <div class="p-5">
                    <!-- Total & Status -->
                    <div class="text-center mb-6 pb-6 border-b border-gray-100">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Tagihan</div>
                        <div class="text-3xl font-extrabold text-gray-800">Rp <?= number_format($tagihan['jumlah'] ?? 0, 0, ',', '.') ?></div>
                        
                        <?php
                            $status = $tagihan['status'] ?? 'belum_bayar';
                            $statusColor = 'bg-red-100 text-red-800 border border-red-200';
                            if ($status == 'lunas') $statusColor = 'bg-green-100 text-green-800 border border-green-200';
                            elseif ($status == 'bayar_sebagian' || $status == 'proses') $statusColor = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $statusColor ?> mt-2">
                            <?= strtoupper(str_replace('_', ' ', $status)) ?>
                        </span>
                    </div>

                    <!-- Details List -->
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Jenis Pembayaran</span>
                            <span class="font-semibold text-gray-800 text-right"><?= esc($tagihan['nama_pembayaran'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Siswa</span>
                            <span class="font-semibold text-gray-800 text-right"><?= esc($tagihan['nama_lengkap'] ?? $tagihan['nama_siswa'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">NIS</span>
                            <span class="font-mono font-semibold text-gray-800 text-right"><?= esc($tagihan['nis'] ?? '-') ?></span>
                        </div>
                        <div class="flex justify-between items-start">
                            <span class="text-gray-500 whitespace-nowrap mr-4">Deskripsi</span>
                            <span class="font-medium text-gray-600 text-right text-xs leading-relaxed"><?= esc($tagihan['deskripsi'] ?? '-') ?></span>
                        </div>
                    </div>

                    <!-- Summary Box -->
                    <div class="bg-blue-50 rounded-lg p-4 mt-6 border border-blue-100">
                        <?php 
                            $jumlah = $tagihan['jumlah'] ?? 0;
                            $terbayar = $tagihan['total_terbayar'] ?? 0;
                            $sisa = isset($tagihan['sisa_tagihan']) ? $tagihan['sisa_tagihan'] : ($jumlah - $terbayar);
                        ?>
                        <div class="flex justify-between items-center text-sm mb-2">
                            <span class="text-blue-600">Sudah Dibayar</span>
                            <span class="font-bold text-green-600">Rp <?= number_format($terbayar, 0, ',', '.') ?></span>
                        </div>
                        <div class="border-t border-blue-200 my-2"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-blue-800 font-medium">Sisa Tagihan</span>
                            <span class="text-lg font-bold text-red-600">Rp <?= number_format($sisa, 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Payment Form -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden h-full">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <h6 class="font-bold text-blue-600 text-sm uppercase tracking-wide flex items-center">
                        <i class="fas fa-hand-holding-usd mr-2"></i> Input Realisasi Pembayaran
                    </h6>
                </div>
                <div class="p-6">
                    <form action="<?= base_url('app/keuangan/tagihan/process_bayar/' . ($tagihan['id'] ?? '')) ?>" method="post" id="formBayar" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_tagihan" value="<?= $tagihan['id'] ?? '' ?>">

                        <!-- Jumlah Bayar -->
                        <div class="mb-5">
                            <label for="jumlah_bayar" class="block text-sm font-bold text-gray-700 mb-2">Jumlah Bayar (Rp)</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm font-bold">Rp</span>
                                </div>
                                <input type="number" 
                                    name="jumlah_bayar" 
                                    id="jumlah_bayar"
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md py-2.5 font-semibold text-gray-900 <?= (session('errors.jumlah_bayar')) ? 'border-red-500 ring-1 ring-red-500' : '' ?>" 
                                    placeholder="0" 
                                    max="<?= $sisa ?>"
                                    value="<?= old('jumlah_bayar', $sisa) ?>" 
                                    required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Maksimal pembayaran: <span class="font-bold text-gray-700">Rp <?= number_format($sisa, 0, ',', '.') ?></span></p>
                        </div>

                        <!-- Date & Method Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Bayar</label>
                                <input type="date" 
                                    name="tanggal_bayar" 
                                    class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2" 
                                    value="<?= old('tanggal_bayar', date('Y-m-d')) ?>" 
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Metode Pembayaran</label>
                                <select name="metode_pembayaran" id="metode_pembayaran" class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md py-2" required>
                                    <!-- Perbaikan: Value menggunakan Huruf Kapital Depan (Title Case) -->
                                    <option value="Tunai" <?= old('metode_pembayaran') == 'Tunai' ? 'selected' : '' ?>>Tunai (Cash)</option>
                                    <option value="Transfer" <?= old('metode_pembayaran') == 'Transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                                </select>
                            </div>
                        </div>

                        <!-- Upload Proof -->
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Upload Bukti Bayar</label>
                            <input type="file" 
                                name="bukti_bayar" 
                                id="bukti_bayar"
                                accept="image/*"
                                class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-xs file:font-bold file:uppercase
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100 border border-gray-300 rounded-md cursor-pointer py-2 px-3">
                            <p class="mt-1 text-xs text-gray-500" id="label_wajib_bukti">Opsional untuk metode Tunai.</p>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Keterangan / Catatan</label>
                            <textarea name="keterangan" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Contoh: Titipan orang tua, pembayaran lunas, dll"><?= old('keterangan') ?></textarea>
                        </div>

                        <!-- Footer Actions -->
                        <div class="border-t border-gray-100 pt-5 flex items-center justify-between">
                             <div class="text-xs text-gray-400 italic">
                                * Pastikan nominal sudah benar.
                            </div>
                            <button type="submit" id="btnSubmit" class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-md text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                <i class="fas fa-save mr-2 mt-0.5"></i> Simpan Pembayaran
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const metodeSelect = document.getElementById('metode_pembayaran');
    const labelWajib = document.getElementById('label_wajib_bukti');
    const inputBukti = document.getElementById('bukti_bayar');

    function updateBuktiLabel() {
        // Perbaikan: Cek value dengan huruf kapital 'Transfer'
        if (metodeSelect.value === 'Transfer') {
            labelWajib.classList.add('text-red-500', 'font-bold');
            labelWajib.classList.remove('text-gray-500');
            labelWajib.innerHTML = 'Wajib diunggah untuk metode Transfer.';
            inputBukti.required = true;
        } else {
            labelWajib.classList.remove('text-red-500', 'font-bold');
            labelWajib.classList.add('text-gray-500');
            labelWajib.innerHTML = 'Opsional untuk metode Tunai.';
            inputBukti.required = false;
        }
    }

    metodeSelect.addEventListener('change', updateBuktiLabel);
    // Init state
    updateBuktiLabel();

    // Loading button effect
    document.getElementById('formBayar').onsubmit = function() {
        const btn = document.getElementById('btnSubmit');
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...';
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    };
</script>
<?= $this->endSection() ?>