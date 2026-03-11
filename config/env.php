<?php

function loadEnv(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    static $loaded = false;

    if ($loaded) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        $line = trim($line);

        /* ==========================
           SKIP COMMENT
        ========================== */

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        /* ==========================
           INVALID FORMAT
        ========================== */

        if (!str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        /* ==========================
           VALIDATE ENV KEY
        ========================== */

        if (!preg_match('/^[A-Z0-9_]+$/', $name)) {
            continue;
        }

        /* ==========================
           REMOVE QUOTES
        ========================== */

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $value = trim($value);

        /* ==========================
           PARSE BOOLEAN
        ========================== */

        if (strtolower($value) === 'true') {
            $value = true;
        }

        if (strtolower($value) === 'false') {
            $value = false;
        }

        /* ==========================
           PARSE NULL
        ========================== */

        if (strtolower($value) === 'null') {
            $value = null;
        }

        /* ==========================
           PARSE INTEGER
        ========================== */

        if (is_numeric($value) && ctype_digit($value)) {
            $value = (int)$value;
        }

        /* ==========================
           SET ENV VARIABLE
        ========================== */

        if (!isset($_ENV[$name]) && !isset($_SERVER[$name])) {

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;

            putenv($name . '=' . (is_bool($value) ? ($value ? 'true' : 'false') : $value));
        }
    }

    $loaded = true;
}

loadEnv(__DIR__ . '/../.env');
