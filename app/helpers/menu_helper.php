<?php

function currentRoute()
{
    return basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
}


function activeMenu($url)
{
    $current = currentRoute();
    $target  = basename($url);

    if ($current === $target) {
        return "bg-gray-200 -ml-1.5 dark:bg-gray-700 font-semibold border-yellow-500";
    }

    return "";
}
