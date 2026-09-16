<?php

/**
 * Public Agent discovery endpoint.
 *
 * Installed by Geeklog as /agent/llms.php. The future canonical /llms.txt
 * facade can route or publish this representation without changing the Agent
 * resource/provider model.
 *
 * @package Agent
 */

require_once '../lib-common.php';

if (!function_exists('AGENT_buildLlmsText')) {
    header('HTTP/1.1 503 Service Unavailable');
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Agent discovery is unavailable.\n";
    exit;
}

$content = AGENT_buildLlmsText();
if ($content === '') {
    header('HTTP/1.1 404 Not Found');
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Agent discovery is disabled.\n";
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');
header('Cache-Control: public, max-age=300');
header('X-Content-Type-Options: nosniff');

echo $content;
