<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- CSS Khusus untuk Pattern Batik (Updated: Lebih Jelas & Solid) -->
<style>
    .pattern-batik {
        background-color: transparent;
        /* Motif Kawung Modern (SVG Base64) - Opacity ditingkatkan */
        background-image: url("data:image/svg+xml,%3Csvg width='52' height='52' viewBox='0 0 52 52' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.25'%3E%3Cpath d='M10 10c0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6h2c0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4v2c-2.21 0-4-1.79-4-4 0-3.314-2.686-6-6-6-2.21 0-4-1.79-4-4-3.314 0-6-2.686-6-6 0-2.21 1.79-4 4-4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        background-size: 40px 40px;
    }
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-200 dark:border-white/10 pb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Ruang Kelas (<?= esc($jenjang ?? 'Umum') ?>)</h1>
                
                <?php 
                    $session = session();
                    // LOGIKA CEK SESSION YANG LEBIH KUAT
                    $currentRole = $session->get('role') 
                                ?? $session->get('role_name') 
                                ?? $session->get('level') 
                                ?? $session->get('jabatan') 
                                ?? 'guest';
                    
                    // Debugging: Ambil semua data session untuk ditampilkan di tooltip
                    $allSessionData = $session->get();
                    $debugText = "Session Keys:\n";
                    foreach($allSessionData as $key => $val) {
                        if(is_string($val) || is_numeric($val)) {
                            $debugText .= "$key = $val\n";
                        } else {
                            $debugText .= "$key = [Array/Object]\n";
                        }
                    }
                ?>
                
                <!-- Badge Role dengan Tooltip Debug Lengkap -->
                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-gray-100 dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700 cursor-help relative group" title="Hover untuk lihat detail session">
                    <?= esc(strtoupper($currentRole)) ?>
                    
                    <!-- Custom Tooltip untuk melihat isi session -->
                    <div class="hidden group-hover:block absolute top-full left-0 mt-2 w-64 p-2 bg-black text-white text-xs rounded shadow-lg z-50 whitespace-pre-wrap">
                        <?= esc($debugText) ?>
                    </div>
                </span>
            </div>
            <p class="text-gray-500 dark:text-gray-400">Pusat pembelajaran digital dan manajemen kelas.</p>
        </div>
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            
            <?php 
                $role = strtolower(trim($currentRole));
                
                // Logika Pengecekan Role (Sangat Permisif untuk Debugging)
                $isAdmin = (strpos($role, 'admin') !== false) || 
                           ($role === 'superadmin') || 
                           ($role === 'yayasan') || 
                           (strpos($role, 'kepala') !== false) ||
                           (strpos($role, 'operator') !== false);
                           
                $isGuru = (strpos($role, 'guru') !== false) || 
                          (strpos($role, 'pengajar') !== false) ||
                          (strpos($role, 'wali') !== false);
            ?>

            <!-- Tombol Generate -->
            <?php if ($isAdmin || $role === 'guest'): ?>
            <a href="<?= base_url('app/elearning/generate') ?>" class="flex-1 sm:flex-none px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-all shadow-md text-center no-underline flex items-center justify-center gap-2">
                <i class="fas fa-magic"></i> Generate Kelas
            </a>
            <?php endif; ?>
            
            <!-- Tombol Buat Kelas Manual -->
            <?php if ($isAdmin || $isGuru || $role === 'guest'): ?>
            <a href="<?= base_url('app/elearning/create') ?>" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-bold hover:bg-primary-dark transition-all shadow-md text-center no-underline flex items-center justify-center gap-2">
                <i class="fas fa-plus"></i> Buat Kelas Baru
            </a>
            <?php endif; ?>

            <!-- Tombol Gabung Kelas -->
            <a href="<?= base_url('app/elearning/join') ?>" class="flex-1 sm:flex-none px-4 py-2 bg-indigo-600 text-white border border-indigo-600 rounded-lg text-sm font-bold hover:bg-indigo-700 transition-all shadow-md text-center no-underline flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i> Gabung
            </a>
        </div>
    </div>

    <!-- KPI Stats Cards -->
    <?php
        // Kalkulasi Data
        $totalKelas = count($courses ?? []);
        $uniqueMapel = [];
        $uniqueGuru = [];
        $statsPerUnit = []; 

        if(!empty($courses)) {
            foreach($courses as $c) {
                if(!empty($c['mata_pelajaran'])) $uniqueMapel[] = $c['mata_pelajaran'];
                if(!empty($c['nama_guru'])) $uniqueGuru[] = $c['nama_guru'];
                
                $u = strtoupper($c['kode_jenjang'] ?? 'LAINNYA');
                if(!isset($statsPerUnit[$u])) $statsPerUnit[$u] = 0;
                $statsPerUnit[$u]++;
            }
        }
        $totalMapel = count(array_unique($uniqueMapel));
        $totalGuru = count(array_unique($uniqueGuru));

        // Urutkan Unit
        $unitOrder = ['TK', 'SD', 'SMP', 'SMA', 'SMK'];
        uksort($statsPerUnit, function($a, $b) use ($unitOrder) {
            $posA = array_search($a, $unitOrder);
            $posB = array_search($b, $unitOrder);
            if ($posA === false) return 1;
            if ($posB === false) return -1;
            return $posA - $posB;
        });

        $showBreakdown = count($statsPerUnit) > 1;
    ?>
    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total Kelas -->
        <div class="bg-blue-600 p-5 rounded-xl shadow-lg hover:shadow-xl transition-all flex flex-col justify-center h-full text-white relative overflow-hidden group border border-blue-500">
            <!-- Batik Layer -->
            <div class="absolute inset-0 pattern-batik opacity-30 pointer-events-none"></div>
            <!-- Gradient Glow -->
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex items-center gap-4 relative z-10">
                <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white shrink-0 shadow-inner border border-white/10">
                    <i class="fas fa-chalkboard text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-blue-100 uppercase tracking-wider drop-shadow-sm">Total Kelas Aktif</p>
                    <h3 class="text-3xl font-bold text-white drop-shadow-md"><?= number_format($totalKelas) ?></h3>
                </div>
            </div>
            
            <?php if($showBreakdown): ?>
            <div class="mt-4 pt-3 border-t border-white/20 relative z-10">
                <div class="grid grid-cols-2 gap-2">
                    <?php foreach($statsPerUnit as $unit => $count): ?>
                    <div class="flex justify-between items-center bg-black/20 px-2 py-1 rounded text-[10px] hover:bg-black/30 transition-colors border border-white/10">
                        <span class="font-bold text-blue-50"><?= $unit ?></span>
                        <span class="font-bold text-white"><?= $count ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Card 2: Mata Pelajaran -->
        <div class="bg-emerald-600 p-5 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center gap-4 text-white relative overflow-hidden group border border-emerald-500">
            <div class="absolute inset-0 pattern-batik opacity-30 pointer-events-none"></div>
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white shrink-0 shadow-inner border border-white/10 relative z-10">
                <i class="fas fa-book text-xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider drop-shadow-sm">Variasi Mapel</p>
                <h3 class="text-3xl font-bold text-white drop-shadow-md"><?= number_format($totalMapel) ?></h3>
                <p class="text-[10px] text-emerald-100 mt-1 opacity-90 font-medium">Mata pelajaran terdaftar</p>
            </div>
        </div>

        <!-- Card 3: Pengajar -->
        <div class="bg-purple-600 p-5 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center gap-4 text-white relative overflow-hidden group border border-purple-500">
            <div class="absolute inset-0 pattern-batik opacity-30 pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-40 h-40 bg-white/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white shrink-0 shadow-inner border border-white/10 relative z-10">
                <i class="fas fa-chalkboard-teacher text-xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-bold text-purple-100 uppercase tracking-wider drop-shadow-sm">Pengajar Aktif</p>
                <h3 class="text-3xl font-bold text-white drop-shadow-md"><?= number_format($totalGuru) ?></h3>
                <p class="text-[10px] text-purple-100 mt-1 opacity-90 font-medium">Guru pengampu kelas</p>
            </div>
        </div>
    </div>

    <!-- Grid Kelas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php if (!empty($courses)): foreach ($courses as $course): ?>
        <div class="group bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col h-full relative">
            
            <?php if(strpos($jenjang ?? '', 'SEMUA') !== false): ?>
            <div class="absolute top-3 right-3 z-20">
                <span class="px-2 py-1 bg-black/50 backdrop-blur-sm text-white text-[10px] font-bold rounded uppercase shadow-sm">
                    <?= esc($course['kode_jenjang']) ?>
                </span>
            </div>
            <?php endif; ?>

            <div class="relative h-32 bg-gradient-to-br from-primary-dark to-primary p-5">
                <div class="relative z-10">
                    <a href="<?= base_url('app/elearning/view/' . $course['id']) ?>" class="block group-hover:underline decoration-white underline-offset-4">
                        <h3 class="text-lg font-bold text-white leading-tight mb-1 drop-shadow-md"><?= esc($course['nama_kelas']) ?></h3>
                    </a>
                    <p class="text-sky-100 text-xs font-medium opacity-90 drop-shadow-sm"><?= esc($course['mata_pelajaran'] ?? 'Umum') ?></p>
                </div>
                <div class="absolute -bottom-6 right-5 ring-4 ring-white dark:ring-gray-900 rounded-full bg-white overflow-hidden w-14 h-14 shadow-lg">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($course['nama_guru'] ?? 'G') ?>&background=random" alt="Avatar" class="w-full h-full object-cover">
                </div>
            </div>

            <div class="p-6 pt-10 flex-1">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-500/10 flex items-center justify-center text-primary text-xs">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest leading-none mb-1">Pengajar</p>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 truncate"><?= esc($course['nama_guru'] ?? 'Belum ditentukan') ?></p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50/50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-white/5 flex justify-end gap-4 text-gray-400">
                <a href="<?= base_url('app/elearning/classwork/' . $course['id']) ?>" title="Tugas" class="hover:text-primary transition-colors"><i class="fas fa-clipboard-list text-sm"></i></a>
                <a href="<?= base_url('app/elearning/people/' . $course['id']) ?>" title="Anggota" class="hover:text-primary transition-colors"><i class="fas fa-users text-sm"></i></a>
                <a href="<?= base_url('app/elearning/view/' . $course['id']) ?>" title="Forum" class="hover:text-primary transition-colors"><i class="fas fa-external-link-alt text-sm"></i></a>
            </div>
        </div>
        <?php endforeach; else: ?>
            <div class="col-span-full py-24 flex flex-col items-center justify-center text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-6 text-gray-300">
                    <i class="fas fa-folder-open text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Belum Ada Kelas</h3>
                <p class="text-gray-500 max-w-md mt-2 text-sm">
                    <?php if (strpos($jenjang ?? '', 'SEMUA') !== false): ?>
                        Belum ada kelas di seluruh sistem database. Silakan klik tombol "Buat Kelas Baru" atau "Generate Kelas".
                    <?php else: ?>
                        Tidak ada kelas aktif untuk jenjang <strong><?= esc($jenjang ?? 'ini') ?></strong>.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>