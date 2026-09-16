<?php

/**
 * Geeklog autoinstall support for Agent 0.1.0.
 *
 * @package Agent
 */

function plugin_autoinstall_agent($pi_name)
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/agent/config.php';

    $pi_name = 'agent';
    $pi_display_name = 'Agent';
    $pi_admin = 'Agent Admin';

    $info = array(
        'pi_name'         => $pi_name,
        'pi_display_name' => $pi_display_name,
        'pi_version'      => AGENT_VERSION,
        'pi_gl_version'   => AGENT_MIN_GEEKLOG_VERSION,
        'pi_homepage'     => 'https://github.com/hostellerie/agent'
    );

    $groups = array(
        $pi_admin => 'Users in this group can administer the Agent plugin'
    );

    $features = array(
        'agent.admin' => 'Full access to Agent administration'
    );

    $mappings = array(
        'agent.admin' => array($pi_admin)
    );

    return array(
        'info'     => $info,
        'groups'   => $groups,
        'features' => $features,
        'mappings' => $mappings,
        'tables'   => array()
    );
}

function plugin_load_configuration_agent($pi_name)
{
    global $_CONF;

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $_CONF['path'] . 'plugins/agent/install_defaults.php';

    return plugin_initconfig_agent();
}

/**
 * Reject unsupported old runtimes while remaining forward-tolerant.
 * Agent source deliberately uses the PHP 5.6-compatible language subset.
 */
function plugin_compatible_with_this_version_agent($pi_name)
{
    if (version_compare(PHP_VERSION, '5.6.0', '<')) {
        return false;
    }

    if (defined('VERSION')) {
        if (function_exists('COM_versionCompare')) {
            if (COM_versionCompare(VERSION, '2.1.1', '<')) {
                return false;
            }
        } elseif (version_compare(VERSION, '2.1.1', '<')) {
            return false;
        }
    }

    return true;
}
