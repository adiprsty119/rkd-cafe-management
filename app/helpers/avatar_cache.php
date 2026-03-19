<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function cacheGoogleAvatar(string $url, int $userId): ?string
{
    $dir = __DIR__ . "/../../storage/avatars/google/";

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = "user_" . $userId . ".jpg";
    $path = $dir . $filename;

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10
    ]);

    $image = curl_exec($ch);

    curl_close($ch);

    if (!$image) {
        return null;
    }

    file_put_contents($path, $image);

    return "/rkd-cafe/storage/avatars/google/" . $filename;
}
