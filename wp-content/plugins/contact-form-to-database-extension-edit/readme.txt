=== Contact Form to Database Extension Editing ===
Contributors: msimpson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=NEVDJ792HKGFN&lc=US&item_name=Wordpress%20Plugin&item_number=cf7%2dto%2ddb%2dextension&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: contact form,database
License: EULA
License URI: http://cfdbplugin.com/files/cfdb-edit/license.txt
Requires at least: 3.1
Tested up to: 4.7
Stable tag: 1.5.1

Adds editing capability to Contact Form DB plugin.

== Description ==

Adds editing capability to Contact Form DB plugin.

== Installation ==

1. Your WordPress site must be running PHP5 or better. This plugin will fail to activate if your site is running PHP4.
1. Ensure that you have the CFDB plugin installed.
1. If you have a previous version of this plugin installed in WordPress, delete it
1. Install as a new plugin in Wordpress. Upload the plugin .zip file.
1. Activate the plugin


== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.5 =
* Update: CSV Import parsing

= 1.5 =
* Improvement: CSV Import - can now set delimiter, enclosure and escape characters
* Improvement: CSV Import - better de-duplication of submit_times read in CSV

= 1.4.4 =
* Bug Fix: Occasional issues on Import page when form names from extended character sets

= 1.4.3 =
* Bug Fix: where editing a cell that has a new line could result in adding &lt;br&gt; in certain cases
* Improvement: Can now limit which columns have editable cells by adding "editcolumns" in shortcode:
[cfdb-datatable form="your-form" edit="cells" editcolumns="column1,column2,column3"]

= 1.4.2 =
* Bug Fix: Edit could incorrectly report an error due to a MySQL issue
* Bug Fix: Where WP installations that output debug would add text to ajax return value
* Bug Fix: When WPML plugin is also installed, ajax URLs were incorrect

= 1.4.1 =
* Performance improvement

= 1.4 =
* Added Data cleanup function on Import page
* Better handling of special characters
* Fixes to import

= 1.2.2 =
* Bug fix: where import did not work properly on some customers' sites

= 1.2.1 =
* Bug fix: where editing column names was not working with datatables 1.9.4

= 1.2 =
* Support for editable datatable via short code [cfdb-datatable form="form-name" edit="true"]

= 1.1 =
* Handling bug where duplicate rows in DB could cause edits to display at blanks

= 1.0 =
* Initial version


== Upgrade Notice ==

