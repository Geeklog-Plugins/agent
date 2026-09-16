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

    AGENT_configAddGroup($c, 'providers', 1);
    $c->add('providers_enabled', $_AGENT_DEFAULT['providers_enabled'], 'text', 0, 1, null, 10, true, 'agent', 1);

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
