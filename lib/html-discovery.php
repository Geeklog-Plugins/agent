<?php

/**
 * HTML discovery helpers for public Agent resources.
 *
 * Adds lightweight machine-discovery links to normal Geeklog HTML pages
 * without changing themes or Core files.
 *
 * @package Agent
 */

function AGENT_htmlDiscoveryRequestContext($server, $request)
{
    $server = is_array($server) ? $server : array();
    $request = is_array($request) ? $request : array();

    $uri = isset($server['REQUEST_URI']) ? (string) $server['REQUEST_URI'] : '';
    $script = isset($server['PHP_SELF']) ? (string) $server['PHP_SELF'] : '';
    $path = parse_url($uri, PHP_URL_PATH);
    if (!is_string($path)) {
        $path = '';
    }

    $haystack = strtolower($script . ' ' . $path);
    if (strpos($haystack, '/admin/') !== false || strpos($haystack, '/agent/') !== false) {
        return array();
    }

    $storyId = isset($request['story']) ? trim((string) $request['story']) : '';
    if ($storyId === '' && function_exists('COM_getArgument')) {
        $storyId = trim((string) COM_getArgument('story'));
    }
    if ($storyId === '') {
        if (preg_match('~/(?:article\.php|article)/([^/?#]+)~i', $path, $match)) {
            $storyId = rawurldecode($match[1]);
        } elseif (preg_match('~/index\.php/article/([^/?#]+)~i', $path, $match)) {
            $storyId = rawurldecode($match[1]);
        }
    }
    if ($storyId !== '' && (strpos($haystack, 'article') !== false || isset($request['story']))) {
        return array('provider' => 'stories', 'id' => $storyId);
    }

    $pageId = isset($request['page']) ? trim((string) $request['page']) : '';
    if ($pageId === '' && function_exists('COM_getArgument')) {
        $pageId = trim((string) COM_getArgument('page'));
    }
    if ($pageId === '') {
        if (preg_match('~/staticpages/index\.php/([^/?#]+)~i', $path, $match)) {
            $pageId = rawurldecode($match[1]);
        } elseif (preg_match('~/index\.php/staticpages/([^/?#]+)~i', $path, $match)) {
            $pageId = rawurldecode($match[1]);
        }
    }
    if ($pageId !== '' && (strpos($haystack, 'staticpages') !== false || isset($request['page']))) {
        return array('provider' => 'staticpages', 'id' => $pageId);
    }

    return array();
}

function AGENT_htmlDiscoveryUrl($path)
{
    global $_CONF;

    if (empty($_CONF['site_url'])) {
        return '';
    }

    return rtrim((string) $_CONF['site_url'], '/') . '/' . ltrim((string) $path, '/');
}

function AGENT_htmlDiscoveryResourceUrl($provider, $id)
{
    $base = AGENT_htmlDiscoveryUrl('agent/resource.php');
    if ($base === '') {
        return '';
    }

    return $base . '?provider=' . rawurlencode((string) $provider)
        . '&id=' . rawurlencode((string) $id);
}

function AGENT_buildHtmlDiscoveryHead()
{
    if (!function_exists('AGENT_isEnabled') || !AGENT_isEnabled()) {
        return '';
    }

    if (function_exists('AGENT_getConfig') && (int) AGENT_getConfig('llms_enabled', 1) !== 1) {
        return '';
    }

    $context = AGENT_htmlDiscoveryRequestContext($_SERVER, $_REQUEST);
    if (empty($context) && isset($_SERVER['PHP_SELF'])) {
        $lower = strtolower((string) $_SERVER['PHP_SELF']);
        if (strpos($lower, '/admin/') !== false || strpos($lower, '/agent/') !== false) {
            return '';
        }
    }

    $llmsUrl = AGENT_htmlDiscoveryUrl('llms.txt');
    if ($llmsUrl === '') {
        return '';
    }

    $lines = array();
    $lines[] = '<link rel="describedby" href="'
        . htmlspecialchars($llmsUrl, ENT_QUOTES, 'UTF-8')
        . '" type="text/plain">';

    if (!empty($context['provider']) && !empty($context['id'])) {
        $enabled = function_exists('AGENT_getEnabledProviders') ? AGENT_getEnabledProviders() : array();
        if (empty($enabled) || in_array($context['provider'], $enabled, true)) {
            $markdownUrl = AGENT_htmlDiscoveryResourceUrl($context['provider'], $context['id']);
            if ($markdownUrl !== '') {
                $lines[] = '<link rel="alternate" type="text/markdown" href="'
                    . htmlspecialchars($markdownUrl, ENT_QUOTES, 'UTF-8')
                    . '">';
            }
        }
    }

    return implode("\n", $lines) . "\n";
}
