<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
        // BARU: Mendaftarkan Model kustom agar aturan 'is_after_or_equal' dikenali.
        \App\Models\KalenderPendidikanModel::class,
        // FIX KRITIS: Mendaftarkan AlumniModel agar custom rule 'validation_level_specific_status' dikenali.
        \App\Models\Kesiswaan\AlumniModel::class,
        // FIX BARU: Mendaftarkan PrestasiSiswaModel
        \App\Models\Kesiswaan\PrestasiSiswaModel::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'    => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------
}