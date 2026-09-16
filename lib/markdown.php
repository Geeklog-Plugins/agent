<?php

/**
 * Provider-neutral Markdown representation for Agent resources.
 *
 * @package Agent
 */

/**
 * Convert a small, safe subset of editorial HTML to Markdown.
 *
 * Agent deliberately does not attempt to reproduce presentation HTML. It
 * preserves only semantic structure useful to machine readers: headings,
 * lists, blockquotes, emphasis and ordinary links.
 */
function AGENT_markdownContent($value)
{
    $value = (string) $value;
    if (function_exists('AGENT_removeNonContentMarkup')) {
        $value = AGENT_removeNonContentMarkup($value);
    }

    // Preserve ordinary links before stripping remaining HTML.
    $value = preg_replace_callback(
        '/<a\b[^>]*\bhref\s*=\s*(["\'])(.*?)\1[^>]*>(.*?)<\/a>/isu',
        function ($matches) {
            $url = html_entity_decode(trim(strip_tags($matches[2])), ENT_QUOTES, 'UTF-8');
            $label = html_entity_decode(trim(strip_tags($matches[3])), ENT_QUOTES, 'UTF-8');
            if ($url === '' || $label === '') {
                return $label;
            }
            $label = str_replace(array('\\', '[', ']'), array('\\\\', '\\[', '\\]'), $label);
            return '[' . $label . '](' . $url . ')';
        },
        $value
    );

    // Keep document hierarchy below Agent's own "## Content" section.
    $headingMap = array(
        'h1' => '###',
        'h2' => '###',
        'h3' => '####',
        'h4' => '#####',
        'h5' => '######',
        'h6' => '######'
    );
    foreach ($headingMap as $tag => $prefix) {
        $value = preg_replace_callback(
            '/<' . $tag . '\b[^>]*>(.*?)<\/' . $tag . '>/isu',
            function ($matches) use ($prefix) {
                $text = trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES, 'UTF-8'));
                return $text === '' ? '' : "\n\n" . $prefix . ' ' . $text . "\n\n";
            },
            $value
        );
    }

    $value = preg_replace_callback(
        '/<li\b[^>]*>(.*?)<\/li>/isu',
        function ($matches) {
            $text = trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES, 'UTF-8'));
            return $text === '' ? '' : "\n- " . $text;
        },
        $value
    );

    $value = preg_replace_callback(
        '/<blockquote\b[^>]*>(.*?)<\/blockquote>/isu',
        function ($matches) {
            $text = trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES, 'UTF-8'));
            if ($text === '') {
                return '';
            }
            $text = preg_replace('/\s+/u', ' ', $text);
            return "\n\n> " . $text . "\n\n";
        },
        $value
    );

    $value = preg_replace('/<(strong|b)\b[^>]*>(.*?)<\/\1>/isu', '**$2**', $value);
    $value = preg_replace('/<(em|i)\b[^>]*>(.*?)<\/\1>/isu', '*$2*', $value);
    $value = preg_replace('/<\s*br\s*\/?\s*>/iu', "\n", $value);
    $value = preg_replace('/<\s*\/\s*(p|div|ul|ol)\s*>/iu', "\n\n", $value);

    $value = html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8');
    $value = preg_replace('/\[[A-Za-z][A-Za-z0-9_-]*:[^\]\r\n]*\]/u', ' ', $value);
    $value = preg_replace('/\[image(?:\d+|X)\]/iu', ' ', $value);
    $value = str_replace("\r", '', $value);

    $lines = explode("\n", $value);
    foreach ($lines as $index => $line) {
        $line = preg_replace('/[\t ]+/u', ' ', $line);
        $lines[$index] = rtrim($line);
    }

    $value = trim(implode("\n", $lines));
    $value = preg_replace('/\n{3,}/', "\n\n", $value);

    return trim($value);
}

function AGENT_markdownDate($value)
{
    if ($value === null || $value === '') {
        return '';
    }

    if (is_numeric($value)) {
        return gmdate('c', (int) $value);
    }

    return trim((string) $value);
}

function AGENT_buildResourceMarkdown($provider, $id)
{
    if (!AGENT_isEnabled()) {
        return '';
    }

    $resource = AGENT_getProviderResource($provider, $id, 0);
    if (!is_array($resource) || empty($resource['title']) || empty($resource['canonical_url'])) {
        return '';
    }

    $title = AGENT_discoveryText($resource['title']);
    $excerpt = !empty($resource['excerpt']) ? AGENT_discoveryExcerpt($resource['excerpt'], 500) : '';
    $content = !empty($resource['content']) ? AGENT_markdownContent($resource['content']) : '';

    $lines = array();
    $lines[] = '# ' . $title;
    $lines[] = '';
    $lines[] = 'Canonical URL: ' . $resource['canonical_url'];
    $lines[] = 'Provider: ' . $resource['provider'];
    $lines[] = 'Type: ' . $resource['type'];

    if (!empty($resource['author'])) {
        $lines[] = 'Author: ' . AGENT_discoveryText($resource['author']);
    }
    if (!empty($resource['language'])) {
        $lines[] = 'Language: ' . AGENT_discoveryText($resource['language']);
    }

    $created = AGENT_markdownDate($resource['created']);
    if ($created !== '') {
        $lines[] = 'Created: ' . $created;
    }
    $modified = AGENT_markdownDate($resource['modified']);
    if ($modified !== '') {
        $lines[] = 'Modified: ' . $modified;
    }

    if ($excerpt !== '') {
        $lines[] = '';
        $lines[] = '## Summary';
        $lines[] = '';
        $lines[] = $excerpt;
    }

    if ($content !== '') {
        $lines[] = '';
        $lines[] = '## Content';
        $lines[] = '';
        $lines[] = $content;
    }

    $lines[] = '';
    $lines[] = '---';
    $lines[] = 'Generated by Geeklog Agent from the active site context and permission-aware provider contract.';

    return implode("\n", $lines) . "\n";
}
