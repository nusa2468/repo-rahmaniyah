<?php

namespace App\Controllers\Pengaturan;

use App\Controllers\BaseController;

class Pengaturan extends BaseController
{
    public function index()
    {
        $data = [
            'title'          => 'Pengaturan Sistem',
            'current_module' => 'pengaturan',
        ];

        return view('pengaturan/index', $data);
    }
}
