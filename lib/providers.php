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
            'resource_type' => 'story',
            'label'         => 'Stories'
        ),
        'staticpages' => array(
            'geeklog_type' => 'staticpages',
            'resource_type' => 'staticpage',
            'label'         => 'Static Pages'
        )
    );
}

/**
 * Return the provider ids enabled for the active Geeklog site.
 */
function AGENT_getEnabledProviders()
{
    $configured = AGENT_getConfig('providers_enabled', 'stories,staticpages');
    if (is_array($configured)) {
        $values = $configured;
    } else {
        $values = explode(',', (string) $configured);
    }

    $catalog = AGENT_getProviderCatalog();
    $providers = array();
    foreach ($values as $value) {
        $provider = strtolower(trim((string) $value));
        if ($provider !== '' && isset($catalog[$provider]) &&
            !in_array($provider, $providers, true)) {
            $providers[] = $provider;
        }
    }

    return $providers;
}

/**
 * Report whether a provider can be queried in the active Geeklog context.
 */
function AGENT_providerAvailable($provider)
{
    global $_PLUGINS;

    if (!AGENT_isEnabled() ||
        !in_array($provider, AGENT_getEnabledProviders(), true)) {
        return false;
    }

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
 * Item Info fields requested for one complete resource.
 */
function AGENT_getProviderItemFields()
{
    return array(
        'id',
        'title',
        'url',
        'excerpt',
        'description',
        'date-created',
        'date-modified',
        'uid',
        'author',
        'hits',
        'type',
        'subtype'
    );
}

/**
 * Fields safe and inexpensive to request for a provider collection.
 *
 * Geeklog 2.1.1 Static Pages has a core bug in
 * plugin_getiteminfo_staticpages('*', ...): requesting description/excerpt
 * resets its collection accumulator to a string before using [] on it. Keep
 * collection discovery to metadata, then hydrate selected pages individually.
 *
 * Stories can expose `excerpt` directly; avoid requesting their full
 * `description` for every collection item merely to build /llms.txt.
 */
function AGENT_getProviderCollectionFields($provider)
{
    if ($provider === 'staticpages') {
        return array(
            'id',
            'title',
            'url',
            'date-modified'
        );
    }

    if ($provider === 'stories') {
        return array(
            'id',
            'title',
            'url',
            'excerpt',
            'date-created',
            'date-modified',
            'uid',
            'author',
            'hits',
            'type',
            'subtype'
        );
    }

    return AGENT_getProviderItemFields();
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
    $fields = AGENT_getProviderItemFields();

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

    // Item Info `description` is the owning provider's complete readable body.
    if (!empty($raw['description'])) {
        $raw['content'] = $raw['description'];
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
 * Sort normalized resources by modified date, newest first.
 */
function AGENT_sortResourcesModifiedDesc(&$resources)
{
    usort($resources, function ($a, $b) {
        $aValue = isset($a['modified']) ? $a['modified'] : '';
        $bValue = isset($b['modified']) ? $b['modified'] : '';
        $aTime = is_numeric($aValue) ? (int) $aValue : strtotime((string) $aValue);
        $bTime = is_numeric($bValue) ? (int) $bValue : strtotime((string) $bValue);
        if ($aTime === $bTime) {
            return 0;
        }
        return ($aTime > $bTime) ? -1 : 1;
    });
}

/**
 * Read a collection through Geeklog's Item Info '*' convention.
 *
 * Options are passed through to the owning provider where supported. Agent
 * still applies its own final sort/limit because older Geeklog callbacks such
 * as Static Pages 2.1.1 ignore collection options.
 */
function AGENT_getProviderResources($provider, $options = array(), $uid = 0)
{
    $resources = array();
    if (!AGENT_providerAvailable($provider)) {
        return $resources;
    }

    if (!in_array('content.collection', AGENT_getProviderCapabilities($provider), true)) {
        return $resources;
    }

    if (!is_array($options)) {
        $options = array();
    }

    $limit = isset($options['limit']) ? max(1, min(100, (int) $options['limit'])) : 100;
    $options['limit'] = $limit;

    $catalog = AGENT_getProviderCatalog();
    $definition = $catalog[$provider];
    $fields = AGENT_getProviderCollectionFields($provider);

    $result = PLG_getItemInfo(
        $definition['geeklog_type'],
        '*',
        implode(',', $fields),
        (int) $uid,
        $options
    );

    if (!is_array($result)) {
        return $resources;
    }

    $capabilities = AGENT_getProviderCapabilities($provider);
    foreach ($result as $item) {
        $raw = AGENT_mapItemInfoResult($fields, $item);
        if (empty($raw) || empty($raw['id']) || empty($raw['url'])) {
            continue;
        }

        $raw['capabilities'] = $capabilities;
        $resource = AGENT_normalizeResource(
            $provider,
            $definition['resource_type'],
            $raw
        );
        if ($resource !== false) {
            $resources[] = $resource;
        }
    }

    if (isset($options['order']) && $options['order'] === 'modified-desc') {
        AGENT_sortResourcesModifiedDesc($resources);
    }

    if (count($resources) > $limit) {
        $resources = array_slice($resources, 0, $limit);
    }

    /*
     * Hydrate only the selected Static Pages. A single-item Item Info request
     * does not trigger the Geeklog 2.1.1 collection accumulator bug and keeps
     * ACL/content ownership inside the Static Pages plugin.
     */
    if ($provider === 'staticpages') {
        foreach ($resources as $index => $resource) {
            $detail = AGENT_getProviderResource($provider, $resource['id'], $uid);
            if (is_array($detail)) {
                $resources[$index] = array_merge($resource, $detail);
            }
        }
    }

    return $resources;
}

/**
 * Expose provider state for diagnostics and later discovery output.
 */
function AGENT_getProviderStatus()
{
    global $_PLUGINS;

    $enabled = AGENT_getEnabledProviders();
    $status = array();
    foreach (AGENT_getProviderCatalog() as $provider => $definition) {
        $installed = $provider === 'stories' ||
            (is_array($_PLUGINS) && in_array($provider, $_PLUGINS, true));

        $status[$provider] = array(
            'configured' => in_array($provider, $enabled, true),
            'installed' => $installed,
            'available' => AGENT_providerAvailable($provider),
            'resource_type' => $definition['resource_type'],
            'capabilities' => AGENT_getProviderCapabilities($provider)
        );
    }

    return $status;
}
