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
                                <h3 class="text-sm font-medium text-blue-800">Sistem Jurnal Otomatis (Accrual Basis)</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Setiap tagihan yang Anda <i>generate</i> di sini akan otomatis dicatat sebagai <strong>Piutang</strong> pada Laporan Keuangan Yayasan.</p>
                                    <p class="mt-1">Pilih bulan dan tahun secara spesifik untuk menghindari pembengkakan piutang sebelum waktunya jatuh tempo.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="<?= base_url('app/keuangan/tagihan/generate_proses') ?>" method="post" id="formGenerate">
                        <?= csrf_field() ?>

                        <!-- SUPERADMIN UNIT FILTER -->
                        <?php if(isset($isSuperAdmin) && $isSuperAdmin): ?>
                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Unit Entitas <span class="text-red-500">*</span>
                                </label>
                                <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm p-2.5 bg-gray-50 border" name="kode_jenjang" onchange="ubahUnitForm(this.value)" required>
                                    <option value="" disabled <?= empty($active_unit) ? 'selected' : '' ?>>-- Pilih Unit Terlebih Dahulu --</option>
                                    <?php if(isset($jenjang_list)): foreach ($jenjang_list as $j): ?>
                                        <option value="<?= $j['kode_jenjang'] ?>" <?= ($active_unit ?? '') == $j['kode_jenjang'] ? 'selected' : '' ?>>
                                            UNIT <?= strtoupper($j['kode_jenjang']) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Jenis Pembayaran -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                1. Pilih Jenis Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select class="select2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 sm:text-sm p-2.5 bg-gray-50 border" name="id_jenis_pembayaran" id="id_jenis_pembayaran" required>
                                <option value="" data-tipe="">-- Pilih Jenis Pembayaran --</option>
                                <?php foreach ($jenis_pembayaran as $item) : ?>
                                    <option value="<?= $item['id'] ?>" data-tipe="<?= esc($item['tipe']) ?>">
                                        <?= esc($item['nama_pembayaran']) ?> 
                                        (<?= ucfirst(str_replace('_', ' ', esc($item['tipe']))) ?>) 
                                        - Rp <?= number_format($item['nominal'], 0, ',', '.') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- PILIHAN PERIODE (Hanya Muncul Jika Jenis Pembayaran = Bulanan) -->
                        <div id="wrapper-periode" style="display: none;" class="mb-6 bg-indigo-50 border border-indigo-100 rounded-lg p-5">
                            <h4 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-4"><i class="fas fa-calendar-alt mr-1"></i> Periode Tagihan SPP</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2">Bulan Tagihan <span class="text-red-500">*</span></label>
                                    <select name="bulan" id="input_bulan" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm p-2.5 bg-white border">
                                        <?php 
                                            $bulanIni = date('m');
                                            $listBulan = [
                                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', 
                                                '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', 
                                                '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                            ];
                                            foreach($listBulan as $num => $nama): 
                                        ?>
                                            <option value="<?= $num ?>" <?= $bulanIni == $num ? 'selected' : '' ?>><?= strtoupper($nama) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2">Tahun <span class="text-red-500">*</span></label>
                                    <select name="tahun" id="input_tahun" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 sm:text-sm p-2.5 bg-white border">
                                        <?php 
                                            $tahunIni = date('Y');
                                            for($i = $tahunIni - 1; $i <= $tahunIni + 1; $i++): 
                                        ?>
                                            <option value="<?= $i ?>" <?= $tahunIni == $i ? 'selected' : '' ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Target Kelas -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                2. Target Kelas (Rombel) <span class="text-red-500">*</span>
                            </label>
                            <select class="select2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 sm:text-sm p-2.5 bg-gray-50 border" name="id_kelas" required <?= empty($kelas) ? 'disabled' : '' ?>>
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
                                <strong>Peringatan:</strong> Proses ini akan memakan waktu beberapa detik karena sistem akan sekaligus <strong>men-generate Jurnal Akuntansi</strong> untuk seluruh siswa di kelas tersebut. Jangan tutup halaman saat proses berjalan.
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-between pt-2 gap-3">
                            <a href="<?= base_url('app/keuangan/tagihan') ?>" class="w-full sm:w-auto px-5 py-2.5 text-center bg-white text-gray-700 font-medium border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none transition-colors">
                                Batal
                            </a>
                            <button type="submit" id="btnSubmit" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 bg-blue-600 text-white font-medium text-sm leading-tight uppercase rounded-lg shadow-md hover:bg-blue-700 focus:outline-none transition duration-150 ease-in-out" onclick="return confirm('Sistem akan membuat Piutang dan Tagihan secara otomatis. Lanjutkan?')">
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
        // Inisialisasi Select2
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }

        // FUNGSI MENAMPILKAN/MENYEMBUNYIKAN PILIHAN BULAN UNTUK SPP
        $('#id_jenis_pembayaran').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var tipe = selectedOption.data('tipe');
            
            if (tipe === 'bulanan') {
                $('#wrapper-periode').slideDown(300);
                $('#input_bulan').prop('required', true);
                $('#input_tahun').prop('required', true);
            } else {
                $('#wrapper-periode').slideUp(300);
                $('#input_bulan').prop('required', false);
                $('#input_tahun').prop('required', false);
            }
        });
        
        // Trigger saat pertama load (jika form dikembalikan oleh validasi error)
        $('#id_jenis_pembayaran').trigger('change');
        
        // Animasi tombol submit
        $('#formGenerate').on('submit', function() {
            $('#btnSubmit').html('<i class="fas fa-spinner fa-spin mr-2"></i> Sedang Memproses...').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        });
    });

    // Refresh Form jika Superadmin mengganti unit (agar kelas yang muncul sesuai unit)
    function ubahUnitForm(val) {
        if(val !== '') {
            let url = new URL(window.location.href);
            url.searchParams.set('jenjang', val);
            window.location.href = url.toString();
        }
    }
</script>
<?= $this->endSection() ?>