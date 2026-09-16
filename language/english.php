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
    'read_only_notice'     => 'Agent 0.1.0 is an installable foundation only. It does not expose content endpoints or write actions yet.',
    'access_denied'        => 'You do not have permission to administer Agent.',
    'available'            => 'Available',
    'unavailable'          => 'Unavailable'
);

$LANG_configsections['agent'] = array(
    'label' => 'Agent',
    'title' => 'Agent Configuration'
);

$LANG_configsubgroups['agent']['sg_main'] = 'Agent';

$LANG_tab['agent']['general'] = 'General';
$LANG_tab['agent']['discovery'] = 'Discovery / llms.txt';
$LANG_tab['agent']['providers'] = 'Providers';
$LANG_tab['agent']['resources'] = 'Resources';
$LANG_tab['agent']['capabilities'] = 'Capabilities';
$LANG_tab['agent']['cache'] = 'Cache';
$LANG_tab['agent']['security'] = 'Security';

$LANG_fs['agent']['general'] = 'General';
$LANG_fs['agent']['discovery'] = 'Discovery / llms.txt';
$LANG_fs['agent']['providers'] = 'Providers';
$LANG_fs['agent']['resources'] = 'Resources';
$LANG_fs['agent']['capabilities'] = 'Capabilities';
$LANG_fs['agent']['cache'] = 'Cache';
$LANG_fs['agent']['security'] = 'Security';

$LANG_confignames['agent'] = array(
    'enabled'                 => 'Enable Agent foundation',
    'llms_enabled'            => 'Enable future /llms.txt discovery',
    'site_description'        => 'Short site description',
    'additional_instructions' => 'Additional discovery instructions',
    'providers_enabled'       => 'Enabled providers (future)',
    'recent_limit'            => 'Recent content limit',
    'popular_limit'           => 'Popular content limit',
    'markdown_enabled'        => 'Enable future Markdown resources',
    'json_enabled'            => 'Enable future JSON resources',
    'capabilities_enabled'    => 'Enable future capability discovery',
    'cache_enabled'           => 'Enable cache when implemented',
    'cache_ttl'               => 'Cache TTL in seconds',
    'public_read_only'        => 'Restrict public surface to read-only',
    'authenticated_access'    => 'Enable authenticated access (future)'
);
