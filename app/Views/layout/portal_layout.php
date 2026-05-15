<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Pendaftaran Siswa Baru - <?= esc($settings['nama_sekolah'] ?? 'ERP Sekolah') ?>">
    <meta name="author" content="Tim Pengembang">
    <title><?= esc($title ?? 'Portal Sekolah') ?></title>
    
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    
    <link href="<?= base_url('assets/css/fonts-inter.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">
    
    <!-- FIX: Load FontAwesome CDN agar ikon muncul -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" xintegrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="<?= base_url('assets/js/alpine.min.js') ?>" defer></script> 
    
    <style type="text/css">
        /* Tailwind v4 CSS-First Configuration */
        @theme {
            --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;
            --color-brand: var(--color-blue-600);
            --color-brand-dark: var(--color-blue-700);
            
            /* Custom Animation for CI4 Flashdata */
            @keyframes slide-out {
                to { opacity: 0; transform: translateY(-1rem); }
            }
        }

        [x-cloak] { display: none !important; }
        
        /* Modern Scrollbar menggunakan property standar */
        @supports (scrollbar-width: thin) {
            .custom-scrollbar {
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 transparent;
            }
        }

        /* Webkit scrollbar fallback */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { 
            background: #cbd5e1; 
            border-radius: 10px; 
        }

        .alert-hide-animation {
            animation: slide-out 0.5s ease-in forwards;
        }
    </style>
    
    <?= $this->renderSection('header') ?>
</head>

<body class="h-full bg-white dark:bg-gray-950 font-sans antialiased text-gray-900 dark:text-gray-100 selection:bg-blue-100 dark:selection:bg-blue-900/30">

    <nav x-data="{ mobileMenuOpen: false }" 
         class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-white/10 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?= base_url('/') ?>" class="flex items-center gap-3 group focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded-lg">
                        <div class="bg-blue-600 p-2 rounded-xl group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-blue-500/20">
                            <i class="fas fa-school text-white"></i>
                        </div>
                        <span class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-blue-600 to-indigo-500 bg-clip-text text-transparent">
                            <?= esc($settings['nama_sekolah'] ?? 'ERP Sekolah V9') ?>
                        </span>
                    </a>
                </div>

                <div class="hidden md:flex items-center space-x-6">
                    <?php $current_url = current_url(); ?>
                    <a href="<?= base_url('portal/ppdb/home') ?>#info" class="text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Home</a>
                    <a href="<?= base_url('portal/ppdb/login') ?>" class="text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400 transition-colors">PPDB Login</a>
                    
                    <div class="h-5 w-px bg-gray-200 dark:bg-gray-800"></div>
                    
                    <a href="<?= base_url('login') ?>" class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-blue-500/20 active:scale-95">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login Staf
                    </a>
                </div>

                <div class="flex md:hidden items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            aria-label="Toggle Menu">
                        <i class="fas fa-lg" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars-staggered'"></i>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileMenuOpen" 
             x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-white/5 shadow-xl">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="<?= base_url('/') ?>#berita" class="block px-3 py-3 rounded-xl text-base font-medium hover:bg-blue-50 dark:hover:bg-gray-800 transition-colors">Berita</a>
                <a href="<?= base_url('/') ?>#info" class="block px-3 py-3 rounded-xl text-base font-medium hover:bg-blue-50 dark:hover:bg-gray-800 transition-colors">Pengumuman</a>
                <a href="<?= base_url('portal/ppdb/login') ?>" class="block px-3 py-3 rounded-xl text-base font-medium hover:bg-blue-50 dark:hover:bg-gray-800 transition-colors">PPDB Online</a>
                <div class="pt-2">
                    <a href="<?= base_url('login') ?>" class="block w-full text-center px-3 py-3 rounded-xl text-base font-bold bg-blue-600 text-white">Login Staf</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert-box mb-6 flex items-center p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-2xl text-emerald-700 dark:text-emerald-400 shadow-sm transition-all duration-500">
                    <div class="bg-emerald-500 p-1.5 rounded-full mr-3">
                        <i class="fas fa-check text-xs text-white"></i>
                    </div>
                    <span class="text-sm font-semibold"><?= session()->getFlashdata('success') ?></span>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert-box mb-6 flex items-center p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl text-red-700 dark:text-red-400 shadow-sm transition-all duration-500">
                    <div class="bg-red-500 p-1.5 rounded-full mr-3">
                        <i class="fas fa-exclamation text-xs text-white"></i>
                    </div>
                    <span class="text-sm font-semibold"><?= session()->getFlashdata('error') ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="animate-in fade-in slide-in-from-bottom-2 duration-700 ease-out">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <footer class="bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-white/5 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Copyright &copy; <?= date('Y') ?> 
                        <span class="font-bold text-gray-900 dark:text-white"><?= esc($settings['nama_sekolah'] ?? 'ERP Sekolah') ?></span>. 
                        <span class="hidden sm:inline">Seluruh Hak Cipta Dilindungi.</span>
                    </p>
                </div>
                <div class="flex items-center space-x-5 text-gray-400">
                    <a href="#" class="hover:text-blue-600 transition-all hover:scale-110"><i class="fab fa-lg fa-facebook"></i></a>
                    <a href="#" class="hover:text-blue-400 transition-all hover:scale-110"><i class="fab fa-lg fa-twitter"></i></a>
                    <a href="#" class="hover:text-pink-600 transition-all hover:scale-110"><i class="fab fa-lg fa-instagram"></i></a>
                    <a href="#" class="hover:text-red-600 transition-all hover:scale-110"><i class="fab fa-lg fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <?= $this->renderSection('scripts') ?>
    
    <script>
        /**
         * Auto-hide Flash Messages
         * Menggunakan class 'alert-box' untuk seleksi yang lebih bersih
         */
        document.addEventListener('DOMContentLoaded', () => {
            const alerts = document.querySelectorAll('.alert-box');
            alerts.forEach(el => {
                setTimeout(() => {
                    el.classList.add('alert-hide-animation');
                    el.addEventListener('animationend', () => el.remove());
                }, 5000);
            });
        });
    </script>
</body>
</html>