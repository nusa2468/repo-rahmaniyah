<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto space-y-6" x-data="classForm()">
    
    <!-- Navbar Internal (Statik untuk Konsistensi Visual) -->
    <nav class="flex justify-center border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 rounded-xl mb-6 sticky top-16 z-20 shadow-sm transition-all">
        <a href="#" class="px-6 py-4 text-sm font-semibold border-b-2 border-primary text-primary">Informasi Kelas</a>
        <a href="#" class="px-6 py-4 text-sm font-semibold text-gray-400 cursor-not-allowed">Tugas Kelas (Locked)</a>
        <a href="#" class="px-6 py-4 text-sm font-semibold text-gray-400 cursor-not-allowed">Anggota (Locked)</a>
    </nav>

    <!-- Banner Kelas dengan Live Preview -->
    <div class="relative h-48 sm:h-64 rounded-xl overflow-hidden shadow-lg group">
        <!-- Background Banner berdasarkan Tema -->
        <div :class="{
            'bg-blue-600': formData.theme === 'blue',
            'bg-emerald-600': formData.theme === 'green',
            'bg-purple-600': formData.theme === 'purple',
            'bg-orange-600': formData.theme === 'orange',
            'bg-pink-600': formData.theme === 'pink'
        }" class="w-full h-full transition-colors duration-500 relative">
            <img src="https://www.gstatic.com/classroom/themes/img_read.jpg" class="w-full h-full object-cover mix-blend-overlay opacity-50" alt="Overlay Banner">
        </div>

        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-6 sm:p-8">
            <span class="text-white/70 text-xs font-bold uppercase tracking-widest mb-1">Pratinjau Kelas Baru</span>
            <h1 class="text-2xl sm:text-4xl font-bold text-white mb-1 drop-shadow-md" x-text="formData.nama_kelas || 'Nama Kelas Anda'"></h1>
            <p class="text-lg text-white/90 drop-shadow-sm" x-text="formData.mapel || 'Mata Pelajaran'"></p>
        </div>
        
        <div class="absolute bottom-4 right-4 bg-white/20 backdrop-blur-md text-white px-3 py-1.5 rounded-full text-[10px] font-bold uppercase border border-white/30 shadow-lg">
            <i class="fas fa-eye mr-1"></i> Live Preview
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        <!-- Sidebar Info -->
        <div class="hidden lg:block space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 p-5 shadow-sm">
                <h4 class="text-sm font-bold text-gray-800 dark:text-white mb-2">Panduan</h4>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Gunakan **Isi Otomatis dari Jadwal** untuk mempercepat proses pembuatan kelas berdasarkan data kurikulum yang ada.
                </p>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-2">Unit Aktif</p>
                    <span class="px-2 py-1 bg-primary/10 text-primary text-[10px] font-bold rounded">
                        <?= esc(session()->get('kode_jenjang') ?? 'GLOBAL') ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Kolom Utama (Form) -->
        <div class="lg:col-span-3">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden transition-all duration-300">
                <div class="p-6 border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">Detail Konfigurasi</h2>
                </div>

                <form action="<?= base_url('app/elearning/store') ?>" method="post" class="p-6 space-y-6">
                    <?= csrf_field() ?>

                    <!-- FITUR: Pilih dari Jadwal -->
                    <?php if (!empty($jadwalGuru)): ?>
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                        <label class="block text-xs font-black text-blue-800 dark:text-blue-300 uppercase tracking-widest mb-2">
                            <i class="fas fa-magic mr-1"></i> Smart Setup
                        </label>
                        <select class="w-full bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm transition-all"
                                @change="fillFromSchedule($event)">
                            <option value="">-- Pilih dari Jadwal Mengajar Anda --</option>
                            <?php foreach($jadwalGuru as $j): ?>
                                <option value='<?= json_encode([
                                    'nama_kelas' => $j['nama_mapel'] . ' - ' . $j['nama_grup'],
                                    'mapel'      => $j['nama_mapel'],
                                    'ruang'      => $j['nama_ruangan'] ?? ''
                                ]) ?>'>
                                    <?= $j['hari'] ?> | <?= substr($j['jam_mulai'],0,5) ?> | <?= $j['nama_mapel'] ?> (<?= $j['nama_grup'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Unit Sekolah (Hanya untuk Superadmin) -->
                        <?php if (strpos(session()->get('role'), 'super') !== false || session()->get('kode_jenjang') === 'GLOBAL'): ?>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Unit Sekolah</label>
                            <select name="kode_jenjang" required class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none transition-all">
                                <option value="">Pilih Unit</option>
                                <option value="TK">TK</option>
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA">SMA</option>
                                <option value="SMK">SMK</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_kelas" x-model="formData.nama_kelas" required placeholder="Contoh: Matematika X-A" 
                                   class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Mata Pelajaran</label>
                            <input type="text" name="mata_pelajaran" x-model="formData.mapel" placeholder="Contoh: Matematika Wajib" 
                                   class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Ruang</label>
                            <input type="text" name="ruang" x-model="formData.ruang" placeholder="Contoh: Lab Komputer 1" 
                                   class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tema Warna Banner</label>
                            <div class="flex gap-3">
                                <template x-for="color in ['blue', 'green', 'purple', 'orange', 'pink']">
                                    <label class="cursor-pointer relative group">
                                        <input type="radio" name="theme" :value="color" x-model="formData.theme" class="peer sr-only">
                                        <div :class="{
                                            'bg-blue-500': color === 'blue',
                                            'bg-emerald-500': color === 'green',
                                            'bg-purple-500': color === 'purple',
                                            'bg-orange-500': color === 'orange',
                                            'bg-pink-500': color === 'pink'
                                        }" class="w-8 h-8 rounded-full transition-all group-hover:scale-110 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-gray-400 dark:peer-checked:ring-white"></div>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Deskripsi / Catatan Kelas</label>
                        <textarea name="deskripsi" rows="4" placeholder="Tuliskan aturan kelas atau deskripsi singkat..."
                                  class="w-full bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary outline-none transition-all resize-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <a href="<?= base_url('app/elearning') ?>" class="px-6 py-2.5 rounded-lg text-sm font-bold text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="px-8 py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-primary-dark shadow-lg transition-all transform active:scale-95 flex items-center gap-2">
                            <i class="fas fa-plus-circle"></i> Buat Kelas Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function classForm() {
        return {
            formData: {
                nama_kelas: '',
                mapel: '',
                ruang: '',
                theme: 'blue'
            },
            fillFromSchedule(event) {
                const selectedValue = event.target.value;
                if (selectedValue) {
                    const data = JSON.parse(selectedValue);
                    this.formData.nama_kelas = data.nama_kelas;
                    this.formData.mapel = data.mapel;
                    this.formData.ruang = data.ruang;
                }
            }
        }
    }
</script>
<?= $this->endSection() ?>