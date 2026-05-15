<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        
        // Filter Global Admin
        'auth'          => \App\Filters\AuthFilter::class,
        'role'          => \App\Filters\RoleFilter::class,
        'unit_check'    => \App\Filters\UnitFilter::class,

        // REGISTER FILTER SISWA
        'siswa_auth'    => \App\Filters\SiswaAuthFilter::class, 
    ];

    public array $globals = [
        'before' => [
            // 'secureheaders',
            'csrf' => [
                'except' => [
                    'api/*', 
                    'portal/webhooks/*',
                    'callback/*' 
                ]
            ],
            
            // FILTER AUTH GLOBAL (ADMIN)
            'auth' => [
                'except' => [
                    '/',                    
                    'home',
                    'sd', 'smp', 'sma', 
                    'sd/*', 'smp/*', 'sma/*',
                    
                    // Auth Admin Routes
                    'login', 'register', 'auth/*', 
                    'logout', 'forgot-password', 'reset-password',
                    
                    // PENTING: Bypass SEMUA Portal dari Auth Admin
                    // Agar AuthFilter Admin tidak menendang Siswa/Ortu keluar
                    'portal/*', 
                    'portal/siswa/*',
                    'portal/ppdb/*',
                    'portal/affiliated/*',
                    
                    // Assets
                    'assets/*', 'public/*', 'uploads/*', 'favicon.ico'
                ]
            ],
        ],
        'after' => [
            'toolbar',
        ],
    ];

    public array $methods = [];

    public array $filters = [
        // Terapkan siswa_auth HANYA pada dashboard siswa
        'siswa_auth' => [
            'before' => [
                'portal/siswa/dashboard',
                'portal/siswa/dashboard/*',
                'portal/siswa/profil',
                'portal/siswa/jadwal',
                // Masukkan rute siswa lainnya di sini
            ]
        ]
    ];
}