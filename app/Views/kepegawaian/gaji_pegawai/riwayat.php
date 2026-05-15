<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Font Premium -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans antialiased text-slate-800">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div>
            <nav class="flex mb-3">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/kepegawaian/dashboard') ?>" class="hover:text-indigo-600 transition-colors">KEPEGAWAIAN</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li><a href="<?= base_url('app/kepegawaian/gaji-pegawai') ?>" class="hover:text-indigo-600 transition-colors">PAYROLL</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600 italic">RIWAYAT SLIP</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 dark:text-white uppercase italic leading-none">
                Riwayat Gaji <span class="text-indigo-600"><?= esc($pegawai['nama_lengkap']) ?></span>
            </h1>
        </div>

        <div class="flex gap-3">
             <!-- PERBAIKAN: Link Kembali mengarah ke halaman Kelola (Setting Individu) -->
            <a href="<?= base_url('app/kepegawaian/gaji-pegawai/kelola/' . $pegawai['id']) ?>" 
               class="inline-flex items-center px-6 py-3 bg-white border-2 border-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:border-indigo-600 hover:text-indigo-600 transition-all shadow-sm active:scale-95">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Setting
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGE -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl flex items-center shadow-sm animate-bounce">
            <i class="fas fa-check-circle text-emerald-500 mr-3"></i>
            <p class="text-xs font-black uppercase text-emerald-800 tracking-tight"><?= session()->getFlashdata('message') ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-2xl flex items-center shadow-sm">
            <i class="fas fa-exclamation-triangle text-rose-500 mr-3"></i>
            <p class="text-xs font-black uppercase text-rose-800 tracking-tight"><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <!-- TABEL RIWAYAT -->
    <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] border-2 border-slate-100 dark:border-white/5 shadow-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 dark:border-white/10 bg-slate-50 dark:bg-white/5">
            <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest italic flex items-center gap-2">
                <i class="fas fa-history text-indigo-500"></i> Arsip Slip Gaji (24 Bulan Terakhir)
            </h3>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-indigo-900 text-white italic">
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] w-16 text-center">No</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">Periode</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em]">No. Transaksi</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">Pendapatan</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">Potongan</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-right">THP Bersih</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px]">
                    <?php if(empty($riwayat_gaji)): ?>
                        <tr><td colspan="8" class="px-8 py-20 text-center opacity-40 italic">Belum ada slip gaji yang digenerate.</td></tr>
                    <?php else: ?>
                        <?php 
                            $no = 1;
                            $namaBulan = [ '01'=>'JAN', '02'=>'FEB', '03'=>'MAR', '04'=>'APR', '05'=>'MEI', '06'=>'JUN', '07'=>'JUL', '08'=>'AGU', '09'=>'SEP', '10'=>'OKT', '11'=>'NOV', '12'=>'DES'];
                            foreach($riwayat_gaji as $slip): 
                                $isPaid = $slip['status_bayar'] === 'Dibayar';
                        ?>
                            <tr class="hover:bg-indigo-50/20 transition-colors group">
                                <td class="px-8 py-5 text-center font-black text-slate-300"><?= $no++ ?></td>
                                <td class="px-6 py-5 font-black text-slate-700">
                                    <?= $namaBulan[$slip['bulan']] ?? $slip['bulan'] ?> <?= $slip['tahun'] ?>
                                </td>
                                <td class="px-6 py-5 text-xs text-slate-500 uppercase font-bold tracking-wider">
                                    <?= esc($slip['no_transaksi']) ?>
                                </td>
                                <td class="px-6 py-5 text-right font-bold text-emerald-600">
                                    Rp <?= number_format($slip['total_pendapatan'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-5 text-right font-bold text-rose-500">
                                    Rp <?= number_format($slip['total_potongan'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-5 text-right font-black text-indigo-700 bg-indigo-50/30">
                                    Rp <?= number_format($slip['gaji_bersih'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <?php if ($isPaid): ?>
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-600 border border-emerald-200">
                                            LUNAS
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase bg-rose-50 text-rose-500 border border-rose-100">
                                            BELUM
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- TOMBOL BAYAR (Hanya jika belum lunas) -->
                                        <?php if (!$isPaid): ?>
                                            <button type="button" 
                                                    onclick="openPaymentModal(<?= $slip['id'] ?>, '<?= esc($slip['no_transaksi']) ?>', <?= $slip['gaji_bersih'] ?>)" 
                                                    class="inline-flex items-center justify-center w-8 h-8 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-all active:scale-95" 
                                                    title="Proses Pembayaran">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- TOMBOL CETAK SLIP -->
                                        <a href="<?= base_url('app/kepegawaian/gaji-pegawai/slip/' . $slip['id']) ?>" target="_blank" 
                                           class="inline-flex items-center justify-center w-8 h-8 bg-slate-900 text-white rounded-lg hover:bg-indigo-600 transition-all shadow-md active:scale-95"
                                           title="Cetak Slip">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL PEMBAYARAN GAJI -->
<div id="paymentModal" class="hidden fixed inset-0 z-[100] overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 text-center font-sans">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity" onclick="closePaymentModal()"></div>
        <div class="inline-block bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-md w-full border-b-8 border-emerald-600">
            <!-- Form Action menuju rute 'bayar' -->
            <form action="<?= base_url('app/kepegawaian/gaji-pegawai/bayar') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="bayar_id">
                
                <div class="p-10">
                    <div class="flex items-center gap-5 mb-8">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shadow-inner border border-emerald-100"><i class="fas fa-hand-holding-usd"></i></div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest italic leading-none">Konfirmasi Pembayaran</h3>
                            <p id="bayar_no_ref" class="text-xs font-bold text-slate-400 mt-2 uppercase italic tracking-tight"></p>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 mb-6 text-center">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Tagihan Gaji</p>
                        <h2 class="text-3xl font-black text-slate-800 tracking-tighter" id="bayar_amount">Rp 0</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal Bayar</label>
                            <input type="date" name="tanggal_bayar" value="<?= date('Y-m-d') ?>" required class="w-full px-5 py-3 bg-white border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-emerald-500 outline-none">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Metode Pembayaran</label>
                            <select name="metode_bayar" class="w-full px-5 py-3 bg-white border-2 border-slate-100 rounded-2xl text-xs font-bold uppercase focus:border-emerald-500 outline-none appearance-none cursor-pointer">
                                <option value="Transfer">Transfer Bank</option>
                                <option value="Tunai">Tunai / Cash</option>
                                <option value="Cek">Cek</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-10 flex gap-3">
                        <button type="button" onclick="closePaymentModal()" class="flex-1 px-6 py-4 bg-slate-100 text-[10px] font-black text-slate-500 uppercase tracking-widest rounded-2xl hover:bg-slate-200 transition-all">BATAL</button>
                        <button type="submit" class="flex-1 px-6 py-4 bg-emerald-600 text-[10px] font-black text-white uppercase tracking-widest rounded-2xl shadow-xl hover:bg-emerald-700 transition-all border-b-4 border-emerald-800 active:scale-95">PROSES BAYAR</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPaymentModal(id, noRef, amount) {
        document.getElementById('bayar_id').value = id;
        document.getElementById('bayar_no_ref').innerText = noRef;
        // Format number dengan locale Indonesia
        document.getElementById('bayar_amount').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        document.getElementById('paymentModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<?= $this->endSection() ?>