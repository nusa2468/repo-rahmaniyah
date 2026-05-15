<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
    // Normalisasi data object
    $p = $pendaftar;
    
    // Helper visual
    $isLunas  = ($p->status_pembayaran ?? '') === 'Lunas';
    $selColor = match(strtolower($p->status_seleksi ?? 'pending')) {
        'lolos'   => 'emerald',
        'pending' => 'amber',
        'gagal'   => 'red',
        default   => 'gray',
    };
?>

<div class="max-w-7xl mx-auto">
    <!-- Header Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <a href="<?= base_url('app/ppdb/list') ?>"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold text-xs uppercase tracking-widest rounded-xl transition-all">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Daftar
        </a>

        <div class="flex flex-wrap gap-2">
            <a href="<?= base_url('app/ppdb/print/' . $p->pendaftar_id) ?>" target="_blank"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-print"></i>
                Cetak Formulir
            </a>
            <a href="<?= base_url('app/ppdb/edit/' . $p->pendaftar_id) ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                <i class="fas fa-edit"></i>
                Edit Data
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-white/10 overflow-hidden">
        
        <!-- 1. Profile Header (Gradient) -->
        <div class="bg-gradient-to-br from-sky-500 to-sky-700 p-6 text-white">
            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-24 h-24 rounded-2xl bg-white text-sky-600 flex items-center justify-center text-4xl font-black shadow-xl shrink-0">
                        <?= strtoupper(substr($p->nama_lengkap ?? '?', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded-md bg-white/20 text-white text-[10px] font-black uppercase tracking-wider">
                                <?= esc($p->kode_jenjang ?? 'REG') ?>
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-white/10 text-sky-100 text-[10px] font-mono font-bold">
                                <?= esc($p->tahun_ajaran ?? date('Y')) ?>
                            </span>
                        </div>
                        <h1 class="text-3xl font-black leading-tight"><?= esc($p->no_pendaftaran) ?></h1>
                        <h2 class="text-xl font-bold mt-1 text-white opacity-90"><?= esc($p->nama_lengkap) ?></h2>
                        <div class="flex flex-wrap items-center gap-3 mt-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/20 text-white text-xs font-bold">
                                <i class="fas fa-school"></i>
                                <?= esc($p->asal_sekolah ?? 'N/A') ?>
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 text-white text-xs font-bold">
                                Jalur <?= esc($p->jalur_masuk) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="lg:ml-auto lg:text-right mt-4 lg:mt-0">
                    <p class="text-sky-100 text-xs uppercase tracking-widest opacity-90">Status Seleksi</p>
                    <span class="inline-block mt-2 px-6 py-3 rounded-full text-lg font-black uppercase bg-<?= $selColor ?>-600 text-white shadow-lg border-2 border-white/20">
                        <?= esc(strtoupper($p->status_seleksi ?? 'Pending')) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="p-6 lg:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- KIRI: Biodata & Orang Tua -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- 2. Biodata Lengkap -->
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-5 flex items-center gap-2">
                            <i class="fas fa-user-circle text-sky-600"></i>
                            Biodata Lengkap
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">NIK</p>
                                <p class="text-base font-bold text-gray-900 dark:text-white mt-1 font-mono"><?= esc($p->nik ?? '-') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">NISN</p>
                                <p class="text-base font-bold text-gray-900 dark:text-white mt-1 font-mono"><?= esc($p->nisn ?? '-') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Jenis Kelamin</p>
                                <p class="text-base font-bold text-gray-900 dark:text-white mt-1">
                                    <?php 
                                        if (($p->jenis_kelamin ?? '') === 'L') echo 'Laki-laki';
                                        elseif (($p->jenis_kelamin ?? '') === 'P') echo 'Perempuan';
                                        else echo '-';
                                    ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Tempat, Tanggal Lahir</p>
                                <p class="text-base font-bold text-gray-900 dark:text-white mt-1">
                                    <?= esc($p->tempat_lahir ?? '-') ?>, <?= ($p->tanggal_lahir && $p->tanggal_lahir != '0000-00-00') ? date('d F Y', strtotime($p->tanggal_lahir)) : '-' ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">No. WhatsApp</p>
                                <p class="text-base font-bold text-gray-900 dark:text-white mt-1 flex items-center gap-2">
                                    <i class="fab fa-whatsapp text-emerald-500"></i>
                                    <?= esc($p->no_hp_whatsapp ?? '-') ?>
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Alamat Domisili</p>
                                <p class="text-base font-medium text-gray-900 dark:text-white mt-1 leading-relaxed"><?= esc($p->alamat_lengkap ?? '-') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Data Orang Tua -->
                    <div class="pt-6 border-t border-gray-100 dark:border-white/10">
                        <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-5 flex items-center gap-2">
                            <i class="fas fa-users text-emerald-600"></i>
                            Data Orang Tua
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nama Ayah Kandung</p>
                                <p class="text-base font-bold text-gray-900 dark:text-white mt-1"><?= esc($p->nama_ayah ?? '-') ?></p>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Nama Ibu Kandung</p>
                                <p class="text-base font-bold text-gray-900 dark:text-white mt-1"><?= esc($p->nama_ibu ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KANAN: Info Akademik & Administrasi -->
                <div class="space-y-8">
                    
                    <!-- 4. Akademik -->
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-5 flex items-center gap-2">
                            <i class="fas fa-graduation-cap text-indigo-600"></i>
                            Info Akademik
                        </h3>
                        <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-500/10 dark:to-indigo-600/10 rounded-2xl p-6 text-center border border-indigo-100 dark:border-indigo-500/20">
                            <p class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Skor / Nilai Akhir</p>
                            <p class="text-5xl font-black text-indigo-700 dark:text-indigo-300 mt-3">
                                <?= number_format($p->skor_akhir ?? 0, 2) ?>
                            </p>
                            <p class="text-sm font-bold text-indigo-600 dark:text-indigo-400 mt-4 uppercase tracking-wider">
                                Jalur <?= esc($p->jalur_masuk) ?>
                            </p>
                        </div>
                    </div>

                    <!-- 5. Administrasi (Pembayaran) -->
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-5 flex items-center gap-2">
                            <i class="fas fa-wallet text-emerald-600"></i>
                            Keuangan
                        </h3>
                        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-500/10 dark:to-emerald-600/10 rounded-2xl p-6 space-y-5 border border-emerald-100 dark:border-emerald-500/20">
                            <div>
                                <p class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Status Pembayaran</p>
                                <p class="text-2xl font-black <?= $isLunas ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' ?> mt-2">
                                    <?= esc(strtoupper($p->status_pembayaran ?? 'Belum Bayar')) ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-black text-gray-600 dark:text-gray-400 uppercase tracking-widest">Metode Pembayaran</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                                    <?= esc($p->metode_bayar ?? 'Belum Ditentukan') ?>
                                </p>
                            </div>

                            <?php if (!empty($p->bukti_setor)): ?>
                                <a href="<?= base_url('uploads/ppdb/bukti_bayar/' . $p->bukti_setor) ?>" target="_blank"
                                   class="block mt-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm uppercase tracking-widest rounded-xl text-center shadow-md hover:shadow-lg transition-all">
                                    <i class="fas fa-file-image mr-2"></i>
                                    Lihat Bukti Bayar
                                </a>
                            <?php else: ?>
                                <div class="px-4 py-3 bg-white/50 dark:bg-black/20 rounded-lg text-center text-xs text-gray-500 italic">
                                    Bukti bayar belum diupload
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 6. Afiliasi Marketing (Jika Ada) -->
                    <?php if (!empty($p->kode_afiliasi)): ?>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tight mb-5 flex items-center gap-2">
                            <i class="fas fa-handshake text-sky-600"></i>
                            Afiliasi Marketing
                        </h3>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-white/10 space-y-4">
                            <div>
                                <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Kode Agen</p>
                                <p class="text-lg font-black text-sky-600 dark:text-sky-400 mt-1 flex items-center gap-2">
                                    <i class="fas fa-tag"></i> <?= esc($p->kode_afiliasi) ?>
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Komisi / Fee</p>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white mt-1">
                                        Rp <?= number_format($p->nominal_fee ?? 0, 0, ',', '.') ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-500 uppercase tracking-widest">Status Fee</p>
                                    <?php 
                                        $feeStatus = $p->status_fee ?? 'Pending';
                                        $feeColor = ($feeStatus === 'Dibayar') ? 'text-emerald-600 bg-emerald-100' : 'text-amber-600 bg-amber-100';
                                    ?>
                                    <span class="inline-block mt-1 px-2 py-1 rounded-md text-xs font-bold <?= $feeColor ?>">
                                        <?= esc($feeStatus) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-white/10 text-center text-xs text-gray-500 dark:text-gray-400">
            Didaftarkan pada <span class="font-bold text-gray-700 dark:text-gray-300"><?= ($p->created_at) ? date('d/m/Y H:i', strtotime($p->created_at)) : '-' ?></span>
            <span class="mx-2">•</span>
            Terakhir diperbarui <span class="font-bold text-gray-700 dark:text-gray-300"><?= ($p->updated_at) ? date('d/m/Y H:i', strtotime($p->updated_at)) : '-' ?></span>
        </div>
    </div>
</div>

<?= $this->endSection() ?>