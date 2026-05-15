<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>

<div class="px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto font-sans antialiased text-slate-900">
    
    <!-- HEADER & BREADCRUMB -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase italic">
                <?= esc($title) ?>
            </h1>
            <nav class="flex mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                    <li class="inline-flex items-center uppercase italic">CMS</li>
                    <li>
                        <a href="<?= base_url('app/cms/album') ?>" class="flex items-center hover:text-amber-600 transition-colors">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase">Galeri</span>
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-amber-600">
                            <i class="fas fa-chevron-right mx-2 text-[8px]"></i>
                            <span class="uppercase italic underline decoration-2">Kelola Foto</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        
        <div class="flex items-center gap-3">
             <div class="text-right hidden sm:block">
                <?php 
                    // Logika Penentuan Badge Unit Dinamis
                    $unitLabel = $album['kode_jenjang'] ?? $album['jenjang'] ?? 'Global';
                    $badgeStyle = match($unitLabel) {
                        'SD' => 'bg-rose-100 text-rose-800 border-rose-200',
                        'SMP' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'SMA' => 'bg-sky-100 text-sky-800 border-sky-200',
                        default => 'bg-amber-100 text-amber-800 border-amber-200'
                    };
                    $status = $album['status'] ?? 'publik';
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-md border <?= $badgeStyle ?> text-[10px] font-black uppercase tracking-wide shadow-sm">
                    <i class="fas fa-building mr-1.5 opacity-50"></i> Unit: <?= esc($unitLabel) ?>
                </span>
                <div class="text-[9px] text-slate-400 mt-1.5 uppercase font-bold flex justify-end items-center gap-1">
                    Status: 
                    <span class="<?= ($status == 'publik') ? 'text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded' : 'text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded' ?>">
                        <i class="fas <?= ($status == 'publik') ? 'fa-globe' : 'fa-lock' ?> mr-1 text-[8px]"></i>
                        <?= esc(ucfirst($status)) ?>
                    </span>
                </div>
            </div>
            <a href="<?= base_url('app/cms/album') ?>" class="inline-flex items-center px-4 py-2 text-xs font-black uppercase tracking-widest bg-white text-slate-700 border-2 border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition-all shadow-sm active:scale-95 rounded-lg">
                <i class="fas fa-arrow-left mr-2 text-slate-400"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if (session()->getFlashdata('success')) : ?>
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 shadow-sm flex items-center rounded-r-lg animate-fade-in-down">
            <div class="bg-emerald-100 p-2 rounded-full mr-3 text-emerald-600"><i class="fas fa-check"></i></div>
            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wide"><?= session()->getFlashdata('success') ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-600 p-4 shadow-sm flex items-center rounded-r-lg animate-fade-in-down">
            <div class="bg-rose-100 p-2 rounded-full mr-3 text-rose-600"><i class="fas fa-exclamation-triangle"></i></div>
            <p class="text-xs font-bold text-rose-800 uppercase tracking-wide"><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- PANEL UPLOAD (KIRI) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-cloud-upload-alt text-amber-500"></i> Unggah Foto
                    </h3>
                </div>
                <div class="p-6">
                    <form action="<?= base_url('app/cms/album/upload-photos') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_album" value="<?= $album['id'] ?>">
                        
                        <div class="relative group">
                            <label for="filePhotos" class="flex flex-col items-center justify-center w-full h-48 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 transition-colors">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fas fa-images text-4xl text-slate-300 mb-3 group-hover:text-amber-500 transition-colors"></i>
                                    <p class="mb-2 text-xs font-bold text-slate-500 uppercase tracking-wide">Klik untuk pilih foto</p>
                                    <p class="text-[9px] text-slate-400 font-medium italic" id="fileInfo">Support: JPG, PNG (Max 2MB)</p>
                                </div>
                                <input type="file" name="photos[]" id="filePhotos" multiple required class="hidden" accept="image/*">
                            </label>
                        </div>

                        <button type="submit" class="w-full mt-4 py-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-amber-200 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <i class="fas fa-upload"></i> Mulai Upload
                        </button>
                    </form>
                </div>
                <div class="px-6 pb-6 pt-0">
                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-3 flex items-start gap-2">
                        <i class="fas fa-info-circle text-amber-500 mt-0.5 text-xs"></i>
                        <p class="text-[10px] text-amber-800 leading-snug font-medium">
                            Anda dapat memilih banyak foto sekaligus. Pastikan ukuran file tidak terlalu besar untuk performa website.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- GALERI GRID (KANAN) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-images text-indigo-500"></i> Koleksi Foto (<?= count($photos) ?>)
                    </h3>
                </div>
                
                <div class="p-6">
                    <?php if (!empty($photos)) : ?>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($photos as $photo) : ?>
                                <div class="group relative bg-slate-100 rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all">
                                    
                                    <!-- Image Thumbnail -->
                                    <div class="aspect-square bg-slate-200 overflow-hidden">
                                        <img src="<?= base_url('uploads/galeri/photos/' . $photo['file_foto']) ?>" 
                                             alt="<?= esc($photo['caption']) ?>"
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    </div>

                                    <!-- Caption Overlay (Hover) -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-3">
                                        <p class="text-white text-[10px] font-bold truncate">
                                            <?= esc($photo['caption']) ?>
                                        </p>
                                    </div>

                                    <!-- Delete Button (Top Right) -->
                                    <a href="<?= base_url('app/cms/album/delete-photo/' . $photo['id']) ?>" 
                                       onclick="return confirm('Hapus foto ini secara permanen?')"
                                       class="absolute top-2 right-2 w-7 h-7 flex items-center justify-center bg-rose-500/90 text-white rounded-lg shadow-sm backdrop-blur-sm hover:bg-rose-600 transition-colors transform scale-90 opacity-0 group-hover:opacity-100 group-hover:scale-100"
                                       title="Hapus Foto">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="py-12 text-center text-slate-400 flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-xl bg-slate-50">
                            <i class="fas fa-images text-5xl mb-3 opacity-20"></i>
                            <h5 class="text-sm font-bold uppercase tracking-wider text-slate-500">Album Masih Kosong</h5>
                            <p class="text-xs mt-1">Belum ada foto yang diunggah ke album ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // File Input Listener untuk Menampilkan Jumlah File
    document.getElementById('filePhotos').addEventListener('change', function(e) {
        let count = e.target.files.length;
        const info = document.getElementById('fileInfo');
        if (count > 0) {
            info.innerHTML = `<span class="text-amber-600 font-black bg-amber-50 px-2 py-0.5 rounded">${count} Foto Dipilih</span>`;
        } else {
            info.innerHTML = 'Support: JPG, PNG (Max 2MB)';
        }
    });
</script>

<?= $this->endSection() ?>