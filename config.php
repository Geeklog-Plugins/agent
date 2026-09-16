<?php

/**
 * Agent for Geeklog - code metadata and installation defaults.
 *
 * This file contains no site selection logic. Runtime configuration is loaded
 * from Geeklog's Configuration Manager for the active site.
 *
 * @package Agent
 */

if (isset($_SERVER['PHP_SELF']) && strpos(strtolower($_SERVER['PHP_SELF']), 'config.php') !== false) {
    die('This file can not be used on its own.');
}

if (!defined('AGENT_VERSION')) {
    define('AGENT_VERSION', '0.1.0');
}
if (!defined('AGENT_MIN_GEEKLOG_VERSION')) {
    define('AGENT_MIN_GEEKLOG_VERSION', '2.1.1');
}
if (!defined('AGENT_MIN_PHP_VERSION')) {
    define('AGENT_MIN_PHP_VERSION', '5.6.0');
}

/**
 * Fresh-install defaults only. Runtime code must use persisted site-scoped
 * Configuration Manager values, not this array as a second source of truth.
 */
global $_AGENT_DEFAULT;

$_AGENT_DEFAULT = array(
    'enabled'                 => 1,
    'llms_enabled'            => 0,
    'site_description'        => '',
    'additional_instructions' => '',
    'providers_enabled'       => 'stories,staticpages',
    'recent_limit'            => 10,
    'popular_limit'           => 10,
    'markdown_enabled'        => 0,
    'json_enabled'            => 0,
    'capabilities_enabled'    => 0,
    'cache_enabled'           => 1,
    'cache_ttl'               => 300,
    'public_read_only'        => 1,
    'authenticated_access'    => 0
);
