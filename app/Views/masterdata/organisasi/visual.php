<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<?php
    // --- 1. PREPARASI DATA & FILTER ---
    $role = strtolower(session()->get('role_name') ?? session()->get('role') ?? '');
    
    $request = \Config\Services::request();
    $filter_jenjang  = $request->getGet('kode_jenjang') ?? '';
    // [BARU] Tangkap opsi include yayasan
    $include_yayasan = $request->getGet('with_parent') == '1';

    // Helper warna
    if (!function_exists('getJenjangColor')) {
        function getJenjangColor($kode) {
            $kode = strtoupper($kode ?? '');
            return match ($kode) {
                'GLOBAL', 'PUSAT', 'YAYASAN' => 'bg-slate-800 text-white',
                'SD', 'MI'        => 'bg-rose-500 text-white',
                'SMP', 'MTS'      => 'bg-sky-600 text-white',
                'SMA', 'SMK', 'MA' => 'bg-indigo-500 text-white',
                'TK', 'PAUD'      => 'bg-emerald-500 text-white',
                default           => 'bg-gray-100 text-gray-600 border border-gray-200',
            };
        }
    }

    // Ambil Data Jenjang (Khusus Superadmin)
    $listJenjang = [];
    if (in_array($role, ['superadmin', 'yayasan'])) {
        try {
            $jenjangModel = new \App\Models\JenjangModel();
            $listJenjang = $jenjangModel->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        } catch (\Throwable $e) {}
    }

    // Filter Data Organisasi
    if (!empty($filter_jenjang) && !empty($organisasi)) {
        $organisasi = array_filter($organisasi, function($item) use ($filter_jenjang, $include_yayasan) {
            $k = strtoupper(is_object($item) ? $item->kode_jenjang : $item['kode_jenjang']);
            $f = strtoupper($filter_jenjang);

            // 1. Ambil data unit yang sesuai filter
            if ($k === $f) return true;

            // 2. Ambil data Yayasan jika opsi dicentang (agar hierarki ke atas terlihat)
            if ($include_yayasan && in_array($k, ['GLOBAL', 'PUSAT', 'YAYASAN'])) return true;

            return false;
        });
        $organisasi = array_values($organisasi); // Reindex
    }

    // --- 2. LOGIKA MEMBANGUN TREE (HIERARKI CERDAS) ---
    $treeData = [];
    $refs = [];

    if (!empty($organisasi)) {
        // [Langkah 1] Indexing & Normalisasi
        usort($organisasi, function($a, $b) {
            $la = is_object($a) ? $a->level_jabatan : $a['level_jabatan'];
            $lb = is_object($b) ? $b->level_jabatan : $b['level_jabatan'];
            return $la <=> $lb;
        });

        foreach ($organisasi as $row) {
            $obj = (object)$row;
            $obj->children = [];
            $refs[$obj->id] = $obj;
        }

        // [Langkah 2] Linking Parent-Child
        $roots = [];
        foreach ($refs as $id => $node) {
            if (!empty($node->parent_id) && isset($refs[$node->parent_id])) {
                $refs[$node->parent_id]->children[] = $node;
            } else {
                $roots[] = $node; // Kandidat Root
            }
        }

        // [Langkah 3] Post-Processing: Fix Hierarki Yayasan
        if (empty($filter_jenjang) || $include_yayasan) {
            $targetParent = null;

            // A. Cari "Ketua Pengurus" atau "Ketua Umum" di Global
            foreach ($refs as $node) {
                $jenjang = strtoupper($node->kode_jenjang ?? '');
                if (in_array($jenjang, ['GLOBAL', 'PUSAT', 'YAYASAN'])) {
                    $jabatan = strtolower($node->nama_jabatan ?? '');
                    if (str_contains($jabatan, 'pengurus') || str_contains($jabatan, 'ketua umum') || str_contains($jabatan, 'eksekutif')) {
                        $targetParent = $node;
                        break; 
                    }
                }
            }

            // B. Fallback: Cari Root Global sembarang
            if (!$targetParent) {
                foreach ($roots as $node) {
                    if (in_array(strtoupper($node->kode_jenjang ?? ''), ['GLOBAL', 'PUSAT', 'YAYASAN'])) {
                        $targetParent = $node;
                        break;
                    }
                }
            }

            // C. Proses Pemindahan Anak Unit ke Bawah Yayasan
            if ($targetParent) {
                $finalRoots = [];
                foreach ($roots as $node) {
                    if ($node->id === $targetParent->id) { // Jangan pindahkan diri sendiri
                        $finalRoots[] = $node;
                        continue;
                    }
                    // Cek apakah ini Node Unit
                    $jenjangNode = strtoupper($node->kode_jenjang ?? '');
                    if (!in_array($jenjangNode, ['GLOBAL', 'PUSAT', 'YAYASAN'])) {
                        $targetParent->children[] = $node; // Adopsi
                    } else {
                        $finalRoots[] = $node; 
                    }
                }
                $treeData = $finalRoots;
            } else {
                $treeData = $roots;
            }
        } else {
            $treeData = $roots;
        }
    }
?>

<style>
    /* Container Styling */
    .org-tree-canvas { background: #f8fafc; border-radius: 1.5rem; padding: 4rem 2rem; min-height: 80vh; overflow-x: auto; display: flex; justify-content: center; align-items: flex-start; border: 1px solid #eef2f6; }
    
    /* Tree Logic */
    .tree { white-space: nowrap; min-width: 100%; display: flex; justify-content: center; }
    .tree ul { padding-top: 40px; position: relative; transition: all 0.5s; display: flex; justify-content: center; }
    .tree li { float: left; text-align: center; list-style-type: none; position: relative; padding: 40px 10px 0 10px; transition: all 0.5s; }
    
    /* Connector Lines */
    .tree li::before, .tree li::after { content: ''; position: absolute; top: 0; right: 50%; border-top: 2px solid #cbd5e1; width: 50%; height: 40px; z-index: 1; }
    .tree li::after { right: auto; left: 50%; border-left: 2px solid #cbd5e1; }
    .tree li:only-child::after, .tree li:only-child::before { display: none; }
    .tree li:only-child { padding-top: 0; }
    .tree li:first-child::before, .tree li:last-child::after { border: 0 none; }
    .tree li:last-child::before { border-right: 2px solid #cbd5e1; border-radius: 0 10px 0 0; }
    .tree li:first-child::after { border-radius: 10px 0 0 0; }
    .tree ul ul::before { content: ''; position: absolute; top: 0; left: 50%; border-left: 2px solid #cbd5e1; width: 0; height: 40px; margin-left: -1px; }
    
    /* Node Card */
    .node-card { display: inline-block; width: 220px; background: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 16px 12px; position: relative; z-index: 2; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.05); white-space: normal; }
    .node-card:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15); border-color: #94a3b8; z-index: 10; }
    .node-accent { position: absolute; top: 0; left: 50%; transform: translateX(-50%); height: 3px; width: 50px; border-radius: 0 0 10px 10px; }
    .avatar-wrapper { width: 32px; height: 32px; margin: 0 auto 6px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #64748b; border: 2px solid white; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    
    /* Header */
    .hero-compact { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 1.5rem 2.5rem; border-radius: 1.5rem; margin-bottom: 2rem; }
</style>

<div class="container-fluid mb-10 px-4">
    <!-- Header Aligned -->
    <div class="hero-compact shadow-xl flex flex-col md:flex-row items-center justify-between gap-4 mb-6 p-6 bg-gradient-to-br from-indigo-900 to-slate-900 rounded-2xl shadow-xl text-white border border-white/10">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-white/10 border border-white/20 backdrop-blur-sm">
                <i class="fas fa-sitemap text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tight">Struktur Organisasi</h1>
                <p class="text-xs text-indigo-200 font-medium uppercase tracking-widest mt-0.5">Visualisasi Hirarki & Komando</p>
                <?php if(!empty($filter_jenjang)): ?>
                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[9px] font-bold bg-white/20 border border-white/10">
                        Filter: <?= esc($filter_jenjang) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Group: Container dengan tinggi fix 40px -->
        <div class="flex flex-wrap items-center gap-2" style="height: 40px;">
            
            <!-- [OPSI] Checkbox Include Yayasan -->
            <form action="" method="get" class="flex items-center gap-2 m-0 p-0" style="height: 40px;">
                <?php if (!empty($filter_jenjang)): ?>
                    <input type="hidden" name="kode_jenjang" value="<?= esc($filter_jenjang) ?>">
                <?php endif; ?>

                <label class="flex items-center gap-2 cursor-pointer bg-white/10 px-3 rounded-xl border border-white/20 hover:bg-white/20 transition-all shadow-sm box-border" style="height: 40px;">
                    <input type="checkbox" name="with_parent" value="1" <?= $include_yayasan ? 'checked' : '' ?> 
                           onchange="this.form.submit()" 
                           class="rounded text-indigo-600 focus:ring-0 cursor-pointer h-4 w-4 bg-white/80 border-none">
                    <span class="text-[10px] font-bold text-white uppercase tracking-wider whitespace-nowrap">
                        + Struktur Yayasan
                    </span>
                </label>
            </form>

            <!-- Dropdown Unit (Khusus Superadmin) -->
            <?php if (in_array($role, ['superadmin', 'yayasan']) && !empty($listJenjang)): ?>
                <form action="" method="get" class="flex items-center m-0 p-0" style="height: 40px;">
                    <input type="hidden" name="with_parent" value="<?= $include_yayasan ? '1' : '0' ?>">
                    <div class="relative w-full group" style="height: 40px;">
                        <select name="kode_jenjang" onchange="this.form.submit()" 
                                class="w-full pl-3 pr-8 bg-white/10 border border-white/20 text-white text-xs font-bold uppercase tracking-wide rounded-xl shadow-lg focus:ring-2 focus:ring-white/30 outline-none cursor-pointer hover:bg-white/10 transition-colors appearance-none flex items-center box-border"
                                style="height: 40px !important;">
                            <option value="" class="text-gray-800">Semua Unit</option>
                            <?php foreach ($listJenjang as $j): ?>
                                <?php 
                                    $val = is_array($j) ? ($j['kode_jenjang'] ?? '-') : ($j->kode_jenjang ?? '-');
                                    $lbl = is_array($j) ? ($j['nama_jenjang'] ?? 'Unit ' . $val) : ($j->nama_jenjang ?? 'Unit ' . $val);
                                    $sel = ($filter_jenjang === $val) ? 'selected' : '';
                                ?>
                                <option value="<?= esc($val) ?>" <?= $sel ?> class="text-gray-800"><?= esc($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-white/50">
                            <i class="fas fa-chevron-down text-[9px]"></i>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <div class="hidden xl:flex items-center gap-4 mx-2 border-r border-slate-700 pr-4" style="height: 24px;">
                <div class="flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase">
                    <span class="h-2 w-2 rounded-full bg-slate-800"></span> Yayasan
                </div>
                <div class="flex items-center gap-2 text-[9px] font-black text-slate-400 uppercase">
                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span> Unit
                </div>
            </div>

            <!-- Tombol Mode Tabel -->
            <a href="<?= base_url('app/masterdata/organisasi') ?>" 
               class="inline-flex items-center justify-center gap-2 px-4 rounded-xl bg-white/10 border border-white/20 text-white text-[10px] font-black uppercase tracking-widest hover:bg-white/20 transition-all shadow-lg active:scale-95 no-underline box-border leading-none"
               style="height: 40px !important;">
                <i class="fas fa-table mr-1"></i> Mode Tabel
            </a>
        </div>
    </div>

    <!-- Tree Content -->
    <div class="org-tree-canvas shadow-inner">
        <?php if(empty($treeData)): ?>
            <div class="flex flex-col items-center justify-center opacity-40 py-20">
                <i class="fas fa-network-wired text-4xl mb-4 text-slate-300"></i>
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Tidak ada data untuk ditampilkan</p>
                <p class="text-xs text-slate-400 mt-2">Coba centang "+ Struktur Yayasan" atau pilih unit lain.</p>
            </div>
        <?php else: ?>
            <div class="tree">
                <?php 
                    // Fungsi Render Rekursif
                    function renderOrgTree($nodes) {
                        if (empty($nodes)) return;
                        
                        echo "<ul>";
                        foreach ($nodes as $node) {
                            $isYayasan = (strtoupper($node->kode_jenjang ?? '') == 'GLOBAL');
                            $colorClass = getJenjangColor($node->kode_jenjang);
                            
                            echo "<li>";
                            ?>
                            <div class="node-card group">
                                <div class="node-accent <?= $isYayasan ? 'bg-slate-800' : 'bg-indigo-500' ?>"></div>
                                
                                <div class="flex justify-between items-start mb-2">
                                    <div class="avatar-wrapper">
                                        <i class="fas <?= $isYayasan ? 'fa-landmark' : 'fa-user-tie' ?> text-xs"></i>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded text-[7px] font-black uppercase <?= $colorClass ?>">
                                        <?= esc($node->kode_jenjang ?? '-') ?>
                                    </span>
                                </div>

                                <div class="text-[9px] font-black text-indigo-600 uppercase tracking-tight mb-0.5 truncate leading-tight">
                                    <?= esc($node->nama_jabatan ?? 'Jabatan') ?>
                                </div>
                                
                                <div class="text-[11px] font-bold text-slate-800 leading-tight mb-2 line-clamp-2 min-h-[2.2em]">
                                    <?= esc($node->nama_display ?? '-') ?>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                    <span class="text-[8px] font-bold text-slate-400">
                                        <?= $node->nip_display ?: '-' ?>
                                    </span>
                                    
                                    <a href="<?= base_url('app/masterdata/organisasi/edit/' . ($node->id ?? 0)) ?>" 
                                       class="text-slate-300 hover:text-amber-500 transition-colors" title="Edit">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                            <?php
                            // REKURSIF: Render Anak
                            if (!empty($node->children)) {
                                renderOrgTree($node->children);
                            }
                            echo "</li>";
                        }
                        echo "</ul>";
                    }

                    // Render Pohon
                    renderOrgTree($treeData);
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>