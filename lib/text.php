<?php

/**
 * Shared machine-facing text cleanup helpers.
 *
 * @package Agent
 */

/**
 * Remove markup whose body is not editorial content before strip_tags() runs.
 *
 * strip_tags() removes the element tags but keeps script/style bodies, which
 * would otherwise expose JavaScript such as AdSense snippets to agents.
 */
function AGENT_removeNonContentMarkup($value)
{
    $value = (string) $value;

    return preg_replace(
        '#<(script|style|noscript|template)\b[^>]*>.*?</\1\s*>#isu',
        ' ',
        $value
    );
}
