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
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
1. Copyright and disclaimer
---------------------------
This application is opensource software released under the GPL.  Please
see source code and the LICENSE file


2. Changelog
------------
This is a non-exhaustive (but still near complete) changelog for
Joomla! 1.1, including beta and release candidate versions.
Our thanks to all those people who've contributed bug reports and
code fixes.


Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note


-------------------- 1.1.0 Alpha2 [**-***-2006] ------------------------

01-Feb 2006 Louis Landry
 * Itemid script injection - Thanks Mathijs de Jong
 ^ Framework file catagorization cleanup
 ^ JACL class renamed to JAuthorization
 
01-Feb 2006 Rey Gigataras
 ^ Registration component output correctly separated into .html.php
 
01-Feb 2006 Johan Janssens
 - Removed mod_templatechooser

01-Feb-2006 Emir Sakic
 # Fixed admin menubar hover href issue

01-Feb-2006 Andrew Eddie
 # Fixed change of JModel::publish_array to JModel::publish (since 1.0.3)
 # Fixed bug in JTree where parent_id of 0 not correctly handled if children array is out of order
 # Added missing getAffectedRows methods to database classes

31-Jan-2006 Louis Landry
 # Fixed [topic,34073.0.html] : 2076 - Installation problem
 # Fixed [topic,34212.0.html] : Bug on installation

31-Jan-2006 Rey Gigataras
 # Fixed [topic,34507.0.html] : Contact Us -> not authorized result
 # Fixed [topic,34574.0.html] : JS error in overlibs for the menu items in menu
 # Fixed [topic,34515.0.html] : Error when adding weblink on frontend
 # Fixed [topic,34103.0.html] : .contentheading and the frontend edit button
 # Fixed [topic,34522.0.html] : Menu Manager `Type` column error
 # Fixed [topic,34247.0.html] : Not authorized message on News Feeds
 # Fixed [topic,34303.0.html] : Menu not greyed out
 # Fixed : DOMIT notice errors
 # Fixed : missing access column in 'Contact Manager'
 # Fixed : 'Banner Manager' `state` filter
 ^ Modified sample data menu ids
 + Additional Contact Component hardening

30-Jan-2006 Louis Landry
 # Fixed cache path problem on install

30-Jan-2006 Emir Sakic
 + Added Preview in new window for inactive toolbar menus
 # Fixed css upload a file style
 # Fixed upload a file image
 # Incorrect slash replace in ImageCheck function

30-Jan-2006 Samuel Moffatt
 ^ Moved $my to after onAfterStart trigger in index.php and index2.php

30-Jan-2006 Arno Zijlstra
 # Fixed css file edit style

29-Jan-2006 Louis Landry
 ^ Moved event library to the application package
 ^ Event system cleanup
 # Fixed editor display issue
 # Fixed artf3306 : locale - time offset in configuration - very minor
 # Fixed artf3255 : unable to edit _system css files
 # Fixed XAJAX problem on installation (PHP as CGI on apache)
 # Fixed custom help site per user not working

29-Jan-2006 Johan Janssens
 ^ Moved mail classes into mail library package
 # Fixed artf3285 : White page on Your Details and Check-In My Items
 # Fixed artf3263 : Unable to make new message in private messaging
 # Fixed artf3271 : Category image not visible (path incorrect)
 # Fixed artf3282 : Sample image is missing

29-Jan-2006 Rey Gigataras
 + Static Content can be assigned to `Frontpage`
 + `Move` & `Copy` ability added to "Static Content Manager"
 + `Move` to 'Static Content' added to "Content Items Manager"
 ^ Content item page navigaiton moved to `Page Navigation` plugin
 ^ `Messages` sub menu moved under `Site`
 ^ `Site` menu reorganized

28-Jan-2006 Louis Landry
 ^ Renamed auth plugins to authentication plugins
 # Fixed problem with 1 being displayed on events being triggered
 ^ Moved activate method to new static JUserHelper class

28-Jan-2006 Rey Gigataras
 + `mod_rss` renamed `mod_feed`
 + New `Delete` button for Admin "Edit" pages
 + `Save Order` Admin functionality added com_weblinks, com_newsfeeds, com_contact manager pages
 + `Filter` Admin functionality added to all manager pages
 + Pagination support to com_weblinks, com_newsfeeds, com_contact frontend output
 ^ `Preview` Admin Menu dropdown option moved under `Template Manager`
 - Depreciated `Content by Section` Admin Menu dropdown option

27-Jan-2006 Louis Landry
 - Removed siteurlbot
 ^ josURL now uses a quick switch and JURI to determine secure site URI information

27-Jan-2006 Rey Gigataras
 + Admin `Manager Pages` table ordering
 + Content Category now utilizes `table ordering` instead of `order select` method
 + `Trash Manager` separated into `Menu` & `Cotent` menu dropdowns
 - Depreciate `com_rss`, functionality replaced with `com_syndicate`
 - Depreciate `mod_rssfeed`, functionality replaced with `mod_syndicate`

26-Jan-2006 Rey Gigataras
 + Fully extensible Syndication functionality via `com_syndicate` and `syndicate` plugins
 + `Live Bookmark` functionality extended to other pages
 + `Syndicate` plugins

24-Jan-2006 Rey Gigataras
 ^ Consolidated toolbar icon functions

24-Jan-2006 David Gal
 + Added - pdf fonts now loaded with language packs and selected in language meta data
 + Added tools folder under tcpdf library with tools for adding user fonts
 - Removed old font folder under tcpdf
 - Removed Helvetica font from media folder (used with old pdf library)

24-Jan-2006 Johan Janssens
 + Added new JDocument Exists function
 - Removed live_site and absolute_path from configuration
 ^ Moved pdf.php into components/com_content

24-Jan-2006 Louis Landry
 ^ Finished JUser class
 + Added onActivate event triggered on user activation
 # Fixed: artf3197 : component install creates wrong directory permission
 # Fixed: artf2736 : Incorrect language js escape
 # Fixed: artf2911 : 1661: Admin menus inconsistent
 # Fixed: artf3193 : Template editor escapes on save... continually

23-Jan-2006 Johan Janssens
 ^ Feature request artf2781 : change $mosConfig_live_site to permit server aliasing
 # Fixed artf1938 : Help site server choice per admin user

23-Jan-2006 Rey Gigataras
 + Allow control of the formating of the SEO Page Title attribute via a new `Global Configuration` parameter
 ^ `Table of Contents on multi-page items` Global Param moved to "MosPaging" Param
 ^ Modified tooltips in `Global Config` to newer lower profile styling

22-Jan-2006 Louis Landry
 ^ Renamed JAuth to JAuthenticate and moved to the application package
 + Added JUser class to encapsulate operations on a user object [WIP]
 + Added JCacheHash for future compatability with phpGACL

22-Jan-2006 Rey Gigataras
 + `New` option in Module Manager, now allows selection of available Module Types, much like the `New` Menu Item functionality
 + Filter `State` dropdown added to all "Manager" pages
 + Allow Menu Items to be changed
 + `Apply` button to other core components
 ^ Reordered menu item edit page slightly

21-Jan-2006 Louis Landry
 ^ Changed authentication and login/logout event names
 + Added example user plugin

19-Jan-2006 Johan Janssens
 # Fixed base href problem in installation
 ^ Removed all instances for JURL_SITE define, use JApplication->getBasePath instead

19-Jan-2006 David Gal
 + Added rendering of {mosimage} images in pdf generation
 # Fixed tcpdf output headers to show pdf's in IE6
 + Added modified tcpdf pdf generation library for utf-8 data and php 4
 - Removed cpdf library - not suitable for utf-8
 ^ Changed component installer's query execution routine to support discrete sql scripts

19-Jan-2006 Louis Landry
 # Fixed a bug causing configuration values not to save
 ^ Finished implementation of component disable/enable functionality

18-Jan-2006 Louis Landry
 # Fixed a bug with module installer after move
 # Fixed artf3140 : component install creates wrong directory permission
 # Fixed artf3123 : Bad help addressing
 ^ Implemented phase one of component disable/enable functionality (GUI) WIP

18-Jan-2006 Johan Janssens
 # Fixed artf2172 : database settings not retained in installer

17-Jan-2006 Emir Sakic
 # Fixed a bug with base href in installer
 # Section and category lists not loading

17-Jan-2006 Louis Landry
 ^ Changed AJAX library for installer to XAJAX
 ^ Improved com_installer to a common Joomla Extension Manager interface
 ^ Moved modules into separate subdirectories of /modules and /administrator/modules
 # Publish language not working with new config file format
 # Pagination bug in com_search (not displaying full links)

17-Jan-2006 Johan Janssens
 ^ Implemented JDocument interface in the installation

15-Jan-2006 Samuel Moffatt
 ^ Added JauthResponse class to handle responses from plugins
 ^ Altered framework to include JAuthResponse object

14-Jan-2006 Samuel Moffatt
 # Fixed artf2143 : Altered radio buttons to checkboxes for installers

13-Jan-2006 Johan Janssens
 # Fixed artf2514 : Cannot preview template positions

12-Jan-2006 Louis Landry
 + Phase 1 of refactor and general code cleanup of content component
 ^ Implmented static template array in JApplication->getTemplate methods
 ^ Deprecated mosErrorAlert function, use josErrorAlert instead

11-Jan-2006 Louis Landry
 + Added template parameters

09-Jan-2006 Louis Landry
 ^ Refactor and general code cleanup of frontend Contact component
 ^ Implemented PHP class for global configuration values
 + Added PHP registry format

09-Jan-2006 Johan Janssens
 ^ Upgraded TinyMCE Compressor [1.06]

08-Jan-2006 Louis Landry
 ^ Refactor and general code cleanup of Media Manager
 ^ Removed $option coupling in JApplication
 + Added Template and Language extension handlers in installer menu
 # Fixed artf2941: Image Upload and Media Manager issues
 # Fixed artf1863 : Function delete_file and delete_folder not check str
 # Fixed artf2424 : Help Server Select list default problem
 # Fixed artf2948 : 1700: SEF broken
 # Fixed artf1747 : No clean-up in event of component installation failure
 ^ Feature request artf1728 : upload component from server
 ^ Feature request artf2017 : popups/uploadimage.php not using directory

07-Jan-2006 Louis Landry
 + Added JPagination class
 ^ Deprecated mosPageNav class, use JPagination instead
 # Fixed artf2917 : Rev#1665 -- Forced log-out on clicking "Upload" in content

06-Jan-2006 Johan Janssens
 ^ Implemented adpater pattern in JModel class
 ^ Updated geshi to version 1.0.7.5, moved to the libraries

06-Jan-2006 Louis Landry
 ^ Mambots refactored to Plugins
 ^ Interaction with editors is now controlled by JEditor
 # Fixed artf2926 : SVN 1669 file not renamed
 ^ Implemented auth plugins for user authentication

05-Jan-2006 Johan Janssens
 + Refactored administrator/com_installer - contributed by Louis Landry

04-Jan-2006 Johan Janssens
 - Removed JRegistry storage engines
 ^ Simplified JRegistry interface

03-Jan-2006 Andy Miller
 ^ Updated copyright information for iCandy Junior icons

02-Jan-2006 Johan Janssens
 ^ Deprecated mosPHPMailer, use JMail instead

01-Jan-2006 Johan Janssens
 + Added error templates for custom debug output
 # Fixed artf2807 : Missing file - Legacy plugins
 + Added JMail class

30-Dec-2005 Johan Janssens
 + Added JURI and JRequest classes - contributed by Louis Landry
 + Implemented AJAX functionality in the installation - contributed by Louis Landry
 - Removed administrator/mod_pathway
 + Template rendering completely overhaulted by new JDocument interface

27-Dec-2005 Johan Janssens
 # Fixed artf2742 : Backend generates just plain text output in IE or Opera
 # Fixed artf2739 : mambot edit-save error
 # Fixed artf2729 : Same content displayed twice on FrontPage
 - Removed administrator mod_msg module

26-Dec-2005 Samuel Moffatt
 + Added French language to installer

24-Dec-2005 Emir Sakic
 # Fixed a bug with 404 header being returned for homepage when SEF activated
 # Fixed a bug with all items on frontpage returning Itemid=1 (duplicate content)

23-Dec-2005 Andrew Eddie
 # Fixed mysqli support for collation

22-Dec-2005 Johan Janssens
 ^ Implemented adapter pattern for JDocument class
 + Added JDocumentHTML class
 ^ Deprecated mosParamaters, use JParameters instead
 ^ Deprecated mosCategory, use JCategoryModel instead
 ^ Deprecated mosComponent, use JComponentModel instead
 ^ Deprecated mosContent, use JContentModel instead
 ^ Deprecated mosMambot, use JMambotModel instead
 ^ Deprecated mosMenu, use JMenuModel instead
 ^ Deprecated mosModule, use JModuleModel instead
 ^ Deprecated mosSection, use JSectionModel instead
 ^ Deprecated mosSession, use JSessionModel instead
 ^ Deprecated mosUser, use JUserModel instead

22-Dec-2005 Andy Miller
 ^ Changed multi column content to display vertical like a newspaper
 + Added padding and seperator styles to multi column layout

21-Dec-2005 Johan Janssens
 + Added JTemplate Zlib outputfilter for transparent gzip compression
 + Added JPluginModel and JInstallerPlugin classes - contributed by Louis Landry

21-Dec-2005 Andy Miller
 + Added editor_content.css for MilkyWay template
 + changed admin acccent color from red to green to differentiate 1.0 and 1.1

21-Dec-2005 Levis Bisson
 + Added and wrapped tinymce language module file for parameters in the backend

20-Dec-2005 Emir Sakic
 # Fixed artf2432 : Apostrophe in paths isn't escaped properly

20-Dec-2005 Levis Bisson
 ^ Changed the translation text Mambots to Plugins
 # Reworked "load language function" for translating Modules and Plugins
 # Fixed path for site or admin modules in the backend

20-Dec-2005 Johan Janssens
 # Fixed artf2606 : JApplication::getBasePath interface changed from Joomla 1.0.4
 ^ Reworked installer to use an adapter pattern

19-Dec-2005 Johan Janssens
 ^ Refined mbstring installation checks - contributed by David Gal
 ^ Renamed Pathway module to Breadcrumbs - contributed by Louis Landry
 ^ Minor fixes in FTP library that should solve response code problems experienced on
   some mac ftp servers - contributed by Louis Landry
 # Fixed artf2655 : factory.php - xml_domit_lite_parser.php

17-Dec-2005 Johan Janssens
 + Added JPlugin class for easy handling of plugins - contributed by Louis Landry

16-Dec-2005 Andy Miller
 ^ Applied new rtl background for installer - Contributed by David Gal

16-Dec-2005 Johan Janssens
 + Imeplented authentication framework - Contributed by Louis Landry
 + Implemented observer design pattern - Contributed by Louis Landry
 + Implemented new plugin architecture - Contributed by Louis Landry
 ^ Refactored JEventHandler to JEventDispatcher extending from JObservable
 + Added installation setting for disbaling FTP settings

14-Dec-2005 Johan Janssens
 ^ Reworked caching system, moved handlers to seperate files.
 + Added PHPUTF8 library
 + Added JString class to handle mbstrings - contributed by David Gal

14-Dec-2005 Samuel Moffatt
 + Added Registry Table
 + Fixed up a few registry issues

13-Dec-2005 Johan Janssens
 ^ Implemented JFile and JFolder classes in the installers - contributed by Louis Landry
 + Added JError class for easy error management
 + Added JTemplate class, extends patTemplate class
 ^ Feature request artf1063 : Safemode patch for Joomla
 ^ Feature request artf1507 : FTP installer

12-Dec-2005 Johan Janssens
 ^ Fixed smaller file system problems - contributed by Louis Landry
 + Added mbstring and dbcollaction information to system info - contributed by David Gal
 # Fixed artf2485 : Impossible to install component/module/mambot/templates

11-Dec-2005 Levis Bisson
 # fixed Parameters translation from xml files
 # fixed Menu Manager Type & Tooltip translation
 + Added User Group translation
 + Added Access Level translation
 + Added Component Name translation

11-Dec-2005 Samuel Moffatt
 + Added JRegistry File Storage Engine
 * Added missing no direct access statements for JRegistry
 ^ XML Storage Format now working for JRegistry

10-Dec-2005 Emir Sakic
 # Fixed artf2517 : "Cancel" the editing of content after "apply" not possible

10-Dec-2005 Samuel Moffatt
 ^ Disabled php_value in htaccess file, caused 500 Internal Server Errors
 + JRegistry Core Complete (INI Format and Database Storage Engines)

09-Dec-2005 Emir Sakic
 # Fixed artf2324 : SEF for components assumes option is always first part of query
 # Fixed artf1955 : Search results bug
 + Added a solution for url-type menu highlighting

09-Dec-2005 Johan Janssens
 + Added support for FTP to the installation - Contributed by Louis Landry
 # Fixed artf2495 : Cant save user details from FE.
 + Added FTP settings to configuration
 + Added Debugging and Logging settings to configuration
 ^ Deprecated _VALID_MOS, used _JEXEC instead

08-Dec-2005 Andrew Eddie
 + Added patTemplate version of pathway code

08-Dec-2005 Johan Janssens
 + Added mbstring checks to installation - contributed by David Gal
 ^ Changed .htaccess file to ensure correct utf-8 support through mbstring
 + Added support for different languages to the TinyMCE bot

07-Dec-2005 Johan Janssens
 + Added JPathWay class for flexible pathway handling - Contributed by Louis Landry
 + Added transparent support for FTP to file handling classes - Contributed by Louis Landry
 ^ Upgraded TinyMCE Compressor [1.0.4]
 ^ Upgraded TinyMCE [2.0.1]
 + Added locale metadata to language xml file (used in setLocale function)
 ^ Replaced install.png with transparent image - contributed by joomlashack

06-Dec-2005 Alex Kempkens
 ^ Installer to detect languages in correct folders
 ^ Added capability for the installer to install language dependend sample data
 + German Installer translations
 # fixed little issues within the installer

05-Dec-2005 Johan Janssens
 ^ Moved ldap class to connectors directory
 - Removed locale setting from configuration
 + Added JFTP connector class (uses PHP streams) - Contributed by Louis Landry

03-Dec-2005 Andrew Eddie
 + Search by areas

02-Dec-2005 Andy Miller
 # Fixed Admin header layout issues

02-Dec-2005 Johan Janssens
 ^ Moved help files to administrator directory
 + Added JHelp class for easy handling of the help system

01-Dec-2005 Johan Janssens
 + Added JPage class for flexible page head handling
 - Removed favicon configuration setting, system looks in template folder or root
   folder for a favicon.ico file

30-Nov-2005 Emir Sakic
 + Added 404 handling for missing content and components
 + Added 404 handling to SEF for unknown files

30-Nov-2005 Johan Janssens
 # Fixed artf2369 : $mosConfig_lang & $mosConfig_lang_administrator pb
 + Added 'Site if offline' message to mosMainBody
 + Added error.php system template
 + Added login box to offline system template
 - Removed login and logout message functionality

29-Nov-2005 Johan Janssens
 # Fixed artf2361 : Fatal error: Call to a member function triggerEvent()
 ^ Moved offline.php to templates/_system
 ^ Moved template/css to template/_system/css
 - Removed offlinebar.php
 ! Cleanedup index.php and index2.php
 - Removed administrator/popups, moved functionality into respective components

28-Nov-2005 Andy Miller
 + Added RTL code/css to rhuk_milkyway template - Thanks David Gal

28-Nov-2005 Johan Janssens
 - Rmeoved /mambots/content/legacybots.*
 + Deprecated mosMambotHandler class, use JEventHandler instead
 + Added JBotLoader class
 + Added registerEvent and triggerEvent to JApplication class

28-Nov-2005 Andrew Eddie
 ^ All $mosConfig_absolute_path to JPATH_SITE and $mosConfig_live_site to JURL_SITE

27-Nov-2005 Johan Janssens
 # Fixed artf2317 : Installation language file
 # Fixed artf2319 : Spelling error

26-Nov-2005 Emir Sakic
 + Added mambots/system to chmod check array

26-Nov-2005 Johan Janssens
 ^ Changed help server to dropdown in config
 ^ Changed language prefix to eng_GB (accoording to ISO639-2 and ISO 3166)
 ^ Changed language names to English(GB)
 # Fixed artf2285 : Installation fails

24-Nov-2005 Emir Sakic
 # Fixed artf2225 : Email / Print redirects to homepage
 # Fixed artf1705 : Not same URL for same item : duplicate content

23-Nov-2005 Andy Miller
 ^ Admin UI lang tweaks

23-Nov-2005 Johan Janssens
 ^ Added javascript escaping to all alert and confirm output
 # Fixed : Content Finish Publishing & not authorized
 + Added administrator language manager
 - Removed configuration language setting

23-Nov-2005 Samuel moffatt
 + Added structure of JRegistry

22-Nov-2005 Andy Miller
 + Added new MilkyWay template

22-Nov-2005 Marko Schmuck
 # Fixed artf2240 : URL encoding entire frontend?
 # Fixed artf2222 : ampReplace in content.html.php
 # Fixed wrong class call
 + Versioncheck for new_link parameter for mysql_connect.

22-Nov-2005 Johan Janssens
 # Fixed artf2232 : Installation failure

21-Nov-2005 Marko Schmuck
 # Fixed files.php wrong default value

21-Nov-2005 Johan Janssens
 # Fixed artf2216 : Extensions Installer
 # Fixed artf2206 : Registered user only is permitted as manager in the backend

21-Nov-2005 Levis Bisson
 ^ Changed concatenated translation $msg string to sprintf()
 ^ Changed concatenated translation .' '. and ." ". string to sprintf()
 # Fixed artf2103 : Who's online works partly
 # Fixed artf2215 : smtp mail -> PHP fatal

20-Nov-2005 Johan Janssens
 # Fixed artf2196 : Error saving content from back-end
 # Fixed artf2207 : Remember me option -> PHP fatal

20-Nov-2005 Levis Bisson
 # Fixed Artifact artf1967 : displays with an escaped apostrophe in both title and TOC.
 # Fixed Artifact artf2194 : mod_fullmenu - 2 little mistakes

20-Nov-2005 Emir Sakic
 # Hardened SEF against XSS injection of global variable through the _GET array

19-Nov-2005 Samuel Moffatt
 ^ Installer Rewrites (module and template positions)

18-Nov-2005 Andy Miller
 # Installer issues with IE fixed
 ^ Changed Administrator text in admin header to be text and translatable

18-Nov-2005 Johan Janssens
 # Fixed overlib javascript escaping
 ^ Deprecated mosFS class, use JPath, JFile or JFolder instead
 ^ Committed RTL language changes (contributed by David Gal)

18-Nov-2005 Levis Bisson
 + Added fullmenu translation for Status bar

17-Nov-2005 Johan Janssens
 ^ Replaced install.png with new image
 - Reverted artf2139 : admin menu xhtml
 # Fixed artf2170 : logged.php does not show logged in people
 # Fixed artf2175 : Admin main page vanishes when changing list length
 + Added clone function for PHP5 backwards compatibility
 ^ Deprecated database, use JFactory::getDBO or JDatabase::getInstance instead
 + Added database driver support (currently only mysql and mysqli)


17-Nov-2005 Andrew Eddie
 + Support for determining quoted fields in a database table
 + New configuration var for database driver type
 ^ Moved printf and sprintf from JLanguage to JText
 ^ Upgrade phpGACL to latest version (yes!)
 + Added database compatibility methods for ADODB based librarie

16-Nov-2005 Johan Janssens
 ^ Moved language metadata to language xml file
 + Added new JSession class
 ^ Implemented full session support
 ^ Deprecated mosDBTable, use JModel instead

16-Nov-2005 Emir Sakic
 # Optimized SEF query usage
 + Added ImageCheckAdmin compability for previous versions

15-Nov-2005 Levis Bisson
 + Added new language terms in language files
 ^ Deprecated mosWarning, use JWarning instead
 - Removed the left over Global $_LANG in each function

15-Nov-2005 Johan Janssens
 # Fixed artf2122 : Typo in mosGetOS function
 + Added new DS define to shortcut DIRECTORT_SEPERATOR
 + Added new mosHTML::Link, Image and Script function

14-Nov-2005 Andy Miller
 ^ Reimplemented installation with new 'dark' theme

14-Nov-2005 Johan Janssens
 # Fixed artf2102 : Cpanel: logged.php works displays incomplete info.
 # Fixed artf2034 : patTemplate - page.html, et al: wrong namespace
 + UTF-8 modifications to the installation (contributed by David Gal)
 ^ Changed all instances of $_LANG to JText
 - Deprecated mosProfiler, use JProfiler instead

14-Nov-2005 Emir Sakic
 + Added support for SEF without mod_rewrite as mambot parameter

14-Nov-2005 Arno Zijlstra
 # Fixed typo in libraries/joomla/factory.php

13-Nov-2005 Johan Janssens
 ^ Renamed mosConfig_mbf_content to mosConfig_multilingual_support
 # Fixed artf2081 : Contact us: You are not authorized to view this resource.
 ^ Renamed mosLink function to josURL.
 ^ Reverted use of mosConfig_admin_site back to mosConfig_live_site
 ^ Moved includes/domit to libraries/domit
 + Added a JFactory::getXMLParser method to get xml and rss document parsers

13-Nov-2005 Arno Zijlstra
 + Added languagepack info text and button/link to the joomla help site to the finish installation screen
 ! Link needs to change when the specific language help page is ready

12-Nov-2005 Levis Bisson
 ^ Changed from backported Mambo 4.5.3 installation template to the joomla template

12-Nov-2005 Johan Janssens
 ^ Moved includes/Cache to libraries/cache
 - Deprecated mosCache, use JFactory::getCache instead
 + Added improved JCache class
 ^ Moved includes/phpmailer to libraries/phpmailer

11-Nov-2005 Levis Bisson
 ^ Fixed installation - added alert when empty password field
 ^ Wrapped installation static text for translation
 ^ Optimized the installation english.ini file
 # Fixed "GNU Lesser General Public License" link

11-Nov-2005 Johan Janssens
 + Added new JBrowser class
 - Deprecated mosGetOS and mosGetBrowser, use JBrowser instead
 + Added new Visitor Statistics system bot

10-Nov-2005 Johan Janssens
 ^ Installation alterations, backported Mambo 4.5.3 installation
 + Added new JApplication class
 - Deprecated mosMainFrame class, use JApplication instead
 + Introduced JPATH defines, replaced $mosConfig_admin_path by JPATH_ADMINISTRATOR

10-Nov-2005 Andy Miller
 # Fixed IE issues with variable tabs
 ^ Modified the tab code to support variable width tabs (needed for language support)
 ^ Cleaned up and modified some images

10-Nov-2005 Samuel Moffatt
 ^ Installer alterations
 ^ Fixed up a few capitalization issues

09-Nov-2005 Johan Janssens
 # Fixed artf2018 : Admin Menu strings missing
 ^ Moved includes/gacl.class.php and gacl_api_class.php to libraries/phpgacl
 ^ Moved includes/vcard.class.php and feedcreator.class.php to libraries/bitfolge
 ^ Moved includes/class.pdf.php and class.ezpdf.php to libraries/cpdf
 ^ Moved includes/pdf.php to libraries/joomla
 ^ Moved includes/Archive to libraries/archive
 ^ Moved includes/phpInputFilter to libraries/phpinputfilter
 ^ Moved includes/PEAR to libraries/pear
 ^ Moved administrator/includes/pcl to libraries/pcl

08-Nov-2005 Arno Zijlstra
 # Fixed : Notices in sefurlbot

08-Nov-2005 Levis Bisson
 + Added the mambots language files
 ^ Modified some xml mambots files for translation

08-Nov-2005 Johan Janssens
 # Fixed artf2002 : Can't access Site Mambots
 # Fixed artf2003 : Fatal errors - typos in backend

08-Nov-2005 Alex Kempkens
 + Added variable admin path with config vars $mosConfig_admin_path & $mosConfig_admin_site
 ^ changed -hopefully all- administrator references of site or path type to the new variables
 ^ changed config var mbf_content to multilingual_support for future independence

07-Nov-2005 Arno Zijlstra
 # Fixed template css manager

06-Nov-2005 Rey Gigataras
 + Added `Pathway` module, templates now no longer call function directly
 + Added param to `Content SearchBot` allowing you determine whether to search `Content Items`, `Static Content` and `Archived Content`

05-Nov-2005 Rey Gigataras
 ^ Separated newsfeed ability from custom/new module into its own module = `Newsfeed` [mod_rss.php]
   Backward compatability retained for existing custom modules with newsfeed params

04-Nov-2005 Levis Bisson
 + Added the modules frontend and backend language files
 + Wrapped all frontend texts with the new JText::_()
 ^ Optimized the english backend language files

04-Nov-2005 Johan Janssens
 # Fixed artf1949 : Typo in back-end com_config.ini
 # Fixed artf1866 : Alpha1: Content categories don't show

02-Nov-2005 Andrew Eddie
 ^ Reworked ACL ACO's to better align with future requirements

02-Nov-2005 Arno Zijlstra
 ^ Changed footer module
 # Fixed : version include path in joomla installer

02-Nov-2005 Johan Janssens
 + Added XML-RPC support
 # Fixed artf1918 : Edit News Feeds gives error
 # Fixed artf1841 : Problem with E-Mail / Print Icon Links

01-Nov-2005 Arno Zijlstra
 + Added footer module english language file

01-Nov-2005 Johan Janssens
 - Removed global $version, use $_VERSION->getLongVersion() instead.
 ^ Moved includes/version.php to libraries/joomla/version.php
 # Fixed artf1901 : english.com_templates.ini
 + Added artf1895 : Footer as as module

31-Oct-2005 Johan Janssens
 # Fixed : artf1883 : DESCMENUGROUP twice in english.com_menus.ini
 # Fixed : artf1891 : When trying to register a new user get Fatal error.

30-Oct-2005 Rey Gigataras
 ^ Upgraded TinyMCE Compressor [1.02]
 ^ Upgraded TinyMCE [2.0 RC4]

30-Oct-2005 Johan Janssens
 # Fixed artf1878 : english.com_config.ini missing Berlin
 ^ Moved editor/editor.php to libraries/joomla/editor.php
 - Removed editor folder

30-Oct-2005 Levis Bisson
 + Added the new frontend language files (structure)

28-Oct-2005 Samuel Moffatt
 + Library Support Added
 + Added getUserList() and userExists($username) functions to mosUser
 ^ LDAP userbot modified (class moved to libraries)

28-Oct-2005 Johan Janssens
 ^ Changed artf1719 : Don't run initeditor from template

27-Oct-2005 Marko Schmuck
 # Fixed artf1805 : Time Offset problem

27-Oct-2005 Johan Janssens
 # Fixed artf1826 : Typo's in language files
 # Fixed artf1820 : Call to undefined function: mosmainbody()
 # Fixed artf1825 : Can't delete uploaded pic from media manager
 # Fixed artf1818 : Error at "Edit Your Details"
 ^ Moved backtemplate head handling into new mosShowHead_Admin();

27-Oct-2005 Robin Muilwijk
 # Fixed artf1824, fatal error in Private messaging, backend


-------------------- 1.1.0 Alpha Released [26-Oct-2005] ------------------------


26-Oct-2005 Samuel Moffatt
 # Fixed user login where only the first user bot would be checked.
 # Fixed bug where a new database object with the same username, password and host but different database name would kill Joomla!

26-Oct-2005 Levis Bisson
 # Fixed Artifact artf1713 : Hardcoded text in searchbot
 # Fixed selectlist finishing by an Hypen

25-Oct-2005 Johan Janssens
 # Fixed artf1724 : Back end language is not being selected
 # Fixed artf1784 : Back end language selected on each user not working

25-Oct-2005 Emir Sakic
 # Fixed a bug with live_site appended prefix in SEF
 # $mosConfig_mbf_content missing in mosConfig class
 + Added handle buttons to filter box in content admin managers

23-Oct-2005 Johan Janssens
 # Fixed artf1684 : Media manager broken
 # Fixed artf1742 : Can't login in front-end, wrong link
 ^ Artifact artf1413 : My Settings Page: editor selection option

23-Oct-2005 Arno Zijlstra
 + Added reset statistics functions

23-Oct-2005 Andrew Eddie
 ^ Changed globals.php to emulate off mode (fixes many potential security holes)

22-Oct-2005 Emir Sakic
 + Turned SEF in system bot and added the mambot
 - Removed SEF include

21-Oct-2005 Arno Zijlstra
 ^ Changed template css editor. Choose css file to edit.

20-Oct-2005 Levis Bisson
 Applied Feature Requests:
 ^ Artifact artf1206 : Don't show Database Password in admin area
 ^ Artifact artf1301 : Expand content title lengths
 ^ Artifact artf1282 : Easier sorting of static content in creating menu links
 ^ Artifact artf1162 : Remove hardcoding of <<, <, > and >> in pageNavigation.php

19-Oct-2005 Johan Janssens
 + Added full UTF-8 support

18-Oct-2005 Johan Janssens
 + Added RTL compilance changes (submitted by David Gal)

17-Oct-2005 Alex Kempkens
 + Added site url system bot for URL rewrite depending on protocol and domain name

15-Oct-2005 Johan Janssens
 + Added user bot triggers

14-Oct-2005 Levis Bisson
 + Added the choice for the admin language
 + Wrapped all backend static texts
 + Added the english admin language files

14-Oct-2005 Johan Janssens
 + Added userbot group
 + Added Joomla, LDAP and example userbots
 + Added onUserLogin and onUserLogout triggers
 + Added backend language chooser on login page

12-Oct-2005 Andy Miller
 + Added advanced SSL support plus new mosLink method

