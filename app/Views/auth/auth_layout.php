<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Informasi Manajemen Sekolah">
    <meta name="author" content="Tim Pengembang">
    <title><?= esc($title ?? 'ERP Sekolah V9') ?></title>
    
    <!-- Tailwind CSS v4 CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Inter (Lebih modern dari Nunito) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
            /* Warna Dinamis Berdasarkan Jenjang jika tersedia, default ke Blue */
            --color-auth-primary: <?= $theme_color ?? '#2563eb' ?>;
        }

        @layer base {
            body {
                @apply bg-gray-50 text-gray-900 antialiased font-sans;
            }
        }

        /* Background Pattern Minimalis */
        .auth-bg {
            background-color: #f8f9fc;
            background-image: radial-gradient(var(--color-auth-primary) 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            background-opacity: 0.05;
        }
    </style>
</head>

<body class="auth-bg">

    <div class="min-h-screen flex items-center justify-center p-4 md:p-8">
        <div class="w-full max-w-5xl">
            <!-- Container Utama -->
            <div class="bg-white dark:bg-gray-900 rounded-[2rem] shadow-2xl overflow-hidden border border-gray-100 dark:border-white/5">
                
                <div class="flex flex-col lg:flex-row min-h-[600px]">
                    
                    <!-- Content Area (Inject dari Login/Register View) -->
                    <!-- Biasanya col-lg-6 atau col-lg-12 di Bootstrap -->
                    <?= $this->renderSection('content') ?>

                </div>
            </div>

            <!-- Footer Small -->
            <div class="mt-8 text-center text-gray-400 text-sm font-medium uppercase tracking-widest">
                &copy; <?= date('Y') ?> <?= esc($settings['nama_sekolah'] ?? 'SIMS V9') ?> 
                <span class="mx-2">•</span> 
                Smart Education System
            </div>
        </div>
    </div>

    <!-- Script Support jika dibutuhkan -->
    <script>
        // Efek transisi halus saat form muncul
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.querySelector('.bg-white');
            card.classList.add('animate-in', 'fade-in', 'zoom-in-95', 'duration-700');
        });
    </script>
</body>

</html>