<?php

/**
 * Agent administration and diagnostics page.
 */

require_once '../../../lib-common.php';
require_once '../../auth.inc.php';

if (!SEC_hasRights('agent.admin')) {
    $display = COM_showMessageText($MESSAGE[29], $MESSAGE[30]);
    $username = isset($_USER['username']) ? $_USER['username'] : 'unknown';
    COM_accessLog('User ' . $username . ' tried to access Agent administration.');
    COM_output(COM_createHTMLDocument($display, array('pagetitle' => $MESSAGE[30])));
    exit;
}

$T = new Template($_CONF['path'] . 'plugins/agent/templates');
$T->set_file('page', 'administration.thtml');
$T->set_block('page', 'endpoint_row', 'endpoint_rows');
$T->set_block('page', 'runtime_row', 'runtime_rows');

$siteUrl = isset($_CONF['site_url']) ? rtrim($_CONF['site_url'], '/') : '';
$agentEnabled = AGENT_isEnabled();
$providers = function_exists('AGENT_getEnabledProviders') ? AGENT_getEnabledProviders() : array();
$providersLabel = !empty($providers) ? implode(', ', $providers) : $LANG_AGENT['not_available'];
$recentLimit = (int) AGENT_getConfig('recent_limit', 10);

$descriptionSource = $LANG_AGENT['description_none'];
$agentDescription = trim((string) AGENT_getConfig('site_description', ''));
if ($agentDescription !== '') {
    $descriptionSource = $LANG_AGENT['description_agent_override'];
} elseif (!empty($_CONF['meta_description'])) {
    $descriptionSource = $LANG_AGENT['description_meta'];
} elseif (!empty($_CONF['site_slogan'])) {
    $descriptionSource = $LANG_AGENT['description_slogan'];
}

$discoveryTestUrl = $siteUrl !== '' ? $siteUrl . '/llms.txt' : '/llms.txt';
$discoveryDirectUrl = $siteUrl !== '' ? $siteUrl . '/agent/llms.php' : '/agent/llms.php';
$capabilitiesUrl = $siteUrl !== '' ? $siteUrl . '/agent/capabilities.php' : '/agent/capabilities.php';
$collectionProvider = !empty($providers) ? reset($providers) : 'stories';
$collectionUrl = ($siteUrl !== '' ? $siteUrl : '')
    . '/agent/resources-json.php?provider=' . rawurlencode((string) $collectionProvider)
    . '&limit=' . max(1, min(50, $recentLimit))
    . '&order=modified-desc';

$T->set_var(array(
    'admin_title'                => htmlspecialchars($LANG_AGENT['admin_title'], ENT_QUOTES, 'UTF-8'),
    'admin_intro'                => htmlspecialchars($LANG_AGENT['admin_intro'], ENT_QUOTES, 'UTF-8'),
    'read_only_notice'           => htmlspecialchars($LANG_AGENT['read_only_notice'], ENT_QUOTES, 'UTF-8'),
    'status_label'               => htmlspecialchars($LANG_AGENT['status'], ENT_QUOTES, 'UTF-8'),
    'status_value'               => htmlspecialchars($agentEnabled ? $LANG_AGENT['operational'] : $LANG_AGENT['disabled'], ENT_QUOTES, 'UTF-8'),
    'status_class'               => $agentEnabled ? 'agent-status-ok' : 'agent-status-off',
    'active_site_label'          => htmlspecialchars($LANG_AGENT['active_site'], ENT_QUOTES, 'UTF-8'),
    'active_site_value'          => htmlspecialchars($siteUrl !== '' ? $siteUrl : $LANG_AGENT['not_available'], ENT_QUOTES, 'UTF-8'),
    'enabled_providers_label'    => htmlspecialchars($LANG_AGENT['enabled_providers'], ENT_QUOTES, 'UTF-8'),
    'enabled_providers_value'    => htmlspecialchars($providersLabel, ENT_QUOTES, 'UTF-8'),
    'recent_resources_label'     => htmlspecialchars($LANG_AGENT['recent_resources'], ENT_QUOTES, 'UTF-8'),
    'recent_resources_value'     => htmlspecialchars((string) $recentLimit, ENT_QUOTES, 'UTF-8'),
    'description_source_label'   => htmlspecialchars($LANG_AGENT['description_source'], ENT_QUOTES, 'UTF-8'),
    'description_source_value'   => htmlspecialchars($descriptionSource, ENT_QUOTES, 'UTF-8'),
    'configuration_url'          => htmlspecialchars($_CONF['site_admin_url'] . '/configuration.php', ENT_QUOTES, 'UTF-8'),
    'configuration_label'        => htmlspecialchars($LANG_AGENT['configuration'], ENT_QUOTES, 'UTF-8'),
    'public_endpoints_label'     => htmlspecialchars($LANG_AGENT['public_endpoints'], ENT_QUOTES, 'UTF-8'),
    'public_endpoints_intro'     => htmlspecialchars($LANG_AGENT['public_endpoints_intro'], ENT_QUOTES, 'UTF-8'),
    'open_endpoint_label'        => htmlspecialchars($LANG_AGENT['open_endpoint'], ENT_QUOTES, 'UTF-8'),
    'discovery_setup_label'      => htmlspecialchars($LANG_AGENT['discovery_setup'], ENT_QUOTES, 'UTF-8'),
    'discovery_setup_intro'      => htmlspecialchars($LANG_AGENT['discovery_setup_intro'], ENT_QUOTES, 'UTF-8'),
    'discovery_rewrite_rule'     => htmlspecialchars($LANG_AGENT['discovery_rewrite_rule'], ENT_QUOTES, 'UTF-8'),
    'discovery_rewrite_example'  => htmlspecialchars($LANG_AGENT['discovery_rewrite_example'], ENT_QUOTES, 'UTF-8'),
    'discovery_rewrite_example_label' => htmlspecialchars($LANG_AGENT['discovery_rewrite_example_label'], ENT_QUOTES, 'UTF-8'),
    'discovery_rewrite_note'     => htmlspecialchars($LANG_AGENT['discovery_rewrite_note'], ENT_QUOTES, 'UTF-8'),
    'discovery_test_url'         => htmlspecialchars($discoveryTestUrl, ENT_QUOTES, 'UTF-8'),
    'discovery_test_label'       => htmlspecialchars($LANG_AGENT['discovery_test'], ENT_QUOTES, 'UTF-8'),
    'discovery_direct_url'       => htmlspecialchars($discoveryDirectUrl, ENT_QUOTES, 'UTF-8'),
    'discovery_direct_label'     => htmlspecialchars($LANG_AGENT['discovery_direct'], ENT_QUOTES, 'UTF-8'),
    'runtime_label'              => htmlspecialchars($LANG_AGENT['runtime'], ENT_QUOTES, 'UTF-8'),
    'runtime_intro'              => htmlspecialchars($LANG_AGENT['runtime_intro'], ENT_QUOTES, 'UTF-8')
));

$endpoints = array(
    array($LANG_AGENT['endpoint_llms'], $discoveryTestUrl),
    array($LANG_AGENT['endpoint_direct_llms'], $discoveryDirectUrl),
    array($LANG_AGENT['endpoint_capabilities'], $capabilitiesUrl),
    array($LANG_AGENT['endpoint_collection'], $collectionUrl)
);

foreach ($endpoints as $endpoint) {
    $T->set_var(array(
        'endpoint_name' => htmlspecialchars($endpoint[0], ENT_QUOTES, 'UTF-8'),
        'endpoint_url'  => htmlspecialchars($endpoint[1], ENT_QUOTES, 'UTF-8')
    ));
    $T->parse('endpoint_rows', 'endpoint_row', true);
}

$runtime = AGENT_getRuntimeCapabilities();
$runtime[$LANG_AGENT['site_namespace']] = AGENT_getSiteNamespace();
$runtime[$LANG_AGENT['cache_path']] = AGENT_getCachePath();

foreach ($runtime as $name => $value) {
    if (is_bool($value)) {
        $value = $value ? $LANG_AGENT['available'] : $LANG_AGENT['unavailable'];
    }

    $label = (string) $name;
    if (strpos($label, '_') !== false) {
        $label = ucfirst(str_replace('_', ' ', $label));
    }

    $T->set_var(array(
        'runtime_name'  => htmlspecialchars($label, ENT_QUOTES, 'UTF-8'),
        'runtime_value' => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
    ));
    $T->parse('runtime_rows', 'runtime_row', true);
}

$content = $T->finish($T->parse('output', 'page'));
COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_AGENT['admin_title'])));
