<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    $session = session();
    $filter_jenjang = $filter_jenjang ?? $session->get('kode_jenjang');
?>

<div class="min-h-screen bg-slate-50/50 font-sans text-slate-600 pb-20" x-data="{ 
    showModal: false, 
    jenisTransaksi: 'keluar', // default
    formData: { 
        kode_jenjang: '<?= $isSuperAdmin ? "" : $filter_jenjang ?>', 
        tanggal: '<?= date('Y-m-d') ?>', 
        id_kategori: '',
        nominal: '', 
        keterangan: '',
        referensi: ''
    },
    openModal(jenis) {
        this.jenisTransaksi = jenis;
        this.formData.id_kategori = '';
        this.formData.nominal = '';
        this.formData.keterangan = '';
        this.formData.referensi = '';
        this.showModal = true;
    }
}">
    
    <!-- HEADER SECTION -->
    <div class="sticky top-0 z-30 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 mb-8 transition-all duration-300">
        <div class="flex flex-col xl:flex-row justify-between items-center gap-4 max-w-screen-2xl mx-auto">
            
            <div class="flex-1 w-full xl:w-auto text-center xl:text-left">
                <div class="flex items-center justify-center xl:justify-start gap-3">
                    <div class="p-2 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg shadow-lg text-white">
                        <i class="fas fa-exchange-alt text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">Buku Kas Operasional</h1>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5 mt-1">
                            PENCATATAN UANG MASUK & KELUAR UNIT
                        </p>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="flex flex-wrap justify-center xl:justify-end items-center gap-3">
                
                <div class="flex items-center p-1 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <form action="" method="get" class="flex items-center gap-2 m-0 p-0 relative">
                        <input type="hidden" name="start_date" value="<?= $start_date ?>">
                        <input type="hidden" name="end_date" value="<?= $end_date ?>">
                        <div class="relative">
                            <select name="jenjang" onchange="this.form.submit()" 
                                    <?= !$isSuperAdmin ? 'disabled' : '' ?> 
                                    class="pl-9 pr-8 py-2 bg-transparent text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer disabled:text-slate-400 disabled:cursor-not-allowed transition text-slate-600 uppercase">
                                <?php if ($isSuperAdmin): ?><option value="">SEMUA UNIT</option><?php endif; ?>
                                <?php if(!empty($jenjang_list)): foreach($jenjang_list as $j): ?>
                                    <option value="<?= $j['kode_jenjang'] ?>" <?= ($filter_jenjang == $j['kode_jenjang']) ? 'selected' : '' ?>>
                                        UNIT <?= strtoupper($j['kode_jenjang']) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fas <?= !$isSuperAdmin ? 'fa-lock' : 'fa-filter' ?> text-xs"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-slate-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                            <?php if(!$isSuperAdmin): ?><input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>"><?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="flex gap-2">
                    <button @click="openModal('keluar')" class="h-10 px-5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-rose-200 transition flex items-center gap-2 border-b-2 border-rose-700 active:translate-y-0.5">
                        <i class="fas fa-minus-circle"></i> Kas Keluar
                    </button>
                    <button @click="openModal('masuk')" class="h-10 px-5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-200 transition flex items-center gap-2 border-b-2 border-emerald-700 active:translate-y-0.5">
                        <i class="fas fa-plus-circle"></i> Kas Masuk
                    </button>
                </div>
            </div>
        </div>

        <!-- NAVIGATION TABS -->
        <?php if(isset($navigation)): ?>
        <div class="mt-6 overflow-x-auto pb-1 scrollbar-hide max-w-screen-2xl mx-auto">
            <div class="inline-flex p-1 bg-slate-200/60 rounded-xl border border-slate-200/60">
                <?php foreach($navigation as $key => $nav): 
                    if (stripos($key, 'akuntan') !== false || stripos($nav['label'] ?? '', 'akuntan') !== false) continue;

                    $isActive = ($key === 'kas-operasional'); 
                    $activeClass = $isActive 
                        ? 'bg-white text-indigo-600 shadow-sm ring-1 ring-black/5' 
                        : 'text-slate-500 hover:text-slate-700 hover:bg-white/50';
                ?>
                <a href="<?= base_url($nav['url']) ?>" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 whitespace-nowrap flex items-center justify-center gap-2 <?= $activeClass ?>">
                    <i class="fas fa-<?= $nav['icon'] ?>"></i> <?= $nav['label'] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- MAIN CONTENT -->
    <div class="px-6 max-w-screen-2xl mx-auto space-y-6">

        <!-- ALERTS -->
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="rounded-xl bg-emerald-50 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold text-emerald-800 uppercase tracking-tight"><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="rounded-xl bg-rose-50 border-l-4 border-rose-500 p-4 shadow-sm flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 text-lg"></i>
                <span class="text-sm font-bold text-rose-800 uppercase tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif ?>

        <!-- Table Card -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
             <!-- Toolbar / Filter Date -->
             <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                 <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                     <i class="fas fa-list text-indigo-400"></i> Riwayat Transaksi Kas
                 </h3>
                 <form action="" method="get" class="flex items-center gap-2">
                    <input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                    <input type="date" name="start_date" value="<?= $start_date ?>" class="py-1.5 px-3 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 focus:ring-1 focus:ring-indigo-500 outline-none">
                    <span class="text-slate-400">-</span>
                    <input type="date" name="end_date" value="<?= $end_date ?>" class="py-1.5 px-3 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 focus:ring-1 focus:ring-indigo-500 outline-none">
                    <button type="submit" class="bg-slate-800 text-white px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition-colors"><i class="fas fa-search mr-1"></i> Filter</button>
                 </form>
             </div>

             <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] uppercase text-slate-400 font-black border-b border-slate-100 tracking-widest">
                            <th class="px-6 py-4 w-12 text-center">No</th>
                            <th class="px-6 py-4">Tanggal & Bukti</th>
                            <th class="px-6 py-4">Kategori Jurnal</th>
                            <th class="px-6 py-4">Keterangan / Uraian</th>
                            <th class="px-6 py-4 text-center">Unit</th>
                            <th class="px-6 py-4 text-center">Jenis</th>
                            <th class="px-6 py-4 text-right">Nominal (Rp)</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-600">
                        <?php if(empty($transaksi)): ?>
                            <tr><td colspan="8" class="px-6 py-16 text-center text-slate-400 italic">Belum ada pencatatan kas masuk / keluar pada periode ini.</td></tr>
                        <?php else: ?>
                            <?php $no = isset($nomor_urut) ? $nomor_urut + 1 : 1; ?>
                            <?php foreach($transaksi as $t): ?>
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 text-center font-mono text-slate-400 font-bold"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-700"><?= date('d M Y', strtotime($t['tanggal'])) ?></div>
                                    <?php if($t['referensi']): ?>
                                        <div class="text-[10px] font-bold text-indigo-500 mt-0.5"><i class="fas fa-receipt mr-1"></i><?= esc($t['referensi']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-[10px] font-bold border border-slate-200">
                                        <?= esc($t['nama_kategori'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700 truncate max-w-[200px]" title="<?= esc($t['deskripsi']) ?>">
                                    <?= esc($t['deskripsi']) ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-black text-[10px] text-slate-400 uppercase tracking-widest">
                                        <?= esc($t['kode_jenjang']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if($t['sumber_transaksi'] === 'Kas Masuk'): ?>
                                        <span class="px-2 py-1 bg-emerald-50 border border-emerald-200 text-emerald-600 text-[9px] font-black uppercase rounded shadow-sm">
                                            <i class="fas fa-arrow-down mr-1"></i> IN
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-rose-50 border border-rose-200 text-rose-600 text-[9px] font-black uppercase rounded shadow-sm">
                                            <i class="fas fa-arrow-up mr-1"></i> OUT
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right font-black <?= $t['sumber_transaksi'] === 'Kas Masuk' ? 'text-emerald-600' : 'text-rose-500' ?> text-sm tracking-tight">
                                    <?= number_format($t['nominal'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center opacity-50 group-hover:opacity-100 transition-opacity">
                                        <a href="<?= base_url('app/keuangan/kas-operasional/delete/'.$t['id']) ?>" onclick="return confirm('Membatalkan transaksi ini akan menghapus jurnal akuntansi terkait secara permanen. Lanjutkan?')" class="w-8 h-8 flex items-center justify-center text-rose-500 bg-white border border-rose-200 hover:bg-rose-500 hover:text-white rounded-lg shadow-sm transition-all" title="Batal & Hapus">
                                            <i class="fas fa-trash-alt text-[10px]"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
             </div>

             <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/50 flex justify-center">
                <?= $pager->links('default', 'tailwind_pagination') ?>
            </div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL FORM STEALTH ACCOUNTING (Alpine.js)      -->
    <!-- ============================================== -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border-t-8"
                 :class="jenisTransaksi === 'masuk' ? 'border-emerald-500' : 'border-rose-500'">
                
                <div class="bg-white px-8 py-8">
                    <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl text-white shadow-inner"
                                 :class="jenisTransaksi === 'masuk' ? 'bg-emerald-500' : 'bg-rose-500'">
                                <i class="fas" :class="jenisTransaksi === 'masuk' ? 'fa-arrow-down' : 'fa-arrow-up'"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800 tracking-tight leading-none" x-text="jenisTransaksi === 'masuk' ? 'Catat Pemasukan' : 'Catat Pengeluaran'"></h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Buku Kas Operasional Unit</p>
                            </div>
                        </div>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <form action="<?= base_url('app/keuangan/kas-operasional/store') ?>" method="post" class="space-y-5">
                        <?= csrf_field() ?>
                        <input type="hidden" name="jenis_transaksi" :value="jenisTransaksi">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest ml-1">Tanggal</label>
                                <input type="date" name="tanggal" x-model="formData.tanggal" required class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest ml-1">Unit Pengelola</label>
                                <select name="kode_jenjang" x-model="formData.kode_jenjang" <?= !$isSuperAdmin ? 'disabled' : '' ?> class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-xs font-black uppercase focus:ring-2 focus:ring-indigo-500 text-slate-700 disabled:opacity-60 disabled:cursor-not-allowed outline-none appearance-none">
                                    <?php if ($isSuperAdmin): ?><option value="GLOBAL">YAYASAN / PUSAT</option><?php endif; ?>
                                    <?php foreach($jenjang_list as $j): ?>
                                        <option value="<?= $j['kode_jenjang'] ?>">UNIT <?= $j['kode_jenjang'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (!$isSuperAdmin): ?><input type="hidden" name="kode_jenjang" x-model="formData.kode_jenjang"><?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest ml-1" x-text="jenisTransaksi === 'masuk' ? 'Kategori Pendapatan' : 'Kategori Beban/Biaya'"></label>
                            
                            <!-- Dropdown Pemasukan -->
                            <div class="relative" x-show="jenisTransaksi === 'masuk'">
                                <select name="id_kategori_masuk" :required="jenisTransaksi === 'masuk'" :disabled="jenisTransaksi !== 'masuk'" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-emerald-500 text-slate-700 outline-none appearance-none">
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <?php foreach($kategori_masuk as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= esc($cat['nama_akun']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>

                            <!-- Dropdown Pengeluaran -->
                            <div class="relative" x-show="jenisTransaksi === 'keluar'">
                                <select name="id_kategori_keluar" :required="jenisTransaksi === 'keluar'" :disabled="jenisTransaksi !== 'keluar'" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-rose-500 text-slate-700 outline-none appearance-none">
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <?php foreach($kategori_keluar as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= esc($cat['nama_akun']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>
                        </div>

                        <!-- Ganti nama id_kategori_xxx menjadi id_kategori yang sesungguhnya saat submit -->
                        <input type="hidden" name="id_kategori" :value="jenisTransaksi === 'masuk' ? document.querySelector('select[name=id_kategori_masuk]').value : document.querySelector('select[name=id_kategori_keluar]').value">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest ml-1">No. Bukti / Referensi</label>
                                <input type="text" name="referensi" x-model="formData.referensi" class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-indigo-500 text-slate-700 outline-none" placeholder="Cth: KW-001">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest ml-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                                <input type="text" name="nominal" x-model="formData.nominal" required @input="formData.nominal = $event.target.value.replace(/[^,\d]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" 
                                       class="w-full bg-slate-50 border-2 rounded-xl px-4 py-3 text-lg font-black text-slate-800 outline-none transition-colors text-right"
                                       :class="jenisTransaksi === 'masuk' ? 'focus:border-emerald-500 text-emerald-700' : 'focus:border-rose-500 text-rose-700'" placeholder="0">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest ml-1">Keterangan / Uraian Transaksi <span class="text-rose-500">*</span></label>
                            <textarea name="keterangan" x-model="formData.keterangan" rows="2" required class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-indigo-500 text-slate-700 outline-none resize-none" placeholder="Jelaskan secara rinci untuk keperluan apa..."></textarea>
                        </div>

                        <div class="pt-6 mt-6 border-t border-slate-100 flex gap-3">
                            <button type="button" @click="showModal = false" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition">Batal</button>
                            <button type="submit" class="flex-1 py-4 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl transition-all active:scale-95 border-b-4"
                                    :class="jenisTransaksi === 'masuk' ? 'bg-emerald-500 hover:bg-emerald-600 border-emerald-700 shadow-emerald-200' : 'bg-rose-500 hover:bg-rose-600 border-rose-700 shadow-rose-200'">
                                <i class="fas fa-save mr-1"></i> Simpan Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    [x-cloak] { display: none !important; }
</style>

<?= $this->endSection() ?>