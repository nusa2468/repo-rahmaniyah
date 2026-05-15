<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<!-- Load FullCalendar CDN -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<style>
    /* Custom Style untuk FullCalendar agar serasi dengan Tailwind */
    .fc-header-toolbar { margin-bottom: 1.5rem !important; }
    .fc-button { background-color: #4f46e5 !important; border-color: #4f46e5 !important; font-weight: 700 !important; text-transform: uppercase !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; border-radius: 0.75rem !important; padding: 0.6rem 1rem !important; }
    .fc-button:hover { background-color: #4338ca !important; border-color: #4338ca !important; }
    .fc-button-active { background-color: #312e81 !important; border-color: #312e81 !important; }
    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 800 !important; color: #1e293b; font-family: 'Plus Jakarta Sans', sans-serif; }
    .fc-daygrid-day-number { color: #475569; font-weight: 600; font-size: 0.85rem; padding: 0.5rem !important; }
    .fc-col-header-cell-cushion { padding: 1rem 0 !important; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 800; color: #64748b; }
    .fc-event { border-radius: 0.5rem; border: none; padding: 2px 4px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: transform 0.1s; }
    .fc-event:hover { transform: scale(1.02); }
    .fc .fc-daygrid-day.fc-day-today { background-color: #f8fafc !important; }
</style>

<div class="px-4 py-6 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
        <div>
            <nav class="flex mb-3" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                    <li><a href="<?= base_url('app/akademik/dashboard') ?>" class="hover:text-indigo-600 transition-colors">AKADEMIK</a></li>
                    <li><i class="fas fa-chevron-right text-[7px] opacity-50 mx-2"></i></li>
                    <li class="text-slate-600">KALENDER PENDIDIKAN</li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black tracking-tighter text-slate-900 uppercase italic leading-none">
                Kalender <span class="text-indigo-600">Visual</span>
            </h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <!-- Tombol Kembali ke List Mode -->
            <a href="<?= base_url('app/akademik/kalender') ?>" 
               class="inline-flex items-center px-5 py-3 text-[10px] font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-indigo-400 hover:text-indigo-600 transition-all shadow-sm active:scale-95 rounded-xl">
                <i class="fas fa-list mr-2"></i> List Mode
            </a>
            <a href="<?= base_url('app/akademik/kalender/new') ?>" 
               class="inline-flex items-center px-6 py-3 text-[10px] font-black uppercase tracking-widest bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95 rounded-xl border-b-4 border-indigo-800">
                <i class="fas fa-plus mr-2"></i> Tambah Agenda
            </a>
        </div>
    </div>

    <!-- FITUR NAVIGASI TAB MODUL AKADEMIK -->
    <div class="flex items-center gap-2 p-1.5 bg-slate-100 dark:bg-slate-900 rounded-2xl w-fit overflow-x-auto no-scrollbar mb-8 border border-slate-200 dark:border-white/5 shadow-inner">
        <a href="<?= base_url('app/akademik/kalender') ?>" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all bg-white text-indigo-600 shadow-md">
            <i class="fas fa-calendar-day mr-2"></i> Kalender
        </a>
        <a href="<?= base_url('app/akademik/jadwalpelajaran') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-clock mr-2 opacity-50"></i> Jadwal
        </a>
        <a href="<?= base_url('app/akademik/absensi-siswa') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-user-check mr-2 opacity-50"></i> Presensi
        </a>
        <a href="<?= base_url('app/akademik/nilai') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-star mr-2 opacity-50"></i> Nilai
        </a>
        <a href="<?= base_url('app/akademik/rapor') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-file-contract mr-2 opacity-50"></i> E-Rapor
        </a>
        <a href="<?= base_url('app/akademik/kenaikan_kelas') ?>" class="px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-slate-500 hover:text-indigo-600 hover:bg-white/50">
            <i class="fas fa-rocket mr-2 opacity-50"></i> Kenaikan
        </a>
    </div>

    <!-- CALENDAR CARD -->
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="p-6 md:p-10">
            <div id='calendar'></div>
        </div>
        <div class="px-10 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            <span>* Klik tanggal untuk menambah agenda cepat</span>
            <span>SIAKAD v2.0 &bull; Modul Akademik</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            locale: 'id', // Bahasa Indonesia
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Daftar'
            },
            events: '<?= base_url("app/akademik/kalender/events") ?>', // Sumber Data JSON dari Controller
            editable: false,
            droppable: false,
            dayMaxEvents: true, // Allow "more" link when too many events
            eventClick: function(info) {
                // Aksi saat event diklik (misal: Edit)
                window.location.href = '<?= base_url("app/akademik/kalender/edit/") ?>' + info.event.id;
            },
            dateClick: function(info) {
                // Aksi saat tanggal kosong diklik (Tambah Agenda)
                // alert('Clicked on: ' + info.dateStr);
                // Bisa diarahkan ke form create dengan pre-fill tanggal
                window.location.href = '<?= base_url("app/akademik/kalender/new") ?>?date=' + info.dateStr;
            },
            eventDidMount: function(info) {
                // Tambahkan tooltip (opsional)
                info.el.title = info.event.title + (info.event.extendedProps.keterangan ? '\n' + info.event.extendedProps.keterangan : '');
            }
        });
        calendar.render();
    });
</script>

<?= $this->endSection() ?>