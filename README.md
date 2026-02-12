# OWBN Territory Manager

Manage OWBN chronicle and coordinator territory assignments with admin tools, bulk import, and REST API access.

**Version:** 1.2.0
**Requires:** WordPress 5.8+ / PHP 7.4+
**License:** GPL-2.0-or-later

## Overview

OWBN Territory Manager provides a unified system for managing territory data across One World by Night chronicles and coordinators. Each territory record tracks country, region, location, detail, owner, and associated chronicle/coordinator slugs.

## Features

- Custom post type (`owbn_territory`) with registered meta fields
- Admin metabox for territory data entry with Select2 dropdowns
- Sortable and filterable admin columns (Country, Region, Location, Owner, Slugs)
- Bulk CSV import with duplicate detection and validation
- REST API with API key authentication
- Chronicle/Coordinator slug association for cross-plugin linking
- Local-only mode toggle to disable external API access

## Territory Data Fields

| Field | Meta Key | Type | Description |
|-------|----------|------|-------------|
| Countries | `_owbn_tm_countries` | array | ISO country codes |
| Region | `_owbn_tm_region` | string | State / Province |
| Location | `_owbn_tm_location` | string | City or area name |
| Detail | `_owbn_tm_detail` | string | Additional location detail |
| Owner | `_owbn_tm_owner` | string | Display name of controlling entity |
| Slugs | `_owbn_tm_slug` | array | Chronicle/Coordinator slugs |
| Update Date | `_owbn_tm_update_date` | string | Last update timestamp |
| Update User | `_owbn_tm_update_user` | string | User who last updated |

The post content field stores the territory description, including approval parameters and notes.

## Installation

1. Upload `owbn-territory-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress Plugins menu
3. Configure settings under **OWBN Territory > Settings**
4. Import territory data via **OWBN Territory > Import** or add entries manually

## REST API

All endpoints use the `owbn-tm/v1` namespace.

### Authentication

Every request requires a valid API key via the `x-api-key` header. Configure the key under **OWBN Territory > Settings**.

### Endpoints

#### List All Territories

```
POST /wp-json/owbn-tm/v1/territories
```

Returns all published territories with full field data.

#### Get Single Territory

```
POST /wp-json/owbn-tm/v1/territory
Content-Type: application/json

{"id": 123}
```

#### Get Territories by Slug

```
POST /wp-json/owbn-tm/v1/territories-by-slug
Content-Type: application/json

{"slug": "tremere"}
```

Returns all territories associated with the given chronicle or coordinator slug.

### Response Fields

All endpoints return: `id`, `title`, `countries`, `region`, `location`, `detail`, `owner`, `slugs`, `description`, `update_date`, `update_user`.

## File Structure

```
owbn-territory-manager/
  owbn-territory-manager.php    # Plugin entry point
  README.txt                    # WordPress readme
  includes/
    init.php                    # Bootstrap loader
    admin/
      columns.php               # Admin list table columns
      init.php                  # Admin loader
      menu.php                  # Admin menu registration
      metabox.php               # Territory edit metabox
      settings.php              # Settings page
    assets/
      css/select2.min.css       # Select2 styles
      js/select2.min.js         # Select2 script
    core/
      init.php                  # Core loader
      post-type.php             # CPT and meta registration
    helper/
      countries.php             # Country code data
      init.php                  # Helper loader
    tools/
      get-cc-data.php           # Chronicle/Coordinator data fetch
      init.php                  # Tools loader
    utils/
      import-bulk.php           # Bulk CSV import
      init.php                  # Utils loader
    webhooks/
      client-api-detail.php     # Detail API endpoint
      client-api-list.php       # List API endpoint
      client-init.php           # Route registration and auth
      init.php                  # Webhooks loader
```

## Related Plugins

- **[OWBN Chronicle & Coordinator Plugin](https://github.com/One-World-By-Night/owbn-chronicle-plugin)** - Manages chronicle and coordinator entities; provides the slugs referenced in territory records
- **[OWBN Client](https://github.com/One-World-By-Night/owbn-client)** - Public-facing display plugin for chronicles, coordinators, and territories

## Changelog

### 1.2.0
- Removed unused stub files and empty asset placeholders
- Scoped Select2 asset loading to territory admin pages only
- Cleaned up plugin file structure

### 1.1.0
- Initial release
- Territory custom post type with registered meta fields
- Admin metabox with Select2 dropdowns
- Sortable/filterable admin columns
- REST API endpoints (list, detail, by-slug)
- Bulk CSV import with validation
- API key authentication and settings page
