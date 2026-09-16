<?php

/**
 * Optional Hub integration for Geeklog Agent.
 *
 * Agent never reads Hub tables and never maintains its own relationship
 * graph. It only consumes Hub through Geeklog's normal service surface when
 * Hub exposes a compatible read-only service.
 *
 * @package Agent
 */

function AGENT_getHubServiceMap()
{
    return array(
        'get_context' => 'hub.context.read',
        'get_related_items' => 'hub.related.read',
        'get_affected_items' => 'hub.affected.read',
        'get_integrity_report' => 'hub.integrity.read',
        'get_suggestions' => 'hub.suggestions.read'
    );
}

function AGENT_hubInstalled()
{
    return function_exists('plugin_chkVersion_hub') || function_exists('plugin_getadminoption_hub');
}

function AGENT_hubServiceAvailable($action)
{
    $services = AGENT_getHubServiceMap();
    $action = trim((string) $action);

    if (!isset($services[$action]) || !AGENT_hubInstalled()) {
        return false;
    }

    if (!function_exists('PLG_invokeService')) {
        return false;
    }

    $callback = 'service_' . $action . '_hub';
    if (!function_exists($callback)) {
        return false;
    }

    if (function_exists('PLG_wsEnabled') && !PLG_wsEnabled('hub')) {
        return false;
    }

    return true;
}

function AGENT_getHubCapabilities()
{
    $capabilities = array();

    foreach (AGENT_getHubServiceMap() as $action => $capability) {
        if (AGENT_hubServiceAvailable($action)) {
            $capabilities[] = $capability;
        }
    }

    return $capabilities;
}

function AGENT_invokeHubService($action, $args, &$output, &$svcMsg)
{
    if (!AGENT_hubServiceAvailable($action)) {
        return false;
    }

    if (!is_array($args)) {
        $args = array();
    }

    $output = array();
    $svcMsg = array();
    $result = PLG_invokeService('hub', (string) $action, $args, $output, $svcMsg);

    if (defined('PLG_RET_OK')) {
        return $result === PLG_RET_OK;
    }

    return $result === 0;
}
