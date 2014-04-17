# ARYO Activity Log #
[![Build Status](https://travis-ci.org/KingYes/wordpress-aryo-activity-log.svg?branch=master)](https://travis-ci.org/KingYes/wordpress-aryo-activity-log) [![Built with Grunt](https://cdn.gruntjs.com/builtwith.png)](http://gruntjs.com/) [![Dependency Status](https://david-dm.org/KingYes/wordpress-aryo-activity-log/dev-status.svg)](https://david-dm.org/KingYes/wordpress-aryo-activity-log)
**Contributors:** KingYes, ariel.k, maor  
**Tags:** access, administration, activity, community, event, monitor, multisite, multi-users, log, logger, audit log, login, network, stats, security, tracking, madeinisrael, woocommerce  
**Requires at least:** 3.5  
**Tested up to:** 3.9  
**Stable tag:** 2.0.6  
**License:** GPLv2 or later  

Get aware of any activities that are taking place on your dashboard! Imagine it like a black-box for your WordPress site.

## Description ##

<h3>Like being in control? Check this out.</h3>

We all know that it’s relatively easy to analyze what your visitors are looking for while browsing your site. But there is really no easy way to know what registered users (say, with an Administrator account or even Editors) are doing on the dashboard of your site. How can you know if a post was deleted? or if a plugin was activated/deactivated? or if the active theme was changed?
If you have tens of users or more, you really can’t know who did it. This plugin tries to solve this issue by tracking what users do on the dashboard of your WordPress site. 

<strong>As of this moment, the plugin logs things when:</strong><br />

* <strong>Posts</strong> - Created, Updated, Deleted.
* <strong>Pages</strong> - Created, Updated, Deleted.
* <strong>Custom Post Type</strong> - Created, Updated, Deleted.
* <strong>Tags</strong> - Created, Edited, Deleted.
* <strong>Categories</strong> - Created, Edited, Deleted.
* <strong>Taxonomies</strong> - Created, Edited, Deleted.
* <strong>Media</strong> - Uploaded, Edited, Deleted.
* <strong>Users</strong> - Login, Logout, Login has failed, Update profile, Registered and Deleted.
* <strong>Plugins</strong> - Activated, Deactivated, Changed.
* <strong>Themes</strong> - Activeted, Changed (Editor and Customizer).
* <strong>Widgets</strong> - Added to a sidebar / Deleted from a sidebar, Order widgets.
* <strong>Menus</strong> - A menu is being updated.
* <strong>Setting</strong> - General, Writing, Reading, Discussion, Media, Permalinks.
* <strong>Options</strong> - Can be extend by east filter.
* <strong>WooCommerce</strong> - Few options updated (will be more soon)
* and much more..

<h4>What people are saying</h4>
* <em>“Optimized code - The plugin itself is blazing fast and leaves almost no footprint on the server.”</em> - [freshtechtips.com](http://www.freshtechtips.com/2014/01/best-audit-trail-plugins-for-wordpress.html)
* <em>“The plugin successful for activity log for wordpress.”</em> - [wp-tricks.co.il](http://www.wp-tricks.co.il/2013/08/%D7%99%D7%95%D7%9E%D7%9F-%D7%A4%D7%A2%D7%99%D7%9C%D7%95%D7%AA-%D7%9C%D7%95%D7%95%D7%A8%D7%93%D7%A4%D7%A8%D7%A1-aryo-activity-log/)
* <em>“This is a pretty simple yet quite effective plugin for keeping track of what your admins and users do on your sites.”</em> - [shadowdragonunlimited.com](http://shadowdragonunlimited.com/plugin-of-the-week-9302013-aryo-activity-log/plugin-of-the-week/)
* Thanks

<h4>Translators:</h4>
* German (de_DE) - [Robert Harm](http://www.mapsmarker.com/)
* Serbo-Croatian (sr_RS) - [Borisa Djuraskovic](http://www.webhostinghub.com/)
* Danish (da_DK) - [Morten Dalgaard Johansen](http://www.iosoftgame.com/)
* Hebrew (he_IL) + RTL Support - [ARYO Digital](http://www.aryodigital.com/)
* Armenia (hy_AM) - Hayk Jomardyan

The plugin does not require any kind of setup. It works out of the box (and that’s why we love it too).

We’re planning to add a lot more features in the upcoming releases. If you think we’re missing something big time, please post your suggestions in the plugin’s forum.

<strong>Contributions:</strong><br />

Would you like to like to cotribute to Activity Log? You are more than welcome to submit your pull requests on the [GitHub repo](https://github.com/KingYes/wordpress-aryo-activity-log). Also, if you have any notes about the code, please open a ticket on ths issue tracker.


## Installation ##

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
1. Activate the plugin
1. Go to the plugin page (under Dashboard > Activity Log)

## Screenshots ##

### 1. The log viewer page ###
![The log viewer page](http://s.wordpress.org/extend/plugins/aryo-activity-log/screenshot-1.png)

### 2. The settings page ###
![The settings page](http://s.wordpress.org/extend/plugins/aryo-activity-log/screenshot-2.png)

### 3. Screen Options ###
![Screen Options](http://s.wordpress.org/extend/plugins/aryo-activity-log/screenshot-3.png)


## Frequently Asked Questions ##

### Requirements ###
* __Requires PHP5__ for list management functionality.

### What is the plugin license? ###

* This plugin is released under a GPL license.


## Changelog ##

### 2.1.0 ###
* Tested up to WordPress v3.9.

### 2.0.6 ###
* Fixed! Random fatal error ([topic](https://github.com/KingYes/wordpress-aryo-activity-log/issues/32))

### 2.0.5 ###
* New! Register `aal_init_caps` filter.
* Tweak! Change all methods to non-static.
* Tweak! Some improved coding standards and PHPDoc.
* Tweak! Split `AAL_Hooks` class to multiple classes.
* New! Added translate: Armenia (hy_AM) - Thanks to Hayk Jomardyan.

### 2.0.4 ###
* Tweak! Don't allowed to access in direct files.
* New! Added translate: Danish (da_DK) - Thanks to [Morten Dalgaard Johansen](http://www.iosoftgame.com/)

### 2.0.3 ###
* New! Record when widgets change orders.

### 2.0.2 ###
* New! Save more Options:
* General
* Writing
* Reading
* Discussion
* Media
* Permalinks

### 2.0.1 ###
* New! filter for disable erase all the log
* Bugs fixed

### 2.0.0 ###
* Added Screen Options
* New! Ability to select a number of activity items per page
* New! Columns are now sortable
* Added filter by date - All Time, Today, Yesterday, Week, Month
* Added Avatar to author
* Added role for author
* Added log for activeted theme
* Re-order Culoumns
* Compatible up to 3.8.1
* Settings page is now accessible directly from Activity Log's menu
* Keep your log for any time your wants
* Delete Log Activities from Database.
* Bugs fixed


### 1.0.8 ###
* Added translate: Serbo-Croatian (sr_RS) - Thanks to [Borisa Djuraskovic](http://www.webhostinghub.com/).

### 1.0.7 ###
* Added 'view_all_aryo_activity_log' user capability ([topic](http://wordpress.org/support/topic/capability-to-access-the-activity-log)).

### 1.0.6 ###
* Added WooCommerce integration (very basic).
* Added Settings link in plugins page.

### 1.0.5 ###
* Fix - Make sure no save double lines (menu taxonomy / post).

### 1.0.4 ###
* Added Taxonomy type (created, updated, deleted).

### 1.0.3 ###
* Added Multisite compatibility.
* Added Options hooks (limit list, you can extend by simple filter).
* Added Menu hooks.
* Tweak - Ensure no duplicate logs.. 

### 1.0.2 ###
* Forget remove old .pot file

### 1.0.1 ###
* Added translate: German (de_DE) - Thanks to [Robert Harm](http://www.mapsmarker.com/)
* Added translate: Hebrew (he_IL)
* Plugin name instead of file name on activation/deactivation
* <strong>New Hooks:</strong>
* A widget is being deleted from a sidebar
* A plugin is being changed
* Theme Customizer (Thanks to Ohad Raz)

### 1.0 ###
* Blastoff!
