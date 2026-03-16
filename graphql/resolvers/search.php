<?php

function searchGlobal($pdo, $keyword)
{

    $url = "http://localhost:8082/search?keyword=" . urlencode($keyword);

    $response = file_get_contents($url);

    if (!$response) {
        return [
            "users" => [],
            "menu" => [],
            "orders" => []
        ];
    }

    return json_decode($response, true);
}
