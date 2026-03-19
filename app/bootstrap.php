<?php

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// CORE
require_once __DIR__ . '/../config/database.php';

// AUTH & USER
require_once __DIR__ . '/helpers/auth_helper.php';
require_once __DIR__ . '/helpers/user_helper.php';
require_once __DIR__ . '/helpers/avatar_helper.php';
require_once __DIR__ . '/helpers/role_helper.php';

// FEATURE
require_once __DIR__ . '/helpers/notification_helper.php';

// UI / MENU SYSTEM 🔥 TAMBAHKAN INI
require_once __DIR__ . '/helpers/menu_helper.php';
require_once __DIR__ . '/helpers/childmenu_helper.php';
require_once __DIR__ . '/helpers/icon_helper.php';
require_once __DIR__ . '/helpers/menu_engine.php';
