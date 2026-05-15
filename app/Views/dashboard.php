<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load Tailwind CSS (CDN - Pastikan internet aktif atau download lokal) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a', // Slate 900
                    secondary: '#334155', // Slate 700
                    accent: '#3b82f6', // Blue 500
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                }
            }
        }
    }
</script>

<!-- Load Library: Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Load Library: FullCalendar -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

<div class="min-h-screen bg-slate-50 p-4 sm:p-6 font-sans text-slate-800">

    <!-- HEADER: Compact & Solid -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fas fa-th-large text-blue-600"></i> Dashboard Overview
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Tahun Ajaran: <span class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded text-[10px] font-bold tracking-wide"><?= esc($tahunAjaranAktif ?? 'N/A') ?></span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-md shadow-sm">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">System Online</span>
            </div>
        </div>
    </div>

    <!-- STATS CARDS: Grid Rapat (Compact) & Solid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        
        <!-- Card 1: Siswa -->
        <div class="bg-white rounded-lg p-3 border-l-[3px] border-blue-600 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Siswa Aktif</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <h3 class="text-2xl font-black text-slate-800"><?= number_format($siswaAktif ?? 0) ?></h3>
                        <span class="text-[10px] font-medium text-blue-600 bg-blue-50 px-1 rounded">Jiwa</span>
                    </div>
                </div>
                <div class="bg-blue-50 p-2 rounded-md">
                    <i class="fas fa-users text-blue-600 text-sm"></i>
                </div>
            </div>
            <!-- Decorative Icon -->
            <i class="fas fa-users absolute -bottom-2 -right-2 text-5xl text-slate-50 transform -rotate-12 group-hover:scale-110 transition-transform duration-300"></i>
        </div>

        <!-- Card 2: Guru -->
        <div class="bg-white rounded-lg p-3 border-l-[3px] border-emerald-500 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Guru</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <h3 class="text-2xl font-black text-slate-800"><?= number_format($guruAktif ?? 0) ?></h3>
                        <span class="text-[10px] font-medium text-emerald-600 bg-emerald-50 px-1 rounded">Orang</span>
                    </div>
                </div>
                <div class="bg-emerald-50 p-2 rounded-md">
                    <i class="fas fa-chalkboard-teacher text-emerald-600 text-sm"></i>
                </div>
            </div>
            <i class="fas fa-chalkboard-teacher absolute -bottom-2 -right-2 text-5xl text-slate-50 transform -rotate-12 group-hover:scale-110 transition-transform duration-300"></i>
        </div>

        <!-- Card 3: Karyawan -->
        <div class="bg-white rounded-lg p-3 border-l-[3px] border-amber-500 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Karyawan</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <h3 class="text-2xl font-black text-slate-800"><?= number_format($karyawanAktif ?? 0) ?></h3>
                        <span class="text-[10px] font-medium text-amber-600 bg-amber-50 px-1 rounded">Staff</span>
                    </div>
                </div>
                <div class="bg-amber-50 p-2 rounded-md">
                    <i class="fas fa-user-tie text-amber-600 text-sm"></i>
                </div>
            </div>
            <i class="fas fa-user-tie absolute -bottom-2 -right-2 text-5xl text-slate-50 transform -rotate-12 group-hover:scale-110 transition-transform duration-300"></i>
        </div>

        <!-- Card 4: Keuangan (Solid Dark) -->
        <div class="bg-slate-800 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group text-white">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Capaian SPP</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <h3 class="text-2xl font-black text-white"><?= esc($capaianSpp ?? 0) ?>%</h3>
                        <span class="text-[9px] text-emerald-300"><?= date('M Y') ?></span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1 font-mono">Rp <?= number_format($realisasiSpp ?? 0, 0, ',', '.') ?></p>
                </div>
                <div class="relative w-10 h-10 flex items-center justify-center">
                    <svg class="transform -rotate-90 w-10 h-10">
                        <circle cx="20" cy="20" r="16" stroke="currentColor" stroke-width="3" fill="transparent" class="text-slate-600" />
                        <circle cx="20" cy="20" r="16" stroke="currentColor" stroke-width="3" fill="transparent" stroke-dasharray="100" stroke-dashoffset="<?= 100 - (100 * ($capaianSpp ?? 0) / 100) ?>" class="text-emerald-400" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT: Split 2 Kolom (Compact Layout) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 h-full">
        
        <!-- KOLOM KIRI (1/3): Grafik Donut -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-sm flex flex-col">
            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-lg">
                <h6 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-chart-pie text-indigo-500"></i> Sebaran Siswa
                </h6>
                <button class="text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-ellipsis-v text-xs"></i></button>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-center items-center relative" style="min-height: 250px;">
                <canvas id="siswaPieChart"></canvas>
            </div>
            <!-- Legend Manual (Optional for cleaner look) -->
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/30 rounded-b-lg">
                <div class="flex justify-center gap-3 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-[10px] text-slate-500 font-medium">Kelas X</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] text-slate-500 font-medium">Kelas XI</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-[10px] text-slate-500 font-medium">Kelas XII</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN (2/3): Kalender -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-sm flex flex-col">
            <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-lg">
                <h6 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-blue-500"></i> Kalender Akademik
                </h6>
                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded font-medium border border-slate-200">
                    Agenda Sekolah
                </span>
            </div>
            <div class="p-4 flex-1">
                <!-- FullCalendar Container with custom CSS overriding for compact look -->
                <style>
                    .fc .fc-toolbar-title { font-size: 0.9rem; font-weight: 700; color: #334155; }
                    .fc .fc-button { font-size: 0.7rem; padding: 0.2rem 0.5rem; }
                    .fc .fc-col-header-cell-cushion { font-size: 0.75rem; text-transform: uppercase; font-weight: 600; color: #64748b; padding-top: 8px; padding-bottom: 8px; }
                    .fc .fc-daygrid-day-number { font-size: 0.75rem; color: #475569; font-weight: 500; }
                    .fc-event { font-size: 0.7rem; border: none; border-radius: 2px; padding: 1px 2px; }
                    .fc-theme-standard td, .fc-theme-standard th { border-color: #f1f5f9; }
                </style>
                <div id='calendar' class="text-xs"></div>
            </div>
        </div>
        
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. Grafik Siswa (Data Internal) ---
        const rawData = <?= json_encode($dataGrafikTingkat) ?>;

        if (rawData && rawData.length > 0) {
            const labels = rawData.map(item => 'Kelas ' + item.tingkat);
            const dataCounts = rawData.map(item => parseInt(item.total));

            // Warna Solid Profesional (Tailwind Palette adaptation)
            const backgroundColors = [
                '#3b82f6', // Blue 500
                '#10b981', // Emerald 500
                '#f59e0b', // Amber 500
                '#6366f1', // Indigo 500
                '#ef4444', // Red 500
                '#64748b'  // Slate 500
            ];
            
            const ctx = document.getElementById("siswaPieChart");
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: dataCounts,
                            backgroundColor: backgroundColors.slice(0, dataCounts.length),
                            borderWidth: 0, // No border for cleaner look
                            hoverOffset: 4
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false // Kita pakai custom legend HTML
                            },
                            tooltip: {
                                backgroundColor: '#1e293b',
                                padding: 10,
                                cornerRadius: 4,
                                bodyFont: { size: 12 },
                                displayColors: true
                            }
                        },
                        cutout: '75%', // Lebih tipis/modern
                    },
                });
            }
        }
        
        // --- 2. Kalender Akademik (Data Internal) ---
        const calendarEl = document.getElementById('calendar');
        const calendarData = <?= json_encode($calendarEvents) ?>; 
        
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'prev,next today'
                },
                buttonText: {
                    today: 'Hari Ini'
                },
                events: calendarData.map(event => ({
                    title: event.title,
                    start: event.start_date || event.start,
                    end: event.end_date || event.end,
                    backgroundColor: event.color || '#3b82f6',
                    borderColor: 'transparent',
                    allDay: true 
                })),
                height: 350, // Fixed height for compact look
                contentHeight: 'auto'
            });
            calendar.render();
        }
    });
</script>

<?= $this->endSection() ?>