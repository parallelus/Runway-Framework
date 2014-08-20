## v1.1.1 (June 24th 2014)

* Fix for '[]' instead of 'array()' reference in code.


## v1.1 (June 20th 2014)

* Fixed notice: Constant DS already defined in framework/includes/themes-manager/object.php
* Fixed some notices if admin object is invoked with an empty settings array
* The enhancement “#11 - Auto update from WordPress admin” is fixed
* The enhancement “#14 - Add tags to package history” is fixed
* The enhancement “#15 - Repeating Fields for Data Types” is fixed
* The bug “#16 - Default child theme: Problems changing theme name and settings” is fixed
* The bug “#17 - Add Options Page - menu item showing in standalone themes” is fixed
* Updated get_option('home') to home_url()
* The bug “#21 - Problems with standalone theme getting setup from Layout Manager, Sidebars and Content Types extensions“ is fixed
* The enhancement “#22 - Media Library popup for image select options“ is fixed
* Basic style updates for better appearance in WP 3.8 admin
* The enhancement “#23 - Update deprecated function get_theme_data() to wp_get_theme() “ is fixed
* Move function db_json_sync() to load-functions.php
* The enhancement “#24 - Update instances of TEMPLATEPATH with get_template_directory() “ is fixed
* Updated bloginfo() to get_stylesheet_directory_uri()
* Updated deprecated WP function get_bloginfo() to get_stylesheet_directory_uri()
* Updated deprecated WP funciton get_bloginfo() to get_stylesheet_directory_uri() and get_template_directory_uri()
* Updates for deprecated WP functions
* Fix to make sure we don't get Runway menus in a standalone theme that has been setup to run as a child theme for end user customization
* Fixed z-index for dialog pop-up window
* The incremental database update from JSON file is fixed
* The bug “#25 - Notice Undefined index: theme_options“ is fixed
* The bug “#26 - Custom Icon doesn't appear“ is fixed
* The bug “#27 - File Upload - data type problems in WordPress theme Customizer interface” is fixed
* Fixed text editor bug
* Updates for theme prefix
* The bug “#29 - Crossover data in File Upload data type” is fixed
* Updated style for field title description in Options Builder pages
* Updates to translation strings domain from THEME_NAME to 'framework'
* Fixed repeating field validation bug
* Fixed option page duplicate bug
* The bug in multi-select data type is fixed
* Updated show/hide options in layout manager
* The bug “Sidebars in standalone don’t delete” is fixed
* The bug “Resetting layout for category” is fixed
* The bug “Extensions deactivating during using theme options” is fixed
* The bug “Runway Child Theme name changing with error messages” is fixed
* The bug “#31 - Rebuild Download Packages - Tag does not show” is fixed
* The enhancement “#32 - PHP translation file for Options Builder, Layout Manager and other dynamic Extensions” is fixed
* The enhancement “Add functions rf__() and rf_e()” is fixed
* The enhancement “Create translation strings for data types” is fixed
* The enhancement “Add javascript translations for formsbuilder” is fixed
* The enhancement “Add file with javascript translations” is fixed
* The enhancement “Add translation strings for Dashboard, Download Directory, Auth Manager, Extensions Manager and Options Builder” is fixed
* The enhancement “Add translation strings for Option-page-render, Reports Manager and Themes Manager” is fixed
* Fixed custom taxonomy query bug
* The bug “#33 - Extension references not updating with theme name change” is fixed
* Fixed package download bug
* The bug “#34 - Faulty PHP Version Error on WPEngine Servers” is fixed
* Updated function get_options_data() for Content Types custom meta fields
* The enhancement “Add Code Editor data type” is fixed
* Fixed Code Editor data type bug
* Fix for Runway admin menus not appearing when no child theme is active
* The enhancement “Add width, height settings for Code Editor data type” is fixed
* Fix for error message, "Warning: Invalid argument supplied for foreach() in ..../wp-content/themes/runway-framework/framework/includes/load-functions.php
* The enhancement “Add confirm message before deleting for Options Builder” is fixed
* Fixed Custom taxonomies bug
* The bug “Content Types not deleting proper types in standalone theme, options not always saving” is fixed
* Fixed deletion taxonomies bug
* The enhancement “#36 - Exclude "framework/themes" folder from standalone versions” is fixed
* The enhancement “#37 - Core extensions to exclude from standalone themes” is fixed
* Updates for download-directory, extensions-manager, auth-manager
* The enhancement “Add custom base64_decode function” is fixed
* Moved Theme Updater extension to core
* Updated Theme Updater extension


## v1.0.1 (January 9th 2014)

* Fix for #6, local data for Layout Manager fields.
* Fixed missing image in Forms Builder.
* Added the checking for 'DS' constant #5.
* Added the message about successful settings save #10.
* Update develop branch to the latest version of the framework
* Add extensions dir
* Fixed bug in report-manager
* Change extensions server source
* Add new extensions "Auth Manager" structure
* Inputs not showing in correct order - issue #12 is fixed
* Changes in auth manager
* Local JSON data file for Reports Manager
* Fix for edit/delete buttons in Forms Builder on WP 3.8 new interface.


## v1.0 (November 5th 2013)

* Initial release of version 1.0
