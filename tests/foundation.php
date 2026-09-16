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
    'language/english.php'
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
        fwrite(STDERR, 'plugin.json does not match the Agent 0.1.0 contract.' . PHP_EOL);
        exit(1);
    }
}

$autoinstall = file_get_contents($root . '/autoinstall.php');
$functions = file_get_contents($root . '/functions.inc');
$admin = file_get_contents($root . '/admin/index.php');
if (strpos($autoinstall, 'agent.admin') === false || strpos($autoinstall, 'Agent Admin') === false) {
    fwrite(STDERR, 'Required Agent permission/group is missing.' . PHP_EOL);
    exit(1);
}
if (strpos($functions, 'AGENT_getSiteNamespace') === false || strpos($functions, 'AGENT_getRuntimeCapabilities') === false) {
    fwrite(STDERR, 'Required multisite/runtime helpers are missing.' . PHP_EOL);
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

/*
 * Rendering convention guard.
 *
 * Agent targets Geeklog 2.1.1 through 2.2.2. New/modernized HTML pages must
 * use COM_createHTMLDocument() rather than the legacy COM_siteHeader() /
 * COM_siteFooter() page assembly path. Scan every runtime PHP/INC file so a
 * future admin or public page cannot silently reintroduce that incompatibility.
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

echo 'Agent 0.1.0 foundation checks passed.' . PHP_EOL;
