<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php 
    // Robust Role Detection (Matches Dashboard logic)
    $session = session();
    $rawRole = $session->get('role') 
               ?? $session->get('role_name') 
               ?? $session->get('level') 
               ?? $session->get('jabatan') 
               ?? 'guest';
    
    $role = strtolower(trim($rawRole));
    
    // Permission Check: Admin, Guru, Superadmin, or Pengajar
    $isAdminOrGuru = (strpos($role, 'admin') !== false) || 
                     (strpos($role, 'guru') !== false) || 
                     (strpos($role, 'super') !== false) ||
                     (strpos($role, 'pengajar') !== false) ||
                     ($role === 'yayasan');
?>

<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Navbar Internal E-learning -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="<?= base_url('app/elearning/view/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Forum</a>
        <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Tugas Kelas</a>
        <a href="<?= base_url('app/elearning/people/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Anggota</a>
    </nav>

    <!-- Banner Kelas -->
    <div class="relative h-48 sm:h-64 rounded-xl overflow-hidden shadow-lg group">
        <img src="https://www.gstatic.com/classroom/themes/img_read.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Banner">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-6 sm:p-8">
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1 drop-shadow-md"><?= esc($nama_kelas) ?></h1>
            <p class="text-lg text-white/90 drop-shadow-sm"><?= esc($mata_pelajaran ?? 'Umum') ?></p>
        </div>
        
        <button onclick="alert('Fitur ganti tema segera hadir!')" class="absolute bottom-4 right-4 bg-white/20 hover:bg-white/30 backdrop-blur-md text-white p-2.5 rounded-full transition-all border border-white/30 shadow-lg" title="Ubah Tema">
            <i class="fas fa-palette"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        <!-- Sidebar Kiri -->
        <div class="hidden lg:block space-y-4 sticky top-32">
            
            <!-- Box Tugas Mendatang -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-4">Mendatang</h4>
                <div class="text-center py-2">
                    <p class="text-xs text-gray-500 mb-4">Tidak ada tugas yang perlu segera diselesaikan.</p>
                    <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="text-xs font-bold text-primary hover:underline">Lihat semua</a>
                </div>
            </div>

            <!-- SIDEBAR MANAJEMEN: Muncul jika isAdminOrGuru adalah TRUE -->
            <?php if ($isAdminOrGuru): ?>
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 p-5 shadow-sm space-y-3">
                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Manajemen Kelas</h4>
                
                <!-- Button: Tambah Anggota -->
                <a href="<?= base_url('app/elearning/people/'.$id_kelas) ?>" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                        <i class="fas fa-user-plus text-xs"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">Tambah Anggota</span>
                </a>

                <!-- Button: Bank Soal (Direct to Quiz Creation with Bank feature) -->
                <a href="<?= base_url('app/elearning/quiz/create/'.$id_kelas) ?>" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600">
                        <i class="fas fa-database text-xs"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">Bank Soal</span>
                </a>

                <!-- Button: Buku Nilai -->
                <a href="<?= base_url('app/elearning/grades/'.$id_kelas) ?>" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-graduation-cap text-xs"></i>
                    </div>
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-300 group-hover:text-primary transition-colors">Buku Nilai</span>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Utama (Forum) -->
        <div class="lg:col-span-3 space-y-5">
            
            <?php 
                $sessionName = session()->get('nama_lengkap') ?? session()->get('nama') ?? 'User';
                $sessionAvatar = "https://ui-avatars.com/api/?name=" . urlencode($sessionName) . "&background=random";
            ?>

            <!-- Post Announcement Box -->
            <div x-data="{ expanded: false }" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="p-4 flex items-center gap-4 cursor-pointer" @click="expanded = true; $nextTick(() => $refs.postInput.focus())" x-show="!expanded">
                    <img src="<?= $sessionAvatar ?>" class="w-10 h-10 rounded-full shadow-sm">
                    <div class="flex-1 px-4 py-2.5 bg-gray-50 dark:bg-gray-800 rounded-full text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Umumkan sesuatu ke kelas Anda...
                    </div>
                </div>
                
                <form action="<?= base_url('app/elearning/post_announcement') ?>" method="post" x-show="expanded" x-cloak class="p-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">
                    <textarea x-ref="postInput" name="content" class="w-full bg-gray-50 dark:bg-gray-800 rounded-lg border-none focus:ring-2 focus:ring-primary text-sm p-4 min-h-[120px] resize-none shadow-inner" placeholder="Apa yang ingin Anda bagikan?" required></textarea>
                    
                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" class="text-gray-500 hover:text-primary p-2 transition-colors rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"><i class="fas fa-paperclip"></i></button>
                        <div class="flex gap-2">
                            <button type="button" @click="expanded = false" class="px-4 py-2 text-sm font-semibold text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">Batal</button>
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg text-sm font-bold shadow-md hover:bg-primary-dark transition-all flex items-center gap-2">
                                <i class="fas fa-paper-plane text-xs"></i> Posting
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- List Posts -->
            <?php if (!empty($posts)): foreach($posts as $post): ?>
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden group transition-all hover:border-gray-300 dark:hover:border-gray-700">
                <div class="p-4 sm:p-5 flex gap-4">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($post['author'] ?? 'User') ?>&background=random" class="w-10 h-10 rounded-full ring-2 ring-gray-100 dark:ring-gray-800">
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h5 class="text-sm font-bold text-gray-900 dark:text-gray-100"><?= esc($post['author'] ?? 'User') ?></h5>
                                <p class="text-[10px] text-gray-400"><?= isset($post['created_at']) ? date('d M Y, H:i', strtotime($post['created_at'])) : date('d M Y') ?></p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-full"><i class="fas fa-ellipsis-v px-2"></i></button>
                        </div>
                        <div class="mt-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line"><?= nl2br(esc($post['content'] ?? '')) ?></div>
                    </div>
                </div>
                
                <!-- Comment Section -->
                <?php if (!empty($post['comments'])): ?>
                <div class="bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-white/5 p-4 space-y-4">
                    <?php foreach ($post['comments'] as $comment): ?>
                        <div class="flex gap-3">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($comment['commenter_name'] ?? 'User') ?>&background=random" class="w-8 h-8 rounded-full mt-1">
                            <div class="bg-white dark:bg-gray-800 py-2 px-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm text-sm">
                                <span class="font-bold text-gray-900 dark:text-white block text-xs mb-0.5"><?= esc($comment['commenter_name'] ?? 'Pengguna') ?></span>
                                <span class="text-gray-700 dark:text-gray-300"><?= esc($comment['comment']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="border-t border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02] p-3 sm:p-4">
                    <form action="<?= base_url('app/elearning/post_comment') ?>" method="post" class="flex gap-3 items-center">
                        <?= csrf_field() ?>
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?? 0 ?>">
                        <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>"> 
                        <img src="<?= $sessionAvatar ?>" class="w-8 h-8 rounded-full shadow-sm">
                        <div class="flex-1 relative">
                            <input type="text" name="comment" required placeholder="Tulis komentar..." class="w-full bg-transparent border border-gray-200 dark:border-gray-700 rounded-full py-2 px-4 text-sm focus:ring-2 focus:ring-primary/50 outline-none">
                            <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-primary p-1.5 hover:text-primary-dark transition-colors"><i class="fas fa-paper-plane text-xs"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; else: ?>
                <div class="bg-white dark:bg-gray-900 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 p-12 text-center text-gray-500">
                    <div class="w-16 h-16 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Belum ada pengumuman</h3>
                    <p class="text-xs mt-1">Gunakan kotak di atas untuk memulai diskusi dengan kelas.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
<?= $this->endSection() ?>