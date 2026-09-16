<?php

/**
 * Minimal Agent administration/status page.
 */

require_once '../../../lib-common.php';

if (!SEC_hasRights('agent.admin')) {
    COM_accessLog('User ' . (int) $_USER['uid'] . ' tried to access Agent administration.');
    echo COM_refresh($_CONF['site_url'] . '/index.php');
    exit;
}

$display = COM_siteHeader('menu', $LANG_AGENT['admin_title']);
$display .= COM_startBlock($LANG_AGENT['admin_title']);
$display .= '<p>' . htmlspecialchars($LANG_AGENT['read_only_notice'], ENT_QUOTES, COM_getEncodingtobehonest()) . '</p>';

$display .= '<p><a href="' . htmlspecialchars($_CONF['site_admin_url'] . '/configuration.php?conf_group=agent', ENT_QUOTES, COM_getEncodingtobehonest()) . '">'
    . htmlspecialchars($LANG_AGENT['configuration'], ENT_QUOTES, COM_getEncodingtobehonest()) . '</a></p>';

$runtime = AGENT_getRuntimeCapabilities();
$display .= '<h2>' . htmlspecialchars($LANG_AGENT['runtime'], ENT_QUOTES, COM_getEncodingtobehonest()) . '</h2>';
$display .= '<table class="admin-list" style="width:100%">';
foreach ($runtime as $name => $value) {
    if (is_bool($value)) {
        $value = $value ? $LANG_AGENT['available'] : $LANG_AGENT['unavailable'];
    }
    $display .= '<tr><td>' . htmlspecialchars($name, ENT_QUOTES, COM_getEncodingtobehonest()) . '</td><td>'
        . htmlspecialchars((string) $value, ENT_QUOTES, COM_getEncodingtobehonest()) . '</td></tr>';
}
$display .= '<tr><td>' . htmlspecialchars($LANG_AGENT['site_namespace'], ENT_QUOTES, COM_getEncodingtobehonest()) . '</td><td>'
    . htmlspecialchars(AGENT_getSiteNamespace(), ENT_QUOTES, COM_getEncodingtobehonest()) . '</td></tr>';
$display .= '<tr><td>' . htmlspecialchars($LANG_AGENT['cache_path'], ENT_QUOTES, COM_getEncodingtobehonest()) . '</td><td>'
    . htmlspecialchars(AGENT_getCachePath(), ENT_QUOTES, COM_getEncodingtobehonest()) . '</td></tr>';
$display .= '</table>';

$display .= COM_endBlock();
$display .= COM_siteFooter();

echo $display;
