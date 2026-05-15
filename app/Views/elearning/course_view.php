<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Navbar Internal E-learning (Classroom Style) -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm">
        <a href="<?= base_url('elearning/view/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Forum</a>
        <a href="<?= base_url('elearning/classwork/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">Tugas Kelas</a>
        <a href="<?= base_url('elearning/people/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">Anggota</a>
    </nav>

    <!-- Banner Kelas -->
    <div class="relative h-48 sm:h-64 rounded-xl overflow-hidden shadow-lg">
        <img src="https://www.gstatic.com/classroom/themes/img_read.jpg" class="w-full h-full object-cover" alt="Banner">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex flex-col justify-end p-6 sm:p-8">
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1"><?= esc($nama_kelas) ?></h1>
            <p class="text-lg text-white/90"><?= esc($mata_pelajaran) ?></p>
        </div>
        <button class="absolute bottom-4 right-4 bg-white/20 hover:bg-white/30 backdrop-blur-md text-white p-2 rounded-full transition-all" title="Ubah Tema">
            <i class="fas fa-info-circle"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        <!-- Sidebar Kiri (Tugas Mendatang) -->
        <div class="hidden lg:block space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
                <h4 class="text-sm font-bold mb-4">Mendatang</h4>
                <p class="text-xs text-gray-500 mb-4">Hore, tidak ada tugas yang perlu segera diselesaikan!</p>
                <a href="#" class="text-xs font-bold text-primary hover:underline">Lihat semua</a>
            </div>
        </div>

        <!-- Kolom Utama (Forum) -->
        <div class="lg:col-span-3 space-y-5">
            <!-- Share Something Box -->
            <div x-data="{ expanded: false }" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm transition-all duration-300">
                <div class="p-4 flex items-center gap-4" @click="expanded = true" :class="expanded ? 'hidden' : 'flex cursor-pointer'">
                    <img src="https://ui-avatars.com/api/?name=User" class="w-10 h-10 rounded-full">
                    <span class="text-sm text-gray-400">Umumkan sesuatu ke kelas Anda</span>
                </div>
                
                <div x-show="expanded" x-cloak class="p-5">
                    <textarea class="w-full bg-gray-50 dark:bg-gray-800 rounded-lg border-none focus:ring-2 focus:ring-primary text-sm p-4 min-h-[120px]" placeholder="Umumkan sesuatu ke kelas Anda..."></textarea>
                    <div class="flex justify-between items-center mt-4">
                        <button class="text-gray-500 hover:text-primary p-2 transition-colors"><i class="fas fa-paperclip"></i></button>
                        <div class="flex gap-2">
                            <button @click="expanded = false" class="px-4 py-2 text-sm font-semibold text-gray-500">Batal</button>
                            <button class="px-6 py-2 bg-primary text-white rounded-lg text-sm font-bold shadow-md hover:bg-primary-dark transition-all">Posting</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Pengumuman -->
            <?php foreach($posts as $post): ?>
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden group">
                <div class="p-4 sm:p-5 flex gap-4">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($post['author']) ?>" class="w-10 h-10 rounded-full ring-2 ring-gray-100 dark:ring-gray-800">
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h5 class="text-sm font-bold"><?= esc($post['author']) ?></h5>
                                <p class="text-[10px] text-gray-400"><?= $post['date'] ?></p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"><i class="fas fa-ellipsis-v"></i></button>
                        </div>
                        <div class="mt-4 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            <?= nl2br(esc($post['content'])) ?>
                        </div>
                    </div>
                </div>
                
                <!-- Comment Section -->
                <div class="border-t border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-white/[0.02] p-4 flex gap-3 items-center">
                    <img src="https://ui-avatars.com/api/?name=User" class="w-8 h-8 rounded-full">
                    <input type="text" placeholder="Tambahkan komentar kelas..." class="flex-1 bg-transparent border-none text-sm focus:ring-0 placeholder-gray-400">
                    <button class="text-primary opacity-50 hover:opacity-100 transition-opacity"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
