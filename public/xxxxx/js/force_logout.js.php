// Script ini memastikan tautan logout Topbar berfungsi, mengatasi masalah cache/intervensi JS.
document.addEventListener('DOMContentLoaded', function() {
    // ID ini harus sesuai dengan ID pada tautan Logout di layout/_partials/topbar.php
    const logoutLink = document.getElementById('logoutTopbar');
    
    if (logoutLink) {
        // Ambil URL yang benar dari atribut href (yang seharusnya base_url('logout'))
        const logoutUrl = logoutLink.getAttribute('href'); 
        
        logoutLink.addEventListener('click', function(e) {
            // Mencegah tindakan default tautan (misalnya, melompat ke # atau diblokir Bootstrap)
            e.preventDefault(); 
            
            // Arahkan browser secara langsung ke URL logout
            window.location.href = logoutUrl;
        });
    }
});