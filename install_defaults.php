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

    agent_config_add_group($c, 'general', 10);
    $c->add('enabled', $_AGENT_DEFAULT['enabled'], 'select', 0, 0, 0, 10, true, 'agent', 0);

    agent_config_add_group($c, 'discovery', 20);
    $c->add('llms_enabled', $_AGENT_DEFAULT['llms_enabled'], 'select', 0, 0, 0, 10, true, 'agent', 0);
    $c->add('site_description', $_AGENT_DEFAULT['site_description'], 'text', 0, 0, null, 20, true, 'agent', 0);
    $c->add('additional_instructions', $_AGENT_DEFAULT['additional_instructions'], 'text', 0, 0, null, 30, true, 'agent', 0);

    agent_config_add_group($c, 'providers', 30);
    $c->add('providers_enabled', $_AGENT_DEFAULT['providers_enabled'], 'text', 0, 0, null, 10, true, 'agent', 0);

    agent_config_add_group($c, 'resources', 40);
    $c->add('recent_limit', $_AGENT_DEFAULT['recent_limit'], 'text', 0, 0, null, 10, true, 'agent', 0);
    $c->add('popular_limit', $_AGENT_DEFAULT['popular_limit'], 'text', 0, 0, null, 20, true, 'agent', 0);
    $c->add('markdown_enabled', $_AGENT_DEFAULT['markdown_enabled'], 'select', 0, 0, 0, 30, true, 'agent', 0);
    $c->add('json_enabled', $_AGENT_DEFAULT['json_enabled'], 'select', 0, 0, 0, 40, true, 'agent', 0);

    agent_config_add_group($c, 'capabilities', 50);
    $c->add('capabilities_enabled', $_AGENT_DEFAULT['capabilities_enabled'], 'select', 0, 0, 0, 10, true, 'agent', 0);

    agent_config_add_group($c, 'cache', 60);
    $c->add('cache_enabled', $_AGENT_DEFAULT['cache_enabled'], 'select', 0, 0, 0, 10, true, 'agent', 0);
    $c->add('cache_ttl', $_AGENT_DEFAULT['cache_ttl'], 'text', 0, 0, null, 20, true, 'agent', 0);

    agent_config_add_group($c, 'security', 70);
    $c->add('public_read_only', $_AGENT_DEFAULT['public_read_only'], 'select', 0, 0, 0, 10, true, 'agent', 0);
    $c->add('authenticated_access', $_AGENT_DEFAULT['authenticated_access'], 'select', 0, 0, 0, 20, true, 'agent', 0);

    return true;
}

/**
 * Add one Configuration Manager tab and fieldset.
 */
function agent_config_add_group($c, $name, $sort)
{
    $c->add('tab_' . $name, null, 'tab', 0, 0, null, $sort, true, 'agent', 0);
    $c->add('fs_' . $name, null, 'fieldset', 0, 0, null, $sort, true, 'agent', 0);
}
