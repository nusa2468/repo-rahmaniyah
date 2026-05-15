<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
// Otorisasi & Scope Unit
$session = session();
$userRole = $session->get('role'); 
$userUnit = $session->get('kode_jenjang'); 

$initialUnitCode = ($userRole !== 'superadmin') ? $userUnit : "";
$initialUnitName = "Global / Yayasan";

if ($userRole !== 'superadmin') {
    foreach ($jenjang as $j) {
        if ($j['kode_jenjang'] == $userUnit) {
            $initialUnitName = $j['nama_jenjang'];
            break;
        }
    }
}
?>

<div class="w-full max-w-4xl mx-auto" x-data='{ 
    calcMode: "fixed", 
    basisHitung: "siswa",
    idKategori: "", 
    jmlSiswa: 0, 
    jmlGuru: 0,
    jmlKaryawan: 0,
    nominalSatuan: 0,
    siklus: 12,
    total: 0,
    selectedCompName: "",
    kodeJenjang: "<?= $initialUnitCode ?>",
    namaJenjang: "<?= $initialUnitName ?>",
    komponenList: <?= json_encode($komponen_gaji) ?>,

    updateByComponent(id) {
        const comp = this.komponenList.find(c => c.id == id);
        if (comp) {
            this.selectedCompName = comp.nama_komponen;
            this.calcMode = (comp.metode_hitung.toLowerCase() === "variabel") ? "per_siswa" : "fixed";
            
            if (comp.id_kategori) {
                this.idKategori = comp.id_kategori;
            }

            if (this.calcMode === "per_siswa") {
                const name = comp.nama_komponen.toLowerCase();
                // Deteksi otomatis basis hitung
                if (name.includes("guru") && name.includes("karyawan")) this.basisHitung = "sdm";
                else if (name.includes("guru") || name.includes("pengajar")) this.basisHitung = "guru";
                else if (name.includes("karyawan") || name.includes("staf")) this.basisHitung = "karyawan";
                else this.basisHitung = "siswa";
            }

            let rawNominal = parseInt(comp.nominal_default) || 0;
            this.nominalSatuan = rawNominal.toLocaleString("id-ID");
            this.calculate();
        }
    },

    calculate() {
        let cleanNominal = String(this.nominalSatuan).replace(/\./g, "").replace(/[^0-9]/g, "");
        const nominal = parseInt(cleanNominal) || 0;
        const multiplier = parseInt(this.siklus) || 1;
        
        if (this.calcMode === "per_siswa") {
            let count = 0;
            if (this.basisHitung === "siswa") count = parseInt(this.jmlSiswa) || 0;
            else if (this.basisHitung === "guru") count = parseInt(this.jmlGuru) || 0;
            else if (this.basisHitung === "karyawan") count = parseInt(this.jmlKaryawan) || 0;
            else if (this.basisHitung === "sdm") {
                // Rata-rata x (Guru + Karyawan)
                count = (parseInt(this.jmlGuru) || 0) + (parseInt(this.jmlKaryawan) || 0);
            }
            
            this.total = count * nominal * multiplier;
        } else {
            this.total = nominal * multiplier;
        }
    },

    formatRupiah(e) {
        let val = e.target.value.replace(/[^0-9]/g, "");
        this.nominalSatuan = val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        this.calculate();
    },

    updateUnit(e) {
        this.kodeJenjang = e.target.value;
        this.namaJenjang = e.target.options[e.target.selectedIndex].text;
    }
}'>
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4">
        <a href="<?= base_url('app/keuangan/budget') ?>" class="hover:text-primary transition-colors">Manajemen Anggaran</a>
        <i class="fas fa-chevron-right text-[8px]"></i>
        <span class="text-gray-800 dark:text-white uppercase tracking-tighter">Kalkulator Gaji & Beban</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-center justify-between mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tighter flex items-center uppercase text-amber-600">
                <i class="fas fa-calculator mr-3"></i> KALKULATOR BEBAN UNIT
            </h2>
            <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                Akses: <span class="text-amber-600"><?= ($userRole === 'superadmin') ? 'Superadmin' : 'Admin ' . $initialUnitName ?></span>
            </p>
        </div>
        <a href="<?= base_url('app/keuangan/budget') ?>" class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-200 transition-all">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-7 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl shadow-xl overflow-hidden">
            <form action="<?= base_url('app/keuangan/budget/target-beban/save') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="nama_komponen_hidden" :value="selectedCompName">
                <input type="hidden" name="basis_hitung" :value="calcMode === 'fixed' ? 'tetap' : basisHitung">
                
                <div class="p-8 space-y-6">
                    <!-- Unit & Tahun -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Unit Sekolah</label>
                            <select name="kode_jenjang" required @change="updateUnit($event)"
                                    <?= ($userRole !== 'superadmin') ? 'disabled' : '' ?>
                                    class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-4 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-amber-500 disabled:opacity-70">
                                <?php if ($userRole === 'superadmin'): ?>
                                    <option value="">Global / Yayasan</option>
                                <?php endif; ?>
                                <?php foreach($jenjang as $j): ?>
                                    <?php if ($userRole !== 'superadmin' && $j['kode_jenjang'] !== $userUnit) continue; ?>
                                    <option value="<?= $j['kode_jenjang'] ?>" <?= ($j['kode_jenjang'] == $userUnit) ? 'selected' : '' ?>>
                                        <?= $j['nama_jenjang'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($userRole !== 'superadmin'): ?>
                                <input type="hidden" name="kode_jenjang" value="<?= $userUnit ?>">
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tahun Ajaran</label>
                            <input type="text" name="tahun" required value="<?= date('Y') ?>/<?= date('Y')+1 ?>"
                                   class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-4 py-4 text-sm font-bold">
                        </div>
                    </div>

                    <!-- Pilih Komponen -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                            <i class="fas fa-user-tag mr-2 text-amber-500"></i> Komponen Biaya
                        </label>
                        <select @change="updateByComponent($event.target.value)" 
                                class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-4 py-4 text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-amber-500">
                            <option value="">-- Input Manual --</option>
                            <?php foreach($komponen_gaji as $kg): ?>
                                <?php 
                                    $isGlobal = empty($kg['kode_jenjang']);
                                    $isMatchUnit = ($kg['kode_jenjang'] == $userUnit);
                                    if ($userRole !== 'superadmin' && !$isGlobal && !$isMatchUnit) continue; 
                                ?>
                                <option value="<?= $kg['id'] ?>"><?= strtoupper($kg['nama_komponen']) ?> (<?= strtoupper($kg['metode_hitung']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Mapping ISAK 35 -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 flex items-center">
                            <i class="fas fa-tags mr-2 text-amber-500"></i> Akun Biaya (ISAK 35) <span class="text-red-500">*</span>
                        </label>
                        <select name="id_kategori" x-model="idKategori" required 
                                class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-4 py-4 text-sm font-bold focus:ring-2 focus:ring-amber-500">
                            <option value="" disabled selected>-- Pilih Akun Biaya --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">[<?= $cat['kode_kategori'] ?>] <?= strtoupper($cat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Mode Beban -->
                    <div class="flex gap-4 p-1 bg-gray-100 dark:bg-gray-800 rounded-2xl">
                        <button type="button" @click="calcMode = 'fixed'; calculate()" 
                                :class="calcMode === 'fixed' ? 'bg-amber-500 text-white shadow-md' : 'text-gray-400'"
                                class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase transition-all">
                            Beban Tetap
                        </button>
                        <button type="button" @click="calcMode = 'per_siswa'; calculate()" 
                                :class="calcMode === 'per_siswa' ? 'bg-amber-500 text-white shadow-md' : 'text-gray-400'"
                                class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase transition-all">
                            Beban Variabel
                        </button>
                    </div>

                    <!-- Input Data Aktif (Contextual) -->
                    <div class="space-y-6">
                        <!-- Kontrol Basis Variabel (Updated with SDM) -->
                        <div x-show="calcMode === 'per_siswa'" x-transition class="bg-amber-50 dark:bg-amber-900/10 p-4 rounded-2xl border border-amber-100 dark:border-amber-900/30">
                            <label class="block text-[9px] font-black text-amber-600 uppercase tracking-widest mb-3">Hitung Variabel Berdasarkan:</label>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="mode in ['siswa', 'guru', 'karyawan', 'sdm']">
                                    <button type="button" @click="basisHitung = mode; calculate()"
                                            :class="basisHitung === mode ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-400'"
                                            class="px-4 py-2 rounded-lg text-[9px] font-black uppercase tracking-tighter transition-all flex-1 border border-transparent shadow-sm"
                                            x-text="mode === 'sdm' ? 'Per SDM (Guru+Kar)' : 'Per ' + mode"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Grid Input SDM/Siswa Aktif -->
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Siswa Aktif</label>
                                <input type="number" name="jumlah_siswa" x-model="jmlSiswa" @input="calculate()"
                                       class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl px-4 py-3 text-sm font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Guru Aktif</label>
                                <input type="number" name="jumlah_guru" x-model="jmlGuru" @input="calculate()"
                                       class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl px-4 py-3 text-sm font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">Karyawan Aktif</label>
                                <input type="number" name="jumlah_karyawan" x-model="jmlKaryawan" @input="calculate()"
                                       class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-xl px-4 py-3 text-sm font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>

                        <!-- Input Nominal -->
                        <div class="grid grid-cols-12 gap-6 items-end">
                            <div class="col-span-8">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2" 
                                       x-text="calcMode === 'per_siswa' ? 'Nominal Rata-rata (Rp / ' + (basisHitung === 'sdm' ? 'SDM' : basisHitung) + ')' : 'Nominal Gaji/Biaya per Bulan (Rp)'"></label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-amber-600 font-black text-xs">Rp</span>
                                    <input type="text" x-model="nominalSatuan" @input="formatRupiah($event)" required
                                           class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl pl-10 pr-4 py-4 text-lg font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-amber-500">
                                </div>
                            </div>
                            <div class="col-span-4">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Siklus</label>
                                <select name="siklus" x-model="siklus" @change="calculate()" 
                                        class="w-full bg-gray-50 dark:bg-gray-800 border-none rounded-2xl px-4 py-4 text-sm font-bold">
                                    <option value="1">1 Bln</option>
                                    <option value="12">12 Bln</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-800/20 px-8 py-6">
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white rounded-2xl py-5 font-black text-[10px] uppercase tracking-widest shadow-xl shadow-amber-500/30 transition-all hover:scale-[1.01]">
                        <i class="fas fa-save mr-2"></i> Simpan Pagu Anggaran
                    </button>
                    <input type="hidden" name="nominal_final" :value="total">
                </div>
            </form>
        </div>

        <!-- Sidebar Hasil -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-gradient-to-br from-amber-500 to-orange-700 rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden group">
                <i class="fas fa-coins absolute -right-6 -bottom-6 text-9xl opacity-10 transform group-hover:scale-110 transition-transform duration-700"></i>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-[9px] font-black uppercase tracking-widest opacity-70">Scope Unit</p>
                            <p class="text-sm font-black uppercase" x-text="namaJenjang"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] font-black uppercase tracking-widest opacity-70">Tipe</p>
                            <p class="text-xs font-black uppercase" x-text="calcMode === 'fixed' ? 'Fixed' : 'Variable'"></p>
                        </div>
                    </div>
                    
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-2 border-b border-white/20 pb-1 w-fit" 
                       x-text="selectedCompName || 'Estimasi Pagu'"></p>
                    <h3 class="text-4xl font-black tracking-tighter mb-8" x-text="'Rp ' + total.toLocaleString('id-ID')">Rp 0</h3>
                    
                    <div x-show="!idKategori" class="mb-4 bg-red-600/30 p-3 rounded-xl border border-red-500/50 text-[10px] font-black uppercase animate-pulse flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Akun Biaya ISAK 35 Wajib Diisi!
                    </div>

                    <div class="space-y-3 bg-white/10 p-4 rounded-2xl backdrop-blur-sm border border-white/10">
                        <div class="flex justify-between text-[10px]" x-show="basisHitung === 'sdm'">
                            <span class="font-black text-amber-200 uppercase tracking-tighter">TOTAL SDM (GURU+KAR)</span>
                            <span class="font-black" x-text="(parseInt(jmlGuru)||0) + (parseInt(jmlKaryawan)||0)">0</span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span class="opacity-70 font-bold uppercase">Siswa Aktif</span>
                            <span class="font-black" x-text="jmlSiswa">0</span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span class="opacity-70 font-bold uppercase">Guru Aktif</span>
                            <span class="font-black" x-text="jmlGuru">0</span>
                        </div>
                        <div class="flex justify-between text-[10px]">
                            <span class="opacity-70 font-bold uppercase">Karyawan Aktif</span>
                            <span class="font-black" x-text="jmlKaryawan">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Perhitungan -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-[2rem] p-6 shadow-sm">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-amber-500 text-sm"></i> Detail Perhitungan
                </h4>
                <div class="space-y-3 text-[10px] font-bold text-gray-500 leading-relaxed">
                    <p x-show="basisHitung === 'sdm' && calcMode === 'per_siswa'">
                        Metode: <span class="text-amber-600 font-black">Rata-rata Nominal</span> dikalikan akumulasi total <span class="text-gray-800 dark:text-white">Guru & Karyawan Aktif</span>. Digunakan jika komponen biaya tidak seragam per individu.
                    </p>
                    <p x-show="calcMode === 'per_siswa' && basisHitung !== 'sdm'">
                        Metode: Beban variabel berdasarkan jumlah <span class="text-amber-600 font-black uppercase" x-text="basisHitung"></span> aktif.
                    </p>
                    <p x-show="calcMode === 'fixed'">
                        Metode: Beban tetap (Fixed Cost) berdasarkan nominal bulanan dikalikan siklus anggaran.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>