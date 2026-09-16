<?php

$root = dirname(__DIR__);
$required = array(
    'config.php',
    'autoinstall.php',
    'install_defaults.php',
    'functions.inc',
    'plugin.json',
    'admin/index.php',
    'public_html/llms.php',
    'public_html/resource.php',
    'templates/administration.thtml',
    'language/english.php',
    'lib/text.php',
    'lib/compat.php',
    'lib/resource.php',
    'lib/providers.php',
    'lib/discovery.php',
    'lib/markdown.php'
);

foreach ($required as $path) {
    if (!is_file($root . DIRECTORY_SEPARATOR . $path)) {
        fwrite(STDERR, 'Missing required Agent file: ' . $path . PHP_EOL);
        exit(1);
    }
}

$manifest = json_decode(file_get_contents($root . '/plugin.json'), true);
if (!is_array($manifest) || json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, 'plugin.json is not valid JSON.' . PHP_EOL);
    exit(1);
}

$checks = array(
    isset($manifest['schema']) && $manifest['schema'] === 1,
    isset($manifest['id']) && $manifest['id'] === 'agent',
    isset($manifest['name']) && $manifest['name'] === 'Agent',
    isset($manifest['requires']['geeklog']) && $manifest['requires']['geeklog'] === '2.1.1',
    isset($manifest['requires']['php']) && $manifest['requires']['php'] === '5.6.0'
);
foreach ($checks as $ok) {
    if (!$ok) {
        fwrite(STDERR, 'plugin.json does not match the Agent 0.x contract.' . PHP_EOL);
        exit(1);
    }
}

$autoinstall = file_get_contents($root . '/autoinstall.php');
$functions = file_get_contents($root . '/functions.inc');
$admin = file_get_contents($root . '/admin/index.php');
$defaults = file_get_contents($root . '/install_defaults.php');
$language = file_get_contents($root . '/language/english.php');
$text = file_get_contents($root . '/lib/text.php');
$compat = file_get_contents($root . '/lib/compat.php');
$providers = file_get_contents($root . '/lib/providers.php');
$discovery = file_get_contents($root . '/lib/discovery.php');
$markdown = file_get_contents($root . '/lib/markdown.php');
$publicLlms = file_get_contents($root . '/public_html/llms.php');
$publicResource = file_get_contents($root . '/public_html/resource.php');

if (strpos($autoinstall, 'agent.admin') === false || strpos($autoinstall, 'Agent Admin') === false) {
    fwrite(STDERR, 'Required Agent permission/group is missing.' . PHP_EOL);
    exit(1);
}
if (strpos($functions, 'AGENT_getSiteNamespace') === false || strpos($functions, 'AGENT_getRuntimeCapabilities') === false) {
    fwrite(STDERR, 'Required multisite/runtime helpers are missing.' . PHP_EOL);
    exit(1);
}
foreach (array('lib/text.php', 'lib/compat.php', 'lib/resource.php', 'lib/providers.php', 'lib/discovery.php', 'lib/markdown.php') as $library) {
    if (strpos($functions, $library) === false) {
        fwrite(STDERR, 'Agent library is not wired into runtime: ' . $library . PHP_EOL);
        exit(1);
    }
}
if (strpos($functions, 'plugin_autouninstall_agent') === false) {
    fwrite(STDERR, 'Agent automatic uninstall callback is missing.' . PHP_EOL);
    exit(1);
}
if (strpos($admin, 'COM_createHTMLDocument') === false || strpos($admin, 'administration.thtml') === false) {
    fwrite(STDERR, 'Agent admin rendering contract is incomplete.' . PHP_EOL);
    exit(1);
}

$prematureControls = array('json_enabled', 'capabilities_enabled', 'cache_enabled', 'authenticated_access');
foreach ($prematureControls as $control) {
    if (strpos($defaults, $control) !== false) {
        fwrite(STDERR, 'Premature Configuration Manager control exposed: ' . $control . PHP_EOL);
        exit(1);
    }
}
foreach (array('llms_enabled', 'site_description', 'recent_limit') as $control) {
    if (strpos($defaults, $control) === false) {
        fwrite(STDERR, 'Implemented discovery control missing: ' . $control . PHP_EOL);
        exit(1);
    }
}
if (strpos($language, "'Disabled' => 0") === false || strpos($language, "'Enabled'  => 1") === false) {
    fwrite(STDERR, 'Agent boolean Configuration Manager labels are not mapped label => value.' . PHP_EOL);
    exit(1);
}

if (strpos($providers, 'PLG_getItemInfo') === false || strpos($providers, "'*'") === false) {
    fwrite(STDERR, 'Agent providers must use PLG_getItemInfo() including collection retrieval.' . PHP_EOL);
    exit(1);
}
if (strpos($providers, "'geeklog_type' => 'staticpages'") === false || strpos($providers, "'label'         => 'Articles'") === false) {
    fwrite(STDERR, 'Provider definitions or public labels are incomplete.' . PHP_EOL);
    exit(1);
}
if (strpos($providers, 'AGENT_getProviderCollectionFields') === false ||
    strpos($providers, "if ($provider === 'staticpages')") === false ||
    strpos($providers, "'date-modified'") === false) {
    fwrite(STDERR, 'Geeklog 2.1.1-safe Static Pages collection field guard is missing.' . PHP_EOL);
    exit(1);
}
if (strpos($providers, 'DB_query') !== false || strpos($providers, 'DB_getItem') !== false || strpos($providers, '$_TABLES') !== false) {
    fwrite(STDERR, 'Provider layer must not contain direct table access.' . PHP_EOL);
    exit(1);
}
if (strpos($compat, 'AGENT_compatMetaDescription') === false ||
    strpos($compat, 'meta_description') === false ||
    strpos($compat, 'DB_getItem') === false) {
    fwrite(STDERR, 'Isolated meta-description compatibility helper is incomplete.' . PHP_EOL);
    exit(1);
}
if (strpos($text, 'AGENT_removeNonContentMarkup') === false || strpos($text, 'script|style|noscript|template') === false) {
    fwrite(STDERR, 'Shared machine-text cleanup helper is incomplete.' . PHP_EOL);
    exit(1);
}

if (strpos($discovery, 'AGENT_getProviderResources') === false ||
    strpos($discovery, 'AGENT_discoveryExcerpt') === false ||
    strpos($discovery, 'AGENT_discoveryMarkdownUrl') === false ||
    strpos($discovery, "'stories'     => 'Articles'") === false ||
    strpos($discovery, 'AGENT_removeNonContentMarkup') === false ||
    strpos($publicLlms, 'AGENT_buildLlmsText') === false ||
    strpos($publicLlms, 'text/plain') === false) {
    fwrite(STDERR, 'Agent public llms discovery path is incomplete.' . PHP_EOL);
    exit(1);
}
if (strpos($markdown, 'AGENT_buildResourceMarkdown') === false ||
    strpos($markdown, 'AGENT_removeNonContentMarkup') === false ||
    strpos($publicResource, 'AGENT_buildResourceMarkdown') === false ||
    strpos($publicResource, 'text/markdown') === false) {
    fwrite(STDERR, 'Agent public Markdown resource path is incomplete.' . PHP_EOL);
    exit(1);
}

require_once $root . '/lib/text.php';
require_once $root . '/lib/resource.php';
$sample = AGENT_normalizeResource(
    'stories',
    'story',
    array(
        'id' => 'example',
        'title' => 'Example',
        'url' => 'https://example.test/article',
        'description' => 'Full body',
        'excerpt' => 'Summary',
        'type' => '',
        'date-created' => '2026-01-01',
        'date-modified' => '2026-01-02',
        'capabilities' => array('content.read', 'content.read')
    )
);
if (!is_array($sample) ||
    $sample['schema_version'] !== '1' ||
    $sample['provider'] !== 'stories' ||
    $sample['type'] !== 'story' ||
    $sample['excerpt'] !== 'Summary' ||
    $sample['content'] !== 'Full body' ||
    $sample['created'] !== '2026-01-01' ||
    $sample['modified'] !== '2026-01-02' ||
    $sample['canonical_url'] !== 'https://example.test/article' ||
    count($sample['capabilities']) !== 1 ||
    AGENT_getResourceIdentity($sample) !== 'stories:story:example') {
    fwrite(STDERR, 'Agent normalized resource contract failed.' . PHP_EOL);
    exit(1);
}
$cleaned = AGENT_removeNonContentMarkup('<p>Hello</p><script>(adsbygoogle=[]).push({});</script><p>World</p>');
if (strpos($cleaned, 'adsbygoogle') !== false || strpos($cleaned, 'Hello') === false || strpos($cleaned, 'World') === false) {
    fwrite(STDERR, 'Agent non-content markup cleanup failed.' . PHP_EOL);
    exit(1);
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
$legacyRenderingCalls = array('COM_' . 'siteHeader' . '(', 'COM_' . 'siteFooter' . '(');
foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }
    $path = $fileInfo->getPathname();
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
    if (strpos($relative, 'tests/') === 0 || strpos($relative, '.github/') === 0 || strpos($relative, 'dist/') === 0) {
        continue;
    }
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($extension !== 'php' && $extension !== 'inc') {
        continue;
    }
    $source = file_get_contents($path);
    foreach ($legacyRenderingCalls as $call) {
        if (strpos($source, $call) !== false) {
            fwrite(STDERR, 'Legacy page rendering call ' . $call . ' is not allowed in Agent runtime file: ' . $relative . PHP_EOL);
            exit(1);
        }
    }
}

echo 'Agent 0.x foundation/resource/provider/discovery/markdown checks passed.' . PHP_EOL;
