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
    'discovery_setup_intro'    => 'To expose the canonical /llms.txt URL, add the Agent rewrite rule inside the existing <IfModule mod_rewrite.c> block and before Geeklog\'s generic routing rule.',
    'discovery_rewrite_rule'   => 'RewriteRule ^llms\\.txt$ /agent/llms.php [L]',
    'discovery_rewrite_example'=> "RewriteEngine On\n\n# Geeklog Agent - canonical AI discovery endpoint\nRewriteRule ^llms\\.txt$ /agent/llms.php [L]\n\n# Geeklog generic routing must remain after the Agent rule\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ /index.php/$1 [L]",
    'discovery_rewrite_note'   => 'Do not place the Agent rule after Geeklog\'s generic catch-all rule, because /llms.txt would already have been routed to /index.php.',
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
