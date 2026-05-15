<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php 
    $p = (array) ($pegawai ?? []); 
    $docs = $dokumen ?? []; 
    $eduList = $pendidikan ?? []; 
    $careerList = $riwayat_kepegawaian ?? [];
?>

<div class="container-fluid mb-6 px-4" x-data="{ subTab: 'docs' }">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight leading-none mb-2">
                    <?= empty($p['id']) ? 'Registrasi Pegawai Baru' : 'Perbarui Profil Pegawai' ?>
                </h1>
                <p class="text-sm text-slate-500 font-medium">Manajemen data identitas, berkas, pendidikan, dan riwayat karir terpadu.</p>
            </div>
            <div class="flex items-center gap-3">
                <?php if(!empty($p['id'])): ?>
                <a href="<?= route_to('pegawai_show', $p['id']) ?>" class="px-5 py-2.5 bg-slate-800 text-white rounded-xl text-xs font-bold uppercase tracking-widest hover:bg-slate-700 shadow-lg transition-all no-underline">
                    <i class="fas fa-eye mr-2 text-[10px]"></i> Lihat Profil
                </a>
                <?php endif; ?>
                <a href="<?= route_to('pegawai_index') ?>" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold uppercase tracking-widest text-slate-600 hover:bg-slate-50 hover:text-slate-800 shadow-sm transition-all no-underline">
                    <i class="fas fa-times mr-2 text-[10px]"></i> Batal
                </a>
            </div>
        </div>

        <!-- FORM DATA UTAMA -->
        <!-- FIX: Ditambahkan enctype="multipart/form-data" agar upload foto profil berhasil -->
        <form action="<?= empty($p['id']) ? route_to('pegawai_create') : route_to('pegawai_update', $p['id']) ?>" method="post" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>
            <?php if(!empty($p['id'])): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

            <!-- ERROR ALERT -->
            <?php if(session()->has('errors')): ?>
                <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl mb-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-rose-500 mt-0.5"></i>
                        <div class="text-sm text-rose-700">
                            <p class="font-bold mb-1">Terjadi kesalahan input:</p>
                            <ul class="list-disc pl-4 space-y-1">
                                <?php foreach(session('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- KOLOM KIRI: Identitas Utama (8 Cols) -->
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-slate-200 p-8 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-slate-100 flex items-center justify-center text-slate-500"><i class="fas fa-user"></i></span>
                            Identitas Personal
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Jenis Pegawai (TRIGGER JS) -->
                            <div class="md:col-span-2 p-4 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                                <label class="label-req text-indigo-900">Kategori Pegawai</label>
                                <div class="grid grid-cols-3 gap-3 mt-2">
                                    <?php $curJenis = $p['jenis_pegawai'] ?? 'guru'; ?>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="jenis_pegawai" value="guru" class="peer sr-only" onchange="toggleForm()" <?= ($curJenis == 'guru') ? 'checked' : '' ?>>
                                        <div class="px-4 py-3 rounded-xl border-2 border-indigo-200 bg-white text-center text-sm font-bold text-slate-500 peer-checked:border-indigo-600 peer-checked:text-indigo-600 peer-checked:bg-indigo-50 group-hover:border-indigo-300 transition-all">
                                            <i class="fas fa-chalkboard-teacher block mb-1 text-lg"></i> GURU
                                        </div>
                                    </label>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="jenis_pegawai" value="staff" class="peer sr-only" onchange="toggleForm()" <?= ($curJenis == 'staff') ? 'checked' : '' ?>>
                                        <div class="px-4 py-3 rounded-xl border-2 border-indigo-200 bg-white text-center text-sm font-bold text-slate-500 peer-checked:border-amber-500 peer-checked:text-amber-600 peer-checked:bg-amber-50 group-hover:border-amber-300 transition-all">
                                            <i class="fas fa-briefcase block mb-1 text-lg"></i> STAFF
                                        </div>
                                    </label>
                                    <label class="cursor-pointer group">
                                        <input type="radio" name="jenis_pegawai" value="penunjang" class="peer sr-only" onchange="toggleForm()" <?= ($curJenis == 'penunjang') ? 'checked' : '' ?>>
                                        <div class="px-4 py-3 rounded-xl border-2 border-indigo-200 bg-white text-center text-sm font-bold text-slate-500 peer-checked:border-emerald-500 peer-checked:text-emerald-600 peer-checked:bg-emerald-50 group-hover:border-emerald-300 transition-all">
                                            <i class="fas fa-hands-helping block mb-1 text-lg"></i> PENUNJANG
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="label-req">Unit Kerja Penempatan</label>
                                <select name="kode_jenjang" class="form-input" required>
                                    <?php foreach ($jenjang_list as $j): $j = (array)$j; ?>
                                        <option value="<?= $j['kode_jenjang'] ?>" <?= ($p['kode_jenjang'] ?? '') == $j['kode_jenjang'] ? 'selected' : '' ?>>
                                            Unit <?= strtoupper($j['kode_jenjang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-4 mb-6">
                            <div class="col-span-3">
                                <label class="label">Gelar Dpn</label>
                                <input type="text" name="gelar_depan" value="<?= old('gelar_depan', $p['gelar_depan'] ?? '') ?>" class="form-input" placeholder="Dr.">
                            </div>
                            <div class="col-span-6">
                                <label class="label-req">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" value="<?= old('nama_lengkap', $p['nama_lengkap'] ?? '') ?>" class="form-input font-bold text-base" required placeholder="Nama asli sesuai KTP">
                            </div>
                            <div class="col-span-3">
                                <label class="label">Gelar Blkg</label>
                                <input type="text" name="gelar_belakang" value="<?= old('gelar_belakang', $p['gelar_belakang'] ?? '') ?>" class="form-input" placeholder="S.Pd">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="label-req">NIK (Sesuai KTP)</label>
                                <input type="text" name="nik" value="<?= old('nik', $p['nik'] ?? '') ?>" class="form-input font-mono" required maxlength="16" placeholder="16 Digit Angka">
                            </div>
                            <div>
                                <label class="label-req">Jenis Kelamin</label>
                                <div class="flex gap-4 mt-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="L" class="text-indigo-600 focus:ring-indigo-500" <?= ($p['jenis_kelamin'] ?? 'L') == 'L' ? 'checked' : '' ?>>
                                        <span class="text-sm font-bold text-slate-700">Laki-laki</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="jenis_kelamin" value="P" class="text-indigo-600 focus:ring-indigo-500" <?= ($p['jenis_kelamin'] ?? '') == 'P' ? 'checked' : '' ?>>
                                        <span class="text-sm font-bold text-slate-700">Perempuan</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><label class="label">Email Aktif</label><input type="email" name="email" value="<?= old('email', $p['email'] ?? '') ?>" class="form-input" placeholder="email@domain.com"></div>
                            <div><label class="label">No. HP / WhatsApp</label><input type="text" name="no_hp" value="<?= old('no_hp', $p['no_hp'] ?? '') ?>" class="form-input font-mono" placeholder="08xxxx"></div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Status Kepegawaian (4 Cols) -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-slate-50 dark:bg-gray-800 rounded-2xl shadow-inner border border-slate-200 p-6">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-slate-200 flex items-center justify-center text-slate-500"><i class="fas fa-briefcase"></i></span>
                            Data Jabatan Aktif
                        </h3>
                        <div class="space-y-4">
                            <div id="field-nuptk">
                                <label class="label text-indigo-600">NUPTK (Pendidik)</label>
                                <input type="text" name="nuptk" value="<?= old('nuptk', $p['nuptk'] ?? '') ?>" class="form-input bg-white border-indigo-200 focus:border-indigo-500" placeholder="Nomor Unik Pendidik">
                            </div>
                            <div>
                                <label class="label">NIP (ASN)</label>
                                <input type="text" name="nip" value="<?= old('nip', $p['nip'] ?? '') ?>" class="form-input bg-white" placeholder="Kosongkan jika bukan PNS">
                            </div>
                            <div>
                                <label class="label">NIP Yayasan</label>
                                <input type="text" name="nipy" value="<?= old('nipy', $p['nipy'] ?? '') ?>" class="form-input bg-white">
                            </div>

                            <hr class="border-slate-200 border-dashed">

                            <div>
                                <label class="label">Status Kepegawaian</label>
                                <select name="status_kepegawaian" class="form-input bg-white">
                                    <?php foreach(['GTY/PTY', 'GTT/PTT', 'Guru Honor Sekolah', 'PNS', 'Tetap Yayasan', 'Kontrak', 'Honor'] as $st): ?>
                                        <option value="<?= $st ?>" <?= ($p['status_kepegawaian'] ?? '') == $st ? 'selected' : '' ?>><?= $st ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="label">Jenis PTK / Jabatan</label>
                                <select name="jenis_ptk" id="jenis_ptk" class="form-input bg-white"></select>
                            </div>
                            
                            <div>
                                <label class="label">Tugas Tambahan</label>
                                <input type="text" name="tugas_tambahan" value="<?= old('tugas_tambahan', $p['tugas_tambahan'] ?? '') ?>" class="form-input bg-white" placeholder="Wakasek / Koordinator / Pembina">
                            </div>

                            <!-- INPUT FOTO -->
                            <div class="pt-2">
                                <label class="label">Foto Profil</label>
                                <input type="file" name="foto" class="form-input bg-white text-xs file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <?php if(!empty($p['foto']) && file_exists('uploads/pegawai/'.$p['foto'])): ?>
                                    <div class="mt-2 text-xs text-slate-500">File saat ini: <?= esc($p['foto']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="pt-4">
                                <label class="label-req">Status Kerja</label>
                                <select name="status_aktif" class="form-input bg-white border-2 border-emerald-100 text-emerald-700 font-bold">
                                    <option value="aktif" <?= ($p['status_aktif'] ?? '') == 'aktif' ? 'selected' : '' ?>>✅ AKTIF BEKERJA</option>
                                    <option value="nonaktif" <?= ($p['status_aktif'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>❌ NON-AKTIF</option>
                                    <option value="cuti" <?= ($p['status_aktif'] ?? '') == 'cuti' ? 'selected' : '' ?>>⏸️ CUTI</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full py-4 bg-slate-900 hover:bg-black text-white font-black uppercase tracking-widest rounded-xl shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Simpan Data Pokok
                    </button>
                </div>
            </div>
        </form>

        <!-- AREA SUPLEMENTER: BERKAS, PENDIDIKAN, KARIR (Only Edit Mode) -->
        <?php if(!empty($p['id'])): ?>
            <div class="mt-12">
                <!-- Tab Headers -->
                <div class="flex items-center gap-2 mb-6 border-b border-slate-200">
                    <button @click="subTab = 'docs'" :class="subTab === 'docs' ? 'border-indigo-600 text-indigo-600 bg-indigo-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-6 py-3 border-b-4 text-[10px] font-black uppercase tracking-widest transition-all">
                        <i class="fas fa-folder-open mr-2"></i> Berkas Lampiran
                    </button>
                    <button @click="subTab = 'edu'" :class="subTab === 'edu' ? 'border-sky-500 text-sky-600 bg-sky-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-6 py-3 border-b-4 text-[10px] font-black uppercase tracking-widest transition-all">
                        <i class="fas fa-graduation-cap mr-2"></i> Pendidikan Formal
                    </button>
                    <button @click="subTab = 'career'" :class="subTab === 'career' ? 'border-purple-500 text-purple-600 bg-purple-50/30' : 'border-transparent text-slate-400 hover:text-slate-600'" class="px-6 py-3 border-b-4 text-[10px] font-black uppercase tracking-widest transition-all">
                        <i class="fas fa-history mr-2"></i> Riwayat Karir & SK
                    </button>
                </div>

                <!-- Tab Contents Wrapper -->
                <div class="bg-white dark:bg-gray-900 rounded-[1.5rem] border border-slate-200 p-8 shadow-sm">
                    
                    <!-- TAB 1: MANAJEMEN BERKAS -->
                    <div x-show="subTab === 'docs'" x-transition>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Form Upload Section -->
                            <div class="lg:col-span-1">
                                <form action="<?= route_to('pegawai_upload') ?>" method="post" enctype="multipart/form-data" class="p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-300">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_pegawai" value="<?= $p['id'] ?>">
                                    <div class="mb-4">
                                        <label class="label">Jenis Dokumen</label>
                                        <select name="jenis_dokumen" class="form-input bg-white">
                                            <option value="KTP">Kartu Tanda Penduduk (KTP)</option>
                                            <option value="KK">Kartu Keluarga (KK)</option>
                                            <option value="IJAZAH">Ijazah Terakhir</option>
                                            <option value="SK">SK Pengangkatan/Pangkat</option>
                                            <option value="SERTIFIKAT">Sertifikat Kompetensi</option>
                                            <option value="FOTO">Pas Foto Resmi</option>
                                        </select>
                                    </div>
                                    <div class="mb-4">
                                        <label class="label">Pilih File (PDF/JPG, Max 5MB)</label>
                                        <input type="file" name="file_dokumen" class="w-full text-xs font-medium text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700" required>
                                    </div>
                                    <button type="submit" class="w-full py-3 bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all">
                                        <i class="fas fa-cloud-upload-alt mr-2"></i> Unggah Berkas
                                    </button>
                                </form>
                            </div>
                            
                            <!-- List View Section -->
                            <div class="lg:col-span-2 space-y-3">
                                <?php if(empty($docs)): ?>
                                    <div class="text-center py-12 border-2 border-dashed border-slate-100 rounded-3xl">
                                        <i class="fas fa-file-invoice text-4xl text-slate-200 mb-3"></i>
                                        <p class="text-slate-400 italic text-xs uppercase tracking-widest">Belum ada berkas digital terlampir.</p>
                                    </div>
                                <?php else: foreach($docs as $doc): ?>
                                    <div class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition-all group">
                                        <div class="flex items-center gap-4 overflow-hidden">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-[10px] font-black shrink-0 <?= ($doc['tipe_file'] == 'pdf') ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600' ?>">
                                                <?= strtoupper($doc['tipe_file']) ?>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-black text-slate-800 truncate uppercase tracking-tight"><?= esc($doc['jenis_dokumen']) ?></p>
                                                <p class="text-[10px] text-slate-400 truncate leading-none mt-1"><?= esc($doc['nama_file']) ?> (<?= number_format($doc['ukuran_file']) ?> KB)</p>
                                            </div>
                                        </div>
                                        <div class="flex gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-all">
                                            <a href="<?= route_to('pegawai_download', $doc['id']) ?>" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm"><i class="fas fa-download text-[10px]"></i></a>
                                            <form action="<?= route_to('pegawai_delete_doc', $doc['id']) ?>" method="post" class="contents" onsubmit="return confirm('Hapus berkas ini permanen?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm"><i class="fas fa-trash-alt text-[10px]"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: RIWAYAT PENDIDIKAN -->
                    <div x-show="subTab === 'edu'" x-cloak x-transition>
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Sejarah Pendidikan Formal</h4>
                            <button type="button" class="px-4 py-2 bg-sky-500 text-white text-[10px] font-black uppercase rounded-lg shadow-lg shadow-sky-100 hover:bg-sky-600 hover:-translate-y-0.5 transition-all">
                                <i class="fas fa-plus mr-1"></i> Tambah Data
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs whitespace-nowrap">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-3">Jenjang</th>
                                        <th class="px-4 py-3">Institusi Sekolah / Kampus</th>
                                        <th class="px-4 py-3">Bidang Studi / Jurusan</th>
                                        <th class="px-4 py-3 text-center">Tahun Lulus</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(empty($eduList)): ?>
                                        <tr><td colspan="5" class="py-12 text-center text-slate-300 italic uppercase tracking-widest text-[10px]">Belum ada data riwayat pendidikan.</td></tr>
                                    <?php else: foreach($eduList as $e): $e=(array)$e; ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-4 font-black text-slate-800 uppercase"><?= esc($e['jenjang']) ?></td>
                                            <td class="px-4 py-4 font-bold text-slate-700"><?= esc($e['nama_sekolah']) ?></td>
                                            <td class="px-4 py-4 text-slate-500"><?= esc($e['jurusan'] ?: '-') ?></td>
                                            <td class="px-4 py-4 text-center"><span class="px-3 py-1 bg-sky-50 text-sky-700 font-black rounded-lg"><?= esc($e['tahun_lulus']) ?></span></td>
                                            <td class="px-4 py-4 text-right">
                                                <button class="w-7 h-7 rounded bg-slate-100 text-slate-400 hover:text-rose-600 transition-all"><i class="fas fa-trash-alt text-[10px]"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: RIWAYAT KARIR -->
                    <div x-show="subTab === 'career'" x-cloak x-transition>
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Riwayat Karir, Pangkat & SK</h4>
                            <button type="button" class="px-4 py-2 bg-purple-500 text-white text-[10px] font-black uppercase rounded-lg shadow-lg shadow-purple-100 hover:bg-purple-600 hover:-translate-y-0.5 transition-all">
                                <i class="fas fa-plus mr-1"></i> Tambah SK
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs whitespace-nowrap">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr>
                                        <th class="px-4 py-3">No. SK / Tanggal</th>
                                        <th class="px-4 py-3">Terhitung (TMT)</th>
                                        <th class="px-4 py-3">Jenis SK</th>
                                        <th class="px-4 py-3">Jabatan / Golongan</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(empty($careerList)): ?>
                                        <tr><td colspan="6" class="py-12 text-center text-slate-300 italic uppercase tracking-widest text-[10px]">Belum ada riwayat kepangkatan / SK.</td></tr>
                                    <?php else: foreach($careerList as $c): $c=(array)$c; ?>
                                        <tr class="hover:bg-slate-50 transition-colors <?= ($c['is_aktif']==1) ? 'bg-indigo-50/30' : '' ?>">
                                            <td class="px-4 py-4">
                                                <div class="font-black text-slate-800"><?= esc($c['no_sk']) ?></div>
                                                <div class="text-[9px] text-slate-400 uppercase font-bold"><?= date('d M Y', strtotime($c['tanggal_sk'])) ?></div>
                                            </td>
                                            <td class="px-4 py-4 font-mono font-black text-indigo-600"><?= date('d/m/Y', strtotime($c['tmt_sk'])) ?></td>
                                            <td class="px-4 py-4"><span class="px-2 py-0.5 rounded bg-slate-100 text-[9px] font-black uppercase text-slate-600"><?= esc($c['jenis_sk']) ?></span></td>
                                            <td class="px-4 py-4">
                                                <div class="font-bold text-slate-700"><?= esc($c['jabatan_fungsional'] ?: '-') ?></div>
                                                <div class="text-[9px] text-slate-400 font-bold"><?= esc($c['pangkat_golongan'] ?: '-') ?></div>
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <?= ($c['is_aktif']==1) ? '<span class="px-2 py-1 rounded text-[9px] font-black bg-emerald-500 text-white uppercase shadow-sm">Aktif</span>' : '<span class="text-[9px] font-bold text-slate-300 uppercase">Arsip</span>' ?>
                                            </td>
                                            <td class="px-4 py-4 text-right">
                                                <button class="w-7 h-7 rounded bg-slate-100 text-slate-400 hover:text-rose-600 transition-all"><i class="fas fa-trash-alt text-[10px]"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- STYLES -->
<style>
    .label { @apply block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5; }
    .label-req { @apply block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5 after:content-['*'] after:ml-0.5 after:text-rose-500; }
    .form-input { @apply w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all placeholder-slate-300 dark:bg-gray-800 dark:border-white/10 dark:text-white; }
    
    /* Custom Scrollbar for better UI */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

<!-- SCRIPTS -->
<script>
    // Data Opsi Jabatan per Kategori
    const optsGuru = ['Guru Mapel', 'Guru Kelas', 'Guru BK', 'Guru Pendamping', 'Kepala Sekolah'];
    const optsStaff = ['Tenaga Administrasi', 'Laboran', 'Pustakawan', 'Operator Dapodik', 'Bendahara Unit'];
    const optsPenunjang = ['Penjaga Sekolah', 'Keamanan (Satpam)', 'Petugas Kebersihan', 'Driver Yayasan'];
    
    const currentJenisPtk = "<?= $p['jenis_ptk'] ?? '' ?>";

    function toggleForm() {
        // MENGAMBIL NILAI RADIO BUTTON DENGAN QUERYSELECTOR (FIXED)
        const selectedRadio = document.querySelector('input[name="jenis_pegawai"]:checked');
        const jenis = selectedRadio ? selectedRadio.value : 'guru';
        
        const selectPtk = document.getElementById('jenis_ptk');
        const fieldNuptk = document.getElementById('field-nuptk');

        // Reset Options Jabatan
        selectPtk.innerHTML = '';
        let opts = [];
        
        if(jenis === 'guru') {
            opts = optsGuru;
            fieldNuptk.style.display = 'block';
        } else if(jenis === 'staff') {
            opts = optsStaff;
            fieldNuptk.style.display = 'none';
        } else {
            opts = optsPenunjang;
            fieldNuptk.style.display = 'none';
        }
        
        opts.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt;
            option.text = opt;
            if (opt === currentJenisPtk) option.selected = true;
            selectPtk.appendChild(option);
        });
    }

    // Inisialisasi saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function() {
        toggleForm();
    });
</script>
<?= $this->endSection() ?>