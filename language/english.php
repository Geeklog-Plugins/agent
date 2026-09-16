<?php

/**
 * English language file for Agent.
 */

$LANG_AGENT = array(
    'plugin_name'              => 'Agent',
    'admin_title'              => 'Agent Administration',
    'status'                   => 'Agent status',
    'configuration'            => 'Configuration',
    'runtime'                  => 'Runtime',
    'site_namespace'           => 'Site namespace',
    'cache_path'               => 'Future site cache path',
    'read_only_notice'         => 'Agent exposes an initial public llms-style discovery endpoint at /agent/llms.php. Markdown and JSON resource endpoints are not exposed yet.',
    'discovery_setup'          => 'Public discovery setup',
    'discovery_setup_intro'    => 'To expose the canonical /llms.txt URL, add this rule to the site .htaccess file:',
    'discovery_rewrite_rule'   => 'RewriteRule ^llms\\.txt$ /agent/llms.php [L]',
    'discovery_test'           => 'Test /llms.txt',
    'discovery_direct'         => 'Direct Agent endpoint',
    'access_denied'            => 'You do not have permission to administer Agent.',
    'available'                => 'Available',
    'unavailable'              => 'Unavailable'
);

$LANG_configsections['agent'] = array(
    'label' => 'Agent',
    'title' => 'Agent Configuration'
);

$LANG_configsubgroups['agent'] = array(
    'sg_main' => 'Agent'
);

$LANG_tab['agent'] = array(
    'tab_general'   => 'General',
    'tab_providers' => 'Providers',
    'tab_discovery' => 'Discovery / llms'
);

$LANG_fs['agent'] = array(
    'fs_general'   => 'General',
    'fs_providers' => 'Providers',
    'fs_discovery' => 'Discovery / llms'
);

$LANG_confignames['agent'] = array(
    'enabled'           => 'Enable Agent runtime',
    'providers_enabled' => 'Enabled providers (comma-separated)',
    'llms_enabled'      => 'Enable public llms discovery',
    'site_description'  => 'Short site description for agents',
    'recent_limit'      => 'Recent resources per provider'
);

/* Geeklog Configuration Manager expects label => stored value. */
$LANG_configselects['agent'][0] = array(
    'Disabled' => 0,
    'Enabled'  => 1
);
