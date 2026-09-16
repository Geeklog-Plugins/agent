<?php

/**
 * Isolated Geeklog compatibility helpers for data not exposed consistently by
 * the shared Item Info contract on the supported 2.1.1-2.2.2 range.
 *
 * These helpers must only enrich resources that have already passed through
 * the owning provider's permission-aware API.
 *
 * @package Agent
 */

/**
 * Return provider table/id metadata for supported compatibility lookups.
 */
function AGENT_compatProviderStorage($provider)
{
    global $_TABLES;

    if (!is_array($_TABLES)) {
        return false;
    }

    if ($provider === 'stories' && !empty($_TABLES['stories'])) {
        return array(
            'table' => $_TABLES['stories'],
            'id_field' => 'sid'
        );
    }

    if ($provider === 'staticpages' && !empty($_TABLES['staticpage'])) {
        return array(
            'table' => $_TABLES['staticpage'],
            'id_field' => 'sp_id'
        );
    }

    return false;
}

/**
 * Build a safe WHERE clause for one already-authorized provider resource.
 */
function AGENT_compatResourceWhere($provider, $id)
{
    if (!function_exists('DB_escapeString')) {
        return false;
    }

    $storage = AGENT_compatProviderStorage($provider);
    if ($storage === false) {
        return false;
    }

    return array(
        'table' => $storage['table'],
        'where' => $storage['id_field'] . " = '" . DB_escapeString((string) $id) . "'"
    );
}

/**
 * Read the stored meta description for an already-authorized resource.
 */
function AGENT_compatMetaDescription($provider, $id)
{
    if (!function_exists('DB_getItem')) {
        return '';
    }

    $lookup = AGENT_compatResourceWhere($provider, $id);
    if ($lookup === false) {
        return '';
    }

    $value = DB_getItem($lookup['table'], 'meta_description', $lookup['where']);
    if ($value === null || $value === false) {
        return '';
    }

    return trim((string) $value);
}

/**
 * Read stored editorial HTML for an already-authorized resource.
 *
 * Item Info remains the authority for existence, visibility and permissions.
 * This helper is only used after that contract succeeds, so Agent can preserve
 * semantic HTML that older Geeklog callbacks may flatten or normalize.
 */
function AGENT_compatRawContent($provider, $id)
{
    if (!function_exists('DB_getItem')) {
        return '';
    }

    $lookup = AGENT_compatResourceWhere($provider, $id);
    if ($lookup === false) {
        return '';
    }

    if ($provider === 'stories') {
        $intro = DB_getItem($lookup['table'], 'introtext', $lookup['where']);
        $body = DB_getItem($lookup['table'], 'bodytext', $lookup['where']);

        $intro = ($intro === null || $intro === false) ? '' : stripslashes((string) $intro);
        $body = ($body === null || $body === false) ? '' : stripslashes((string) $body);
        $value = trim($intro . ($intro !== '' && $body !== '' ? "\n\n" : '') . $body);

        if ($value !== '' && function_exists('PLG_replaceTags')) {
            $value = PLG_replaceTags($value, '', false, 'article', (string) $id);
        }

        return trim((string) $value);
    }

    if ($provider === 'staticpages') {
        $value = DB_getItem($lookup['table'], 'sp_content', $lookup['where']);
        if ($value === null || $value === false) {
            return '';
        }

        $value = stripslashes((string) $value);
        if ($value !== '' && function_exists('PLG_replaceTags')) {
            $value = PLG_replaceTags($value, '', false, 'staticpages', (string) $id);
        }

        return trim((string) $value);
    }

    return '';
}
