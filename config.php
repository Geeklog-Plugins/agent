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
 *
 * Only settings with real runtime behavior belong here. Markdown, JSON,
 * capability, cache and authenticated-action controls are introduced only
 * when those features exist.
 */
global $_AGENT_DEFAULT;

$_AGENT_DEFAULT = array(
    'enabled'           => 1,
    'providers_enabled' => 'stories,staticpages',
    'llms_enabled'      => 1,
    'site_description'  => '',
    'recent_limit'      => 10
);
