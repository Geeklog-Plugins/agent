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

function AGENT_ADMIN_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$display = COM_siteHeader('menu', $LANG_AGENT['admin_title']);
$display .= COM_startBlock($LANG_AGENT['admin_title']);
$display .= '<p>' . AGENT_ADMIN_h($LANG_AGENT['read_only_notice']) . '</p>';

$display .= '<p><a href="' . AGENT_ADMIN_h($_CONF['site_admin_url'] . '/configuration.php?conf_group=agent') . '">'
    . AGENT_ADMIN_h($LANG_AGENT['configuration']) . '</a></p>';

$runtime = AGENT_getRuntimeCapabilities();
$display .= '<h2>' . AGENT_ADMIN_h($LANG_AGENT['runtime']) . '</h2>';
$display .= '<table class="admin-list" style="width:100%">';
foreach ($runtime as $name => $value) {
    if (is_bool($value)) {
        $value = $value ? $LANG_AGENT['available'] : $LANG_AGENT['unavailable'];
    }
    $display .= '<tr><td>' . AGENT_ADMIN_h($name) . '</td><td>'
        . AGENT_ADMIN_h($value) . '</td></tr>';
}
$display .= '<tr><td>' . AGENT_ADMIN_h($LANG_AGENT['site_namespace']) . '</td><td>'
    . AGENT_ADMIN_h(AGENT_getSiteNamespace()) . '</td></tr>';
$display .= '<tr><td>' . AGENT_ADMIN_h($LANG_AGENT['cache_path']) . '</td><td>'
    . AGENT_ADMIN_h(AGENT_getCachePath()) . '</td></tr>';
$display .= '</table>';

$display .= COM_endBlock();
$display .= COM_siteFooter();

COM_output($display);
