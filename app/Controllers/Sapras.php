<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Sapras extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Modul Manajemen Sapras',
            'current_module' => 'sapras'
        ];
        return view('sapras/index', $data);
    }
}
