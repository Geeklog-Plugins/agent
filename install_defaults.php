<?php

/**
 * Configuration Manager defaults for Agent.
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
    global $_AGENT_DEFAULT;

    $c = config::get_instance();
    if ($c->group_exists('agent')) {
        return true;
    }

    $c->add('sg_main', null, 'subgroup', 0, 0, null, 0, true, 'agent', 0);

    AGENT_configAddGroup($c, 'general', 0);
    $c->add('enabled', $_AGENT_DEFAULT['enabled'], 'select', 0, 0, 0, 10, true, 'agent', 0);

    AGENT_configAddGroup($c, 'discovery', 1);
    $c->add('llms_enabled', $_AGENT_DEFAULT['llms_enabled'], 'select', 0, 1, 0, 10, true, 'agent', 1);
    $c->add('site_description', $_AGENT_DEFAULT['site_description'], 'text', 0, 1, null, 20, true, 'agent', 1);
    $c->add('additional_instructions', $_AGENT_DEFAULT['additional_instructions'], 'text', 0, 1, null, 30, true, 'agent', 1);

    AGENT_configAddGroup($c, 'providers', 2);
    $c->add('providers_enabled', $_AGENT_DEFAULT['providers_enabled'], 'text', 0, 2, null, 10, true, 'agent', 2);

    AGENT_configAddGroup($c, 'resources', 3);
    $c->add('recent_limit', $_AGENT_DEFAULT['recent_limit'], 'text', 0, 3, null, 10, true, 'agent', 3);
    $c->add('popular_limit', $_AGENT_DEFAULT['popular_limit'], 'text', 0, 3, null, 20, true, 'agent', 3);
    $c->add('markdown_enabled', $_AGENT_DEFAULT['markdown_enabled'], 'select', 0, 3, 0, 30, true, 'agent', 3);
    $c->add('json_enabled', $_AGENT_DEFAULT['json_enabled'], 'select', 0, 3, 0, 40, true, 'agent', 3);

    AGENT_configAddGroup($c, 'capabilities', 4);
    $c->add('capabilities_enabled', $_AGENT_DEFAULT['capabilities_enabled'], 'select', 0, 4, 0, 10, true, 'agent', 4);

    AGENT_configAddGroup($c, 'cache', 5);
    $c->add('cache_enabled', $_AGENT_DEFAULT['cache_enabled'], 'select', 0, 5, 0, 10, true, 'agent', 5);
    $c->add('cache_ttl', $_AGENT_DEFAULT['cache_ttl'], 'text', 0, 5, null, 20, true, 'agent', 5);

    AGENT_configAddGroup($c, 'security', 6);
    $c->add('public_read_only', $_AGENT_DEFAULT['public_read_only'], 'select', 0, 6, 0, 10, true, 'agent', 6);
    $c->add('authenticated_access', $_AGENT_DEFAULT['authenticated_access'], 'select', 0, 6, 0, 20, true, 'agent', 6);

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
