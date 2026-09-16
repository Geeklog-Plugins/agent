<?php

/**
 * Lightweight source guard for the maintained PHP 5.6 language subset.
 * The real parser check is performed by running php -l under each CI runtime.
 */

$root = dirname(__DIR__);
$files = array();
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    $path = $file->getPathname();
    if (strpos($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false) {
        continue;
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($extension === 'php' || $extension === 'inc') {
        $files[] = $path;
    }
}

$forbidden = array(
    'null coalescing operator' => '/\?\?/',
    'spaceship operator'       => '/<=>/',
    'nullsafe operator'        => '/\?->/',
    'arrow function'           => '/\bfn\s*\(/',
    'match expression'         => '/\bmatch\s*\(/',
    'enum declaration'         => '/\benum\s+[A-Za-z_]/'
);

$failed = false;
foreach ($files as $path) {
    $source = file_get_contents($path);
    foreach ($forbidden as $label => $pattern) {
        if (preg_match($pattern, $source)) {
            fwrite(STDERR, $path . ': forbidden PHP >5.6 construct: ' . $label . PHP_EOL);
            $failed = true;
        }
    }
}

if ($failed) {
    exit(1);
}

echo 'PHP 5.6 subset source guard passed for ' . count($files) . ' files.' . PHP_EOL;
