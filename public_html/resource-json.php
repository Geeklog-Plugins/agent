<?php

/**
 * Public JSON resource endpoint for Geeklog Agent.
 *
 * Example:
 * /agent/resource-json.php?provider=stories&id=my-story-id
 * /agent/resource-json.php?provider=staticpages&id=my-page-id
 *
 * @package Agent
 */

require_once '../lib-common.php';

$provider = isset($_GET['provider']) ? strtolower(trim((string) $_GET['provider'])) : '';
$id = isset($_GET['id']) ? trim((string) $_GET['id']) : '';

$catalog = function_exists('AGENT_getProviderCatalog') ? AGENT_getProviderCatalog() : array();
if ($provider === '' || !isset($catalog[$provider]) || $id === '' || strlen($id) > 255 || strpos($id, "\0") !== false) {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    echo "{\"error\":\"resource_not_found\"}\n";
    exit;
}

if (!function_exists('AGENT_buildResourceJson')) {
    http_response_code(503);
    header('Content-Type: application/json; charset=UTF-8');
    echo "{\"error\":\"json_unavailable\"}\n";
    exit;
}

$output = AGENT_buildResourceJson($provider, $id);
if ($output === '') {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    echo "{\"error\":\"resource_not_found\"}\n";
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=300');
echo $output;
