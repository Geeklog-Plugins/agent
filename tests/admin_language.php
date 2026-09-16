<?php

$root = dirname(__DIR__);
$LANG_AGENT = array();
$LANG_configsections = array();
$LANG_configsubgroups = array();
$LANG_tab = array();
$LANG_fs = array();
$LANG_confignames = array();
$LANG_configselects = array();

require $root . '/language/english.php';

$admin = file_get_contents($root . '/admin/index.php');
preg_match_all('/\$LANG_AGENT\[[\'\"]([^\'\"]+)[\'\"]\]/', $admin, $matches);
$keys = array_unique(isset($matches[1]) ? $matches[1] : array());
$missing = array();

foreach ($keys as $key) {
    if (!array_key_exists($key, $LANG_AGENT)) {
        $missing[] = $key;
    }
}

if (!empty($missing)) {
    fwrite(STDERR, 'Missing Agent admin language keys: ' . implode(', ', $missing) . PHP_EOL);
    exit(1);
}

$template = file_get_contents($root . '/templates/administration.thtml');
if (strpos($template, 'Full rewrite example') !== false) {
    fwrite(STDERR, 'Agent admin template still contains an untranslated visible label.' . PHP_EOL);
    exit(1);
}

if (strpos($template, '{admin_title}') === false || strpos($template, '{public_endpoints_label}') === false) {
    fwrite(STDERR, 'Agent admin template is missing required localized UI bindings.' . PHP_EOL);
    exit(1);
}

echo 'Agent admin language coverage passed for ' . count($keys) . ' keys.' . PHP_EOL;
