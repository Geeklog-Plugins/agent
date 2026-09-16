<?php

/**
 * Agent provider access built on Geeklog interoperability contracts.
 *
 * Providers remain authoritative for permissions and URL construction. Agent
 * consumes PLG_getItemInfo() and normalizes the result; compatibility-only
 * metadata/content fallbacks are isolated outside this provider layer.
 *
 * @package Agent
 */

function AGENT_getProviderCatalog()
{
    return array(
        'stories' => array(
            'geeklog_type' => 'story',
            'resource_type' => 'story',
            'label'         => 'Articles'
        ),
        'staticpages' => array(
            'geeklog_type' => 'staticpages',
            'resource_type' => 'staticpage',
            'label'         => 'Static Pages'
        )
    );
}

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

function AGENT_applyEditorialExcerpt($provider, $id, &$raw)
{
    if (!is_array($raw) || !function_exists('AGENT_compatMetaDescription')) {
        return;
    }

    $metaDescription = AGENT_compatMetaDescription($provider, $id);
    if ($metaDescription !== '') {
        $raw['excerpt'] = $metaDescription;
    }
}

/**
 * Prefer raw stored editorial HTML after Item Info has authorized the item.
 */
function AGENT_applyEditorialContent($provider, $id, &$raw)
{
    if (!is_array($raw)) {
        return;
    }

    if (function_exists('AGENT_compatRawContent')) {
        $rawContent = AGENT_compatRawContent($provider, $id);
        if ($rawContent !== '') {
            $raw['content'] = $rawContent;
            return;
        }
    }

    if (!empty($raw['description'])) {
        $raw['content'] = $raw['description'];
    }
}

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

    // Item Info is the permission gate. Compatibility enrichment happens only
    // after this successful permission-aware lookup.
    AGENT_applyEditorialContent($provider, $id, $raw);
    AGENT_applyEditorialExcerpt($provider, $id, $raw);
    $raw['capabilities'] = AGENT_getProviderCapabilities($provider);

    return AGENT_normalizeResource(
        $provider,
        $definition['resource_type'],
        $raw,
        array('id' => (string) $id)
    );
}

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

    foreach ($resources as $index => $resource) {
        if ($provider === 'staticpages') {
            $detail = AGENT_getProviderResource($provider, $resource['id'], $uid);
            if (is_array($detail)) {
                $resources[$index] = array_merge($resource, $detail);
            }
            continue;
        }

        if ($provider === 'stories' && function_exists('AGENT_compatMetaDescription')) {
            $metaDescription = AGENT_compatMetaDescription($provider, $resource['id']);
            if ($metaDescription !== '') {
                $resources[$index]['excerpt'] = $metaDescription;
            }
        }
    }

    return $resources;
}

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
