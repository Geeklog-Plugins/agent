# Agent for Geeklog

Agent is the provider-neutral machine access layer for Geeklog.

It exposes Geeklog content and capabilities to machine consumers without making Agent a second content database and without coupling Geeklog plugins to ChatGPT, Claude, Gemini, MCP or another provider/protocol.

## Architecture

```text
Plugin owner = data and business logic
Hub          = context / relationships
Agent        = machine access layer
Connector    = client / provider adapter
```

## 0.1.0 foundation

The `develop-1.0` branch now contains the installable 0.1.0 foundation:

- Geeklog autoinstall metadata;
- `agent.admin` permission and `Agent Admin` group;
- Configuration Manager integration;
- seven configuration groups: General, Discovery / llms.txt, Providers, Resources, Capabilities, Cache and Security;
- runtime feature detection for Geeklog APIs;
- active-site-derived cache namespace/path helpers;
- minimal administration/status page;
- static `plugin.json` metadata manifest;
- PHP 5.6-compatible source policy and CI checks through PHP 8.3.

0.1.0 deliberately does **not** implement content providers, `/llms.txt`, Markdown/JSON endpoints, Hub logic, MCP, Connector code or write APIs. Those layers are introduced by later roadmap milestones after the installable foundation is stable.

## Compatibility target

- Geeklog 2.1.1 through 2.2.2
- PHP 5.6 through 8.3
- mono-site and multisite
- shared plugin files with site-scoped persisted state
- no Geeklog Core modification

## Configuration ownership

Agent stores only Agent behavior/editorial settings in Geeklog Configuration Manager. Site content such as stories, static pages, topics, URLs, hits, dates and plugin relationships remains owned by Geeklog Core or the relevant plugin.

Agent derives site identity from the already-selected Geeklog runtime context (`$_CONF`, table prefix). It does not maintain a hostname registry or inspect sibling sites.

See [ROADMAP.md](ROADMAP.md) for the staged development plan.

## Design principle

> Plugins expose shared data and capabilities. Hub interprets relationships. Agent exposes machine-readable access. Connectors adapt that access to clients.
