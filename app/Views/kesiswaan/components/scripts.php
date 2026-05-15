<?php
    // FIX CRITICAL: Inisialisasi variabel statistik dengan nilai default 0.
    // Ini mencegah error "Undefined Variable" saat berada di tab selain Dashboard,
    // yang menyebabkan seluruh JavaScript (termasuk tombol Edit/Tambah) macet.
    $s = $stats ?? [];
    $v_presensi = $s['total_presensi'] ?? 0;
    $v_kasus    = $s['total_kasus'] ?? 0;
    $v_prestasi = $s['total_prestasi'] ?? 0;
    // Data untuk Chart 2 (Dummy/Static)
    // Jika nanti ada data real, ambil dari $s juga
?>
<script>
    /**
     * KESISWAAN MODULE SCRIPTS
     * Berisi logika: Chart, Modal, Form Edit, dan Tabel Interaktif.
     */

    // --- 1. CHART LOGIC (Hanya render jika canvas ada) ---
    document.addEventListener("DOMContentLoaded", function() {
        // Cek apakah element chart ada (Hanya ada di tab dashboard)
        const chartEl1 = document.getElementById('kpiChart1');
        const chartEl2 = document.getElementById('kpiChart2');

        if (!chartEl1 && !chartEl2) return; // Stop jika bukan di dashboard (Hemat resource)

        if (typeof Chart === 'undefined') {
            console.warn('Chart.js tidak terdeteksi. Pastikan file asset dimuat di header.');
            return;
        }

        if(chartEl1) {
            new Chart(chartEl1.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Akademik (Presensi)', 'Pelanggaran (BK)', 'Kedisiplinan', 'Prestasi'],
                    datasets: [{
                        label: 'Jumlah Data',
                        // Menggunakan variabel PHP yang sudah diamankan di atas
                        data: [<?= $v_presensi ?>, <?= $v_kasus ?>, <?= floor($v_kasus * 0.8) ?>, <?= $v_prestasi ?>],
                        backgroundColor: ['#0ea5e9', '#f43f5e', '#f59e0b', '#10b981'],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } } }
                }
            });
        }

        if(chartEl2) {
            new Chart(chartEl2.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Olahraga', 'Seni', 'Sains', 'Lainnya'],
                    datasets: [{
                        data: [35, 25, 20, 20], // Static data for now
                        backgroundColor: ['#6366f1', '#ec4899', '#14b8a6', '#64748b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });

    // --- 2. MODAL FUNCTIONS (Global) ---
    function openModal(id) { 
        const el = document.getElementById(id);
        if(el) {
            el.showModal(); 
        } else {
            console.error('Modal dengan ID ' + id + ' tidak ditemukan!');
        }
    }
    
    function closeModal(id) { 
        const m = document.getElementById(id); 
        if(!m) return;

        // Reset Form
        const form = m.querySelector('form');
        if(form) form.reset(); 
        
        // Reset Hidden ID (Penting agar tidak tertukar antara Add dan Edit)
        const h = m.querySelector('input[type="hidden"][name="id"]');
        if(h) h.value = '';
        
        // Reset Khusus Modal Presensi
        if(id === 'modalPresensi') {
            const listContainer = document.getElementById('presensiListContainer');
            if(listContainer) {
                listContainer.innerHTML = '<div class="text-center py-10 text-slate-400 text-sm italic">Pilih Ekskul terlebih dahulu.</div>';
            }
            const totalSiswa = document.getElementById('total_siswa');
            if(totalSiswa) totalSiswa.innerText = '0';
            
            presensiList = [];
            updateHiddenInput();
        }
        m.close(); 
    }

    // --- 3. HELPER EDIT FUNCTIONS (Populate Forms) ---
    
    function editEkskul(data) {
        document.getElementById('ekskul_id').value = data.id;
        document.getElementById('nama_ekskul').value = data.nama_ekskul;
        document.getElementById('kategori').value = data.kategori;
        document.getElementById('hari_latihan').value = data.hari_latihan;
        document.getElementById('jam_mulai').value = data.jam_mulai;
        document.getElementById('jam_selesai').value = data.jam_selesai;
        document.getElementById('deskripsi').value = data.deskripsi;
        
        // Set Select Option (Unit)
        if(data.kode_jenjang) {
            const unitSelect = document.querySelector('#modalEkskul select[name="kode_jenjang"]');
            if(unitSelect) unitSelect.value = data.kode_jenjang;
        }
        // Set Select Option (Guru)
        if(data.guru_pembina_id) {
            const guruSelect = document.getElementById('guru_pembina_id');
            if(guruSelect) guruSelect.value = data.guru_pembina_id;
        }
        openModal('modalEkskul');
    }

    function editOrganisasi(data) {
        document.getElementById('org_id').value = data.id;
        document.getElementById('org_siswa_id').value = data.siswa_id;
        document.getElementById('org_jabatan').value = data.jabatan;
        document.getElementById('org_jenis').value = data.jenis_organisasi;
        document.getElementById('org_status').checked = (data.status_aktif == 1);
        document.getElementById('org_urutan').value = data.urutan || '';
        document.getElementById('org_parent').value = data.parent_id || 0;

        if(data.kode_jenjang) {
            const unitSelect = document.querySelector('#modalOrganisasi select[name="kode_jenjang"]');
            if(unitSelect) unitSelect.value = data.kode_jenjang;
        }
        openModal('modalOrganisasi');
    }

    function editKasus(data) {
        document.getElementById('bk_id').value = data.id;
        document.getElementById('bk_siswa_id').value = data.siswa_id;
        if(data.bk_kategori_id) document.getElementById('bk_kategori_id').value = data.bk_kategori_id;
        document.getElementById('bk_tanggal').value = data.tanggal_kejadian;
        document.getElementById('bk_keterangan').value = data.keterangan_detail;
        document.getElementById('bk_tindak').value = data.tindak_lanjut;
        
        if(data.kode_jenjang) {
            const unitSelect = document.querySelector('#modalKasus select[name="kode_jenjang"]');
            if(unitSelect) unitSelect.value = data.kode_jenjang;
        }
        openModal('modalKasus');
    }

    function editAlumni(data) {
        document.getElementById('alumni_id').value = data.id;
        document.getElementById('alm_siswa_id').value = data.siswa_id;
        document.getElementById('alm_tahun').value = data.tahun_lulus;
        document.getElementById('alm_status').value = data.status_kegiatan;
        document.getElementById('alm_instansi').value = data.nama_instansi;
        document.getElementById('alm_jurusan').value = data.jabatan_jurusan;
        document.getElementById('alm_testimoni').value = data.testimoni;
        
        if(data.kode_jenjang) {
            const unitSelect = document.querySelector('#modalAlumni select[name="kode_jenjang"]');
            if(unitSelect) unitSelect.value = data.kode_jenjang;
        }
        openModal('modalAlumni');
    }
    
    function editAnggota(data) {
        document.getElementById('anggota_id').value = data.id;
        document.getElementById('agt_ekskul').value = data.ekskul_id;
        document.getElementById('agt_siswa').value = data.siswa_id;
        document.getElementById('agt_nilai').value = data.nilai_huruf;
        document.getElementById('agt_desk').value = data.deskripsi_nilai;
        
        if(data.kode_jenjang) {
            const u = document.querySelector('#modalAnggota select[name="kode_jenjang"]');
            if(u) u.value = data.kode_jenjang;
        }
        openModal('modalAnggota');
    }

    function editPrestasi(data) {
        document.getElementById('prestasi_id').value = data.id;
        document.getElementById('pres_siswa_id').value = data.siswa_id;
        document.getElementById('pres_jenis').value = data.jenis_prestasi;
        document.getElementById('pres_nama').value = data.nama_prestasi;
        document.getElementById('pres_tingkat').value = data.tingkat;
        // Fix: menggunakan field 'peringkat'
        document.getElementById('pres_juara').value = data.peringkat;
        
        // Parsing tanggal_prestasi (Format YYYY-MM-DD HH:MM:SS -> YYYY-MM-DD)
        const dateVal = data.tanggal_prestasi ? data.tanggal_prestasi.split(' ')[0] : '';
        document.getElementById('pres_tanggal').value = dateVal;
        document.getElementById('pres_keterangan').value = data.keterangan;
        
        openModal('modalPrestasi');
    }
    
    // --- 4. PRESENSI LOGIC (Dynamic Form) ---
    // Pastikan variabel allAnggota didefinisikan di view induk, jika tidak, pakai array kosong
    const allAnggota = <?= isset($jsonAllAnggota) ? $jsonAllAnggota : '[]' ?>; 
    let presensiList = []; 

    function loadAnggotaByEkskul(ekskulId) {
        const filteredMembers = allAnggota.filter(m => m.ekskul_id == ekskulId);
        presensiList = filteredMembers.map(m => ({
            siswa_id: m.siswa_id,
            nama: m.nama_siswa,
            nis: m.nis,
            status: 'H' // Default Hadir
        }));
        renderPresensiRows();
    }

    function renderPresensiRows() {
        const container = document.getElementById('presensiListContainer');
        const totalEl = document.getElementById('total_siswa');
        if(totalEl) totalEl.innerText = presensiList.length;

        if (presensiList.length === 0) {
            if(container) container.innerHTML = '<div class="text-center py-10 text-slate-400 text-sm italic">Tidak ada anggota terdaftar di ekskul ini.</div>';
            updateHiddenInput();
            return;
        }

        let html = '';
        presensiList.forEach((item, index) => {
            let borderClass = 'border-slate-200';
            if(item.status == 'H') borderClass = 'border-emerald-300 bg-emerald-50/50';
            if(item.status == 'I') borderClass = 'border-blue-300 bg-blue-50/50';
            if(item.status == 'S') borderClass = 'border-amber-300 bg-amber-50/50';
            if(item.status == 'A') borderClass = 'border-rose-300 bg-rose-50/50';

            html += `
                <div class="grid grid-cols-12 gap-2 items-center px-3 py-2 border-b border-slate-100 hover:bg-slate-50 transition-colors">
                    <div class="col-span-1 text-xs text-slate-400 font-mono">${index + 1}</div>
                    <div class="col-span-6">
                        <div class="text-sm font-medium text-slate-700 truncate">${item.nama}</div>
                        <div class="text-[10px] text-slate-400">${item.nis || '-'}</div>
                    </div>
                    <div class="col-span-5">
                        <select onchange="updateStatus(${index}, this.value)" class="w-full text-xs font-bold py-1.5 px-2 rounded-lg border ${borderClass} focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="H" ${item.status === 'H' ? 'selected' : ''} class="text-emerald-600">Hadir</option>
                            <option value="I" ${item.status === 'I' ? 'selected' : ''} class="text-blue-600">Izin</option>
                            <option value="S" ${item.status === 'S' ? 'selected' : ''} class="text-amber-600">Sakit</option>
                            <option value="A" ${item.status === 'A' ? 'selected' : ''} class="text-rose-600">Alpha</option>
                        </select>
                    </div>
                </div>
            `;
        });
        if(container) container.innerHTML = html;
        updateHiddenInput();
    }

    function updateStatus(index, newStatus) {
        if(presensiList[index]) {
            presensiList[index].status = newStatus;
            renderPresensiRows(); 
        }
    }

    function setAllStatus(status) {
        presensiList.forEach(item => item.status = status);
        renderPresensiRows();
    }

    function updateHiddenInput() {
        const input = document.getElementById('data_presensi');
        if(input) input.value = JSON.stringify(presensiList);
    }

    function editPresensi(data) {
        document.getElementById('presensi_id').value = data.id;
        const ekskulSelect = document.getElementById('pre_ekskul');
        if(ekskulSelect) ekskulSelect.value = data.ekskul_id;
        
        document.getElementById('pre_tanggal').value = data.tanggal;
        document.getElementById('pre_materi').value = data.materi_kegiatan;
        
        try {
            const savedList = JSON.parse(data.data_presensi);
            // Reconstruct list with names (assuming we have allAnggota data)
            presensiList = savedList.map(savedItem => {
                const memberInfo = allAnggota.find(m => m.siswa_id == savedItem.siswa_id);
                return {
                    siswa_id: savedItem.siswa_id,
                    status: savedItem.status,
                    nama: memberInfo ? memberInfo.nama_siswa : ('Siswa #' + savedItem.siswa_id),
                    nis: memberInfo ? memberInfo.nis : '-'
                };
            });
            renderPresensiRows();
        } catch(e) {
            console.error("Error parsing presensi", e);
            presensiList = [];
            renderPresensiRows();
        }
        openModal('modalPresensi');
    }

    // --- 5. ADVANCED TABLE LOGIC (Pagination, Sort, Filter) ---
    function initAdvancedTable(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return; // Exit if table not found

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        // Jika tabel kosong (berisi pesan "Belum ada data"), jangan aktifkan fitur
        if (rows.length === 1 && rows[0].cells.length > 1 && rows[0].innerText.includes('Belum ada')) return;

        const originalRows = [...rows]; // Keep copy for filtering
        let filteredRows = [...rows];
        
        // Elements Controls
        const perPageSelect = document.getElementById(`perPage-${tableId}`);
        const searchInput = document.getElementById(`search-${tableId}`);
        const infoEl = document.getElementById(`info-${tableId}`);
        const controlsEl = document.getElementById(`controls-${tableId}`);
        
        let currentPage = 1;
        let perPage = parseInt(perPageSelect ? perPageSelect.value : 10);
        let sortCol = null;
        let sortAsc = true;

        function updateTable() {
            // Calculate pages
            const totalRows = filteredRows.length;
            const totalPages = Math.ceil(totalRows / perPage);
            
            // Validate page
            if (currentPage < 1) currentPage = 1;
            if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
            
            // Slice rows
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            
            // Hide all original rows first
            originalRows.forEach(r => r.style.display = 'none');
            
            // Show filtered slice
            filteredRows.slice(start, end).forEach(r => r.style.display = '');
            
            // Update Info
            if (infoEl) infoEl.innerText = `Menampilkan ${totalRows === 0 ? 0 : start + 1} - ${Math.min(end, totalRows)} dari ${totalRows} data`;
            
            // Update Controls
            if (controlsEl) {
                let html = '';
                // Prev
                html += `<button class="px-3 py-1 border rounded hover:bg-slate-50 disabled:opacity-50" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage('${tableId}', -1)">&laquo;</button>`;
                // Next
                html += `<button class="px-3 py-1 border rounded hover:bg-slate-50 disabled:opacity-50" ${currentPage === totalPages || totalPages === 0 ? 'disabled' : ''} onclick="changePage('${tableId}', 1)">&raquo;</button>`;
                controlsEl.innerHTML = html;
            }
        }

        // Search Logic
        if (searchInput) {
            searchInput.addEventListener('keyup', (e) => {
                const term = e.target.value.toLowerCase();
                filteredRows = originalRows.filter(row => {
                    return row.innerText.toLowerCase().includes(term);
                });
                currentPage = 1;
                updateTable();
            });
        }

        // Per Page Logic
        if (perPageSelect) {
            perPageSelect.addEventListener('change', (e) => {
                perPage = parseInt(e.target.value);
                currentPage = 1;
                updateTable();
            });
        }

        // Sort Logic
        table.querySelectorAll('th.sortable').forEach((th, index) => {
            th.addEventListener('click', () => {
                const type = th.dataset.sort; // string | number
                sortAsc = sortCol === index ? !sortAsc : true;
                sortCol = index;
                
                // Update Icons
                table.querySelectorAll('.sort-icon').forEach(i => i.innerHTML = '');
                th.querySelector('.sort-icon').innerHTML = sortAsc ? ' ▲' : ' ▼';

                filteredRows.sort((a, b) => {
                    const valA = a.children[index].innerText.trim();
                    const valB = b.children[index].innerText.trim();
                    
                    if (type === 'number') {
                        return sortAsc ? (parseFloat(valA) - parseFloat(valB)) : (parseFloat(valB) - parseFloat(valA));
                    } else {
                        return sortAsc ? valA.localeCompare(valB) : valB.localeCompare(valA);
                    }
                });
                updateTable();
            });
        });

        // Global Helper for Pagination Button
        // Kita attach ke window agar onclick di string HTML bisa mengaksesnya
        window.changePage = (id, dir) => {
            if (id !== tableId) return;
            currentPage += dir;
            updateTable();
        };

        // Init
        updateTable();
    }

    // --- 6. INIT ALL TABLES ---
    document.addEventListener('DOMContentLoaded', function() {
        initAdvancedTable('table-ekskul');
        initAdvancedTable('table-anggota');
        initAdvancedTable('table-prestasi');
        initAdvancedTable('table-presensi');
        initAdvancedTable('table-organisasi');
    });
</script>