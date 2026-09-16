<?php

/**
 * English language file for Agent.
 */

$LANG_AGENT = array(
    'plugin_name'          => 'Agent',
    'admin_title'          => 'Agent Administration',
    'status'               => 'Foundation status',
    'configuration'        => 'Configuration',
    'runtime'              => 'Runtime',
    'site_namespace'       => 'Site namespace',
    'cache_path'           => 'Future site cache path',
    'read_only_notice'     => 'Agent is preparing a provider-neutral machine access layer. Public llms.txt, Markdown and JSON endpoints are not exposed yet.',
    'access_denied'        => 'You do not have permission to administer Agent.',
    'available'            => 'Available',
    'unavailable'          => 'Unavailable'
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
    'tab_providers' => 'Providers'
);

$LANG_fs['agent'] = array(
    'fs_general'   => 'General',
    'fs_providers' => 'Providers'
);

$LANG_confignames['agent'] = array(
    'enabled'           => 'Enable Agent runtime',
    'providers_enabled' => 'Enabled providers (comma-separated)'
);

/* Geeklog Configuration Manager expects label => stored value. */
$LANG_configselects['agent'][0] = array(
    'Disabled' => 0,
    'Enabled'  => 1
);
