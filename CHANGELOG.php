<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );
?>

1. Changelog
------------
This is a non-exhaustive (but still near complete) changelog for
Joomla! 1.0, including beta and release candidate versions.
Our thanks to all those people who've contributed bug reports and
code fixes.

Legend:

# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note


--------------------

11-Oct-2005 Rey Gigataras
  # Fixed Search Component flooding, by limiting searching to 20 characters

09-Oct-2005 Rey Gigataras
 # Fixed SQL injection bug in content submission - * Medium Security Bug *
 # Fixed artf1454 : After update email_cloacking bot is always on
 # Fixed artf1447 : Bug in mosloadposition mambot
 # Fixed artf1483 : SEF default .htaccess file settings are too lax
 # Fixed artf1480 : Administrator type user can loggof Super Adminstrator
 # Fixed artf1405 : Joomla shows Items to unauthorized users - * Minor Security Bug in 1.0.2 *
 # Fixed artf1422 : PDF Icon is set to on when it should be off
 # Fixed artf1476 : Error at "number of Trashed Items" in sections
 # Fixed artf1415 : Wrong image in editList() function of mosToolBar class

08-Oct-2005 Johan Janssens
 # Fixed artf1384 : tinyMCE doesnt save converted entities

07-Oct-2005 Andy Miller
 # Fixed tabpane css font issue

07-Oct-205 Andy Stewart
 # Fixed artf1382 : Added installation check to ensure "//" is not generated via PHP_SELF

07-Oct-2005 Johan Janssens
 # Fixed artf1421 : unneeded file includes\domit\testing_domit.php
 # Fixed artf1439 : Used correct ErrorMsg function and updated javascript redirect to remove POSTDATA message
 # Fixed artf1400 : Added a check of $other within com_categories to skip section exists check if set to "other"

05-Oct-2005 Robin Muilwijk
 # Fixed artf1366 : Typo in admin, Adding a new menu item - Blog Content Category

-------------------- 1.0.2 Released ----------------------

02-Oct-2005 Rey Gigataras
 ^ Added check to mosCommonHTML::loadOverlib(); function that will stop it from being loaded twice on a page
 # Fixed Content display not honouring Section or Category publish state
 # Fixed artf1344 : Link to menu shows wrong menu type
 # Fixed artf1189 : Long menu names get truncated, duplicate menus made
 # Fixed artf1192 : Unpublished Bots
 # Fixed artf1223 : Error with Edit items in categories and sections
 # Fixed artf1219 : Joomla Component Module displays Error!
 # Fixed artf1183 : Section module: Still "no items to display"
 # Fixed artf1241 : Editing content fails with MySQL 5.0.12b
 # Fixed artf1306 : modules - parameters of type text not stored correctly

01-Oct-2005 Andy Miller
 # Fixed base href in Content Preview for broken images

01-Oct-2005 Johan Janssens
 ^ Updated TinyMCE editor to version RC 3
 # Fixed artf1221 : Unable to Submit Content (still not working post-patch)
 # Fixed artf1108 : Tooltips on mouseover causes parameter panel to widen
 # Fixed artf1217 : WYSIWYG-Editor and mospagebreak with 2 parameters

01-Oct-2005 Andy Stewart
 # Fixed artf1305 - Added a check within mosimage mambot for introtext being hidden
 # Fixes artf1343 - Removed xml declaration at top of gpl.html

01-Oct-2005 Arno Zijlstra
 ^ Changed OSM banner 2 a little to show banner changing

01-Oct-2005 Levis Bisson
 # Fixed artf1311 : Banners not showing / returning PHP error
 # Fixed artf1319 : Banners not showing in frontend / admin

30-Sep-2005 Andy Miller
 # Fixed poor rendering of fieldset with solarflare2
 ^ Updated solarflare2 template with new colors and logos
 ^ Moved modules to divs, and shuffled pathway to give more button room
 ^ Updated favicon and other Joomla! logos for admin
 # Fixed alignment of footer in admin for safari/opera

30-Sep-2005 Andy Stewart
 + Updated installation routine to recognise port numbers other than 80
 # Fixed artf1293 : added $op=mosGetParam so sendmail is called when running globals.php-off

30-Sep-2005 Rey Gigataras
 ^ Module Manager `position` dropdown ordering alphabetically
 ^ Ability to Hide feed title for `New` modules used to display feeds
 ^ Content Items `New` button sensitive to dropdown filters
 # Fixed Seach Module not using Itemid of existng `Seach` component menu item
 # Fixed `Link to Menu` problem with Sections menu ordering
 # Fixed `Link to Menu` problem with Category = `Content Category`
 # Fixed artf1300 : PDF shows Author name despite setting content item

30-Sep-2005 Levis Bisson
 + Added UTF-8 support
 # Fixed tooltips empty links
 # Fixed artf1265 : url in 'edit-menue-item' of submenues is wrong
 # Fixed artf1277 : News Feed Display Bad Accent character

29-Sep-2005 Arno Zijlstra
 # Fixed publish/unpublish select check in contacts

29-Sep-2005 Rey Gigataras
 # Fixed artf1276 : tiny mce background
 # Fixed artf1281 : Bad name of XML file
 # Fixed artf1180 : Call-by-reference warning when editing menu
 # Fixed artf1188 : includes/vcard.class.php uses short open tags

29-Sep-2005 Levis Bisson
# Fixed artf1274 : Module display bug when using register/forgot password links
# Fixed artf1238 : header("Location: $url")- some servers require an absolute URI

28-Sep-2005 Levis Bisson
 # Fixed artf1250 : Order is no use when many pages
 # Fixed artf1254 : Unable to delete when count > 1
 # Fixed artf1248 : Invalid argument supplied for 3P modules

27-Sep-2005 Arno Zijlstra
 # Fixed artf1253 : Apply button image path
 # Fixed artf1240 : WITH FIX: banners component - undefined var task
 # Fixed artf1242 : Problem with "Who's online"
 # Fixed artf1218 : 'Search' does not include weblinks?

25-Sep-2005 Emir Sakic
 # Fixed artf1185 : globals.php-off breaks pathway
 # Fixed artf1196 : undefined constant categoryid
 # Fixed artf1216 : madeyourweb no </head> TAG

24-Sep-2005 Rey Gigataras
 ^ artf1214 : pastarchives.jpg seems unintuitive.

22-Sep-2005 Rey Gigataras
 + Added Version Information to bottom of joomla_admin template, with link to 'Joomla! 1.0.x Series Information'
 # Fixed artf1175 : Create catagory with selection of Section
 # Fixed artf1179 : Custom RSS Newsfeed Module has nested <TR>

-------------------- 1.0.1 Released ----------------------

21-Sep-2005 Rey Gigataras
 # Fixed artf1157 : Section module: Content not displayed, wrong header
 # Fixed artf1159 : Can't cancel "Submit - Content" menu item type form
 # Fixed artf1172 : "Help" link in Administration links to Mamboserver.com
 # Fixed artf1171 : mod_related_items shows all items twice
 # Fixed artf1167 : Component - Search
 # Fixed [RC] incorrect redirect when cancelling from Frontend 'Submit - Content'
 # Fixed undefined variable in Trash Manager
 # Fixed [RC] `Trash` button when no item selected
 # Fixed [RC] `New` Menu Item Type `Next` button bug

20-Sep-2005 Levis Bisson
 ^ added a chmod to the install unlink function
 # Fixed artf1150 : the created_by on initial creation of Static Content Item

20-Sep-2005 Marko Schmuck
 ^ Changed Time Offsets to hardcoded list with country/city names

20-Sep-2005 Rey Gigataras
 # Fixed /installation/ folder check
 # Fixed artf1153 : Quote appears in com_poll error
 # Fixed artf1151 : empty span
 # Fixed artf1089 : multile select image insert reverses list order
 # Fixed artf1138 : Joomla allows creation of double used username
 # Fixed artf1133 : There is no install request to make /mambot/editor writeable

19-Sep-2005 Andrew Eddie
 # Fixed incorrect js function in patTemplate sticky and ordering templates/links

19-Sep-2005 Rey Gigataras
 ^ Changed Overlib styling when creating new menu items
 ^ Additional Overlib info for non-image files and directories
 ^ 'Cancel' button for Media Manager
 ^ Option to run TinyMCE in compressed mode - off by default
 # Fixed artf1111 : mosShowHead and the order of headers
 # Fixed artf1117 : database.php - bcc
 # Fixed artf1114 : database.php _nullDate
 # Fixed TinyMCE errors caused by use of compressed tinymce_gzip.php [artf1088||artf1034||artf1090||artf1044]
 # Installed Editor Mambots are now published by default
 # Fixed error in RSS module
 # Fixed artf1106 : Default Editor Will Not Take Codes Like Java Script
 # Fixed delete file in Media Manager

18-Sep-2005 Arno Zijlstra
 # Fixed artf1084 : <br> stays in empty content
 # Fixed artf1101: Typo in Global Config

18-Sep-2005 Andrew Eddie
 # Fixed issues in patTemplate Translate Function and Modifier
 # Fixed issue with patTemplate variable for Tabs graphics

18-Sep-2005 Rey Gigataras
 # Fixed artf1046 : Menu Manager Item Publishing
 # Fixed artf1036 : newsflash error when logged in in frontend
 # Fixed artf1033 : madeyourweb template logo path
 # Fixed artf1039 : & to &amp; translation in menu and contenttitle
 # Fixed PHP5 passed by reference error in admin.content.php
 # Fixed artf1068 : live bookmark link is wrong
 # Fixed artf1030 : Bug Joomla 1.0.0 Stable (un)publishing News Feeds
 # Fixed artf1048 : Custom Module Bug
 # Fixed artf1080 : Joomla! Installer
 # Fixed artf1050 : error in sql - database update
 # Fixed artf1081 : com_categories can't edit category when clicking hyperlink
 # Fixed artf1053 : Can not unassign template
 # Fixed artf1079 : com_weblinks can't edit links
 # Fixed artf1029 : Site -> Global Configuration = greyed out top menu
 # Fixed artf1064 : Deletion of Modules and Fix
 # Fixed artf1052 : Double Installer Locations
 # Fixed artf1051 : Copyright bumped to the right of the site
 # Fixed artf1059 : component editor bug
 # Fixed artf1041 : mod_mainmenu.xml: escape character for apostrophe missing
 # Fixed artf1040 : category manager not in content-menu

17-Sep-2005 Levis Bisson
 # Fixed artf1037: Media Manager not uploading
 # Fixed artf1025: Registration admin notification
 # Fixed artf1043: Template Chooser doesn't work
 # Fixed artf1042: Template Chooser shows rogue entry

-------------------- 1.0.0 Released ----------------------

16-Sep-2005 Andrew Eddie
 # Fixed: 1014 : & amp ; in pathway
 # Fixed: Missing space in mosimage IMG tags
 # Fixed: Incomplete function call - mysql_insert_id()
 + Added nullDate handling to database class
 + Added database::NameQuote function for quoting field names
 # Fixed: com_checkin to properly use database class
 # Fixed: Missed stripslashes in`global configuration - site`
 + Added admin menu item to clear all caches (for 3rd party addons)

16-Sep-2005 Emir Sakic
 # Fixed sorting by author on frontend category listing
 + Added time offset to copyright year in footer
 # Fixed spelling in sam        
 # Reflected some file name changes in installer CHMOD
 # Fixed bugs in paged search component

16-Sep-2005 Alex Kempkens
 + template contest winner 'MadeYourWeb' added

16-Sep-2005 Rey Gigataras
 + Pagination Support for Search Component
 ^ Ordering of Toolbar Icons/buttons now more consistent
 ^ Frontend Edit, status info moved to an overlib
 ^ Search Component converted to GET method
 # Fixed artf1018 : Warning Backend Statistic
 # Fixed artf1016 : Notice: RSS undefined constant
 # Fixed artf1020 : Hide mosimages in blogview doesn't work
 # Various Search Component Fixes
 # Fixed Search Component not honouring Show/Hide Date Global Config setting
 # Fixed [#6668] No static content edit icon for frontend logged in author
 # Fixed [#6710] `Link to menu` function from components Category not working
 # Fixed [#7011] Subtle bug in saveUser() - admin.users.php
 # Fixed [#7120] Articles with `publish_up` today after noon are shown with status `pending`
 # Fixed [#6669] mosmail BCC not working, send as CC
 # Fixed [#7422] Weblink submission emails
 # Fixed [#7196] mosRedirect and Input Filter CGI Error
 # Fixed [#6814] com_wrapper Iframe Name tag / relative url modifications
 # Fixed [#6844] rss version is wrong in the Live Bookmark feeds
 # Fixed [#7120] Articles with `publish_up` today after noon are shown with status `pending`
 # Fixed [#7161] Apparently unncessary code in sendNewPass - registration.php

15-Sep-2005 Andy Miller
 ^ Fixed some width issues with Admin template in IE
 ^ Fixed some UI issues with Banners Component
 ^ Added a default header image for components that don't specify one

15-Sep-2005 Andrew Eddie
 - Removed unused globals from joomla.php
 + Added mosAbstractLog class

15-Sep-2005 Rey Gigataras
 + added `Apply` button to frontend Content editing
 ^ Added publish date to syndicated feeds output [credit: gharding]
 ^ Added RSS Enclosure support to feedcreator [credit: Joseph L. LeBlanc]
 ^ Added Google Sitemap support to feedcreator
 ^ Modified layout of Media Manager
 ^ Added Media Manager support for XCF, ODG, ODT, ODS, ODP file formats
 # Fixed use of 302 redirect instead of 301
 # Content frontend `Save` Content redirects to full content view
 # Fixed Wrapper auto-height problem
 # Queries cleaned of incorrect encapsulation of integer values
 # Fixed Login Component redirection [credit: David Gal]

15-Sep-2005 Arno Zijlstra
 ^ changed tab images to fit new color
 ^ changed overlib colors

14-Sep-2005 Rey Gigataras
 ^ Ugraded TinyMCE [2.0 RC2]
 ^ Param tip style change to dashed underline
 # Queries cleaned of incorrect encapsulation of integer values

14-Sep-2005 Andrew Eddie
 # Added PHP 5 compatibility functions file_put_contents and file_get_contents
 + Added new version of js calendar
 + mosAbstractTasker::setAccessControl method
 + mosUser::getUserListFromGroup
 + mosParameters::toObject and mosParameters::toArray

13-Sep-2005 Andrew Eddie
 ^ Rationalised global configuration handling
 # Fixed polls access bug
 # Fixed module positions preview to show positions regardless of module count
 ^ Modified database:setQuery method to take offset and record limit
 + Added alternative version of globals.php that emulates register_globals=off
 # Added missing parent_id field from mosCategory class

12-Sep-2005 Rey Gigataras
 + Per User Editor selection
 # Module styling applied to custom/new modules
 # Fixed Agent Browser bug

12-Sep-2005 Andrew Eddie
 + New onAfterMainframe event added to site index.php
 + Added dtree javascript library
 + Added some extra useful toolbar icons
 + Added css for fieldsets and legends and some 1.1 admin style formating
 + Added mosDBTable::isCheckedOut() method, applied to components
 # fixed bug in typedcontent edit - checked out is done before object load and always passes
 ^ Updated Help toolbar button to accept component based help files
 ^ Updated version class with new methods
 + Added support for params file to have <mosparams> root tag

12-Sep-2005 Andy Stewart
 # Fixed issue with new content where Categories weren't displayed for sections

12-Sep-2005 Andrew Eddie
 ^ Upgrade DOMIT! and DOMIT!RSS (fixes issues in PHP 4.4.x)
 + Added database.mysqli.php, a MySQL 4.1.x compatible version
 + Added [Check Again] button to installation check screen
 ^ Changed web installer to always use the database connector
 # Fixed PHP 4.4 issues with new objects returning by reference

11-Sep-2005 Rey Gigataras
 + Output Buffering for Admin [pulled from Johan's work in 1.1]
 + Loading of WYSIWYG Editor only when `editorArea` is present [pulled from Johan's work in 1.1]
 ^ Upgraded JSCookMenu [1.4.3]
 ^ Upgraded wz_tooltip [3.34]
  ^ Upgraded Overlib [4.21]
 ^ editor-xtd mosimage & mospagebreak button hidden on category, section & module pages
 # Poll class $this-> bug
 # Fixed change creator dropdown to exclude registered users (who do not have author rights)

11-sep-2005 Arno Zijlstra
 + Added offlinebar.php
 ^ Changed site offline check
 ^ Cosmetic change to offline.php

11-Sep-2005 Andrew Eddie
 + Added sort up and down icons
 + Added mosPageNav::setTemplateVars method

10-Sep-2005 Rey Gigataras
 + `Submit - Content` menu type [credit: Jason Murpy]

09-Sep-2005 Andy Miller
 ^ made changes to new joomla admin template
 ^ changed login lnf to match new admin template
 ^ removed border and width, set padding on div.main in admin
 ^ changed Force Logout text

09-Sep-2005 Alex Kempkens
 ^ changed mosHTML::makeOption to handle different coulmn names
 ^ corrected several calls from makeOption in order to become multi lingual compatible
 ^ corrected little fixes in query handling in order to get multi lingual compatible
 + Added system bot's for better integration of ml support, ssl & multi sites

08-Sep-2005 Rey Gigataras
 + Added back Sys Info link in menubar
 + Added Changelog link to Help area
 ^ Cosmetic change to Toolbar Icon appearance
 ^ Cosmetic change to QuickIcon appearance
 ^ Toolbar icons now 'coloured' no longer 'greyed out'
 ^ Dropdown menu now shows on edit pages but is inactive
 # Fixed Newsfeed component generates image tag instead of img tag
 # Fixed Joomlaxml: tooltips need to use label instead of name
 # Fixed One parameter too many in orderModule call in admin.modules.php
 # Fixed inabiility to show/hide VCard
 # Fixed Mambot Manager filtering

08-Sep-2005 Alex Kempkens
 + mosParameter::_mos_filelist for xml parameters
 ^ mos_ table prefix to jos_ in installation and in some other files.
 + added category handling for contact component
 + added color adapted joomla_admin template

07-Sep-2005 Andrew Eddie
 # Added label tags to mod_login (WCAG compliance)
 # Added label tags to com_contact (WCAG compliance)
 # Added label tags to com_search (WCAG compliance)
 # Added label tag support to mosHTML::selectList (WCAG compliance)
 # Added label tag support to mosHTML::radioList (WCAG compliance)

01-Sep-2005 Andrew Eddie
 + Added article_separator span after a content item
 ^ Hardened mosGetParam by using phpInputFilter for NO_HTML mode
 + Added new mosHash function to produce secure keys
 + Hardened Email to Friend form

31-Aug-2005 Andrew Eddie
 + Added setTemplateVars method to admin pageNavigation class
 ^ Added auto mapping function to mosAbstractTasker constructor
 + Added patHTML class for patTemplate utility methods
 ^ Upgraded patTemplate library
 ! patTemplate::createTemplate has changed parameters
 - Removed requirement to accept GPL on installation
 # Fixed bug in Send New Password function - mail from not defined
 # Fixed undefined $row variable in wrapper component
 # Fixed undefined $params in contacts component
 - Removed unused getids.php
 - Removed redundant whitespace
 ^ Convert 4xSpace to tab

08-Aug-2005 Andrew Eddie
 ^ Encased text files in PHP wrapper to help obsfucate version info
 # Changed admin session name to hash of live_site to allow you to log into more than one Joomla! on the same host
 # Fixed hardcoded (c) character in web installer files
 # Fixed slow query in admin User Manager list screen
 # Fixed bug in poll stats calculation
 # Fixed SQL injection bugs in user activation (thanks Enno Klasing)
 # Updated bug fixes in phpMailer class
 # Fixed login bug for nested Joomla! sites on the same domain

02-Aug-2005 Alex Kempkens
 # [#6775] Display of static content without Itemid
 # [#6330] Corrected default value of field

----- Derived from Mambo 4.5.2.3 circa. 17 Aug 12005 -----

2. Copyright and disclaimer
---------------------------
This application is opensource software released under the GPL.  Please
see source code and the LICENSE file
