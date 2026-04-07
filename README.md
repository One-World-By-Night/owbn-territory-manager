# OWBN Territory Manager

Manages geographic territory assignments for OWBN chronicles and coordinators.

**Version:** 1.8.1
**Deployed to:** chronicles.owbn.net

## What It Does

Every OWBN chronicle and coordinator genre has a geographic territory — the real-world regions where they operate. This plugin stores those assignments and provides admin tools for managing them.

Key features:

- **Territory records** — custom post type with country, region, location, detail fields, and linked chronicle/coordinator slugs
- **Bulk import** — CSV import with configurable defaults for fast initial population
- **Change history** — tracks who changed what and when on each territory (up to 50 entries per record)
- **Custom locations** — admin-configurable country/location entries (Virtual, Online, etc.) alongside the standard ISO list
- **Dashboard widget** — quick links and last 5 updated territories
- **Data served via owbn-gateway** — no custom REST endpoints; territory data flows through the OWBN Client gateway to other sites

## Requirements

- WordPress 5.8+, PHP 7.4+
- owbn-core + owbn-gateway for cross-site data access

## License

GPL-2.0-or-later
