<?php

namespace App\Controllers\Kesiswaan;

use App\Controllers\BaseController;

class Debug extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        echo "<h1>Kesiswaan Debugger</h1>";
        echo "<p>Jenjang Session: <strong>" . (session()->get('kode_jenjang') ?? 'KOSONG') . "</strong></p>";
        
        $tables = ['kesiswaan_ekskul', 'kesiswaan_organisasi', 'kesiswaan_bk_catatan', 'kesiswaan_alumni'];
        
        foreach($tables as $t) {
            $count = $db->table($t)->countAllResults();
            $data  = $db->table($t)->limit(3)->get()->getResultArray();
            
            echo "<h3>Tabel: $t (Total: $count)</h3>";
            if($count > 0) {
                echo "<pre>" . print_r($data, true) . "</pre>";
            } else {
                echo "<p style='color:red'>KOSONG!</p>";
            }
            echo "<hr>";
        }
    }
}