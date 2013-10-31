=== WordPress Store Locator ===
Contributors: viadat
Tags: business locations, admin, dealer locator, dealer locater, store locator, post, store locater, mapping, mapper, google, google maps, ajax, shop locator, shop finder, shortcode, location finder, places, widget, stores, plugin, maps, coordinates, latitude, longitude, posts, geo, geocoding, jquery, shops, page, zipcode, zip code, zip code search, store finder, address map, address location map, map maker, map creator, mapping software, map tools, mapping tools, locator maps, map of addresses, map multiple locations, wordpress locator, store locator map
Requires at least: 3.0
Tested up to: 3.6.1
Stable tag: 2.4

A WordPress-integrated map & location management interface. Quickly create store locator maps -- provides several top-level addon features

== Description ==
Power your site with this WordPress-integrated map making & location management system possessing mapping tools to create store locators, store finders, and other location address maps. Manage and display a few or thousands of your important stores, points of interest, or product locations anywhere on Earth using Google Maps. 

Its strength is in its flexibility to allow you to easily manage any number of locations from your WordPress admin interface and the several addons featuring top-level features that are available to further boost its capabilities. Also referred to as an address map, address location map, locator map, store finder, dealer locator (locater), shop finder, and zip code or zipcode search. 

= New in Version 2 =
* __Now uses Google Maps V3__
* Streamlined admin interface (more powerful yet much simplier)
* Implemented much faster code (reduced database use {insert/update/delete} in code by 81.4% & applied fastest functions throughout)
* __LotsOfLocales&trade; Dashboard:__ Pull-out interface that provides you with the latest Store Locator news, installation & usage instructions, your website's hosting server information, available shortcode parameters to create multiple maps of specific groups of locations, general settings, and the activation interface for the Addons Platform & G1 (Generation 1) addons
* __Availability of the Addons Platform:__ (more details below)
* Ability to auto-locate your website visitors in order to show locations based on where visitor is currently located
* 40+ new Google Maps country domains
* New address map icons
* __New default fields:__ Fax & Email Address

= LotsOfLocales&trade; Addons Platform =
[Addons Platform](http://docs.viadat.com/Main_Page#Addons) provides you with a growing number of __free__ Generation 2 (G2; comes with the Addons Platform) addons & themes to save you time, provide you highly-advanced features, and impress your clients. Below is a list of some of the addons available, starting in Store Locator Version 2 ([view documentation](http://docs.viadat.com/Main_Page#Addons) for more details on each):

* __Categorizer__
* __Multiple Mapper__
* __Advanced Theme Manager__
* __Location Pages__
* __CSV Importer/Exporter G2__
* __DB Importer G2__
* __Multiple-Field Updater G2__
* __Custom Field Manager__

The above addons are only a few of the addons available via the Addons Platform -- install them with one click directly from your admin interface's Addons Marketplace.  These G2 addons also communicate with each other to make full benefit of the Addons Platform's API. 

All addons available previous to Store Locator 2.0 are now called "G1" (Generation 1) addons, and can still be used, with or without the Addons Platform installed (if you purchased an addon prior to 2.0, re-download it via the emailed link of your purchase and install a slighty updated version that allows it to work with 2.0).  

In addition to the 8 addons listed above, there are currently 8 additional bonus addons -- visible to those with the Addons Platform installed: Currently, 3 are active, 1 is yet-to-be-released, and 4 more addons are in development.

= Target Users =
* Those of you who create sites for clients using WordPress
* Those of you who want to show your important locations (stores, buildings, points of interest, etc.) in an easily searchable manner.

= Great Built-In Functionality & Features =
* You can use it for numerous countries, which will continue to be added as Google adds new countries to their Google Maps API.  See the documentation for the latest
* Supports international languages/translations and character sets 
* Allows you to use unique map icons or your own custom map icons --- great for branding your map
* Gives your map the desired look by using our MapDesigner&trade; interface in the WordPress admin section
* Pick other cool Google Maps options, such as an inset box, zoom level, map types (street, satellite, hybrid, physical), and more
* You can use miles or kilometers
* Automatically restricts loading of Javascript & CSS to only pages that display the map (or that might need access to the JS & CSS) for better site performance
* Option to show dropdown list of cities allows visitors to quickly see where your locations are and choose their search accordingly

= Upgrades =
If you need additional features, enhance your store locator with addons & themes. [Upgrade Here](http://www.viadat.com/products-page/)

= Other Links =
[All Downloads](http://www.viadat.com/store-locator/) | [Addons & Themes](http://www.viadat.com/products-page/) | [New Features & Updates Blog](http://www.viadat.com/category/store-locator/) | [Documentation](http://docs.viadat.com/)

= Special Thanks to Translators (Email new translations to info{at}viadat{dot}com) =
* Simon Schmid: German (Deutsche), Italian (Italiano), Czech (Cestina), French (Francais)
* Gwyn Fisher: Spanish (Espanol)
* [Josef Klimosz](http://pepa.rudice.eu/): Czech (Cestina)(updated)
* [Willem-Jan Korsten](http://www.ezhome.nl/): Dutch (Nederlands)
* [Marcelo V. Araujo](http://www.mgerais.net/): Portuguese (Portugues)
* [Reno](http://www.creaprime.fr): French (Francais)(updated)
* [Alf Vidar Snaeland](http://www.fastfrilans.no): Norwegian (Norsk)
* [Laifeilim](http://www.fileem.com): Simplified Chinese
* Victor Ukhimenko: Russian
* [Rene](http://wpwebshop.com): Turkish
* [Outshine Solutions](http://outshinesolutions.com): Hindi
* [Diana S.](http://www.wpcouponshop.com): Serbian

([How to submit your translation](http://www.viadat.com/2009/02/store-locator-translation-files-wanted-you-might-just-get-something-nice-in-return/) | If you provide your web address, we'll link back to you)

== Installation ==
= Main Plugin =
1. Upload the `store-locator` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Sign up for a Google Maps API Key for your domain. Instructions: https://developers.google.com/maps/documentation/javascript/tutorial#api_key
4. Add your locations through the 'Locations' page in the Store Locator admin panel
5. Place the shortcode `[STORE-LOCATOR]` in the body of a page or a post to display your store locator (See 'Creating Multiple Store Locators' section for details on adding parameters to the shortcode, which would allow for multiple store locators on your website)

= Addons =
1. Unzip & Upload the entire addon folder to the `/wp-content/uploads/sl-uploads/addons` directory
2. Activate the addon by updating the Activation Key that you receive after purchase at the bottom of the "News & Upgrades" Page

= Themes =
1. Unzip & Upload the entire theme folder to the `/wp-content/uploads/sl-uploads/themes` directory
2. Select theme from the theme dropdown menu under the "Design" section on the "MapDesigner&trade;" Page

= Icons =
1. There are some default icons in the `/wp-content/plugins/store-locator/icons` directory
2. Add your own custom icons in to `/wp-content/uploads/sl-uploads/custom-icons`

= Custom CSS (Stylesheet) =
1. You can modify the default 'store-locator.css' and place it under `/wp-content/uploads/sl-uploads/custom-css/`
2. The store locator will give priority to the 'store-locator.css' in the `/wp-content/uploads/sl-uploads/custom-css/` folder over the default 'store-locator.css' in the main `store-locator/` folder. This allows you to upgrade the main store locator plugin without worrying about losing your custom styling.

== Screenshots ==

1. Showcase of Store Locator Usage By Well-Known Organizations, With Some Having Customized it to Their Brands
2. News & Upgrades: See the Latest News and Available Addons for Your Store Locator
3. Manage Locations: Easily Manage a Few or Many Locations, Sortable by Name, City, etc.
4. Add Locations: Once You Add a Location, it is Automatically Given Coordinates
5. MapDesigner&trade;: Choose the Important Options For the Look & Feel of Your Map
6. Addons Platform: Increase Your Store Locator's Capabilities With Numerous Addons & Themes that are Included
7. Quickly Upload Many Locations From a Spreadsheet to Save Time (Addon: CSV/XML Importer/Exporter)
8. Bulk Update More than just the Tags Field (Addon: Multiple-Field Updater)
9. Examples of the Store Locator's Basic and Upgraded User Interfaces

== Changelog ==
= 2.4 =
* New option in MapDesigner&trade; admin page to decide whether or not to show search results listing in addition to map icons when loading locations by default
* Updated deprecated regex functions, to faster ones
* Wrapped new strings for [translation](http://www.viadat.com/2009/02/store-locator-translation-files-wanted-you-might-just-get-something-nice-in-return)

= 2.3.x =
* New fields in MapDesigner&trade; admin page to modify the messages to website visitors when a searched location doesn't exist or there are no results after performing a search
* Further location loading enhancements
* Admin CSS Update for News & Upgrades Page
* Image updates

= 2.2 =
* Improved loading of XML of locations for those loading locations by default
* Re-introducing "News & Upgrades" page
* Added Screenshots
* Now uses more stable Google Maps API V3 

= 2.1 =
* Improved Version 1.x.x to Version 2.x.x transition -- better map loading on Store Locator page

= 2.0 =
* Fully Uses Google Maps API V3 -- both for displaying maps, geocoding locations, and performing reverse geocoding.  Transition from V2 is automatic and seamless -- no extra steps on your part.
* New default fields added: Fax & Email Address
* Streamlined interface
* Benchmarked coding improvements for fastest code performance
* Greatly reduced database usage (reduced by 81.4%)
* New pull-out Dashboard for important settings & management tasks
* New option to perform automatic search based on visitor's current location (auto-location)
* 40+ new Google maps country domains
* New address map icons
* Addons Platform: boosts your Store Locator's abilities dramatically, based off of your most-requested features & paid customizations: 11+ free G2 addons. 8 listed above in 'Description' section, 3 visible only to those with the Addons Platform installed, 1 yet-to-be-released, 4+ currently in development, and compatible with the 3 main addons prior to Store Locator 2.0 (CSV/XML Importer/Exporter, DB Importer, & Multiple Field Updater; the Point, Click, Add Mapper will be retired).  For those who purchased addons prior to 2.0, you can re-visit the email link sent to you when you purchased the addon, then re-download and install the updated version which makes it compatible with 2.0.

= 1.9.7 =
* Update for geocoding of locations for those previously having Google Geocoding API issues

= 1.9.6.x =
* Added Serbian translation (Thank you [Diana S.](http://wpcouponshop.com)). Copy translation into `/wp-content/uploads/sl-uploads/languages/` to use.
* Additional updates/fixes added

= 1.9.5 =
* New links for custom icons on MapDesigner&trade; page
* Minor fixes

= 1.9.4 =
* Added South Africa as a country option
* Bug fixes
* CSS fixes

= 1.9.2 =
* Added Philippines as a country option for one's map

= 1.9.1 =
* Update/fix for re-geocoding (thank you Seb M.)

= 1.9 =
* Tagging fixes
* CSS fixes
* Bug fixes

= 1.8.1 =
* Translator update

= 1.8 =
* Added Hindi translation (Thank you [Outshine Solutions](http://outshinesolutions.com)). Copy translation into `/wp-content/uploads/sl-uploads/languages/` to use.

= 1.7 =
* Improved tagging
* More efficient loading of admin Javascript & CSS
* Improved code for devs using 'WP_DEBUG' mode
* CSS fix

= 1.6 =
* Much improved processing image next to search button - important to update from 1.5
* Tag management improvement/fixes
* Bug fixes

= 1.5 =
* Circular loading/processing image next to search button during search to know that locator is actively performing search
* Zooms out when showing 1 location on initial load or search is zoomed in too close
* Smoother initial loading of locations by default
* Numerous CSS fixes to map interface
* Ability to determine maximum number of locations shown in search results for DB health (uses same value for number of locations loaded by default)

= 1.4.1 =
* Bug fix for 'Mangage Locations' page not showing locations if Google API Key isn't entered yet

= 1.4 =
* Functionality to bulk re-geocode ungeocoded locations on 'Manage Locations' page (button only appears if you have any ungeocoded locations)
* Small admin CSS fix
* Updated link to get Google Maps API key

= 1.3 =
* New horizontal top navigation for Store Locator admin pages
* Made 'Directions' link label on map & results list editable from MapDesigner&trade; page
* Better response messages during key validation
* Cleaned up 'News & Upgrades' page; updated navigation links of 'Manage Locations'
* Google API Key submission/important message for new users, instead of restricting view of other pages 

= 1.2.43 =
* Bug fixes

= 1.2.42.1 =
* Added new translators to readme

= 1.2.42 =
* Added Russian translation (Thank you Victor Ukhimenko)
* Added Turkish translation (Thank you [Rene](http://wpwebshop.com))
* Updated Norwegian translation to proper abbreviation (no_NO -> nb_NO). Copy translations into `/wp-content/uploads/sl-uploads/languages/` to use.
* Several CSS fixes, noticeable only for certain themes

= 1.2.41 =
* Added Portuguese translation (Thank you [Marcelo V. Araujo](http://www.mgerais.net))

= 1.2.40 =
* Added updated French translation (Thank you [Reno](http://www.creaprime.fr))
* Added Norwegian translation (Thank you [Alf Vidar Snaeland](http://www.fastfrilans.no))
* Added Simplified Chinese translation (Thank you [Laifeilim](http://www.fileem.com)). Copy translations into `/wp-content/uploads/sl-uploads/languages/` to use
* Small fix for export link path
* Small fix for navigation links when viewing locations and clicking 'Next' or 'Previous'
* Added translation wrappers to labels 'Update Central' ('News & Upgrades' page) and 'Additional Information' ('Add Locations' page)

= 1.2.39.3 =
* Added Dutch translation (Thank you [Willem-Jan](http://www.ezhome.nl/)). Place translation in `/wp-content/uploads/sl-uploads/languages/` to use.
* Added translation wrappers to text currently without them

= 1.2.39.2 =
* Small fix of XML issue when displaying locations by default on map load if ampersand (&) is used in a location's URL

= 1.2.39.1 =
* Updated the Czech translation (Thanks to [Josef](http://pepa.rudice.eu/))
* Small fix to make URLs of icons point to correct directory on first use

= 1.2.39 =
* 'NOT NULL' changed to 'NULL' when creating database table fields to prevent table creation error for some users
* Missing search button image bug fixed
* 'Insufficient permissions' issue fixed that's occuring for some users when viewing past the first page of locations on 'Manage Locations' page
* Styling of hover color, background color, other styling of individual search results div moved to 'store-locator.css' for easier customization
* Styling updates of address search form elements above the map being mis-aligned by some themes
* ReadMe in admin section styling update for better readibility
* Minor admin dashboard rss widget improvements (bugfix/styling)

= 1.2.38 =
* Small fix with JS/CSS section message on non-store locator pages
* Notification message added to admin panel for those switching from 'wordpress-store-locator-location-finder' to 'store-locator' to re-select their icons on the 'MapDesigner&trade;' page to avoid blank icons on map
* Higher number options for 'Locations Per Page' on 'Manage Locations' page
* For those who don't use the default WordPress plugin updater, but instead download and paste on top of an old version, it checks if addons, themes, languages, images moved to 'wp-content/uploads/sl-uploads' and copies them over if they haven't been already

= 1.2.37.1 =
* Moved more inline styles classes & ids in 'store-locator.css' for better styling of store locator map
* Updated automatic folder creation and moving of addons, themes, languages, images, etc. in the 'uploads/sl-uploads' folder.  
* Very important -- make sure to update from version 1.2.37 to avoid any map issues

= 1.2.37 =
* Updated default database, server, & connection character set and collation.  Should complete international language support.
* A few XHTML Transitional 1.0 validation fixes
* Different url encoding for map directions
* Directory structure update: Upgrades (addons, themes), icons, languages, images, custom css moved to 'wp-content/uploads/sl-uploads' directory (moved automatically).  
* WordPress default one-click updater is now active again.  (Custom quick updater introduced in version 1.2.21 is no longer needed)
* Moved inline styles to stylesheet classes for search result text and columns, and for text in info bubble on map.  Fully customizable from 'store-locator.css' now.
* NOTE: As a result of the updated directory structure, you no longer need to worry about losing any of your customizations (CSS, addons, themes, icons, images, languages)! Place any customizations that you make into the corresponding folder in the 'wp-content/uploads/sl-uploads' directory and they will be safe during upgrades to newer, better versions (For example: if place 'store-locator.css' in the 'wp-content/uploads/sl-uploads/custom-css' folder, the plugin will use those styles instead of the styles of any 'store-locator.css' in the main `store-locator` directory.  So, when an update is performed --- you don't lose your style customizations!)

= 1.2.36 =
* Added option to select character encoding set on 'Localization & Google API Key' page. Should help with supporting characters in many different languages.

= 1.2.35.1 =
* Removed non-working Google Map Country Domains (Hungary, Kenya, Malaysia, South Africa, Thailand -- seems they have map domains, but can't be embedded on websites yet)

= 1.2.35 =
* Improved restricted loading of JS, CSS to pages & posts on which store locator shortcode has been placed (and the archive pages only if shortcode is placed into any posts, and the home and search pages)
* Added input field on 'MapDesigner&trade;' page for editing message to website visitors shown below map
* Modified custom upgrade link on plugins page to be one-click, similar to other plugins
* Few other admin styling changes for buttons
* Fixed bug causing search results to not show all locations for a given radius when distance unit is set to 'km'
* Improved geocoding on locations for each Google Map Country Domain. Added param that should make geocoding more accurate depending on which country you've selected

= 1.2.34 =
* Fixed 50+ XHTML validation issues (HTML generated by plugin should now validate as XHTML 1.0 Transitional)
* Updated 'Localization & Google API Key' page layout
* Added new Google Maps country domains
* Multiple minor interface improvements across admin pages

= 1.2.33.1 =
* Small XHTML validation fix
* Removed restriction of loading javascript & CSS to only pages with store locator shortcode for time-being

= 1.2.33 =
* Fixed gray / blank map issue due to CSS from certain themes affecting map tile images
* Cleaned up header javascript, CSS appearance
* Fixed 'store-locator-js.php' issue causing gray / blank map for some users due to their servers
* Restricted loading of javascript, CSS to only pages on which store locator shortcode has been placed

= 1.2.32 =
* Fixed directions link by better handling of '#' (and other symbols) in addresses

= 1.2.31 =
* Fixed small bug with display of URL on "View Locations" page for each location
* Beautified the update form for individual locations on the "View Locations" page

= 1.2.30 =
* Improved handling of URLs for each location. Now, URL will still show up even if you omit the "http://" at the beginning
* Beautified the default input form on the "Add Locations" page

= 1.2.29 =
* Fix for some maps having issues showing results in certain browsers

= 1.2.28.4 =
* Minor fix to prevent duplicate of the same city from showing up in the city dropdown options

= 1.2.28.3 =
* Fixes the "The Google Maps API rejected your request ..." error on the "Add Locations" page.  Good catch by forum user 'Israel' of http://earthsoutlet.com .

= 1.2.28.2 =
* Incorporated fix for "Warning: Cannot modify header information Ð headers already sent ..." error occuring for those that have updated to WordPress Version 2.8.1.  Fix provided by forum user 'mikeycnp' here: http://rosile.com/2009/07/wp-store-locator-fix/ --- thank you mikeyncp.  Users, please make sure to leave thank you comment on http://rosile.com .

= 1.2.28.1 =
* Fixed "You do not have sufficient permissions to access this page." issue occuring for those that have updated to WordPress Version 2.8.1
* Added this Changelog

== Frequently Asked Questions ==
Make sure to check http://docs.viadat.com for the most updated information

= Do I need a Google Account to use this store locator? =
Yes, you will need a Google account in order to retrieve an API for your maps to work properly

1. To sign up for a Google Account, visit: https://www.google.com/accounts/
2. To sign up for a Google Maps API Key, visit: https://developers.google.com/maps/documentation/javascript/tutorial#api_key

= How Do I use a Translation? =
1. Place .po & .mo translation files into the `/wp-content/uploads/sl-uploads/languages` folder, and then change the `WPLANG` constant to the corresponding language abbreviation in the `wp-config.php` file in the root wordpress directory
2. Example: to use French, make sure `lol-fr_FR.po` & `lol-fr_FR.mo` are in the `/wp-content/uploads/sl-uploads/languages` folder, then make sure to update the code in `wp-config.php` to read `define('WPLANG', 'fr_FR')`, and Voila, Il sera en francais (It will be in French).

= Which countries is this compatible with? =
This plugin is compatible with all countries that have Google Map domains. This includes:
* Algeria
* American Samoa
* Angola
* Argentina
* Australia
* Austria
* Bangladesh
* Bahrain
* Belgium
* Belize
* Benin
* Botswana
* Brazil
* Bulgaria
* Burundi
* Canada
* Central African Republic
* Chile
* China
* Congo
* Czech Republic
* Djibouti
* Dem. Republic of Congo
* Denmark
* Ethiopia
* Finland
* France
* Gabon
* Gambia
* Germany
* Ghana
* Greece
* Hong Kong
* Hungary
* India
* Indonesia
* Italy
* Japan
* Kenya
* Lesotho
* Liechtenstein
* Macedonia
* Madagascar
* Malawi
* Malaysia
* Mauritius
* Mexico
* Mozambique
* Namibia
* Netherlands
* New Zealand
* Nigeria
* Norway
* Philippines
* Poland
* Portugal
* Reunion
* Russia
* Rwanda
* Sao Tome & Principe
* Senegal
* Seychelles
* Sierra Leone
* Singapore
* South Africa
* South Korea
* Spain
* Sri Lanka
* Sweden
* Switzerland
* Taiwan
* Tanzania
* Thailand
* Togo
* Uganda
* United Arab Emirates
* United Kingdom
* United States
* Venezuela
* Zambia
* Zimbabwe