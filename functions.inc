<?php

/**
 * Agent for Geeklog plugin API callbacks and foundation helpers.
 *
 * 0.1.0 intentionally contains no content-provider SQL and no external
 * provider/client dependency.
 *
 * @package Agent
 */

if (isset($_SERVER['PHP_SELF']) && strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die('This file can not be used on its own.');
}

global $_CONF, $_AGENT_CONF;

$agent_path = $_CONF['path'] . 'plugins/agent/';
require_once $agent_path . 'config.php';

$agent_langfile = $agent_path . 'language/' . $_CONF['language'] . '.php';
if (file_exists($agent_langfile)) {
    require_once $agent_langfile;
} else {
    require_once $agent_path . 'language/english.php';
}

require_once $_CONF['path_system'] . 'classes/config.class.php';
$agent_config = config::get_instance();
$_AGENT_CONF = $agent_config->get_config('agent');

function plugin_cclabel_agent()
{
    global $_CONF, $LANG_AGENT;

    if (!SEC_hasRights('agent.admin')) {
        return array();
    }

    return array(
        $LANG_AGENT['plugin_name'],
        $_CONF['site_admin_url'] . '/plugins/agent/index.php',
        ''
    );
}

function plugin_getadminoption_agent()
{
    global $_CONF, $LANG_AGENT;

    if (!SEC_hasRights('agent.admin')) {
        return null;
    }

    return array(
        $LANG_AGENT['plugin_name'],
        $_CONF['site_admin_url'] . '/plugins/agent/index.php',
        0
    );
}

function plugin_chkVersion_agent()
{
    return AGENT_VERSION;
}

/**
 * 0.1.0 has no persistent schema migration. This callback is deliberately
 * conservative so future upgrades can add site-scoped migrations without
 * turning file deployment into an implicit migration.
 */
function plugin_upgrade_agent()
{
    global $_CONF;

    require_once $_CONF['path'] . 'plugins/agent/autoinstall.php';

    if (!plugin_compatible_with_this_version_agent('agent')) {
        return 3002;
    }

    return true;
}

/**
 * Report active-runtime facts by feature detection rather than by assuming a
 * specific Geeklog minor version.
 */
function AGENT_getRuntimeCapabilities()
{
    $capabilities = array();

    $capabilities['geeklog_version'] = defined('VERSION') ? VERSION : 'unknown';
    $capabilities['php_version'] = PHP_VERSION;
    $capabilities['plg_getiteminfo'] = function_exists('PLG_getItemInfo');
    $capabilities['plg_invoke_service'] = function_exists('PLG_invokeService');
    $capabilities['plg_ws_enabled'] = function_exists('PLG_wsEnabled');
    $capabilities['plg_item_saved'] = function_exists('PLG_itemSaved');
    $capabilities['plg_item_deleted'] = function_exists('PLG_itemDeleted');
    $capabilities['configuration_manager'] = class_exists('config');

    return $capabilities;
}

/**
 * Create a deterministic identifier for the active Geeklog site.
 *
 * It intentionally uses already-selected Geeklog context and never performs
 * hostname routing. The value is suitable for cache keys and site-local
 * persistent namespaces, but is not a credential or public site identifier.
 */
function AGENT_getSiteNamespace()
{
    global $_CONF, $_DB_table_prefix;

    $parts = array(
        isset($_CONF['path_data']) ? $_CONF['path_data'] : '',
        isset($_CONF['site_url']) ? $_CONF['site_url'] : '',
        isset($_DB_table_prefix) ? $_DB_table_prefix : ''
    );

    return 'agent-' . substr(hash('sha256', implode('|', $parts)), 0, 20);
}

/**
 * Return the future cache root for the active site without creating it.
 * Directory creation and full cache lifecycle belong to a later milestone.
 */
function AGENT_getCachePath()
{
    global $_CONF;

    $base = isset($_CONF['path_data']) ? rtrim($_CONF['path_data'], '/\\') : '';
    if ($base === '') {
        return '';
    }

    return $base . DIRECTORY_SEPARATOR . 'agent' . DIRECTORY_SEPARATOR
        . 'cache' . DIRECTORY_SEPARATOR . AGENT_getSiteNamespace();
}

/**
 * Read one persisted Agent setting with a safe fallback for shared-file
 * deployments where a site's configuration has not yet gained a future key.
 */
function AGENT_getConfig($key, $default)
{
    global $_AGENT_CONF;

    if (is_array($_AGENT_CONF) && array_key_exists($key, $_AGENT_CONF)) {
        return $_AGENT_CONF[$key];
    }

    return $default;
}
