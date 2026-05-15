<?= $this->extend('layout/main_layout') ?>

<?= $this->section('title') ?>
    <?= isset($karyawan['id']) ? 'Edit' : 'Tambah' ?> Karyawan
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    /**
     * Manajemen Karyawan - Form Create/Edit (Enterprise Edition)
     */
    $is_edit = isset($karyawan['id']);
    $karyawan_data = $karyawan ?? [];
    
    // Ambil error dari sesi (untuk validasi model)
    $errors = session()->getFlashdata('errors') ?? [];

    $sessionJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
    $isGlobal = in_array($sessionJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);
    $isRestricted = !$isGlobal;

    $url = $is_edit 
        ? base_url('app/masterdata/karyawan/update/' . $karyawan['id']) 
        : base_url('app/masterdata/karyawan/create'); 

    // Mapping Data (Old Input > DB Value > Default)
    $currentKJ       = old('kode_jenjang', $karyawan_data['kode_jenjang'] ?? ($isGlobal ? '' : $sessionJenjang));
    $currentNIP      = old('nip', $karyawan_data['nip'] ?? '');
    $currentNIK      = old('nik', $karyawan_data['nik'] ?? '');
    $currentNama     = old('nama_lengkap', $karyawan_data['nama_lengkap'] ?? '');
    $currentJK       = old('jenis_kelamin', $karyawan_data['jenis_kelamin'] ?? '');
    $currentTempat   = old('tempat_lahir', $karyawan_data['tempat_lahir'] ?? '');
    $currentTanggal  = old('tanggal_lahir', $karyawan_data['tanggal_lahir'] ?? '');
    $currentAgama    = old('agama', $karyawan_data['agama'] ?? 'Islam');
    $currentJabatan  = old('jabatan', $karyawan_data['jabatan'] ?? '');
    $currentEmail    = old('email', $karyawan_data['email'] ?? '');
    $currentTelepon  = old('telepon', $karyawan_data['telepon'] ?? '');
    $currentAlamat   = old('alamat', $karyawan_data['alamat'] ?? '');
    $currentStatus   = old('status', $karyawan_data['status'] ?? 'aktif');

    $displayUnitText = $isGlobal ? 'Seluruh Unit' : 'Unit ' . $sessionJenjang;
?>

<div class="container mx-auto px-4 py-6 mb-12 animate-fade-in">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <nav class="flex text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2">
                    <li><a href="<?= base_url('app/masterdata/karyawan') ?>" class="hover:text-blue-600 transition-colors">Karyawan</a></li>
                    <li><i class="fas fa-chevron-right text-[8px] mx-1"></i></li>
                    <li class="text-slate-600"><?= $is_edit ? 'Sunting Data' : 'Registrasi Baru' ?></li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-4">
                <span class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-xl shadow-blue-200">
                    <i class="fas <?= $is_edit ? 'fa-user-pen' : 'fa-user-plus' ?> text-2xl"></i>
                </span>
                <div>
                    <?= $is_edit ? "Ubah Profil Karyawan" : "Daftarkan Personel" ?>
                    <span class="block text-xs font-bold text-slate-500 mt-1 uppercase tracking-widest">
                        <i class="fas fa-building-circle-check text-blue-500 mr-1"></i> Otoritas: <?= $displayUnitText ?>
                    </span>
                </div>
            </h1>
        </div>
        <a href="<?= base_url('app/masterdata/karyawan') ?>" 
           class="inline-flex items-center text-xs font-black text-slate-500 hover:text-slate-800 transition-all bg-slate-100 hover:bg-slate-200 px-6 py-3 rounded-xl uppercase tracking-wider shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Alert Handler -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="mb-8 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl flex items-center shadow-sm animate-shake">
            <i class="fas fa-triangle-exclamation mr-3 text-xl opacity-50"></i> 
            <span class="text-sm font-bold"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= $url ?>" method="post" class="max-w-5xl mx-auto">
        <?= csrf_field() ?>
        <?php if ($is_edit) : ?>
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="<?= esc($karyawan['id']) ?>">
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Kolom Kiri: Informasi Penempatan & Biodata Utama -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Section 1: Penempatan Kerja (Scope Unit) -->
                <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-50 bg-slate-50/40 flex items-center justify-between">
                        <h2 class="text-[11px] font-black uppercase tracking-widest text-slate-500">1. Informasi Penempatan</h2>
                        <i class="fas fa-university text-slate-300"></i>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Unit Jenjang Penugasan <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="kode_jenjang" required <?= $isRestricted ? 'disabled' : '' ?>
                                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none appearance-none <?= isset($errors['kode_jenjang']) ? 'border-rose-400' : '' ?>">
                                        <?php if ($isGlobal): ?><option value="">Pilih Unit Sekolah...</option><?php endif; ?>
                                        <?php foreach ($jenjang_list as $j) : 
                                            $j_kode = is_object($j) ? $j->kode_jenjang : $j['kode_jenjang'];
                                            $j_nama = is_object($j) ? $j->nama_jenjang : $j['nama_jenjang'];
                                        ?>
                                            <option value="<?= $j_kode ?>" <?= $currentKJ == $j_kode ? 'selected' : '' ?>>
                                                UNIT <?= strtoupper($j_kode) ?> - <?= esc($j_nama) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-xs"></i>
                                </div>
                                <?php if($isRestricted): ?>
                                    <input type="hidden" name="kode_jenjang" value="<?= $sessionJenjang ?>">
                                    <p class="text-[9px] text-amber-600 font-bold italic ml-1">Unit terkunci otomatis sesuai otoritas login Anda.</p>
                                <?php endif; ?>
                                <?php if(isset($errors['kode_jenjang'])): ?>
                                    <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1"><?= $errors['kode_jenjang'] ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Status Kepegawaian <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none appearance-none">
                                        <option value="aktif" <?= $currentStatus == 'aktif' ? 'selected' : '' ?>>AKTIF (BEKERJA)</option>
                                        <option value="tidak aktif" <?= $currentStatus == 'tidak aktif' ? 'selected' : '' ?>>NON-AKTIF / RESIGN</option>
                                        <option value="cuti" <?= $currentStatus == 'cuti' ? 'selected' : '' ?>>CUTI PANJANG</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Identitas & Biodata -->
                <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-8 py-5 border-b border-slate-50 bg-slate-50/40 flex items-center justify-between">
                        <h2 class="text-[11px] font-black uppercase tracking-widest text-slate-500">2. Identitas Personal</h2>
                        <i class="fas fa-id-card-clip text-slate-300"></i>
                    </div>
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nomor Induk Pegawai (NIP)</label>
                                <input type="text" name="nip" value="<?= esc($currentNIP) ?>" placeholder="Contoh: 19881201..." class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none <?= isset($errors['nip']) ? 'border-rose-400' : '' ?>">
                                <?php if(isset($errors['nip'])): ?>
                                    <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1"><?= $errors['nip'] ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">NIK KTP (16 Digit) <span class="text-rose-500">*</span></label>
                                <input type="text" name="nik" value="<?= esc($currentNIK) ?>" maxlength="16" placeholder="Wajib sesuai KTP" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none <?= isset($errors['nik']) ? 'border-rose-400' : '' ?>" required>
                                <?php if(isset($errors['nik'])): ?>
                                    <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1"><?= $errors['nik'] ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama Lengkap Sesuai Dokumen <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_lengkap" value="<?= esc($currentNama) ?>" placeholder="Tuliskan nama lengkap tanpa gelar di kolom ini" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-4 text-slate-900 font-black text-xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none shadow-sm" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-6 pt-2">
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" name="jenis_kelamin" value="L" <?= $currentJK == 'L' ? 'checked' : '' ?> class="w-5 h-5 text-blue-600 border-slate-300 focus:ring-blue-500" required>
                                        <span class="text-xs font-bold text-slate-600 group-hover:text-blue-600 transition-colors">Laki-laki</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio" name="jenis_kelamin" value="P" <?= $currentJK == 'P' ? 'checked' : '' ?> class="w-5 h-5 text-blue-600 border-slate-300 focus:ring-blue-500" required>
                                        <span class="text-xs font-bold text-slate-600 group-hover:text-blue-600 transition-colors">Perempuan</span>
                                    </label>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" value="<?= esc($currentTempat) ?>" placeholder="Kota/Kab" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="<?= esc($currentTanggal) ?>" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none appearance-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Agama</label>
                                <div class="relative">
                                    <select name="agama" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none appearance-none">
                                        <?php foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'] as $ag): ?>
                                            <option value="<?= $ag ?>" <?= $currentAgama == $ag ? 'selected' : '' ?>><?= $ag ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-xs"></i>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Jabatan Operasional <span class="text-rose-500">*</span></label>
                                <input type="text" name="jabatan" value="<?= esc($currentJabatan) ?>" placeholder="Contoh: Staff Keuangan, Satpam, IT Support" class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none" required>
                                <?php if(isset($errors['jabatan'])): ?>
                                    <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1"><?= $errors['jabatan'] ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Kolom Kanan: Kontak, Alamat, & Actions -->
            <div class="lg:col-span-1 space-y-8">
                
                <!-- Section 3: Kontak & Komunikasi -->
                <section class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/40 flex items-center justify-between">
                        <h2 class="text-[10px] font-black uppercase tracking-widest text-slate-500">3. Kontak Personel</h2>
                        <i class="fas fa-headset text-slate-300"></i>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nomor WhatsApp / HP</label>
                            <div class="relative group">
                                <span class="absolute left-4 top-3.5 text-slate-300 font-black text-sm transition-colors group-focus-within:text-emerald-500">+62</span>
                                <input type="text" name="telepon" value="<?= esc($currentTelepon) ?>" placeholder="812xxx" class="w-full bg-white border border-slate-200 rounded-xl pl-14 pr-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none shadow-sm">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Korespondensi</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                                <input type="email" name="email" value="<?= esc($currentEmail) ?>" placeholder="personel@email.com" class="w-full bg-white border border-slate-200 rounded-xl pl-10 pr-4 py-3.5 text-slate-800 font-bold focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none shadow-sm">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 4: Domisili -->
                <section class="bg-slate-900 rounded-3xl shadow-2xl shadow-slate-200 overflow-hidden text-white group">
                    <div class="px-6 py-4 border-b border-white/5 bg-white/5 flex items-center justify-between">
                        <h2 class="text-[10px] font-black uppercase tracking-widest text-slate-400">4. Alamat Domisili</h2>
                        <i class="fas fa-map-location-dot text-slate-600 transition-colors group-hover:text-blue-500"></i>
                    </div>
                    <div class="p-6">
                        <textarea name="alamat" rows="4" placeholder="Tuliskan alamat lengkap sesuai tempat tinggal saat ini untuk keperluan administratif..." class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 text-white placeholder-slate-500 focus:ring-4 focus:ring-white/5 focus:border-white/20 transition-all outline-none resize-none text-xs leading-relaxed font-medium"><?= esc($currentAlamat) ?></textarea>
                    </div>
                </section>

                <!-- Action Panel -->
                <div class="flex flex-col gap-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-2xl shadow-2xl shadow-blue-500/30 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3 uppercase tracking-wider text-sm">
                        <i class="fas fa-save text-lg"></i> <?= $is_edit ? 'Perbarui Profil' : 'Simpan Personel' ?>
                    </button>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button type="reset" class="py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                            Reset Form
                        </button>
                        <a href="<?= base_url('app/masterdata/karyawan') ?>" class="py-3 text-[10px] font-black text-rose-500 text-center uppercase tracking-widest border border-rose-100 rounded-xl hover:bg-rose-50 transition-colors">
                            Batal
                        </a>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl">
                    <div class="flex gap-3">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                        <p class="text-[10px] text-amber-800 font-bold leading-relaxed">
                            Pastikan data <strong>NIK</strong> dan <strong>Unit Afiliasi</strong> sudah diverifikasi. Perubahan Unit setelah penyimpanan mungkin akan mempengaruhi alur pelaporan di Dashboard.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Styling for Date Inputs */
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0.5);
        cursor: pointer;
    }
    
    @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); } 20%, 40%, 60%, 80% { transform: translateX(4px); } }
    
    .animate-fade-in { animation: fade-in 0.5s ease-out forwards; }
    .animate-shake { animation: shake 0.6s cubic-bezier(.36,.07,.19,.97) both; }
</style>

<?= $this->endSection() ?>