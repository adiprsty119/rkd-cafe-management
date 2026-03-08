<?php

function currentRoute()
{
    return basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
}

function activeMenu($url)
{
    $current = currentRoute();

    if ($current === basename($url)) {
        return "bg-gray-200 dark:bg-gray-700 font-semibold border-l-4 border-yellow-500";
    }

    return "";
}
