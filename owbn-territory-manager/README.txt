=== OWBN Territory Manager ===
Contributors: greghacke
Tags: owbn, territory, vampire, larp, chronicle, coordinator
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPL-2.0-or-later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage chronicle and coordinator territory assignments with admin tools and bulk import.

== Description ==

Territory management for One World by Night. Each record tracks country, region, location, owner, and associated chronicle/coordinator slugs. Includes bulk CSV/JSON import and Select2-powered admin UI.

== Installation ==

1. Upload `owbn-territory-manager` folder to `/wp-content/plugins/`
2. Activate via Plugins menu
3. Configure under OWBN Territory > Settings
4. Import data via OWBN Territory > Import or add entries manually

== Changelog ==

= 1.5.0 =
* Removed dead webhooks/ directory (REST API removed in 1.3.0)
* Stripped comment bloat and redundant PHPDoc
* Updated stale documentation

= 1.4.0 =
* Settings page reworked for import defaults
* Integrated with OWBN Client API Gateway

= 1.3.0 =
* Removed custom REST API endpoints (served via OWBN Client gateway now)
* Added chronicle/coordinator data bridge via OWBN Client

= 1.2.0 =
* Removed unused stub files
* Scoped Select2 loading to territory admin pages

= 1.1.0 =
* Initial release
