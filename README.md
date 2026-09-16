# Agent for Geeklog

Agent is the provider-neutral machine access layer for Geeklog.

It is designed to expose Geeklog content and capabilities safely to AI assistants, LLMs, agents, MCP clients, automation tools and future machine consumers without coupling content plugins to a specific provider or protocol.

## Role

```text
Hub       = context / relationships
Agent     = machine access layer
Connector = client/provider adapter
```

Agent does not own content and does not replace Hub.

Content remains owned by Geeklog Core or the plugin that created it. Hub remains responsible for relationships, pillar context, dependency graphs and integrity diagnostics. Agent exposes normalized machine-readable resources and capabilities. External Connectors translate those resources into provider-specific formats such as ChatGPT tools.

## Initial target

- Geeklog 2.1.1 through 2.2.2
- PHP 5.6 through 8.3
- mono-site and multisite
- no Core modification required for the initial roadmap
- read-only/public machine access first

Initial goals include:

- `/llms.txt` generation;
- normalized content providers;
- Markdown resources;
- JSON resources;
- recent/popular/featured collections;
- capability discovery;
- permission-aware output;
- Hub integration through shared services;
- multisite-safe configuration and cache isolation.

Authenticated write actions, MCP and provider-specific Connectors are later layers built on the same resource/capability model.

See [ROADMAP.md](ROADMAP.md) for the development plan.

## Design principle

> Plugins expose shared data and capabilities. Hub interprets relationships. Agent exposes machine-readable access. Connectors adapt that access to clients.
