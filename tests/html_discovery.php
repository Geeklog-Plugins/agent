<?php

$root = dirname(__DIR__);
require_once $root . '/lib/html-discovery.php';

$cases = array(
    array(
        array('PHP_SELF' => '/article.php', 'REQUEST_URI' => '/article.php/example-story'),
        array(),
        array('provider' => 'stories', 'id' => 'example-story')
    ),
    array(
        array('PHP_SELF' => '/article.php', 'REQUEST_URI' => '/article.php?story=query-story'),
        array('story' => 'query-story'),
        array('provider' => 'stories', 'id' => 'query-story')
    ),
    array(
        array('PHP_SELF' => '/staticpages/index.php', 'REQUEST_URI' => '/staticpages/index.php/example-page'),
        array(),
        array('provider' => 'staticpages', 'id' => 'example-page')
    ),
    array(
        array('PHP_SELF' => '/index.php', 'REQUEST_URI' => '/index.php/article/routed-story'),
        array(),
        array('provider' => 'stories', 'id' => 'routed-story')
    ),
    array(
        array('PHP_SELF' => '/admin/index.php', 'REQUEST_URI' => '/admin/index.php'),
        array(),
        array()
    ),
    array(
        array('PHP_SELF' => '/agent/resource.php', 'REQUEST_URI' => '/agent/resource.php?provider=stories&id=example'),
        array(),
        array()
    )
);

foreach ($cases as $case) {
    $actual = AGENT_htmlDiscoveryRequestContext($case[0], $case[1]);
    if ($actual !== $case[2]) {
        fwrite(STDERR, 'HTML discovery route detection failed.' . PHP_EOL);
        var_export($actual);
        fwrite(STDERR, PHP_EOL);
        exit(1);
    }
}

echo 'Agent HTML discovery route checks passed.' . PHP_EOL;
