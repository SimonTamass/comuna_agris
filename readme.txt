=== Comuna Agriș Elementor Widgets ===
Contributors: comunaagris
Tags: elementor, municipality, documents, accessibility, theme builder
Requires at least: 6.4
Requires PHP: 8.0
Stable tag: 1.8.1
License: GPLv2 or later

A complete modular Elementor widget suite for the Comuna Agriș website.

== Description ==

Adds 24 purpose-built widgets under the “Comuna Agriș” Elementor category, plus a structured Document post type and document categories.

Global and Theme Builder widgets:

* Full responsive header with WordPress menus and language links
* Full footer
* Accessibility tools
* Search modal
* Blog/category archive
* Single post layout

Page-building widgets:

* Home hero and inner-page hero
* Section heading
* Services grid
* Content and image split
* Leadership profile
* Audience schedule
* Local council members
* Link/sidebar list
* Contact details and map
* Secure AJAX contact form
* Institutional CTA/banner
* Photo gallery with Elementor lightbox
* Responsive public-data table
* Statistics/progress bars
* Dynamic news grid
* Manual document cards
* Dynamic filterable document library

== Installation ==

1. Upload the ZIP in Plugins > Add New > Upload Plugin.
2. Activate Elementor, Elementor Pro and this plugin.
3. Create the main WordPress menu under Appearance > Menus.
4. Build Header, Footer, Single Post and Archive templates in Elementor Theme Builder.
5. Use the widgets from the “Comuna Agriș” category for pages.
6. Add official files under the new Documents menu.

== Changelog ==

= 1.8.1 =
* Scope the approved homepage styling through a WordPress body class so Elementor 4 containers render it reliably.
* Store white and dark homepage bands as native Elementor background settings.
* Add an editable dark style variant to the section heading widget.

= 1.8.0 =
* Restore the approved local homepage composition in Elementor: full hero content, portal updates, search and calls to action.
* Restore the seven homepage bands for services, announcements, recent council decisions, community content, Monitorul Oficial and SIPOCĂ.
* Match local homepage card sizing, section backgrounds, media proportions, spacing and responsive behavior.

= 1.7.1 =
* Restore all eight frequent-service cards from the approved local homepage, in the same order and with live WordPress destinations.

= 1.7.0 =
* Match the Romanian homepage content order to the original site: welcome links, announcements, reports, council decisions and SIPoCA banner.
* Restore legacy post-list counts and categories instead of truncating archives to 24 items.
* Convert legacy Qode and OTW buttons into working links so public documents and internal destinations remain accessible.
* Remove unrelated automatic content blocks from generic pages while preserving the shared Elementor header and footer.

= 1.6.1 =
* Keep the Romanian homepage hero tied to its original Slider Revolution cover image.
* Restore the original SIPoCA media in the homepage CTA instead of reusing a current announcement slide.

= 1.6.0 =
* Recover inline, attached, featured and shortcode-based media from the original page source.
* Restore legacy WPBakery single images and galleries at their original content position.
* Resolve Slider Revolution images for the Romanian homepage and restore the mayor's original photo.
* Preserve WordPress audio, video, playlist, embed and caption shortcodes during conversion.

= 1.5.0 =
* Match the global Elementor header and footer to the approved local design on desktop and mobile.
* Add accessible Dashicons, linked navigation controls and flag-based language selection.
* Remove the duplicated Polylang language item from the primary menu and add the complete footer utility row.

= 1.4.3 =
* Extract only the innermost content block from legacy pages that already contain rendered Agris Elementor markup.
* Prevent nested page imports from duplicating the header, hero and footer.

= 1.4.2 =
* Remove unregistered WPBakery/Qode layout shortcodes while preserving their readable inner content.
* Rebuild legacy masonry category pages as dynamic Elementor post grids.
* Build gallery pages from attached and category-post images with an Elementor lightbox.
* Add complete contact details and secure contact form widgets to the Romanian contact page.

= 1.4.1 =
* Preserve legacy post content while writing Elementor data directly to metadata.
* Avoid legacy save hooks that can reject bulk page conversions or alter language-aware permalinks.
* Report grouped error codes after a complete Romanian-site rebuild.

= 1.4.0 =
* Matched the plugin typography, spacing, radii and responsive dimensions to the local redesign.
* Added reliable Sora and Source Sans 3 loading for both Elementor pages and widgets.
* Added a complete server-side rebuild for every published Romanian page in one operation.
* Added type-aware inner-page templates while preserving original content, page IDs, slugs and URLs.
* Added reusable source-content backups so repeated rebuilds cannot erase legacy page content.

= 1.3.0 =
* Split all 24 Elementor widgets into independently maintainable component files.
* Added a dedicated Elementor integration, widget registry and asset service.
* Added a shared Style tab with colors, spacing, radius and title typography controls to every widget.
* Preserved widget IDs, frontend markup and safe URL-preserving rebuild behavior.

= 1.2.2 =
* Removed the built-in GitHub/WordPress plugin updater.
* Plugin updates are now performed exclusively through cPanel Git Version Control.
* The documented deployment directory is the stable comunaagris_plugin path.

= 1.2.1 =
* Rebuild failures now show a safe diagnostic code in the WordPress dashboard.
* Elementor JSON is validated before a page backup or content update starts.

= 1.2.0 =
* Added a safe one-click Elementor rebuild for the existing Romanian Primar page.
* Rebuild dashboard now manages multiple existing pages with separate backups and restore actions.
* Page ID, language, slug and public URL remain unchanged during every rebuild.

= 1.1.0 =
* Safe server-side Elementor rebuild with automatic backups and one-click restore.
* Existing page URLs are resolved dynamically and verified before an update is accepted.
* Romanian main-menu selection now prefers the populated Fo Roman menu.
* Document library can reuse existing WordPress posts and categories.

= 1.0.0 =
* Initial release with 24 widgets, responsive design, document library and accessible interactions.
