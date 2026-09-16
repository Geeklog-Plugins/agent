<?php

/**
 * Public read-only capability discovery endpoint for Geeklog Agent.
 *
 * @package Agent
 */

require_once '../lib-common.php';

if (!function_exists('AGENT_buildCapabilitiesJson')) {
    http_response_code(503);
    header('Content-Type: application/json; charset=UTF-8');
    echo "{\"error\":\"Agent capabilities are unavailable.\"}\n";
    exit;
}

$output = AGENT_buildCapabilitiesJson();
if ($output === '') {
    http_response_code(404);
    header('Content-Type: application/json; charset=UTF-8');
    echo "{\"error\":\"Capabilities not available.\"}\n";
    exit;
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: public, max-age=300');
echo $output;
