<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Pelaporan extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Modul Pelaporan',
            'current_module' => 'pelaporan'
        ];
        return view('pelaporan/index', $data);
    }
}
