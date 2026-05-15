<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- X-DATA AlpineJS untuk Manajemen Modal -->
<div class="max-w-5xl mx-auto space-y-6" x-data="{ 
    showContentModal: false, 
    showTopicModal: false,
    contentType: 'materi',
    formTitle: 'Buat Materi'
}">
    
    <!-- Navbar Internal -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="<?= base_url('app/elearning/view/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">Forum</a>
        <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Tugas Kelas</a>
        <a href="<?= base_url('app/elearning/people/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">Anggota</a>
    </nav>

    <!-- Banner Kelas -->
    <div class="relative h-48 sm:h-64 rounded-xl overflow-hidden shadow-lg mb-8 group">
        <img src="https://www.gstatic.com/classroom/themes/img_read.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Banner">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-6 sm:p-8">
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1 drop-shadow-md"><?= esc($nama_kelas) ?></h1>
            <p class="text-lg text-white/90 drop-shadow-sm"><?= esc($mata_pelajaran ?? 'Umum') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        <!-- Sidebar Topik -->
        <div class="hidden lg:block space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 p-4 shadow-sm sticky top-32">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-3 px-2">Topik</h4>
                <div class="space-y-1">
                    <a href="#" class="block px-3 py-2 text-sm font-bold text-primary bg-primary/10 rounded-lg">Semua Topik</a>
                    <?php if(!empty($topics)): foreach($topics as $topic): ?>
                    <a href="#topic-<?= $topic['id'] ?>" class="block px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                        <?= esc($topic['nama_topik']) ?>
                    </a>
                    <?php endforeach; endif; ?>
                </div>
                <button @click="showTopicModal = true" class="w-full mt-4 py-2 text-xs font-bold text-primary border border-dashed border-primary/50 rounded-lg hover:bg-primary/5 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Topik
                </button>
            </div>
        </div>

        <!-- Kolom Utama -->
        <div class="lg:col-span-3 space-y-8">
            
            <!-- Tombol Buat (Guru/Admin Only) -->
            <div class="flex justify-end mb-4">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-full shadow-lg hover:shadow-xl hover:bg-primary-dark transition-all font-bold text-sm">
                        <i class="fas fa-plus"></i> Buat
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute right-0 mt-2 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-white/10 z-50 py-2 overflow-hidden">
                        
                        <button @click="open = false; contentType = 'materi'; formTitle = 'Buat Materi'; showContentModal = true" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-200 flex items-center gap-3 transition-colors">
                            <i class="fas fa-book text-blue-500 w-5 text-center"></i> Materi
                        </button>
                        
                        <button @click="open = false; contentType = 'tugas'; formTitle = 'Buat Tugas'; showContentModal = true" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-200 flex items-center gap-3 transition-colors">
                            <i class="fas fa-clipboard-list text-emerald-500 w-5 text-center"></i> Tugas
                        </button>

                        <a href="<?= base_url('app/elearning/quiz/create/'.$id_kelas) ?>" class="block px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-200 flex items-center gap-3 transition-colors">
                            <i class="fas fa-question-circle text-purple-500 w-5 text-center"></i> Kuis
                        </a>
                        
                        <div class="border-t border-gray-100 dark:border-white/5 my-1"></div>
                        
                        <button @click="open = false; showTopicModal = true" class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-200 flex items-center gap-3 transition-colors">
                            <i class="fas fa-tags text-gray-400 w-5 text-center"></i> Topik
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loop Group Konten -->
            <?php foreach($groupedContents as $topicId => $group): ?>
                <?php if(!empty($group['items']) || $topicId !== 0): ?>
                <div id="topic-<?= $topicId ?>" class="scroll-mt-32">
                    <?php if($topicId !== 0): ?>
                        <div class="flex justify-between items-center border-b-2 border-primary/20 pb-2 mb-4">
                            <h2 class="text-xl font-bold text-primary"><?= esc($group['nama_topik']) ?></h2>
                            <button class="text-gray-400 hover:text-gray-600 transition-colors"><i class="fas fa-ellipsis-v"></i></button>
                        </div>
                    <?php endif; ?>

                    <div class="space-y-3">
                        <?php foreach($group['items'] as $item): 
                            // LOGIKA URL DINAMIS
                            $detailUrl = '#';
                            if (isset($item['real_type']) && $item['real_type'] == 'kuis') {
                                // Link ke editor kuis untuk guru
                                $detailUrl = base_url('app/elearning/quiz/questions/' . $item['id']);
                            } elseif ($item['tipe'] == 'tugas') {
                                $detailUrl = base_url('app/elearning/assignment/' . $item['id']);
                            } else {
                                $detailUrl = base_url('app/elearning/material/' . $item['id']);
                            }
                        ?>
                        <a href="<?= $detailUrl ?>" class="block group bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl hover:shadow-md transition-all overflow-hidden">
                            <div class="flex items-center p-4 gap-4">
                                <!-- Ikon Dinamis berdasarkan Tipe -->
                                <?php 
                                    $isKuis = (isset($item['real_type']) && $item['real_type'] == 'kuis');
                                    $isTugas = ($item['tipe'] == 'tugas');
                                ?>
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 
                                    <?= $isKuis ? 'bg-purple-100 text-purple-600' : ($isTugas ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600') ?>">
                                    <i class="fas <?= $isKuis ? 'fa-question-circle' : ($isTugas ? 'fa-clipboard-list' : 'fa-book') ?>"></i>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary transition-colors truncate">
                                        <?= esc($item['judul']) ?>
                                    </h3>
                                    <p class="text-[10px] text-gray-400 mt-0.5 uppercase font-bold tracking-wider">
                                        <?= $isKuis ? 'KUIS' : ucfirst($item['tipe']) ?> • Diposting <?= date('d M Y', strtotime($item['created_at'])) ?>
                                        <?php if(!empty($item['deadline'])): ?>
                                            <span class="mx-1 text-red-500">• Tenggat: <?= date('d M, H:i', strtotime($item['deadline'])) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <?php if($isKuis && isset($item['is_published']) && !$item['is_published']): ?>
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-400 text-[9px] font-bold rounded border border-gray-200 dark:border-gray-700">DRAF</span>
                                <?php endif; ?>

                                <div class="text-gray-300 group-hover:text-gray-500 transition-colors">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Empty State -->
            <?php if(empty($groupedContents) || (count($groupedContents) == 1 && empty($groupedContents[0]['items']))): ?>
            <div class="text-center py-20 bg-white dark:bg-gray-900 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book-open text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Belum ada tugas kelas</h3>
                <p class="text-sm text-gray-500 mt-2">Mulai dengan membuat materi atau tugas baru.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- MODAL BUAT MATERI / TUGAS -->
    <div x-show="showContentModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" x-cloak>
        <div class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden transform transition-all" @click.away="showContentModal = false">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-lg" x-text="formTitle"></h3>
                <button @click="showContentModal = false" class="text-gray-400 hover:text-gray-600 transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            
            <form action="<?= base_url('app/elearning/store_content') ?>" method="post" class="p-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">
                <input type="hidden" name="tipe" :value="contentType">
                
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Judul</label>
                    <input type="text" name="judul" required placeholder="Judul materi atau tugas"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-primary transition-all shadow-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Topik</label>
                        <select name="id_topic" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-primary transition-all">
                            <option value="">Tanpa Topik</option>
                            <?php if(!empty($topics)): foreach($topics as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= esc($t['nama_topik']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div x-show="contentType == 'tugas'">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tenggat Waktu</label>
                        <input type="datetime-local" name="deadline" 
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-primary transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Isi / Instruksi</label>
                    <textarea name="deskripsi" rows="5" placeholder="Tulis materi atau instruksi tugas di sini..."
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-primary transition-all resize-none shadow-sm"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Link Lampiran (Opsional)</label>
                    <input type="url" name="link_materi" placeholder="https://youtube.com/... atau link drive"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-primary transition-all shadow-sm">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="showContentModal = false" class="px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-400 rounded-xl transition-colors">Batal</button>
                    <button type="submit" class="px-8 py-2.5 text-sm font-bold bg-primary text-white rounded-xl hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i> Publikasikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TAMBAH TOPIK -->
    <div x-show="showTopicModal" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" x-cloak>
        <div class="bg-white dark:bg-gray-900 w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden transform transition-all" @click.away="showTopicModal = false">
            <div class="p-6">
                <h3 class="font-bold text-gray-800 dark:text-white text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-tag text-primary"></i> Tambah Topik Baru
                </h3>
                <form action="<?= base_url('app/elearning/store_topic') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">
                    <input type="text" name="nama_topik" required placeholder="Nama topik (misal: Bab 1)"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-primary transition-all mb-4 shadow-sm">
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showTopicModal = false" class="px-4 py-2 text-sm text-gray-500 font-bold hover:bg-gray-100 rounded-lg transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-2 text-sm bg-primary text-white font-bold rounded-lg shadow-md hover:bg-primary-dark transition-all transform active:scale-95">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<style>
    [x-cloak] { display: none !important; }
    html { scroll-behavior: smooth; }
</style>
<?= $this->endSection() ?>