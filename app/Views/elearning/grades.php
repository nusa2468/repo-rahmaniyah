<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// --- DATA FALLBACK ---
// Memastikan variabel banner tetap tersedia jika tidak dikirim lengkap dari controller
if (!isset($nama_kelas)) {
    $db = \Config\Database::connect();
    $courseData = $db->table('el_courses')->select('nama_kelas')->where('id', $id_kelas)->get()->getRow();
    $nama_kelas = $courseData->nama_kelas ?? 'Kelas E-Learning';
    $mata_pelajaran = 'Buku Nilai Digital';
}
?>

<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Navbar Internal (Identik dengan View/People/Classwork) -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="<?= base_url('app/elearning/view/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Forum</a>
        <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Tugas Kelas</a>
        <a href="<?= base_url('app/elearning/people/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Anggota</a>
        <a href="<?= base_url('app/elearning/grades/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Nilai</a>
    </nav>

    <!-- Banner Kelas (Identik Style) -->
    <div class="relative h-48 sm:h-64 rounded-xl overflow-hidden shadow-lg group mb-8">
        <img src="https://www.gstatic.com/classroom/themes/img_read.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Banner">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-6 sm:p-8">
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1 drop-shadow-md"><?= esc($nama_kelas) ?></h1>
            <p class="text-lg text-white/90 drop-shadow-sm"><?= esc($mata_pelajaran ?? 'Umum') ?></p>
        </div>
    </div>

    <!-- Container Utama Buku Nilai -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden transition-all">
        
        <!-- Header Tabel & Aksi -->
        <div class="p-6 border-b border-gray-100 dark:border-white/5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50/50 dark:bg-white/[0.02]">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Rekapitulasi Nilai Siswa</h2>
                <p class="text-xs text-gray-500 mt-1">Akumulasi nilai otomatis dari aktivitas kuis dan tugas digital.</p>
            </div>
            
            <div class="flex flex-wrap gap-2 w-full md:w-auto">
                <!-- Tombol Sinkron ke Akademik Utama -->
                <a href="<?= base_url('app/elearning/grades/sync/'.$id_kelas) ?>" 
                   onclick="return confirm('Apakah Anda yakin ingin memindahkan rata-rata nilai ini ke Buku Nilai Utama (Leger)?')"
                   class="flex-1 md:flex-none px-4 py-2.5 bg-primary text-white text-xs font-bold rounded-xl hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 transform active:scale-95">
                    <i class="fas fa-sync-alt"></i> Sinkron ke Akademik
                </a>
                
                <!-- Tombol Ekspor -->
                <button class="flex-1 md:flex-none px-4 py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-file-excel"></i> Ekspor Excel
                </button>
            </div>
        </div>

        <!-- Tabel Responsive -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="p-4 border-b border-gray-100 dark:border-white/5 font-bold text-gray-700 dark:text-gray-300 sticky left-0 bg-gray-50 dark:bg-gray-800 z-10 w-64 shadow-[2px_0_5px_rgba(0,0,0,0.05)]">
                            Nama Siswa
                        </th>
                        
                        <!-- Header Kolom Tugas -->
                        <?php foreach($assignments as $a): ?>
                            <th class="p-4 border-b border-gray-100 dark:border-white/5 font-bold text-center min-w-[120px]">
                                <span class="block text-[10px] uppercase text-emerald-500 font-black tracking-tighter mb-1">Tugas</span>
                                <span class="truncate block w-24 mx-auto text-gray-600 dark:text-gray-400" title="<?= esc($a['judul']) ?>">
                                    <?= esc(substr($a['judul'], 0, 12)) ?>..
                                </span>
                            </th>
                        <?php endforeach; ?>

                        <!-- Header Kolom Kuis -->
                        <?php foreach($quizzes as $q): ?>
                            <th class="p-4 border-b border-gray-100 dark:border-white/5 font-bold text-center min-w-[120px]">
                                <span class="block text-[10px] uppercase text-purple-500 font-black tracking-tighter mb-1">Kuis</span>
                                <span class="truncate block w-24 mx-auto text-gray-600 dark:text-gray-400" title="<?= esc($q['judul']) ?>">
                                    <?= esc(substr($q['judul'], 0, 12)) ?>..
                                </span>
                            </th>
                        <?php endforeach; ?>
                        
                        <th class="p-4 border-b border-primary/20 font-black text-primary text-center bg-primary/5 min-w-[100px]">Rerata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php if(empty($students)): ?>
                        <tr>
                            <td colspan="100%" class="p-12 text-center text-gray-400 italic">Belum ada siswa terdaftar di kelas ini.</td>
                        </tr>
                    <?php else: foreach($students as $s): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                            <td class="p-4 font-bold text-gray-800 dark:text-gray-200 sticky left-0 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800 shadow-[2px_0_5px_rgba(0,0,0,0.05)] transition-colors">
                                <?= esc($s['nama']) ?>
                                <p class="text-[10px] font-normal text-gray-400 mt-0.5"><?= esc($s['nis'] ?? '-') ?></p>
                            </td>
                            
                            <!-- Nilai-nilai Tugas -->
                            <?php foreach($assignments as $a): ?>
                                <?php $val = $s['grades']['assignments'][$a['id']] ?? '-'; ?>
                                <td class="p-4 text-center <?= is_numeric($val) && $val < 75 ? 'text-red-500 font-bold' : 'text-gray-600 dark:text-gray-400' ?>">
                                    <?= $val ?>
                                </td>
                            <?php endforeach; ?>

                            <!-- Nilai-nilai Kuis -->
                            <?php foreach($quizzes as $q): ?>
                                <?php $val = $s['grades']['quizzes'][$q['id']] ?? '-'; ?>
                                <td class="p-4 text-center <?= is_numeric($val) && $val < 75 ? 'text-red-500 font-bold' : 'text-gray-600 dark:text-gray-400' ?>">
                                    <?= $val ?>
                                </td>
                            <?php endforeach; ?>

                            <!-- Rata-rata Akhir -->
                            <td class="p-4 text-center font-black text-primary bg-primary/5 group-hover:bg-primary/10 transition-colors">
                                <?= $s['rata_rata'] ?? '0' ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Footer Info -->
        <div class="p-4 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex justify-between items-center text-[10px] text-gray-400 uppercase font-bold tracking-widest">
            <span>Total Siswa: <?= count($students) ?></span>
            <span class="flex items-center gap-2">
                <i class="fas fa-info-circle text-primary"></i> 
                Nilai di bawah 75 ditandai merah (KKM)
            </span>
        </div>
    </div>
</div>

<style>
    /* Styling scrollbar untuk tabel lebar agar tetap cantik */
    .custom-scrollbar::-webkit-scrollbar {
        height: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }
</style>

<?= $this->endSection() ?>