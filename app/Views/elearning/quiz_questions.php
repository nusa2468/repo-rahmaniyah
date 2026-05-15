<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Navbar Internal (Identik dengan View/People/Create) -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="<?= base_url('app/elearning/view/'.$quiz['id_kelas']) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">Forum</a>
        <a href="<?= base_url('app/elearning/classwork/'.$quiz['id_kelas']) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Tugas Kelas</a>
        <a href="<?= base_url('app/elearning/people/'.$quiz['id_kelas']) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">Anggota</a>
    </nav>

    <!-- Banner Kuis (Identik Style) -->
    <div class="relative h-40 sm:h-48 rounded-xl overflow-hidden shadow-lg mb-8 bg-purple-700">
        <img src="https://www.gstatic.com/classroom/themes/img_reachout.jpg" class="w-full h-full object-cover opacity-40" alt="Banner">
        <div class="absolute inset-0 flex flex-col justify-end p-6">
            <div class="flex items-center gap-2 text-purple-200 text-xs font-bold uppercase tracking-widest mb-1">
                <i class="fas fa-tasks"></i> Editor Soal Kuis
            </div>
            <h1 class="text-2xl font-bold text-white mb-1"><?= esc($quiz['judul']) ?></h1>
            <p class="text-white/80 text-sm">Kelola butir soal dan kunci jawaban di bawah ini.</p>
        </div>
    </div>

    <!-- Alert Status -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 p-4 rounded-xl text-sm font-bold flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i> <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Sisi Kiri: Form Input Soal -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden sticky top-32">
                <div class="p-4 bg-gray-50 dark:bg-white/5 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-plus-circle text-primary"></i> Tambah Soal PG
                    </h3>
                </div>
                <form action="<?= base_url('app/elearning/quiz/add_question') ?>" method="post" class="p-5 space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_quiz" value="<?= $quiz['id'] ?>">
                    
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Pertanyaan</label>
                        <textarea name="pertanyaan" required rows="3" class="w-full p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary transition-all shadow-sm"></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Opsi Jawaban</label>
                        <div class="relative group">
                            <span class="absolute left-3 top-2.5 font-bold text-gray-400 text-xs">A</span>
                            <input type="text" name="opsi_a" required placeholder="Jawaban A" class="w-full pl-8 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm shadow-sm outline-none focus:border-primary">
                        </div>
                        <div class="relative group">
                            <span class="absolute left-3 top-2.5 font-bold text-gray-400 text-xs">B</span>
                            <input type="text" name="opsi_b" required placeholder="Jawaban B" class="w-full pl-8 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm shadow-sm outline-none focus:border-primary">
                        </div>
                        <div class="relative group">
                            <span class="absolute left-3 top-2.5 font-bold text-gray-400 text-xs">C</span>
                            <input type="text" name="opsi_c" required placeholder="Jawaban C" class="w-full pl-8 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm shadow-sm outline-none focus:border-primary">
                        </div>
                        <div class="relative group">
                            <span class="absolute left-3 top-2.5 font-bold text-gray-400 text-xs">D</span>
                            <input type="text" name="opsi_d" required placeholder="Jawaban D" class="w-full pl-8 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm shadow-sm outline-none focus:border-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Jawaban Benar</label>
                        <select name="jawaban_benar" class="w-full p-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm font-bold text-primary outline-none focus:ring-2 focus:ring-primary/50">
                            <option value="A">Opsi A</option>
                            <option value="B">Opsi B</option>
                            <option value="C">Opsi C</option>
                            <option value="D">Opsi D</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary text-white font-bold rounded-xl shadow-lg hover:bg-primary-dark transition-all transform active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Tambahkan Soal
                    </button>
                </form>
            </div>
        </div>

        <!-- Sisi Kanan: Daftar Soal -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="flex justify-between items-center bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm">
                <span class="text-sm font-bold text-gray-600 dark:text-gray-400">Total: <?= count($questions) ?> Soal</span>
                <div class="flex gap-2">
                    <a href="<?= base_url('app/elearning/quiz/publish/'.$quiz['id']) ?>" class="px-4 py-2 bg-emerald-600 text-white text-xs font-black uppercase tracking-widest rounded-lg hover:bg-emerald-700 shadow-md transition-all">
                        <?= $quiz['is_published'] ? 'Batal Publish' : 'Publish Sekarang' ?>
                    </a>
                </div>
            </div>

            <?php if(empty($questions)): ?>
                <div class="bg-white dark:bg-gray-900 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl p-16 text-center text-gray-400">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-list-ol text-3xl opacity-20"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 dark:text-white">Belum Ada Pertanyaan</h3>
                    <p class="text-sm mt-1">Gunakan panel di sebelah kiri untuk menyusun soal kuis Anda.</p>
                </div>
            <?php else: foreach($questions as $index => $q): ?>
                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm relative group">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-2 py-0.5 bg-primary/10 text-primary text-[10px] font-black uppercase rounded mb-2">Soal #<?= $index + 1 ?></span>
                            <p class="text-gray-800 dark:text-gray-200 font-bold text-lg leading-relaxed mb-4"><?= esc($q['pertanyaan']) ?></p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <?php foreach(['a', 'b', 'c', 'd'] as $opt): ?>
                                    <div class="text-sm p-3 rounded-xl border flex items-center gap-3 transition-all <?= strtoupper($opt) == $q['jawaban_benar'] ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 font-bold ring-1 ring-emerald-500' : 'bg-gray-50 dark:bg-gray-800/50 border-gray-100 dark:border-gray-800 text-gray-500' ?>">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center border font-bold text-[10px] <?= strtoupper($opt) == $q['jawaban_benar'] ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600' ?>">
                                            <?= strtoupper($opt) ?>
                                        </span>
                                        <span class="truncate"><?= esc($q['opsi_'.$opt]) ?></span>
                                        <?php if(strtoupper($opt) == $q['jawaban_benar']): ?>
                                            <i class="fas fa-check-circle ml-auto"></i>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="<?= base_url('app/elearning/quiz/delete_question/'.$q['id']) ?>" 
                               onclick="return confirm('Hapus soal ini?')"
                               class="text-gray-300 hover:text-red-500 p-2 transition-colors">
                               <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>

            <div class="flex justify-center pt-4">
                <a href="<?= base_url('app/elearning/classwork/'.$quiz['id_kelas']) ?>" class="text-sm font-bold text-gray-400 hover:text-primary transition-colors flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Tugas
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>