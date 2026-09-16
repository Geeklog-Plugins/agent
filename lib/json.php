<?php

/**
 * Provider-neutral JSON representations for Agent resources and collections.
 *
 * @package Agent
 */

/**
 * Prepare one normalized resource for public JSON output.
 *
 * The provider model remains authoritative. The JSON adapter only removes
 * empty implementation details and converts editorial HTML to the same clean
 * semantic text representation used by machine-readable resources.
 */
function AGENT_jsonResourceData($resource, $includeContent = true)
{
    if (!is_array($resource)) {
        return array();
    }

    $keys = array(
        'schema_version', 'id', 'type', 'subtype', 'provider', 'title',
        'canonical_url', 'excerpt', 'language', 'created', 'modified', 'uid',
        'author', 'image', 'category', 'topic', 'hits', 'visibility',
        'capabilities'
    );

    $data = array();
    foreach ($keys as $key) {
        if (!array_key_exists($key, $resource)) {
            continue;
        }

        $value = $resource[$key];
        if ($value === '' || $value === null || $value === array()) {
            continue;
        }
        $data[$key] = $value;
    }

    if (isset($data['excerpt']) && function_exists('AGENT_discoveryText')) {
        $data['excerpt'] = AGENT_discoveryText($data['excerpt']);
    }

    if ($includeContent && !empty($resource['content'])) {
        $content = function_exists('AGENT_markdownContent')
            ? AGENT_markdownContent($resource['content'])
            : (string) $resource['content'];
        if ($content !== '') {
            $data['content'] = $content;
            $data['content_format'] = 'markdown';
        }
    }

    return $data;
}

function AGENT_jsonEncode($data)
{
    $options = 0;
    if (defined('JSON_PRETTY_PRINT')) {
        $options |= JSON_PRETTY_PRINT;
    }
    if (defined('JSON_UNESCAPED_SLASHES')) {
        $options |= JSON_UNESCAPED_SLASHES;
    }
    if (defined('JSON_UNESCAPED_UNICODE')) {
        $options |= JSON_UNESCAPED_UNICODE;
    }

    $json = json_encode($data, $options);
    if ($json === false) {
        return '';
    }

    return $json . "\n";
}

function AGENT_buildResourceJson($provider, $id)
{
    if (!AGENT_isEnabled()) {
        return '';
    }

    $resource = AGENT_getProviderResource($provider, $id, 0);
    if (!is_array($resource) || empty($resource['id']) || empty($resource['canonical_url'])) {
        return '';
    }

    return AGENT_jsonEncode(AGENT_jsonResourceData($resource, true));
}

function AGENT_buildCollectionJson($provider, $options = array())
{
    if (!AGENT_isEnabled() || !AGENT_providerAvailable($provider)) {
        return '';
    }

    if (!is_array($options)) {
        $options = array();
    }

    $limit = isset($options['limit']) ? (int) $options['limit'] : 10;
    $limit = max(1, min(100, $limit));

    $order = isset($options['order']) ? trim((string) $options['order']) : 'modified-desc';
    if ($order !== 'modified-desc') {
        $order = 'modified-desc';
    }

    $resources = AGENT_getProviderResources(
        $provider,
        array(
            'limit' => $limit,
            'order' => $order
        ),
        0
    );

    $items = array();
    foreach ($resources as $resource) {
        $item = AGENT_jsonResourceData($resource, false);
        if (!empty($item)) {
            $items[] = $item;
        }
    }

    return AGENT_jsonEncode(array(
        'schema_version' => '1',
        'provider' => $provider,
        'order' => $order,
        'limit' => $limit,
        'count' => count($items),
        'resources' => $items
    ));
}
