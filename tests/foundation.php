<?php

$root = dirname(__DIR__);
$required = array(
    'config.php',
    'autoinstall.php',
    'install_defaults.php',
    'functions.inc',
    'plugin.json',
    'admin/index.php',
    'templates/administration.thtml',
    'language/english.php',
    'lib/resource.php',
    'lib/providers.php'
);

foreach ($required as $path) {
    if (!is_file($root . DIRECTORY_SEPARATOR . $path)) {
        fwrite(STDERR, 'Missing required foundation file: ' . $path . PHP_EOL);
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
$providers = file_get_contents($root . '/lib/providers.php');

if (strpos($autoinstall, 'agent.admin') === false || strpos($autoinstall, 'Agent Admin') === false) {
    fwrite(STDERR, 'Required Agent permission/group is missing.' . PHP_EOL);
    exit(1);
}
if (strpos($functions, 'AGENT_getSiteNamespace') === false || strpos($functions, 'AGENT_getRuntimeCapabilities') === false) {
    fwrite(STDERR, 'Required multisite/runtime helpers are missing.' . PHP_EOL);
    exit(1);
}
if (strpos($functions, "lib/resource.php") === false || strpos($functions, "lib/providers.php") === false) {
    fwrite(STDERR, 'Agent normalized resource/provider libraries are not wired into runtime.' . PHP_EOL);
    exit(1);
}
if (strpos($admin, 'COM_createHTMLDocument') === false) {
    fwrite(STDERR, 'Agent admin page must use COM_createHTMLDocument().' . PHP_EOL);
    exit(1);
}
if (strpos($admin, 'administration.thtml') === false) {
    fwrite(STDERR, 'Agent admin page must render through administration.thtml.' . PHP_EOL);
    exit(1);
}

/* Future controls must not be exposed before their features exist. */
$prematureControls = array(
    'llms_enabled',
    'markdown_enabled',
    'json_enabled',
    'capabilities_enabled',
    'cache_enabled',
    'authenticated_access'
);
foreach ($prematureControls as $control) {
    if (strpos($defaults, $control) !== false) {
        fwrite(STDERR, 'Premature Configuration Manager control exposed: ' . $control . PHP_EOL);
        exit(1);
    }
}

/* Geeklog select arrays use human label => stored value. */
if (strpos($language, "'Disabled' => 0") === false || strpos($language, "'Enabled'  => 1") === false) {
    fwrite(STDERR, 'Agent boolean Configuration Manager labels are not mapped label => value.' . PHP_EOL);
    exit(1);
}

/* Initial providers must consume Geeklog contracts, not provider SQL tables. */
if (strpos($providers, 'PLG_getItemInfo') === false) {
    fwrite(STDERR, 'Agent providers must use PLG_getItemInfo().' . PHP_EOL);
    exit(1);
}
if (strpos($providers, 'DB_query') !== false || strpos($providers, 'DB_getItem') !== false ||
    strpos($providers, '$_TABLES') !== false) {
    fwrite(STDERR, 'Initial Agent providers must not query Geeklog/plugin tables directly.' . PHP_EOL);
    exit(1);
}

/* Validate the provider-neutral resource model in isolation. */
require_once $root . '/lib/resource.php';
$sample = AGENT_normalizeResource(
    'stories',
    'story',
    array(
        'id' => 'example',
        'title' => 'Example',
        'url' => 'https://example.test/article',
        'description' => 'Summary',
        'date-created' => '2026-01-01',
        'date-modified' => '2026-01-02',
        'capabilities' => array('content.read', 'content.read')
    )
);
if (!is_array($sample) ||
    $sample['schema_version'] !== '1' ||
    $sample['provider'] !== 'stories' ||
    $sample['excerpt'] !== 'Summary' ||
    $sample['created'] !== '2026-01-01' ||
    $sample['modified'] !== '2026-01-02' ||
    $sample['canonical_url'] !== 'https://example.test/article' ||
    count($sample['capabilities']) !== 1 ||
    AGENT_getResourceIdentity($sample) !== 'stories:story:example') {
    fwrite(STDERR, 'Agent normalized resource contract failed.' . PHP_EOL);
    exit(1);
}

/*
 * Rendering convention guard.
 *
 * Agent targets Geeklog 2.1.1 through 2.2.2. New/modernized HTML pages must
 * use COM_createHTMLDocument() rather than the legacy page assembly path.
 */
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);
$legacyRenderingCalls = array(
    'COM_' . 'siteHeader' . '(',
    'COM_' . 'siteFooter' . '('
);

foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }

    $path = $fileInfo->getPathname();
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));

    if (strpos($relative, 'tests/') === 0 ||
        strpos($relative, '.github/') === 0 ||
        strpos($relative, 'dist/') === 0) {
        continue;
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($extension !== 'php' && $extension !== 'inc') {
        continue;
    }

    $source = file_get_contents($path);
    foreach ($legacyRenderingCalls as $call) {
        if (strpos($source, $call) !== false) {
            fwrite(
                STDERR,
                'Legacy page rendering call ' . $call . ' is not allowed in Agent runtime file: ' . $relative . PHP_EOL
            );
            exit(1);
        }
    }
}

echo 'Agent 0.x foundation/resource/provider checks passed.' . PHP_EOL;
