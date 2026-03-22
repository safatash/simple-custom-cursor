# Simple Custom Cursor

A lightweight WordPress plugin that replaces the default browser cursor with a smooth, animated custom cursor.

## Overview

Simple Custom Cursor lets you customize how the cursor looks and behaves on your website without writing any code. All settings are managed from the WordPress admin panel.

Designed to be lightweight, fast, and compatible with modern themes and page builders.

---

## Features

- Multiple cursor styles:
  - Dot + Circle
  - Dot Only
  - Circle Only
  - Crosshair
  - Filled Ring

- Customization options:
  - Dot color and outline color
  - Cursor sizes and border thickness
  - Hover scale effects
  - Cursor lag/smoothness control
  - Blend mode (Difference or Normal)

- Performance-focused:
  - No external libraries
  - Uses requestAnimationFrame for smooth animation
  - Loads only on the frontend
  - Disabled on touch devices by default

---

## Installation

### Method 1: Upload via WordPress

1. Download or clone this repository
2. Zip the `simple-custom-cursor` folder
3. Go to **Plugins → Add New → Upload Plugin**
4. Upload and activate

### Method 2: Manual install

1. Copy the plugin folder to:
/wp-content/plugins/simple-custom-cursor/
2. Activate the plugin from the WordPress dashboard

---

## Usage

After activation:

1. Go to **Settings → Custom Cursor**
2. Choose your cursor style
3. Adjust colors, sizes, and behavior
4. Save changes

The cursor will automatically apply to the frontend of your site.

---

## Folder Structure
simple-custom-cursor/
├── simple-custom-cursor.php
├── readme.txt
├── assets/
│ ├── css/
│ └── js/
├── admin/

---

## Compatibility

- WordPress 5.5+
- PHP 7.4+
- Works with:
  - Avada
  - Elementor
  - Divi
  - Most modern themes

---

## Performance Notes

- Minimal CSS and JavaScript footprint
- No impact on backend/admin performance
- Optimized to avoid unnecessary DOM updates

---

## Roadmap

- More cursor styles
- Per-page enable/disable
- Custom cursor triggers (hover targets)
- Animation presets

---

## Contributing

Pull requests are welcome. For major changes, open an issue first to discuss what you’d like to change.

---

## License

GPL v2 or later  
https://www.gnu.org/licenses/gpl-2.0.html

---

## Author

Developed by Safa Tash  
https://www.novaadvertising.com
