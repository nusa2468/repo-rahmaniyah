<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">

    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase italic">
                <?= esc($title) ?>
            </h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li class="inline-flex items-center uppercase italic">Keuangan</li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <a href="<?= base_url('app/keuangan/tagihan') ?>" class="uppercase hover:text-indigo-600 transition-colors">Tagihan</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase italic underline decoration-2 font-black">#<?= esc($tagihan['id']) ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= base_url('app/keuangan/tagihan') ?>" 
               class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            
            <a href="<?= base_url('app/keuangan/tagihan/form/' . $tagihan['id']) ?>" 
               class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-amber-400 text-slate-900 border-b-4 border-amber-600 hover:bg-amber-500 transition-all shadow-lg active:scale-95">
                <i class="fas fa-edit mr-2"></i> Edit Tagihan
            </a>

            <?php 
                // Cek status real jika ada, atau fallback ke status biasa
                $statusCek = $tagihan['status_real'] ?? $tagihan['status']; 
            ?>
            <?php if ($statusCek !== 'lunas'): ?>
                <a href="<?= base_url('app/keuangan/pembayaran/create/' . $tagihan['id']) ?>" 
                   class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-emerald-600 text-white border-b-4 border-emerald-800 hover:bg-emerald-700 transition-all shadow-lg active:scale-95">
                    <i class="fas fa-cash-register mr-2"></i> Proses Bayar
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT COLUMN: Summary & Siswa -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Summary Stack -->
            <div class="space-y-4">
                <!-- Total Tagihan -->
                <div class="bg-indigo-600 p-5 border-l-8 border-indigo-800 shadow-md text-white group relative overflow-hidden">
                    <i class="fas fa-file-invoice-dollar absolute -right-6 -bottom-6 text-9xl text-white/10 transform -rotate-12 group-hover:rotate-0 transition-transform duration-500"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest text-indigo-200 mb-1 italic">Total Nominal Tagihan</p>
                    <h3 class="text-2xl font-black italic">Rp <?= number_format($tagihan['jumlah'], 0, ',', '.') ?></h3>
                </div>

                <!-- Sudah Terbayar -->
                <div class="bg-emerald-600 p-5 border-l-8 border-emerald-800 shadow-md text-white group relative overflow-hidden">
                    <i class="fas fa-check-circle absolute -right-6 -bottom-6 text-9xl text-white/10 transform -rotate-12 group-hover:rotate-0 transition-transform duration-500"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-200 mb-1 italic">Sudah Terbayar</p>
                    <?php 
                        $terbayar = $tagihan['total_terbayar_real'] ?? $tagihan['total_terbayar'] ?? 0;
                        $sisa = $tagihan['sisa_tagihan'] ?? ($tagihan['jumlah'] - $terbayar);
                        $persen = ($tagihan['jumlah'] > 0) ? ($terbayar / $tagihan['jumlah']) * 100 : 0;
                    ?>
                    <h3 class="text-2xl font-black italic">Rp <?= number_format($terbayar, 0, ',', '.') ?></h3>
                    
                    <!-- Progress Bar -->
                    <div class="mt-3 w-full bg-emerald-800/50 h-2 rounded-none">
                        <div class="bg-white h-2 rounded-none" style="width: <?= $persen ?>%"></div>
                    </div>
                    <p class="text-[9px] font-bold text-emerald-100 text-right mt-1 uppercase tracking-widest italic"><?= round($persen, 1) ?>% Lunas</p>
                </div>

                <!-- Sisa Piutang -->
                <div class="bg-rose-600 p-5 border-l-8 border-rose-800 shadow-md text-white group relative overflow-hidden">
                    <i class="fas fa-exclamation-triangle absolute -right-6 -bottom-6 text-9xl text-white/10 transform -rotate-12 group-hover:rotate-0 transition-transform duration-500"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest text-rose-200 mb-1 italic">Sisa Kewajiban (Piutang)</p>
                    <h3 class="text-2xl font-black italic">Rp <?= number_format($sisa > 0 ? $sisa : 0, 0, ',', '.') ?></h3>
                </div>
            </div>

            <!-- Card Info Siswa -->
            <div class="bg-white border-2 border-slate-200 shadow-xl overflow-hidden">
                <div class="bg-slate-900 px-6 py-3 border-b-2 border-slate-800 flex justify-between items-center">
                    <h3 class="text-[11px] font-black text-white uppercase tracking-widest italic">Data Siswa</h3>
                    <i class="fas fa-user-graduate text-slate-500"></i>
                </div>
                <div class="p-0">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic mb-1">Nama Lengkap</p>
                        <p class="text-sm font-black text-slate-800 uppercase italic"><?= esc($tagihan['nama_lengkap']) ?></p>
                    </div>
                    <div class="px-6 py-4 border-b border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic mb-1">Nomor Induk (NIS)</p>
                        <p class="text-sm font-black text-slate-800 uppercase italic tracking-widest"><?= esc($tagihan['nis']) ?></p>
                    </div>
                    <div class="px-6 py-4 border-b border-slate-100">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic mb-1">Kelas Saat Ini</p>
                        <p class="text-sm font-black text-indigo-600 uppercase italic">
                            <?= !empty($tagihan['nama_kelas']) ? esc($tagihan['nama_kelas']) : '<span class="text-slate-400 italic">Belum ditentukan</span>' ?>
                        </p>
                    </div>
                    <div class="px-6 py-4 bg-slate-50">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic mb-2">Status Saat Ini</p>
                        <?php 
                            $badgeColor = 'bg-slate-500 border-slate-700'; 
                            $statusLabel = 'BELUM LUNAS';
                            
                            if ($statusCek == 'lunas') {
                                $badgeColor = 'bg-emerald-600 border-emerald-800';
                                $statusLabel = 'LUNAS';
                            } elseif ($statusCek == 'mencicil' || $statusCek == 'sebagian') {
                                $badgeColor = 'bg-amber-500 border-amber-700';
                                $statusLabel = 'MENCICIL';
                            } else {
                                $badgeColor = 'bg-rose-600 border-rose-800';
                            }
                        ?>
                        <span class="inline-block px-3 py-1 text-white text-[10px] font-black border-b-4 uppercase italic tracking-widest <?= $badgeColor ?>">
                            <?= $statusLabel ?>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Details & History -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Rincian Tagihan Card -->
            <div class="bg-white border-2 border-slate-200 shadow-xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-3 border-b-2 border-slate-200 flex justify-between items-center">
                    <h3 class="text-[11px] font-black text-slate-700 uppercase tracking-widest italic">Rincian Tagihan</h3>
                    <div class="px-2 py-1 bg-white border border-slate-300 text-[9px] font-bold text-slate-500">ID: #<?= esc($tagihan['id']) ?></div>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-1">Kategori Pembayaran</p>
                        <h4 class="text-lg font-black text-indigo-700 uppercase italic leading-tight">
                            <?= esc($tagihan['nama_pembayaran']) ?>
                        </h4>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-1">Batas Waktu (Jatuh Tempo)</p>
                        <?php 
                            $isOverdue = (strtotime($tagihan['tanggal_jatuh_tempo']) < time() && $statusCek !== 'lunas');
                        ?>
                        <div class="flex items-center gap-3">
                            <h4 class="text-lg font-black <?= $isOverdue ? 'text-rose-600' : 'text-slate-800' ?> uppercase italic leading-tight">
                                <?= date('d M Y', strtotime($tagihan['tanggal_jatuh_tempo'])) ?>
                            </h4>
                            <?php if($isOverdue): ?>
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-600 text-[9px] font-black uppercase tracking-widest border border-rose-200 animate-pulse">Terlambat</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if(!empty($tagihan['keterangan']) || !empty($tagihan['deskripsi'])): ?>
                    <div class="md:col-span-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic mb-2">Deskripsi / Keterangan</p>
                        <div class="p-4 bg-slate-50 border-l-4 border-indigo-500 text-xs font-bold text-slate-600 uppercase italic leading-relaxed">
                            <?= !empty($tagihan['deskripsi']) ? esc($tagihan['deskripsi']) : '' ?>
                            <?= (!empty($tagihan['deskripsi']) && !empty($tagihan['keterangan'])) ? '<br>' : '' ?>
                            <?= !empty($tagihan['keterangan']) ? nl2br(esc($tagihan['keterangan'])) : '' ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Riwayat Pembayaran Card -->
            <div class="bg-white border-2 border-slate-200 shadow-xl overflow-hidden">
                <div class="bg-slate-50 px-6 py-3 border-b-2 border-slate-200 flex justify-between items-center">
                    <h3 class="text-[11px] font-black text-slate-700 uppercase tracking-widest italic">Riwayat Transaksi</h3>
                    <i class="fas fa-history text-slate-400"></i>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white border-b-2 border-slate-100">
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest italic border-r border-slate-50">Tanggal</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest italic border-r border-slate-50">Metode</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest italic border-r border-slate-50 text-right">Jumlah Bayar</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest italic border-r border-slate-50 text-center">Petugas</th>
                                <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest italic text-center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (!empty($pembayaran)): ?>
                                <?php foreach ($pembayaran as $trx): ?>
                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="px-6 py-4 border-r border-slate-50">
                                        <div class="text-[11px] font-black text-slate-800 uppercase italic"><?= date('d/m/Y', strtotime($trx['tanggal_bayar'])) ?></div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase italic"><?= date('H:i', strtotime($trx['tanggal_bayar'])) ?> WIB</div>
                                    </td>
                                    <td class="px-6 py-4 border-r border-slate-50">
                                        <span class="inline-flex items-center px-2 py-1 bg-white border border-slate-200 text-[9px] font-black uppercase tracking-tighter text-slate-600">
                                            <i class="fas <?= $trx['metode_pembayaran'] == 'tunai' ? 'fa-money-bill text-emerald-500' : 'fa-university text-indigo-500' ?> mr-1.5"></i>
                                            <?= esc($trx['metode_pembayaran']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 border-r border-slate-50 text-right">
                                        <div class="text-[11px] font-black text-emerald-600 italic">
                                            + Rp <?= number_format($trx['jumlah_bayar'], 0, ',', '.') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 border-r border-slate-50 text-center">
                                        <div class="text-[10px] font-bold text-slate-500 uppercase"><?= esc($trx['nama_admin'] ?? 'System') ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="<?= base_url('app/keuangan/pembayaran/cetak/' . $trx['id']) ?>" target="_blank"
                                               class="w-7 h-7 flex items-center justify-center bg-white border-2 border-slate-200 text-slate-600 hover:border-indigo-500 hover:text-indigo-600 transition-all shadow-sm"
                                               title="Cetak Kwitansi">
                                                <i class="fas fa-print text-[10px]"></i>
                                            </a>
                                            <?php if(!empty($trx['bukti_bayar'])): ?>
                                                <a href="<?= base_url('uploads/pembayaran/' . $trx['bukti_bayar']) ?>" target="_blank"
                                                   class="w-7 h-7 flex items-center justify-center bg-white border-2 border-slate-200 text-slate-600 hover:border-indigo-500 hover:text-indigo-600 transition-all shadow-sm"
                                                   title="Lihat Bukti">
                                                    <i class="fas fa-image text-[10px]"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center bg-slate-50/50">
                                        <i class="fas fa-receipt text-slate-200 text-5xl mb-3 block"></i>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Belum ada riwayat transaksi pembayaran.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (!empty($pembayaran)): ?>
                <div class="bg-slate-50 px-6 py-2 border-t border-slate-200 text-center">
                    <p class="text-[9px] font-bold text-slate-400 uppercase italic">
                        <i class="fas fa-lock mr-1"></i> Data transaksi terkunci dan tidak dapat dihapus demi keamanan audit.
                    </p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>