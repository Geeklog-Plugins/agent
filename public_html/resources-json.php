<?php

/**
 * Public JSON collection endpoint for Geeklog Agent.
 *
 * Example:
 * /agent/resources-json.php?provider=stories&limit=10&order=modified-desc
 *
 * @package Agent
 */

require_once '../lib-common.php';

$provider = isset($_GET['provider']) ? strtolower(trim((string) $_GET['provider'])) : '';
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
$order = isset($_GET['order']) ? trim((string) $_GET['order']) : 'modified-desc';

$catalog = function_exists('AGENT_getProviderCatalog') ? AGENT_getProviderCatalog() : array();
if ($provider === '' || !isset($catalog[$provider])) {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    echo "{\"error\":\"provider_not_found\"}\n";
    exit;
}

if (!function_exists('AGENT_buildCollectionJson')) {
    http_response_code(503);
    header('Content-Type: application/json; charset=UTF-8');
    echo "{\"error\":\"json_unavailable\"}\n";
    exit;
}

$output = AGENT_buildCollectionJson(
    $provider,
    array(
        'limit' => $limit,
        'order' => $order
    )
);

if ($output === '') {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    echo "{\"error\":\"collection_not_found\"}\n";
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=300');
echo $output;
