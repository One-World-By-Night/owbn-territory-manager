# OWBN Territory Manager

Manage chronicle and coordinator territory assignments with admin tools and bulk import.

**Version:** 1.6.1
**Requires:** WordPress 5.8+ / PHP 7.4+
**License:** GPL-2.0-or-later

## Installation

1. Upload `owbn-territory-manager` to `/wp-content/plugins/`
2. Activate via Plugins menu
3. Configure under **OWBN Territory > Settings**
4. Import data via **OWBN Territory > Import** or add manually

## Changelog

### 1.6.1

- Added custom country/location management in Settings (add game-specific entries like Virtual, Online, Umbra)
- Custom entries appear in all country dropdowns alongside the standard ISO list
- Entries stored as a WordPress option, manageable without code changes

### 1.6.0

- Added metadata change history tracking for countries, region, location, detail, owner, and linked slugs
- History stored per territory (up to 50 entries), showing timestamp, editor, and before/after values
- Read-only Change History metabox added to territory edit sidebar

### 1.5.1

- Territory slug format changed to typed slugs: `chronicle/{slug}` and `coordinator/{slug}`

### 1.5.0

- Removed dead webhooks/ directory (REST API removed in 1.3.0)
- Stripped comment bloat and redundant PHPDoc
- Updated stale documentation

### 1.4.0

- Settings page reworked for import defaults
- Integrated with OWBN Client API Gateway

### 1.3.0

- Removed custom REST API endpoints (territory data now served via OWBN Client gateway)
- Added chronicle/coordinator data bridge via OWBN Client

### 1.2.0

- Removed unused stub files
- Scoped Select2 loading to territory admin pages

### 1.1.0

- Initial release
