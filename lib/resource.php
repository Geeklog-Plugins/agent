<?php

/**
 * Provider-neutral Agent resource model.
 *
 * This file deliberately contains no Geeklog/plugin SQL and no protocol
 * formatting. Providers supply data; adapters such as Markdown, JSON or MCP
 * consume the normalized resource returned here.
 *
 * @package Agent
 */

if (!defined('AGENT_RESOURCE_SCHEMA_VERSION')) {
    define('AGENT_RESOURCE_SCHEMA_VERSION', '1');
}

function AGENT_normalizeResource($provider, $type, $raw, $defaults = array())
{
    if (!is_array($raw)) {
        return false;
    }

    $provider = trim((string) $provider);
    $type = trim((string) $type);
    if ($provider === '' || $type === '') {
        return false;
    }

    $aliases = array(
        'excerpt'  => array('excerpt', 'description'),
        'content'  => array('content', 'description'),
        'created'  => array('created', 'date-created'),
        'modified' => array('modified', 'date-modified')
    );

    foreach ($aliases as $target => $sources) {
        if (!array_key_exists($target, $raw)) {
            foreach ($sources as $source) {
                if (array_key_exists($source, $raw)) {
                    $raw[$target] = $raw[$source];
                    break;
                }
            }
        }
    }

    $resource = array(
        'schema_version' => AGENT_RESOURCE_SCHEMA_VERSION,
        'provider'       => $provider,
        'id'             => null,
        'type'           => $type,
        'subtype'        => null,
        'title'          => null,
        'url'            => null,
        'canonical_url'  => null,
        'excerpt'        => null,
        'content'        => null,
        'language'       => null,
        'created'        => null,
        'modified'       => null,
        'uid'            => null,
        'author'         => null,
        'image'          => null,
        'category'       => null,
        'topic'          => null,
        'hits'           => null,
        'visibility'     => 'accessible',
        'capabilities'   => array()
    );

    /*
     * schema_version, provider and type are Agent-owned normalized identity
     * fields. Provider callbacks may return empty/different values for fields
     * named "type"; those must never override the normalized resource type.
     */
    $identityFields = array('schema_version', 'provider', 'type');

    foreach ($resource as $field => $value) {
        if (in_array($field, $identityFields, true)) {
            continue;
        }
        if (array_key_exists($field, $defaults)) {
            $resource[$field] = $defaults[$field];
        }
        if (array_key_exists($field, $raw)) {
            $resource[$field] = $raw[$field];
        }
    }

    if (empty($resource['canonical_url']) && !empty($resource['url'])) {
        $resource['canonical_url'] = $resource['url'];
    }

    if (!is_array($resource['capabilities'])) {
        $resource['capabilities'] = array();
    }

    $capabilities = array();
    foreach ($resource['capabilities'] as $capability) {
        $capability = trim((string) $capability);
        if ($capability !== '' && !in_array($capability, $capabilities, true)) {
            $capabilities[] = $capability;
        }
    }
    $resource['capabilities'] = $capabilities;

    if ($resource['id'] === null || trim((string) $resource['id']) === '') {
        return false;
    }

    $resource['id'] = (string) $resource['id'];

    return $resource;
}

function AGENT_normalizeResourceCollection($provider, $type, $items, $defaults = array())
{
    $resources = array();
    if (!is_array($items)) {
        return $resources;
    }

    foreach ($items as $item) {
        $resource = AGENT_normalizeResource($provider, $type, $item, $defaults);
        if ($resource !== false) {
            $resources[] = $resource;
        }
    }

    return $resources;
}

function AGENT_getResourceIdentity($resource)
{
    if (!is_array($resource) || empty($resource['provider']) ||
        empty($resource['type']) || !isset($resource['id'])) {
        return '';
    }

    return (string) $resource['provider'] . ':' . (string) $resource['type']
        . ':' . (string) $resource['id'];
}
