<?php
/**
* @version $Id: CHANGELOG.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined( '_VALID_MOS' ) or die( 'Restricted access' );
?>

1. Changelog
---------------
This is a non-exhaustive (but still near complete) changelog for
Joomla! 4.5.x, including beta and release candidate versions.
Our thanks to all those people who've contributed bug reports and
code fixes.

Legend:

# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

--------------------------------------------------------------------------------------------------------------

12-Sep-2005 Rey Gigataras
 # Poll class $this-> bug

10-Aug-2005 Alex Kempkens
 + Winner of template contest; madeyourweb

10-Aug-2005 Andrew Eddie
 # Upgraded phpMailer libs to fix known DOC attack
 # Fixed security vulnerability in registration activation
 ^ Changed admin session name to be based on the live site url (this is unique for a client)
 ^ Changed INSTALL to INSTALL.php to reduce visible element to determine the Joomla! version

09-Aug-2005 Andrew Eddie
 ^ Change instances of 4-spaces to tab (except for libraries that are frequently updated)
 ^ Updated help files

07-Aug-2005 Arno Zijlstra
 - Removed tabs from system info

06-Aug-2005 Johan Janssens
 - Removed tabs from mos_config
 + Added mosSwitcher js component

06-Aug-2005 Tim Broeker
 # fixed bad function cal in mod_related items
 # added required for loop in mod_sections
 ^ fixed pat code in all relevant modules to stop them from looping multiple ul and div elements for each row

05-Aug-2005 Johan Janssens
 ^ Renamed administrator mod_stats to mod_menustats to prevent keyref interferance

04-Aug-2005 Alex Kempkens
 # [#7598] Wrong default value of language used

02-Aug-2005 Alex Kempkens
 # [#6775] - Display of static content without Itemid
 # [#6028] Random Image module; width/height calculation and empty width/height accepted
 # [#6652] Fixed handling of language config for admin; config value stays as it is in admin, frontend is modified to be synchron to $_LANG
 # [#7570] Toolbar icon description not fully translated

02-Aug-2005 Rey Gigataras
 + `Mass Create Users` functionality to User Manager
 ^ upgraded TinyMCE to 1.45
 # Fixed [#7314] Cannot Upload XCF File (The GIMP) Using Media Manager

02-Aug-2005 Andrew Eddie
 + Added proof-of-concept component builder
 + Split modules install screen into install forms and management screen

01-Aug-2005 Alex Kempkens
 # [#6301] All param descriptions are now run thru the translator

 28-Jul-2005 Johan Janssens/Ric Allinson
 + Added new cache manager component

28-Jul-2005 Rey Gigataras
 # Fixed [#7341] bug when help server set to empty
 # Fixed [#7422] Weblink submission emails

27-Jul-2005 Andrew Eddie
 + Added new debug config setting for database and query logging

19-Jul-2005 Arno Zijlstra
 # Fixed package Template
 + Added missing delete button to messages

19-Jul-2005 Andrew Eddie
 # Fixed [#6590] Cannot access user manager anymore

18-Jul-2005 Rey Gigataras
 # Fixed [#7315] 2 suggestions concerning global checkin

16-Jul-2005 Arno Zijlstra
 ^ Added statistics reset actions
 + Added components Related links and Quick tip boxes

16-Jul-2005 Rey Gigataras
 ^ Added publish date to syndicated feeds output - Thanks to gharding
 ^ Added RSS Enclosure support to feedcreator - Thanks to Joseph L. LeBlanc
 ^ Added Google Sitemap support to feedcreator
 # Fixed [#5364] Simple Named Anchors Not Working under IIS
 # Fixed [#5344] Accessibility: Contact component requires JavaScript
 # Fixed [#7255] Frontend Save and Cancel buttons

15-Jul-2005 Andy Miller
 # Miscellaneous UI bug fixes in administrator

15-Jul-2005 Andrew Eddie
 # Added site name to admin session name to allow you to log into more that one local administrator

15-Jul-2005 Johan Janssens
 # Fixed : javascript fixes for mambojavascript.js - Thanks to Rainer Podlas

14-Jul-2005 Johan Janssens/Ric Allinson
 + Major overhaul of caching implementation
 ^ Moved cache creation to mosFactory
 + Added patTemplate caching
 + Added page caching
 ^ Reworked module caching
 + Added Admin Menu caching

14-Jul-2005 Rey Gigataras
 ^ Param tip style change to dashed underline

14-Jun-2005 Alex Kempkens
 + Filelist as parameter function uses attribute directory & filter for selection of file
 ^ mos_category parameter function. Added attribute scope to allow selection of other categories
 ^ contact component to be able to directly link to one category within the menu (parameter)
 + language parameter for content, might be moving to a separate column in future versions

14-Jul-2005 Emir Sakic
 # Fixed [#5952] Joomla! does a 302 redirect not 301

13-Jul-2005 Emir Sakic
 # Fixed Language detection at installation

11-Jun-2005 Alex Kempkens
 # Fixed [#6522] IE - no user login: page just refreshes
 ^ Core templates changed: InitEditor removed, it moved to mosShowHead function!

12-Jul-2005 Rey Gigataras
 # Fixed [#6852] Section or category names with ' doesn't load the java menu at the left
 # Fixed [#6925] Help files: Each Menu Item should have its own specific help file
 # Fixed [#6924] Help files: each module should have its specific Help file
 # Fixed [#6960] Help files: Specific help file for each Mambot

11-Jul-2005 Arno Zijlstra
 ^ Changed userSelect() dropdown to not list registered to avoid a dropdown of 100.000 or more
 # Fixed [#6590] Cannot access user manager anymore
 # Fixed missing alt for banners
 # Fixed [#7133] News submitting with Firefox through frontend

11-Jul-2005 Andrew Eddie
 + Added extra handlers for Sam Moffat's updater code

10-July-2005 Johan Janssens
 - Removed com_trash, moved trash handling to related components (com_content and com_menus)
 ^ Updated Cache Lite library to version 1.5.1

10-July-2005 Alex Kempkens
 ^ Language handling reworked. All language files now within a separate folder, except installation files
 ^ Corrected small issues with the language files
 ^ Corrected language mananger to handle files in separate folders
 ^ Corrected language installer to install files in new directories

10-July-2005 Rey Gigataras
 ^ `Live Bookmarks` control moved to Global Configuration
 # Fixed [#6710] `Link to menu` function from Weblinks-component not working
 # Fixed [#7062] INCORRECT default value in installation SQL script
 # Fixed [#7196] mosRedirect and Input Filter CGI Error
 # Fixed [#6814] com_wrapper Iframe Name tag / relative url modifications
 # Fixed [#6844] rss version is wrong in the feeds

08-July-2005 Rey Gigataras
 ^ Increased Title column for `mos_content`, `mos_categories` & `mos_section` tables to varchar(100)
 # Fixed [#6668] No static content edit icon for frontend logged in author

07-July-2005 Rey Gigataras
 # Fixed [#6402] Can't demote a super adminstrator
 # Fixed [#6669] mosmail BCC not working, send as CC
 # Fixed [#7120] Articles with `publish_up` today after noon are shown with status `pending`
 # Fixed [#7011] Subtle bug in saveUser() - admin.users.php
 # Fixed [#7161] Apparently unncessary code in sendNewPass - registration.php

30-Jun-2005 Johan Janssens
 ^ Speed Improvements : Added caching support to administrator menu.

29-Jun-2005 Johan Janssens
 + Implemented component buffering in administrator
 ^ Speed improvements : Editor only loads itself after a call to editorArea

28-Jun-2005 Andrew Eddie
 ^ Made allowance for module based language files (auto-loaded)

25-Jun-2005 Andrew Eddie
 # Fixed bug in mossef mambot where regex was case sensitive

22-Jun-2005 Andrew Eddie
 + Added getNullDate method to database class

22-Jun_2005 Arno Zijlstra
 # Fixed frontend logout redirect

21-Jun-2005 Arno Zijlstra
 -> Changed site offline check. Superadministrator, administrator and manager will see the site if logged in at front

20-Jun-2005 Andrew Eddie
 + Added Sef patTemplate function

20-June-2005 Rey Gigataras
 ^ Converted all frontend Module output to patTemplates [except for mod_mainmenu]

18-June-2005 Johan Janssens
 + Implemented javascript command pattern for admin popup handling

17-June-2005 Johan Janssens
 + Implemented javascript command pattern for admin task handling
 ^ Removed all hardcoded javascript from menubar.html.php

17-June-2005 Rey Gigataras
 # Fixed SQL [#6767] Couple errors in agent_browser.php

17-June-2005 Andy Miller
 + Added RTL support based on work by David Gal

15-Jun-2005 Andrew Eddie
 # Fixed SQL injection via voting form
 # Fixed uninitialised variables

15-Jun-2005 Andy Miller
 ^ Updated SSL handling to be more robust, with per/menu SSL options - Thanks to Louis Landry

14-Jun-2005 Andrew Eddie
 # Fixed bug in content caching

13-Jun-2005 Andrew Eddie
 + Added link to site to admin login screen
 + Added latest version of jscalendar (need to keep the old one for bwc as the API changed)
 + addToHead Joomla! macro in patTemplate
 # Added missing parent_id var to mosCategory class
 ^ Moved calendar tmpl's into separate file
 ^ Parameters files can now use <mosparams> as root tag

11-Jun-2005 Emir Sakic
 # Fixed search array bug
 + Added parameter for search results per page

11-Jun-2005 Johan Janssens
 + Create javascript behavior framework
 + Added toolbar behavior prototype

10-Jun-2005 Rey Gigataras
 + Global Config param allowing control of format of SEO Page Title
 + Meta Keywords & Description for menu items - via params
 + SEO Page Title for menu items - via params
 + Ability to stop Blog pages (incl frontpage compt) from inheriting meta keywords & descriptions from content items

09-Jun-2005 Rey Gigataras
 - toolbar.{componentname}.html.php files depreciated

07-Jun-2005 Rey Gigataras
 ^ Media Manager enhancements
 - depreciated /administrator/components/com_media/images directory, images moved into admin images directory

07-Jun-2005 Andrew Eddie
 ^ Updated ADODB Libraries
 - Removed instances of MD5 SQL calls (use PHP instead) for better X-DB support
 ^ Updated test xml schema to version 0.3 (CVS)
 ^ Installer uses more DataDictionary routines for X-DB support
 + Added xml schema option to install
 ^ Changed limit handling in SQL calls
 + Added config var to denote the 'zero' date for the db platform

06-Jun-2005 Rey Gigataras
 + {moemailcloak=off} tag to allow disabling of mosemailcloak mambot for specific content items
 + Global Config Param allowing Frontend editing to open larger uncluttered popup window
 + MOSimage now can be assigned url link

05-Jun-2005 Rey Gigataras
 + Global Param to enable/disable user ability to change their Name from Frontend
 + Individual `Checked Out` lock icons, now actionable, allowing individual item checkin
 ^ Group Access information when frontend editing now placed within overlib popup.
 # Fixed Active Menu highlighting for Link - Url
 - /administrator/popups/contentwindow.php depreciated, functionality moved into com_content files
 - /administrator/popups/modulewindow.php depreciated, functionality moved into com_modules files
 - /administrator/popups/pollwindow.php depreciated, functionality moved into com_polls files
 - /administrator/popups/uploadimage.php depreciated, functionality moved into com_media files
 - /administrator/popups directory depreciated

03-Jun-2005 Rey Gigataras
 + `Changelog` link in Help Page

03-Jun-2005 Andrew Eddie
 ^ Improvements to database::setQuery method thanks to thede & David McKinnis
 # Fixed [#6574] Problem w/ mosDBTable::getError() in w/ Contact component

02-Jun-2005 Andrew Eddie
 ^ Moved Content-Type directive directly after <head>

02-Jun-2005 Emir Sakic
 ^ Paged search results
 # Fixed default and assign bugs in template manager
 + Append http:// to authorURL without it while reading XML

02-Jun-2005 Rey Gigataras
 + `Static Content` can now be assigned to `Frontpage` Component

01-Jun-2005 Emir Sakic
 ^ Changed search component to use sessions instead of POST to avoid "Page has expired" problem

01-Jun-2005 Andrew Eddie
 # Fixed [#5756] Database::loadAssocList($key='')

31-May-2005 Andrew Eddie
 ^ Updated Template Manager
 ^ Updated Language Manager
 + New TODO file to communicate state of the CVS to devs and testers

30-May-2005 Rey Gigataras
 ^ Layout change for `Image` tab when editing content
 ^ Changed 'Published and is Current' image to tick.png

29-May-2005 Rey Gigataras
 + template print.php file for use when printing
 + New JS Tree view added to 'Module' & 'Mambot' Managers
 + New GC Param show/hide toolbar icon text
 # Fixed [#6285] User management: user deletion
 # Fixed [#6450] Step 5 should not show previous

28-May-2005 Rey Gigataras
 + New JS Tree Menu Structure view added to 'Menu' Managers
 ^ Admin pathway unpublished by default

28-May-2005 Arno Zijlstra
 ^ updated export, language and template tree's to match content tree

27-May-2005 Rey Gigataras
 ^ hidemainmenu function now shows the toplevel of JS menu with links deactivated

27-May-2005 Andrew Eddie
 - Deleted editHTML, editCSS, assign and makeDefault toolbar functions (specific to templates)
 ^ moved mosAdminMenu::MenuLinks to mosMenuFactory::buildMenuLinks
 ^ moved mosAdminMenu::Parent to mosMenuFactory::buildParentList
 ^ moved mosAdminMenu::Ordering to mosMenuFactory::buildOrderingList
 ^ moved mosAdminMenu::menutype to mosMenuFactory::getMenuTypes
 ^ moved mosAdminMenu::MenuSelect to mosContentFactory::buildMenuSelect
 ^ moved mosAdminMenu::Links2Menu to mosContentFactory::buildLinksToMenu
 ^ moved mosAdminMenu::SelectSection to mosContentFactory::buildSelectSection
 ^ moved mosAdminMenu::Category to mosContentFactory::buildCategoryList
 ^ moved mosAdminMenu::Section to mosContentFactory::buildSectionList
 ^ moved mosAdminMenu::Component to mosComponentFactory::buildList
 ^ moved mosAdminMenu::ComponentName to mosComponentFactory::buildNameList
 ^ moved mosAdminMenu::ComponentCategory to mosComponentFactory::buildCategoryList
 ^ moved mosCommonHTML::menuLinksSecCat to mosMenuFactory::buildLinksSecCat
 ^ moved mosCommonHTML::ContentLegend to mosContentFactory::buildContentLegend
 ^ moved mosCommonHTML::menuLinksContent to mosContentFactory::buildMenuLinks
 ^ moved mosHTML::cleanText to com_rss
 ^ moved mosAdminMenus::saveOrderIcon to mosAdminHTML::saveOrderIcon
 ^ moved mosAdminMenus::UserSelect to mosAdminHTML::userSelect
 ^ moved mosAdminMenus::ImageCheckAdmin to mosAdminHTML::imageCheck
 ^ moved mosAdminMenus::SelectState to mosAdminHTML::stateList
 ^ moved mosCommonHTML::PublishedProcessing to mosAdminHTML::publishProcessing
 ^ moved mosCommonHTML::AccessProcessing to mosAdminHTML::accessProcessing
 ^ moved mosCommonHTML::CheckedOutProcessing to mosAdminHTML::checkedOutProcessing
 ^ moved mosCommonHTML::checkedOut to mosAdminHTML::checkedOut
 - removed mosAdminMenu::menuItem

25-May-2005 Emir Sakic
 ^ Added open folder icon appearance when category selected in content admin dtree view

24-May-2005 Andy Miller
 # Fixed some rendering issues with new Content Item Manager

24-May-2005 Rey Gigataras
 + New JS Tree Content Structure view added to 'Content Items' & 'Static Content' Managers

23-May-2005 Andrew Eddie
 ^ Reworked permission conventions to align with future models

21-May-2005 Rey Gigataras
 ^ `Link to Menu` functionality now available when creating `New` Sections or Categories
 # Fixed [#6230] `noscript` tag of SpamBot and W3C
 # Fixed [#6029] Content erased when clicking on 'Link to Menu'

20-May-2005 Rey Gigataras
 + New Admin module `mod_logoutbutton`
 ^ more template code shifted in mosShowHead & mosShowHead_Admin
 # Fixed [#6245] usermenu link `logout` doesnt work

19-May-2005 Alex Kempkens
 ^ corrected little templating/translation issue in installation
 + key's for preinstall check into language files

16-May-2005 Andy Miller
 ^ Added css element to SF2 template to stop scrollbars showing/vanishing

16-May-2005 Andrew Eddie
 + Added Linkbar function (similar to Toolbar)

16-May-2005 Rey Gigataras
 + Added 'New Content Item' button to mod_quickicon
 + New Admin & Site module `mod_footer`
 # Fixed [#4950] No admin if table prefix removed in install
 # Fixed [#5986] mosimage problems
 # Fixed [#6077] com_search doesn't use the label tag

15-May-2005 Rey Gigataras
 ^ Admin templates modified to load module positions, rather than the modules directly

15-May-2005 Emir Sakic
 # Fixed [#5919] SEF not returning &amp; back, RSS feeds do not produce SEF URLs
 # Fixed language include in admin popup upload
 # Fixed undefined option in auth.php
 ^ Excluded CVS directories from the folder listing fnction
 + Admin upload popup extended with ability to upload to sub-directories

14-May-2005 Rey Gigataras
 ^ Added `/mambots/editors` directory in list of directories that need to be writeable

13-May-2005 Andrew Eddie
 + Added AXMLS filter to com_export
 ^ Hack to ADODB library to extract xml schema for selected tables

11-May-2005 Emir Sakic
 # Notice for mailer list in global config admin
 # Fixed JS error in trash button

10-May-2005 Andy Miller
 ^ Changed default install chmod perms to 0664 and 0775
 - Removed version number from login header

10-May-2005 Rey Gigataras
 + Mambots now parse `Weblinks` Descrition and ` Weblinks Category` Description
 + Mambots now parse `Section` and `Category` Description
 ^ mospage.btn editor-xtd mambot is only loaded for specific components
 # Fixed [#6123] mosimage and pagebreak buttons showing on unneeded screens
 # Fixed [#6127] admin - mambots - new
 # Fixed [#6121] Installer should check input
 # Fixed [#6126] new cpanel admin module shows no content
 # Fixed [#6113] Tooltips
 # Fixed [#6117] mainmenu tooltip bug
 # Fixed [#6119] Site Module - New - Save
 # Fixed [#6122] GeSHi description
 # Fixed [#6130] Messages - inbox and config screens -warning
 # Fixed [#6110] admin.contact.html.php does not parse

09-May-2005 Alex Kempkens
 + Language directory checks
 ^ Proof reading german installation language files (thx to mambogtt team)
 # Fixed [#6102] unneeded parameter in class function call
 # Fixed wrong method call in class.inputfilter
 + Added language handling bot and moved language default detection to it
 + Added optional attributes to mosHTML::makeOption in order to allow option with different value/text attributes
 # Incompatibility problems with the multi lingual queries for categories & sections
 + Added parameter for advanced search link in search module
 ^ Rearranged the configuration tabs; contribution to the locale settings in the language files

09-May-2005 Rey Gigataras
 + Ability to edit Category information in Frontend
 + New `State` dropdown added to all Admin table view pages
 + New Site and Admin Module allowing the display of an RSS feed
 ^ * Custom Module * no longer has ability to display RSS feeds
 # Fixed [#6095] TinyMCE and SWFs

09-May-2005 Andrew Eddie
 ^ Upgraded phpGACL library

08-May-2005 Rey Gigataras
 ^ `New` button in 'Module Manager' now leads to list of Modules you can create

06-May-2005 Alex Kempkens
 ^ Language selection for the administrator - adapted new design
 ^ User parameter settings to admin language, moved language list to mosParameter

05-May-2005 Emir Sakic
 + Added Swedish language file for installation (thanks Sune Hulltman)

05-May-2005 Andrew Eddie
 # Hardened mosRedirect against malicious injection
 # Hardened array of ids (eg, $cid) against malicious injection
 - Removed redundant file /includes/getids.php
 # Fixed bug in admin sections (incorrect section)

04-May-2005 Emir Sakic
 + Added browser language detection for installer

03-May-2005 Andrew Eddie
 ^ Moved visitor stats detection to a mambot
 ^ Consolidated login functions and session handling
 # Prevent attacks via injection of POST variables through GET
 # Fix injection bugs in various class 'check' methods
 + Added input filter class (replacement for built-in strip tags)
 + Added toString method to mosDBTable class
 + Added permissions column to export and language files lists
 - Removed $version
 + Added long and short version methods to mamboVersion class

03-May-2005 Rey Gigataras
 ^ Added Register Date column to User Manager
 ^ User Manager now uses DATE_FORMAT_LC & DATE_FORMAT_LC2 for date display
 # Fixed Admin created users not listing a Register Date
 # Fixed [#5994] Strange Last Visit Date
 # Fixed [#5993] Table - Static Content (no $_LANG) + absent from english.com_menus.ini
 # Fixed [#5988] Menu bugs
 # Fixed [#5987] Warning on main menu
 # Fixed [#5989] Missing pre tag?

03-May-2005 Andrew Eddie
 + New mosFactory class
 ^ Moved patTemplate creation to mosFactory
 ^ Bolstered protection against session id spoofing attempts
 ^ Change com_massmail to patTemplate format

02-May-2005 Alex Kempkens
 + Language selection for the Administrator
 + Language real name logic for installation and administrator incl. caching
 + LanguageFactory and changed all loadLanguage calls to the factory

30-April-2005 Emir Sakic
 # Fixed CHMOD settings bug in installer
 # pass-by-reference warning in export admin component
 # Access to the language installer

29-April-2005 Rey Gigataras
 + New 'Column Ordering' ability for all Admin tables
 # Fixed [#5942] CHMOD needed for new Export db functionality
 # Fixed [#5925] Save content in Frontend does not display content anymore

29-April-2005 Andrew Eddie
 + New Export Manager (aka Backup)
 ! Update ADODB to 4.62

28-April-2005 Rey Gigataras
 ^ rewording of GPL License understanding in Installation steps

28-April-2005 Johan Janssens
 # Added user management and session events
 # Added example.userbot to demonstrate implementation of user events

28-April-2005 Andrew Eddie
 ^ mosAbstractTasker partially self aware (public methods are set as tasks)
 ^ Added ACL checking to performTask method of mosAbstractTasker
 ^ Changed metrics for language acl
 ^ Moved com_login and com_search xml files to site folders (removed admin placeholders)
 ^ moved page navigation template into it's own template file
 ^ updated global checkin to allow for selection of items to checkin

27-April-2005 Rey Gigataras
 # Fixed [#5068] Category name.: newline after add, no newline after edit
 # Fixed [#5863] Typewriter apostrophy causing javascript error + error in php
 # Fixed [#5909] Error: Call to undefined function when click on a Section link from an article

27-April-2005 Andrew Eddie
 + Added ldap authentication
 + Added lang attribute to template HTML elements in templates

26-April-2005 Rey Gigataras
 # Fixed [#5877] Delete main menu
 # Fixed [#5876] Media Manager Error Msg

24-April-2005 Emir Sakic
 + Added Filter submit button to all content and component admins
 + Added JS warning for using too high session lifetime values in config admin

25-April-2005 Rey Gigataras
 + Re-added 'System Info' Admin menu option
 # Fixed [#5005] Using class=`componentheading` in the backend should be forbidden
 # Fixed [#5199] RSS 'Live Bookmark' Feed doesn't work correctly
 # Fixed [#5233] Static content not filtered through htmlentities()
 # Fixed [#5098] Apostrophe ( ' ) shows as &amp;
 # Fixed [#3340] administrator com_user filter box does not handle apostrophy properly

24-April-2005 Rey Gigataras
 ^ Global Param $mosConfig_multipage_toc depreciated
 + New mosErrorAlert function to handle all JS Error alerts
 # Fixed [#4826] xml parser and overlib conflict when dealing with apostrophes
 # Fixed [#5201] Problems with background colours when using some templates/colour scheme
 # Fixed [#4801] Succesful message after editing Mambot name has problem with apostrophes
 # Fixed [#4778] overlib problems with apostrophes
 # Fixed [#4683] Pathway XHTML compliance hack destroys correctly defined entities
 # Fixed [#5706] Frontend Filtering does not hold
 # Fixed [#5050] loadmodulepositions does not respect module class suffix
 # Fixed [#4959] Content component JS alert problem
 # Fixed [#4963] checkout function uses the wrong date format
 # Fixed [#5842] super admin can disable himself being only one in super administrators group
 # Fixed [#5846] Custom Module has lost RSS Parameters
 # Fixed [#5491] Mospagebreak bug on 4.5.2.1 (after upgrading from 4.5 (1.0.9))
 # Fixed [#5570] Explicit ampersands in URI's causing XHTML non-compliance

24-April-2005 Emir Sakic
 # Fixed [#4849] Search does not work if content contains HTML entities
 # Fixed Language Manager menu
 # Fixed the mosLoadLanguage call in administrator/popups/uploadimage.php
 # Corrected an error in MOS file handling on windows

23-April-2005 Arno Zijlstra
 ^ Converted sample_data.sql to sample_data.xml

22-April-2005 Arno Zijlstra
 ^ Updated dutch installation language

22-April-2005 Rey Gigataras
 + Add GC Param to show/hide User Params on frontend
 # Fixed [#4571] Favicon Configuration
 # Fixed [#5196] Admin interface Favicon incorrect
 # Fixed [#5326] menu copy wrongly includes trash items

22-April-2005 Emir Sakic
 # Fixed [#2846] Archive module to find out Itemid
 + Added Filter button in admin menu manager for proper submit
 # Fixed [#4474] 4.5.2 CVS Menu Manager [menu] (Menu items list) Filter function

22-April-2005 Andrew Eddie
 + New isCheckedOut method in mosDBTable class
 ^ Added configurable name to select methods in mosAdminMenus class

21-April-2005 Rey Gigataras
 # Fixed [#4923] Links pathway not showing up when accessed by TOP MENU
 # Fixed [#4621] {mosimage} not working correctly when Intro Text is set to Hide
 # Fixed [#4644] mosLoadModules-Parameter &`-` doesn't work with module-type `user`
 # Fixed [#5264] Contact Component: 3 PHP5 errors
 # Fixed [#5453] Problem showing pathway
 # Fixed [#4636] Pathway mangles ampersands &
 # Fixed [#5798] JS error with begin editing any Static Content item in backend
 # Fixed [#5797] JS error on ToolTip in MainMenu Parameter 'Activate parent:'
 # Fixed [#5799] Old image header_blue.jpg in Joomla! 4.5.3 installer
 # Fixed [#5796] wrong _NUM_VOTERSAN on com_poll
 # Fixed [#5677] https / http automatic site switchover does not work
 # Fixed [#5266] Bug in index2.php logic could cause admin login loops
 # Fixed [#5554] Accessibility: e-mail form doesn't work with JS disabled
 # Fixed [#5500] Frontpage incorrectly displays Pathway
 # Fixed [#5126] Pathway show main menu home > home for the frontpage
 # Fixed [#4998] PHP errors - unchecked variables which must not be set
 # Fixed [#4706] closing table row </tr> not drawn when intro items is less than # of columns
 # Fixed [#5094] component poll: database problem

21-April-2005 Andrew Eddie
 + Added ability for template to have their own version of tabs graphics

20-April-2005 Rey Gigataras
 ^ Upgraded Overlib to 4.17
 ^ Upgraded TinyMCE Editor to 1.44RC1
 # Fixed [#5694] mod_newsflash: 'random'-style not working
 # Fixed [#5645] Dangerous use of eregi
 # Fixed [#5553] Accessibility: PDF, Print and E-Mail icons require JavaScript
 # Fixed [#5137] mos_core_acl_aro and mos_core_acl_group_aro_map arent updated
 # Fixed [#5325] Suggestion concerning all Menu Items
 # Fixed [#5637] Login/Logout Redirection URL not working
 # Fixed [#5458] Who's online module (one english error)
 # Fixed [#5466] small error in style generation code in mambots/content/mosimage.php
 # Fixed [#5456] XHTML compliance error: img 'name' attribute not unique

20-April-2005 Alex Kempkens
 + Added a activate parent parameter for mod_mainmenu

20-April-2005 Andrew Eddie
 + Added installer options to overwrite and backup files

16-april-2005 L�vis Bisson
 ^ Update for the new admin language filelong strings standards
   Almost all admin files using language files were updated
 ^ Prepared the french admin language files for translation

13-April-2005 Emir Sakic
 # Fixed [#5622] Using PHP short tag in vcard class

11-April-2005 Alex Kempkens
 ^ Moved language defines to new format:
   com_poll, com_registration, com_search, metadata, com_banners, com_login, com_weblinks,
   com_newfeeds, mod_whos_online, mod_stats, admin.menus, com_users
 # little handling problems with language loading
 ^ ISO define to exist without the charset - corrected all _ISO uses

10-April-2005 Alex Kempkens
 ^ Moved language defines content component (till line 285) to new format

08-April-2005 Alex Kempkens
 ^ Moved language defines (till line 189) to new format

06-April-2005 Andrew Eddie
 + Added packaging function to langauge manager

05-April-2005 Emir Sakic
 # Fixed [#3149] Error when creating Joomla! for search

05-April-2005 Andrew Eddie
 + Added XML file editor to Language Manager

04-April-2005 Andrew Eddie
 + Fixed bug on new upgrade that sets dbtype to mssql
 + Added experimental XML-RPC server
 + Added parameter to users to personalise the editor they use

01-April-2005 Johan Janssens
 # Fixed [#5197] Meta Tags have one or more stray commas when no page specific Meta Data entered

01-April-2005 Andrew Eddie
 + Language configuration added to 'your detail' frontend
 ^ Reworked mainframe::getPath to overcome com_user vs com_users problem

31-Mar-2005 Andrew Eddie
 + new parameter for setting the user language

30-Mar-2005 Rey Gigataras
 ^ Module Position ordering on Postion page and module position dropdown alphabetically ordered

30-Mar-2005 Johan Janssens
 # Fixed [#4799] Ugly margins around administrative interface with Opera browser
 # Fixed [#5121] JS-bug in backend (modules-&gt;menu item)
 # Fixed [#5220] 4.5.3 CVS TinyMCE does not accept relative paths for images or files links

29-Mar-2005 Andrew Eddie
 + new patTemplate modifier to allow variables to be translated

28-Mar-2005 Rey Gigataras
 # Fixed lack of closing php ?> tag in agent_browser.php & agent_os.php

27-Mar-2005 Andy Stewart
 # Fixed [#5007] Needed to add "/images/stories" tp HTML_Media::num_files call
 # Fixed num_files in media manager to ignore ".." and "index.html" when counting.

26-Mar-2005 Rey Gigataras
 # Fixed [#4692] 4.5.2.1 Wrapper Module/Link Target parameter Suggestion
 # Fixed [#4768] emailCloaking() doesn't completely combine parts of mail address
 # Fixed [#4972] Truncated email address in Contacts
 # Fixed incorrect From email in Email a Friend function

25-Mar-2005 Alex Kempkens
 ^ Moved language defines (till line 70) to new format
 ^ Extracted the components out of the frontend language
 # Fixed notify in globals.php

24-Mar-2005 Andy Miller
 + Added `columnpad` class to td of any column > 1 in blog layout

20-Mar-2005 Rey Gigataras
 + Added `Apply` button to frontend Content & Section editing
 # Fixed [#5205] Page Title not shown on Search Page

20-Mar-2005 Emir Sakic
 # Fixed CHMOD stuff in installer template
 # Fixed a bug when displaying admin templates
 # Fixed a JS bug in static content admin

19-Mar-2005 Rey Gigataras
 + Add param support for TinyMCE advanced plugins
 ^ Upgraded JSCook JS Menu to 1.31
 ^ Upgraded TinyMCE Editor to 1.43

18-Mar-2005 Rey Gigataras
 ^ Added Mosimage Caption support to Frontend editing
 # Fixed [#4754] login module: missing CSS-style to text-links to change style in CSS
 # Fixed [#4590] New Menu `Table Content Section` Parameters bugs
 # Fixed [#4823] Saving a category without title loses all the changes
 # Fixed [#4604] No img title tags for buttons
 # Fixed [#4701] Wrapper Auto-Height Sticks to First Wrapped Page Height
 # Fixed [#4688] `E-Mail this page` popup displays incorrectly
 # Fixed [#4634] User groups not recognized
 # Fixed [#4565] Static Content on Frontpage
 # Fixed [#4470] Nobody with at least Author access may publish in an empty category
 # Fixed [#4729] mainmenu bug
 # Fixed [#4473] 4.5.2 CVS Clean cache does not erase rss...xml
 # Fixed [#4885] Hardcoded charset in Feed creator class
 # Fixed [#5003] No item selected - 1 item sent to trash
 # Fixed [#5028] getItemid() does not support `Table - Content Category` menu type

17-Mar-2005 Rey Gigataras
 # Fixed [#5166] Randomness in Newsflashes
 # Fixed [#5066] List - Content Section error
 # Fixed [#5060] mosimage/ witdth-attribute in HTML code

17-Mar-2005 Andrew Eddie
 ! Split admin language files into common and component parts

16-Mar-2005 Andrew Eddie
 + Added support for restricting searches areas
 + Added parameter to optionally show the google search from search results

13-Mar-2005 Alex Kempkens
 + Frontend-Language: introduced INI file format for frontend language files
 ^ Integrated backward compatibility for defines in language class translation function
 ^ Changed first common defines to language class calls
 # [#4956] mosBindArrayToObject internal var handling

9-Mar-2005 Andrew Eddie
 # Fixed bug in toolbar help button ignoring component help files if help url set

7-Mar-2005 Rey Gigataras
 + Global Param to set character length for Username
 + Global Param to set character length for Password
 + Global Param to enable/disable user ability to change their Username from Frontend
 ^ User Details page modified to use `save` and `cancel` buttons
 # Fixed [#4933] Saving admin template CSS redirect error
 # Fixed [#4968] Category `checked out` info is empty when an item is being checked out
 # Fixed [#4980] checked out and publish info tip don't use the user defined local
 # Fixed [#4949] Various Bugs and suggestions for Installation Screens
 # Fixed [#4955] Choice for Icons in Global Config
 # Fixed [#4960] Overlib not loaded in edit weblink page

6-Mar-2005 Alex Kempkens
 + Added german translation for installation
 + Added JavaScript and error messages to the installation language files
 ^ Some translation handling in patTemplate integration
 # [#4951] Contact: Edit - Label typo
 # [#4954] overlib lang error

5-Mar-2005 Rey Gigataras
 # Fixed [#4786] Contact Manager missing new ordering facility

4-Mar-2005 Rey Gigataras
 # Fixed spacing issue with Control Panel Quick Icons
 # Fixed [#4844] Poll allowed to save without options
 # Fixed [#4858] Missing JS for publish and unpublish buttons in Contact List view
 # Fixed [#4798] saving content item in frontend, redirects to index.php not to content item
 # Fixed [#4863] vCard address incomplete
 # Fixed [#4868] Contact Manager: New/Edit screen, useless Menu Image Parameter
 # Fixed [#4794] Hardcoded component name : Syndicate
 # Fixed [#4642] Can't login to ADMINISTATION


3-Mar-2005 Andrew Eddie
 # Fixed 4.1.x compatibility for ob_flush function
 ! Placed commented time stamp on the index page behind the debug setting
 # Fixed [#4908] GeSHI mambot does not process &amp; correctly

3-Mar-2005 Levis Bisson
 # Fixed the mosLoadLanguage call in administrator/popups/contentwindow.php

3-Mar-2005 Rey Gigataras
 # Fixed [#4884] One parameter too many in orderModule call in admin.modules.php
 # Fixed [#4883] Mamboxml: tooltips need to use label instead of name

1-Mar-2005 Levis Bisson
 + Added the backend translation for the Administrator
 ^ Change the text embedded in the php files to wrap it in a tranlsation function

28-Feb-2005 Andy Stewart
 # Fixes [#4769] Corrected "Table - Content Section" heading

27-Feb-2005 Rey Gigataras
 + Ability to edit Section information in Frontend

26-Feb-2005 Rey Gigataras
 + Global Config param allowing setting of Usertype of new users
 + Added `Apply` button to 'Edit HTML' & 'Edit CSS' in 'Template Manager'
 ^ New `User` Tab for Global Configuration
 # Fixed [#4474] Menu Manager [menu] (Menu items list) Filter function

25-Feb-2005 Rey Gigataras
 ^ MOdified layout of Edit Contact Info
 ^ modified remaining calls to overlib and calendar to use function call
 + New menu item `Table - Static Content`
 # Fixed `Search Content Category` handling for `List - Content Sections`

24-Feb-2005 Emir Sakic
 + Added bosnian installation language
 # Removed duplicate strings and added forgotten ones in installation lang
 # Corrected JS bug on select database on installation

23-Feb-2005 Rey Gigataras
 + Added option to change Banner `alt` text
 + Added `Apply` button to Banners, Contacts, Newsfeeds, Polls & Weblinks
 # Fixed [#4693] Vcard privacy issues and another in contacts.php

23-Feb-2005 Andrew Eddie
 - Deprecated mosMainframe::_setConfig method and associated support property
 ^ Altered mosMainframe::getConfig to return value based on variable name
 ^ Simplified the internal configuration edit page
 + Added PHP5 compatibility functions

22-Feb-2005 Rey Gigataras
 + xml Module Description overlib in `Module Manager` page
 + xml Mambot Description overlib in `Mambot Manager` page
 ^ Upgraded Overlib to 4.14
 ^ `User` modules now referred to as `Custom Modules`
 # Fixed [#4543] Minor bug with overlib
 # Fixed [#4638] hide mosimage capability for selected `pages` not working
 # Fixed [#4641] Overlib and typo error in`Edit Menu Item:: Blog - Content Category`
 # Fixed [#4635] Joomla! 4.5.2.1 Newsfeed component generates image tag instead of img tag
 # Fixed [#4648] 4.5.2.1 Unable to filter [user] module in Module Manager [site]
 # Fixed [#4544] Mambots - `Site Mambots` Pagenav not working

22-Feb-2005 Andrew Eddie
 + Added global-killer variant of globals.php
 + Added ADODB libary
 ^ File functions groups into separate include called mambo.files.php
 + Language handling classes added from 5.0 tree
 + Added translation string trawler to admin language component
 + Added translation ability to installation
 ^ Refurbish installation code

20-Feb-2005 Rey Gigataras
 ! removed installers references in separate `Sub-Menus`
 # Fixed [#4619] Page Title properties missing in backend
 # Fixed [#4586] `List Length` is ignored
 # Fixed [#4605] Typo: in tool tip Global configuration `require unique email`
 # Fixed [#4607] admin.typedcontent.php missing $lists['_caption_align'] initialisation code
 # Fixed [#4610] MOSimage doesn't work in static content manager
 # Fixed [#4598] Error in admin.contact.html.php
 # Fixed [#4593] Problem with displaying all the components in list
 # Fixed [#4589] Parameter show/hide vcard in contact item not available in backend

19-Feb-2005 Andrew Eddie
 # Fixed security vulnerability in Tar.php

18-Feb-2005 Andy Miller
 # Fixed com_content to use <div> for componentheading
 ^ Changed implementation of new -3 module style for more robust solution


=============================================================================================================


2. Copyright and disclaimer
---------------------------
This application is opensource software released under the GPL.  Please
see source code and the LICENSE file


3. Whats New to 4.5.3 Summary
---------------------------

------------------------------------------------------------------
>>> Developer Info <<<

globals.php-off

This file is a variant of the existing globals.php but instead of emulating register_globals=on,
it emulates register_globals=off.  While the setting of off is certainly not a cure all for all
malicous attacks, it can prevent some.  To use this file, rename your existing globls.php file
to globals.php-on and rename globals.php-off to globals.php  The reason this change is optional
is because some 3rd party components will fail with register_globals=off.  Developers are
encouraged to develop with the new file to ensure that your works are compatible with future
versions of Mambo.

------------------------------------------------------------------
>>> Developer Info <<<

+ ADODB

ADODB has been added as an abstraction layer to initially support MySQL 4.1+ on PHP5+ but
also serves as the foundation for future porting to other database platforms.

------------------------------------------------------------------

Installation

Translations have been added to the installation process.  The first screen/step
selects  allows the user to select from an included language and proceed with the
installation is their desired language.

The installation code has also been completely refurbished with the presentation
layer converted to patTemplate.

------------------------------------------------------------------

+ Administator Translation

Translation of the Administrator is performed with a new language handling class
that uses ini format files as it's source.

------------------------------------------------------------------

+ Language Trawler

This function search for instances of $_LANG->_('Some Text') in PHP files and
<mos:Translate>Some text</mos:Translate> in html template files.  If facilitates
the creation of the raw english language files.

------------------------------------------------------------------
>>> Developer Info <<<

^ Changes to configuration

The way that internal configuration variables are maintained has been simplified.
The _config array has been removed from the mainframe class.  Instead, the getCfg
method now simply looks for a global variable like mosConfig_$varname.

The configuration edit page has also be rationalised.  The _alias property of the
mosConfig class has been dropped in leiu of syncronising the property names with
the global variable names with the exception that the mosConfig_ prefix is replaced
simply by config_.  This means that to add a new global variable you add a new
class property to mosConfig and then your associated code in the component.

------------------------------------------------------------------
>>> Developer Info <<<

^ PHP 5 Compatibility

file_put_contents is now available regardless of the PHP version.

------------------------------------------------------------------

 + xml Module Description overlib in `Module Manager` page
 + xml Mambot Description overlib in `Mambot Manager` page

Tooltip descriptions added to `Module Manager` & `Mambot Manager` pulled directly from the xml description of the module/mambot

------------------------------------------------------------------

 + Added option to change Banner `alt` text

There is a new option in the Banner Manager to set the alt text for Banner images


------------------------------------------------------------------

 + New menu item `Table - Static Content`

New "Table View" that displays Static Content.
This new table also allows you to show `Static content`, that is not linked to a Menu via a `Link - Static Content`.  This gives added flexibility to `Static Content`.
In fact this view allows you to select whether to to show `Static Content` linked to menus only, or `Static Content` not linked to menus or both.

------------------------------------------------------------------

 ^ Modified layout of Edit Contact Info

Changed layout of Edit Contact Info in Admin Backend.
Removed tabs to make information fully visible on page


------------------------------------------------------------------

 ^ New `User` Tab for Global Configuration

To better separate the parameters in the Global Configuration that relate to User Managment, a new `User` Tab has been added.

------------------------------------------------------------------

 + Global Config param allowing setting of Usertype of new users

You can now select the usertype of all new users registering via the frontend registration component.

------------------------------------------------------------------

 - removed installers references in separate `Sub-Menus`

In The backend dropdown menu, all links to the individual installers in their individual dropdown menus have been removed. For example the `Component Installer` under the `Component` dropdown menu has been removed.  This is because of the introduction of the amalgamated `Installer` dropdown menus introduced in 4.5.2

------------------------------------------------------------------

 + Ability to edit Section information in Frontend

You can now edit the Section Information in the frontend.
Basically it matches the ability to edit content items from the frontend.

------------------------------------------------------------------

 + Global Param to set character length for Username
 + Global Param to set character length for Password
 + Global Param to enable/disable user ability to change their Username
 ^ User Details page modified to use `save` and `cancel` buttons

3 new User Registration related global params.
You can now set the number of characters required for the User Username and Password.  You can now disable/enable users being able to edit their Usernames from the frontend.

------------------------------------------------------------------

 + Added support for restricting searches areas (Andrew Eddie)

A new parameter has been added to the search component to allow for searching specific areas.
The parameter can be enabled by editting the menu item which references the search component.
When enabled you will be presented with a number of checkboxes.  The logic is this:
- If no boxes are checked, then the whole site is search.
- If boxes are checked then only those areas will be searched

For developers:

A new mambot trigger has been added to support the areas, onSearchAreas.
You would add the following code to the top of a search mambot file:

// ----------
$_MAMBOTS->registerFunction( 'onSearch', 'botSearchContacts' );
$_MAMBOTS->registerFunction( 'onSearchAreas', 'botSearchContactAreas' );

$GLOBALS['_SEARCH_CONTACT_AREAS'] = array(
	'contact' => 'Contact'
);

/**
 * @return array An array of search areas
 */
function &botSearchContactAreas() {
	return $GLOBALS['_SEARCH_CONTACT_AREAS'];
}
// ----------

The format of the globals array is 'value' => 'text to display
Any number of items can be returned (for example, in a forum component you might
return 'forums' and 'threads' or the 'areas' function may actually do a database
lookup for specific items.

Then, in the search function itself you would include code like the following:

// ----------
function botSearchContacts( $text, $phrase='', $ordering='', $areas=null ) {
	global $database, $my;

	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( $GLOBALS['_SEARCH_CONTACT_AREAS'] ) )) {
			return array();
		}
	}
// ----------

The fourth parameter is new and is an array of the selected checkboxes.

------------------------------------------------------------------

 + Added parameter to optionally show the google search from search results

New parameter to hide the google search icon.
NOTE: NEED TO FIX STRING THAT DISPLAYS CONCLUSION

------------------------------------------------------------------

 + Add param support for TinyMCE advanced plugins

Via new params for the TinyMCE mambot, you can now show/hide some additional plugins for Tiny, as listed here:
http://tinymce.moxiecode.com/plugins.php
Not all plugins are avilable.  These plugins are only available when the editor is in `Advanced` mode.  By default these plugins are all active.

An additional mode 'Simple' is also now available.

------------------------------------------------------------------

User Personalisation

A user is able to select the language they use (when they are logged in)
and also the WYSIWYG editor the want to use.

------------------------------------------------------------------

Template Tab Graphics

Custom tab graphics can be used with the template.  The graphics are to be
placed in /template_name/images/tabs

------------------------------------------------------------------
>>> Developer Info <<<

 + New mosErrorAlert function to handle all JS Error alerts

New global function to handle all JS error alerts

------------------------------------------------------------------

 ^ Global Param $mosConfig_multipage_toc depreciated

 The Global Configuration parameter $mosConfig_multipage_toc has been depreciated.
 It controlled the displaying of either the Table of Contents or Page Navigation buttons for items using the {mospagebreak}.
 The controls for this have been shifted into the parameters for the MOSPaging mambot, a more logical place for the control of this aspect of the {mospagebreak} feature
 To ensure that people are aware of this change a tooltip exists in the Global Configuration area informing of this move, with a direct link to the mospaging mambot.

------------------------------------------------------------------

  + Re-added 'System Info' Admin menu option

`System Information` is once again a Admin menu option under the `System` sub-menu

------------------------------------------------------------------
>>> Developer Info <<<

  + Added user management and sessions events

  To allow syncronisation of external applications and authentication of users with
  different protocols we added the following mambot events :

    	- onBeforeStoreUser
    	- onAfterStoreUser
    	- onBeforeDeleteUser
    	- onAfterDeleteUser

    	- onLoginUser
    	- onLogoutUser

  A example.userbot that demonstrates the implementation of these events can be found i
  in the user group

------------------------------------------------------------------

 ^ New Checkin Manager

 Global checkin now shows a list of the items that are checked out.
 The list shows the name of the item (if that can be determined),
 the user who has it checked out and the date and the time it was checked out.
 The administrator may select which items to check in.

------------------------------------------------------------------

 + New Export Manager (aka Backup)

------------------------------------------------------------------

 + New 'Column Ordering' ability for all Admin tables

 All Admin pages that are Table Views now have the new ability that allows you to order the table.
 All you have to do is click the column headings to order in terms of that column.
 Click on the same column heading to switch between Ascending and Descending order direction

 ------------------------------------------------------------------

 Minor breakages

 Administrator login templates
 usrname renamed to username
 pass renamed to passwd

 ------------------------------------------------------------------

 ^ `New` button in 'Module Manager' now leads to list of Modules you can create

 Now in the 'Module Manager' if you click the `New` button you are led to a list of Modules you can create.
 This moves it in line with the workflow process used to create new menu items.
 The list of modules is pulled by searching the respective modules diorectories for .xml files.

 ------------------------------------------------------------------

  + New Site and Admin Module allowing the display of an RSS feed

Previously this functionality was included in the * Custom Module * ability.
It seems more logical to have it as a standalone module.

------------------------------------------------------------------

 ^ * Custom Module * no longer has ability to display RSS feeds

This means that you can no longer create RSS feeds via the * Custom Module *.
However, if you have an existing * Custom Module * that you use to display newsfeeds, it will still work correctly.
And you will be able to edit its params as you would in older versions.
In other words full backward compatability is being kept

------------------------------------------------------------------

+ New `State` dropdown added to all Admin table view pages

For all Manager type pages that display in a table format, there is a new `Select State` dropdown.
This allows you to filter the table list in terms of whether items are published or unpublished

------------------------------------------------------------------

 + Ability to edit Category information in Frontend

You can now edit the Category Information in the frontend.
Basically it matches the ability to edit content items from the frontend.

------------------------------------------------------------------
>>> Developer Info <<<

# Fixed [#6123] mosimage and pagebreak buttons showing on unneeded screens

 ** Important for WYSIWYG Editor Mambot Creators **

An extra parameter has been added to the  editorArea() function a $showbut param.
editorArea( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut=1 )

The $showbut param is used to control whether the optional Editors-xtd buttons are loaded or not.
For the two core editors they are loaded automatically, example:

function botNoEditorEditorArea( $name, $content, $hiddenField, $width, $height, $col, $row, $showbut=1 ) {
	global $mosConfig_live_site, $_MAMBOTS, $option;

	$buttons = '';
	// show buttons
	if ( $showbut ) {
		$buttons = array();
		$results = $_MAMBOTS->trigger( 'onCustomEditorButton' );
		foreach ($results as $result) {
		    $buttons[] = '<img src="'.$mosConfig_live_site.'/mambots/editors-xtd/'.$result[0].'" onclick="insertAtCursor( document.adminForm.'.$hiddenField.', \''.$result[1].'\' )" />';
		}
		$buttons = implode( "", $buttons );
	}

	return <<<EOD
<textarea name="$hiddenField" id="$hiddenField" cols="$col" rows="$row" style="width:$width;height:$height;">$content</textarea>
<br />$buttons
EOD;
}

This allows these buttons to be hidden in edit screens where they cannot be used - like when entering Section data.

------------------------------------------------------------------
>>> Developer Info <<<

 ^ mospage.btn editor-xtd mambot is only loaded for specific components

This button which enters a {mospagebreak} text into the editor has been hardcoded to appear only in:
-> com_content
-> com_typedcontent

This is because the mospage break mambot is specific to content handling and has no wider uses beyond that.

------------------------------------------------------------------

 + Mambots now parse `Section` and `Category` Description
 + Mambots now parse `Weblinks` Descrition and ` Weblinks Category` Description

This means mambots can now be used in the Section & Category description field.
So for example any email addresses you place in the section description, will now be cloaked by the mosemailcloak mambot.

------------------------------------------------------------------

 + New Admin module `mod_links`

This new admin module adds context sensitive quick link buttons for various pages throughout the Admin area.
These new buttons appear below the pathway and toolbar area of the Admin area.
For example on the `Static Content` Manager pge, you have quick link buttons to:
-> All Content Items
-> Media Manager
-> Trash Manager

The aim for this addition is to improve workflow speed, by lessening the need to utilise the standard dropdown menu of the admin backend.

------------------------------------------------------------------

 + New Admin & Site module `mod_footer`

 This new module simply displays the contents of the footer.php file.
 The Admin version also conatins the code to generate the page generation time
 This has been added to make it easier to remove the Joomla! copyright statement without having to worry about breaching the GPL

------------------------------------------------------------------

  + New Admin module `mod_logoutbutton`

Module displays the username of the logged in user and the logout button.
Created so that it no longer needs to be hardcoded into Admin templates.
Loaded via module position, rather than a direct call in the template - added to `header` module position

------------------------------------------------------------------

 ^ more template code shifted in mosShowHead & mosShowHead_Admin

Loading of editor file, template css file and meta `Content-Type` now included in these functions
** This change will not affect how older templates work, however, this does mean cerating things maybe loaded twice

------------------------------------------------------------------

 + template print.php file for use when printing

Now when you use the print button on Content Items, a separate template file just for printing can be loaded.
If a print.php file is found in your 'Site' template `directory` it will be loaded.
Also if a print_css.css file is found in your templates `css` directory it will be loaded, otherwise the templates default css file is used.
If no file is found than the existing framework is used to create the print page.

This means you can no fully customize how the printed versions of you content will appear and optimize for best appearance on paper.

------------------------------------------------------------------

 + `Static Content` can now be assigned to `Frontpage` Component

 What does this mean.  Well it means you can select a `Static Content` item and have it appear in the Frontpage Component.
 Basically the same functionality that exists for `Content Items` is now avaialble for `Static Content`.

------------------------------------------------------------------
>>> Developer Info <<<

 - /administrator/popups/contentwindow.php depreciated, functionality moved into com_content files
 - /administrator/popups/modulewindow.php depreciated, functionality moved into com_modules files
 - /administrator/popups/pollwindow.php depreciated, functionality moved into com_polls files
 - /administrator/popups/uploadimage.php depreciated, functionality moved into com_media files
 - /administrator/popups directory depreciated

All files in the /administrator/popups directory have been depreciated and their functionality shifted into other mroe relevant areas.
The popup funcitonality still exists but it has been moved away from these old legacy files into component files.
This depreciation was made as the separation of popup handling into separate files in the /popup directory is legacy from Joomla! 4.0
It is far more logical to have the functioanlity stored within relevant components.

------------------------------------------------------------------

 + MOSimage now can be assigned url link

Two new fields have been added to MOSImage: Link URl & Link Target.
These fields allow you to make you MOSImage linkable.

------------------------------------------------------------------

 + Global Config Param allowing Frontend editing to open larger uncluttered popup window

If this new 'Site' Global Configuration parameter is enabled, frontend editing opens a 800 x 600 popup window.
This edit screen does not utilise the template file, so it can utilize the maximum area available, allowing for larger edit screens.
By default this option is disabled and inline editing is utilized for frontend editing.

------------------------------------------------------------------

 + {moemailcloak=off} tag to allow disabling of mosemailcloak mambot for specific items

By entering this tag {moemailcloak=off} within a content item, it will stop the mosemailcloak mambot from poperating on that specific item

------------------------------------------------------------------
>>> Developer Info <<<

 ^ Limit handling in SQL

 Limits should not be done in the SQL anymore to allow for x-db portability,
 you should instead use:

 $database->setQuery( $sql, $offset, $limit );

------------------------------------------------------------------

Media Manager

Upload and Create Directory forms now form hidden parts of the screen which are shown when you
click the respective toolbar button.

------------------------------------------------------------------
>>> Developer Info <<<

 - toolbar.{componentname}.html.php files depreciated

All previous code shifted into toolbar.{componentname}.php files.
Utilization of new mosAbstractTasker class.
This does not affect exiting components as they will still call toolbar.{componentname}.html.php
However, this change should be seen as a new standard for component creation.

------------------------------------------------------------------

 ^ Ability to stop Blog pages (incl frontpage compt) from inheriting meta keywords & descriptions from content items

Fixes a potential barrier to SEO, where the meta data from every article displayed on the blog page was being added to meta tags for the page.
This behavour might be considered spamming by search engines.  It also meant that it was difficult to create a unique set of meta for that page.
Bu default this functionality is enabled, that is the meta from articles is no longer appended to the meta of the page.

------------------------------------------------------------------

 + Meta Keywords & Description for menu items - via params
 + SEO Page Title for menu items - via params

To improve potential SEO performance, you can now specify meta keywords and descriptions for every menu itemm.

To hopefully enhance SEO, you can also now control the title tag for the page, allowing you to create a unique title.
If you do not define a title, the menu name (or content title for content views) will be used.

------------------------------------------------------------------

 + Global Config param allowing control of format of SEO Page Title

In SEO tab of Global Configuration you will see a new param with 3 options for the format of the SEO title of your pages
Site - Title [default]
Title - Site
Title

------------------------------------------------------------------

 ^ Updated SSL handling to be more robust, with per/menu SSL options - Thanks to Louis Landry

 Several things here have changed.  First and foremost is the addition of a new link function called mosLink() function.  This now takes
 an optional second param that can be one of the following:

 -1 = SSL is off, and use non-SSL URL
  0 = Ignore, and use whatever site is currently running under
  1 - SSL is on, and use SSL URL

 To support this functionality a new global configuraiton param has been created called $mosConfig_secure_site.  This defaults to
 the $mosConfig_live_site value with https:// rather than http://

------------------------------------------------------------------

  ^ `Live Bookmarks` control moved to Global Configuration

The control for 'Live Bookmarks' originally was in the Syndication component.
However since it is a site wide functionality it makes more sense to have control moved to `Global Configuration`

------------------------------------------------------------------
 # Fixed [#6590] Cannot access user manager anymore

 When you edit content, the list allowing you to change the author will only
 show users in the author group and above, for manager group and above.

 These are the users that are permitted to enter content.  It gets over problems
 where a site has many thousands of registered user but only a few content
 providers.

------------------------------------------------------------------

+ Added new cache manager

1. We moved all cache classes into /includes/mambo.cache.php

  Introduced three caching mechanism

  - Template caching (for caching of parsed pT templates)
  - Item caching (for caching of individual module and component output)
  - Page caching (for caching of whole page)

  A cache object is created using mosFactory::getCache(group, handler)
  The handler is the class name of a cache_lite extended class.

  Possibilities are :

  - mosCache_Function
  - mosCache_Output
  - mosCache_Page

  An 3PD can easily extend this by creating his own cache handler.

  2. Caching strategy is set inside a module/component

  It is impossible for the core to predict in what way component/module
  output can be cached. Some components output is very static, other will be
  very dynamic.Components and modules will be responsible for creating and
  maitaining their own cache.

  3. Page caching

  We added a experimental page caching system. This caches the whole
  page and sends a 304 Not modified header when nothing has changed.
