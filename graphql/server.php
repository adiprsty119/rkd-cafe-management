<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/resolvers/search.php';

use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\GraphQL;

/* =========================
   DATABASE
========================= */

$pdo = getPDO();

/* =========================
   GRAPHQL TYPES
========================= */

$userType = new ObjectType([
    'name' => 'User',
    'fields' => [
        'id' => Type::id(),
        'name' => Type::string(),
        'username' => Type::string(),
        'role' => Type::string(),
    ]
]);

$menuType = new ObjectType([
    'name' => 'Menu',
    'fields' => [
        'id' => Type::id(),
        'name' => Type::string(),
        'price' => Type::float(),
    ]
]);

$orderType = new ObjectType([
    'name' => 'Order',
    'fields' => [
        'id' => Type::id(),
        'order_code' => Type::string(),
        'total' => Type::float(),
    ]
]);

$searchResultType = new ObjectType([
    'name' => 'SearchResult',
    'fields' => [
        'users' => Type::listOf($userType),
        'menu' => Type::listOf($menuType),
        'orders' => Type::listOf($orderType)
    ]
]);

/* =========================
   ROOT QUERY
========================= */

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [

        'search' => [
            'type' => $searchResultType,

            'args' => [
                'keyword' => Type::nonNull(Type::string())
            ],

            'resolve' => function ($root, $args) use ($pdo) {

                try {
                    return searchGlobal($pdo, $args['keyword']);
                } catch (Throwable $e) {

                    error_log($e->getMessage());

                    return [
                        "users" => [],
                        "menu" => [],
                        "orders" => []
                    ];
                }
            }
        ]

    ]
]);

/* =========================
   SCHEMA
========================= */

$schema = new Schema([
    'query' => $queryType
]);

/* =========================
   REQUEST
========================= */

$rawInput = file_get_contents('php://input');

if (!$rawInput) {

    header('Content-Type: application/json');

    echo json_encode([
        "message" => "GraphQL endpoint ready",
        "usage" => "Send POST request with GraphQL query"
    ]);

    exit;
}

$input = json_decode($rawInput, true);

/* =========================
   EXECUTE GRAPHQL
========================= */

try {

    $result = GraphQL::executeQuery(
        $schema,
        $input['query'] ?? ''
    );

    $output = $result->toArray();
} catch (Throwable $e) {

    http_response_code(500);

    $output = [
        "error" => $e->getMessage()
    ];
}

/* =========================
   RESPONSE
========================= */

header('Content-Type: application/json');

echo json_encode($output);
