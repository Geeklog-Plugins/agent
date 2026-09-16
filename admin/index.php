<?php

/**
 * Minimal Agent administration/status page.
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
$T->set_block('page', 'runtime_row', 'runtime_rows');

$siteUrl = isset($_CONF['site_url']) ? rtrim($_CONF['site_url'], '/') : '';
$discoveryTestUrl = $siteUrl !== '' ? $siteUrl . '/llms.txt' : '/llms.txt';
$discoveryDirectUrl = $siteUrl !== '' ? $siteUrl . '/agent/llms.php' : '/agent/llms.php';

$T->set_var(array(
    'read_only_notice'         => htmlspecialchars($LANG_AGENT['read_only_notice'], ENT_QUOTES, 'UTF-8'),
    'configuration_url'        => htmlspecialchars($_CONF['site_admin_url'] . '/configuration.php?conf_group=agent', ENT_QUOTES, 'UTF-8'),
    'configuration_label'      => htmlspecialchars($LANG_AGENT['configuration'], ENT_QUOTES, 'UTF-8'),
    'discovery_setup_label'    => htmlspecialchars($LANG_AGENT['discovery_setup'], ENT_QUOTES, 'UTF-8'),
    'discovery_setup_intro'    => htmlspecialchars($LANG_AGENT['discovery_setup_intro'], ENT_QUOTES, 'UTF-8'),
    'discovery_rewrite_rule'   => htmlspecialchars($LANG_AGENT['discovery_rewrite_rule'], ENT_QUOTES, 'UTF-8'),
    'discovery_test_url'       => htmlspecialchars($discoveryTestUrl, ENT_QUOTES, 'UTF-8'),
    'discovery_test_label'     => htmlspecialchars($LANG_AGENT['discovery_test'], ENT_QUOTES, 'UTF-8'),
    'discovery_direct_url'     => htmlspecialchars($discoveryDirectUrl, ENT_QUOTES, 'UTF-8'),
    'discovery_direct_label'   => htmlspecialchars($LANG_AGENT['discovery_direct'], ENT_QUOTES, 'UTF-8'),
    'runtime_label'            => htmlspecialchars($LANG_AGENT['runtime'], ENT_QUOTES, 'UTF-8')
));

$runtime = AGENT_getRuntimeCapabilities();
$runtime[$LANG_AGENT['site_namespace']] = AGENT_getSiteNamespace();
$runtime[$LANG_AGENT['cache_path']] = AGENT_getCachePath();

foreach ($runtime as $name => $value) {
    if (is_bool($value)) {
        $value = $value ? $LANG_AGENT['available'] : $LANG_AGENT['unavailable'];
    }

    $T->set_var(array(
        'runtime_name'  => htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8'),
        'runtime_value' => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
    ));
    $T->parse('runtime_rows', 'runtime_row', true);
}

$content = $T->finish($T->parse('output', 'page'));
COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_AGENT['admin_title'])));
