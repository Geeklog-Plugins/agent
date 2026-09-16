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

$T->set_var(array(
    'read_only_notice'    => htmlspecialchars($LANG_AGENT['read_only_notice'], ENT_QUOTES, 'UTF-8'),
    'configuration_url'   => htmlspecialchars($_CONF['site_admin_url'] . '/configuration.php?conf_group=agent', ENT_QUOTES, 'UTF-8'),
    'configuration_label' => htmlspecialchars($LANG_AGENT['configuration'], ENT_QUOTES, 'UTF-8'),
    'runtime_label'       => htmlspecialchars($LANG_AGENT['runtime'], ENT_QUOTES, 'UTF-8')
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
