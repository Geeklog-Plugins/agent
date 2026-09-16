<?php

/**
 * Configuration Manager defaults and upgrade-safe synchronization for Agent.
 *
 * Installation is site-scoped because Geeklog's active Configuration Manager
 * context is authoritative. No hostname registry or cross-site write occurs.
 *
 * @package Agent
 */

if (isset($_SERVER['PHP_SELF']) && strpos(strtolower($_SERVER['PHP_SELF']), 'install_defaults.php') !== false) {
    die('This file can not be used on its own.');
}

global $_CONF, $_AGENT_DEFAULT;

require_once $_CONF['path'] . 'plugins/agent/config.php';

function plugin_initconfig_agent()
{
    return AGENT_syncConfig();
}

/**
 * Synchronize the active site's Agent Configuration Manager structure.
 *
 * Shared plugin files may be updated while individual multisite instances keep
 * older persisted configuration. Preserve implemented setting values, remove
 * obsolete/future-only entries, and ensure the current tabs/fields exist.
 */
function AGENT_syncConfig()
{
    global $_AGENT_DEFAULT;

    $c = config::get_instance();
    $groupExists = $c->group_exists('agent');
    $current = $groupExists ? $c->get_config('agent') : array();
    if (!is_array($current)) {
        $current = array();
    }

    if ($groupExists) {
        $obsolete = array(
            'additional_instructions',
            'popular_limit',
            'markdown_enabled',
            'json_enabled',
            'capabilities_enabled',
            'cache_enabled',
            'cache_ttl',
            'public_read_only',
            'authenticated_access',
            'tab_resources',
            'fs_resources',
            'tab_capabilities',
            'fs_capabilities',
            'tab_cache',
            'fs_cache',
            'tab_security',
            'fs_security'
        );

        foreach ($obsolete as $name) {
            $c->del($name, 'agent');
        }

        /* Recreate structural rows so tab/fieldset ids match current layout. */
        foreach (array(
            'sg_main',
            'tab_general', 'fs_general',
            'tab_providers', 'fs_providers',
            'tab_discovery', 'fs_discovery'
        ) as $name) {
            $c->del($name, 'agent');
        }
    }

    $c->add('sg_main', null, 'subgroup', 0, 0, null, 0, true, 'agent', 0);

    AGENT_configAddGroup($c, 'general', 0);
    if (!array_key_exists('enabled', $current)) {
        $c->add('enabled', $_AGENT_DEFAULT['enabled'], 'select', 0, 0, 0, 10, true, 'agent', 0);
    }

    AGENT_configAddGroup($c, 'providers', 1);
    if (!array_key_exists('providers_enabled', $current)) {
        $c->add('providers_enabled', $_AGENT_DEFAULT['providers_enabled'], 'text', 0, 1, null, 10, true, 'agent', 1);
    }

    AGENT_configAddGroup($c, 'discovery', 2);
    if (!array_key_exists('llms_enabled', $current)) {
        $c->add('llms_enabled', $_AGENT_DEFAULT['llms_enabled'], 'select', 0, 2, 0, 10, true, 'agent', 2);
    }
    if (!array_key_exists('site_description', $current)) {
        $c->add('site_description', $_AGENT_DEFAULT['site_description'], 'text', 0, 2, null, 20, true, 'agent', 2);
    }
    if (!array_key_exists('recent_limit', $current)) {
        $c->add('recent_limit', $_AGENT_DEFAULT['recent_limit'], 'text', 0, 2, null, 30, true, 'agent', 2);
    }

    return true;
}

/**
 * Add one Configuration Manager tab and fieldset using matching numeric ids.
 */
function AGENT_configAddGroup($c, $name, $id)
{
    $c->add('tab_' . $name, null, 'tab', 0, $id, null, 0, true, 'agent', $id);
    $c->add('fs_' . $name, null, 'fieldset', 0, $id, null, 0, true, 'agent', $id);
}
