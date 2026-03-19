<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function isChildActive($children)
{
    $current = basename($_SERVER['PHP_SELF']);

    foreach ($children as $child) {

        if (basename($child['url']) === $current) {
            return true;
        }
    }

    return false;
}
