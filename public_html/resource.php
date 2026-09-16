<?php

/**
 * Public Markdown resource endpoint for Geeklog Agent.
 *
 * Example:
 * /agent/resource.php?provider=stories&id=my-story-id
 * /agent/resource.php?provider=staticpages&id=my-page-id
 *
 * @package Agent
 */

require_once '../lib-common.php';

$provider = isset($_GET['provider']) ? strtolower(trim((string) $_GET['provider'])) : '';
$id = isset($_GET['id']) ? trim((string) $_GET['id']) : '';

$catalog = function_exists('AGENT_getProviderCatalog') ? AGENT_getProviderCatalog() : array();
if ($provider === '' || !isset($catalog[$provider]) || $id === '' || strlen($id) > 255 || strpos($id, "\0") !== false) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Resource not found.\n";
    exit;
}

if (!function_exists('AGENT_buildResourceMarkdown')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Agent Markdown resources are unavailable.\n";
    exit;
}

$output = AGENT_buildResourceMarkdown($provider, $id);
if ($output === '') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Resource not found.\n";
    exit;
}

header('Content-Type: text/markdown; charset=UTF-8');
header('Cache-Control: public, max-age=300');
echo $output;
