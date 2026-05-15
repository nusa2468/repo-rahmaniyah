<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- Wrap seluruh konten dalam x-data untuk kontrol Modal -->
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showAddModal: false, addRole: 'student' }">
    
    <!-- Navbar Internal -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="<?= base_url('app/elearning/view/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Forum</a>
        <a href="<?= base_url('app/elearning/classwork/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Tugas Kelas</a>
        <a href="<?= base_url('app/elearning/people/'.$id_kelas) ?>" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Anggota</a>
    </nav>

    <!-- Banner Kelas -->
    <div class="relative h-48 sm:h-64 rounded-xl overflow-hidden shadow-lg group">
        <img src="https://www.gstatic.com/classroom/themes/img_read.jpg" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Banner">
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-6 sm:p-8">
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1 drop-shadow-md"><?= esc($nama_kelas) ?></h1>
            <p class="text-lg text-white/90 drop-shadow-sm"><?= esc($mata_pelajaran ?? 'Umum') ?></p>
        </div>
    </div>

    <!-- Bagian Guru/Pengajar -->
    <section class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm p-6">
        <div class="flex justify-between items-center border-b border-gray-100 dark:border-white/10 pb-4 mb-4">
            <h2 class="text-xl font-bold text-primary tracking-tight">Pengajar</h2>
            <?php if ($is_admin): ?>
            <!-- Tombol Tambah Pengajar -->
            <button @click="showAddModal = true; addRole = 'teacher'" class="text-primary hover:bg-primary/10 p-2 rounded-full transition-colors" title="Undang Pengajar">
                <i class="fas fa-user-plus text-lg"></i>
            </button>
            <?php endif; ?>
        </div>
        <div class="space-y-3">
            <?php foreach($teachers as $teacher): ?>
            <?php 
                $teacherName = !empty($teacher['nama']) ? $teacher['nama'] : 'Pengajar';
            ?>
            <div class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($teacherName) ?>&background=0ea5e9&color=fff" class="w-12 h-12 rounded-full shadow-sm">
                <span class="font-semibold text-gray-700 dark:text-gray-200 text-lg"><?= esc($teacherName) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Bagian Siswa -->
    <section class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm p-6">
        <div class="flex justify-between items-center border-b border-gray-100 dark:border-white/10 pb-4 mb-4">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-primary tracking-tight">Teman Sekelas</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-xs font-bold"><?= count($students) ?> siswa</span>
            </div>
            <?php if ($is_admin): ?>
            <!-- Tombol Tambah Siswa -->
            <button @click="showAddModal = true; addRole = 'student'" class="text-primary hover:bg-primary/10 p-2 rounded-full transition-colors" title="Undang Siswa">
                <i class="fas fa-user-plus text-lg"></i>
            </button>
            <?php endif; ?>
        </div>

        <div class="space-y-1">
            <?php if(empty($students)): ?>
                <div class="text-center py-12 text-gray-400 italic">
                    <i class="fas fa-users-slash text-4xl mb-3 opacity-30"></i>
                    <p>Belum ada siswa yang bergabung di kelas ini.</p>
                </div>
            <?php else: foreach($students as $student): ?>
            <?php 
                $studentName = !empty($student['nama']) ? $student['nama'] : 'Siswa';
                $isAktif = ($student['status'] ?? 1) == 1; 
                $enrollmentId = $student['id']; 
            ?>
            <div class="flex items-center justify-between p-3 rounded-lg border border-transparent hover:border-gray-100 dark:hover:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all group <?= !$isAktif ? 'bg-red-50/50 dark:bg-red-900/10' : '' ?>">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($studentName) ?>&background=random" class="w-10 h-10 rounded-full shadow-sm <?= !$isAktif ? 'grayscale opacity-60' : '' ?>">
                        <?php if(!$isAktif): ?>
                            <div class="absolute -bottom-1 -right-1 bg-red-500 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold shadow-sm border border-white">NON</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex flex-col">
                        <span class="font-semibold text-gray-700 dark:text-gray-200 <?= !$isAktif ? 'text-gray-400 line-through' : '' ?>">
                            <?= esc($studentName) ?>
                        </span>
                        <?php if(!$isAktif): ?>
                            <span class="text-[10px] text-red-500 font-bold uppercase tracking-wide flex items-center gap-1">
                                <i class="fas fa-ban text-[8px]"></i> Ditangguhkan (Suspend)
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tindakan (Hanya Admin/Guru) -->
                <?php if ($is_admin): ?>
                <div class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:text-primary hover:border-primary px-3 py-1.5 rounded-lg shadow-sm transition-all text-xs font-bold">
                            <i class="fas fa-cog"></i> <span class="hidden sm:inline">Kelola</span>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-50 py-2 overflow-hidden" style="display: none;">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700 text-xs text-gray-400 font-bold uppercase tracking-wider">
                                Aksi Siswa
                            </div>
                            
                            <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-envelope mr-3 text-gray-400 w-4 text-center"></i> Kirim Email
                            </a>
                            
                            <form action="<?= base_url('app/elearning/update_student_status') ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_enrollment" value="<?= $enrollmentId ?>">
                                <?php if($isAktif): ?>
                                    <input type="hidden" name="status" value="0">
                                    <button type="submit" class="w-full text-left flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <i class="fas fa-ban mr-3 w-4 text-center"></i> Nonaktifkan (SPP)
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="1">
                                    <button type="submit" class="w-full text-left flex items-center px-4 py-2.5 text-sm text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors">
                                        <i class="fas fa-check-circle mr-3 w-4 text-center"></i> Aktifkan Kembali
                                    </button>
                                <?php endif; ?>
                            </form>
                            
                            <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                            <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-red-500 transition-colors">
                                <i class="fas fa-sign-out-alt mr-3 w-4 text-center"></i> Keluarkan dari Kelas
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </section>

    <!-- MODAL TAMBAH ANGGOTA -->
    <div x-show="showAddModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-cloak>
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all" @click.away="showAddModal = false">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-user-plus text-primary"></i>
                    Tambah <span x-text="addRole == 'teacher' ? 'Pengajar' : 'Siswa'"></span>
                </h3>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form action="<?= base_url('app/elearning/add_member') ?>" method="post" class="p-6 space-y-5">
                <?= csrf_field() ?>
                <input type="hidden" name="id_kelas" value="<?= $id_kelas ?>">
                <input type="hidden" name="role" :value="addRole == 'teacher' ? 'teacher' : 'student'">
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Cari <span x-text="addRole == 'teacher' ? 'Pengajar' : 'Siswa'"></span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="identifier" required 
                               class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-sm"
                               :placeholder="addRole == 'teacher' ? 'Masukkan Email atau NIP Guru' : 'Masukkan Email, NIS, atau NISN'">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pastikan pengguna sudah terdaftar di sistem.
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-primary text-white text-sm font-bold rounded-lg hover:bg-primary-dark shadow-md transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Tambahkan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
<?= $this->endSection() ?>