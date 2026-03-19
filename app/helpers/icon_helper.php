<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function iconSpacing($menu)
{
    // jika menu memiliki children berarti ada chevron
    if (isset($menu['children'])) {
        return "ml-1";
    }

    return "";
}
