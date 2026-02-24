=== MBT Avatar Uploader ===
Contributors: mindboxtechnologies
Tags: avatar, profile picture, user profile, uploader, customization
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enable users to upload and customize their avatars directly from your WordPress website. Replaces the default Gravatar dependency with a user-friendly avatar uploader.

== Description ==

MBT Avatar Uploader lets logged-in users upload, crop, and manage their own profile pictures entirely from the WordPress frontend — no Gravatar account required.

**Features:**
* Upload avatars from any frontend page or post via a shortcode.
* In-browser crop & rotate with the Croppie library.
* Supports JPG, JPEG, and PNG file formats (configurable).
* Circle or square avatar display style (configurable).
* Set maximum file size and output dimensions from the admin.
* Two easy shortcodes — one for the upload form, one for displaying the avatar.
* Stored as a standard WordPress Media Library attachment; works with `get_avatar()`.
* Fully translatable (`.pot` file included).

== Installation ==

1. Download the plugin zip file.
2. In your WordPress admin go to **Plugins → Add New → Upload Plugin**.
3. Choose the zip file and click **Install Now**, then **Activate**.
4. Navigate to **Users → Avatar Uploader** to configure settings.

== Usage ==

**Shortcodes**

* `[MBT_Avatar_Field]` – Renders the avatar upload/crop form for the currently logged-in user.
* `[MBT_Avatar]` – Displays the current user's avatar.
* `[MBT_Avatar user_id="5"]` – Displays the avatar for any specific user by ID.

**Settings** — `Users → Avatar Uploader`

| Setting | Description |
|---------|-------------|
| Max Avatar Size Allowed | Maximum upload size in KB (1–10 240). |
| Allowed Avatar Type | Tick which file types (JPG / JPEG / PNG) are accepted. |
| Avatar Style | Circle or square display shape. |
| Avatar Dimension | Width × height of the cropped output in pixels (50–800). |

== Screenshots ==

1. Avatar upload field with crop and rotate controls.
2. Plugin settings page under Users → Avatar Uploader.
3. Displayed avatar with circle styling.

== Frequently Asked Questions ==

= What file types are supported? =
By default JPG, JPEG, and PNG. You can restrict the allowed types from **Users → Avatar Uploader → Allowed Avatar Type**.

= How do I show the upload form on a page? =
Add the shortcode `[MBT_Avatar_Field]` to any page or post. The form is only visible to logged-in users; guests see a "please log in" message.

= How do I display a user's avatar? =
Use `[MBT_Avatar]` for the current user, or `[MBT_Avatar user_id="42"]` for a specific user.

= Where are avatars stored? =
Avatars are saved in `wp-content/uploads/mbt_user_avatars/` and registered as WordPress Media Library attachments. They work with the standard WordPress `get_avatar()` function.

= Does this replace Gravatar completely? =
Yes. Once a user uploads an avatar it takes priority over Gravatar everywhere in WordPress that uses `get_avatar()` or `get_avatar_url()`.

= What happens to uploaded files when the plugin is uninstalled? =
Deleting the plugin removes all plugin options, user meta, media attachments, and the `mbt_user_avatars` upload folder.

= Is multisite supported? =
There are no fatal errors on multisite. Each sub-site stores its own settings and each user's avatar is associated with their user ID as normal.

= Is it compatible with caching plugins? =
Yes. Scripts and styles are enqueued only on pages that contain a plugin shortcode, which minimises conflicts. If you use a full-page cache, ensure the page with `[MBT_Avatar_Field]` is either excluded from caching or uses a fragment-caching strategy so the login state is respected.

== Changelog ==

= 1.1.0 =
* Security: removed `wp_ajax_nopriv_*` hooks — avatar save/remove now requires authentication.
* Security: added nonce verification (`check_ajax_referer`) to all AJAX actions.
* Security: added server-side image validation (`getimagesize`) — arbitrary binary data can no longer be uploaded.
* Security: enforced server-side maximum file-size check (was client-side only before).
* Security: `.htaccess` protection added to the avatar upload folder on first use.
* Security: fixed PHP role check in admin (`$user->roles[0]` caused PHP 8 undefined-index notice); replaced with `current_user_can('manage_options')`.
* Security: added sanitization callback to `register_setting` — all options are sanitised/validated before being saved.
* Security: all HTML outputs now properly escaped with `esc_url()`, `esc_attr()`, `esc_html()`.
* Fix: external CDN jQuery replaced with WordPress bundled jQuery.
* Fix: scripts and styles now enqueued **only** on pages containing a plugin shortcode.
* Fix: admin CSS now loaded only on the plugin's own settings page.
* Fix: correct script dependency chain (`script.js` depends on `croppie.js` which depends on `jquery`).
* Fix: version numbers added to all enqueued handles for cache-busting.
* Fix: nonce (`mbt_au_nonce`) passed to JavaScript via `wp_localize_script` and sent with every AJAX call.
* Fix: undefined-index PHP notice for `file_dimension` when the option was not yet saved.
* Fix: `avatar_field()` and `avatar_photo()` function names prefixed with `mbt_au_` to avoid collisions.
* Fix: `[MBT_Avatar]` shortcode now accepts a `user_id` attribute to display any user's avatar.
* Fix: `get_avatar_url` filter now resolves email addresses and WP_User / WP_Post objects, not only numeric IDs.
* Fix: `mkdir()` with `0777` replaced with `wp_mkdir_p()`.
* Fix: removed unused `global $wpdb` from AJAX handlers.
* Fix: removed `console.log` from production JavaScript.
* Fix: Upload button changed from `type="submit"` to `type="button"` to prevent accidental page reloads.
* Fix: JavaScript wrapped in IIFE `(function($){...}(jQuery))` for proper no-conflict mode.
* Added: `uninstall.php` — cleans up options, user meta, attachments and the upload folder on plugin deletion.
* Added: `ABSPATH` guard (direct-access block) to all PHP files.
* added: `MBT_AU_VERSION` constant used for asset versioning.
* Added: `mbt_au_sanitize_settings()` — server-side whitelisting for all option values.
* Translation: wrapped previously un-translated strings in `esc_html__()`.

= 1.0.0 =
* Initial release (07-07-2023).

== Upgrade Notice ==

= 1.1.0 =
Important security update. Unauthenticated avatar upload/removal is now blocked. All AJAX calls are nonce-protected. Please update immediately.

= 1.0.0 =
Initial release. Please report any issues to info@mindboxtechnologies.com.
