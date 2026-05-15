<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="w-full px-6 py-6 mx-auto">
    <!-- Page Heading -->
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-800">Generate Tagihan Masal</h1>
        <a href="<?= base_url('app/keuangan/tagihan') ?>" class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="flex justify-center">
        <div class="w-full lg:w-2/3">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-100">
                <!-- Card Header -->
                <div class="px-6 py-4 bg-white border-b border-gray-100">
                    <h6 class="font-bold text-blue-600 flex items-center">
                        <i class="fas fa-layer-group mr-2"></i> Form Pembuatan Tagihan Otomatis
                    </h6>
                </div>
                
                <!-- Card Body -->
                <div class="p-6">
                    <!-- Notification Success -->
                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r shadow-sm flex justify-between items-start" role="alert">
                            <div class="flex">
                                <i class="fas fa-check-circle mt-1 mr-2"></i>
                                <span><?= session()->getFlashdata('success') ?></span>
                            </div>
                            <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.remove()">
                                <span class="text-xl">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Notification Error -->
                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r shadow-sm flex justify-between items-start" role="alert">
                            <div class="flex">
                                <i class="fas fa-exclamation-triangle mt-1 mr-2"></i>
                                <span><?= session()->getFlashdata('error') ?></span>
                            </div>
                            <button type="button" class="text-red-700 hover:text-red-900" onclick="this.parentElement.remove()">
                                <span class="text-xl">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-100 rounded-md p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Petunjuk:</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Pilih jenis pembayaran dan kelas target. Sistem akan membuat tagihan secara otomatis untuk <strong>seluruh siswa</strong> yang terdaftar di kelas tersebut.</p>
                                    <p class="mt-1">Jika tipe pembayaran bersifat <strong>Bulanan</strong>, sistem akan meng-generate tagihan untuk satu tahun ajaran penuh (12 bulan).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="<?= base_url('app/keuangan/tagihan/generate_proses') ?>" method="post">
                        <?= csrf_field() ?>

                        <!-- Jenis Pembayaran -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                1. Pilih Jenis Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select class="select2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 sm:text-sm p-2.5 bg-gray-50 border" name="id_jenis_pembayaran" required>
                                <option value="">-- Pilih Jenis Pembayaran --</option>
                                <?php foreach ($jenis_pembayaran as $item) : ?>
                                    <option value="<?= $item['id'] ?>">
                                        <?= esc($item['nama_pembayaran']) ?> 
                                        (<?= ucfirst(str_replace('_', ' ', esc($item['tipe']))) ?>) 
                                        - Rp <?= number_format($item['nominal'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Target Kelas -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                2. Target Kelas <span class="text-red-500">*</span>
                            </label>
                            <select class="select2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 sm:text-sm p-2.5 bg-gray-50 border" name="id_kelas" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelas as $item) : ?>
                                    <option value="<?= $item['id'] ?>"><?= esc($item['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="border-t border-gray-100 my-6"></div>

                        <!-- Warning Alert -->
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-md mb-6 text-sm flex items-start">
                            <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
                            <div>
                                <strong>Peringatan:</strong> Proses ini dapat memakan waktu beberapa detik tergantung jumlah siswa. Pastikan data yang dipilih sudah benar sebelum melanjutkan.
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between pt-2">
                            <a href="<?= base_url('app/keuangan/tagihan') ?>" class="px-5 py-2.5 bg-white text-gray-700 font-medium border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white font-medium text-sm leading-tight uppercase rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out" onclick="return confirm('Apakah Anda yakin ingin membuat tagihan masal untuk kelas ini? Proses ini tidak dapat dibatalkan.')">
                                <i class="fas fa-cogs mr-2"></i> Mulai Generate
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
    $(document).ready(function() {
        // Inisialisasi Select2 jika tersedia
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%',
                // Menghapus 'theme: bootstrap4' karena kita menggunakan Tailwind
                // Anda mungkin perlu CSS khusus agar Select2 menyatu sempurna dengan Tailwind,
                // atau gunakan library alternatif seperti TomSelect untuk integrasi Tailwind native.
            });
        }
    });
</script>
<?= $this->endSection() ?>