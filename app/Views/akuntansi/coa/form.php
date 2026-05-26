<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
$isEdit = !empty($coa);

// Nilai default untuk form
$valNama = $coa->nama_akun ?? '';
$valKode = $coa->kode_akun ?? '';
$valKategori = $coa->id_kategori ?? '';
$valParentId = $coa->parent_id ?? '';
$valIsParent = $coa->is_parent ?? 0;
$valIsActive = $coa->is_active ?? 1;

// Format Saldo
$valSaldo = '';
if ($coa && !empty($coa->saldo_awal)) {
    $valSaldo = number_format($coa->saldo_awal, 0, ',', '.');
}
?>

<!-- ALpineJS Scope -->
<div x-data="coaForm(<?= $valIsParent ? 'true' : 'false' ?>, <?= $valIsActive ? 'true' : 'false' ?>)" class="bg-[#f8f9fa] min-h-screen pb-20">
    
    <!-- STICKY TOP BAR (Gaya ERP Modern) -->
    <div class="sticky top-0 z-40 bg-white border-b border-gray-200 px-4 sm:px-8 py-4 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4 transition-all">
        
        <div class="flex items-center gap-3">
            <a href="<?= base_url('app/akuntansi?jenjang=' . esc($filterJenjang)) ?>" class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 transition-colors" title="Batal & Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <ol class="inline-flex items-center space-x-1 md:space-x-2 text-xs font-bold text-gray-400">
                <li>Bagan Akun</li>
                <li><i class="fas fa-chevron-right text-[8px] opacity-50"></i></li>
                <li class="text-gray-800"><?= $isEdit ? esc($valKode) : 'Baru' ?></li>
            </ol>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button form="form-coa" type="button" @click="document.getElementById('form-coa').submit()" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-widest rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-cloud-upload-alt"></i> Simpan Dokumen
            </button>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 mt-8">
        
        <!-- ERROR ALERTS -->
        <?php if (session()->getFlashdata('errors')) : ?>
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm mb-6 flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 mt-0.5 text-lg"></i>
                <div>
                    <h3 class="text-sm font-bold text-rose-800 uppercase tracking-widest mb-1">Gagal Menyimpan Validasi</h3>
                    <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                        <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm mb-6 flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 mt-0.5 text-lg"></i>
                <div class="text-sm font-bold text-rose-800"><?= session()->getFlashdata('error') ?></div>
            </div>
        <?php endif ?>

        <!-- DOCUMENT SHEET (Form Container) -->
        <div class="bg-white rounded border border-gray-200 shadow-sm relative overflow-hidden">
            
            <!-- Dekorasi Garis Atas -->
            <div class="absolute top-0 left-0 w-full h-1 bg-indigo-500"></div>

            <!-- Ribbon / Label Status di Sudut Kanan Atas -->
            <div class="absolute top-6 right-0 w-32 flex justify-end overflow-hidden h-32 pointer-events-none">
                <div class="text-[10px] font-black uppercase tracking-widest text-white py-1.5 w-48 text-center transform rotate-45 translate-x-[30%] -translate-y-2 shadow-md transition-colors duration-300"
                     :class="isActive ? 'bg-emerald-500' : 'bg-gray-400'"
                     x-text="isActive ? 'AKTIF' : 'ARSIP'">
                </div>
            </div>

            <form id="form-coa" action="<?= base_url('app/akuntansi/coa/save') ?>" method="post" class="p-8 md:p-12">
                <?= csrf_field() ?>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= esc($coa->id) ?>">
                <?php endif; ?>

                <!-- DOKUMEN HEADER: Nama Akun -->
                <div class="mb-10 w-full md:w-3/4">
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">Nama Rekening / Akun <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_akun" required
                           class="w-full bg-transparent border-b-2 border-gray-200 focus:border-indigo-500 outline-none transition-colors text-3xl md:text-4xl font-black text-gray-900 placeholder-gray-300 pb-2 leading-tight"
                           placeholder="Contoh: Kas Kecil Sekolah"
                           value="<?= old('nama_akun', $valNama) ?>">
                </div>

                <!-- DOKUMEN BODY: 2 Columns Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                    
                    <!-- COLUMN 1: Basic Info -->
                    <div class="space-y-6">
                        
                        <!-- Kode Akun -->
                        <div class="flex items-center border-b border-gray-100 pb-2 group focus-within:border-indigo-300 transition-colors">
                            <label class="w-1/3 text-xs font-bold text-gray-600">Kode Akun</label>
                            <div class="w-2/3">
                                <input type="text" name="kode_akun" required
                                       class="w-full bg-transparent outline-none text-sm font-bold text-gray-900 placeholder-gray-300"
                                       placeholder="1101"
                                       value="<?= old('kode_akun', $valKode) ?>">
                            </div>
                        </div>

                        <!-- Kategori (Tipe) -->
                        <div class="flex items-center border-b border-gray-100 pb-2 group focus-within:border-indigo-300 transition-colors">
                            <label class="w-1/3 text-xs font-bold text-gray-600">Tipe / Kategori</label>
                            <div class="w-2/3 relative">
                                <select name="id_kategori" required
                                        class="w-full bg-transparent outline-none text-sm font-bold text-indigo-700 appearance-none cursor-pointer">
                                    <option value="" disabled selected>-- Pilih Tipe Akun --</option>
                                    <?php foreach ($kategoriList as $k): ?>
                                        <option value="<?= $k['id'] ?>" <?= old('id_kategori', $valKategori) == $k['id'] ? 'selected' : '' ?>>
                                            <?= esc($k['nama_kategori']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-caret-down absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Parent Akun -->
                        <div class="flex items-center border-b border-gray-100 pb-2 group focus-within:border-indigo-300 transition-colors" x-show="!isParent" x-collapse>
                            <label class="w-1/3 text-xs font-bold text-gray-600">Sub-Akun Dari</label>
                            <div class="w-2/3 relative">
                                <select name="parent_id"
                                        class="w-full bg-transparent outline-none text-sm font-medium text-gray-800 appearance-none cursor-pointer">
                                    <option value="">-- Tidak Ada Induk (Root) --</option>
                                    <?php foreach ($parentList as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= old('parent_id', $valParentId) == $p['id'] ? 'selected' : '' ?>>
                                            [<?= esc($p['kode_akun']) ?>] <?= esc($p['nama_akun']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-caret-down absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                    </div>

                    <!-- COLUMN 2: Accounting & Config -->
                    <div class="space-y-6">
                        
                        <!-- Unit (Jenjang) -->
                        <div class="flex items-center border-b border-gray-100 pb-2 group focus-within:border-indigo-300 transition-colors">
                            <label class="w-1/3 text-xs font-bold text-gray-600">Unit Entitas</label>
                            <div class="w-2/3 relative">
                                <?php if ($isGlobal): ?>
                                    <select name="kode_jenjang" required
                                            class="w-full bg-transparent outline-none text-sm font-bold text-gray-800 uppercase appearance-none cursor-pointer">
                                        <option value="GLOBAL" <?= old('kode_jenjang', $filterJenjang) == 'GLOBAL' ? 'selected' : '' ?>>KONSOLIDASI (YAYASAN)</option>
                                        <?php foreach ($daftarUnit as $kode => $nama): ?>
                                            <option value="<?= $kode ?>" <?= old('kode_jenjang', $filterJenjang) == $kode ? 'selected' : '' ?>>
                                                UNIT <?= strtoupper($kode) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fas fa-caret-down absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                <?php else: ?>
                                    <input type="hidden" name="kode_jenjang" value="<?= esc($filterJenjang) ?>">
                                    <span class="text-sm font-bold text-gray-500 uppercase"><i class="fas fa-lock mr-1 text-[10px]"></i> Unit <?= esc($filterJenjang) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Saldo Awal -->
                        <div class="flex items-center border-b border-gray-100 pb-2 group focus-within:border-indigo-300 transition-colors" x-show="!isParent" x-collapse>
                            <label class="w-1/3 text-xs font-bold text-gray-600">Saldo Awal (Rp)</label>
                            <div class="w-2/3 relative">
                                <input type="text" name="saldo_awal" id="saldoInput"
                                       class="w-full bg-transparent outline-none text-sm font-black text-gray-900 placeholder-gray-300 text-right pr-2"
                                       placeholder="0"
                                       value="<?= old('saldo_awal', $valSaldo) ?>">
                            </div>
                        </div>

                        <!-- Checkbox Config -->
                        <div class="pt-2 flex flex-col gap-4">
                            <!-- Toggle Parent -->
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_parent" value="1" x-model="isParent" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <div>
                                    <span class="block text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">Ini adalah Akun Induk (Header)</span>
                                    <span class="block text-[10px] text-gray-500 mt-0.5 leading-tight">Akun Induk tidak dapat digunakan untuk memposting jurnal. Nilainya adalah total dari sub-akun di bawahnya.</span>
                                </div>
                            </label>

                            <!-- Toggle Active -->
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_active" value="1" x-model="isActive" class="w-4 h-4 text-emerald-500 border-gray-300 rounded focus:ring-emerald-500">
                                <div>
                                    <span class="block text-sm font-bold text-gray-800 group-hover:text-emerald-600 transition-colors">Aktifkan Rekening Ini</span>
                                    <span class="block text-[10px] text-gray-500 mt-0.5 leading-tight">Hapus centang untuk mengarsipkan akun agar tidak muncul di form jurnal.</span>
                                </div>
                            </label>
                        </div>

                    </div>
                </div>
            </form>
        </div>
        
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('coaForm', (initParent, initActive) => ({
            isParent: initParent,
            isActive: initActive
        }))
    });

    // Auto Format Rupiah Logic
    document.addEventListener("DOMContentLoaded", function() {
        const saldoInput = document.getElementById('saldoInput');
        if (saldoInput) {
            saldoInput.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        }
    });

    function formatRupiah(angka) {
        let number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }
</script>

<?= $this->endSection() ?>