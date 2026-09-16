<?php

/**
 * Isolated Hub integration contract test.
 */

define('PLG_RET_OK', 42);

function plugin_getadminoption_hub()
{
    return array('Hub', '/admin/plugins/hub/index.php', 0);
}

function PLG_wsEnabled($type)
{
    return $type === 'hub';
}

function service_get_context_hub($args, &$output, &$svcMsg)
{
    $output = array('context' => isset($args['id']) ? $args['id'] : '');
    $svcMsg = array();
    return PLG_RET_OK;
}

function PLG_invokeService($type, $action, $args, &$output, &$svcMsg)
{
    $callback = 'service_' . $action . '_' . $type;
    if (!function_exists($callback)) {
        return -1;
    }

    return $callback($args, $output, $svcMsg);
}

require_once dirname(__DIR__) . '/lib/hub.php';

if (!AGENT_hubInstalled()) {
    fwrite(STDERR, 'Hub should be detected in isolated test.' . PHP_EOL);
    exit(1);
}

$capabilities = AGENT_getHubCapabilities();
if ($capabilities !== array('hub.context.read')) {
    fwrite(STDERR, 'Unexpected Hub capability detection.' . PHP_EOL);
    exit(1);
}

$output = array();
$svcMsg = array();
if (!AGENT_invokeHubService('get_context', array('id' => 'example'), $output, $svcMsg)) {
    fwrite(STDERR, 'Hub service invocation failed.' . PHP_EOL);
    exit(1);
}
if (!isset($output['context']) || $output['context'] !== 'example') {
    fwrite(STDERR, 'Hub service output was not preserved.' . PHP_EOL);
    exit(1);
}

if (AGENT_hubServiceAvailable('unknown_action')) {
    fwrite(STDERR, 'Unknown Hub action must not be exposed.' . PHP_EOL);
    exit(1);
}

echo 'Agent Hub integration contract passed.' . PHP_EOL;
