<?php

$root = dirname(__DIR__);
require_once $root . '/lib/text.php';
require_once $root . '/lib/discovery.php';

$GLOBALS['_AGENT_TEST_CONFIG'] = array();

function AGENT_getConfig($key, $default)
{
    return array_key_exists($key, $GLOBALS['_AGENT_TEST_CONFIG'])
        ? $GLOBALS['_AGENT_TEST_CONFIG'][$key]
        : $default;
}

$_CONF = array(
    'meta_description' => 'Geeklog meta description',
    'site_slogan' => 'Geeklog site slogan'
);

$GLOBALS['_AGENT_TEST_CONFIG']['site_description'] = 'Agent override';
if (AGENT_discoverySiteDescription() !== 'Agent override') {
    fwrite(STDERR, 'Agent site_description override was not preferred.' . PHP_EOL);
    exit(1);
}

$GLOBALS['_AGENT_TEST_CONFIG']['site_description'] = '';
if (AGENT_discoverySiteDescription() !== 'Geeklog meta description') {
    fwrite(STDERR, 'Geeklog meta_description fallback was not used.' . PHP_EOL);
    exit(1);
}

$_CONF['meta_description'] = '';
if (AGENT_discoverySiteDescription() !== 'Geeklog site slogan') {
    fwrite(STDERR, 'Geeklog site_slogan fallback was not used.' . PHP_EOL);
    exit(1);
}

$_CONF['site_slogan'] = '';
if (AGENT_discoverySiteDescription() !== '') {
    fwrite(STDERR, 'Empty description fallback should return an empty string.' . PHP_EOL);
    exit(1);
}

echo 'Agent site description fallback contract passed.' . PHP_EOL;
