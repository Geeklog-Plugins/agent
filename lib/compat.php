<?php

/**
 * Isolated Geeklog compatibility helpers for data not exposed by the shared
 * Item Info contract on the supported 2.1.1-2.2.2 range.
 *
 * These helpers must only enrich resources that have already passed through
 * the owning provider's permission-aware API.
 *
 * @package Agent
 */

/**
 * Read the stored meta description for an already-authorized resource.
 *
 * Geeklog stores meta_description for Stories and Static Pages, but the
 * historical plugin_getiteminfo_* contract does not expose it consistently.
 * Keep this small direct lookup isolated so it can be removed if/when the
 * shared contract gains this field.
 */
function AGENT_compatMetaDescription($provider, $id)
{
    global $_TABLES;

    if (!function_exists('DB_getItem') || !function_exists('DB_escapeString') ||
        !is_array($_TABLES)) {
        return '';
    }

    if ($provider === 'stories' && !empty($_TABLES['stories'])) {
        $table = $_TABLES['stories'];
        $idField = 'sid';
    } elseif ($provider === 'staticpages' && !empty($_TABLES['staticpage'])) {
        $table = $_TABLES['staticpage'];
        $idField = 'sp_id';
    } else {
        return '';
    }

    $where = $idField . " = '" . DB_escapeString((string) $id) . "'";
    $value = DB_getItem($table, 'meta_description', $where);

    if ($value === null || $value === false) {
        return '';
    }

    return trim((string) $value);
}
