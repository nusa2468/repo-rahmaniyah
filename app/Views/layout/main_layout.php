<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= esc($title ?? 'ERP SIMS') ?> | Dashboard</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Inter -->
    <link href="<?= base_url('assets/css/fonts-inter.css') ?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="<?= base_url('assets/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet">

    <!-- FIX: Load FontAwesome CDN agar ikon muncul -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" xintegrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Alpine.js -->
    <script src="<?= base_url('assets/js/alpine.min.js') ?>" defer></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0ea5e9',
                        'primary-dark': '#0284c7',
                    }
                }
            }
        }
    </script>

    <style type="text/css">
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
        }

        /* Mobile Safe Padding */
        .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom); }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 font-sans antialiased text-gray-900 dark:text-gray-100"
      x-data="{ sidebarOpen: false }"
      @keydown.escape="sidebarOpen = false"
      @resize.window="if (window.innerWidth >= 1024) sidebarOpen = true">
    <div class="flex h-full overflow-hidden">
        <!-- SIDEBAR -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-gray-900 dark:bg-black transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 border-r border-white/10 shadow-2xl lg:shadow-none overflow-y-auto custom-scrollbar">
            <?= $this->include('layout/_partials/sidebar') ?>
        </aside>

        <!-- Mobile Backdrop -->
        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

        <!-- MAIN WRAPPER -->
        <div class="flex flex-col flex-1 min-w-0">
            <!-- TOPBAR -->
            <header class="h-16 flex items-center justify-between px-4 sm:px-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-white/10 backdrop-blur-sm sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <i class="fas fa-bars text-xl text-gray-600 dark:text-gray-300"></i>
                    </button>

                    <div class="flex items-center gap-3">
                        <span class="text-xs font-black uppercase tracking-widest text-sky-600 bg-sky-100 dark:bg-sky-500/20 px-3 py-1 rounded-lg">
                            ERP PRO
                        </span>
                        <span class="hidden sm:block text-sm text-gray-500 dark:text-gray-400">/</span>
                        <h2 class="hidden sm:block text-sm font-semibold text-gray-700 dark:text-gray-300 truncate">
                            <?= esc($title ?? 'Dashboard') ?>
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <?= $this->include('layout/_partials/topbar') ?>
                </div>
            </header>

            <!-- MAIN CONTENT -->
            <main class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50 dark:bg-gray-950">
                <div class="p-4 sm:p-6 lg:p-8 safe-area-bottom">
                    <div class="max-w-7xl mx-auto">
                        <!-- Flash Messages -->
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-500/10 border-l-4 border-emerald-500 rounded-r-xl flex items-center gap-3 shadow-sm">
                                <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                                <p class="font-semibold text-emerald-800 dark:text-emerald-300">
                                    <?= session()->getFlashdata('success') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border-l-4 border-red-500 rounded-r-xl flex items-center gap-3 shadow-sm">
                                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                                <p class="font-semibold text-red-800 dark:text-red-300">
                                    <?= session()->getFlashdata('error') ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Page Content -->
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
            </main>

            <!-- FOOTER -->
            <footer class="border-t border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 py-4 px-6">
                <?= $this->include('layout/_partials/footer') ?>
            </footer>
        </div>
    </div>

    <!-- Page Specific Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>