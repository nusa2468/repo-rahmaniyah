<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    $session = session();
    $filter_jenjang = $filter_jenjang ?? $session->get('kode_jenjang');
?>

<div class="min-h-screen bg-slate-50/50 font-sans text-slate-600 pb-20" x-data="{ 
    showModal: false, 
    isEdit: false,
    formData: { 
        id: '', 
        kode_jenjang: '<?= $isSuperAdmin ? "" : $filter_jenjang ?>', 
        id_kategori: '', 
        tanggal: '<?= date('Y-m-d') ?>', 
        jumlah: '', 
        keterangan: '' 
    },
    openModal(data = null) {
        if(data) {
            this.isEdit = true;
            this.formData = { 
                id: data.id, 
                kode_jenjang: data.kode_jenjang || '', 
                id_kategori: data.id_kategori, 
                tanggal: data.tanggal, 
                jumlah: parseInt(data.jumlah).toLocaleString('id-ID'), 
                keterangan: data.keterangan 
            };
        } else {
            this.isEdit = false;
            this.formData = { 
                id: '', 
                kode_jenjang: '<?= $isSuperAdmin ? "" : $filter_jenjang ?>', 
                id_kategori: '', 
                tanggal: '<?= date('Y-m-d') ?>', 
                jumlah: '', 
                keterangan: '' 
            };
        }
        this.showModal = true;
    }
}">
    
    <!-- HEADER SECTION -->
    <div class="sticky top-0 z-30 bg-slate-50/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 mb-8 transition-all duration-300">
        <div class="flex flex-col xl:flex-row justify-between items-center gap-4 max-w-screen-2xl mx-auto">
            
            <!-- Title -->
            <div class="flex-1 w-full xl:w-auto text-center xl:text-left">
                <div class="flex items-center justify-center xl:justify-start gap-3">
                    <div class="p-2 bg-amber-500 rounded-lg shadow-lg shadow-amber-500/30 text-white">
                        <i class="fas fa-file-invoice-dollar text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 leading-tight">Transaksi Pengeluaran</h1>
                        <p class="text-xs font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                            <span class="bg-amber-50 text-amber-600 border border-amber-100 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest">
                                BEBAN OPS
                            </span>
                            
                            <!-- INDIKATOR TUTUP BUKU -->
                            <?php if(isset($lock_date) && $lock_date !== '2000-01-01'): ?>
                            <span class="text-[10px] font-bold text-rose-500 ml-2 border-l border-slate-300 pl-2">
                                <i class="fas fa-lock mr-1"></i> Tutup Buku: <?= date('d M Y', strtotime($lock_date)) ?>
                            </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="flex flex-wrap justify-center xl:justify-end items-center gap-3">
                
                <!-- DROPDOWN UNIT CERDAS -->
                <div class="flex items-center p-1 bg-white border border-slate-200 rounded-xl shadow-sm">
                    <form action="" method="get" class="flex items-center gap-2 m-0 p-0 relative">
                        <!-- Pertahankan Tanggal Saat Filter Unit Berubah -->
                        <input type="hidden" name="start_date" value="<?= esc($start_date) ?>">
                        <input type="hidden" name="end_date" value="<?= esc($end_date) ?>">

                        <div class="relative">
                            <select name="jenjang" onchange="this.form.submit()" 
                                    <?= !$isSuperAdmin ? 'disabled' : '' ?> 
                                    class="pl-9 pr-8 py-2 bg-transparent text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none appearance-none cursor-pointer disabled:text-slate-400 disabled:cursor-not-allowed transition text-slate-600">
                                <?php if ($isSuperAdmin): ?><option value="">SEMUA UNIT</option><?php endif; ?>
                                <?php if(!empty($jenjang_list)): foreach($jenjang_list as $j): ?>
                                    <option value="<?= $j['kode_jenjang'] ?>" <?= ($filter_jenjang == $j['kode_jenjang']) ? 'selected' : '' ?>>
                                        Unit <?= strtoupper($j['nama_jenjang'] ?? $j['kode_jenjang']) ?>
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

                <button @click="openModal()" class="h-10 px-5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-amber-200 transition flex items-center gap-2 border-b-2 border-amber-700 active:translate-y-0.5">
                    <i class="fas fa-plus-circle"></i> Input Pengeluaran
                </button>
            </div>
        </div>

        <!-- NAVIGATION TABS -->
        <?php if(isset($navigation)): ?>
        <div class="mt-6 overflow-x-auto pb-1 scrollbar-hide max-w-screen-2xl mx-auto">
            <div class="inline-flex p-1 bg-slate-200/60 rounded-xl border border-slate-200/60">
                <?php foreach($navigation as $key => $nav): 
                    
                    // ========================================================
                    // PENGAMAN GANDA: Cegah "Akuntansi" muncul di Keuangan Ops
                    // ========================================================
                    if (stripos($key, 'akuntan') !== false || stripos($nav['label'] ?? '', 'akuntan') !== false) {
                        continue;
                    }

                    $isActive = ($key === 'pengeluaran'); 
                    $activeClass = $isActive 
                        ? 'bg-white text-amber-600 shadow-sm ring-1 ring-black/5' 
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

        <!-- Flash Alert -->
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="rounded-xl bg-emerald-50 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center gap-3 animate-pulse">
                <i class="fas fa-check-circle text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold text-emerald-800 tracking-tight"><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif ?>
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="rounded-xl bg-rose-50 border-l-4 border-rose-500 p-4 shadow-sm flex items-center gap-3 animate-pulse">
                <i class="fas fa-exclamation-triangle text-rose-500 text-lg"></i>
                <span class="text-sm font-bold text-rose-800 tracking-tight"><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif ?>

        <!-- Table -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
             <!-- Toolbar / Filter Date -->
             <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                 <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
                     <i class="fas fa-list text-slate-400"></i> Riwayat Pengeluaran
                 </h3>
                 <form action="" method="get" class="flex items-center gap-2">
                    <input type="hidden" name="jenjang" value="<?= $filter_jenjang ?>">
                    <input type="date" name="start_date" value="<?= $start_date ?>" class="py-1.5 px-3 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 outline-none focus:ring-1 focus:ring-amber-500">
                    <span class="text-slate-400">-</span>
                    <input type="date" name="end_date" value="<?= $end_date ?>" class="py-1.5 px-3 border border-slate-200 rounded-lg text-xs font-bold text-slate-600 outline-none focus:ring-1 focus:ring-amber-500">
                    <button type="submit" class="bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-slate-700"><i class="fas fa-search"></i></button>
                 </form>
             </div>

             <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-[10px] uppercase text-slate-400 font-bold border-b border-slate-100 tracking-widest">
                            <th class="px-6 py-4 w-12 text-center">No</th>
                            <th class="px-6 py-4">Tanggal & Status</th>
                            <th class="px-6 py-4">Kategori Akun (COA)</th>
                            <th class="px-6 py-4">Keterangan Ops</th>
                            <th class="px-6 py-4 text-center">Unit</th>
                            <th class="px-6 py-4 text-right">Nominal (Rp)</th>
                            <th class="px-6 py-4 text-center">Bukti</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-600">
                        <?php if(empty($pengeluaran)): ?>
                            <tr><td colspan="8" class="px-6 py-12 text-center text-slate-400 italic">Belum ada pencatatan pengeluaran.</td></tr>
                        <?php else: ?>
                            <?php $no = isset($nomor_urut) ? $nomor_urut + 1 : 1; ?>
                            <?php foreach($pengeluaran as $p): 
                                // LOGIKA TUTUP BUKU (LOCK DATE) DARI CONTROLLER
                                $isLocked = false;
                                if (isset($lock_date) && strtotime($p['tanggal']) <= strtotime($lock_date)) {
                                    $isLocked = true;
                                }
                            ?>
                            <tr class="hover:bg-amber-50/40 transition-colors group <?= $isLocked ? 'bg-slate-50/50 opacity-90' : '' ?>">
                                <td class="px-6 py-4 text-center font-mono text-slate-400"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-700"><?= date('d/m/Y', strtotime($p['tanggal'])) ?></div>
                                    <?php if($isLocked): ?>
                                        <div class="mt-1 flex items-center gap-1 text-[9px] font-black text-rose-500 uppercase tracking-widest">
                                            <i class="fas fa-lock"></i> Locked
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-amber-50 text-amber-700 px-2 py-1 rounded-md text-[10px] font-bold border border-amber-200 uppercase tracking-tight">
                                        <?= esc($p['nama_kategori'] ?? 'BEBAN UMUM') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-700 truncate max-w-[200px]" title="<?= esc($p['keterangan']) ?>">
                                    <?= esc($p['keterangan']) ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded text-[9px] font-bold border border-slate-200 uppercase">
                                        <?= esc($p['kode_jenjang']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-rose-600 tracking-tight text-sm">
                                    <?= number_format($p['jumlah'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if(!empty($p['bukti'])): ?>
                                        <button type="button" onclick="showPreview('<?= base_url('uploads/pengeluaran/' . $p['bukti']) ?>')" class="text-slate-400 hover:text-amber-500 transition border border-slate-200 hover:border-amber-500 rounded p-1.5 shadow-sm"><i class="fas fa-image"></i></button>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if($isLocked): ?>
                                        <!-- JIKA TUTUP BUKU: TAMPILKAN GEMBOK SAJA -->
                                        <div class="flex items-center justify-center opacity-40 cursor-not-allowed" title="Data telah dibukukan. Hubungi Akuntan Yayasan untuk jurnal pembalik.">
                                            <i class="fas fa-lock text-rose-500 text-lg"></i>
                                        </div>
                                    <?php else: ?>
                                        <!-- JIKA TERBUKA: BISA EDIT/HAPUS (Stealth Accounting Active) -->
                                        <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <button @click="openModal(<?= htmlspecialchars(json_encode($p)) ?>)" class="w-8 h-8 flex items-center justify-center text-indigo-600 bg-white border border-indigo-200 hover:bg-indigo-600 hover:text-white rounded-lg shadow-sm transition-all" title="Edit Transaksi">
                                                <i class="fas fa-pen text-[10px]"></i>
                                            </button>
                                            <a href="<?= base_url('app/keuangan/pengeluaran/delete/'.$p['id']) ?>" onclick="return confirm('Hapus data ini? (Sistem akan otomatis menghapus Jurnal Akuntansinya)')" class="w-8 h-8 flex items-center justify-center text-rose-500 bg-white border border-rose-200 hover:bg-rose-500 hover:text-white rounded-lg shadow-sm transition-all" title="Hapus & Batal Jurnal">
                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
             </div>

             <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-50 bg-slate-50/50 flex justify-center">
                <?= isset($pager) ? $pager->links('default', 'tailwind_pagination') : '' ?>
            </div>
        </div>

    </div>

    <!-- MODAL FORM (Alpine.js) -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border-t-8 border-amber-500">
                <div class="bg-white px-8 py-8">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 uppercase italic tracking-tight" x-text="isEdit ? 'Edit Pengeluaran' : 'Catat Pengeluaran Baru'"></h3>
                            <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Sistem otomatis mencatat ke Buku Besar</p>
                        </div>
                        <button @click="showModal = false" class="w-8 h-8 rounded-full bg-slate-100 text-slate-400 hover:bg-rose-100 hover:text-rose-500 flex items-center justify-center transition-colors"><i class="fas fa-times"></i></button>
                    </div>
                    
                    <form action="<?= base_url('app/keuangan/pengeluaran/store') ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" x-model="formData.id">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest">Tanggal <span class="text-rose-500">*</span></label>
                                <input type="date" name="tanggal" x-model="formData.tanggal" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-amber-500 outline-none text-slate-700">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest">Unit <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="kode_jenjang" x-model="formData.kode_jenjang" <?= !$isSuperAdmin ? 'disabled' : '' ?> class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-10 py-3 text-sm font-bold focus:ring-2 focus:ring-amber-500 outline-none appearance-none text-slate-700 disabled:opacity-60 disabled:cursor-not-allowed">
                                        <?php if ($isSuperAdmin): ?><option value="GLOBAL">YAYASAN / PUSAT</option><?php endif; ?>
                                        <?php foreach($jenjang_list as $j): ?>
                                            <option value="<?= $j['kode_jenjang'] ?>">UNIT <?= strtoupper($j['kode_jenjang']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                                </div>
                                <?php if (!$isSuperAdmin): ?><input type="hidden" name="kode_jenjang" x-model="formData.kode_jenjang"><?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest">Kategori (Buku Besar) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="id_kategori" x-model="formData.id_kategori" required class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-10 py-3 text-sm font-bold focus:ring-2 focus:ring-amber-500 outline-none appearance-none text-slate-700">
                                    <option value="" disabled>-- Pilih Kategori Akuntansi --</option>
                                    <?php foreach($kategori_list as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= esc($cat['nama_kategori']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
                            </div>
                        </div>

                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100">
                            <label class="block text-[10px] font-black text-amber-700 mb-2 uppercase tracking-widest">Nominal (Rp) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-amber-600 font-black text-lg">Rp</span>
                                <input type="text" name="jumlah" x-model="formData.jumlah" required @input="formData.jumlah = $event.target.value.replace(/[^,\d]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" class="w-full bg-white border-none rounded-xl pl-12 pr-4 py-4 text-xl font-black text-slate-800 shadow-inner focus:ring-2 focus:ring-amber-500 outline-none tracking-tight">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest">Keterangan / Uraian <span class="text-rose-500">*</span></label>
                            <textarea name="keterangan" x-model="formData.keterangan" rows="2" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium focus:ring-2 focus:ring-amber-500 outline-none text-slate-700 resize-none" placeholder="Jelaskan penggunaan dana secara spesifik..."></textarea>
                        </div>

                         <div>
                            <label class="block text-[10px] font-black text-slate-500 mb-2 uppercase tracking-widest">Upload Bukti (Opsional)</label>
                            <input type="file" name="bukti" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-[10px] file:uppercase file:tracking-widest file:font-black file:bg-amber-100 file:text-amber-700 hover:file:bg-amber-200 transition-colors bg-slate-50 border border-slate-200 rounded-xl">
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="showModal = false" class="flex-1 py-3.5 bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-colors">Batal</button>
                            <button type="submit" class="flex-1 py-3.5 bg-amber-500 text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-amber-600 shadow-lg shadow-amber-500/30 transition-all border-b-4 border-amber-700 active:translate-y-0.5">Simpan Jurnal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Preview Gambar -->
<div id="modalBukti" class="fixed inset-0 z-[150] hidden bg-slate-900/90 flex items-center justify-center p-4 backdrop-blur-md" onclick="closePreview()">
    <div class="bg-white rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl relative" onclick="event.stopPropagation()">
        <div class="p-3 bg-slate-100 flex justify-between items-center border-b border-slate-200">
            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest ml-2">Preview Bukti Transaksi</h3>
            <button onclick="closePreview()" class="w-8 h-8 rounded-full bg-white text-slate-400 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center transition-colors shadow-sm"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 bg-slate-50 flex justify-center min-h-[300px]">
            <img id="imgBukti" src="" class="max-h-[70vh] rounded-lg shadow-md object-contain">
        </div>
    </div>
</div>

<script>
    function showPreview(url) {
        document.getElementById('imgBukti').src = url;
        document.getElementById('modalBukti').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closePreview() {
        document.getElementById('modalBukti').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>

<style>
    .custom-scroll::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    [x-cloak] { display: none !important; }
</style>

<?= $this->endSection() ?>