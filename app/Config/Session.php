<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Session extends BaseConfig
{
    /**
     * Session Driver
     * Menggunakan DatabaseHandler untuk menyimpan sesi di database.
     * @var string
     */
    public string $driver = \CodeIgniter\Session\Handlers\DatabaseHandler::class;

    /**
     * Session Cookie Name
     * @var string
     */
    public string $cookieName = 'ci_session';

    /**
     * Session Expiration (dalam detik)
     * 7200 detik = 2 jam
     * @var int
     */
    public int $expiration = 7200;

    /**
     * Session Save Path
     * Untuk DatabaseHandler, ini adalah nama tabel.
     * @var string
     */
    public string $savePath = 'ci_sessions';

    /**
     * Session Match IP
     * @var bool
     */
    public bool $matchIP = false;

    /**
     * Session Time to Update
     * @var int
     */
    public int $timeToUpdate = 300;

    /**
     * Session Regenerate Destroy
     * @var bool
     */
    public bool $regenerateDestroy = false;

    /**
     * Session DB Group
     * @var string
     */
    public string $DBGroup = 'default';
    
    // --- PENGATURAN PENTING UNTUK STABILITAS ---
    
    public string $cookieDomain = '';
    public string $cookiePath = '/';
    public bool $cookieSecure = false;
    public bool $cookieHTTPOnly = true; 
    
    /**
     * Pengaturan SameSite untuk cookie sesi. 'Lax' adalah standar yang aman.
     * @var string 'Lax', 'Strict', 'None'
     */
    public string $cookieSameSite = 'Lax';
}