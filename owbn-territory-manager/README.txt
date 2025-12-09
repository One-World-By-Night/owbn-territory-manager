=== OWBN Territory Manager ===
Contributors: greghacke
Tags: owbn, territory, vampire, larp, chronicle, coordinator
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage OWBN chronicle and coordinator territory assignments with admin tools, public displays, and REST API access.

== Description ==

OWBN Territory Manager provides a unified system for managing territory data across One World by Night chronicles and coordinators.

**Features:**

* Associate territories with chronicle or coordinator slugs
* Admin interface for territory CRUD operations
* Public-facing territory listings with filtering
* Detailed territory views with approval parameters
* REST API for external integrations
* Bulk import/export utilities
* Select2-powered dropdowns for easy data entry

**Territory Data Fields:**

* Country
* Region
* Location
* Detail
* Description (includes approval parameters, notes, joint approvals)
* Owner (chronicle or coordinator slug)

**Shortcodes:**

* `[owbn-territory-list]` - Display filterable territory listing
* `[owbn-territory-detail]` - Display single territory details

== Installation ==

1. Upload `owbn-territory-manager` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Configure settings under OWBN > Territories
4. Import territory data or add entries manually

== REST API ==

**Endpoints:**

* `GET /wp-json/owbn/v1/territories` - All territories (short format)
* `GET /wp-json/owbn/v1/territories/{id}` - Single territory (full detail)
* `GET /wp-json/owbn/v1/slugs/{slug}/territories` - Territories by slug

**Short Response:** country, region, location, slug

**Detail Response:** All fields including description and approval parameters

== Frequently Asked Questions ==

= What is a slug? =

A slug is a URL-safe identifier for a chronicle or coordinator (e.g., `tremere`, `assamite`, `green-bay-wi`).

= Can I import existing territory data? =

Yes. Use the bulk import tool under OWBN > Territories > Import. Accepts CSV format.

= Is authentication required for the API? =

Public read access is available. Write operations require authentication.

== Changelog ==

= 1.1.0 =
* Initial release
* Territory CRUD admin interface
* REST API endpoints
* Shortcodes for list and detail views
* Bulk import/export utilities
* Select2 integration

== Upgrade Notice ==

= 1.1.0 =
Initial release.