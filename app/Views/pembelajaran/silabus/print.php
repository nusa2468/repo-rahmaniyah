<?php
// ==================================================================================
// [SIMULASI CONTROLLER] 
// Bagian ini mensimulasikan data yang dikirim oleh Controller ke View.
// Dalam aplikasi CodeIgniter 4 sebenarnya, hapus blok ini dan kirim data $sekolah, 
// $header_silabus, & $data_silabus dari method controller Anda.
// ==================================================================================

// Simulasi Data Sekolah (Output dari SettingsModel::getSettingsAsArray())
if (!isset($sekolah)) {
    $sekolah = [
        'instansi_1'     => 'PEMERINTAH PROVINSI JAWA BARAT',
        'instansi_2'     => 'DINAS PENDIDIKAN DAN KEBUDAYAAN',
        'nama_sekolah'   => 'SMA NEGERI 1 NUSANTARA',
        'alamat_jalan'   => 'Jl. Pendidikan No. 123, Kel. Merdeka, Kec. Belajar',
        'alamat_kota'    => 'Kota Ilmu - 40123',
        'website'        => 'www.sman1nusantara.sch.id',
        'email'          => 'info@sman1nusantara.sch.id',
        'kepala_sekolah' => 'DR. BUDI SANTOSO, M.Pd.',
        'nip_kepsek'     => '19700101 199503 1 002',
        'logo_url'       => 'https://via.placeholder.com/80?text=Logo' // Opsional
    ];
}

// Simulasi Header Silabus
if (!isset($header_silabus)) {
    $header_silabus = [
        'tahun_ajaran'   => '2024 / 2025',
        'kelas'          => 'X (Sepuluh) / Ganjil',
        'mata_pelajaran' => 'Teknologi Informasi',
        'alokasi_waktu'  => '3 JP x 45 Menit',
        'ki'             => 'Memahami, menerapkan, menganalisis pengetahuan faktual, konseptual, prosedural berdasarkan rasa ingin tahunya tentang ilmu pengetahuan, teknologi, seni, budaya, dan humaniora dengan wawasan kemanusiaan, kebangsaan, kenegaraan, dan peradaban terkait penyebab fenomena dan kejadian, serta menerapkan pengetahuan prosedural pada bidang kajian yang spesifik sesuai dengan bakat dan minatnya untuk memecahkan masalah.',
        'guru_mapel'     => 'SITI AMINAH, S.Kom.',
        'nip_guru'       => '19850505 201001 2 005',
        'tgl_sah'        => date('d F Y'),
        'kota_sah'       => 'Bandung'
    ];
}

// Simulasi Data Tabel (Rows)
if (!isset($data_silabus)) {
    $data_silabus = [
        [
            'kd' => '3.1 Mengenal fungsi perangkat keras dan sistem operasi.',
            'materi' => "<strong>Sistem Komputer:</strong><br>- Hardware<br>- Software",
            'ipk' => "3.1.1 Menjelaskan fungsi CPU.<br>3.1.2 Mengklasifikasikan I/O device.",
            'kegiatan_daring' => "<strong>Metode:</strong> Asinkron (LMS)<br><strong>Aktivitas:</strong> Menyimak video & modul PDF.",
            'kegiatan_luring' => "<strong>Metode:</strong> Tatap Muka<br><strong>Aktivitas:</strong> Praktik bongkar pasang CPU.",
            'penilaian' => "Tes Tulis, Unjuk Kerja",
            'waktu' => "3 JP",
            'sumber' => "Buku Paket, Internet"
        ],
        [
            'kd' => '3.2 Menerapkan logika dan algoritma komputer.',
            'materi' => "<strong>Logika:</strong><br>- Flowchart<br>- Pseudocode",
            'ipk' => "3.2.1 Membuat flowchart sederhana.",
            'kegiatan_daring' => "<strong>Metode:</strong> Virtual Meet<br><strong>Aktivitas:</strong> Diskusi studi kasus.",
            'kegiatan_luring' => "<strong>Metode:</strong> Tatap Muka<br><strong>Aktivitas:</strong> Presentasi kelompok.",
            'penilaian' => "Portofolio",
            'waktu' => "6 JP",
            'sumber' => "Modul Logika"
        ]
    ];
}
// ==================================================================================
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Silabus - <?= esc($header_silabus['mata_pelajaran']) ?></title>
    
    <!-- Tailwind CSS (Diperlukan karena tidak menggunakan Main Layout) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Styling Khusus Cetak -->
    <style>
        /* Menggunakan Font Serif (Times New Roman) agar terlihat formal seperti dokumen Word */
        .font-serif-print { 
            font-family: 'Times New Roman', Times, serif; 
        }
        
        /* CSS Khusus saat Print Dialog muncul */
        @media print {
            @page { 
                size: A4 landscape; /* Kertas Landscape untuk tabel lebar */
                margin: 10mm; 
            }
            body { 
                background-color: white !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            /* Sembunyikan elemen navigasi/tombol saat dicetak */
            .no-print, nav, footer, header.main-header { 
                display: none !important; 
            }
            /* Reset container agar full width di kertas */
            .page-container { 
                box-shadow: none !important; 
                margin: 0 !important; 
                width: 100% !important; 
                max-width: 100% !important; 
                border: none !important; 
                padding: 0 !important;
            }
            /* Paksa background warna tabel tercetak */
            th { 
                background-color: #e5e7eb !important; 
                -webkit-print-color-adjust: exact; 
            } 
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans">

    <div class="container mx-auto py-6">
        
        <!-- Tombol Aksi (Hanya tampil di Layar, Hilang saat diprint) -->
        <div class="flex justify-end mb-4 no-print gap-2">
            <a href="javascript:history.back()" class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 transition flex items-center gap-2">
                <span>&larr; Kembali</span>
            </a>
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Dokumen (PDF)
            </button>
        </div>

        <!-- Container Kertas A4 -->
        <div class="page-container bg-white text-gray-900 font-serif-print p-8 md:p-12 shadow-xl border border-gray-200 mx-auto max-w-[297mm] min-h-[210mm] relative">

            <!-- 1. KOP SURAT (Dinamis dari SettingsModel) -->
            <header class="border-b-4 border-double border-black pb-4 mb-6 text-center relative">
                <?php if(!empty($sekolah['logo_url'])): ?>
                    <!-- Logo Sekolah -->
                    <img src="<?= $sekolah['logo_url'] ?>" alt="Logo" class="absolute left-0 top-0 h-24 w-auto object-contain hidden md:block">
                <?php endif; ?>

                <h3 class="text-lg font-bold uppercase tracking-wide leading-tight">
                    <?= esc($sekolah['instansi_1'] ?? '') ?>
                </h3>
                <h3 class="text-lg font-bold uppercase tracking-wide leading-tight">
                    <?= esc($sekolah['instansi_2'] ?? '') ?>
                </h3>
                <h1 class="text-3xl font-bold uppercase tracking-wider mt-1 leading-tight">
                    <?= esc($sekolah['nama_sekolah'] ?? 'NAMA SEKOLAH') ?>
                </h1>
                <p class="text-sm mt-1">
                    <?= esc($sekolah['alamat_jalan'] ?? '') ?>, <?= esc($sekolah['alamat_kota'] ?? '') ?>
                </p>
                <p class="text-sm italic">
                    Website: <?= esc($sekolah['website'] ?? '-') ?> | Email: <?= esc($sekolah['email'] ?? '-') ?>
                </p>
            </header>

            <!-- 2. JUDUL DOKUMEN -->
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold uppercase underline decoration-2 underline-offset-4">
                    SILABUS PEMBELAJARAN (HYBRID LEARNING)
                </h2>
                <p class="text-sm mt-1 font-semibold">
                    Tahun Pelajaran <?= esc($header_silabus['tahun_ajaran']) ?>
                </p>
            </div>

            <!-- 3. IDENTITAS DOKUMEN -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1 mb-6 text-sm">
                <div class="flex">
                    <span class="w-40 font-bold shrink-0">Satuan Pendidikan</span>
                    <span>: <?= esc($sekolah['nama_sekolah']) ?></span>
                </div>
                <div class="flex">
                    <span class="w-40 font-bold shrink-0">Kelas / Semester</span>
                    <span>: <?= esc($header_silabus['kelas']) ?></span>
                </div>
                <div class="flex">
                    <span class="w-40 font-bold shrink-0">Mata Pelajaran</span>
                    <span>: <?= esc($header_silabus['mata_pelajaran']) ?></span>
                </div>
                <div class="flex">
                    <span class="w-40 font-bold shrink-0">Alokasi Waktu</span>
                    <span>: <?= esc($header_silabus['alokasi_waktu']) ?></span>
                </div>
                <div class="flex col-span-1 md:col-span-2 mt-1">
                    <span class="w-40 font-bold shrink-0">Kompetensi Inti (KI)</span>
                    <span class="flex-1 text-justify align-top leading-tight">: <?= esc($header_silabus['ki']) ?></span>
                </div>
            </div>

            <!-- 4. TABEL SILABUS -->
            <div class="w-full">
                <table class="w-full border-collapse border border-black text-xs md:text-sm">
                    <thead>
                        <tr class="bg-gray-200 text-center font-bold">
                            <th class="border border-black p-2 w-[5%]">No</th>
                            <th class="border border-black p-2 w-[15%]">Kompetensi Dasar (KD)</th>
                            <th class="border border-black p-2 w-[15%]">Materi Pokok</th>
                            <th class="border border-black p-2 w-[12%]">Indikator (IPK)</th>
                            <th class="border border-black p-2 w-[23%]">Kegiatan Pembelajaran (Hybrid)</th>
                            <th class="border border-black p-2 w-[10%]">Penilaian</th>
                            <th class="border border-black p-2 w-[8%]">Waktu</th>
                            <th class="border border-black p-2 w-[12%]">Sumber Belajar</th>
                        </tr>
                    </thead>
                    <tbody class="align-top">
                        <?php if (empty($data_silabus)): ?>
                            <tr>
                                <td colspan="8" class="text-center p-4 italic text-gray-500">Data silabus belum tersedia.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($data_silabus as $row): ?>
                            <tr>
                                <td class="border border-black p-2 text-center"><?= $no++ ?></td>
                                <td class="border border-black p-2"><?= $row['kd'] ?></td>
                                <td class="border border-black p-2"><?= $row['materi'] // Gunakan raw HTML jika data dari editor teks ?></td>
                                <td class="border border-black p-2"><?= $row['ipk'] ?></td>
                                <td class="border border-black p-2">
                                    <!-- Layout Hybrid untuk Cetak -->
                                    <div class="mb-3">
                                        <div class="font-bold underline mb-1 text-[10px]">DARING (Online):</div>
                                        <div class="pl-2 border-l border-gray-400">
                                            <?= $row['kegiatan_daring'] ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold underline mb-1 text-[10px]">LURING (Offline):</div>
                                        <div class="pl-2 border-l border-gray-400">
                                            <?= $row['kegiatan_luring'] ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="border border-black p-2"><?= $row['penilaian'] ?></td>
                                <td class="border border-black p-2 text-center"><?= $row['waktu'] ?></td>
                                <td class="border border-black p-2"><?= $row['sumber'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- 5. LEGALITAS / TANDA TANGAN (Footer Halaman) -->
            <div class="mt-8 grid grid-cols-2 gap-10 page-break-inside-avoid">
                <!-- Kolom Kiri: Kepala Sekolah -->
                <div class="text-center flex flex-col items-center">
                    <div class="mb-20">
                        <p>Mengetahui,</p>
                        <p class="font-bold">Kepala Sekolah</p>
                    </div>
                    <div>
                        <p class="font-bold underline uppercase">
                            <?= esc($sekolah['kepala_sekolah'] ?? '..........................') ?>
                        </p>
                        <p>NIP. <?= esc($sekolah['nip_kepsek'] ?? '-'); ?></p>
                    </div>
                </div>

                <!-- Kolom Kanan: Guru Mata Pelajaran -->
                <div class="text-center flex flex-col items-center">
                    <div class="mb-20">
                        <p>
                            <?= esc($header_silabus['kota_sah'] ?? 'Bandung') ?>, 
                            <?= esc($header_silabus['tgl_sah'] ?? date('d F Y')) ?>
                        </p>
                        <p class="font-bold">Guru Mata Pelajaran</p>
                    </div>
                    <div>
                        <p class="font-bold underline uppercase">
                            <?= esc($header_silabus['guru_mapel'] ?? '..........................') ?>
                        </p>
                        <p>NIP. <?= esc($header_silabus['nip_guru'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>