<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    <?= isset($siswa['id']) ? 'Edit' : 'Tambah' ?> Data Siswa
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    // --- 1. INISIALISASI DATA & HELPER ---
    $isEdit = isset($siswa['id']);
    $url = $isEdit 
        ? base_url('app/masterdata/siswa/update/' . $siswa['id']) 
        : base_url('app/masterdata/siswa/create');

    // Helper Ekstraksi Data Aman (Object/Array)
    $getVal = function($data, $key) {
        if (is_object($data)) return $data->$key ?? '';
        return $data[$key] ?? '';
    };

    // Helper Ambil Data Keluarga Spesifik (Ayah/Ibu/Wali)
    $getKeluarga = function($role) use ($siswa_relasi) {
        $list = $siswa_relasi['keluarga'] ?? [];
        foreach ($list as $k) {
            $hub = is_object($k) ? $k->hubungan : $k['hubungan'];
            if (strtolower($hub) === strtolower($role)) {
                return is_object($k) ? (array)$k : $k;
            }
        }
        return [];
    };

    $ayah = $getKeluarga('Ayah');
    $ibu  = $getKeluarga('Ibu');
    $wali = $getKeluarga('Wali');
    $demografi = $siswa_relasi['demografi'] ?? [];

    // Data Utama
    $currentJenjang = old('kode_jenjang', $getVal($siswa, 'kode_jenjang') ?: (session('kode_jenjang') !== 'GLOBAL' ? session('kode_jenjang') : ''));
    $currentKelas   = old('id_kelas_initial', $current_kelas_id ?? '');
    
    // Error Handling
    $errors = session()->getFlashdata('errors') ?? [];
?>

<div class="max-w-5xl mx-auto space-y-8 pb-20 font-sans text-slate-800"
     x-data="{ 
        unit: '<?= esc($currentJenjang) ?>',
        isEdit: <?= $isEdit ? 'true' : 'false' ?>,
     }">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <nav class="flex mb-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li><a href="<?= base_url('app/masterdata/siswa') ?>" class="hover:text-indigo-600 transition-colors">Master Siswa</a></li>
                    <li><i class="fas fa-chevron-right text-[8px] opacity-50"></i></li>
                    <li class="text-indigo-600"><?= $isEdit ? 'Edit' : 'Baru' ?></li>
                </ol>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                <?= $isEdit ? 'Sunting Data Siswa' : 'Registrasi Siswa Baru' ?>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Lengkapi data pokok, akademik, dan kekeluargaan.</p>
        </div>
        <a href="<?= base_url('app/masterdata/siswa') ?>" 
           class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- ALERT ERROR GLOBAL -->
    <?php if ($errors) : ?>
        <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-sm animate-pulse">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-bold text-rose-800">Terdapat Kesalahan Input</h3>
                    <ul class="list-disc list-inside text-xs text-rose-600 mt-1">
                        <?php foreach ($errors as $e) : ?>
                            <li><?= esc($e) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif ?>

    <form action="<?= $url ?>" method="post" enctype="multipart/form-data" class="space-y-8">
        <?= csrf_field() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="<?= $getVal($siswa, 'id') ?>">
        <?php endif; ?>

        <!-- 1. DATA AKADEMIK & PENEMPATAN -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-white/5 shadow-sm p-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
                <i class="fas fa-school text-9xl text-indigo-900"></i>
            </div>
            
            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b border-slate-100 pb-4 mb-6 relative z-10">
                <i class="fas fa-university mr-2"></i> I. Data Akademik & Penempatan
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                <!-- Unit Sekolah -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Unit Sekolah <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="kode_jenjang" required x-model="unit" 
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm transition-all appearance-none cursor-pointer">
                            <option value="">-- Pilih Unit --</option>
                            <?php foreach($jenjang_list as $j): 
                                 $kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                            ?>
                                <option value="<?= $kode ?>" <?= $currentJenjang == $kode ? 'selected' : '' ?>>UNIT <?= strtoupper($kode) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>

                <!-- Penempatan Kelas (Baru/Pindah) -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">
                        <?= $isEdit ? 'Kelas Saat Ini (Pindah Kelas)' : 'Penempatan Kelas Awal' ?> <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="id_kelas_initial" required 
                                class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm transition-all appearance-none cursor-pointer">
                            <option value="">-- Pilih Kelas --</option>
                            
                            <?php if(!empty($kelas_list)): foreach($kelas_list as $kls): 
                                $kId = is_object($kls) ? $kls->id : $kls['id'];
                                $kNama = is_object($kls) ? $kls->nama_kelas : $kls['nama_kelas'];
                                $kUnit = is_object($kls) ? $kls->kode_jenjang : $kls['kode_jenjang'];
                            ?>
                                <!-- Filter AlpineJS: Hanya tampilkan kelas sesuai unit yang dipilih -->
                                <option value="<?= $kId ?>" 
                                        x-show="!unit || unit == '<?= $kUnit ?>'" 
                                        <?= $currentKelas == $kId ? 'selected' : '' ?>>
                                    [<?= $kUnit ?>] <?= esc($kNama) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                    <p class="text-[10px] text-indigo-500 mt-1.5 font-medium italic">
                        <i class="fas fa-info-circle"></i> Siswa otomatis terdaftar di kelas ini untuk Tahun Ajaran Aktif.
                    </p>
                </div>

                <!-- Jurusan -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Jurusan (Opsional)</label>
                    <div class="relative">
                        <select name="id_jurusan" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm transition-all appearance-none cursor-pointer">
                            <option value="">-- Umum / Tidak Ada --</option>
                            <?php foreach($jurusan_list as $jur): 
                                 $jId = is_object($jur) ? $jur->id : $jur['id'];
                                 $jNama = is_object($jur) ? $jur->nama_jurusan : $jur['nama_jurusan'];
                                 $jUnit = is_object($jur) ? $jur->kode_jenjang : $jur['kode_jenjang'];
                            ?>
                                <option value="<?= $jId ?>" x-show="!unit || unit == '<?= $jUnit ?>'" <?= ($getVal($siswa, 'id_jurusan') == $jId) ? 'selected' : '' ?>>
                                    <?= esc($jNama) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>

                <!-- Angkatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tahun Angkatan <span class="text-rose-500">*</span></label>
                    <input type="number" name="angkatan" required value="<?= old('angkatan', $getVal($siswa, 'angkatan') ?: date('Y')) ?>" 
                           class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm">
                </div>
            </div>
        </div>

        <!-- 2. IDENTITAS PRIBADI -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-white/5 shadow-sm p-8">
            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b border-slate-100 pb-4 mb-6">
                <i class="fas fa-user mr-2"></i> II. Identitas Pribadi
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" required value="<?= old('nama_lengkap', $getVal($siswa, 'nama_lengkap')) ?>" 
                           class="w-full px-5 py-4 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-lg uppercase tracking-wide placeholder-slate-300"
                           placeholder="CONTOH: AHMAD DAHLAN">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">NIS (Nomor Induk Siswa) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nis" required value="<?= old('nis', $getVal($siswa, 'nis')) ?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">NISN</label>
                    <input type="text" name="nisn" value="<?= old('nisn', $getVal($siswa, 'nisn')) ?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">NIK</label>
                    <input type="text" name="nik" value="<?= old('nik', $getVal($siswa, 'nik')) ?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="jenis_kelamin" required class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm appearance-none cursor-pointer">
                            <option value="L" <?= $getVal($siswa, 'jenis_kelamin') == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= $getVal($siswa, 'jenis_kelamin') == 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Status Siswa</label>
                    <div class="relative">
                        <select name="status" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm appearance-none cursor-pointer">
                            <option value="Aktif" <?= $getVal($siswa, 'status') == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="Lulus" <?= $getVal($siswa, 'status') == 'Lulus' ? 'selected' : '' ?>>Lulus</option>
                            <option value="Pindah" <?= $getVal($siswa, 'status') == 'Pindah' ? 'selected' : '' ?>>Pindah</option>
                            <option value="Keluar" <?= $getVal($siswa, 'status') == 'Keluar' ? 'selected' : '' ?>>Keluar</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. DEMOGRAFI & ALAMAT -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-white/5 shadow-sm p-8">
            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b border-slate-100 pb-4 mb-6">
                <i class="fas fa-map-marker-alt mr-2"></i> III. Demografi & Alamat
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= old('tempat_lahir', $getVal($demografi, 'tempat_lahir')) ?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir', $getVal($demografi, 'tanggal_lahir')) ?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Alamat Lengkap</label>
                    <textarea name="alamat" rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm" placeholder="Jalan, RT/RW, Dusun..."><?= old('alamat', $getVal($demografi, 'alamat')) ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nama Ibu Kandung</label>
                    <input type="text" name="nama_ibu_kandung" value="<?= old('nama_ibu_kandung', $getVal($demografi, 'nama_ibu')) ?>" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Agama</label>
                    <div class="relative">
                        <select name="agama" class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-100 focus:border-indigo-500 outline-none font-bold text-sm appearance-none cursor-pointer">
                            <option value="">-- Pilih Agama --</option>
                            <?php foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agm): ?>
                                <option value="<?= $agm ?>" <?= $getVal($demografi, 'agama') == $agm ? 'selected' : '' ?>><?= $agm ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. DATA KELUARGA (AYAH, IBU, WALI) -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-white/5 shadow-sm p-8">
            <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest border-b border-slate-100 pb-4 mb-6">
                <i class="fas fa-users mr-2"></i> IV. Data Keluarga
            </h3>
            
            <div class="space-y-8">
                <!-- AYAH -->
                <div class="p-5 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                        <h4 class="text-sm font-black text-slate-700 uppercase">Data Ayah</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="hidden" name="keluarga[ayah][hubungan]" value="Ayah">
                        <input type="hidden" name="keluarga[ayah][id]" value="<?= $getVal($ayah, 'id') ?>">
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Ayah</label>
                            <input type="text" name="keluarga[ayah][nama_lengkap]" value="<?= old('keluarga.ayah.nama_lengkap', $getVal($ayah, 'nama_lengkap')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pekerjaan</label>
                            <input type="text" name="keluarga[ayah][pekerjaan]" value="<?= old('keluarga.ayah.pekerjaan', $getVal($ayah, 'pekerjaan')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. Telepon</label>
                            <input type="text" name="keluarga[ayah][no_telepon]" value="<?= old('keluarga.ayah.no_telepon', $getVal($ayah, 'no_telepon')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- IBU -->
                <div class="p-5 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-6 bg-rose-500 rounded-full"></span>
                        <h4 class="text-sm font-black text-slate-700 uppercase">Data Ibu</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="hidden" name="keluarga[ibu][hubungan]" value="Ibu">
                        <input type="hidden" name="keluarga[ibu][id]" value="<?= $getVal($ibu, 'id') ?>">
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Ibu</label>
                            <input type="text" name="keluarga[ibu][nama_lengkap]" value="<?= old('keluarga.ibu.nama_lengkap', $getVal($ibu, 'nama_lengkap')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pekerjaan</label>
                            <input type="text" name="keluarga[ibu][pekerjaan]" value="<?= old('keluarga.ibu.pekerjaan', $getVal($ibu, 'pekerjaan')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. Telepon</label>
                            <input type="text" name="keluarga[ibu][no_telepon]" value="<?= old('keluarga.ibu.no_telepon', $getVal($ibu, 'no_telepon')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- WALI (OPSIONAL) -->
                <div class="p-5 bg-slate-50 rounded-2xl border border-dashed border-slate-200 opacity-80 hover:opacity-100 transition-opacity">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                        <h4 class="text-sm font-black text-slate-700 uppercase">Data Wali (Opsional)</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="hidden" name="keluarga[wali][hubungan]" value="Wali">
                        <input type="hidden" name="keluarga[wali][id]" value="<?= $getVal($wali, 'id') ?>">
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Wali</label>
                            <input type="text" name="keluarga[wali][nama_lengkap]" value="<?= old('keluarga.wali.nama_lengkap', $getVal($wali, 'nama_lengkap')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pekerjaan</label>
                            <input type="text" name="keluarga[wali][pekerjaan]" value="<?= old('keluarga.wali.pekerjaan', $getVal($wali, 'pekerjaan')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">No. Telepon</label>
                            <input type="text" name="keluarga[wali][no_telepon]" value="<?= old('keluarga.wali.no_telepon', $getVal($wali, 'no_telepon')) ?>" class="w-full px-3 py-2.5 rounded-xl border-2 border-white focus:border-indigo-500 outline-none text-sm font-bold shadow-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTON -->
        <div class="sticky bottom-6 z-40">
            <div class="bg-slate-900/90 backdrop-blur-md p-4 rounded-2xl shadow-2xl border border-slate-700 flex justify-between items-center max-w-5xl mx-auto">
                <div class="text-white hidden sm:block">
                    <p class="text-xs font-bold opacity-70 uppercase tracking-wider">Konfirmasi Penyimpanan</p>
                    <p class="text-[10px] opacity-50">Pastikan seluruh data wajib (*) telah terisi dengan benar.</p>
                </div>
                <div class="flex gap-3 w-full sm:w-auto">
                    <button type="reset" class="flex-1 sm:flex-none px-6 py-3 rounded-xl border border-slate-600 text-slate-300 font-bold text-xs uppercase tracking-widest hover:bg-slate-800 transition-all">
                        Reset
                    </button>
                    <button type="submit" class="flex-1 sm:flex-none px-8 py-3 rounded-xl bg-indigo-600 text-white font-black text-xs uppercase tracking-widest shadow-lg shadow-indigo-500/30 hover:bg-indigo-500 hover:-translate-y-0.5 transition-all">
                        <i class="fas fa-save mr-2"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Daftarkan Siswa' ?>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.5s cubic-bezier(0.165, 0.84, 0.44, 1) forwards; }
    
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<?= $this->endSection() ?>