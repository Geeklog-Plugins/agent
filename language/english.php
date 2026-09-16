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

$LANG_configsubgroups['agent'] = array(
    'sg_main' => 'Agent'
);

$LANG_tab['agent'] = array(
    'tab_general'      => 'General',
    'tab_discovery'    => 'Discovery / llms.txt',
    'tab_providers'    => 'Providers',
    'tab_resources'    => 'Resources',
    'tab_capabilities' => 'Capabilities',
    'tab_cache'        => 'Cache',
    'tab_security'     => 'Security'
);

$LANG_fs['agent'] = array(
    'fs_general'      => 'General',
    'fs_discovery'    => 'Discovery / llms.txt',
    'fs_providers'    => 'Providers',
    'fs_resources'    => 'Resources',
    'fs_capabilities' => 'Capabilities',
    'fs_cache'        => 'Cache',
    'fs_security'     => 'Security'
);

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

$LANG_configselects['agent'][0] = array(
    0 => 'Disabled',
    1 => 'Enabled'
);
