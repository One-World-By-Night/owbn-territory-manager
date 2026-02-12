=== OWBN Territory Manager ===
Contributors: greghacke
Tags: owbn, territory, vampire, larp, chronicle, coordinator
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPL-2.0-or-later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage OWBN chronicle and coordinator territory assignments with admin tools, bulk import, and REST API access.

== Description ==

OWBN Territory Manager provides a unified system for managing territory data across One World by Night chronicles and coordinators. Each territory record tracks country, region, location, detail, owner, and associated chronicle/coordinator slugs.

**Features:**

* Custom post type (`owbn_territory`) with registered meta fields
* Admin metabox for territory data entry with Select2 dropdowns
* Sortable and filterable admin columns (Country, Region, Location, Owner, Slugs)
* Bulk CSV import with duplicate detection and validation
* REST API for external integrations (list, detail, by-slug)
* API key authentication and local-only mode toggle
* Chronicle/Coordinator slug association for cross-plugin linking
* Admin settings page for API configuration

**Territory Data Fields:**

* Countries (multi-select ISO codes)
* Region / State / Province
* Location
* Detail
* Owner (display name of controlling chronicle or coordinator)
* Slugs (array of chronicle/coordinator slugs)
* Description (post content - includes approval parameters, notes, joint approvals)
* Update Date and Update User (audit trail)

== Installation ==

1. Upload `owbn-territory-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Configure settings under OWBN Territory > Settings
4. Import territory data via OWBN Territory > Import or add entries manually

== REST API ==

All endpoints use the `owbn-tm/v1` namespace and require a POST request with an `x-api-key` header.

**Endpoints:**

* `POST /wp-json/owbn-tm/v1/territories` - All territories (list format)
* `POST /wp-json/owbn-tm/v1/territory` - Single territory by ID (body: `{"id": 123}`)
* `POST /wp-json/owbn-tm/v1/territories-by-slug` - Territories by slug (body: `{"slug": "tremere"}`)

**List Response Fields:** id, title, countries, region, location, detail, owner, slugs, description, update_date, update_user

**Settings:**

* Enable/disable API access
* Local-only mode (disables all API access)
* API key for authentication

== Frequently Asked Questions ==

= What is a slug? =

A slug is a URL-safe identifier for a chronicle or coordinator (e.g., `tremere`, `green-bay-wi`). Slugs link territories to their managing entity in the OWBN Chronicle & Coordinator Plugin.

= Can I import existing territory data? =

Yes. Use the bulk import tool under OWBN Territory > Import. Accepts CSV format with columns for country, region, location, detail, description, owner, and slugs.

= Is authentication required for the API? =

Yes. All API endpoints require a valid API key sent via the `x-api-key` header. The key is configured in OWBN Territory > Settings.

== Changelog ==

= 1.2.0 =
* Removed unused stub files and empty asset placeholders
* Scoped Select2 asset loading to territory admin pages only
* Cleaned up plugin file structure
* Updated tested WordPress version to 6.7

= 1.1.0 =
* Initial release
* Territory custom post type with registered meta fields
* Admin metabox with Select2 dropdowns
* Sortable/filterable admin columns
* REST API endpoints (list, detail, by-slug)
* Bulk CSV import with validation
* API key authentication
* Settings page

== Upgrade Notice ==

= 1.2.0 =
Maintenance release. Removes unused stub files and scopes asset loading to admin pages.

= 1.1.0 =
Initial release.
