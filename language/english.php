<?php

/**
 * English language file for Agent.
 */

$LANG_AGENT = array(
    'plugin_name'                => 'Agent',
    'admin_title'                => 'Agent Administration',
    'admin_intro'                => 'Agent gives this Geeklog site a machine-readable voice for LLMs and AI agents through llms.txt, Markdown, JSON and capability discovery.',
    'status'                     => 'Agent status',
    'configuration'              => 'Configuration',
    'runtime'                    => 'Runtime diagnostics',
    'runtime_intro'              => 'Technical capabilities detected in the current Geeklog site context.',
    'site_namespace'             => 'Site namespace',
    'cache_path'                 => 'Future site cache path',
    'read_only_notice'           => 'Public access is read-only. Agent can expose discovery, normalized resources and capabilities, but it does not grant AI clients permission to modify Geeklog content.',
    'operational'                => 'Operational',
    'disabled'                   => 'Disabled',
    'enabled'                    => 'Enabled',
    'site_context'               => 'Site context',
    'active_site'                => 'Active site',
    'enabled_providers'          => 'Enabled providers',
    'recent_resources'           => 'Recent resources per provider',
    'description_source'         => 'Site description source',
    'description_agent_override' => 'Agent override',
    'description_meta'           => 'Geeklog Meta Description',
    'description_slogan'         => 'Geeklog site slogan',
    'description_none'           => 'Not configured',
    'public_endpoints'           => 'Public machine endpoints',
    'public_endpoints_intro'     => 'These URLs are the public entry points currently exposed by Agent for machine consumers.',
    'endpoint_llms'              => 'Canonical llms.txt',
    'endpoint_direct_llms'       => 'Direct llms endpoint',
    'endpoint_capabilities'      => 'Capabilities',
    'endpoint_collection'        => 'JSON collection example',
    'open_endpoint'              => 'Open',
    'discovery_setup'            => 'Canonical /llms.txt setup',
    'discovery_setup_intro'      => 'To expose the canonical /llms.txt URL, add the Agent rewrite rule inside the existing <IfModule mod_rewrite.c> block and before Geeklog\'s generic routing rule.',
    'discovery_rewrite_rule'     => 'RewriteRule ^llms\\.txt$ /agent/llms.php [L]',
    'discovery_rewrite_example'  => "RewriteEngine On\n\n# Geeklog Agent - canonical AI discovery endpoint\nRewriteRule ^llms\\.txt$ /agent/llms.php [L]\n\n# Geeklog generic routing must remain after the Agent rule\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ /index.php/$1 [L]",
    'discovery_rewrite_example_label' => 'Full rewrite example',
    'discovery_rewrite_note'     => 'Do not place the Agent rule after Geeklog\'s generic catch-all rule, because /llms.txt would already have been routed to /index.php.',
    'discovery_test'             => 'Test /llms.txt',
    'discovery_direct'           => 'Direct Agent endpoint',
    'access_denied'              => 'You do not have permission to administer Agent.',
    'available'                  => 'Available',
    'unavailable'                => 'Unavailable',
    'not_available'              => 'Not available'
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
    'site_description'  => 'Short site description for agents (optional; defaults to Geeklog Meta Description, then site slogan)',
    'recent_limit'      => 'Recent resources per provider'
);

/* Geeklog Configuration Manager expects label => stored value. */
$LANG_configselects['agent'][0] = array(
    'Disabled' => 0,
    'Enabled'  => 1
);
