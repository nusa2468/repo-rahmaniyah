<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- Load Premium Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-5xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase italic">
                <?= esc($title) ?>
            </h1>
            <p class="text-xs text-slate-500 font-bold uppercase tracking-widest mt-1">
                Manajemen Alokasi Waktu Belajar Mengajar
            </p>
        </div>
        <a href="<?= base_url('app/akademik/jadwalpelajaran') ?>" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border-2 border-slate-200 text-slate-500 hover:text-indigo-600 hover:border-indigo-600 transition-all shadow-sm group">
            <i class="fas fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
        </a>
    </div>

    <!-- Alert Error -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-600 p-4 rounded-r-xl shadow-sm flex items-start gap-3 animate-in fade-in slide-in-from-top-2">
            <i class="fas fa-exclamation-triangle text-rose-600 mt-0.5"></i>
            <div>
                <h3 class="text-sm font-bold text-rose-800">Terdapat Kesalahan</h3>
                <p class="text-xs text-rose-700 mt-1"><?= session()->getFlashdata('error') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Form Card -->
    <div class="bg-white rounded-2xl border-2 border-slate-100 shadow-xl overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b-2 border-slate-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-md">
                <i class="fas fa-calendar-plus text-xs"></i>
            </div>
            <h2 class="text-sm font-black text-slate-700 uppercase tracking-widest">Formulir Jadwal (Smart Filter)</h2>
        </div>

        <div class="p-6 sm:p-8">
            <?php
            $isEdit = isset($jadwal) && !empty($jadwal);
            $errors = session()->getFlashdata('errors') ?? [];
            
            $formAction = $isEdit 
                ? base_url('app/akademik/jadwalpelajaran/update/' . $jadwal['id']) 
                : base_url('app/akademik/jadwalpelajaran/create');
            
            $dataToFill = $isEdit ? (array)$jadwal : [];
            
            // Helper local
            if (!function_exists('getVal')) {
                function getVal($field, $data) {
                    return old($field) !== null ? old($field) : ($data[$field] ?? '');
                }
            }
            if (!function_exists('isInvalid')) {
                function isInvalid($field, $errors) {
                    return isset($errors[$field]) ? 'border-rose-500 focus:ring-rose-200' : 'border-slate-200 focus:ring-indigo-200';
                }
            }

            // --- LOGIKA PROTEKSI SCOPE (UNIT) ---
            $sessionUnit = session()->get('kode_jenjang');
            $isGlobalUser = empty($sessionUnit) || in_array(strtoupper($sessionUnit), ['GLOBAL', 'YAYASAN', 'PUSAT']);
            
            $defaultUnit = $isEdit ? ($jadwal['kode_jenjang'] ?? '') : ($sessionUnit ?? '');

            // Info Tahun Ajaran & Semester Aktif
            $currentTaId = getVal('id_tahun_ajaran', $dataToFill) ?: ($active_ta_id ?? null);
            $currentTaInfo = 'Belum Ditentukan';
            $activeSemester = ''; // Default kosong

            if (!empty($tahun_ajaran)) {
                foreach ($tahun_ajaran as $ta) {
                    if ($ta['id'] == $currentTaId) {
                        $currentTaInfo = esc($ta['tahun_ajaran']) . " - " . esc($ta['semester']);
                        $activeSemester = $ta['semester']; // Ambil semester (Ganjil/Genap)
                        break;
                    }
                }
            }

            // --- PRE-CALCULATE TINGKAT KELAS TERPILIH ---
            // Agar saat edit, filter tingkat langsung jalan
            $defaultTingkat = '';
            $selKlsId = getVal('id_kelas', $dataToFill);
            if ($selKlsId && !empty($kelas)) {
                foreach ($kelas as $k) {
                    $kId = is_array($k) ? $k['id'] : $k->id;
                    if ($kId == $selKlsId) {
                        $defaultTingkat = is_array($k) ? ($k['tingkat'] ?? '') : ($k->tingkat ?? '');
                        break;
                    }
                }
            }

            // Safety Check
            $kelas = $kelas ?? [];
            $kurikulum = $kurikulum ?? [];
            $mapel = $mapel ?? [];
            $guru = $guru ?? [];
            ?>

            <!-- ALPINE JS COMPONENT FOR SMART FILTERING -->
            <form action="<?= $formAction ?>" method="post" 
                  x-data="{ 
                      unit: '<?= esc($defaultUnit) ?>',
                      tingkat: '<?= esc($defaultTingkat) ?>',
                      semester: '<?= esc($activeSemester) ?>',

                      // Logika Smart Filter Mata Pelajaran
                      // Menyaring berdasarkan 3 Kriteria: Unit, Tingkat Kelas, dan Semester
                      isMapelVisible(elUnit, elTingkat, elSemester) {
                          // 1. Cek Unit (Wajib Cocok)
                          if (this.unit && elUnit !== 'ALL' && elUnit !== this.unit.toUpperCase()) {
                              return false;
                          }

                          // 2. Cek Tingkat (Jika kelas sudah dipilih)
                          // Jika mapel punya tingkat spesifik (bukan 0/null), harus sama dengan tingkat kelas
                          if (this.tingkat && elTingkat && elTingkat != 0 && elTingkat != this.tingkat) {
                              return false;
                          }

                          // 3. Cek Semester (Wajib Cocok dengan TA Aktif)
                          // Jika mapel punya semester spesifik, harus sama dengan semester aktif
                          if (this.semester && elSemester && elSemester !== this.semester) {
                              return false;
                          }

                          return true;
                      },

                      // Update Tingkat saat Kelas berubah
                      updateTingkat(e) {
                          const selected = e.target.options[e.target.selectedIndex];
                          this.tingkat = selected.dataset.tingkat || '';
                      }
                  }">
                
                <?= csrf_field() ?>
                <?php if ($isEdit) : ?>
                    <input type="hidden" name="_method" value="PUT">
                <?php endif; ?>
                
                <input type="hidden" name="id_tahun_ajaran" value="<?= esc($currentTaId) ?>">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- LEFT COLUMN -->
                    <div class="space-y-6">
                        <!-- Info Panel & Unit Selector -->
                        <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 space-y-3">
                            <div>
                                <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Tahun Akademik</label>
                                <div class="font-bold text-indigo-700 text-sm flex items-center gap-2">
                                    <i class="fas fa-clock"></i> <?= $currentTaInfo ?>
                                </div>
                            </div>

                            <!-- UNIT SELECTOR -->
                            <div class="pt-3 border-t border-indigo-100">
                                <label for="kode_jenjang" class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">
                                    Unit / Jenjang Sekolah
                                </label>
                                <?php if ($isGlobalUser): ?>
                                    <select x-model="unit" name="kode_jenjang" id="kode_jenjang" class="w-full text-sm font-bold text-slate-700 bg-white rounded-lg border-2 border-indigo-200 focus:border-indigo-500 focus:ring-indigo-200 py-2 px-3 transition-all">
                                        <option value="">-- Pilih Unit (Opsional) --</option>
                                        <option value="SD">SD (Sekolah Dasar)</option>
                                        <option value="SMP">SMP (Sekolah Menengah Pertama)</option>
                                        <option value="SMA">SMA (Sekolah Menengah Atas)</option>
                                    </select>
                                    <p class="text-[9px] text-slate-400 mt-1 italic">* Pilihan unit otomatis memfilter Kelas dan Mapel.</p>
                                <?php else: ?>
                                    <input type="hidden" name="kode_jenjang" x-model="unit">
                                    <div class="font-black text-slate-700 text-sm uppercase flex items-center gap-2">
                                        <i class="fas fa-building text-slate-400"></i> Unit <?= esc($sessionUnit) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- KELAS / ROMBEL -->
                        <div>
                            <label for="id_kelas" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                Kelas (Rombel Utama) <span class="text-rose-500">*</span>
                            </label>
                            <select @change="updateTingkat($event)" class="w-full text-sm font-semibold rounded-xl bg-slate-50 focus:bg-white transition-all outline-none py-3 px-4 border-2 <?= isInvalid('id_kelas', $errors) ?>" id="id_kelas" name="id_kelas">
                                <option value="" data-tingkat="">-- Pilih Kelas --</option>
                                
                                <?php if(empty($kelas)): ?>
                                    <option value="" disabled>Data Kelas Tidak Ditemukan</option>
                                <?php endif; ?>

                                <?php $selKls = getVal('id_kelas', $dataToFill); ?>
                                <?php foreach ($kelas as $k) : ?>
                                    <?php 
                                    $kId = is_array($k) ? $k['id'] : $k->id;
                                    $kNama = is_array($k) ? ($k['nama_kelas'] ?? $k['nama_grup'] ?? '-') : ($k->nama_kelas ?? $k->nama_grup ?? '-'); 
                                    $kUnit = strtoupper(is_array($k) ? ($k['kode_jenjang'] ?? 'ALL') : ($k->kode_jenjang ?? 'ALL'));
                                    $kTingkat = is_array($k) ? ($k['tingkat'] ?? '') : ($k->tingkat ?? '');
                                    ?>
                                    <option value="<?= $kId ?>" 
                                            data-unit="<?= $kUnit ?>"
                                            data-tingkat="<?= $kTingkat ?>"
                                            x-show="!unit || unit === '<?= $kUnit ?>' || '<?= $kUnit ?>' === 'ALL'"
                                            <?= ($selKls == $kId) ? 'selected' : '' ?>>
                                            [<?= $kUnit ?>] <?= esc($kNama) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-rose-500 text-[10px] font-bold mt-1 block"><?= $errors['id_kelas'] ?? '' ?></small>
                        </div>

                        <!-- Mata Pelajaran (Smart Filtered) -->
                        <div>
                            <label for="id_mata_pelajaran" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select class="w-full text-sm font-semibold rounded-xl bg-slate-50 focus:bg-white transition-all outline-none py-3 px-4 border-2 <?= isInvalid('id_mata_pelajaran', $errors) ?>" id="id_mata_pelajaran" name="id_mata_pelajaran">
                                    <option value="">-- Pilih Mapel --</option>
                                    <?php $selMapel = getVal('id_mata_pelajaran', $dataToFill); ?>
                                    <?php foreach ($mapel as $m) : ?>
                                        <?php 
                                            $mId = is_array($m) ? $m['id'] : $m->id;
                                            $mNama = is_array($m) ? $m['nama_mapel'] : $m->nama_mapel;
                                            $mUnit = strtoupper(is_array($m) ? ($m['kode_jenjang'] ?? 'ALL') : ($m->kode_jenjang ?? 'ALL'));
                                            $mTingkat = is_array($m) ? ($m['tingkat'] ?? '') : ($m->tingkat ?? '');
                                            $mSemester = is_array($m) ? ($m['semester'] ?? '') : ($m->semester ?? '');
                                            
                                            // Format Label Tambahan (Opsional)
                                            $labelSuffix = [];
                                            if($mTingkat) $labelSuffix[] = "Tk.$mTingkat";
                                            if($mSemester) $labelSuffix[] = "Smt.$mSemester";
                                            $suffix = !empty($labelSuffix) ? " (" . implode(', ', $labelSuffix) . ")" : "";
                                        ?>
                                        <option value="<?= $mId ?>" 
                                                data-unit="<?= $mUnit ?>"
                                                data-tingkat="<?= $mTingkat ?>"
                                                data-semester="<?= $mSemester ?>"
                                                x-show="isMapelVisible('<?= $mUnit ?>', '<?= $mTingkat ?>', '<?= $mSemester ?>')"
                                                <?= ($selMapel == $mId) ? 'selected' : '' ?>>
                                                <?= esc($mNama) ?><?= $suffix ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- Indikator Filter Aktif -->
                                <div x-show="tingkat || semester" class="absolute top-0 right-0 -mt-6 flex gap-1">
                                    <span x-show="tingkat" class="text-[9px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded font-bold uppercase" x-text="'Tingkat ' + tingkat"></span>
                                    <span x-show="semester" class="text-[9px] px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded font-bold uppercase" x-text="semester"></span>
                                </div>
                            </div>
                            <small class="text-rose-500 text-[10px] font-bold mt-1 block"><?= $errors['id_mata_pelajaran'] ?? '' ?></small>
                        </div>
                        
                        <!-- Grup Siswa (Optional) -->
                        <div x-data="{ showGroup: false }">
                             <div class="flex items-center gap-2 mb-2 cursor-pointer" @click="showGroup = !showGroup">
                                 <i class="fas fa-users text-indigo-500"></i>
                                 <span class="text-[10px] font-bold text-indigo-600 uppercase underline">Pecah ke Kelompok Belajar?</span>
                             </div>
                             <div x-show="showGroup" x-transition>
                                <select name="id_grup_siswa" id="id_grup_siswa" class="w-full text-sm rounded-xl bg-slate-50 border-2 border-slate-100 py-2 px-3">
                                    <option value="">-- Biarkan Kosong (Satu Kelas Penuh) --</option>
                                </select>
                             </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="space-y-6">
                        
                        <!-- Guru Pengajar -->
                        <div>
                            <label for="id_guru" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                Guru Pengajar <span class="text-rose-500">*</span>
                            </label>
                            <select class="w-full text-sm font-semibold rounded-xl bg-slate-50 focus:bg-white transition-all outline-none py-3 px-4 border-2 <?= isInvalid('id_guru', $errors) ?>" id="id_guru" name="id_guru">
                                <option value="">-- Pilih Guru --</option>
                                <?php $selGuru = getVal('id_guru', $dataToFill); ?>
                                <?php foreach ($guru as $g) : ?>
                                    <?php 
                                        $gId = is_array($g) ? $g['id'] : $g->id;
                                        $gName = is_array($g) ? $g['nama_lengkap'] : $g->nama_lengkap;
                                        $gUnit = strtoupper(is_array($g) ? ($g['kode_jenjang'] ?? 'ALL') : ($g->kode_jenjang ?? 'ALL'));
                                        if(empty($gUnit)) $gUnit = 'ALL';
                                    ?>
                                    <option value="<?= $gId ?>" 
                                            data-unit="<?= $gUnit ?>"
                                            x-show="!unit || unit === '<?= $gUnit ?>' || '<?= $gUnit ?>' === 'ALL'"
                                            <?= ($selGuru == $gId) ? 'selected' : '' ?>>
                                            <?= esc($gName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-rose-500 text-[10px] font-bold mt-1 block"><?= $errors['id_guru'] ?? '' ?></small>
                        </div>
                        
                        <!-- Kurikulum (Secondary) -->
                        <div>
                            <label for="id_kurikulum" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                Kurikulum <span class="text-rose-500">*</span>
                            </label>
                            <select class="w-full text-sm font-semibold rounded-xl bg-slate-50 focus:bg-white transition-all outline-none py-3 px-4 border-2 <?= isInvalid('id_kurikulum', $errors) ?>" id="id_kurikulum" name="id_kurikulum">
                                <option value="">-- Pilih Kurikulum --</option>
                                <?php $selKur = getVal('id_kurikulum', $dataToFill); ?>
                                <?php foreach ($kurikulum as $kuri) : ?>
                                    <?php $kUnit = strtoupper($kuri['kode_jenjang'] ?? 'ALL'); ?>
                                    <option value="<?= $kuri['id'] ?>" 
                                            data-unit="<?= $kUnit ?>"
                                            x-show="!unit || unit === '<?= $kUnit ?>' || '<?= $kUnit ?>' === 'ALL'"
                                            <?= ($selKur == $kuri['id']) ? 'selected' : '' ?>>
                                            <?= esc($kuri['nama_kurikulum']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-rose-500 text-[10px] font-bold mt-1 block"><?= $errors['id_kurikulum'] ?? '' ?></small>
                        </div>

                        <!-- Hari -->
                        <div>
                            <label for="hari" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                Hari Pelaksanaan <span class="text-rose-500">*</span>
                            </label>
                            <select class="w-full text-sm font-semibold rounded-xl bg-slate-50 focus:bg-white transition-all outline-none py-3 px-4 border-2 <?= isInvalid('hari', $errors) ?>" id="hari" name="hari">
                                <option value="">-- Pilih Hari --</option>
                                <?php 
                                $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']; 
                                $selHari = getVal('hari', $dataToFill);
                                foreach ($hariOptions as $h) : 
                                ?>
                                    <option value="<?= $h ?>" <?= ($selHari == $h) ? 'selected' : '' ?>><?= $h ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-rose-500 text-[10px] font-bold mt-1 block"><?= $errors['hari'] ?? '' ?></small>
                        </div>

                        <!-- Waktu (Grid) -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="jam_mulai" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                    Jam Mulai <span class="text-rose-500">*</span>
                                </label>
                                <input type="time" class="w-full text-sm font-semibold rounded-xl bg-slate-50 focus:bg-white transition-all outline-none py-3 px-4 border-2 <?= isInvalid('jam_mulai', $errors) ?>" id="jam_mulai" name="jam_mulai" value="<?= esc(getVal('jam_mulai', $dataToFill)) ?>">
                                <small class="text-rose-500 text-[10px] font-bold mt-1 block"><?= $errors['jam_mulai'] ?? '' ?></small>
                            </div>
                            <div>
                                <label for="jam_selesai" class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">
                                    Jam Selesai <span class="text-rose-500">*</span>
                                </label>
                                <input type="time" class="w-full text-sm font-semibold rounded-xl bg-slate-50 focus:bg-white transition-all outline-none py-3 px-4 border-2 <?= isInvalid('jam_selesai', $errors) ?>" id="jam_selesai" name="jam_selesai" value="<?= esc(getVal('jam_selesai', $dataToFill)) ?>">
                                <small class="text-rose-500 text-[10px] font-bold mt-1 block"><?= $errors['jam_selesai'] ?? '' ?></small>
                            </div>
                        </div>

                        <!-- Ruangan (Sapras) -->
                        <div class="pt-4 mt-4 border-t-2 border-slate-100">
                            <label for="id_ruangan" class="block text-xs font-bold text-indigo-600 uppercase tracking-wide mb-2">
                                <i class="fas fa-door-open mr-1"></i> Ruangan (Aset Sapras)
                            </label>
                            <select class="w-full text-sm font-semibold rounded-xl bg-indigo-50/50 focus:bg-white transition-all outline-none py-3 px-4 border-2 border-indigo-100 focus:border-indigo-500 <?= isInvalid('id_ruangan', $errors) ?>" id="id_ruangan" name="id_ruangan">
                                <option value="">-- Pilih Ruangan / Luar Kelas --</option>
                                <?php 
                                $selRuangan = getVal('id_ruangan', $dataToFill); 
                                if (!empty($list_ruangan)) :
                                    foreach ($list_ruangan as $room) : 
                                        $roomId = is_array($room) ? $room['id'] : $room->id;
                                        $roomNama = is_array($room) ? $room['nama'] : $room->nama;
                                        $roomKapasitas = is_array($room) ? $room['kapasitas'] : $room->kapasitas;
                                ?>
                                    <option value="<?= $roomId ?>" <?= ($selRuangan == $roomId) ? 'selected' : '' ?>>
                                        <?= esc($roomNama) ?> (Kap: <?= esc($roomKapasitas) ?>)
                                    </option>
                                <?php 
                                    endforeach; 
                                endif; 
                                ?>
                            </select>
                            <small class="text-rose-500 text-[10px] font-bold mt-1 block"><?= $errors['id_ruangan'] ?? '' ?></small>
                            
                            <!-- Conflict Alert -->
                            <div class="mt-4 p-3 bg-amber-50 border-l-4 border-amber-400 rounded-r-lg flex gap-3">
                                <i class="fas fa-shield-alt text-amber-500 text-lg mt-0.5"></i>
                                <div>
                                    <p class="text-[10px] font-black text-amber-800 uppercase tracking-wide">Sistem Proteksi Jadwal</p>
                                    <p class="text-xs text-amber-700 leading-tight mt-1">
                                        Sistem akan otomatis menolak jika terdeteksi bentrok pada Guru, Rombel, atau Ruangan di jam yang sama.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Buttons -->
                <div class="mt-8 pt-6 border-t-2 border-slate-100 flex items-center justify-end gap-3">
                    <a href="<?= base_url('app/akademik/jadwalpelajaran') ?>" class="px-6 py-3 rounded-xl border-2 border-slate-200 text-slate-500 font-bold text-xs uppercase tracking-widest hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all">
                        <i class="fas fa-save mr-2"></i> Simpan Jadwal
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>