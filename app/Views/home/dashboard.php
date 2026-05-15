<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="w-full max-w-[1600px] mx-auto p-4 lg:p-6 space-y-4">
    
    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white leading-tight">Dashboard Overview</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                Tahun Ajaran Aktif: <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] font-bold"><?= esc($tahunAjaran) ?></span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                System Online
            </span>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        
        <!-- 1. SISWA -->
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-blue-600 group hover:shadow-md transition-all">
            <div class="relative z-10">
                <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Total Siswa</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"><?= number_format($siswaAktif) ?></h3>
                <p class="text-[10px] text-blue-600 font-bold mt-1">Jiwa</p>
            </div>
            <i class="fas fa-user-graduate absolute -bottom-2 -right-2 text-6xl text-gray-50 dark:text-gray-700/50 transform -rotate-12 group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- 2. GURU -->
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-emerald-500 group hover:shadow-md transition-all">
            <div class="relative z-10">
                <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Total Guru</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"><?= number_format($guruAktif) ?></h3>
                <p class="text-[10px] text-emerald-600 font-bold mt-1">Pengajar</p>
            </div>
            <i class="fas fa-chalkboard-teacher absolute -bottom-2 -right-2 text-6xl text-gray-50 dark:text-gray-700/50 transform -rotate-12 group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- 3. KARYAWAN -->
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-amber-500 group hover:shadow-md transition-all">
            <div class="relative z-10">
                <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Karyawan</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"><?= number_format($karyawanAktif) ?></h3>
                <p class="text-[10px] text-amber-600 font-bold mt-1">Staff</p>
            </div>
            <i class="fas fa-user-tie absolute -bottom-2 -right-2 text-6xl text-gray-50 dark:text-gray-700/50 transform -rotate-12 group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- 4. KELAS -->
        <div class="relative overflow-hidden bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border-l-4 border-indigo-500 group hover:shadow-md transition-all">
            <div class="relative z-10">
                <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Total Rombel</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"><?= number_format($totalKelas) ?></h3>
                <p class="text-[10px] text-indigo-600 font-bold mt-1">Ruang Kelas</p>
            </div>
            <i class="fas fa-school absolute -bottom-2 -right-2 text-6xl text-gray-50 dark:text-gray-700/50 transform -rotate-12 group-hover:scale-110 transition-transform"></i>
        </div>

        <!-- 5. KEUANGAN -->
        <div class="relative overflow-hidden bg-slate-800 rounded-xl p-4 shadow-sm group hover:shadow-md transition-all text-white">
            <div class="flex justify-between items-center relative z-10">
                <div>
                    <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Capaian SPP</p>
                    <h3 class="text-xl font-bold mt-1 text-emerald-400"><?= $capaianSpp ?>%</h3>
                    <p class="text-[10px] text-gray-400 mt-1">Rp <?= number_format($realisasiSpp, 0, ',', '.') ?></p>
                </div>
                <div class="relative w-12 h-12 flex items-center justify-center">
                    <svg class="transform -rotate-90 w-12 h-12">
                        <circle cx="24" cy="24" r="18" stroke="currentColor" stroke-width="4" fill="transparent" class="text-gray-600" />
                        <circle cx="24" cy="24" r="18" stroke="currentColor" stroke-width="4" fill="transparent" stroke-dasharray="113" stroke-dashoffset="<?= 113 - (113 * $capaianSpp / 100) ?>" class="text-emerald-500 transition-all duration-1000" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">
        
        <!-- KOLOM KIRI (AGENDA LIST) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="font-bold text-gray-700 dark:text-gray-200 text-sm mb-4">
                    <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>Agenda Terdekat
                </h3>
                
                <?php if (empty($calendarEvents)) : ?>
                    <div class="text-center py-8 text-gray-400 text-xs">
                        Tidak ada agenda dalam waktu dekat.
                    </div>
                <?php else : ?>
                    <div class="space-y-3">
                        <?php foreach (array_slice($calendarEvents, 0, 3) as $event) : ?>
                            <div class="flex items-center gap-3 border-b border-gray-50 pb-2 last:border-0">
                                <div class="bg-blue-50 text-blue-600 w-10 h-10 rounded-lg flex flex-col items-center justify-center text-[10px] font-bold">
                                    <span><?= date('d', strtotime($event['start_date'])) ?></span>
                                    <span class="text-[8px] uppercase"><?= date('M', strtotime($event['start_date'])) ?></span>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800 dark:text-white"><?= esc($event['title']) ?></p>
                                    <p class="text-[10px] text-gray-500">Tipe: <?= esc($event['type'] ?? 'Umum') ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KOLOM KANAN (INFO KEUANGAN & CALENDAR WIDGET) -->
        <div class="space-y-4">
             <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <h3 class="font-bold text-gray-700 dark:text-gray-200 text-sm mb-4">Info Keuangan</h3>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Total Pemasukan Bulan Ini</p>
                    <h2 class="text-2xl font-black text-emerald-600 mt-1">Rp <?= number_format($realisasiSpp, 0, ',', '.') ?></h2>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                 <div id="calendar" class="text-xs"></div>
            </div>
        </div>

    </div>
</div>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. Grafik Siswa ---
        // PENTING: Jika error masih muncul, artinya browser/server masih membaca file LAMA.
        // Baris ini MENGGUNAKAN json_encode() untuk memperbaiki error "Array to string conversion"
        const rawData = <?= json_encode($dataGrafikTingkat) ?>; 

        if(rawData && rawData.length > 0) {
            // Logika chart bisa dimasukkan di sini
        }

        // --- 2. Kalender (FullCalendar) ---
        const calendarEl = document.getElementById('calendar');
        
        // PENTING: Baris ini MENGGUNAKAN json_encode()
        const calendarData = <?= json_encode($calendarEvents) ?>; 
        
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'title',
                    right: 'prev,next'
                },
                height: 300,
                events: calendarData.map(event => ({
                    title: event.title,
                    start: event.start_date,
                    end: event.end_date,
                    color: event.color ?? '#3b82f6'
                }))
            });
            calendar.render();
        }
    });
</script>

<?= $this->endSection() ?>