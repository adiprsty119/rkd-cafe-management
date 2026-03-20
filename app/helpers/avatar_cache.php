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

    // 🔒 VALIDASI URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_MAXREDIRS => 3
    ]);

    $image = curl_exec($ch);

    // 🔍 HTTP STATUS CHECK
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    curl_close($ch);

    if (!$image || $httpCode !== 200) {
        return null;
    }

    // 🔒 VALIDASI CONTENT TYPE
    if (!str_starts_with($contentType, 'image/')) {
        return null;
    }

    // 🔒 LIMIT SIZE (max 2MB)
    if (strlen($image) > 2 * 1024 * 1024) {
        return null;
    }

    // 💾 SIMPAN FILE
    file_put_contents($path, $image, LOCK_EX);

    return "/rkd-cafe/storage/avatars/google/" . $filename;
}
