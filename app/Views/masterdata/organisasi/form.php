<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<?php
// Normalisasi Data: Pastikan $organisasi adalah array
$organisasi = (array) ($organisasi ?? []);
?>

<div class="container-fluid mb-6 px-4">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight"><?= esc($title) ?></h1>
            <p class="text-xs font-bold text-slate-400 dark:text-slate-500">Kelola data struktural dan penempatan personel.</p>
        </div>
        <a href="<?= base_url('app/masterdata/organisasi') ?>" class="px-4 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-white/10 rounded-xl text-slate-600 dark:text-slate-300 font-bold text-xs uppercase tracking-wider hover:bg-slate-50 transition-all no-underline shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-900 rounded-[1.5rem] border border-slate-100 dark:border-white/5 shadow-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-50 dark:border-white/5 bg-slate-50/50 dark:bg-white/5">
            <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-edit"></i> Formulir Data Organisasi
            </h3>
        </div>

        <form action="<?= base_url('app/masterdata/organisasi/save') ?>" method="post" class="p-8">
            <?= csrf_field() ?>
            <!-- Hidden ID untuk Edit Mode -->
            <?php if (!empty($organisasi['id'])): ?>
                <input type="hidden" name="id" value="<?= $organisasi['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                
                <!-- KOLOM KIRI: STRUKTUR -->
                <div class="space-y-6">
                    <div class="pb-2 border-b border-slate-100 dark:border-white/5 mb-4">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">1. Posisi & Jabatan</h4>
                    </div>

                    <!-- Jenis Organisasi -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Kategori Organisasi <span class="text-rose-500">*</span></label>
                        <select name="jenis_organisasi" class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold text-slate-700 dark:text-white transition-all" required>
                            <?php 
                                $opts = ['Sekolah', 'Pendiri', 'Pembina', 'Pengurus', 'Pengawas'];
                                $val = $organisasi['jenis_organisasi'] ?? 'Sekolah';
                            ?>
                            <?php foreach($opts as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($val == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Unit / Jenjang -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Unit / Jenjang <span class="text-rose-500">*</span></label>
                        <select name="kode_jenjang" class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold text-slate-700 dark:text-white transition-all" required>
                            <?php $curJenjang = $organisasi['kode_jenjang'] ?? ''; ?>
                            <?php foreach ($list_jenjang as $lj) : ?>
                                <!-- FIX: Menggunakan Array Syntax $lj['key'] karena Controller sudah mengirim Array -->
                                <option value="<?= $lj['kode_jenjang'] ?>" <?= ($curJenjang == $lj['kode_jenjang']) ? 'selected' : '' ?>>
                                    <?= ($lj['kode_jenjang'] == 'Global') ? '🏛️ GLOBAL / YAYASAN' : '🏫 UNIT ' . strtoupper($lj['kode_jenjang']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jabatan (Master) -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Pilih Jabatan Baku (Opsional)</label>
                        <select name="jabatan_id" id="jabatan_id" class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold text-slate-700 dark:text-white transition-all">
                            <option value="">-- Tidak menggunakan Master Jabatan --</option>
                            <?php $curJab = $organisasi['jabatan_id'] ?? ''; ?>
                            <?php foreach ($list_jabatan as $jab) : ?>
                                <!-- FIX: Menggunakan Array Syntax $jab['key'] -->
                                <option value="<?= $jab['id'] ?>" <?= ($curJab == $jab['id']) ? 'selected' : '' ?>>
                                    [<?= $jab['kode_jenjang'] ?>] <?= $jab['nama_jabatan'] ?> (Level <?= $jab['level'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Nama Jabatan Custom -->
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Nama Jabatan (Custom) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_jabatan" value="<?= $organisasi['nama_jabatan'] ?? '' ?>" 
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold text-slate-700 dark:text-white transition-all"
                               placeholder="Contoh: Kepala Tata Usaha, Staff IT">
                        <p class="text-[10px] text-slate-400">Isi ini jika tidak memilih jabatan baku di atas.</p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Urutan -->
                        <div class="space-y-2">
                            <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">No. Urut</label>
                            <input type="number" name="urutan" value="<?= $organisasi['urutan'] ?? '0' ?>" 
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold text-slate-700 dark:text-white transition-all">
                        </div>
                        
                        <!-- Status -->
                        <div class="space-y-2">
                            <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Status</label>
                            <select name="status" class="w-full px-4 py-3 bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold text-slate-700 dark:text-white transition-all">
                                <option value="aktif" <?= (isset($organisasi['status']) && $organisasi['status'] == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= (isset($organisasi['status']) && $organisasi['status'] == 'nonaktif') ? 'selected' : '' ?>>Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: PERSONEL -->
                <div class="space-y-6">
                    <div class="pb-2 border-b border-slate-100 dark:border-white/5 mb-4">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">2. Personel Pengampu</h4>
                    </div>

                    <!-- TABS PILIHAN SUMBER -->
                    <div class="bg-slate-100 dark:bg-gray-800 p-1 rounded-xl flex mb-4">
                        <button type="button" onclick="switchSource('pegawai')" id="tab-pegawai" class="flex-1 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all bg-white dark:bg-gray-700 text-indigo-600 shadow-sm">
                            <i class="fas fa-id-badge mr-1"></i> Internal (Pegawai)
                        </button>
                        <button type="button" onclick="switchSource('manual')" id="tab-manual" class="flex-1 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all text-slate-400 hover:text-slate-600">
                            <i class="fas fa-user-edit mr-1"></i> Eksternal / Manual
                        </button>
                    </div>
                    
                    <input type="hidden" name="source_personel" id="source_personel" value="<?= (!empty($organisasi['id_pegawai'])) ? 'pegawai' : 'manual' ?>">

                    <!-- SEKSI PEGAWAI (GURU/KARYAWAN) -->
                    <div id="section-pegawai" class="<?= (!empty($organisasi['id_pegawai']) || empty($organisasi)) ? '' : 'hidden' ?>">
                        <div class="bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 rounded-2xl p-6">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-[11px] font-black text-indigo-800 dark:text-indigo-300 uppercase tracking-widest">Pilih Guru</label>
                                    <select name="guru_id" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-indigo-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold transition-all">
                                        <option value="">-- Pilih Guru --</option>
                                        <?php 
                                            // Asumsi id_pegawai di model merujuk ke tabel pegawai
                                            $curPegawai = $organisasi['id_pegawai'] ?? ''; 
                                        ?>
                                        <?php foreach ($list_guru as $g) : ?>
                                            <!-- FIX: Menggunakan Array Syntax $g['key'] -->
                                            <option value="<?= $g['id'] ?>" <?= ($curPegawai == $g['id']) ? 'selected' : '' ?>>
                                                <?= $g['nama_lengkap'] ?> (<?= $g['nip'] ?? $g['nik'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">- ATAU -</div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] font-black text-indigo-800 dark:text-indigo-300 uppercase tracking-widest">Pilih Karyawan/Staff</label>
                                    <select name="karyawan_id" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-indigo-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold transition-all">
                                        <option value="">-- Pilih Karyawan --</option>
                                        <?php foreach ($list_karyawan as $k) : ?>
                                            <!-- FIX: Menggunakan Array Syntax $k['key'] -->
                                            <option value="<?= $k['id'] ?>" <?= ($curPegawai == $k['id']) ? 'selected' : '' ?>>
                                                <?= $k['nama_lengkap'] ?> (<?= $k['nip'] ?? $k['nik'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEKSI MANUAL -->
                    <div id="section-manual" class="<?= (!empty($organisasi['id_pegawai']) || empty($organisasi)) ? 'hidden' : '' ?>">
                        <div class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-white/10 rounded-2xl p-6">
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Nama Lengkap & Gelar</label>
                                    <input type="text" name="nama_pengampu" value="<?= $organisasi['nama_pengampu'] ?? '' ?>" 
                                           class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold transition-all"
                                           placeholder="Contoh: Dr. H. Fulan bin Fulan, M.Pd">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">NIP / Identitas Lain</label>
                                    <input type="text" name="nip" value="<?= $organisasi['nip'] ?? '' ?>" 
                                           class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-slate-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 outline-none text-xs font-bold transition-all"
                                           placeholder="Opsional">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-white/5">
                <a href="<?= base_url('app/masterdata/organisasi') ?>" class="px-6 py-3 rounded-xl bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-slate-400 text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all no-underline">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-indigo-600 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:scale-[1.02] transition-all">
                    <i class="fas fa-save mr-2"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    function switchSource(type) {
        document.getElementById('source_personel').value = type;
        
        // Reset Tabs Style
        const tabPegawai = document.getElementById('tab-pegawai');
        const tabManual = document.getElementById('tab-manual');
        
        const activeClass = ['bg-white', 'dark:bg-gray-700', 'text-indigo-600', 'shadow-sm'];
        const inactiveClass = ['text-slate-400', 'hover:text-slate-600'];

        if (type === 'pegawai') {
            tabPegawai.classList.add(...activeClass);
            tabPegawai.classList.remove(...inactiveClass);
            tabManual.classList.remove(...activeClass);
            tabManual.classList.add(...inactiveClass);
            
            document.getElementById('section-pegawai').classList.remove('hidden');
            document.getElementById('section-manual').classList.add('hidden');
        } else {
            tabManual.classList.add(...activeClass);
            tabManual.classList.remove(...inactiveClass);
            tabPegawai.classList.remove(...activeClass);
            tabPegawai.classList.add(...inactiveClass);
            
            document.getElementById('section-manual').classList.remove('hidden');
            document.getElementById('section-pegawai').classList.add('hidden');
        }
    }

    // Auto-fill Nama Jabatan jika memilih Master Jabatan
    const jabatanSelect = document.getElementById('jabatan_id');
    const namaJabatanInput = document.querySelector('input[name="nama_jabatan"]');

    if (jabatanSelect) {
        jabatanSelect.addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex].text;
            if (this.value) {
                // Ambil teks jabatan saja (hapus [kode] dan level)
                // Format di option: [KODE] Nama Jabatan (Level X)
                let cleanName = selectedText.replace(/\[.*?\]\s/, '').replace(/\s\(Level \d+\)$/, '');
                namaJabatanInput.value = cleanName;
            }
        });
    }
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>