<!-- 
    PENTING: Jangan gunakan class 'fixed' atau script deteksi tinggi manual di sini.
    Struktur flex-col di main_layout.php sudah otomatis menangani posisi footer.
-->
<footer class="w-full bg-white dark:bg-gray-900/50 border-t border-gray-200 dark:border-white/5 transition-colors">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0">
        
        <div class="flex items-center space-x-2">
            <span class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                &copy; <?= date('Y'); ?> <span class="text-gray-900 dark:text-white font-bold">ERP Sekolah VPro</span>.
            </span>
        </div>

        <nav class="flex space-x-6">
            <a href="#" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 hover:text-sky-600 dark:hover:text-sky-400 transition-colors">
                Privasi
            </a>
            <a href="#" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 hover:text-sky-600 dark:hover:text-sky-400 transition-colors">
                Bantuan
            </a>
        </nav>
        
    </div>
    
    <!-- Memberikan sedikit padding bawah ekstra untuk mobile browser bars -->
    <div class="h-[env(safe-area-inset-bottom)]"></div>
</footer>