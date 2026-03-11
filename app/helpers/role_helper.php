<?php

function getSettingsUrl(): string
{
    $roleRoutes = [
        'admin' => '/rkd-cafe/pages/admin/settings.php',
        'kasir' => '/rkd-cafe/pages/kasir/settings.php',
        'owner' => '/rkd-cafe/pages/owner/settings.php'
    ];

    $role = $_SESSION['role'] ?? 'guest';

    return $roleRoutes[$role] ?? '/rkd-cafe/resources/views/auth/login.php';
}
