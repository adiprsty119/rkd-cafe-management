<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function getDashboardUrl(): string
{
    $routes = require __DIR__ . '/../../config/dashboard_routes.php';
    $role = $_SESSION['role'] ?? 'guest';

    return $routes[$role] ?? '/rkd-cafe/resources/views/auth/login.php';
}

function getSettingsUrl(): string
{
    if (hasPermission('manage_system_settings')) {
        return '/rkd-cafe/pages/admin/settings.php';
    }

    if (hasPermission('manage_store_settings')) {
        return '/rkd-cafe/pages/kasir/settings.php';
    }

    if (hasPermission('view_reports')) {
        return '/rkd-cafe/pages/owner/settings.php';
    }

    return getDashboardUrl();
}
