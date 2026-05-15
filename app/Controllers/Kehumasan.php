<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Kehumasan extends BaseController
{
    public function index(): string
    {
        $data = [
            'title' => 'Modul Kehumasan',
            'current_module' => 'kehumasan'
        ];
        return view('kehumasan/index', $data);
    }
}
