<?php

/**
 * Get current route (file name)
 */
function currentRoute(): string
{
    static $route = null;

    if ($route === null) {
        $route = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    }

    return $route;
}


/**
 * Check if menu URL is active
 */
function activeMenu(string $url): string
{
    $current = currentRoute();
    $target  = basename($url);

    return ($current === $target)
        ? "bg-gray-200 -ml-1.5 dark:bg-gray-700 font-semibold border-yellow-500"
        : "";
}


/**
 * Check if parent menu should be active based on prefix
 */
function isActivePrefix(?string $prefix): bool
{
    if (!$prefix) {
        return false;
    }

    $route = currentRoute();
    $prefixes = explode('|', $prefix);

    foreach ($prefixes as $p) {
        if (str_contains($route, $p)) {
            return true;
        }
    }

    return false;
}


/**
 * Active class for parent menu
 */
function activeParent(?string $prefix): string
{
    return isActivePrefix($prefix)
        ? "bg-gray-200 dark:bg-gray-700 font-semibold"
        : "";
}
