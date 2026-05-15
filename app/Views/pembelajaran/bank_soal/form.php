<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Paket Soal - Sheet Style (Tailwind)</title>
    
    <!-- Tailwind CSS 3.4 -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome 5 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Nunito', sans-serif; }
        
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        
        /* Sheet Input Styles (Excel-like) */
        .sheet-input {
            width: 100%;
            padding: 0.375rem 0.5rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
            background-color: transparent;
            transition: all 0.2s;
            outline: none;
        }
        .sheet-input:hover { border-color: #cbd5e1; } /* gray-300 */
        .sheet-input:focus {
            background-color: white;
            border-color: #3b82f6; /* blue-500 */
            box-shadow: 0 0 0 1px #3b82f6;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.2s ease-out forwards; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 pb-20 custom-scrollbar">

    <form id="bulkForm" action="#" onsubmit="event.preventDefault(); saveData();">
        
        <!-- Sticky Header: Toolbar -->
        <div class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm px-4 py-3">
            <div class="max-w-[98%] mx-auto flex flex-col md:flex-row items-center justify-between gap-3">
                
                <!-- Left: Title & Back -->
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <a href="#" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full transition-colors">
                        <i class="fas fa-arrow-left fa-lg"></i>
                    </a>
                    <div>
                        <h1 class="text-lg font-bold text-gray-800 leading-tight flex items-center gap-2">
                            <i class="fas fa-file-invoice text-blue-600"></i>
                            Input Paket Soal
                        </h1>
                        <p class="text-xs text-gray-500 hidden sm:block">Editor Soal Batch (Sheet Mode)</p>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto">
                    <!-- Counter -->
                    <div class="px-3 py-1 bg-gray-100 rounded-full border border-gray-200 text-xs font-bold text-gray-600 whitespace-nowrap" id="question-counter">
                        0 Item
                    </div>

                    <!-- Template & Import -->
                    <div class="flex rounded-md shadow-sm" role="group">
                        <button type="button" onclick="downloadTemplate()" class="px-3 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10 focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-download mr-1"></i> <span class="hidden sm:inline">Template</span>
                        </button>
                        <button type="button" onclick="triggerImport()" class="px-3 py-2 text-sm font-medium text-white bg-green-600 border border-green-600 rounded-r-md hover:bg-green-700 focus:z-10 focus:ring-2 focus:ring-green-500">
                            <i class="fas fa-file-import mr-1"></i> <span class="hidden sm:inline">Import</span>
                        </button>
                    </div>
                    <input type="file" id="fileInput" class="hidden" accept=".txt,.csv" onchange="handleFileImport(this)">

                    <div class="h-6 w-px bg-gray-300 mx-2"></div>

                    <!-- Add & Save -->
                    <button type="button" onclick="addQuestionRow()" class="px-4 py-2 text-sm font-bold text-blue-600 bg-white border border-blue-600 rounded-md hover:bg-blue-50 transition-colors shadow-sm whitespace-nowrap">
                        <i class="fas fa-plus mr-1"></i> Baris Baru
                    </button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors shadow-sm whitespace-nowrap">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-[98%] mx-auto pt-6 px-2">
            
            <!-- Card Header (Kop Soal) -->
            <div class="bg-white rounded-t-lg border border-gray-200 shadow-sm p-5 mb-0 z-10 relative">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                    <!-- Silabus -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">
                            Materi Pokok / Silabus <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="global_silabus_id" class="block w-full rounded-md border-gray-300 bg-gray-50 py-2 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 border">
                                <option value="">-- Pilih Topik Pembelajaran --</option>
                                <option value="1">[MTK-10] Aljabar Linear</option>
                                <option value="2">[BIO-11] Sistem Pernapasan</option>
                                <option value="3">[SEJ-12] Perang Dunia II</option>
                            </select>
                        </div>
                    </div>

                    <!-- Jenis Soal -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Jenis Soal</label>
                        <select name="global_jenis_soal" id="global_jenis_soal" onchange="toggleGlobalOptions()" class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 border">
                            <option value="PG" selected>Pilihan Ganda</option>
                            <option value="Essay">Essay / Uraian</option>
                        </select>
                    </div>

                    <!-- Kesulitan -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Kesulitan</label>
                        <select name="global_tingkat_kesulitan" class="block w-full rounded-md border-gray-300 bg-white py-2 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 border">
                            <option value="Mudah">Mudah</option>
                            <option value="Sedang" selected>Sedang</option>
                            <option value="Sukar">Sukar</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Main Sheet Table -->
            <div class="bg-white border border-gray-200 border-t-0 rounded-b-lg shadow-sm overflow-hidden min-h-[500px] flex flex-col">
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="p-3 w-12 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">#</th>
                                <th class="p-3 w-1/3 min-w-[350px] text-xs font-bold text-gray-500 uppercase tracking-wider">Pertanyaan & Kode</th>
                                <th class="p-3 min-w-[300px] pg-column text-xs font-bold text-gray-500 uppercase tracking-wider">Opsi Jawaban (A-E)</th>
                                <th class="p-3 w-24 text-center pg-column text-xs font-bold text-gray-500 uppercase tracking-wider">Kunci</th>
                                <th class="p-3 w-12 text-center text-xs font-bold text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody id="questions-tbody" class="divide-y divide-gray-100">
                            <!-- JS Generated Rows -->
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div id="emptyState" class="hidden flex flex-col items-center justify-center py-12 text-gray-400">
                        <i class="far fa-clipboard text-5xl mb-3 opacity-30"></i>
                        <p class="text-sm">Belum ada soal. Tambah baris atau import file.</p>
                    </div>
                </div>

                <!-- Add Row Footer Area -->
                <div onclick="addQuestionRow()" class="bg-gray-50 border-t border-gray-200 p-4 text-center cursor-pointer hover:bg-blue-50 transition-colors group flex-none">
                    <div class="flex flex-col items-center justify-center">
                        <i class="fas fa-plus-circle text-2xl text-gray-300 group-hover:text-blue-500 transition-colors mb-1"></i>
                        <span class="text-xs font-bold text-gray-400 group-hover:text-blue-600 transition-colors">Klik area ini untuk menambah baris</span>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300">
        <div class="bg-white border-l-4 border-blue-500 shadow-xl rounded-md p-4 flex items-start w-80">
            <div class="flex-shrink-0" id="toastIcon">
                <i class="fas fa-info-circle text-blue-500 text-lg"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-gray-900" id="toastTitle">Info</h3>
                <div class="mt-1 text-sm text-gray-500" id="toastMessage">Pesan notifikasi.</div>
            </div>
        </div>
    </div>

    <!-- Scripts (Vanilla JS - No jQuery needed for Tailwind) -->
    <script>
        let rowCount = 0;
        const tbody = document.getElementById('questions-tbody');
        const emptyState = document.getElementById('emptyState');

        document.addEventListener('DOMContentLoaded', function() {
            // Init 3 baris kosong
            for (let i = 0; i < 3; i++) {
                addQuestionRow();
            }
            toggleGlobalOptions();
        });

        // --- Core Functions ---

        function addQuestionRow(data = null) {
            rowCount++;
            
            const tr = document.createElement('tr');
            tr.id = `row-${rowCount}`;
            tr.className = "hover:bg-blue-50/50 transition-colors group animate-fade-in bg-white";

            const valTanya = data?.question || '';
            const valKode  = data?.code || '';
            const valOpsiA = data?.optA || '';
            const valOpsiB = data?.optB || '';
            const valOpsiC = data?.optC || '';
            const valOpsiD = data?.optD || '';
            const valOpsiE = data?.optE || '';
            const valKunci = data?.answer ? data.answer.toLowerCase() : '';

            tr.innerHTML = `
                <td class="p-3 text-center align-top pt-4">
                    <span class="inline-block w-7 h-7 leading-7 rounded-full bg-gray-100 text-gray-500 text-xs font-bold border border-gray-200 row-number-badge select-none">${rowCount}</span>
                </td>
                
                <td class="p-2 align-top pt-3">
                    <textarea name="soal[${rowCount}][pertanyaan]" rows="3" class="sheet-input resize-none mb-2 min-h-[80px]" placeholder="Ketik pertanyaan lengkap di sini...">${valTanya}</textarea>
                    <input type="text" name="soal[${rowCount}][kode_soal]" value="${valKode}" class="sheet-input text-xs text-gray-500 border-b border-gray-100 !rounded-none focus:!border-blue-500" placeholder="Kode Soal (Opsional)">
                </td>
                
                <td class="p-2 align-top pt-3 pg-column">
                    <div class="space-y-2">
                        ${renderOptionInput(rowCount, 'a', 'A', valOpsiA)}
                        ${renderOptionInput(rowCount, 'b', 'B', valOpsiB)}
                        ${renderOptionInput(rowCount, 'c', 'C', valOpsiC)}
                        ${renderOptionInput(rowCount, 'd', 'D', valOpsiD)}
                        ${renderOptionInput(rowCount, 'e', 'E', valOpsiE, true)}
                    </div>
                </td>
                
                <td class="p-2 align-top text-center pt-3 pg-column">
                    <select name="soal[${rowCount}][kunci]" class="sheet-input text-center font-bold text-blue-600 bg-gray-50/50">
                        <option value="">-</option>
                        <option value="a" ${valKunci === 'a' ? 'selected' : ''}>A</option>
                        <option value="b" ${valKunci === 'b' ? 'selected' : ''}>B</option>
                        <option value="c" ${valKunci === 'c' ? 'selected' : ''}>C</option>
                        <option value="d" ${valKunci === 'd' ? 'selected' : ''}>D</option>
                        <option value="e" ${valKunci === 'e' ? 'selected' : ''}>E</option>
                    </select>
                </td>
                
                <td class="p-2 align-top text-center pt-3">
                    <button type="button" onclick="removeRow('${tr.id}')" class="text-gray-300 hover:text-red-500 p-2 transition-colors rounded-full hover:bg-red-50" title="Hapus Baris">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            updateRowNumbers();
            updateCounter();
            checkEmptyState();
            
            // Re-apply visibility based on current mode
            toggleGlobalOptions();
        }

        function renderOptionInput(rId, key, label, val, isOpt = false) {
            return `
                <div class="flex items-center gap-2 group/opt">
                    <div class="w-6 flex-none text-center text-xs font-bold text-gray-400 border border-gray-200 rounded bg-gray-50 py-1 select-none">${label}</div>
                    <input type="text" name="soal[${rId}][opsi][${key}]" value="${val}" class="sheet-input py-1 text-sm" placeholder="Pilihan ${label} ${isOpt ? '(Opsional)' : ''}">
                </div>
            `;
        }

        function removeRow(id) {
            const el = document.getElementById(id);
            if(el) {
                el.remove();
                updateRowNumbers();
                updateCounter();
                checkEmptyState();
            }
        }

        function updateRowNumbers() {
            const badges = document.querySelectorAll('.row-number-badge');
            badges.forEach((badge, index) => {
                badge.innerText = index + 1;
            });
            rowCount = badges.length; // Sync counter
        }

        function updateCounter() {
            const total = tbody.children.length;
            document.getElementById('question-counter').innerText = `${total} Item`;
        }

        function checkEmptyState() {
            if (tbody.children.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        function toggleGlobalOptions() {
            const type = document.getElementById('global_jenis_soal').value;
            const pgCols = document.querySelectorAll('.pg-column');
            
            pgCols.forEach(col => {
                col.style.display = (type === 'Essay') ? 'none' : 'table-cell';
            });
        }

        function saveData() {
            // Simulasi collect data
            console.log("Saving data...");
            showToast('Data berhasil dikumpulkan (Cek Console)', 'success');
        }

        // --- IMPORT & TEMPLATE ---

        function downloadTemplate() {
            const header = "Pertanyaan,Opsi A,Opsi B,Opsi C,Opsi D,Opsi E,Kunci Jawaban";
            const content = `${header}\nIbu kota Indonesia?,Jakarta,Bandung,Surabaya,Medan,Makassar,A\nHasil 10+10?,10,20,30,40,50,B`;
            
            const blob = new Blob([content], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = "template_soal.txt";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showToast('Template berhasil didownload', 'info');
        }

        function triggerImport() {
            document.getElementById('fileInput').click();
        }

        function handleFileImport(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    processImportedText(e.target.result);
                    showToast(`Berhasil import: ${file.name}`, 'success');
                } catch (err) {
                    showToast('Gagal memproses file', 'error');
                }
                input.value = '';
            };
            reader.readAsText(file);
        }

        function processImportedText(text) {
            const lines = text.split('\n');
            let start = 0;
            // Simple header check
            if (lines.length > 0 && lines[0].toLowerCase().includes('pertanyaan')) start = 1;

            // FIX: Validasi dulu data sebelum menghapus yang ada
            const rowsToAdd = [];
            for (let i = start; i < lines.length; i++) {
                const line = lines[i].trim();
                if (line) {
                    const cols = line.split(',');
                    if (cols.length >= 2) {
                        rowsToAdd.push({
                            question: cols[0],
                            optA: cols[1] || '',
                            optB: cols[2] || '',
                            optC: cols[3] || '',
                            optD: cols[4] || '',
                            optE: cols[5] || '',
                            answer: cols[6] || ''
                        });
                    }
                }
            }

            if (rowsToAdd.length > 0) {
                // Hapus data lama (kosongkan tabel) agar import mulai dari baris 1
                document.getElementById('questions-tbody').innerHTML = '';
                rowCount = 0;

                // Masukkan data baru
                rowsToAdd.forEach(row => addQuestionRow(row));
                showToast(`Berhasil mengimpor ${rowsToAdd.length} soal`, 'success');
            } else {
                showToast('Tidak ada data valid ditemukan', 'error');
            }
        }

        // --- TOAST ---

        function showToast(msg, type = 'info') {
            const toast = document.getElementById('toast');
            const tIcon = document.getElementById('toastIcon');
            const tTitle = document.getElementById('toastTitle');
            const tMsg = document.getElementById('toastMessage');
            const container = toast.querySelector('div');

            // Reset classes
            container.className = "bg-white border-l-4 shadow-xl rounded-md p-4 flex items-start w-80";
            
            if (type === 'success') {
                container.classList.add('border-green-500');
                tIcon.innerHTML = '<i class="fas fa-check-circle text-green-500 text-lg"></i>';
                tTitle.innerText = "Sukses";
            } else if (type === 'error') {
                container.classList.add('border-red-500');
                tIcon.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 text-lg"></i>';
                tTitle.innerText = "Error";
            } else {
                container.classList.add('border-blue-500');
                tIcon.innerHTML = '<i class="fas fa-info-circle text-blue-500 text-lg"></i>';
                tTitle.innerText = "Info";
            }

            tMsg.innerText = msg;

            // Show
            toast.classList.remove('translate-y-20', 'opacity-0');

            // Hide after 3s
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

    </script>
</body>
</html>