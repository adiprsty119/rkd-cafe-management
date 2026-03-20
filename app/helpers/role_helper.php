<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function getSettingsUrl(): string
{
    $permissions = $_SESSION['permissions'] ?? [];

    if (in_array('manage_system_settings', $permissions)) {
        return '/rkd-cafe/pages/admin/settings.php';
    }

    if (in_array('manage_store_settings', $permissions)) {
        return '/rkd-cafe/pages/kasir/settings.php';
    }

    if (in_array('view_reports', $permissions)) {
        return '/rkd-cafe/pages/owner/settings.php';
    }

    return '/rkd-cafe/resources/views/auth/login.php';
}

function getDashboardUrl(): string
{
    $routes = require __DIR__ . '/../../config/dashboard_routes.php';
    $role = $_SESSION['role'] ?? 'guest';

    return $routes[$role] ?? '/rkd-cafe/resources/views/auth/login.php';
}
