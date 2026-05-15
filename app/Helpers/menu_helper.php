<?php

function check_menu_access($allowed_roles = [], $allowed_units = []) {
    $session = session();
    $userRole = $session->get('role_name');
    $userUnit = $session->get('kode_jenjang');

    // 1. Jika Super Admin, buka semua akses
    if ($userRole === 'superadmin' || $userUnit === 'GLOBAL') {
        return true;
    }

    // 2. Cek kecocokan Role
    $roleMatch = empty($allowed_roles) || in_array($userRole, $allowed_roles);

    // 3. Cek kecocokan Unit (kode_jenjang)
    $unitMatch = empty($allowed_units) || in_array($userUnit, $allowed_units);

    return $roleMatch && $unitMatch;
}