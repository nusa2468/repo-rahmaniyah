<?php
    $jenjang    = $jenjang ?? ''; 
    $isGlobal   = in_array(strtoupper($jenjang), ['GLOBAL', 'YAYASAN', 'ROOT', 'ALL']);
    $filterUnit = service('request')->getGet('filter_unit');
?>

<div class="space-y-6">
    <!-- Header Laporan -->
    <div class="bg-indigo-600 rounded-3xl p-8 text-white shadow-xl shadow-indigo-200 relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold">Pusat Laporan Kesiswaan</h2>
            <p class="text-indigo-100 mt-2 max-w-2xl">Unduh rekapitulasi data kegiatan, pelanggaran, prestasi, dan alumni dalam format PDF atau Excel untuk keperluan administrasi.</p>
        </div>
        <div class="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-white/10 to-transparent"></div>
        <svg class="absolute -right-6 -bottom-12 text-white/10 w-64 h-64 transform rotate-12" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19 8h-1V3H6v5H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zM8 5h8v3H8V5zm8 12v2H8v-4h8v2zm2-2v-2H6v2H4v-4c0-.55.45-1 1-1h14c.55 0 1 .45 1 1v4h-2z"/><circle cx="18" cy="11.5" r="1"/></svg>
    </div>

    <!-- Grid Laporan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- 1. Laporan Ekskul & Anggota -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"/><path d="M8.5 8.5v.01"/><path d="M16 12l-2-2"/><path d="M12 16l-2-2"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Data Ekstrakurikuler</h3>
                    <p class="text-xs text-slate-500">Daftar ekskul, pembina & anggota</p>
                </div>
            </div>
            <form action="<?= base_url('app/kesiswaan/print/ekskul') ?>" method="post" target="_blank" class="space-y-3">
                <?= csrf_field() ?>
                
                <!-- DROPDOWN UNIT (GLOBAL ONLY) -->
                <?php if($isGlobal): ?>
                <select name="kode_jenjang" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Unit</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                </select>
                <?php endif; ?>

                <select name="jenis_laporan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="daftar_ekskul">Daftar Ekskul & Pembina</option>
                    <option value="rekap_anggota">Rekapitulasi Anggota</option>
                    <option value="jadwal">Jadwal Latihan</option>
                </select>
                <div class="flex gap-2 pt-2">
                    <button type="submit" name="format" value="pdf" class="flex-1 py-2 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-100 transition flex justify-center gap-2 items-center">PDF</button>
                    <button type="submit" name="format" value="excel" class="flex-1 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-sm font-bold hover:bg-emerald-100 transition flex justify-center gap-2 items-center">Excel</button>
                </div>
            </form>
        </div>

        <!-- 2. Laporan Pelanggaran BK -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-2xl bg-rose-50 text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Laporan BK</h3>
                    <p class="text-xs text-slate-500">Kasus siswa, poin & tindak lanjut</p>
                </div>
            </div>
            <form action="<?= base_url('app/kesiswaan/print/bk') ?>" method="post" target="_blank" class="space-y-3">
                <?= csrf_field() ?>
                
                <!-- DROPDOWN UNIT (GLOBAL ONLY) -->
                <?php if($isGlobal): ?>
                <select name="kode_jenjang" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">Semua Unit</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                </select>
                <?php endif; ?>

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-rose-500" placeholder="Dari">
                    <input type="date" name="end_date" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-rose-500" placeholder="Sampai">
                </div>
                <select name="status_kasus" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">Semua Status</option>
                    <option value="Open">Belum Selesai (Open)</option>
                    <option value="Close">Selesai (Closed)</option>
                </select>
                <div class="flex gap-2 pt-2">
                    <button type="submit" name="format" value="pdf" class="flex-1 py-2 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-100 transition flex justify-center gap-2 items-center">PDF</button>
                    <button type="submit" name="format" value="excel" class="flex-1 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-sm font-bold hover:bg-emerald-100 transition flex justify-center gap-2 items-center">Excel</button>
                </div>
            </form>
        </div>

        <!-- 3. Laporan Prestasi -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-2xl bg-amber-50 text-amber-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Prestasi Siswa</h3>
                    <p class="text-xs text-slate-500">Rekap kejuaraan & penghargaan</p>
                </div>
            </div>
            <form action="<?= base_url('app/kesiswaan/print/prestasi') ?>" method="post" target="_blank" class="space-y-3">
                <?= csrf_field() ?>
                
                <!-- DROPDOWN UNIT (GLOBAL ONLY) -->
                <?php if($isGlobal): ?>
                <select name="kode_jenjang" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Unit</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                </select>
                <?php endif; ?>

                <select name="tingkat" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Tingkat</option>
                    <option value="Sekolah">Sekolah</option>
                    <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                    <option value="Provinsi">Provinsi</option>
                    <option value="Nasional">Nasional</option>
                    <option value="Internasional">Internasional</option>
                </select>
                <select name="tahun_ajar_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Tahun Ajar</option>
                    <option value="1">2024/2025</option>
                    <option value="2">2025/2026</option>
                </select>
                <div class="flex gap-2 pt-2">
                    <button type="submit" name="format" value="pdf" class="flex-1 py-2 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-100 transition flex justify-center gap-2 items-center">PDF</button>
                    <button type="submit" name="format" value="excel" class="flex-1 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-sm font-bold hover:bg-emerald-100 transition flex justify-center gap-2 items-center">Excel</button>
                </div>
            </form>
        </div>

        <!-- 4. Laporan Presensi (Absensi Ekskul) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-2xl bg-teal-50 text-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Jurnal & Presensi</h3>
                    <p class="text-xs text-slate-500">Rekap kehadiran kegiatan</p>
                </div>
            </div>
            <form action="<?= base_url('app/kesiswaan/print/presensi') ?>" method="post" target="_blank" class="space-y-3">
                <?= csrf_field() ?>
                
                <!-- DROPDOWN UNIT (GLOBAL ONLY) -->
                <!-- Note: Untuk presensi, idealnya ekskul diload dinamis via AJAX saat unit dipilih, 
                     tapi untuk simplifikasi kita tampilkan dropdown unit agar bisa difilter di controller -->
                <?php if($isGlobal): ?>
                <select name="kode_jenjang" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua Unit</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                </select>
                <?php endif; ?>

                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-teal-500">
                    <input type="date" name="end_date" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                <select name="ekskul_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Semua Ekskul</option>
                    <?php if(isset($ekskul_list)): foreach($ekskul_list as $e): ?>
                        <option value="<?= $e['id'] ?>"><?= $e['nama_ekskul'] ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <div class="flex gap-2 pt-2">
                    <button type="submit" name="format" value="pdf" class="flex-1 py-2 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-100 transition flex justify-center gap-2 items-center">PDF</button>
                    <button type="submit" name="format" value="excel" class="flex-1 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-sm font-bold hover:bg-emerald-100 transition flex justify-center gap-2 items-center">Excel</button>
                </div>
            </form>
        </div>

        <!-- 5. Laporan Alumni -->
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Tracer Alumni</h3>
                    <p class="text-xs text-slate-500">Sebaran lulusan & status</p>
                </div>
            </div>
            <form action="<?= base_url('app/kesiswaan/print/alumni') ?>" method="post" target="_blank" class="space-y-3">
                <?= csrf_field() ?>
                
                <!-- DROPDOWN UNIT (GLOBAL ONLY) -->
                <?php if($isGlobal): ?>
                <select name="kode_jenjang" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Unit</option>
                    <option value="SD">SD</option>
                    <option value="SMP">SMP</option>
                    <option value="SMA">SMA</option>
                </select>
                <?php endif; ?>

                <input type="number" name="tahun_lulus" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Tahun Lulus (Contoh: 2023)">
                <select name="status_kegiatan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="Kuliah">Kuliah</option>
                    <option value="Bekerja">Bekerja</option>
                </select>
                <div class="flex gap-2 pt-2">
                    <button type="submit" name="format" value="pdf" class="flex-1 py-2 bg-rose-50 text-rose-600 rounded-xl text-sm font-bold hover:bg-rose-100 transition flex justify-center gap-2 items-center">PDF</button>
                    <button type="submit" name="format" value="excel" class="flex-1 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-sm font-bold hover:bg-emerald-100 transition flex justify-center gap-2 items-center">Excel</button>
                </div>
            </form>
        </div>

    </div>
</div>