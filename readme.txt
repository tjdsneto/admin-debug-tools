=== Admin Debug Tools ===
Contributors: tjdsneto
Tags: debug, debugging, log, error, notice
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Admin Debug Tools makes it easy to manage your site's logs and debug settings directly from the dashboard, without needing to edit backend files.

== Description ==

=== Debug Log ===

The Debug Log file is one of the most powerful tools for WordPress site admins and developers to troubleshoot issues on their site. But, accessing the debug log file can be a hassle, requiring backend file edits and SSH or FTP access.

Admin Debug Tools simplifies the process by giving you the power to manage your debug log from the WP Admin area.

With Admin Debug Tools, you can:

- View a formatted version of the debug log file;
- Monitor the latest log entries in near real-time _(improvements to come)_;
- Filter log entries by type (`Error`, `Warning`, `Notices`, `Deprecations`);
- Search log entries by keyword with regex operators;
- Save and/or clear your debug log file;
- Download your debug log file;
- Toggle debug mode on and off with a single click;
- Edit the WP_DEBUG, WP_DEBUG_LOG, and other debug related constants;

=== WP Debugging ===

Admin Debug Tools makes direct edits to your `wp-config.php` file to enable and disable debug mode through the `WP_DEBUG` constant.

You have full control over the WP debug constants through the Config screen.

When you enable debug mode, WordPress will log errors, notices, and warnings to the debug log file. This can be helpful for troubleshooting issues on your site.

When you disable debug mode, WordPress will stop logging errors, notices, and warnings to the debug log file. This can be helpful for reducing server load and improving site performance.

https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/

=== Future Features ===

Here are some of the features I have on my radar and plan to implement in future versions of Admin Debug Tools:

- Debug log file rotation;
- Option to ignore certain errors and reduce the noise in the log file;
- Custom and usefull error pages for debugging;
- Screen to debug database queries;
- Debug helper functions to use in your code and print debug information to the log file;
- Troubleshoot session mode to disable plugins and themes, and separate debugging from the main site;


== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/admin-debug-tools` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Tools->Debug Log screen to enable WP Debug logging and view the log file.

== Frequently Asked Questions ==

= Should I keep `WP_DEBUG` mode enabled on a live site? =

It is generally not recommended to keep debug mode enabled on a live site, as it can expose sensitive information about your site and server, posing a security risk.

The main reason is that the default `WP_DEBUG_LOG` constant saves the log file in the wp-content directory, which is publicly accessible. This can expose sensitive information about your site and server, such as file paths, database credentials, and other details that could be used by malicious actors.

To secure the debug log file, you can:

- Change the location of the log file to a non-public directory _(you can do this on Admin Debug Tools's Config screen)_;
- Restrict access to the log file using server configuration (e.g., .htaccess, nginx.conf) - _You will need assistance from your hosting to do this._;

= What if my debug.log file is HUGE? =

Debug log files can grow very large over time, especially on high-traffic sites or sites with many errors and warnings. Admin Debug Tools was built from the beggining having that in mind and, by default, will not load the entire log file at once, but only the last 1000 lines.

The code is also optimized to avoid memory issues when loading large log files, but if you're experiencing performance issues, you can:

- Download the log file and use a local tool to analyze it;
- Clear the log file to start fresh;

= What users can access Admin Debug Tools? =

By default, only users with the `manage_options` capability can access the Debug Log screen. This includes Administrators and Super Admins on multisite networks.

But you can change this by using the `wp_debug_assistant_capability` filter. Here's an example of how you can change the capability to `edit_posts`:

```php
add_filter( 'admin_debug_tools_access_capability', function() {
	return 'edit_posts';
} );
```

_I shall make this easier to customize in the future._

== Changelog ==

= 1.0.0 =
* Initial release of Admin Debug Tools. Enjoy the ability to manage your debug log and settings from the WP Admin area!

== Screenshots ==

1. Debug Log screen
2. Config screen

== Upgrade Notice ==

= 1.0.0 =
Initial release of Admin Debug Tools. Enjoy the ability to manage your debug log and settings from the WP Admin area!




