<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Templates
     * --------------------------------------------------------------------------
     */
    public array $templates = [
        'default_full'        => 'CodeIgniter\Pager\Views\default_full',
        'default_simple'      => 'CodeIgniter\Pager\Views\default_simple',
        'default_head'        => 'CodeIgniter\Pager\Views\default_head',
        
        // Template Custom Kita
        'tailwind_pagination' => 'pagers/tailwind_pagination',
        
        // Fallback agar tidak error jika ada yang memanggil 'default'
        'default'             => 'pagers/tailwind_pagination',
    ];

    /**
     * --------------------------------------------------------------------------
     * Items Per Page
     * --------------------------------------------------------------------------
     */
    public int $perPage = 10;
}