=== Simple Custom Cursor ===
Contributors:      safatash
Tags:              cursor, custom cursor, mouse cursor, animation, design
Requires at least: 5.5
Tested up to:      6.9
Requires PHP:      7.4
Stable tag:        2.1.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Add a smooth animated custom cursor to your site. Choose style, colors, size, and behavior from the WordPress admin. No code required.

== Description ==

Simple Custom Cursor replaces the default browser cursor with a smooth, animated custom cursor on the frontend of your WordPress site. Every visual property is configurable from **Settings → Custom Cursor** in your WordPress admin — no code editing required.

**Cursor Styles**

* **Dot + Circle** — classic small dot with a smooth lagging ring
* **Dot Only** — minimal snapping dot, no ring
* **Circle Only** — just the lagging ring, no dot
* **Crosshair** — two intersecting lines that snap to the pointer
* **Filled Ring** — dot plus a semi-transparent filled ring

**Color Controls**

* Individual color pickers for the dot and the outline ring
* Paired hex text inputs that stay in sync with the color picker
* Blend mode: *Difference* (cursor always visible regardless of background) or *Normal* (solid color)

**Size & Weight**

* Dot diameter (2–20 px)
* Outline ring diameter (10–80 px)
* Ring border thickness (1–6 px)

**Behaviour**

* Hover scale — how much the ring grows when hovering over links, buttons, and inputs
* Outline lag speed — controls how smoothly the ring trails the pointer (0.05 = very smooth, 1.0 = instant snap)
* Optional enable on mobile/touch devices (off by default; native cursor is restored automatically)

**Technical**

* Zero dependencies — pure vanilla JavaScript and CSS
* Uses `requestAnimationFrame` for smooth, GPU-friendly animation
* Settings injected as CSS custom properties and passed to JS via `wp_localize_script`
* All inputs sanitised and escaped following WordPress coding standards
* Automatically disabled on touch screens and viewports ≤ 1024 px (configurable)

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`, or install via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings → Custom Cursor** to configure the cursor style, colors, and behaviour.

== Frequently Asked Questions ==

= Does this work on mobile or touch devices? =
By default the custom cursor is disabled on touch and mobile devices, and the native cursor is restored automatically. You can enable it for touch screens via the toggle in **Settings → Custom Cursor → Behaviour**.

= Can I change the cursor color? =
Yes. The **Colors** tab in the settings page provides individual color pickers and hex inputs for both the dot and the outline ring.

= Will this slow down my site? =
No. The plugin loads one small CSS file and one small vanilla JavaScript file with no external dependencies. Animation runs via `requestAnimationFrame`, which is handled natively by the browser.

= Does it work with page builders like Elementor, Divi, or Avada? =
Yes. The plugin enqueues its assets on all frontend pages and works alongside any theme or page builder.

= Can I use it on multiple WordPress sites? =
Yes. Install the plugin zip on as many WordPress sites as you like — it is a self-contained plugin with no licence restrictions.

= Does the plugin store any user data? =
No. The plugin only stores your visual settings (colors, sizes, cursor type) in the WordPress options table. It does not collect, transmit, or log any user data and makes no external HTTP requests.

== Changelog ==

= 2.1.0 =
* Redesigned admin settings page with sidebar tab navigation, live interactive preview, polished sliders, and color swatches.
* Added SVG cursor type previews in the style selector.
* Improved range sliders with gradient fill and live value badges.
* Updated plugin header with Requires at least, Requires PHP, and Tested up to fields.
* Added readme.txt for WordPress.org submission.

= 2.0.0 =
* Added admin settings page with cursor type, color, size, and behaviour controls.
* Added 5 cursor types: Dot + Circle, Dot Only, Circle Only, Crosshair, Filled Ring.
* Settings passed to JS via wp_localize_script; colors injected as CSS custom properties.

= 1.0.0 =
* Initial release.

== Credits ==

Developed by [Safa Tash](https://www.novaadvertising.com) at [NOVA Advertising](https://www.novaadvertising.com).

== Upgrade Notice ==

= 2.1.0 =
Major UI refresh of the settings page. No breaking changes to frontend behaviour or saved settings.

= 2.0.0 =
Adds a full settings page. No breaking changes from 1.0.0.
