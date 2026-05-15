<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Navbar Internal E-learning -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="<?= base_url('app/elearning/view/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Forum</a>
        <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Tugas Kelas</a>
        <a href="<?= base_url('app/elearning/people/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Anggota</a>
    </nav>

    <!-- Banner Kuis -->
    <div class="relative h-40 sm:h-48 rounded-xl overflow-hidden shadow-lg mb-8 bg-purple-700">
        <img src="https://www.gstatic.com/classroom/themes/img_reachout.jpg" class="w-full h-full object-cover opacity-40" alt="Banner">
        <div class="absolute inset-0 flex flex-col justify-end p-6">
            <div class="flex items-center gap-2 text-purple-200 text-xs font-bold uppercase tracking-widest mb-1">
                <i class="fas fa-question-circle"></i> Modul Evaluasi
            </div>
            <h1 class="text-2xl font-bold text-white mb-1">Buat Kuis Baru</h1>
            <p class="text-white/80 text-sm"><?= esc($nama_kelas) ?> • <?= esc($mata_pelajaran ?? 'Umum') ?></p>
        </div>
    </div>

    <!-- Form Area -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <form action="<?= base_url('app/elearning/quiz/store') ?>" method="post" class="p-6 sm:p-8 space-y-6">
            <?= csrf_field() ?>
            <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Sisi Kiri: Detail Kuis -->
                <div class="md:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Judul Kuis</label>
                        <input type="text" name="judul" required placeholder="Misal: Ulangan Harian Bab 1" 
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-primary outline-none transition-all shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Instruksi & Petunjuk</label>
                        <textarea name="deskripsi" rows="6" placeholder="Tuliskan petunjuk pengerjaan (Misal: Pilih satu jawaban paling tepat)..." 
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-primary outline-none transition-all resize-none shadow-sm"></textarea>
                    </div>
                </div>

                <!-- Sisi Kanan: Pengaturan -->
                <div class="space-y-6">
                    <div class="bg-gray-50 dark:bg-white/5 p-6 rounded-2xl border border-gray-100 dark:border-white/5 space-y-5">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b border-gray-200 dark:border-white/10 pb-2">Pengaturan</h4>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Topik Materi</label>
                            <select name="id_topic" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/50">
                                <option value="">Tanpa Topik</option>
                                <?php if(!empty($topics)): foreach($topics as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= esc($t['nama_topik']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Durasi (Menit)</label>
                            <div class="relative">
                                <input type="number" name="durasi_menit" value="0" min="0"
                                       class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg pl-3 pr-10 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/50">
                                <span class="absolute right-3 top-2 text-[10px] text-gray-400 font-bold uppercase">Min</span>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 italic">* Set 0 untuk tanpa batas waktu.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Tenggat Pengerjaan</label>
                            <input type="datetime-local" name="deadline" 
                                   class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary/50">
                        </div>
                    </div>

                    <div class="p-4 bg-primary/5 rounded-xl border border-primary/10">
                        <p class="text-[10px] text-primary font-medium leading-relaxed">
                            <i class="fas fa-info-circle mr-1"></i> Setelah menyimpan, Anda akan diarahkan ke halaman penyusunan butir soal (PG/Essay).
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex flex-col sm:flex-row justify-end gap-3">
                <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors text-center">
                    Batal
                </a>
                <button type="submit" class="px-8 py-2.5 rounded-xl text-sm font-bold text-white bg-primary hover:bg-primary-dark shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2 transform active:scale-95">
                    Lanjut ke Input Soal <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>