# WP Developers Homepage #
**Contributors:** [gregross](https://profiles.wordpress.org/gregross/)  
**Donate link:** http://wordpress.org/plugins/wp-developers-homepage  
**Tags:** developers, plugin, theme, unresolved, support, requests, tickets  
**Requires at least:** 5.0  
**Tested up to:** 6.1.1  
**Stable tag:** 0.8  
**License:** GPLv2  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

The better tool for monitoring your plugins & themes, including support requests, download stats, version support, and more.

## Description ##

WP Developers Homepage provides a central place for developers of WordPress plugins and themes to see their information:

1. View and respond to all of your unresolved plugin & theme support requests.
2. View useful statistics for all of your plugins & themes.

Based on Mickey Kay's great [WP Dev Dashboard](https://wordpress.org/plugins/wp-dev-dashboard/).

### Features ###
* Displays plugin and theme support requests in a sortable table for ease of use.
* Displays all plugins and themes in a sortable, easy-to-parse table.
* Select which plugins and themes to show by username and/or slug.
* Choose whether to show all support tickets, or just unresolved ones.
* Implements caching to reduce load time for plugin and theme support ticket information.
* Includes cache-busting "refresh" option to force refresh plugin and theme support ticket data.
* Exclusion of plugins and themes.
* Additional information on tickets, including last poster and time.
* Set an age limit for the tickets displayed.
* Set the timeout before new data is loaded.
* Schedule a WP Cron job to load the data in the background.

## Installation ##

1. Install the plugin from the wordpress.org plugin directory.
2. Go to Settings->WP Developers Homepage in the WP admin menu.
3. Got to the settings in WordPress settings menu and configure your options.
4. Go to the WP Developers Homepage in the WP admin menu.

## Screenshots ##

### 1. Support tickets for plugins/themes. ###
![Support tickets for plugins/themes.](assets/screenshot-1.png)

### 2. Statistics for plugins/themes. ###
![Statistics for plugins/themes.](assets/screenshot-2.png)

### 3. Settings page. ###
![Settings page.](assets/screenshot-3.png)


## Changelog ##

### 0.8 ###
* Release Date: Jan 2, 2023
* Fixed: Compatibility with PHP 7+.
* Fixed: Parsing of wordpress.org pages.
* Fixed: ...basically just made it work again ;)
* Added: Totals/averages to the stats table.

### 0.7 ###
* Release Date: January 15, 2017
* Fixed: Update parser to handle new forum structure for resolved tickets.

### 0.6 ###
* Release Date: November 17, 2016
* Updated: Increased the number of results retrieved from wordpress.org for author plugins/themes.

### 0.5 ###
* Release Date: Never
* Added: Exclusion of plugins and themes.
* Added: Additional information on tickets, including last poster and time.
* Added: JavaScript sorting of table data, no more page reloads!
* Added: Set an age limit for the tickets displayed.
* Added: Exclude closed and sticky tickets.
* Added: Set the timeout before new data is loaded.
* Added: Schedule a WP Cron job to load the data in the background.
* Added: Display previous data if an update for a plugin/theme fails.
* Added: Display the last data load time.
* Updated: Simplified UI.
* Updated: Tickets presented in a single table instead of multiple metaboxes.
* Forked: From @mickeykay's WP Dev Dashboard V1.4.0 (https://github.com/MickeyKay/wp-dev-dashboard or https://wordpress.org/plugins/wp-dev-dashboard/).

