<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<!-- Memuat TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<?php
    $isEdit = isset($rpp);
    
    // Data awal untuk baris-baris RPP
    $initialData = [];
    if ($isEdit) {
        $initialData[] = $rpp; 
    } elseif (session()->getFlashdata('items')) {
        $initialData = session()->getFlashdata('items'); 
    }

    // [FIX LOGIC] Tentukan prefix nama input
    // Jika Edit: name="topik" (Flat)
    // Jika Create: name="items[{index}][topik]" (Array)
    $namePrefix = $isEdit ? '' : 'items[{index}]';
    
    // Helper function untuk generate name attribute
    function fieldName($prefix, $field) {
        return $prefix ? "{$prefix}[{$field}]" : $field;
    }
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                <?= $isEdit ? 'Edit RPP / Modul Ajar' : 'Penyusunan RPP (Bulk Mode)' ?>
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                <?= $isEdit ? 'Mode Edit Data Tunggal' : 'Anda dapat menyusun beberapa pertemuan sekaligus dalam satu kali simpan.' ?>
            </p>
        </div>
        <a href="<?= base_url('app/pembelajaran/rpp') ?>" class="text-gray-500 hover:text-gray-700 font-medium flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>

    <!-- Error Validation Message -->
    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg shadow-sm mb-6 animate-pulse">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-rose-700 font-bold"><?= session()->getFlashdata('error') ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- FORM START -->
    <form action="<?= $isEdit ? base_url('app/pembelajaran/rpp/update/'.$rpp['id']) : base_url('app/pembelajaran/rpp/create-bulk') ?>" method="post" id="rppForm">
        <?= csrf_field() ?>
        <?php if($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <!-- BAGIAN 1: HEADER (IDENTITAS SILABUS) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h3 class="text-lg font-bold text-indigo-800 dark:text-indigo-300 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                A. Referensi Silabus (Induk)
            </h3>

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih Silabus / Materi Pokok <span class="text-red-500">*</span></label>
                    
                    <?php if($isEdit): ?>
                        <!-- Mode Edit: Silabus Terkunci -->
                        <div class="p-3 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 font-medium">
                            <?php 
                                foreach($silabus as $s) {
                                    if($s['id'] == $rpp['silabus_id']) {
                                        echo "[{$s['kode_jenjang']}] {$s['materi_pokok']} ({$s['jenis_kurikulum']})";
                                        break;
                                    }
                                }
                            ?>
                        </div>
                        <input type="hidden" name="silabus_id" id="silabus_id" value="<?= $rpp['silabus_id'] ?>">
                        <?php 
                            foreach($silabus as $s) {
                                if($s['id'] == $rpp['silabus_id']) {
                                    echo '<input type="hidden" id="current_kurikulum" value="'.$s['jenis_kurikulum'].'">';
                                    break;
                                }
                            }
                        ?>
                    <?php else: ?>
                        <!-- Mode Create: Pilih Silabus -->
                        <select name="silabus_id" id="silabus_id" required class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 focus:border-indigo-500 outline-none transition-all font-bold text-gray-700" onchange="handleSilabusChange()">
                            <option value="" disabled selected>-- Pilih Referensi Silabus --</option>
                            <?php foreach($silabus as $s): ?>
                                <option value="<?= $s['id'] ?>" 
                                    data-kurikulum="<?= $s['jenis_kurikulum'] ?>"
                                    data-jenjang="<?= $s['kode_jenjang'] ?>"
                                    <?= (old('silabus_id') == $s['id']) ? 'selected' : '' ?>>
                                    [<?= $s['kode_jenjang'] ?>] <?= esc($s['materi_pokok']) ?> (<?= $s['jenis_kurikulum'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-2">* Memilih silabus akan menyesuaikan format form di bawah (Merdeka/K13).</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: DETAIL PERTEMUAN RPP -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    B. Rincian Pertemuan (RPP)
                </h3>
                
                <?php if(!$isEdit): ?>
                    <button type="button" onclick="addNewRow()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow flex items-center gap-2 text-sm font-semibold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Tambah Pertemuan
                    </button>
                <?php endif; ?>
            </div>

            <div id="rows-container" class="space-y-8"></div>

            <div class="fixed bottom-0 left-0 w-full bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-50">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <span class="text-sm text-gray-500 font-bold" id="row-counter">Total: 0 Pertemuan</span>
                    <div class="flex gap-4">
                        <a href="<?= base_url('app/pembelajaran/rpp') ?>" class="px-6 py-2.5 text-gray-700 bg-gray-100 hover:bg-gray-200 font-bold rounded-lg transition-colors">Batal</a>
                        <button type="submit" class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-lg shadow-md flex items-center hover:-translate-y-0.5 transition-transform">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            <?= $isEdit ? 'Update RPP' : 'Simpan Semua RPP' ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="h-24"></div>
        </div>
    </form>
</div>

<!-- TEMPLATE BARIS (Dinamic Naming) -->
<template id="row-template">
    <div class="rpp-row bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden relative group transition-all hover:shadow-lg">
        <!-- Header Baris -->
        <div class="bg-indigo-50 dark:bg-gray-700/50 px-6 py-4 border-b border-indigo-100 dark:border-gray-600 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4 flex-1 w-full">
                <div class="bg-indigo-600 text-white font-black px-3 py-1 rounded text-sm row-number-badge">#1</div>
                <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4 w-full">
                    <div>
                        <label class="block text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-1">Pertemuan Ke-</label>
                        <!-- [FIX] Name Dynamic -->
                        <input type="number" name="<?= fieldName($namePrefix, 'pertemuan_ke') ?>" class="w-full px-3 py-1.5 rounded border border-gray-300 focus:border-indigo-500 font-bold text-center" value="1">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-[10px] font-bold text-indigo-400 uppercase tracking-wider mb-1">Topik / Materi</label>
                        <input type="text" name="<?= fieldName($namePrefix, 'topik') ?>" class="w-full px-3 py-1.5 rounded border border-gray-300 focus:border-indigo-500 font-bold" placeholder="Judul topik pertemuan ini...">
                    </div>
                </div>
            </div>
            
            <?php if(!$isEdit): ?>
                <button type="button" onclick="removeRow(this)" class="text-gray-400 hover:text-rose-500 transition-colors p-2" title="Hapus Pertemuan Ini">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            <?php endif; ?>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 dark:bg-gray-900/30 p-4 rounded-xl">
                <!-- Merdeka Fields -->
                <div class="field-merdeka hidden space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-blue-600 uppercase mb-1">Pemahaman Bermakna</label>
                        <textarea name="<?= fieldName($namePrefix, 'pemahaman_bermakna') ?>" rows="2" class="w-full px-3 py-2 rounded border border-blue-200 focus:ring-1 focus:ring-blue-500 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-600 uppercase mb-1">Pertanyaan Pemantik</label>
                        <textarea name="<?= fieldName($namePrefix, 'pertanyaan_pemantik') ?>" rows="2" class="w-full px-3 py-2 rounded border border-blue-200 focus:ring-1 focus:ring-blue-500 text-sm"></textarea>
                    </div>
                </div>

                <!-- K13 Fields -->
                <div class="field-k13 hidden space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-amber-600 uppercase mb-1">Tema (K13)</label>
                        <input type="text" name="<?= fieldName($namePrefix, 'tema') ?>" class="w-full px-3 py-2 rounded border border-amber-200 focus:border-amber-500 text-sm font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-amber-600 uppercase mb-1">Subtema</label>
                        <input type="text" name="<?= fieldName($namePrefix, 'subtema') ?>" class="w-full px-3 py-2 rounded border border-amber-200 focus:border-amber-500 text-sm font-semibold">
                    </div>
                </div>
                
                <!-- Common Meta Fields -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Metode / Model</label>
                        <input type="text" name="<?= fieldName($namePrefix, 'metode_pembelajaran') ?>" class="w-full px-3 py-2 rounded border border-gray-300 text-sm" placeholder="e.g. PBL, Diskusi">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Media / Alat</label>
                        <input type="text" name="<?= fieldName($namePrefix, 'media_alat') ?>" class="w-full px-3 py-2 rounded border border-gray-300 text-sm" placeholder="e.g. LCD, Video">
                    </div>
                </div>
            </div>

            <!-- EDITOR FIELDS (TINYMCE) -->
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-black text-gray-700 dark:text-gray-200 mb-2 border-l-4 border-indigo-500 pl-2">A. Tujuan Pembelajaran <span class="text-red-500">*</span></label>
                    <textarea name="<?= fieldName($namePrefix, 'tujuan_pembelajaran') ?>" id="editor_tujuan_{index}" class="tinymce-editor w-full h-32"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-black text-gray-700 dark:text-gray-200 mb-2 border-l-4 border-indigo-500 pl-2">B. Langkah Pembelajaran <span class="text-red-500">*</span></label>
                    <textarea name="<?= fieldName($namePrefix, 'langkah_pembelajaran') ?>" id="editor_langkah_{index}" class="tinymce-editor w-full h-48"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-black text-gray-700 dark:text-gray-200 mb-2 border-l-4 border-indigo-500 pl-2">C. Penilaian (Asesmen)</label>
                    <textarea name="<?= fieldName($namePrefix, 'penilaian') ?>" id="editor_penilaian_{index}" class="tinymce-editor w-full h-32"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    let rowCount = 0;
    // Pass PHP state to JS
    const isEditMode = <?= $isEdit ? 'true' : 'false' ?>;

    const tinyConfig = {
        height: 250, menubar: false,
        plugins: ['lists', 'link', 'wordcount'],
        toolbar: 'undo redo | bold italic underline | bullist numlist | removeformat',
        content_style: 'body { font-family:Inter,sans-serif; font-size:14px }',
        skin: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oxide-dark' : 'oxide'),
        content_css: (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'default'),
    };

    function handleSilabusChange() {
        const select = document.getElementById('silabus_id');
        if (!select) return;

        let kurikulum = '';
        if(select.tagName === 'INPUT') { 
             const helper = document.getElementById('current_kurikulum');
             if(helper) kurikulum = helper.value;
        } else { 
            if(!select.value) return;
            const selectedOption = select.options[select.selectedIndex];
            kurikulum = selectedOption.getAttribute('data-kurikulum');
        }

        const merdekaFields = document.querySelectorAll('.field-merdeka');
        const k13Fields = document.querySelectorAll('.field-k13');

        if (kurikulum === 'Merdeka') {
            merdekaFields.forEach(el => el.classList.remove('hidden'));
            k13Fields.forEach(el => el.classList.add('hidden'));
        } else {
            merdekaFields.forEach(el => el.classList.add('hidden'));
            k13Fields.forEach(el => el.classList.remove('hidden'));
        }
    }

    // [FIX JS SELECTOR] Fungsi untuk mengambil nama input yang sesuai mode
    function getInputName(field, index) {
        if (isEditMode) {
            return field; // Flat name: "topik"
        } else {
            return `items[${index}][${field}]`; // Array name: "items[0][topik]"
        }
    }

    function addNewRow(data = null) {
        const container = document.getElementById('rows-container');
        const template = document.getElementById('row-template').innerHTML;
        const newIndex = rowCount;

        let newRowHtml = template.replace(/{index}/g, newIndex);
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newRowHtml;
        const newRow = tempDiv.firstElementChild;

        const badge = newRow.querySelector('.row-number-badge');
        badge.textContent = `#${rowCount + 1}`;

        // Populate Data Logic - Menggunakan fungsi helper nama
        if (data) {
            newRow.querySelector(`[name="${getInputName('pertemuan_ke', newIndex)}"]`).value = data.pertemuan_ke || (rowCount + 1);
            newRow.querySelector(`[name="${getInputName('topik', newIndex)}"]`).value = data.topik || '';
            newRow.querySelector(`[name="${getInputName('pemahaman_bermakna', newIndex)}"]`).value = data.pemahaman_bermakna || '';
            newRow.querySelector(`[name="${getInputName('pertanyaan_pemantik', newIndex)}"]`).value = data.pertanyaan_pemantik || '';
            newRow.querySelector(`[name="${getInputName('tema', newIndex)}"]`).value = data.tema || '';
            newRow.querySelector(`[name="${getInputName('subtema', newIndex)}"]`).value = data.subtema || '';
            newRow.querySelector(`[name="${getInputName('metode_pembelajaran', newIndex)}"]`).value = data.metode_pembelajaran || '';
            newRow.querySelector(`[name="${getInputName('media_alat', newIndex)}"]`).value = data.media_alat || '';

            // TinyMCE Textareas (Value property set directly)
            newRow.querySelector(`#editor_tujuan_${newIndex}`).value = data.tujuan_pembelajaran || '';
            newRow.querySelector(`#editor_langkah_${newIndex}`).value = data.langkah_pembelajaran || '';
            newRow.querySelector(`#editor_penilaian_${newIndex}`).value = data.penilaian || '';
        } else {
            newRow.querySelector(`[name="${getInputName('pertemuan_ke', newIndex)}"]`).value = (rowCount + 1);
            newRow.querySelector(`#editor_langkah_${newIndex}`).value = '<ul><li><strong>Pendahuluan:</strong> ...</li><li><strong>Inti:</strong> ...</li><li><strong>Penutup:</strong> ...</li></ul>';
        }

        container.appendChild(newRow);

        setTimeout(() => {
            initTinyMCE(newIndex);
            handleSilabusChange(); 
        }, 100);

        rowCount++;
        updateCounter();
    }

    function initTinyMCE(index) {
        tinymce.init({ ...tinyConfig, selector: `#editor_tujuan_${index}` });
        tinymce.init({ ...tinyConfig, height: 350, selector: `#editor_langkah_${index}` });
        tinymce.init({ ...tinyConfig, selector: `#editor_penilaian_${index}` });
    }

    function removeRow(button) {
        if(confirm('Hapus pertemuan ini?')) {
            const row = button.closest('.rpp-row');
            const editors = row.querySelectorAll('.tinymce-editor');
            editors.forEach(editor => {
                if(tinymce.get(editor.id)) tinymce.get(editor.id).remove();
            });
            row.remove();
            updateCounter();
        }
    }

    function updateCounter() {
        const count = document.querySelectorAll('.rpp-row').length;
        document.getElementById('row-counter').textContent = `Total: ${count} Pertemuan`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        <?php if(!empty($initialData)): ?>
            const initialData = <?= json_encode($initialData) ?>;
            if(Array.isArray(initialData)) {
                initialData.forEach(item => addNewRow(item));
            } else {
                addNewRow(initialData); 
            }
        <?php else: ?>
            addNewRow(); 
        <?php endif; ?>
        setTimeout(() => handleSilabusChange(), 500);
    });
</script>
<?= $this->endSection() ?>