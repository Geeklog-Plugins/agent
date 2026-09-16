<?php

/**
 * Agent provider access built on Geeklog interoperability contracts.
 *
 * Providers remain authoritative for permissions and URL construction. Agent
 * consumes PLG_getItemInfo() and normalizes the result; it does not query
 * another plugin's tables directly.
 *
 * @package Agent
 */

/**
 * Provider definitions used by the first Agent discovery layer.
 */
function AGENT_getProviderCatalog()
{
    return array(
        'stories' => array(
            'geeklog_type' => 'story',
            'resource_type' => 'story'
        ),
        'staticpages' => array(
            'geeklog_type' => 'staticpages',
            'resource_type' => 'staticpage'
        )
    );
}

/**
 * Report whether a provider can be queried in the active Geeklog context.
 */
function AGENT_providerAvailable($provider)
{
    global $_PLUGINS;

    $catalog = AGENT_getProviderCatalog();
    if (!isset($catalog[$provider]) || !function_exists('PLG_getItemInfo')) {
        return false;
    }

    if ($provider === 'stories') {
        return true;
    }

    return is_array($_PLUGINS) && in_array($provider, $_PLUGINS, true);
}

/**
 * Describe effective read capabilities without protocol-specific schemas.
 */
function AGENT_getProviderCapabilities($provider)
{
    $capabilities = array();
    if (!AGENT_providerAvailable($provider)) {
        return $capabilities;
    }

    $capabilities[] = 'content.read';

    $catalog = AGENT_getProviderCatalog();
    $type = $catalog[$provider]['geeklog_type'];
    $callback = 'plugin_getiteminfo_' . $type;

    if ($provider === 'stories' || function_exists($callback)) {
        $capabilities[] = 'content.collection';
    }

    return $capabilities;
}

/**
 * Convert a PLG_getItemInfo return value to named fields.
 */
function AGENT_mapItemInfoResult($fields, $result)
{
    if (!is_array($fields)) {
        return array();
    }

    if (!is_array($result)) {
        if (count($fields) === 1 && $result !== null && $result !== '') {
            return array($fields[0] => $result);
        }
        return array();
    }

    $mapped = array();
    $associative = false;
    foreach (array_keys($result) as $key) {
        if (!is_int($key)) {
            $associative = true;
            break;
        }
    }

    if ($associative) {
        foreach ($fields as $field) {
            if (array_key_exists($field, $result)) {
                $mapped[$field] = $result[$field];
            }
        }
        return $mapped;
    }

    foreach ($fields as $index => $field) {
        if (array_key_exists($index, $result)) {
            $mapped[$field] = $result[$index];
        }
    }

    return $mapped;
}

/**
 * Read one resource through Geeklog's Item Info contract.
 *
 * Empty/denied resources return false. The owning provider remains responsible
 * for ACL checks; Agent does not bypass them.
 */
function AGENT_getProviderResource($provider, $id, $uid = 0)
{
    if (!AGENT_providerAvailable($provider)) {
        return false;
    }

    $catalog = AGENT_getProviderCatalog();
    $definition = $catalog[$provider];
    $fields = array(
        'id',
        'title',
        'url',
        'description',
        'date-created',
        'date-modified',
        'uid',
        'author',
        'hits',
        'type',
        'subtype'
    );

    $result = PLG_getItemInfo(
        $definition['geeklog_type'],
        $id,
        implode(',', $fields),
        (int) $uid,
        array()
    );

    $raw = AGENT_mapItemInfoResult($fields, $result);
    if (empty($raw) || empty($raw['url'])) {
        return false;
    }

    $raw['capabilities'] = AGENT_getProviderCapabilities($provider);

    return AGENT_normalizeResource(
        $provider,
        $definition['resource_type'],
        $raw,
        array('id' => (string) $id)
    );
}

/**
 * Expose provider state for diagnostics and later discovery output.
 */
function AGENT_getProviderStatus()
{
    $status = array();
    foreach (AGENT_getProviderCatalog() as $provider => $definition) {
        $status[$provider] = array(
            'available' => AGENT_providerAvailable($provider),
            'resource_type' => $definition['resource_type'],
            'capabilities' => AGENT_getProviderCapabilities($provider)
        );
    }

    return $status;
}
