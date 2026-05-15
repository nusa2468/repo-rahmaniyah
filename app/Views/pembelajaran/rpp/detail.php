<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
    // [FIX ERROR] Inisialisasi variabel navigasi jika tidak dikirim dari controller
    $prev_id = $prev_id ?? null;
    $next_id = $next_id ?? null;

    // [NEW] AMBIL IDENTITAS SEKOLAH DINAMIS
    $sekolah = [
        'nama'   => 'Satuan Pendidikan',
        'alamat' => '',
        'kepsek' => '............................',
        'nip'    => '' // NIP dikosongkan (request hapus)
    ];

    try {
        if (class_exists('\App\Models\SettingsModel')) {
            $settingsModel = new \App\Models\SettingsModel();
            // Gunakan null coalescing operator untuk mencegah error jika key tidak ada
            $jenjangRef = $rpp['kode_jenjang'] ?? 'GLOBAL'; 
            $config = $settingsModel->getSettingsAsArray($jenjangRef);
            
            if (!empty($config)) {
                $sekolah['nama']   = $config['nama_sekolah'] ?? ('Unit ' . $jenjangRef);
                $sekolah['alamat'] = $config['alamat'] ?? '';
                $sekolah['kepsek'] = $config['kepala_sekolah'] ?? '............................';
            }
        }
    } catch (\Throwable $e) {}

    // [FIX DATA KOSONG] Ambil Nama Mapel Manual jika di Controller belum di-join
    if (empty($rpp['nama_mapel']) && !empty($rpp['silabus_id'])) {
        try {
            $db = \Config\Database::connect();
            $query = $db->table('pembelajaran_silabus')
                        ->select('mata_pelajaran.nama_mapel')
                        ->join('mata_pelajaran', 'mata_pelajaran.id = pembelajaran_silabus.mata_pelajaran_id', 'left')
                        ->where('pembelajaran_silabus.id', $rpp['silabus_id'])
                        ->get()
                        ->getRow();
            
            if ($query) {
                $rpp['nama_mapel'] = $query->nama_mapel;
            }
        } catch (\Throwable $e) {}
    }
?>

<div class="max-w-5xl mx-auto px-4 py-8 font-sans">
    
    <!-- Header Navigasi & Aksi -->
    <div class="flex flex-col md:flex-row items-center justify-between mb-6 no-print gap-4">
        
        <!-- Breadcrumb & Back -->
        <div class="flex items-center gap-4 w-full md:w-auto">
            <a href="<?= base_url('app/pembelajaran/rpp') ?>" class="flex items-center justify-center w-10 h-10 bg-white border border-gray-200 rounded-xl text-gray-500 hover:text-indigo-600 hover:border-indigo-200 shadow-sm transition-all group">
                <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            
            <!-- Navigasi Next/Prev -->
            <div class="flex bg-white rounded-xl shadow-sm border border-gray-200 divide-x divide-gray-200 overflow-hidden">
                <?php if ($prev_id): ?>
                    <a href="<?= base_url('app/pembelajaran/rpp/' . $prev_id) ?>" class="px-4 py-2 hover:bg-gray-50 text-gray-600 hover:text-indigo-600 transition-colors flex items-center gap-2" title="RPP Sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        <span class="text-xs font-bold hidden sm:inline">Prev</span>
                    </a>
                <?php else: ?>
                    <span class="px-4 py-2 bg-gray-50 text-gray-300 cursor-not-allowed flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        <span class="text-xs font-bold hidden sm:inline">Prev</span>
                    </span>
                <?php endif; ?>

                <?php if ($next_id): ?>
                    <a href="<?= base_url('app/pembelajaran/rpp/' . $next_id) ?>" class="px-4 py-2 hover:bg-gray-50 text-gray-600 hover:text-indigo-600 transition-colors flex items-center gap-2" title="RPP Selanjutnya">
                        <span class="text-xs font-bold hidden sm:inline">Next</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                <?php else: ?>
                    <span class="px-4 py-2 bg-gray-50 text-gray-300 cursor-not-allowed flex items-center gap-2">
                        <span class="text-xs font-bold hidden sm:inline">Next</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol Aksi Kanan -->
        <div class="flex gap-2 w-full md:w-auto justify-end">
            <!-- Tombol Cetak (Ditambahkan Kembali) -->
            <button onclick="window.print()" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-sm font-bold shadow-md shadow-gray-800/20 inline-flex items-center transition-all active:scale-95">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak
            </button>
            
            <a href="<?= base_url('app/pembelajaran/rpp/edit/' . $rpp['id']) ?>" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold shadow-md shadow-amber-500/20 inline-flex items-center transition-all active:scale-95">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00 2 2h11a2 2 0 00 2-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit
            </a>
        </div>
    </div>

    <!-- DOKUMEN RPP RESMI (Paper Style) -->
    <div class="bg-white shadow-2xl rounded-sm border border-gray-200 overflow-hidden print-container relative">
        
        <!-- Watermark Background -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none">
             <i class="fas fa-file-signature text-[400px]"></i>
        </div>

        <!-- KOP SURAT DINAMIS -->
        <div class="hidden print:block pt-4 px-10 text-center border-b-2 border-black mb-4 pb-4">
            <h2 class="text-xl font-black uppercase"><?= esc($sekolah['nama']) ?></h2>
            <p class="text-sm"><?= esc($sekolah['alamat']) ?></p>
        </div>

        <!-- Header Dokumen -->
        <div class="p-10 border-b-2 border-black text-center relative z-10">
            <h1 class="text-xl font-black uppercase tracking-widest text-black">RENCANA PELAKSANAAN PEMBELAJARAN (RPP)</h1>
            <h2 class="text-md font-bold uppercase mt-1 text-gray-700">
                <?= ($rpp['jenis_kurikulum'] ?? '') == 'Merdeka' ? 'MODUL AJAR KURIKULUM MERDEKA' : 'KURIKULUM 2013' ?>
            </h2>
            
            <div class="mt-8 grid grid-cols-2 text-left text-sm font-bold border-t border-gray-300 pt-4 gap-4">
                <div class="space-y-1">
                    <p><span class="inline-block w-36 text-gray-500">Satuan Pendidikan</span> : <?= esc($sekolah['nama']) ?></p>
                    <p><span class="inline-block w-36 text-gray-500">Mata Pelajaran</span> : <?= esc($rpp['nama_mapel'] ?? '-') ?></p>
                    <p><span class="inline-block w-36 text-gray-500">Pertemuan Ke</span> : <?= esc($rpp['pertemuan_ke'] ?? '-') ?></p>
                </div>
                <div class="space-y-1 text-right md:text-left md:pl-20">
                    <p><span class="inline-block w-24 text-gray-500">Kurikulum</span> : <?= esc($rpp['jenis_kurikulum'] ?? '-') ?></p>
                    <p><span class="inline-block w-24 text-gray-500">Fase/Tema</span> : <?= ($rpp['jenis_kurikulum'] ?? '') == 'Merdeka' ? esc($rpp['fase'] ?? '-') : esc($rpp['tema'] ?? '-') ?></p>
                    <p><span class="inline-block w-24 text-gray-500">Alokasi Waktu</span> : <?= esc($rpp['alokasi_waktu'] ?? '2 x 45 Menit') ?></p>
                </div>
            </div>
        </div>

        <!-- Konten Dokumen -->
        <div class="p-10 space-y-8 relative z-10">
            
            <!-- A. TUJUAN -->
            <section>
                <h3 class="font-black border-b border-gray-300 pb-1 mb-3 text-gray-900 uppercase text-sm flex items-center gap-2">
                    <span class="bg-black text-white px-1.5 rounded text-xs">A</span> Tujuan Pembelajaran
                </h3>
                <div class="text-sm leading-relaxed text-gray-800 text-justify">
                    <?= nl2br(esc($rpp['tujuan_pembelajaran'] ?? '')) ?>
                </div>
            </section>

            <?php if(($rpp['jenis_kurikulum'] ?? '') == 'Merdeka'): ?>
                <!-- KHUSUS MERDEKA -->
                <section class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-black border-b border-gray-300 pb-1 mb-3 text-gray-900 uppercase text-sm flex items-center gap-2">
                            <span class="bg-black text-white px-1.5 rounded text-xs">B</span> Pemahaman Bermakna
                        </h3>
                        <div class="text-sm italic text-gray-700">
                            <?= nl2br(esc($rpp['pemahaman_bermakna'] ?? '-')) ?>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-black border-b border-gray-300 pb-1 mb-3 text-gray-900 uppercase text-sm flex items-center gap-2">
                            <span class="bg-black text-white px-1.5 rounded text-xs">C</span> Pertanyaan Pemantik
                        </h3>
                        <div class="text-sm text-gray-700">
                            <?= nl2br(esc($rpp['pertanyaan_pemantik'] ?? '-')) ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- D/B. LANGKAH-LANGKAH -->
            <section>
                <h3 class="font-black border-b border-gray-300 pb-1 mb-3 text-gray-900 uppercase text-sm flex items-center gap-2">
                    <span class="bg-black text-white px-1.5 rounded text-xs"><?= ($rpp['jenis_kurikulum'] ?? '') == 'Merdeka' ? 'D' : 'B' ?></span> 
                    <?= ($rpp['jenis_kurikulum'] ?? '') == 'Merdeka' ? 'Kegiatan Pembelajaran' : 'Langkah-langkah Pembelajaran' ?>
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="p-3 bg-gray-50 border rounded-lg text-xs">
                        <p class="font-bold mb-1 text-gray-500 uppercase text-[10px]">Metode</p>
                        <p class="font-semibold text-gray-900"><?= esc($rpp['metode_pembelajaran'] ?? 'N/A') ?></p>
                    </div>
                    <div class="p-3 bg-gray-50 border rounded-lg text-xs md:col-span-2">
                        <p class="font-bold mb-1 text-gray-500 uppercase text-[10px]">Media & Alat</p>
                        <p class="font-semibold text-gray-900"><?= esc($rpp['media_alat'] ?? 'N/A') ?></p>
                    </div>
                </div>
                
                <div class="text-sm leading-loose text-gray-800 whitespace-pre-line border-l-4 border-gray-200 pl-6 py-2">
                    <?= esc($rpp['langkah_pembelajaran'] ?? '') ?>
                </div>
            </section>

            <!-- E/C. ASESMEN -->
            <section>
                <h3 class="font-black border-b border-gray-300 pb-1 mb-3 text-gray-900 uppercase text-sm flex items-center gap-2">
                    <span class="bg-black text-white px-1.5 rounded text-xs"><?= ($rpp['jenis_kurikulum'] ?? '') == 'Merdeka' ? 'E' : 'C' ?></span> 
                    <?= ($rpp['jenis_kurikulum'] ?? '') == 'Merdeka' ? 'Asesmen / Penilaian' : 'Penilaian' ?>
                </h3>
                <div class="text-sm text-gray-800 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <?= nl2br(esc($rpp['penilaian'] ?? 'Belum ditentukan.')) ?>
                </div>
            </section>

            <!-- Tanda Tangan Dinamis (TANPA NIP & GARIS BAWAH) -->
            <div class="mt-20 grid grid-cols-2 gap-10">
                <div class="text-center">
                    <p class="text-sm mb-20 font-bold text-gray-600">Mengetahui,<br>Kepala Sekolah</p>
                    
                    <p class="font-bold w-48 mx-auto">
                        <?= esc($sekolah['kepsek']) ?>
                    </p>
                    <!-- NIP Dihapus sesuai permintaan -->
                </div>
                <div class="text-center">
                    <p class="text-sm mb-20 font-bold text-gray-600">
                        Jakarta, <?= date('d F Y') ?><br>Guru Mata Pelajaran
                    </p>
                    
                    <!-- Nama Guru -->
                    <p class="font-bold w-48 mx-auto">
                        <?= esc(session()->get('fullname') ?? '............................') ?>
                    </p>
                    <!-- NIP Dihapus sesuai permintaan -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        @page { margin: 1.5cm; size: A4; }
        body { background: white !important; color: black !important; -webkit-print-color-adjust: exact; }
        .no-print, nav, aside, header, footer { display: none !important; }
        .max-w-5xl { width: 100% !important; max-width: none !important; margin: 0; padding: 0; }
        .print-container { box-shadow: none !important; border: none !important; border-radius: 0 !important; }
        .p-10 { padding: 0 !important; }
        .hidden.print\:block { display: block !important; }
        section { page-break-inside: avoid; }
        .bg-gray-50 { background-color: transparent !important; border: 1px solid #ddd !important; }
        .text-gray-500 { color: #666 !important; }
    }
</style>
<?= $this->endSection() ?>