# Agent for Geeklog — Development Roadmap

## Vision

Agent is the **provider-neutral machine access layer** for Geeklog.

It exposes site content and capabilities to AI assistants, LLMs, agents, MCP clients, automation tools and future machine consumers without requiring those consumers to know Geeklog tables, plugin internals or theme HTML.

Agent does **not** own content and does **not** replace Hub.

The architectural split is:

```text
Hub       = context / relationships
Agent     = machine access layer
Connector = client/provider adapter
```

Agent consumes shared Geeklog contracts and delegates business logic to the owning Core feature or plugin.

Agent is intended to **replace standalone LLM-discovery scripts autonomously**, not reproduce their implementation or preserve their historical structure. Existing `llms.php`, `llm-dynamic.php`, per-host switches and hand-maintained text files are reference material for identifying useful requirements only. They are not compatibility targets.

---

## Compatibility target

Initial stable target:

- Geeklog **2.1.1 through 2.2.2**;
- PHP **5.6 through 8.3**;
- MySQL / MariaDB supported by the target Geeklog versions;
- mono-site installations;
- multisite installations with shared plugin files and isolated site state;
- no Geeklog Core modification required for 1.0.

Implementation must use the common safe PHP 5.6–8.3 subset and feature-detect newer Geeklog APIs.

---

## Architectural rules

1. **Agent is provider-neutral.** No ChatGPT-, Claude-, Gemini- or MCP-specific assumptions in the core data model.
2. **The owning plugin remains authoritative.** Agent does not duplicate another plugin's tables, ACL, routing or business logic.
3. **Use shared Geeklog contracts first.** Prefer Plugin API, Item Info, services and capability discovery over direct SQL.
4. **Direct SQL is a compatibility fallback only.** Legacy adapters may be used for Core/old plugins when no suitable API exists, and should be isolated for later removal.
5. **Hub owns relationships.** Agent consumes Hub services for context, related items, dependency information, integrity reports and suggestions.
6. **Connector is an adapter.** ChatGPT-specific or other client-specific schemas belong outside Agent core.
7. **Public discovery is separate from authenticated actions.** `llms.txt` and public resources must not imply write authorization.
8. **Permissions are evaluated before exposure.** Draft/private/inaccessible content must not leak through lists, search, popularity rankings or machine endpoints.
9. **Multisite context is mandatory.** Configuration, cache, credentials and audit data must remain site-scoped.
10. **One normalized representation, many adapters.** Markdown, JSON, `llms.txt`, MCP and future protocols should reuse the same internal resource model.
11. **The previous standalone LLM system is not an API contract.** Agent may improve, reorganize or omit historical output when a cleaner machine-facing representation is more useful.
12. **Autonomy is a release requirement.** Agent 1.0 must not require the old `llms.php`, `llm-dynamic.php`, hostname registry or per-site LLM text files to operate.

---

# 0.1.0 — Installable foundation

Goal: create a clean Geeklog plugin skeleton that can be installed safely on the full compatibility matrix.

- Geeklog autoinstall/uninstall support;
- plugin metadata and `plugin.json` following Memorandum conventions;
- `agent.admin` permission and Agent Admin group;
- Configuration Manager integration;
- admin entry and basic status page;
- no PHP syntax newer than PHP 5.6;
- feature detection for Geeklog 2.1.1 vs 2.2.2 APIs;
- multisite-safe configuration loading;
- site-scoped cache namespace;
- initial automated compatibility checks.

Configuration groups should include at least:

```text
General
Discovery / llms.txt
Providers
Resources
Capabilities
Cache
Security (future-ready, read-only defaults)
```

---

# 0.2.0 — Normalized resource model

Goal: define the internal representation reused by every output format.

Initial normalized fields:

```text
id
type
subtype
title
url
excerpt
content
language
created
modified
uid
author
image
category
topic
hits
```

Not every provider must supply every field.

Requirements:

- stable `type + id` identity;
- canonical URL;
- explicit language where available;
- clean text/content separate from theme chrome;
- optional popularity metric normalized as `hits`;
- permission-aware results;
- support one item and collections;
- safe handling of absent/unsupported fields;
- no direct exposure of raw database rows.

Define collection options compatible with the Memorandum:

```text
since
until
limit
order
ids
author
topic
category
subtype
```

Initial ordering support:

```text
modified-desc
created-desc
hits-desc
```

---

# 0.3.0 — Provider layer

Goal: decouple Agent from storage details.

Introduce a provider interface compatible with PHP 5.6.

Conceptual responsibilities:

```text
get item
get collection
search
capabilities
canonical URL
permission filtering
```

Provider classes should be registered/detected without hard-coding hostname-specific behavior.

Two provider categories:

### Native providers

Use shared Geeklog/plugin contracts such as:

- `PLG_getItemInfo()` / `plugin_getiteminfo_*()`;
- `plugin_idtourl_*()` where available;
- `plugin_dopluginsearch_*()` where useful;
- `PLG_invokeService()` for specialized capabilities;
- generic capability descriptors when available.

### Compatibility providers

Isolated adapters for content that cannot yet satisfy the shared contract.

Initial compatibility providers:

- Stories;
- Static Pages;
- Topics/taxonomy where required for discovery.

Compatibility adapters must preserve:

- Geeklog permissions;
- topic access;
- language filtering where supported;
- draft/scheduled publication rules;
- canonical URL behavior.

Compatibility providers exist to bridge Geeklog/plugin API gaps, **not** to preserve the old standalone scripts.

---

# 0.4.0 — Autonomous public discovery

Goal: provide a better, self-contained replacement for the current standalone `llms.php` / `llm-dynamic.php` mechanism.

Agent becomes the owner of public machine-discovery generation for the active Geeklog site.

Canonical public resource:

```text
/llms.txt
```

Optional convenience alias:

```text
/llm.txt -> /llms.txt
```

Requirements:

- generated from the active Geeklog site context;
- no hard-coded hostname switch;
- no dependency on legacy LLM files or scripts;
- useful default output immediately after installation;
- optional site-specific editorial summary in Configuration Manager;
- automatic discovery of useful public resources where possible;
- include selected main resources rather than dump the whole site;
- include canonical sitemap/feed links where configured or discoverable;
- include recent/popular/featured collections when useful and supported;
- link to richer Agent Markdown/JSON resources;
- cache output per site;
- invalidate cache when relevant configuration/content changes where practical;
- remain useful when Hub is absent;
- degrade gracefully when a plugin exposes only part of the interoperability contract.

Distinguish clearly:

```text
recent   = newest/most recently modified
popular  = highest hits where supported
featured = explicitly curated important resources
```

Do not infer `featured` from modification date.

The structure and wording of legacy `ecologie.txt`, `cordiste.txt` or similar files are not requirements. Site description, curated resources and dynamic discovery should be modeled natively in Agent configuration and provider data.

---

# 0.5.0 — Markdown resources

Goal: provide clean LLM-readable content representations.

Conceptual routes:

```text
/agent/resource/{type}/{id}.md
```

or an equivalent Geeklog-safe routing scheme.

Markdown output should contain, when available:

- title;
- canonical URL;
- short metadata header;
- excerpt;
- clean body/content;
- author;
- language;
- created/modified dates;
- taxonomy;
- related-resource links where available.

Requirements:

- no theme navigation/chrome;
- no admin-only information;
- no hidden/private content leakage;
- stable machine-readable headings;
- UTF-8 output;
- cacheable public responses where appropriate.

---

# 0.6.0 — JSON resources and collections

Goal: expose the same normalized model for structured clients.

Conceptual read-only routes:

```text
/agent/resource/{type}/{id}.json
/agent/resources/{type}.json
```

Collection operations should support, where the provider supports them:

```text
limit
order
since
search/topic/category filters
```

Initial collection use cases:

- recent content;
- popular content;
- featured content;
- content by type;
- selected taxonomy collections.

JSON schemas should remain stable and provider-neutral.

---

# 0.7.0 — Capability discovery

Goal: let machine clients discover what the current site and authenticated/public context can actually provide.

Detect standard capabilities from existing Geeklog APIs first.

Examples:

```text
content.read
content.collection
content.search
content.popular
content.related
content.url.resolve
services.available
```

Discovery order:

```text
1. infer existing Plugin API capabilities
2. inspect normal Geeklog service/webservice support
3. use shared capability descriptors when inference is insufficient
4. avoid Agent-specific callbacks in content plugins
```

Conceptual endpoint:

```text
/agent/capabilities
```

For 1.0, expose read-only/public capabilities only unless authenticated access has explicitly been implemented.

Capability metadata should be compatible with future:

- JSON/OpenAPI descriptions;
- MCP tools/resources;
- ChatGPT Connector tool generation;
- administration interoperability audits.

---

# 0.8.0 — Hub integration

Goal: consume Hub context without duplicating Hub.

When Hub is installed and exposes services, Agent may surface:

```text
hub.context.read
hub.related.read
hub.affected.read
hub.integrity.read
hub.suggestions.read
```

Agent must call Hub's public/shared service surface.

Agent must not:

- access Hub relationship tables directly;
- maintain a second graph;
- reproduce orphan/dependency algorithms;
- silently create editorial relationships.

Agent remains fully usable when Hub is not installed.

---

# 0.9.0 — Search and retrieval

Goal: give machine consumers useful retrieval without requiring raw database access.

Initial search model:

- use plugin/Core search APIs where practical;
- aggregate normalized results by provider;
- enforce the current site's permissions;
- support bounded result counts;
- return stable identities and canonical URLs;
- expose snippets/excerpts rather than full content unless requested separately.

Potential capabilities:

```text
content.search
content.recent
content.popular
content.featured
```

Semantic/vector search is explicitly **not required for 1.0**. It may be added later as an optional provider-neutral layer.

---

# 0.10.0 — Multisite hardening

Goal: prove safe operation with shared plugin files and multiple Geeklog sites.

Validate:

- site-specific Configuration Manager values;
- site-specific `$_TABLES` mappings;
- active plugin set per site;
- permissions per site;
- cache key isolation;
- no host switch registry as source of truth;
- safe shared-file upgrades;
- no cross-site credential or audit leakage;
- independent enable/disable state where supported by Geeklog deployment model.

The active Geeklog site context is authoritative.

---

# 0.11.0 — Performance, cache and observability

Goal: keep public machine endpoints inexpensive enough for real-world crawling and agent use.

- per-site cache;
- bounded collection sizes;
- avoid N+1 provider queries;
- ETag/Last-Modified support where safe/practical;
- cache invalidation after content lifecycle events where available;
- basic request/error logging;
- optional rate limiting hooks;
- safe diagnostics for administrators;
- no sensitive implementation details in public errors.

---

# 0.12.0 — Compatibility and security audit

Test matrix:

```text
Geeklog 2.1.1 + PHP 5.6
Geeklog 2.1.1 + supported intermediate PHP where practical
Geeklog 2.2.2 + PHP 8.1
Geeklog 2.2.2 + PHP 8.3
```

Validate mono-site and multisite scenarios.

Security checks:

- permissions on every provider path;
- no draft/private content leakage;
- no direct arbitrary SQL endpoint;
- no arbitrary PHP/shell/filesystem execution;
- output escaping/encoding by format;
- request bounds;
- no implicit write actions;
- safe errors;
- multisite isolation.

---

# 1.0.0 — Stable autonomous read-only Agent

1.0 is reached when Agent can reliably:

- install on Geeklog 2.1.1–2.2.2;
- run on PHP 5.6–8.3;
- operate mono-site and multisite;
- generate useful `/llms.txt` output autonomously from the active site context;
- require no legacy `llms.php`, `llm-dynamic.php`, hostname switch or per-site LLM text file;
- expose clean Markdown resources;
- expose structured JSON resources;
- list recent/popular/featured content where supported;
- expose/read capabilities;
- use native provider contracts when available;
- use isolated compatibility providers only where Geeklog/plugin APIs require them;
- consume Hub services without duplicating Hub;
- enforce permissions consistently;
- cache safely per site;
- provide sensible defaults so a newly installed site is useful before extensive manual configuration;
- ship tests and installation/upgrade documentation.

No authenticated write capability is required for 1.0.

The old standalone LLM-discovery implementation may be removed once Agent is deployed because it is no longer part of Agent's runtime architecture or compatibility contract.

---

# Post-1.0 direction

## 1.1 — Broader plugin provider coverage

Target modernized plugins such as:

- Videos;
- Documents;
- Maps;
- MediaGallery;
- Forum where content exposure is useful;
- Store where public product/resource exposure is appropriate.

Prefer improvements in the owning plugin's shared interoperability contract instead of permanent Agent-specific SQL adapters.

## 1.2 — Rich Hub/context integration

- richer related-content context;
- affected-resource reporting;
- integrity diagnostics;
- machine-readable editorial suggestions;
- optional context bundles for agents.

## 1.3 — Retrieval improvements

- pagination conventions;
- richer filtering;
- optional chunked long-content representation;
- optional semantic retrieval/vector integration behind a generic contract;
- provenance and version/freshness metadata.

## 2.0 — Authenticated Agent API

Introduce only after the read-only model is proven:

- scoped credentials;
- effective capability filtering;
- audit trail;
- revocation/expiration;
- read vs write distinction;
- risk classes;
- human confirmation guidance for sensitive actions.

Progression should be:

```text
read
-> draft/create safe objects
-> update unpublished objects
-> test actions
-> explicit publish/send actions
```

No generic execution primitives such as SQL, PHP, shell or unrestricted filesystem operations.

## Future protocol adapters

Agent's internal model should be reusable by:

- MCP;
- REST/OpenAPI;
- ChatGPT Connector;
- other AI assistants;
- automation platforms;
- trusted custom applications.

Protocol support must remain an adapter over Agent resources/capabilities rather than redefine plugin contracts.

---

## Replacement principle

Agent is **not a migration wrapper around the current LLM scripts**.

The previous system may be studied to identify useful outcomes such as topic discovery, popular content, recent content and site description, but Agent should implement those needs from first principles using Geeklog context and shared interoperability contracts.

The target state is:

```text
Geeklog site context
        +
Core/plugin providers
        +
shared interoperability contracts
        ↓
      Agent
        ↓
llms.txt / Markdown / JSON / capabilities / future adapters
```

There must be no runtime dependency on:

```text
legacy llms.php
legacy llm-dynamic.php
hostname switch registry
legacy per-site LLM text files
```

Manual site-specific configuration should be limited to genuinely editorial information or explicit curation that cannot be derived reliably from Geeklog itself.

---

## Design rule

> **Plugins expose shared data and capabilities. Hub interprets relationships. Agent exposes machine-readable access. Connectors adapt that access to clients.**

Agent must remain useful without Hub, ChatGPT, MCP or any specific AI provider.
