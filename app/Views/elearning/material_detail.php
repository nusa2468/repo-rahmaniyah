<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// --- DATA FALLBACK (FIX ERROR DATABASE) ---
// Memperbaiki masalah jika controller tidak mengirimkan data kelas
if (!isset($nama_kelas)) {
    $db = \Config\Database::connect();
    
    // FIX: Hapus 'kode_mapel' dari select. Gunakan 'nama_kelas' saja.
    $courseData = $db->table('el_courses')
                     ->select('nama_kelas') 
                     ->where('id', $id_kelas ?? 0)
                     ->get()
                     ->getRow();
                     
    $nama_kelas = $courseData->nama_kelas ?? 'Kelas E-Learning';
    $mata_pelajaran = 'Materi Pembelajaran'; 
}
?>

<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Navbar Internal (Identik dengan View/People) -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="<?= base_url('app/elearning/view/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Forum</a>
        <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Tugas Kelas</a>
        <a href="<?= base_url('app/elearning/people/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Anggota</a>
    </nav>

    <!-- Banner Kelas (Identik dengan View/People) -->
    <div class="relative h-48 sm:h-64 rounded-xl overflow-hidden shadow-lg mb-8 group">
        <img src="https://www.gstatic.com/classroom/themes/img_read.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Banner">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-6 sm:p-8">
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1 drop-shadow-md"><?= esc($nama_kelas) ?></h1>
            <p class="text-lg text-white/90 drop-shadow-sm"><?= esc($mata_pelajaran ?? 'Umum') ?></p>
        </div>
    </div>

    <!-- Container Konten Detail -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm p-6 sm:p-8 relative">
        
        <!-- Breadcrumb & Navigasi Kembali -->
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-6 border-b border-gray-100 dark:border-white/5 pb-4">
            <a href="<?= base_url('app/elearning/classwork/' . ($id_kelas ?? 0)) ?>" class="hover:text-primary transition-colors flex items-center gap-2 font-medium">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Materi
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-900 dark:text-white font-medium truncate max-w-[200px]"><?= esc($content['judul'] ?? 'Detail Materi') ?></span>
        </div>

        <!-- Header Konten -->
        <div class="flex flex-col sm:flex-row items-start justify-between gap-6 mb-8">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider">
                        <?= esc(ucfirst($content['tipe'] ?? 'Materi')) ?>
                    </span>
                    <span class="flex items-center gap-1 text-xs text-gray-400">
                        <i class="far fa-clock"></i>
                        <?= isset($content['created_at']) ? date('d M Y, H:i', strtotime($content['created_at'])) : '-' ?>
                    </span>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white leading-tight mb-2">
                    <?= esc($content['judul'] ?? 'Judul Tidak Tersedia') ?>
                </h1>
                
                <p class="text-gray-500 text-sm flex items-center gap-2">
                    <span class="bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded text-xs font-medium text-gray-600 dark:text-gray-400">Topik</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300"><?= esc($nama_topik ?? '-') ?></span>
                </p>
            </div>

            <!-- Icon Besar (Visual) -->
            <div class="hidden sm:flex w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-2xl items-center justify-center text-primary text-3xl shadow-inner shrink-0">
                <i class="fas <?= ($content['tipe'] ?? '') == 'tugas' ? 'fa-clipboard-list' : 'fa-book-open' ?>"></i>
            </div>
        </div>

        <!-- Isi Konten -->
        <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">
            <?= nl2br($content['isi_teks'] ?? $content['deskripsi'] ?? 'Tidak ada deskripsi untuk materi ini.') ?>
        </div>

        <!-- Lampiran / File -->
        <?php if (!empty($content['file_lampiran']) || !empty($content['file_path'])): 
            $file = $content['file_lampiran'] ?? $content['file_path'];
        ?>
        <div class="mt-10 pt-6 border-t border-gray-100 dark:border-white/5">
            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-paperclip text-gray-400"></i> Lampiran
            </h4>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center gap-4 group hover:border-primary/30 transition-all hover:shadow-md">
                    <div class="w-12 h-12 bg-white dark:bg-gray-900 rounded-lg flex items-center justify-center text-red-500 shadow-sm group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-pdf text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h6 class="text-sm font-bold text-gray-900 dark:text-white truncate">File Materi Pembelajaran</h6>
                        <p class="text-xs text-gray-500 truncate"><?= esc($file) ?></p>
                    </div>
                    <a href="<?= base_url('uploads/elearning/materials/' . $file) ?>" target="_blank" class="px-4 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-semibold hover:bg-primary hover:text-white hover:border-primary transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-download"></i> <span class="hidden sm:inline">Unduh</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
<?= $this->endSection() ?>