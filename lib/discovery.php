<?php

/**
 * Public machine-discovery representation for Agent.
 *
 * The output is intentionally simple Markdown/plain text so it remains useful
 * to crawlers and agents without coupling Agent to a specific AI provider.
 *
 * @package Agent
 */

function AGENT_discoveryText($value)
{
    if (function_exists('AGENT_removeNonContentMarkup')) {
        $value = AGENT_removeNonContentMarkup($value);
    }
    $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    $value = preg_replace('/\[[A-Za-z][A-Za-z0-9_-]*:[^\]\r\n]*\]/u', ' ', $value);
    $value = preg_replace('/\[image(?:\d+|X)\]/iu', ' ', $value);
    $value = preg_replace('/\s+/u', ' ', $value);
    return trim($value);
}

function AGENT_discoveryExcerpt($value, $maxLength = 300)
{
    $value = AGENT_discoveryText($value);
    $maxLength = max(80, min(1000, (int) $maxLength));

    if ($value === '' || strlen($value) <= $maxLength) {
        return $value;
    }

    if (function_exists('COM_truncate')) {
        return COM_truncate($value, $maxLength, '...');
    }

    return rtrim(substr($value, 0, $maxLength - 3)) . '...';
}

function AGENT_discoveryMarkdownLabel($value)
{
    $value = AGENT_discoveryText($value);
    return str_replace(array('\\', '[', ']'), array('\\\\', '\\[', '\\]'), $value);
}

function AGENT_discoveryProviderLabel($provider)
{
    $labels = array(
        'stories'     => 'Articles',
        'staticpages' => 'Static Pages'
    );

    return isset($labels[$provider]) ? $labels[$provider] : ucfirst((string) $provider);
}

function AGENT_discoveryMarkdownUrl($provider, $id)
{
    global $_CONF;

    if (!function_exists('AGENT_buildResourceMarkdown') || empty($_CONF['site_url'])) {
        return '';
    }

    return rtrim((string) $_CONF['site_url'], '/')
        . '/agent/resource.php?provider=' . rawurlencode((string) $provider)
        . '&id=' . rawurlencode((string) $id);
}

function AGENT_discoveryJsonUrl($provider, $id)
{
    global $_CONF;

    if (!function_exists('AGENT_buildResourceJson') || empty($_CONF['site_url'])) {
        return '';
    }

    return rtrim((string) $_CONF['site_url'], '/')
        . '/agent/resource-json.php?provider=' . rawurlencode((string) $provider)
        . '&id=' . rawurlencode((string) $id);
}

function AGENT_discoverySiteDescription()
{
    global $_CONF;

    $description = AGENT_discoveryText(AGENT_getConfig('site_description', ''));
    if ($description !== '') {
        return $description;
    }

    if (isset($_CONF['meta_description'])) {
        $description = AGENT_discoveryText($_CONF['meta_description']);
        if ($description !== '') {
            return $description;
        }
    }

    if (isset($_CONF['site_slogan'])) {
        $description = AGENT_discoveryText($_CONF['site_slogan']);
        if ($description !== '') {
            return $description;
        }
    }

    return '';
}

function AGENT_buildLlmsText()
{
    global $_CONF;

    if (!AGENT_isEnabled() || (int) AGENT_getConfig('llms_enabled', 1) !== 1) {
        return '';
    }

    $siteName = isset($_CONF['site_name']) ? AGENT_discoveryText($_CONF['site_name']) : 'Geeklog site';
    $siteUrl = isset($_CONF['site_url']) ? rtrim((string) $_CONF['site_url'], '/') : '';
    $description = AGENT_discoverySiteDescription();

    $limit = (int) AGENT_getConfig('recent_limit', 10);
    if ($limit < 1) {
        $limit = 10;
    }
    $limit = min($limit, 50);

    $lines = array();
    $lines[] = '# ' . $siteName;
    $lines[] = '';
    if ($description !== '') {
        $lines[] = '> ' . $description;
        $lines[] = '';
    }

    if ($siteUrl !== '') {
        $lines[] = 'Canonical site: ' . $siteUrl . '/';
        $lines[] = '';
    }

    $lines[] = '## Public content';
    $lines[] = '';

    $hasResources = false;
    foreach (AGENT_getEnabledProviders() as $provider) {
        if (!AGENT_providerAvailable($provider)) {
            continue;
        }

        $resources = AGENT_getProviderResources(
            $provider,
            array(
                'limit' => $limit,
                'order' => 'modified-desc'
            )
        );

        if (empty($resources)) {
            continue;
        }

        $hasResources = true;
        $lines[] = '### ' . AGENT_discoveryProviderLabel($provider);
        $lines[] = '';
        foreach ($resources as $resource) {
            if (empty($resource['title']) || empty($resource['canonical_url'])) {
                continue;
            }

            $title = AGENT_discoveryMarkdownLabel($resource['title']);
            $url = (string) $resource['canonical_url'];
            $excerpt = !empty($resource['excerpt']) ? AGENT_discoveryExcerpt($resource['excerpt']) : '';
            $markdownUrl = isset($resource['id'])
                ? AGENT_discoveryMarkdownUrl($provider, $resource['id']) : '';
            $jsonUrl = isset($resource['id'])
                ? AGENT_discoveryJsonUrl($provider, $resource['id']) : '';

            $line = '- [' . $title . '](' . $url . ')';
            if ($excerpt !== '') {
                $line .= ' — ' . $excerpt;
            }

            $representations = array();
            if ($markdownUrl !== '') {
                $representations[] = '[Markdown](' . $markdownUrl . ')';
            }
            if ($jsonUrl !== '') {
                $representations[] = '[JSON](' . $jsonUrl . ')';
            }
            if (!empty($representations)) {
                $line .= ' (' . implode(' · ', $representations) . ')';
            }

            $lines[] = $line;
        }
        $lines[] = '';
    }

    if (!$hasResources) {
        $lines[] = 'No provider collection is currently available.';
        $lines[] = '';
    }

    if ($siteUrl !== '') {
        $lines[] = '## Site discovery';
        $lines[] = '';
        $lines[] = '- [Home](' . $siteUrl . '/)';
        $lines[] = '- [XML sitemap](' . $siteUrl . '/sitemap.xml)';
        if (function_exists('AGENT_buildCapabilitiesJson')) {
            $lines[] = '- [Agent capabilities](' . $siteUrl . '/agent/capabilities.php)';
        }
        $lines[] = '';
    }

    $lines[] = 'Generated by Geeklog Agent from the active site context and permission-aware provider contracts.';

    return implode("\n", $lines) . "\n";
}
