=== ARYO Activity Log ===
Contributors: KingYes, ariel.k, maor
Tags: access, admin, administration, activity, community, event, monitor, multisite, multi-users, log, logging, logger, login, network, stats, security, tracking, user, madeinisrael, woocommerce
Requires at least: 3.5
Tested up to: 3.6.0
Stable tag: 1.0.5
License: GPLv2 or later

Get aware of any activities that are taking place on your dashboard! Imagine it like a black-box for your WordPress site.

== Description ==

<h3>Like being in control? Check this out.</h3>

We all know that it’s relatively easy to analyze what your visitors are looking for while browsing your site. But there is really no easy way to know what registered users (say, with an Administrator account or even Editors) are doing on the dashboard of your site. How can you know if a post was deleted? or if a plugin was activated/deactivated? or if the active theme was changed?
If you have tens of users or more, you really can’t know who did it. This plugin tries to solve this issue by tracking what users do on the dashboard of your WordPress site. 

<strong>As of this moment, the plugin logs things when:</strong><br />


Users

* A user logs in.
* A user logs out.
* A login has failed due to incorrect credentials.
* A user updates their profile.
* A new user account is being registered.
* An existing user account is being deleted.

Plugins

* A plugin is being activated.
* A plugin is being deactivated.
* A plugin is being changed.

Themes

* A theme is being changed (Editor and Customizer).

Content

* A new post is being created.
* A post is being updated.
* A post changes status (draft, pending review, publish).
* A post is being deleted.

Media

* An attachment is being uploaded.
* An attachment is being edited.
* An attachment is being deleted.

Widgets

* A widget is being added to a sidebar.
* A widget is being deleted from a sidebar.

Options

* A option is being updated (can be extend by east filter).

Menu

* A menu is being updated.

Taxonomy

* An term is being created.
* An term is being edited.
* An term is being deleted.

WooCommerce

* Few options updated (will be more soon).


<strong>Translators:</strong>

* German (de_DE) - [Robert Harm](http://www.mapsmarker.com/)
* Hebrew (he_IL) - ARYO Digital

The plugin does not require any kind of setup. It works out of the box (and that’s why we love it too).

We’re planning to add a lot more features in the upcoming releases. If you think we’re missing something big time, please post your suggestions in the plugin’s forum.

<strong>Contributions:</strong><br />

Would you like to like to cotribute to Activity Log? You are more than welcome to submit your pull requests on the [GitHub repo](https://github.com/KingYes/wordpress-aryo-activity-log). Also, if you have any notes about the code, please open a ticket on ths issue tracker.


== Installation ==

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
1. Activate the plugin
1. Go to the plugin settings page (under Settings > Activity Log)
1. Go to the log viewer page (under Dashboard > Activity Log)

== Screenshots ==

1. The log viewer page
2. The settings page
2. Looks as nicely in MP6

== Frequently Asked Questions ==

= Requirements =
* __Requires PHP5__ for list management functionality.

= What is the plugin license? =

* This plugin is released under a GPL license.


== Changelog ==

= 1.0.6 =
* Added WooCommerce integration (very basic).

= 1.0.5 =
* Fix - Make sure no save double lines (menu taxonomy / post).

= 1.0.4 =
* Added Taxonomy type (created, updated, deleted).

= 1.0.3 =
* Added Multisite compatibility.
* Added Options hooks (limit list, you can extend by simple filter).
* Added Menu hooks.
* Tweak - Ensure no duplicate logs.. 

= 1.0.2 =
* Forget remove old .pot file

= 1.0.1 =
* Added translate: German (de_DE) - Thanks to [Robert Harm](http://www.mapsmarker.com/)
* Added translate: Hebrew (he_IL)
* Plugin name instead of file name on activation/deactivation
* <strong>New Hooks:</strong>
* A widget is being deleted from a sidebar
* A plugin is being changed
* Theme Customizer (Thanks to Ohad Raz)

= 1.0 =
* Blastoff!
