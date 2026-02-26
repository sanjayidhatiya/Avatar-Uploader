# MBT Avatar Uploader

A WordPress plugin that allows users to upload and crop custom profile pictures (avatars) from the frontend of your website.

---

## Description

**MBT Avatar Uploader** makes it easy to add an avatar upload field to any frontend page, post, or the WordPress Edit Profile screen. Users can select an image, crop it with an interactive cropper, and save it as their profile picture — all without leaving the frontend.

---

## Features

- Frontend avatar upload field via shortcode
- Interactive image cropping powered by [Croppie](https://foliotek.github.io/Croppie/)
- Configurable max file size, allowed file types, avatar style (circle/square), and dimensions
- Admin settings panel under **Users → Avatar Uploader**
- Secure upload directory with `.htaccess` protection
- Shortcode to display the current user's avatar anywhere
- Translation-ready (`.pot` file included)

---

## Requirements

- WordPress 5.6 or higher
- PHP 7.4 or higher

---

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin from the **Plugins** screen in WordPress admin.
3. Navigate to **Users → Avatar Uploader** to configure settings.

---

## Shortcodes

| Shortcode | Description |
|---|---|
| `[MBT_Avatar_Field]` | Renders the avatar upload & crop form |
| `[MBT_Avatar]` | Displays the current user's avatar image |

---

## Admin Settings

Go to **Users → Avatar Uploader** to configure:

| Setting | Description |
|---|---|
| **Max Avatar Size Allowed** | Maximum upload file size in KB |
| **Allowed Avatar Type** | Permitted file types (jpg, jpeg, png) |
| **Avatar Style** | Shape of the cropped avatar (`circle` or `square`) |
| **Avatar Dimension** | Width and height of the saved avatar in pixels |

---

## Plugin Details

| Field | Value |
|---|---|
| Version | 1.1.0 |
| Author | [Mindbox Technologies](https://mindboxtechnologies.com) |
| License | GPLv2 or later |
| Text Domain | `mbt-avatar-uploader` |

---

## License

This plugin is licensed under the [GNU General Public License v2 or later](http://www.gnu.org/licenses/gpl-2.0.html).
