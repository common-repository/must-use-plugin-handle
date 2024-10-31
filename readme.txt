=== Plugin Name ===
Contributors: aohipa
Donate link: http://www.aohipa.com/
Tags: mu-plugins, plugin manager, plugin order, plugin handler, 
Requires at least: 3.5
Tested up to: 4.7.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Must Use Plugin Handle allows the admin to set plugins as required, specify their load order and to block plugins for updates by other users.

== Description ==

**Warning! This plugin does not support WordPress Multisite!**

[Must Use Plugin Handle](http://www.aohipa.com/must-use-plugin-handle/) is a management plugin for your plugins. It allows the admin to set plugins as required, specify their load order and to block updates of plugins that are selected as required for unauthorised users.

= Features =
* Add administrators as power users
* mark plugins as 'must use plugins'
* 'must use plugins' can only be updated, deactivated or deleted by authorised users
* define the plugin load order by drag and drop
* multilanguage (english, german) - more to come in the future

= Requirements =
* WordPress 3.5+
* PHP 5.5+

= Got a bug or suggestion? =
* [Support forum](http://wordpress.org/support/plugin/must-use-plugin-handle/)
* [Documentation](http://www.aohipa.com/must-use-plugin-handle/)
* [Contact author](http://www.aohipa.com/kontakt/) (please *DO NOT* use this form for support requests)

= Bundled translations =
* En, De [aohipa](http://www.aohipa.com/)

Have a translation? [Contact us](http://www.aohipa.com/kontakt/)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/must-use-plugin-handle` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. After activating the plugin it will be copied to the `/wp-content/mu-plugins/must-use-plugin-handle` directory. You can delete the plugin now through the 'Plugins' screen in WordPress
4. Use the 'Must use plugins' screen to configure the plugin

== Screenshots ==
1. Management page where you can manage authorized administrators and the plugins

== Frequently Asked Questions ==

= I want to add another user as an authorised user. How can I do that? =

You can add additional administrators to the authorised admins, but beware! Every authorised user can control all must use plugins and authorised administrators. Be sure, that you can trust them with this amount of power and responsability.

= One of the plugins depends on another plugin. Both are installed and set as must use, but they don't work anymore. What went wrong? =

Most likely they are being loaded in the wrong order. You can specify the load order with drag and drop through the 'Must use plugins' screen

= How can I deinstall the plugin =

You have to delete it manually through the filesystem for now. An option to delete it through the backend will be added in the future.

Your Question has not been answered? [Visit the support forum](http://wordpress.org/support/plugin/must-use-plugins-handle/)

== Upgrade Notice ==

For now you would have to delete the current plugin in the mu-plugin directory and install and activate it again, because it copies itself to the mu-handle directory. Please save the json file (must-use-plugins-handle.json) to keep your settings for the required plugins and their load order. After activation you can copy the file back to the plugin folder (`/wp-content/mu-plugins/must-use-plugin-handle`) to restore your settings.

An option to update it in a simpler fashion will be added in the future.

== Changelog ==

= 1.2 - 2017.10.17 =
* fixed an error with the plugin name detection. Switched from regular expression to get_plugin_data()

= 1.1 - 2017.04.03 =
* fixed an error, added the must-use-plugin-handle directory inside the plugins directory to the list of selectable plugins

= 1.0 - 2016.10.26 =
* Initial release