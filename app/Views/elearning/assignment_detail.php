<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// --- DATA FALLBACK (FIX ERROR DATABASE) ---
// Memperbaiki masalah "Unknown column 'kode_mapel'" dengan menghapusnya dari query.
if (!isset($nama_kelas)) {
    $db = \Config\Database::connect();
    
    // FIX: Hanya select nama_kelas karena kode_mapel sudah tidak ada di el_courses
    $courseData = $db->table('el_courses')
                     ->select('nama_kelas') 
                     ->where('id', $id_kelas ?? 0)
                     ->get()
                     ->getRow();
                     
    $nama_kelas = $courseData->nama_kelas ?? 'Kelas E-Learning';
    $mata_pelajaran = 'Tugas Terstruktur'; // Fallback aman
}
?>

<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Navbar Internal (Identik dengan View/People/Material) -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="<?= base_url('app/elearning/view/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Forum</a>
        <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Tugas Kelas</a>
        <a href="<?= base_url('app/elearning/people/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Anggota</a>
    </nav>

    <!-- Banner Kelas (Identik Style) -->
    <div class="relative h-48 sm:h-64 rounded-xl overflow-hidden shadow-lg mb-8 group">
        <img src="https://www.gstatic.com/classroom/themes/img_read.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Banner">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-6 sm:p-8">
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1 drop-shadow-md"><?= esc($nama_kelas) ?></h1>
            <p class="text-lg text-white/90 drop-shadow-sm"><?= esc($mata_pelajaran ?? 'Umum') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        <!-- Sisi Kiri: Detail Instruksi -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm p-6 sm:p-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>

                <!-- Header Tugas -->
                <div class="flex flex-col sm:flex-row items-start justify-between gap-6 mb-8 border-b border-gray-100 dark:border-white/5 pb-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider">
                                <i class="fas fa-clipboard-list mr-1"></i> Tugas
                            </span>
                            <?php if(!empty($content['deadline'])): ?>
                            <span class="text-xs font-bold text-red-500 flex items-center gap-1">
                                <i class="far fa-calendar-alt"></i>
                                Tenggat: <?= date('d M Y, H:i', strtotime($content['deadline'])) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white leading-tight mb-2">
                            <?= esc($content['judul'] ?? 'Judul Tugas') ?>
                        </h1>
                        
                        <p class="text-gray-500 text-sm flex items-center gap-2">
                            Diposting: <span class="font-medium text-gray-700 dark:text-gray-300"><?= isset($content['created_at']) ? date('d M Y', strtotime($content['created_at'])) : '-' ?></span>
                            <span class="text-gray-300">•</span>
                            Topik: <span class="font-medium text-gray-700 dark:text-gray-300"><?= esc($nama_topik ?? 'Tanpa Topik') ?></span>
                        </p>
                    </div>

                    <div class="flex flex-col items-end">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300"><?= esc($content['poin_max'] ?? '100') ?> Poin</span>
                    </div>
                </div>

                <!-- Isi Instruksi -->
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed mb-10">
                    <?= nl2br($content['isi_teks'] ?? $content['deskripsi'] ?? 'Tidak ada instruksi khusus untuk tugas ini.') ?>
                </div>

                <!-- Lampiran Tugas -->
                <?php if (!empty($content['file_lampiran'])): ?>
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest">Lampiran Tugas</h4>
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4 group hover:border-emerald-500/30 transition-all">
                        <div class="w-12 h-12 bg-white dark:bg-gray-900 rounded-lg flex items-center justify-center text-red-500 shadow-sm group-hover:scale-110 transition-transform">
                            <i class="fas fa-file-pdf text-2xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h6 class="text-sm font-bold text-gray-900 dark:text-white truncate">Panduan Tugas.pdf</h6>
                            <p class="text-xs text-gray-500 truncate"><?= esc($content['file_lampiran']) ?></p>
                        </div>
                        <a href="<?= base_url('uploads/elearning/materials/' . $content['file_lampiran']) ?>" target="_blank" class="px-4 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-emerald-500 hover:text-white transition-all shadow-sm">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sisi Kanan: Panel Pengumpulan -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-lg p-6 sticky top-32">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-gray-800 dark:text-white">Tugas Anda</h3>
                    <span class="text-[10px] font-black uppercase tracking-tighter text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded">Diberikan</span>
                </div>

                <!-- Form Upload -->
                <form action="#" method="post" enctype="multipart/form-data" class="space-y-4">
                    <div class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center group hover:border-primary transition-colors cursor-pointer">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-300 group-hover:text-primary mb-2"></i>
                        <p class="text-xs text-gray-500 group-hover:text-gray-700 transition-colors">Klik untuk tambah file atau seret file ke sini</p>
                        <input type="file" class="hidden">
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary text-white font-bold rounded-xl shadow-md hover:bg-primary-dark transition-all transform active:scale-95">
                        Tandai Selesai
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-white/5">
                    <button class="w-full py-2 text-xs font-bold text-gray-400 hover:text-primary transition-colors">
                        + Tambah Komentar Pribadi
                    </button>
                </div>
            </div>
            
            <p class="text-[10px] text-gray-400 text-center px-4">
                Tugas yang dikumpulkan setelah tenggat waktu akan ditandai sebagai "Terlambat".
            </p>
        </div>

    </div>
</div>
<?= $this->endSection() ?>