<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
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
Joomla! 1.5, including beta and release candidate versions.
Our thanks to all those people who've contributed bug reports and
code fixes.


Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

27-Jul-2007 Andrew Eddie
 + Added example content bot

27-Jul-2007 Andrew Eddie
 # Fixed bug in contact us form: cc not working

25-Jul-2007 Andrew Eddie
 # Fixed bug where JModuleHelper::getModule couldn't load module by real name

24-Jul-2007 Johan Janssens
 ^ site_langauage and administrator_language settings are now stored as com_languages paramaters
   instead of in the configuration.php

23-Jul-2007 Andrew Eddie
 # Fixed bug in JHTMLImage::site giving full image tag in the src attribute
 # Removed eval from JUtility::sendAdminMail method
 # Fixed possible syntax error evaluation in JDocumentHTML::countModules
 ^ Added validation trigger onValiddateContact for custom form handling (compliments onSubmitContact)

-------------------- 1.5.0 Release Candidate 1 Released [21-July-2007] ---------------------

21-Jul-2007 Wilco Jansen
 ^ Changes version file

20-Jul-2007 Johan Janssens
 # Fixed #6133] After saving global configuration default langauge changes to English language

20-Jul-2007 Robin Muilwijk
 # Fixed several small issues in sample_data.sql

20-Jul-2007 Andy Wallace
 # Fixed language translation issues in timezone
 # Further edits of language strings
 # Fixed hardcoded elements in readmore plugin and admin.trash.php

19-Jul-2007 Laurens Vandeput
 # Fixed UTF-8 issues with XML-RPC
 # Fixed XML-RPC server and upgraded the library to the most recent version

19-Jul-2007 Wilco Jansen
 + Added check for removal of installation directory after installation
 ^ Installed new sample data

19-Jul-2007 Enno Klasing
 ^ Updated and added installer application translations from the Accredited Translation Partners

19-Jul-2007 Hannes Papenberg
 + Added option to switch off feed links

19-Jul-2007 Johan Janssens
 # Fixed [#5381] Admin template cosmetic issue in IE6/7

19-Jul-2007 Sam Moffatt
 # JFactory::getXMLParser now case insensitive

19-Jul-2007 Sam Moffatt
 # Fixed unchecked array access for language option in libraries/joomla/application/application

18-Jul-2007 Andy Wallace
 # Corrected GNU/GPL URL links in en_GB language files and various other files

18-Jul-2007 Andy Miller
 ^ Updated the COPYRIGHT.php file

18-Jul-2007 Andy Miller
 + Added print style icon for header/toolbar/menu
 # Fixed some font inconsistencies in milkyway css
 # Fixed [#5851] Insert Image popup is broken under IE7
 # Fixed [#259] IE 6 freezes when resizing Milky Way template under 750 px width

18-Jul-2007 Andy Wallace
 # Fixed missing JText Image and Misc names in com_user

18-Jul-2007 Johan Janssens
 # Fixed [#6093] Saving changes in backend/configuration returns error on JOUputfiler
 + Added configuration file folder and image folder paths
 # Fixed [#6088] If no section is created / available creating category results in blank
   page with error
 # Fixed [#6081] Article selection popup is broken.
 # Fixed [#5926] Kepri: <table class="adminlist"> invalid markup
 # Fixed [#5635] reference to Mambelfish
 # Fixed [#5612] The "Read more... " URL Router cannot working well for "Section Blog Layout"
 # Fixed [#6073] GPL url points to version "latest" (v3) instead of V2 in all files

18-Jul-2007 Enno Klasing
 # Fixed [#4864] Hardcoded string in administration, com_media

17-Jul-2007 Andy Miller
 ^ Changed link 'disabled' state to 'readonly' when editing menu item

17-Jul-2007 Johan Janssens
 ^ Changed template _system directory to system for consistency reasons
 + Added storage parameter to JFactory::getCache to allow different stores to be used
 ^ Renamed JOutputFilter classname to JFilterOutput to adhere to naming conventions
 ^ Renamed JInputFilter classname to JFilterInput to adhere to naming conventions

16-Jul-2007 Johan Janssens
 + Added debug setting to installation localise.xml file to handle easy language debugging
   of the installer

15-Jul-2007 Enno Klasing
 # Fixed [#6003] Removing module fails
 + Added optional "exclude" parameter to JFolder::files and JFolder::folders
 ^ SQL queries in the debug plugin are now displayed with linebreaks before keywords

14-Jul-2007 Rob Schley
 # Fixed [topic,190665] Error after enabling Cache plugin with Cache enabled at Configuration
 # Fixed [topic,189910] Cant change dir in media manager
 ! Note: Image upload via the pop-up box is still quite broken.
 # Fixed [topic,189389] Usability and module positions
 # Fixed [#6035] Media manager, path is not a folder error

14-Jul-2007 Johan Janssens
 # Fixed [#6038] Section Manager - Delete Section When Not Empty

14-Jul-2007 Enno Klasing
 # Fixed database error in the 'loadmodule' content plugin which was caused by a missing/removed table
 # Fixed [#6055] Search plugins return results with access level 'special' to 'registered' users
 - Removed unnecessary database query from the administrator mod_menu module

13-Jul-2007 Rob Schley
 # Removed backticks (`) from language files.  Do not use them, use single quotes (').
 # Fixed [#6034] No tmp folder in current builds
 # Fixed [#5853] Repeated strings at site language file: en-GB.com_user.ini
 ^ Changed language string "PASSWORDS_DO_NOT_MATCH" to "PASSWORDS_DO_NOT_MATCH_LOW" to reflect context
 ^ Changed language string "PASSWORDS DO NOT MATCH" to "PASSWORDS_DO_NOT_MATCH"
 # Fixed [#5641] Repeated strings at site language file en-GB.com_user.ini
 - Removed language strings "LOST PASSWORD?", "LOST YOUR PASSWORD?" "FORGOT USERNAME"
 + Added language string "FORGOT_YOUR_USERNAME" and "FORGOT_YOUR_PASSWORD"
 # Fixed [#4701] Installation: wrong form validation - Thanks Mark Harvey and Matheus Mendes
 # Fixed [#5440] order is 0 for new articles - Thanks Kevn Devine

13-Jul-2007 Andy Wallace
 # Fixed some errors in hard coded language parameters. Updated Admin, Installation, and Site Language files.

11-Jul-2007 Andrew Eddie
 # Fixed module copy when more than one module selected

09-Jul-2007 Sam Moffatt
 # Fixed up issue where two code paths in libraries/joomla/filesystem/archive failed to return a result
 - Disabled database check for module installation; file system check handles things; this allows easier module migration
 ^ Migrating search and login/logout menu items
 + Extra checks
 ! Migration system ready?

08-Jul-2007 Enno Klasing
 - Removed unused code in com_admin, com_media and JLoader

07-Jul-2007 Enno Klasing
 + Added 'username' input filter
 ^ Renamed administrator pcl files (backwards compatibility)

05-Jul-2007 Toby Patterson
 # Fixed [#5513] "Publishing time" of an article differs on frontpage manager than the actual publish time
 # Fixed [#5804] Bug on JParameter method getParam()
 ! Change to JParameter reverted in r7886

04-Jul-2007 Rob Schley
 # Fixed [#4714] Activation not work in Firefox - IE it works okey
 # Fixed inconsistencies in password field naming conventions
 # Fixed encoded ampersands in reset/remind e-mails
 - Removed the username field from the password reset process
 - Removed remnants of the old password reminder system
 # Fixed [topic,187139] Can't copy uncategorized article as new uncategorized article
 # Fixed [topic,187139] Moving article displays uncategorized as static content, changed to uncategorized
 # Fixed [topic,186789] Issue with heading of "Menu Trash" manager
 * Added a check to strip out any line breaks from JApplication::redirect() to prevent HTTP header injections
 # Fixed [topic,187139] Inconsistencies in section/category display for moving and copying articles

04-Jul-2007 Andrew Eddie
 # Fixed bug on registration page: verify password had wrong name

03-Jul-2007 Rob Schley
 # Fixed a bug that allowed the password reset token to be used to activate accounts
 # Fixed the password reset process queries to only work on active accounts

03-Jul-2007
 # Fixed up missing $attr in the input filter on some code paths
 ^ Migration System has a major shock and update.
 - 1.5 to 1.5 Transfer system absorbed into greater migrate infrastructure
 + added modified 'bigdump' script to handle large dump files

03-Jul-2007 Louis Landry
 # Fixed misrouting when components or views don't match
 # Fixed routing problems for index2.php -- legacy issue
 # Fixed menu alias rendering
 # Applied patch from User Doc team with admin language modifications
 # Fixed [#5859] E-Mail this Link to a Friend doesn't work correct

02-Jul-2007 Alex Kempkens
 # fixed duplicate calls of JText in botton rendering
 + added missing text tags in english language files
 # removing unneeded new lines in metadata.xml files as they are causing issues during the translation
 - points in the language tags
 ! we are still in the process of cleaning all language tags this includes a complete check before the RC too

02-Jul-2007 Rastin Mehr
 # Fixed [#5636] Modify a category from a custom component
 + '&type='.$type is added to the end of edit link
 # Fixed [#5833] Backend -> plugin | buttons & navigation broken
 # Fixed [#5858] Hit Counter wird nicht angezeigt bei Statistik
 + Hit Counter feature is now implemented
 ! Hit Counter feature did not exist, but the existing parameters "counter" and "increase" indicated that feature needs to be in place

1-Jul-2007 Toby Patterson
 # Fixed issue with GET input being lost when SEO is enabled in JRoute::parse()

01-Jul-2007 Rastin Mehr
 # Fixed [#5612] The "Read more... " URL Router cannot working well for "Section Blog Layout"
 # Fixed [#5744] Layout "blog" not found

30-Jun-2007 Kevin Devine
 # Fixed [#5773] Error when article is saved without text in the body of article, front and backend

29-Jun-2007 Enno Klasing
 # Fixed: com_templates made every template a default template when saving
 ^ Reworked com_templates so that editing and saving CSS files does not need the full path

29-Jun-2007 Toby Patterson
 # Fixed [#5738] ConfigControllerApplication: wrong "db_prefix" in generated config file
 + Added or updated various component xml files so that the components are properly listed in com_installer (topic,154849)
 - Removed com_menumanager from install sql

28-Jun-2007 Rob Schley
 ^ Fixed [topic,185613] Removed closing PHP tag from the legacy plugin

28-Jun-2007 Louis Landry
 ^ Hardened user password storage to use a random generated salt
 ! Reinstallation required as password strings for all users will be different

27-Jun-2007 Johan Janssens
 ^ Reworked editor-xtd handling, added ability to specifiy which editor-xtd plugins you don't want to display.

27-Jun-2007 Rob Schley
 # Fixed password reset/username remind e-mail links not working with SEF enabled
 ^ Reworked the password reset work-flow to be more secure
 ^ Adjusted some of the links in mod_login to point to the new username reminder and password reset features
 + Added a username reminder feature
 ! Added several strings to com_user and a couple strings to mod_login

27-Jun-2007 Chris Davenport
 ^ Changed 'Enable' to 'Default' in Language Manager
 # Language Manager not retaining change to default language

26-Jun-2007 Johan Janssens
 ^ Fixed [#5748] & appears as *amp; in breadcrumbs
 ^ Fixed remember me functionality for the openid plugin. Changes also have effect on any of the
   other authentication and user plugins.

26-Jun-2007 Toby Patterson
 ^ JDocument will default to RAW document if the request format is not supported, but document's
   type will be that of the requested format (see JDocument::getInstance) - Louis's suggestion
 # Fixed [#5743] Dateformat in category blog layout

26-Jun-2007 Andrew Eddie
 + New method JUtility dump

25-Jun-2007 Mateusz Krzeszowiec
 # Fixed [#5757] com_content view=category does not show registered article link to nonregistered
   user when Show UnAuthorized Links set to Yes

25-Jun-2007 Chris Davenport
 + Added ENABLE and DISABLE tags to admin en-GB.ini
 ^ Changed 'Publish/Unpublish' to 'Enable/Disable' for modules, plugins and languages

22-Jun-2007 Toby Patterson
 # Fixed [#5700] Vote button on poll is rating article

22-Jun-2007 Andrew Eddie
 + Added support to set the class of the <ul> in mod_mainmenu via the class suffix parameter
 # Fixed truncation of multi-line (eg, textbox) parameters
 + Added '*' to item that has the default menu item in Admin dorpdown menus list

19-Jun-2007 Rastin Mehr
 # Fixed unreported bug, Whe front-end user submits a weblink they get an error view "form" does
   not exist
 # Fixed [#169] Article submission fails for both users "Registered" and "Author"
 * Fixed security flaw so Authors and Registered users would not be able to edit articles that don't
   belong to them
 ^ in the SITE ContentController::edit implemented security checking to make sure user has the right
   to edit, doesn't attempt to edit an article that doesn't belong to her

18-Jun-2007 Andrew Eddie
 ^ Allowed JException::getBacktrace to be called statically

14-Jun-2007 Laurens Vandeput
 ^ Updated the phpxmlrpc library to version 2.2

13-Jun-2007 Rastin Mehr
 # Fixed [#4746] New user registration gets accepted even if passwords in form don't coincide

13-Jun-2007 Andy Miller
 # Fixed [#21] Caption CSS
 # Fixed issue with images getting overlayed with text on first load
 # Fixed [#12] IE6 radio buttons and checkboxes with border

12-Jun-2007 Toby Patterson
 # Fixed [#532] Missing parameter for setting date format in showCalendar() javascript function
 ! New DATE_FORMAT_JS1 key in en-GB.ini used to provide data format for JavaScript, used by calendar
   parameter

11-Jun-2007 Jason Kendall
 # Fixed [#5324] Section Blog layout date fix
 # Fixed [#5322] Section Blog Layout - Catagory was not showing
 # Fixed [#5426] Incorrect building of URL for feeds - Thanks IAN
 # Fixed [#5442] Banner impressions left was incorrect
 # Fixed [#4747] Last visit date should read never if the user never activated account
 # Fixed [#4862] Weblinks search was incorrectly building URL - Thanks IAN

11-Jun-2007 Rob Schley
 # Fixed [#5646] Repeated strings at site admin file en-GB.com_installer.ini
 # Fixed [#5642] Repeated strings at site admin file en-GB.mod_menu.ini
 # Fixed [#5641] Repeated strings at site language file en-GB.com_user.ini

10-Jun-2007 Rastin Mehr
 # Fixed [#5628] gacl_api::get_group_id( ... ) now returns the right value, that was causing the
    registration process not going through properly.
 # Fixed [#4694] Com_contact broken
 ^ Modified sample_data.sql file to fix the issue

10-Jun-2007 Toby Patterson
 # Installer reports a more descriptive error when a file cannot be uploaded.

09-Jun-2007 Enno Klasing
 # Disabled XML-RPC Server and Client when they are disabled in configuration.php, Client only
   reachable when debugging is on

09-Jun-2007 Andrew Eddie
 ^ Disabled ID check in gacl_api::add_acl method (not require in Joomla! yet)

08-Jun-2007 Toby Patterson
 # Fixed [#4485] language/en-GB/en-GB.ini "DATE_FORMAT_LC3" and "DATE_FORMAT_LC4" are missing
 ! Various format strings ( eg DATE_FORMAT_LC4 ) no longer defined in the framework - use JText::_()
   instead
 # Fixed [#4744] Error messages prevent successful installation

08-Jun-2007 Enno Klasing
 # Fixed problems with XMLRPC server when JRequest::clean() was called
 # Fixed [#5427] JRequest::get() improperly cacheing results
 # Fixed [#4863] Component name not being translated in the menu

07-Jun-2007 Toby Patterson
 + Added second arguement ($strict) to JComponentHelper methods
 + JFolder::folders() and files() methods can search recursively to a specified depth
 + JInstaller::_fineManifest() looks for manifest file to depth of '1' - could change to '0' ???

07-June-2007 Louis Landry
 ^ Allow component configuration passthrough to menu items for list/radio types

07-June-2007 Johan Janssens
 # Fixed [#5551] Clicking on a contact found using the contact search plugin results in 500 View Not Found

06-June-2007 Toby Patterson
 # Fixed [#4686] Missing error check in Extensions Installer causes error during component install
 + mosInstaller class and deldir function to legacy plugin
 - installer.class.php from admin com_installer

06-June-2007 Johan Janssens
 ^ Removed legacy positions from rhuk_milkyway, legacy position are now only added to the
   module positions if legacy mode is enabled.
 ^ Only load the positions from default and assigned templates in the module manager

05-June-2007 Toby Patterson
 # com_config will force update of FTP credentials so that one can disable FTP
 # Added checks to JArchive that will return false if an archive does not exist
 # Updated installer framework files to address various issues
 # Updated admin's com_installer to reduce duplicate templates and logic
 # Fixed [#4737] Output from com_uninstall() never displayed
 # Fixed [#4861] Error, when trying to install a language
 # Fixed error when uninstaling language, will remove core language
 ^ modules and plugins now support the update method
 - Removed templates from installer views now shared from default view

05-June-2007 Rob Schley
 # Fixed [#5553] Typo in function name, newsfeeds.php search plugin file
 # Fixed [#4824] The Move Categories screen doesn't display the data about categories that are being moved

05-June-2007 Johan Janssens
 # Fixed [#5510] Image preview not work in Section and Category Edit screen

05-June-2007 Andrew Eddie
 # Fixed PK field name in jos_core_acl_aro_sections and removed bogus indexes
 # Upgrade phpGACL to version 3.3.7
 # Unused JTableUser::getUserListFromGroup moved to legacy class
 # Unused JTableUser::getUserList moved to legacy class
 # Moved legacy ACL's to legacy plugin
 # Optimised internal ACL counter
 # Renamed ACL seciton 'action' to contextually correct 'com_content', added 'action' to legacy plugin
 # Refactored mydetails setup file switcher, added return value to JAuthorization::addACL
   (per native phpGACL library) to handle this better

04-June-2007 Rob Schley
 # Fixed [#5394] Can submit an article without a title
 # Fixed [#5397] Frontpage article submission problem
 # Fixed [#5345] Error in mod_related_items
 # Fixed two input requests without filter types

04-June-2007 Louis Landry
 # Fixed [#5393] Using com_content&layout=edit causes php stack overflow
 # Fixed [#4844] JRequest::getVar freezes on unclosed tag and mask=JREQUEST_ALLOWHTML
 # Fixed [#4865] Uploading more then one file doesn't works in media manager

01-June-2007 Johan Janssens
 + Added JElementCalendar class and JHTML::calendar function
 # Fixed [#5468] Drop down lists in backend don't allow selection

01-Jun-2007 Enno Klasing
 # Default value of first call to JRequest::getVar is not used as the return value of subsequent
   calls for the same input variable anymore

31-May-2007 Johan Janssens
 # Fixed [#5452] In backend com_search Reset icon not working

31-May-2007 Louis Landry
 # Fixed [#5436] Wrong formated PDF/Print/Mail Links
 # Fixed [#4894] XHTML Validation Bug
 # Fixed [#5398] Email to friend broken, returns a 403 error
 # Fixed [#267] Incorrect using of forms[0] in javascript
 # Fixed [#4484] JHTMLSelect hasn't the possibility to set a select/option to disabled
 # Fixed [#4895] Image caption uses a container with the wrong class attribute value

30-May-2007 Johan Janssens
 # Fixed [#5445] Site Offline has no effect, users still can view the site when offline

31-May-2007 Enno Klasing
 # Set default charset for the PHPMailer class to utf-8

30-May-2007 Louis Landry
 # Fixed [#443] Bug with highliting in mod_mainmenu
 # Fixed [#5431] is_group_child_of mysql query always returning false

30-May-2007 Enno Klasing
 # Fixed [#4866] Hardcoded string in administration - Banners
 # Fixed [#4867] Hardcoded string in administration - Contacts
 # Fixed [#4868] Hardcoded string in lists
 # Fixed [#4869] Hardcoded string in administration - Search

29-May-2007 Rob Schley
 # Fixed [#4853] Small typos in language/en-GB/en-GB.ini and administrator/language/en-GB/en-GB.ini

29-May-2007 Sam Moffatt
 + Added JFile::getFileName($file) to allow easy determination of a file
 # Removed JArchive blind assumption that .gz/.bz2 files always hold tar files

28-May-2007 Rob Schley
 # Fixed [#4878] Small typo in libraries/joomla/database/table.php
 # Fixed [#4908] Small typo in libraries/joomla/utilities/error.php

28-May-2007 Johan Janssens
 - Removed JHTML::_implode_assoc and JDocumentHelper::implodeAttribs functions
 + Added JArrayHelper::toString function
 # Fixed [#5404] OpenID redirection problem upon successful authentication
 # Fixed [#5421] application router makes ambiguous check against menu route

28-May-2007 Enno Klasing
 ^ The 'CMD' input filter does not allow leading full stops anymore
 # Fixed problem with JRequest::setVar making JRequest::getVar return unfiltered input

28-May-2007 Andrew Eddie
 # Fixed undeclared variable when not over-riding pagination
 # JRequest::getVar $sig needed $type added to for example cover case where $task may be an array
   (used to translate input buttons correctly)

27-May-2007 Enno Klasing
 # Fixed [#5391] Cannot install sample data

26-May-2007 Enno Klasing
 + Added optional filter parameter to JApplication::getUserStateFromRequest

25-May-2007 Johan Janssens
 + Added getName function to JController, JModel and JView.

25-May-2007 Rastin Mehr
^ function JHTMLBehavior::keepalive is now using mootools ajax calls, and the
  ( config file liftime value - 2min ) for refresh rate

23-May-2007 Rastin Mehr
 ^ Updated Mootools Library to version 1.1

22-May-2007 Johan Janssens
 ^ Added ini_set('zend.ze1_compatibility_mode', '0') to all framework.php files
   to force PHP5 to run in none compatibility mode. (default setting)
 # Fixed [#4759] function routeNewsfeeds()
 # Fixed [#4822] undeclared variable in com_wrapper view.php
 # Fixed [#5300] Web links not loading just refreshes the page
 # Fixed [#5320] XML Parsing Error at 1:1674. Error 26: Undeclared entity error

22-May-2007 Rob Schley
 * Fixed two minor XSS vulnerabilities in com_mailto
 * Fixed a couple of unfiltered inputs in com_content
 + Added two new filter types to JInputFilter::clean(): ALNUM for alphanumeric and BASE64 for base64 encoded information

21-May-2007 Andrew Eddie
 # Fixed bug in Banners list: Category used 'name' instead of 'title' field

21-May-2007 Andrew Eddie
 # Fixed PEAR.php case problem
 + Added JHTMLSelect::optgroup to better handle option groups and allow for forward compatibility
 ^ When JHTMLSelect::options receives an array for multiple selected values, the array also be a column of scalar values now
 # Improved display of article last modified date
 # Fixed bug in Newsfeeds list: Category used 'name' instead of 'title' field
 # Fixed problem with JHTML::_implode_assoc yeilding invalid attributes, and malformed iframe syntax (from ianmac)
 # Fixed bug in poll selector (patch from ianmac)
 # Fixed missing published radio in poll edit form

17-May-2007 Johan Janssens
 ^ JHTMLGrid::sort and JHTMLGrid::order now return instead of echoing
 - Removed template_positions table and cleaned up com_modules code

07-May-2007 Johan Janssens
 # Fixed [#4693] PDF nofollow malformed
 # Fixed [#4700] rider still present in installation folder

07-May-2007 Andrew Eddie
 # Fixed "Menu Type" label in the menu manager view.
 # Fixed bug in pagination (all mode)
 - White space cleanup
 - removed unused TODO.php

05-May-2007 Andrew Eddie
 # Fixed undeclared $mosConfig* variables

-------------------- 1.5.0 Beta 2 Released [04-May-2007] ------------------------

04-May-2007 Johan Janssens
 ^ Updated installation languages packs and removed old ones

04-May-2007 Andrew Eddie
 ^ Moved admin articles list legend to component included html element
 ^ favicon moved to template folder to address browser caching anomalies

03-May-2007 Louis Landry
 ^ Menu Parameters / Component Configuration rework
 ! Either resave all data in backend or reinstall :)

03-May-2007 Johan Janssens
 ^ JHTML optimisation, finished
 ^ Changed JParameter xml path attribute from addparameterdir to addpath
 - Removed moo.fx package
 - Removed common.js file, functionality already available in mootools framework

02-May-2007 Johan Janssens
 + Added helper suppport to JView class, see loadHelper function
 + Added JPATH_XMLRPC define
 ^ Changed plugin auto-registration to push plugin parameters into plugin class scope.
 - Removed Cache_Lite package from the framework.
 ^ JHTML optimisation, work in progress

01-May-2007 Johan Janssens
 # Fixed [#381] Reset-button does'nt work in Module- and Plugin Manager
 # Fixed [#496] TableBannerClient::store - fails when adding new client - Unknown column 'alias'
 # Fixed [#479] Weblink model isCkheckout function
 # Fixed [#383] Read More in Category Blog Layout results in Error
 # Fixed [#396] Bug in com_component

30-Apr-2007 Johan Janssens
 ^ Updated Geshi library to version 1.0.7.19

30-Apr-2007 Jason Kendall
 - Spoke to Louis, readded pcl due to leggacy, but patched security bug

30-Apr-2007 Andrew Eddie
 ^ Fully converted JHTMLCommon::tableOrdering to JHTML adapter pattern

29-Apr-2007 Louis Landry
 - Removed unused pcl libraries

28-Apr-2007 Johan Janssens
 ^ Refactored com_content helper classes, added html, route and query helpers

26-Apr-2007 Toby Patterson
 # Fixed [topic 163256] Wrong title displayed for category.
 # Fixed [art #405] Warnings w/ com_content and BEEZ, patch applied - thanks lukewill

24-Apr-2007 Louis Landry
 + Added memcache support for JCache and JSession -- thanks for the help Mitch
 + Global config setting for cache storage engine

24-Apr-2007 Johan Janssens
 + Added cache_handler configuration variable
 ! Changes require a reinstall

23-Apr-2007 Johan Janssens
 ^ Moved event and plugin classes into their own event package
 ^ Moved session classes into their own session package
 ^ Moved functions from JAdminMenus to JAdministratorHelper
 ^ Renamed JMenuBar to JToolBarHelper

21-Apr-2007 Johan Janssens
 ^ Fixed various problems with URL routing

18-Apr-2007 Johan Janssens
 ^ Fixed problems with the pagebreak plugin and the pagination handling
 ^ Changed ComponentParseRoute function implementation, function now needs to return array
   of variables.

18-Apr-2007 Toby Patterson
 + JController::getView() takes $config argument which is passed to the view's constructor

14-Apr-2007 Johan Janssens
 - Removed rider from license for futher review

14-Apr-2007 Toby Patterson
 # Fixed [#306] Post Installation Error - installer updated
 ^ JDatabase::getInstance() accepts $options parameter, may return JException

14-Apr-2007 Enno Klasing
 + Added 'PATH' filter to JInputFilter

12-Apr-2007 Jason Kendall
 # Fixed [#15] - global checkin now available to admins
 # Fixed [topic,159199] - ordering of <= 0 items - Thanks Ian

11-Apr-2007 Louis Landry
 + Fixed [#110] the "remember me" option at login doesn't work
 + JSimpleCrypt for basic string encryption
 ! Changes require a reinstall

10-Apr-2007 Johan Janssens
 ^ Implemented alias support for all content types
 # Fixed [#241] SEF urls create the same url for multiple pages
 ! Changes require a reinstall

10-Apr-2007 Toby Patterson
 ^ JLanguage::_load now responsible for loading into _strings
 + Added JLanguage::_getCallerInfo() to produce debugging information

10-Apr-2007 Andrew Eddie
 ^ Renamed ContactController::sendmail method to ContactController::submit
 + Added onSubmitContact trigger for custom contact handler plugins
 + Added Contact Component configuration setting to turn off the built in reply
  (and allow completly custom handling)

10-Apr-2007 Sam Moffatt
 # Fixed [#264] Menu Weblink doesn't show weblinks Description

08-Apr-2007 Toby Patterson
 + Added check to JDatabase::getInstance() to die if error in connecting
 + Added connected() methods to JDatabase and derived classes

08-Apr-2007 Johan Janssens
 ^ Update OpenID library to version 1.2.2

07-Apr-2007 Johan Janssens
 + Added onLoginFailure and onLogoutFailure events
 + Added Log plugin to catch new onLoginFailure event
 ^ Refactored JAuthentication to allow for stacked authentication plugins
 ! Changes require a reinstall and authentication plugins will need to be adapted

06-Apr-2007 Andrew Eddie
 ^ Split out options rendering in JHTMLSelect::genericList to JHTMLSelect::options

05-Apr-2007 Andrew Eddie
 + Added JView::setLayoutExt to allow a designer, for example, to set .html (default .php)
   for layout files

04-Apr-2007 Jason Kendall
 # [Topic,156662] JTable was using die, not JError -- Thanks Ian
 # [Topic,156522] JTable was not using JDate for dates -- Thanks Ian
 # [Topic,156659] Rouge code (never executed) in JTable::isCheckedout -- Thanks Ian
 # [Topic,156515] Check for check_out_time as well in JTable::checkin -- Thanks Ian
 # [Topic,156656] hit should check for the hits field and should update the local table -- Thanks Ian

02-Apr-2007 Enno Klasing
 + Added JRequest::getString
 # JInputFilter now also handles the types "string" and "array"
 # JArrayHelper::toInteger does not return anything, operates directly on the parameter

31-Mar-2007 Johan Janssens
 ^ Removed closing tags from Joomla! Framework library files

31-Mar-2007 Jason Kendall
 # Fixed [track191] - Missing DS in finding lang file.
 ^ [track13] - Starting working on beautify SQL function

30-Mar-2007 Rob Schley
 + getInt(), getFloat(), getBool(), getWord(), getCmd() to JRequest
 ! Those methods are just proxies to getVar() for now
 + Implemented 'cmd' filter type to JInputFilter
 ! The 'cmd' filter type allows [A-Za-z0-9_-.]

30-Mar-2007 Toby Patterson
 + setError(), getError(), and getErrors() to JObject
 ! identical methods will be removed from the framework

30-Mar-2007 Johan Janssens
 ^ Implemented auto creating for plugins in JPluginHelper::importPlugin
 ^ Renamed JAuthorize to JAuthorization for consistency

29-Mar-2007 Rob Schley
 * Hardened findOption in JAdministrator
 * Hardened various inputs in JAdministrator

28-Mar-2007 Rob Schley
 # Fixed key reference lookups in com_content
 # Adjusted JRequest to recursively strip slashes in getVar()
 * Hardened various inputs in com_contact
 * Hardened various inputs in com_content and related
 * Hardened key reference filtering
 ! Key References are now limited to A-Za-z0-9.-_
 + Reinstated onAuthenticateFailure event trigger in JAuthenticate

27-Mar-2007 Johan Janssens
 # Fixed inconsistencies in module and individual content URL's
 # Smaller improvements to route creation
 - Removed feed.php file, feeds now use index.php again

25-Mar-2007 Hannes Papenberg
 ^ Finished and improved breadcrumbs refactoring.
 ^ Cleaned up installation SQL and removed old {image} tags, replaced them with the proper HTML tags
 ! Reinstalling is advised!

23-Mar-2007 Andy Miller
 # Fixed Tabs styling
 ^ Changed Message and Error styling
 + Added styling for Legacy mode flag

23-Mar-2007 Rastin Mehr
 # Fixed [track41] No "Copy | Move" buttons on Category Manager [Content] toolbar
 # Fixed [track16] All the ADMIN side list limits are now using 'global.list.limit' session
   name to hold the $limit value

21-Mar-2007 Rastin Mehr
 # Fixed [track99] the sectionid error
 # Fixed [track99] the insert page break error

20-Mar-2007 Rastin Mehr
 # Fixed [track79] Error in Joomla SQL

18-Mar-2007 Toby Patterson
 # Fixed [t147196] Breadcrumbs: Home >> content ? - Yet another fix by Ian!

17-Mar-2007 Jason Kendall
 ^ Updated tcpdf to 1.53.0.TC030 (2007-03-06)
 # Fixed [t151189] Blogger XML-RPC incorrectly authenticating - Thanks Ian

17-Mar-2007 Toby Patterson
 # Fixed [track17] Installation without javascript disabled need warning
 # Fixed [track18] Cannot access Site or Admin after completion of install
 # Fixed [t150452] beez template calls ampReplace()
 # Fixed [t150923] ianmac's fix ContactViewContact

14-Mar-2007 Jason Kendall
 ^ [topic,147016] - Updated lang file to show loadposition not mos...

14-Mar-2007 Toby Patterson
 + Added JLanguage::getPaths()
 ! Merging changes from 10-Mar back into new tree.

12-Mar-2007 Louis Landry
 - Removed Legacy mode global configuration setting
 ^ Set legacy mode by publishing the system legacy plugin

12-Mar-2007 Sam Moffatt
 ! A whole new world.

10-Mar-2007 Toby Patterson
 # Fixed [artf7605] Refactored installer to use MVC, added check for cookies
 ^ new code located in J/installation/instaler/
 ! Still need to complete some cleanup work in the includes dir

05-Mar-2007 Johan Janssens
 ^ Moved ampReplace into JOutputFilter

05-Mar-2007 Enno Klasing
 # Improved "Verify FTP settings" in the installer application
 # Re-enabled FTP layer on Windows servers

04-Mar-2007 Mateusz Krzeszowiec
 # Fixed [artf7594] : Bugs in com_media, add file button now working in IE

03-Mar-2007 Toby Patterson
 # JEditor check for object in JEditor
 # Stop JError from double-translating error messages
 ! Errors should go through JText before JError
 + Added methods to installer classes to display a warning
 ! Still need to get message to display nicely in page.html subtemplate
 ^ Changed JLanguage::languageExists to JLanguage::exists

 03-Mar-2007 Jason Kendall

03-Mar-2007 Jason Kendall
 * Updated JLog to write to a PHP file and include a call to die at the start

03-Mar-2007 Johan Janssens
 ^ Implemented very basic segment encoder and decoder in JRouter

02-Mar-2007 Jason Kendall
 # [artf7559] - missing _resource call in mysql

02-Mar-2007 Johan Janssens
 ^ com_content : Implemented new routing mechanism
 ^ mod_latestnews : Implemented new routing mechanism
 ^ mod_mostread : Implemented new routing mechanism
 ^ mod_newsflash : Implemented new routing mechanism
 ^ Changed references to com_registration to com_user
 ^ Implemented simple routing mechanism in JRouter to handle none Itemid linked URL's

01-Mar-2007 Mateusz Krzeszowiec
 # WIP [artf7592] : Popups of Config don't work in Konqueror, first issue fixed

28-Feb-2007
 # All parameter strings ( labels and descriptions ) should now go through JText
 ^ Further tweaks on JLanguage and identifying a default language

27-Feb-2007 Johan Janssens
 + Added new Beeze accessible template, contributed by the design and accessibility working group

26-Feb-2007 Toby Patterson
 + JLanguage will try to load a default language file ( 'en-GB' ) if a localized version cannot
   be loaded

25-Feb-2007 Johan Janssens
 + Added JPATH_CACHE to the application path defines

24-Feb-2007 Johan Janssens
 ^ Implemented caching for administrator menu module

23-Feb-2007 Sam Moffatt
 # Fixed up LDAP plugin so that it works again with MSAD

22-Feb-2007 Hannes Papenberg
 # Updated TinyMCE to 2.1.0
 # Fixed [artf7574] Frontend article submission layout only allows uncategorized content

21-Feb-2007 Jason Kendall
 # [artf7628] - Rewrote LDAP plugin to be easier to setup and fix authentication issues
 # [artf7623] - Added default (ON) to Show All
 # [artf7617] - Send new password was broken
 # [artf7570] - Incorrect date on new/edit content
 # [artf7578] - Possible problem with JDate (Incorrect handling of timezones)
 + [artf6535] - Added logging to Authentication system
 ^ Changed pagenavigation to use JDate (as per Hannes request)

21-Feb-2007 Hannes Papenberg
 + Added page navigation between articles
 ^ Renamed some parameters in com_content preferences
 ! You need to open the com_content preferences and save them anew or reinstall

20-Feb-2007 Johan Janssens
 + Added page cache system plugin
 - Removed template and page cache global configuration setting.

19-Feb-2007 Hannes Papenberg
 # Fixed [artf7597] Newsfeeds were broken
 # Fixed [artf7603] Error message are now shown above login window in offline mode

18-Feb-2007 Johan Janssens
 ^ Refactored pagination routing

18-Feb-2007 Enno Klasing
 + Installation does not save FTP password to configuration.php anymore by default
 ^ Changed FTP layer to use binary transfer mode by default

18-Feb-2007 Mateusz Krzeszowiec
 # Fixed XStandard not saving changes
 + XStandard now uses template css to render content

18-Feb-2007 Hannes Papenberg
 # Fixed Article buttons in blog_category view
 ^ Removed old code
 ^ getIcon from com_content now allways uses the same parameters

18-Feb-2007 Toby Patterson
 # Fixed [artf7621] [artf7473] Saving user information in the admin application updates the session.
 + Added setLanguage() to JLanguage, no change in functionality
 + Changed key requested in JFactory::_createLanguage from 'debug' to 'debug_lang'

17-Feb-2007 Hannes Papenberg
 # Fixed [artf7595] : com_frontpage reset works again and some smaller improvements
 # Fixed some issues in com_media
 # Renamed the parameter hideAuthor and author in article management and article preferences to showAuthor. Show Author when set to 1
You have to open the article preferences and change the setting to show and save or re-install. This one was necessary because of a naming collision.

16-Feb-2007 Toby Patterson
 # Fixed [artf7616] : Email function of articles doesn't work.
 # User is returned to mailto form when mail cannot be sent.
 + phpmailer language files added; local set in JFactory.

16-Feb-2007 Mateusz Krzeszowiec
 # Removed call to not existing JEditor:getButtons in frontend
 # Fixed [artf7626] : XStandard destroys editor content on save and writes empty strings to db

15-Feb-2007 Mateusz Krzeszowiec
 # Fixed [artf7519] : Images/Pagebreak/Readmore buttons don't work in XStandard
 ^ editors-xtd plugins are now rendered in onDisplay event of editor plugin
 ^ JEditor::display now have additional argument for rendering editors-xtd, default = true
 - Removed JEditor::getButtons

15-Feb-2007 Johan Janssens
 ^ Deprecated sefRelToAbs, use JRoute::_() instead
 ^ Removed Itemid in JRoute implementations, JRouter handles this automatically
 ^ Removed JURI::resolve, moved functionality into JRoute::_()

15-Feb-2007 Laurens Vandeput
 # Fixed [artf7572] : XML-RPC server fixed

15-Feb-2007 Andrew Eddie
 # Error deleting section

13-Feb-2007 Johan Janssens
 ^ com_newsfeeds : Implemented new routing mechanism
 ^ Moved handling of pagination routing into JRouter

13-Feb-2007 Toby Patterson
 # Fixed [t140455] SQL syntax error in JTable::move()

12-Feb-2007 Mateusz Krzeszowiec
 # Fixed [artf7490] : A little reorder required in Edit Contact form

11-Feb-2007 Toby Patterson
 # Fixed [artf7473] Edit your details works
 # Fixed [t129709] admin com_user, returns to edit page when record cannot be saved
 # Fixed [t115840] phputf8 JString::str_ireplace uses iammac's code
 # Various small fixes

11-Feb-2007 Johan Janssens
 ^ com_weblinks : Implemented new routing mechanism

11-Feb-2007 Jason Kendall
 # Fixed [Topic 127883] : First attempt at bug when PHP/Server is compressing as well as Joomla!
 # Fixed [Topic 117691] : Missing Delete Icon in Media Manager
 # Fixed [Topic 136174] : Correct checking of cache
 + Add [Topic 60767] : Add Show all option when articles have page breaks
 # Repaired some damnage to XML-RPC client - Although still broke
 # Implement JError in SimpleXML

11-Feb-2007 Louis Landry
 ^ Upgraded TCPDF

11-Feb-2007 Mateusz Krzeszowiec
 # Fixed [artf7503] : Readmore and Pagebreak don't work in editor in IE7

10-Feb-2007 Hannes Papenberg
 ^ Changed JToolBarHelper::configuration to preferences
 - removed obsolete module position code in com_templates
 - removed Apply button in com_weblinks
 # Fixed print function in com_content
 # Fixed link in sample content
 # Fixed ordering of tables in com_content, com_contact

10-Feb-2007 Enno Klasing
 # chmodding of template files and configuration.php before and after writing to these files
 # Fixed [artf7524] : Missing translations in installation

09-Feb-2007 Enno Klasing
 + Added input form for FTP credentials to com_media

09-Feb-2007 Rastin Mehr
 ^ [artf7503] com_weblinks, com_users, com_search and com_templates on the ADMIN side are now using single quotes instead of double quotes.

08-Feb-2007 Enno Klasing
 + Added input form for FTP credentials to com_templates
 + Added input form for FTP credentials to com_config

06-Feb-2007 Johan Janssens
 ^ Major refactoring of Itemid handling
 	^ Implemented JRouter in application package and improved routing algorithms
 	^ Reworked JMenu class to allow lookups based on itemid routes
 	^ Changed component request.php to route.php to better refect routing system
 	^ Split application render function into route and dispatch
 	+ Added onAfterDispatch and onAfterRoute system events
 	+ Added legacy system plugin to handle legacy routes
 	- Removed joomla.request plugin functionality moved to JRouter
 	- Removed onAfterRender system event
 + Added alias support to com_categories and com_sections
 ! Changes require a reinstall

06-Feb-2007 Toby Patterson
 ^ [artf7502] Removed hidemainmenu URL argument, use setVar() instead

05-Feb-2007 Andrew Eddie
 # Fixed JTable::getNextOrder - fails with no where clause
 # Fixed JObject::getPublicProperties() doesn't give (all) properties of object
 ^ Added JTable::reset to handle resetting class properties and make it accessable at any time
 # Fixed No translation of new module names
 + Added support in JLanguage to log orphaned strings when debug on
 + Added support to Debug Plugin for show logged orphaned strings
 ^ Improved output symmantics of Debug Plugin

03-Feb-2007 Enno Klasing
 + Added input form for FTP credentials to com_languages

03-Feb-2007 Mateusz Krzeszowiec
 + [artf7443] : Missing "Mobile"column in Contact Category Layout Parameters

03-Feb-2007 Sam Moffatt
 # [art7349] Fixed up potential path disclosure issues

02-Feb-2007 Enno Klasing
 + Added JClientHelper class
 + Added input form for FTP credentials to com_installer

01-Feb-2007 Andy Miller
 # fixed layout for frontend editing
 + Added calendar icon instead of ... button

01-Feb-2007 Toby Patterson
 # Fixed [artf7442] : Unable to sort Contacts

01-Feb-2007 Johan Janssens
 ^ Added 'plg' prefix to all plugins functions and classes to avoid naming collisions
 ^ Changed JDocument::display to render, function now return data instead of pushing
   it into JResponse. JResponse handling is done inside JApplication:::render

01-Feb-2007 Marko Schmuck
 # Fixed [artf7454] : TinyMCE parameters smilies and flash where wrong in xml file

01-Feb-2007 Andrew Eddie
 # Fixed [artf7404] : Proposed changes to JDatabase->isQuoted method usage
 # Fixed [artf7077] : updateNulls in Database Library
 ^ Tidied JDatbase::explain output
 - Deprecated the JDatabase::insertObject $verbose (4th) argument
 ^ Added argument to JObject::getPublicProperties to optionally return the get_class_vars assoc array
 ^ Refactored JTable to use JObject::getPublicProperties as appropriate
 # Fixed [artf7200] : Are not translated
 + Added support for template language files

31-Jan-2007 Marko Schmuck
 # Fixed [artf7260][artf7362] : Pressing Save or Apply in template properties cause no default template
 + If no custome theme is in the templates folder _system/component.php will be used

30-Jan-2007 Enno Klasing
 # Fixed various small bugs in JFolder and JFile

29-Jan-2007 Louis Landry
 ^ Mootools implementation stage 1

29-Jan-2007 Toby Patterson
 # categories being edited will be checked-out
 # JInstaller_component - existing menu items are deleted before new ones are added if overwrite has been enabled

28-Jan-2007 Jason Kendall
 # [artf7417] - Unable to set artical access to Special - Thanks Alissa
 # [artf7393] - User Group Edits Not correct

26-Jan-2007 Johan Janssens
 - Removed com_frontpage from site application
 - Removed com_login from site application
 ^ Moved login component functionality into a login view in the user component

25-Jan-2007 Louis Landry
 ^ Refactored caching libraries : Implementation is a WIP

25-Jan-2007 Johan Janssens
 + Added support for multiple module styles to module renderer
 ^ Small improvements to session handling
 ! Set database as default session handler
 # Fixed [artf6372] : jos_sessions table not in sync with $_SESSION sessions
 # Feature request [artf2179] : Database handler for sessions
 # Feature request [artf1831] : Check in content automatically when a user's session ends
 # Feature request [artf5984] : Session cookie not reliant on URL accessed
 ^ Moved site user component to mvc structure, implemented user model
 - Removed checkin link from user menu

25-Jan-2007 Enno Klasing
 # Fixed "Only variable references should be returned by reference" error in JError class
 # Calling __destructor on JDatabase only once on PHP5
 + Added destructor to JFTP
 # Changed JFile and JFolder to connect to a FTP server only once per page request

24-Jan-2007 Jason Kendall
 # Fixed [artf7392]: JUser Helper updates in Registration Component Controller - Thanks jenscki
 # Fixed up the XML-RPC client.php file.

23-Jan-2007 Enno Klasing
 ^ Renamed JFTP->nameList to JFTP->listNames
 ^ Renamed JFTP->listDir to JFTP->listDetails
 # Fixed usage of '/' and '\' in JFile and JFolder when the FTP layer is enabled
 # Improved FTP autofind option
 + Added optional parameters user/pass to JFTP::getInstance

22-Jan-2007 Jason Kendall
 # Fixed [artf7373]: Unable to edit user on front end with PHP5
 # Fixed [artf7346]: Edit users in backend
 # Fixed [artf7351]: Catagory blog layout ordering issue

20-Jan-2007 Johan Janssens
 + Added JPATH_THEMES define
 ^ Improved JTable checkout handling

19-Jan-2007 Andrew Eddie
 ^ JPane methods return string rather than echo (to enable capturing of output if desired)

18-Jan-2007 Hannes Papenberg
 ^ Moved com_statistics functionality to com_search and MVCed it

17-Jan-2007 Rastin Mehr
 # Fixed [artf7187] : Section & pagination problem
 ! Same problem existed in the category blog view. It is fixed now.
 # Fixed [artf7330] : Khepri Template: allow other Prototype-based Javascripts

17-Jan-2007 Louis Landry
 + Added <folder> tag capabilities in JInstaller -- Thanks tcp
 ^ Made changes to path handling to better support different filesystems/platforms
 # Cancel button in editing template source and css returns to correct page
 ^ Moved installer SQL files into driver specific subfolder

17-Jan-2007 Johan Janssens
 # Fixed [artf7320] : Can't login when legacy mode is on

16-Jan-2007 Johan Janssens
 ^ Improved session token handling and spoof checking.
 - Deprecated mosErrorAlert, use JError or JApplication::redirect instead
 # Fixed [artf7293] : JFactory::getURI doesn't use it parameter
 ^ Moved JAuthenticationHelper into JUserHelper and into a seperate file

15-Jan-2007 Enno Klasing
 # Fixed [artf7292] : operator precedence problem in joomla.request.php

15-Jan-2007 Sam Moffatt
 # Fixed up installation bug when installing sample data with joomla_backward.sql
   (backwards compat file had extra entries)

14-Jan-2007 Marko Schmuck
 # Fixed [artf7279] : Newly created article has no "modified" date / user
 # Fixed [artf6301] : Uninstallation error messages remain English

12-Jan-2007 Enno Klasing
 # Removed double slashes from sitename in installtion when magic_quotes_gpc was swiched off

11-Jan-2007 Louis Landry
 ^ Reworked JError to remove dependency on patError/patErrorManager
 ^ JLog now uses the log_path config if not explicitly set
 + Added JFolder::copy() -- Thanks instance
 + Added generic buffer stream handler
 ^ JFTP to use PHP FTP extension if available

11-Jan-2007 Enno Klasing
 + Added optional default parameter to JRegistry->getValue
 # Fixed XHTML compliance for feed links
 # Fixed assignments of variables by reference

10-Jan-2007 Johan Janssens
 + Added Session Handler configuration setting
 ^ Improvements to session handling
 ! Changes require a reinstall

09-Jan-2007 Louis Landry
 ^ Reworked module manager : module edit screen and controls
 ^ Reworked JUser for better session storage
 + Added JApplication::close() method.  This is a session integrity safe application exit method and should be used instead of exit();

09-Jan-2007 Johan Janssens
 # Fixed [artf6614] : JApplicationHelper::getItemid() bug and $mainframe->getItemid() missing
 # Fixed [artf6825] : Hide other modules after using links in the mods 'Latest Items and Most Popular'

08-Jan-2007 Johan Janssens
 # Fixed [artf6692] : XSS vulnerability in title and pathway
 + Added SessionHandler support to JSession
 + Added database session handler
 + Added file session handler
 + Added APC session handler
 + Added eAccelerator session handler
 ! The database is now used as default session store instead of the file system
 ! Changes require a reinstall

07-Jan-2007 Rastin Mehr
 # Fixed [artf6887] : failure/improper assigning of active submenu item
 # Fixed [artf6852] : User registration failing ungracefully
 ^ Terms such as "JUser::save" are removed from the JUser class in libraries/joomla/user/user.php

06-Jan-2007 Mateusz Krzeszowiec
 # Fixed [artf7188] : List categories in section

05-Jan-2007 Mateusz Krzeszowiec
 # Fixed [artf6851] : New use registration problem with numbers in password
 ^ JavaScript form validator now allows all characters in password, password can't start and end with whitespace, length from 4 to 100 chars

05-Jan-2007 Johan Janssens
 - Removed pear archive and mime packages
 - Removed JArchive::create method

04-Jan-2007 Andrew Eddie
 ^ Updated language file headers

04-Jan-2007 Louis Landry
 ^ Refactor of Installer libraries to use composition instead of inheritance
 + Added new archive libraries to better support zip|gzip|tar file extraction
 ! Two new config vars tmp_path and log_path will require existing installs to edit global config and set them
 + Added bzip2 support to archive libraries if php bz2 extension is available

03-Jan-2007 Andrew Eddie
 # Fixed bug in menu item copy which results in multiple items set to home
 # Reworked Menu Selection lists in Edit Module and Edit Template pages

03-Jan-2007 Andy Miller
 # Fixed left column always taking up 20% in milkyway
 ^ Removed 1px black border from polls
 # Fixed alignment of polls with Opera

03-Jan-2007 Johan Janssens
 # Fixed [artf7120] : Changing IP adresses of users through proxys

02-Jan-2007 Hannes Papenberg
 # Fixed com_contact mail form
 # Fixed com_massmail

02-Jan-2007 Johan Janssens
 ^ Changed JFactory::getUser, the user object is now stored completely in the session
 + Added 'auto register' setting to the Joomla! user plugin. When turned off it allows the system
   to have temporary logged in users. This setting is experimental and should be used with care.
 # Fix various session related problems
 + Added 'aid' dynamic variable to the user object, this acts as the 'gid' variable in 1.0
 + Added 'guest' dynamic variable to the user object, when a user is logged in guest is set to 0.
 ! Note : changes require a new install and sessions need to be cleaned

02-Jan-2007 Andrew Eddie
 + Added JController::setMessage
 + Added advanced params support for modules
 ^ Tidied controllers and error handling in admin com_banners
 ^ Tidied controllers and error handling in admin com_modules
 + Added path plg_xml for use in leiu of bot_xml
 # Fixed bug in mysqli __deconstruct method (was not closing the connection at all)
 ^ Cleaned up up whitespace
 ^ Updated copyright notices for 2007

01-Jan-2007 Hannes Papenberg
 ^ Changed Meta-Informations gathering in article creation

01-Jan-2007 Andrew Eddie
 ^ Turned submenu off when hidemainmenu not zero

31-Dec-2006 Mateusz Krzeszowiec
 # Fixed [artf6736] : "Insert Image" window doesn't display cursor properly on certain text

30-Dec-2006 Mateusz Krzeszowiec
 # Fixed [artf6631] : Re-editing a page confuses the category drop-down list

30-Dec-2006 Johan Janssens
 # Fixed [artf7073] : Username length

30-Dec-2006 Andrew Eddie
 # Made favicon into an absolute path to overcome an odd bug with IE7

29-Dec-2006 Andrew Eddie
 # Fixed JRequest::clean being fired too late in the execution
 # Fixed bug in SEF internal string cache

28-Dec-2006 Johan Janssens
 + Added JResponse class to environment package
 + Added debug system plugin to handle debug info
 ^ Cleaned up index.php application entry files
 - Removed $_VERSION global, use JVersion instead
 ! Note : changes require a reinstall

28-Dec-2006 Louis Landry
 # Fixed problem with nested menu item removal
 ^ HTTP header SEO patch : Joomlatwork

28-Dec-2006 Andrew Eddie
 # Removed restriction for removing core modules from the Modules Manager
 # Added 'relative image paths' setting to TinyMCE

27-Dec-2006 Hannes Papenberg
 # Fixed [6853] : Error when new user activates account
 # Fixed [6262] : Incorrect description on the Lost your password page

27-Dec-2006 Johan Janssens
 + Added administrator feed module
 ^ Changed _J_ALLOWRAW to JREQUEST_ALLOWRAW to suit naming conventions and to avoid naming conflicts
 ^ Changed _J_ALLOWHTML to JREQUEST_ALLOWHTML to suit naming conventions and to avoid naming conflicts
 ^ Changed _J_NOTRIM to JREQUEST_NOTRIM to suit naming conventions and to avoid naming conflicts

26-Dec-2006 Andrew Eddie
 # Moved onBeforeDisplay and onAfterDisplay triggers close to the actually application display method

21-Dec-2006 Andrew Eddie
 # Fixed [6819] : Errors on Trash Manager
 # Fixed [6537] : Admin Template (ul classes being incorrectly assigned a translated string)
 # Fixed bug: JDate not defined when saving a frontend article
 # Fixed bug: removed case sensitivity when loading modules by position

19-Dec-2006 Rastin Mehr
 # Fixed [artf7004] : Apostrophes in poll choices shows escape character

19-Dec-2006 Andrew Eddie
 # Fixed bug in patTemplate shortModifier tags (patch submitted to author)
 # Added patch to patErrorManager where a class method handler is not found
 # Fixed [6774] : Moving a submenu to another menu by editing "Display in"
 # Fixed [6550] : CVS directories not ignored
 # Fixed [6700] : Menu Manager - Bug in new menu creation
 # Fixed bug preventing language files correctly loading in Legacy Mode
 ^ Removed JPath:check calls in methods JPath, JFiles, and JFolder methods (too tightly coupled to Joomla install)
 # Fixed [6414] : Template installation from directory requires trailing slash
 # Fixed [6678] : Delete Template Fails
 # Fixed [7030] : Missing function reference in installer.php
 # Fixed bug in Banners list view, category filter throwing error due to incorrect field name
 + Added "Tags" column to Banners list view
 # Fixed [6597] : Polls: Poll is assigned to menu both by module and by poll component
 # Fixed [7050] : backend localization was gone in rev. 6013
 # Fixed lingering &josmsg=??? in page redirect methods
 ^ Removed annoying js confirmation when you try to empty trash
 # Fixed [6362] : Untranslated error message
 # Removed upload buttons from Banner and Section edit forms

18-Dec-2006 Rastin Mehr
 # Fixed [6836] - Broken images get displayed in newsfeed categories (patch)

17-Dec-2006 Johan Janssens
 + Added PHP OpenID library to the framework
 + Added Joomla! User plugin, moved login, logout and sessions cleaning into this plugin
 + Added OpenID authentication plugin (based on work from Rob and Jason Kendall)
 ^ Refactord authentication and user plugins to improve flexibility and decoupling
 ! Changes require a reinstall

17-Dec-2006 Rastin Mehr
 # Fixed [6555] - Make unwritable after saving does not work
 + Added ability to enable or disable writing into the application configruation.php file

14-Dec-2006 Johan Janssens
 ^ Deprecated mosGetOrderingList, use JAdminMenus::GenericOrdering instead

13-Dec-2006 Andy Miller
 + Added color variations and width template parameters to Milkyway template

12-Dec-2006 Andy Miller
 ^ Milkyway Template - Removed Quirks mode requirement for < IE6, minor tweaks for IE7

12-Dec-2006 Johan Janssens
 # Fixed [artf7014] : Sample data installs othermenu with legacy style
 # Fixed [artf7008] : Docstring, code format cleanup on application/application.php
 - Removed patTemplate engine from JDocumentError

11-Dec-2006 Hannes Papenberg
 # Fixed [artf7017] : [patch] mod_footer fails to load JDate before use (@ SVN 5956)
 # Fixed [artf7013] : Add content publish end time not multilingual compliant

07-Dec-2006 Sam Moffatt
 ^ LDAP: Added translation strings to the appropriate location (language and XML file)
 ! LDAP: Tested all features successfully against a Novell eDirectory LDAP server
 # Fixed problem where session table was being ignored (could not end a users session via user manager)
 # Fixed problem where user deletion didn't destroy sessions (see next)
 + Added JUserHelper::getUserName($uid)
 # Added joomla.i18n.language to joomla.utilies.error to ensure JText is available

06-Dec-2006 Sam Moffatt
 ^ Making some alterations to LDAP authentication systems to improve them
 + Added Anonymous Compare and Authorized Compare authentication options for LDAP bot (in addition to Bind as User)
 + Added some mapping tools

06-Dec-2006 Andrew Eddie
 ^ Added ability to describe an abitrary path for the configuration xml file for a component using com_config

04-Dec-2006 Johan Janssens
 + Added JElement_Timezones
 + Added Timezone settings to user parameters
 ! Timezone language strings haven been moved from the config to the main ini file

02-Dec-2006 Johan Janssens
 + Enabled module caching
 ^ Improved JFactory::getEditor method, added support for 'force' loading an editor

01-Dec-2006 Johan Janssens
 + Added new cpanel component to handle the control panel in the administrator
 ^ Renamed JDocument setInclude and getInclude functions to setBuffer and getBuffer
 ! Changes require a reinstall

01-Dec-2006 Andrew Eddie
 # Shuffled some modules out of joomla.sql into sample data to make a clean install, well, cleaner

30-Nov-2006 Johan Janssens
 # Fixed [artf6932] : use of unassigned variable in weblink model

30-Nov-2006 Andrew Eddie
 + Added JController::getTasks - returns a list of the available tasks in a controller

29-Nov-2006 Johan Janssens
 ^ Moved JString class to utilities package
 - Removed language loading from JPluginHelper::_import function. If a plugin needs
   to load a language he should load it himself.

28-Nov-2006 Johan Janssens
 ^ Components are now rendered by the application and not by the template

27-Nov-2006 Louis Landry
 - Removed MagPie Feed parser
 + Added SimplePie Feed parser (suggested by Jason Kendall)
 ! Much improved feed parsing and feed support

27-Nov-2006 Johan Janssens
 ^ Weblinks component cleanup and code improvements
 ^ Changed JSiteHelper::getCurrentMenuItem to JSiteHelper::getActiveMenuItem
 ^ JURI class cleanup
 ^ Moved JApplicationHelper to a seperate file

26-Nov-2006 Johan Janssnes
 ^ Added fix to JURI::base to solves issues on Apache CGI

25-Nov-2006 Johan Janssens
 ^ Improvements to search engine friendly url handling
 ! Changes require a reinstall

24-Nov-2006 Louis Landry
 + Added ability to delete multiple files at a time in media manager
 # Fixed mediamanager javascript error

24-Nov-2006 Johan Janssens
 # Fixed [artf6823] : Category order in Frontpage blog layout
 # Fixed [artf6427] : Fatal error: Only variables can be passed by reference
 # Fixed [artf6824] : Ordering articles on frontpage.
 # Fixed [artf6813] : Replaced PHP short tags
 # Fixed [artf6785] : section blog layout: table not closed when pagination_results = 0
 # Fixed [artf6725] : a template with an hyphen in it's name cannot be set to default
 # Fixed [artf6741] : Button 'Next' in com_modules is missing
 # Fixed [artf6811] : com_users/admin.users.php
 # Fixed [artf6782] : "Order By " ignored in standard category layout menu item
 # Fixed [artf6795] : Pagination missing from search results
 # Fixed [artf6838] : "Passwords do not match" JS popup when changing passwords
 # Fixed [artf6726] : Start and finish publishing are modified unexpectedly
 # Fixed [artf6632] : "Start Publishing" date drifts forward in time
 # Fixed [artf4580] : Every save|apply to content item add 3 hours

23-Nov-2006 Johan Janssens
 ^ Changed content models to dynamicaly create article slugs
 ^ Implemented article slug rendering in content views
 - Removed visitor plugin
 - Removed visitor statistics
 ! Changes require a reinstall

22-Nov-2006 Johan Janssens
 + Added stringURLSafe function to JOutputFilter
 + Added dynamic alias creation to com_content
 + Added sef handlers to com_content
 ^ Deprecated mosMakeHTMLSafe, use JOutputFilter::objectHTMLSafe instead
 - Removed article statistics manager, added hit count to article manager

22-Nov-2006 Andrew Eddie
 ^ Allowed menu name fields to have basic html tags to allow for dynamic styling (to a degree) of menu items

21-Nov-2006 Andrew Eddie
 # Fixed bug in JModel where JPATH_COMPONENT* has not been loaded
 ^ Changed handling of request data in backend menu model.  Now pushed into model to allow for 3rd parties to use the model directly to create menus.
 ^ Changed JController::setRedirect.  If null message is passed then no change is made to the internal variable.  This allows for setting on the message directly in complicated scripts.
 ^ Added argument to JArrayHelper::toObject to allow a JObject to be returned

21-Nov-2006 Johan Janssens
 ^ Refactored administrator weblinks component to use MVC component framework

20-Nov-2006 Sam Moffatt
 ^ Altered behaviour of JHTMLSelect::genericList to accept nonlinear arrays

16-Nov-2006 Johan Janssens
 - Removed JController::setViewName, use JController::getView instead
 + Implemented factory method in JController::getView to be able to handle multiple views
 + Added JPath::find function to easily search a array of paths for a certain file
 ^ Switched parameter order in the JController::getView function
 ^ General MVC improvements to simplify component implementations that follow conventions

15-Nov-2006 Andrew Eddie
 ^ JTable::addTableDir -> JTable::addIncludePath
 + JModel::addIncludePath and fixed JModel::getInstance to suit

14-Nov-2006 Rastin Mehr
 # Fixed [artf6679] : 3 bad email bugs causing emails mixups (with fixes) including bug #4

14-Nov-2006 Andrew Eddie
 # Fixed js error in TinyMCE editor when Template CSS set to No

11-Nov-2006 Louis Landry
 # Fixed feeds not rendering with SEF URLs enabled

11-Nov-2006 Johan Janssens
 # Fixed [artf6651] : legacy mode failed when $mainframe is called directly from component 1.0 style
 # Fixed [artf6531] : Legacy notes
 # Fixed [artf6394] : Notice: Undefined property: ContentViewCategory::$lists
 # Fixed [artf6633] : Notice: Undefined property: JTableContent::$publised_down
 # Fixed [artf6644] : Section Blog Layout doesn't paint contents
 # Fixed [artf6424] : Backend- Accessing Menu options from Admn panel
 # Fixed [artf6378] : contact: Using invalid Itemid cause call to undefined function
 # Fixed [artf6630] : Multi-page navigation broken
 # Fixed [artf6496] : The pagination is broken in view=section&layout=blog
 # Fixed [artf6478] : Dropdown menu's "Display #" in o.a. FAQ don't work
 # Fixed [artf6685] : Display # -Picklist-
 # Fixed [artf6558] : mod_custom have no contents
 # Fixed [artf6654] : Section description doesn't saves in HTML format
 # Fixed [artf6426] : Just a warning while debugging is on
 # Fixed [artf6385] : Weblinks, JS Error: form.filter_order_Dir has no properties

10-Nov-2006 Rastin Mehr
 # Fixed [artf6564] : Can't hide email form
 ! Note  [artf6516] : much of the & has been replace with &amp; in all the ADMIN components. I think this issue
 	has to be treated as a coding practice not a bug.

09-Nov-2006 Johan Janssens
 + Added JPATH_COMPONENT_SITE define
 + Added JPATH_COMPONENT_ADMINISTRATOR define
 ^ Changed order of parameters in the JTable::getInstance function.

09-Nov-2006 Hannes Papenberg
 + Added parameter for breadcrumbs module to set the string for the home entry
 # Fixed all ampersands in URLs in components and modules to use &amp;

09-Nov-2006 Andrew Eddie
 + Added protected var JController::_doTask to record the actual mapped task executed
 + Added reserved state variable 'task' to JController::getModel

07-Nov-2006 Hannes Papenberg
 + Added configuration screen for massmail with Subjectprefix and Mailbodysuffix

07-Nov-2006 Johan Janssens
 + Added JPATH_PLUGINS define

06-Nov-2006 Johan Janssens
 # Fixed help file handling for components
 ^ Upgraded TinyMCE Compressor [1.0.9]
 ^ Upgraded TinyMCE [2.0.8]

06-Nov-2006 Andrew Eddie
 # Removed trim operation from values when parsing ini data - fixes problems in class suffixes
 # Fixed syntax error in offline system template

05-Nov-2006 Enno Klasing
 # Fixed weblink "target" settings not making it into the URL

04-Nov-2006 Louis Landry
 # Fixed loadposition content plugin
 # Fixed error in legacy mosParameters class - Thanks Geraint
 # Fixed pagination issue not using default link value - Thanks tcp

04-Nov-2006 Hannes Papenberg
 + Adding _JEXEC check to all views
 + Adding configuration to com_content and com_users
 - Removed content and user setting from global configuration
 - Removed references to index3.php
 # Fixed com_config to only save config relevant values
 ^ Cleaned up com_statistics a bit

04-Nov-2006 Louis Landry
 # Fixed loadposition content plugin
 # Fixed error in legacy mosParameters class - Thanks Geraint
 # Fixed pagination issue not using default link value - Thanks tcp

04-Nov-2006 Hannes Papenberg
 + Adding _JEXEC check to all views

03-Nov-2006 Enno Klasing
 # Fixed small table bug while rendering section and category blog
 # Added missing index.html files
 # Fixed Backend, Help screen: Changelog isn't displayed correctly
 # Fixed irregular output in contacts

01-Nov-2006 Louis Landry
 # Fixed bug in rendering custom modules

01-Nov-2006 Andrew Eddie
 # Fixed bug in phputf8 native library: case conversions arrays were not global enforced
 # Fixed bug in JModuleHelper::_load that overwrites the user object

30-Oct-2006 David Gal
 # Fixed a utf-8 non-compatible string function in registry formating function
 # Fixed logical bug in sql file upload in installation (chmod tmp before upload)

29-Oct-2006 David Gal
 # Fixed mod_mainmenu to build menu tree correctly even when child-id is less than parent-id

28-Oct-2006 David Gal
 # Fixed ordering functionality in adminlists
 # Fixed several migration bugs

28-Oct-2006 Louis Landry
 ^ Removed FTP layer from filesystem read methods -- not necessary

28-Oct-2006 Laurens Vandeput
 # Fixed 5578: fatal errors on content
 # Contact search plugin href fixed
 # Minor bugs fixed

26-Oct-2006 David Gal
 # Fixed [artf6511] : Pagebreak in content truncates PDF output
 # Fixed syndication article link error

26-Oct-2006 Rastin Mehr
 # Fixed [artf6547] : Error if enable readmore in newflash

25-Oct-2006 David Gal
 # Fixed menu item creator link for weblink category
 # Fixed menu item creator link for newsfeed category
 # Fixed [artf6413] migration conversion errors regarding menu items
 - Removed privileges check from step 4 of installation. If db creation fails a more detailed error message is provided
 # Fixed ordering of new menu items

25-Oct-2006 Rastin Mehr
 # Fixed [artf6375] : Starting poll, title: test, options: 1, 2 & 3 -> delete crashes Joomla
 # Fixed [artf6383] : Description testarea is a bit wide on Web Link Edit page

24-Oct-2006 Rastin Mehr
 # Fixed [artf6462] : File Size Upload Issues
 	^ - File size limit now displays on the legend, representing the sum of all the files being uploaded
 	^ - Add File button is now next to the upload files
 	! - Testing on IE6, and IE7 has to be done. I am using a Mac, somebody else please take over
 # Fixed [art6481] : undefined vars + typos

23-Oct-2006 Johan Janssens
 ^ Overhaulted site template implementation.
 	- removed patTemplate engine to improve speed and flexibility
 	- removed jdoc::exist ... />, use countModules function instead
 	- removed <jodc::empty ... />, use countModules function instead
 	- <jdoc::inlcude /> attributes are now also passed to the module chrome functions

23-Oct-2006 Hannes Papenberg
 # Fixed [artf6360] : 'Home' should be translated
 # Fixed [artf6364] : Untranslated elements in the breadcrumbs

22-Oct-2006 Johan Janssens
 ^ Deprecated JHTML::makeOption, use JHTMLSelect::option instead
 ^ Deprecated JHTML::selectList, use JHTMLSelect::genericList instead
 ^ Deprecated JHTML::integerSelectList, use JHTMLSelect::integerList instead
 ^ Deprecated JHTML::radioList, use JHTMLSelect::radioList instead
 ^ Deprecated JHTML::yesnoRadioList, use JHTMLSelect::yesnoList instead

21-Oct-2006 Hannes Papenberg
 + Added function to check integrity of core files
 + Added welcome module in backend in sample data
 ^ Cleaned up indexes of module table

20-Oct-2006 David Gal
 ^ Restored help system that was disabled for Beta 1 release

19-Oct-2006 Johan Janssens
 # Fixed [artf6475] : legacy: mosMainFrame::getBasePath() incorrect when called from admin

18-Oct-2006 Sam Moffatt
 # Fixed issue where GMail autocreate fails.

17-Oct-2006 Johan Janssens
 # Fixed [artf6428] : Category Blog Problem

17-Oct-2006 Rastin Mehr
 # Fixed [artf6313] : Returning on first page after saving module
 # Fixed [artf6386] : "Category Table" ordering fails w/ "Unknown table 'f'"

15-Oct-2006 Enno Klasing
 # Fixed [artf6430] : htaccess tweak

14-Oct-2006 Johan Janssens
 # Fixed [artf6388] : Enable Legacy Mode ERROR
 # Fixed [artf6398] : fatal error on Enable Legacy Mode

13-Oct-2006 Rastin Mehr
 # Fixed [artf6387] : error.css-typo

-------------------- 1.5.0 Beta Released [12-Oct-2006] ------------------------

12-Oct-2006 Johan Janssens
 # Fixed [artf6369] : New menu on Component doesn't work with Community Buider
 # Fixed [artf4245] : Installer: FTP auto-detector adds wrong / at the begining
 # Fixed [artf6321] : Login button for admin doesn't work

12-Oct-2006 Alex Kempkens
 # Fixed missing variable in search helper
 # Removed unneeded parameter in admin.sections, contact
 # Fixed pw check in user frontend
 # Fixed missing moduleclass_sfx in mod_feed
 # corrected spellings mistakes in modules var declarations

11-Oct-2006 Rastin Mehr
 # Fixed [artf6260] "Email this link to" is not working

11-Oct-2006 David Gal
 + Added new sample data for Beta1 release

11-Oct-2006 Rastin Mehr
 # Fixed [artf6257] : Menu Manager allows to save incomplete menu items

12-Oct-2006 Johan Janssens
 # Fixed [artf6358] : Incorrect mod_title description

11-Oct-2006 Andy Miller
 # Fixed pillmenu colors and hover

11-Oct-2006 Emir Sakic
 ^ Added installation language file for Bosnian
 # Typo in English installation language file

10-Oct-2006 Johan Janssens
 # Fixed [artf6326] : legacy mosConfig missing as GLOBALS in legacy mode
 # Fixed [artf6320] : web-link is locked after close
 # Fixed [artf6340] : Menu Manager window caption is incorrect
 # Fixed [artf6353] : IIS 6.0 JURI::base() SCRIPT_FILENAME
 # Fixed [artf5734] : Step 4 of installation - UI improvements needed

10-Oct-2006 Rastin Mehr
 # Fixed [art6319] : Cannot select section for Category

10-Oct-2006 Andy Miller
 # Fixed system-message styling

10-Oct-2006 Hannes Papenberg
 # Fixed [artf6259] : Untranslated strings on Cache page
 # Fixed [artf6235] : Untranslated strings in mod_latestnews
 # Fixed [artf6338] : Untranslated string in Write Message
 # Fixed [artf6245] : Untranslated string in Newsfeed Manager
 # Fixed [artf6277] : Untranslated button captions
 # Fixed [artf6332] : Unlocalized strings in Statistics

09-Oct-2006 David Gal
 # Fixed presentation of languages in extension manager. Only default language are disabled now
 # Fixed language pack removal
 + Added RTL support for com_menu 'new menu' tree structure

07-Oct-2006 Johan Janssens
 ^ Deprecated mosHTML class, use JHTML instead
 ^ Deprecated mosCommonHTML class, use JCommonHTML instead
 ^ Deprecated mosAdminMenus class, use JAdminMenus instead
 # Fixed [artf6312] : Wrong link on Categories Quick Icon
 # Fixed [artf6311] : com_contact (backend): old-style links
 # Fixed [artf6248] : Logged in users from backend are not shown in cpanel
 # Fixed [artf6309] : mod_latest(backend)
 # Fixed [artf6308] : mod_popular (backend): Still linked on com_typedcontent
 # Fixed [artf6310] : mod_stats (backend): old-style link

07-Oct-2006 David Gal
 + Added provisions for localisation of TinyMCE and JS Calendar (to be included only in localised releases)
 + Added provisions for defaulting installation, site and admin languages in localised packages of Joomla!

06-Oct-2006 Johan Janssens
 - Removed system error message from configuration, unused in 1.5
 # Fixed [artf6254] : Launching search from the menu
 # Fixed [artf6269] : Extension Manager Page Title is not translated
 # Fixed [artf6268] : Extension types remain English in install message
 # Fixed [artf6231] : Frontend: Register button not translated
 # Fixed [artf6232] : Frontend: Send button not translated

05-Oct-2006 Louis Landry
 + Added legacy patFactory class to legacy classes -- Thanks to pollen8 (Rob) --

05-Oct-2006 David Gal
 ^ Fixed front end user parameter editing in "My Details" and added access level filtering of parameters

05-Oct-2006 Andy Miller
 ^ Updated on mod_menu tree css and images

05-Oct-2006 Hannes Papenberg
 # Fixed [artf6246] : [5297] Calling frontend component includes toolbar...
 # Fixed [artf6233] : Blog view does not show any content
 # Fixed [artf6159] : rev. 5220 : "Blog" link displays nothing
 # Fixed [artf6252] : Editor cannot be called on FAQ and Latest News
 # Fixed [artf6247] : clicking submit menu link creates error

04-Oct-2006 Laurens vandeput
 # Fixed XML-RPC plugins (Joomla, Blogger, MetaWeblog) to fit in the new API.
 # Fixed [artf5320] : if URL is missing, http:// will not be attached (com_content -> ContactTable).

03-Oct-2006 Johan Janssens
 + Added new javascript driven image caption solution
 # Fixed [artf6226] : Mini green arrows stay turned down on right panel tabs.

03-Oct-2006 Hannes Papenberg
 # Fixed [artf6199] : Missing image on Edit Category page
 ^ Removed all references to index2.php out of core components and modules

02-Oct-2006 Louis Landry
 ^ Consolidated admin mod_cssmenu into mod_menu
 - Removed mod_fullmenu

02-Oct-2006 Hannes Papenberg
 # Fixed [artf6207] : image preview error when editing a contact
 # Fixed [artf6193] : mod_mainmenu: child nodes don't render correcty
 # Fixed [artf6212] : Trash Managers should be differentiated

02-Oct-2006 David Gal
 - Disabled the entire help system for Beta 1. Will be reimplemented in Beta 2 when pages are updated.

02-Oct-2006 Sam Moffatt
 + Added networkAddress translation function to JLDAP care of Jay Burrell, Mississippi State University

01-Oct-2006 Louis Landry
 # Fixed [artf5535] : popup.js gets blocked by Adblock filters

30-Sep-2006 David Gal
 # Fixed JFile and JFolder delete functions for restrictive persmissions
 ^ Made changes to Pear/Archive library to support use with safe mode on

30-Sep-2006 Sam Moffatt
 # Fixed up GMail plugin not setting fullName in JAuthenticateResponse causing JUser to fail on autocreate

29-Sep-2006 Louis Landry
 # [artf6161] Menu Item "Separator": Wrong Link in Frontend
 ^ Removed level number class from main menu rendering -- redundant classitis --

29-Sep-2006 Sam Moffatt
 # Fixed up default DN setting in LDAP Library
 + Added LDAP user autocreation as an option
 + Extended JAuthenticateResponse to include more information for user autocreation
 ^ Cleaned up some of the autocreation detection system
 ^ Enabled more advanced autocreation

28-Sep-2006 Johan Janssens
 ^ Update XStandard plugin to version 1.7.1

28-Sep-2006 Hannes Papenberg
 # Fixed [artf6006] : Removed the broken Preview button in Template Manager and fixed the template preview

27-Sep-2006 Johan Janssens
 # Fixed [artf4427] : Language uninstallation problems
 ^ Renamed presenation framework package to html
 # Fixed [artf6059] : Pressing enter doesn't submit admin login form

26-Sep-2006 David Gal
 + Added RTL parameter to TinyMCE xml file

25-Sep-2006 David Gal
 ^ Removed Tar and PCL archive libraries (GPL) from the framework and implemented pear.File_Archive (LGPL) instead
   The PCL and Tar libraries are maintained for BC
 + Installer and data migration can now unpack .zip .gz .tar and .tar.gz archives

25-Sep-2006 Johan Janssens
 ^ Created application subpackages for component, plugin and module
 ^ Moved MVC classes into application component specific subpackage
 # Fixed [artf5801] : Edit Menu Item will download PHP File
 # Fixed [artf6078] : Admin Login Broken
 # Fixed [artf6091] : error when make an article
 # Fixed [artf6082] : Offline Message
 # Fixed [artf5449] : Notice and error messages during install
 # Fixed [artf2771] : css needs hover on input buttons for default template
 # Fixed [artf5424] : PHP5 passed by reference error
 # Fixed [artf5239] : RSS: Cannot modify header information
 # Fixed [artf6089] : Installer title is not included in the language file

23-Sep-2006 Johan Janssens
 # Fixed [artf4085] : install components fail with a create directory failure notice
 # Fixed [artf5430] : joomla not working with Opera
 # Fixed [artf4440] : icons missing
 # Fixed [artf5834] : inconsistency with module suffix
 # Fixed multiple bugs - Contributed by Hannes Papenberg
 ^ Moved user classes into it's own package

22-Sep-2006 Johan Janssens
 ^ Refactored JSession class to improve session handling
 + Added JFactory::getSession to return a session object
 - Removed JUtility::spookCheck, JSessions checks for spoofing attacks transparently
 ^ Added security measures to prevent session hijacking
 # Fixed [artf6051] : Fatal error when trying to save a new article!
 # Fixed [artf5240] : feed.php download instead of feed display

17-Sep-2006 Johan Janssens
 ^ Deprecated mosFormatDate function, use mosHTML::Date instead
 ^ Deprecated mosCurrentDate function, use mosHTML::Date instead

14-Sep-2006 Johan Janssens
 ^ Deprecated JTable::filter function, always use JRequest to filter input data
 # Fixed [artf5942] : Undefined index: ftpUser
 # Fixed [artf5939] : "Step 6" throws notices on missing FTP vars

10-Sep-2006 David Gal
 ^ FTP configuration step in Installation is skipped for Windows host.
 ^ FTP configuration pane is disabled in global configuration for Windows hosts

10-Sep-2006 Emir sakic
 # Fixed number of links displayed on frontpage to be in accordance with parameters settings

06-Sep-2006 Louis Landry
 - Removed globals.php
 + Added JRequest::clean() to replace globals.php include

06-Sep-2006 Andrew Eddie
 # Fixed [artf5852] : escaped character '
 + Added ordering and user filters to Latest News module

06-Sep-2006 Enno Klasing
 - Removed recommended setting for "magic_quotes_gpc" from installation

05-Sep-2006 David Gal
 # Fixed [artf5822] : Configuration popups are not translated
 # Fixed [artf5711] : Vars in configuration.html (installation) are not translated
                      This resulted in an update to en-GB.ini in installation
 # Fixed [artf4940] : Localisations in menu wizard
 # Fixed "L10n tagged" untranslated strings

04-Sep-2006 Andrew Eddie
 # Fixed [artf5805] : non-existent class: jtablemenutypes
 # Fixed [artf3499] : error while creating configuration.php
 # Fixed [artf5806] : non-existent class: jtablemenutypes
 # Fixed [artf5533] : Backend errors (with maximum Server error reporting)
 # Fixed [artf1994] : User mgmt: Bug or feature?

01-Sep-2006 Johan Janssens
 + Added isCompatible version check to JVersion (Suggested by CirTap)
 # Fixed [artf5705] : jimport() and * wildcard
 # Fixed [artf4720] : Content item editing bug
 # Fixed [artf5784] : L10N: term "Component" in /application/extension/component.php
 # Fixed [artf5433] : Replace the old Save and Cancel button on front-end
 # Fixed [artf4794] : URL of non-existent item gives no feedback
 # Fixed [artf3522] : "Database down" error message missing in backend.
 # Fixed [artf4299] : When MySQL not started, backend doesn't detect correctly

31-Aug-2006 Andrew Eddie
 ^ Spruced up 403, 404 and 500 (designs by Arno Zijlstra)
 ^ If a content item is not found, site will throw a 404 error
 + Admin content list - Added ability to search for ID in filter box
 ^ JRegistryFormatINI::stringToObject - remove option to return as an array and optimised string handling

27-Aug-2006 David Gal
 + Added missing localisation of 'site offline' and 'site error' messages
   (there are changes to installation language files)
 ^ Changed alert on no collation selection to a confirm dialog
 # Fixed missing localisations in installation

24-Aug-2006 Alex Kempkens
 # Fixed bug in JDatabase::loadRowList where passed key is zero
 # JDatabase::loadRowList changed to use mysql*_get_row instead of mysql*_get_assoc

23-Aug-2006 Alex Kempkens
 # Fixed : Plugin installer error messages changed to plugins
 ^ Refactored administrative forms to use index.php instead of index2.php (correct debuggin in Zend)
 # Fixed : Application::setLanguage allows to really set a new language
 # Fixed : Incorrect folder in content  metafile

23-Aug-2006 Johan Janssens
 # Fixed [artf5594] : Error saving Articles (non-existent class: jtablefrontpage)
 # Fixed [artf5350] : error when click on 'Tools>New Messages'
 # Fixed [artf5354] : Publishing the 'Statistics' module kills the frontend
 # Fixed [artf5223] : [com_categories] Toolbar malfunction
 # Fixed [artf5243] : Call to a member function on a non-object
 # Fixed [artf4932] : Component files in template issue
 # Fixed [artf5241] : Error after logging in
 # Fixed [artf5423] : JLoader::_requireOnce incorrectly named

20-Aug-2006 Louis Landry
 ^ josURL changed to JURI::resolve
 # Fixed artf5539 : com_uninstall() function never called
 # Fixed artf5536 : __HTTP_SESSION_IDLE issues in libraries/joomla/environment/session.php
 # Fixed artf5427 : JFile::write always return true with ftp flag

20-Aug-2006 Andrew Eddie
 + JError:isError will also recongnise PHP 5 Exception objects as errors
 ^ JDatabase __construct sets human readable error message
 ^ JCache::setCaching now returns the old value before it was set with the new value

19-Aug-2006 Louis Landry
 + Added ability for template designers to add template specific module chrome to a rendered module

19-Aug-2006 Andrew Eddie
 + Added new sort and filter options to admin/mod_latest

18-Aug-2006 David Gal
 + Added cpanel module sliders in admin. Needs re-install for db changes to apply

15-Aug-2006 Alex Kempkens
  ^ Handling to allow that help files do not exists. Now fall back to English help

15-Aug-2006
 # Fixed remote execution issue in PEAR.php
 + Added copy task for banners
 + Added human readable alias's to the module styles -3=rounded, -2=xhtml, -1=raw, 1=horiz

14-Aug-2006 Louis Landry
 # Fixed Broken getDBO() calls
 # Fixed JTable inclusion issues
 ^ Added global legacy setting for enabling 'legacy mode'

14-Aug-2006 Alex Kempkens
 # Fixed [artf5604] Install from dir does not work
 # Fixed issues with the installer and cleaned pathes
 # Fixed help integration and made it language aware
 # Language awareness of the system help screens

14-Aug-2006 David Gal
 # Fixed broken pdf generation
 # Fixed NBSP artifacts in generated pdf
 + Added db user privilege checking during dbConfig in installation

14-Aug-2006 Andrew Eddie
 # Fixed Injection attack on content submissions where frontpage is selected
 # Fixed possible injection attack thru JPagination constructor
 # Fixed possible injection attack thru saveOrder functions

12-Aug-2006 Andrew Eddie
 ^ JMail::Send now returns a JError object on a failed send
 # Set the language path in the JMail constructor as relative path in phpMailer is causing problems

12-Aug-2006 Louis Landry
 ^ Deprecated InputFilter library, use JInputFilter instead
 ^ Removed josRedirect, use JApplication::redirect() instead
 + Added system message queue that persists redirects

12-Aug-2006 David Gal
 # Fixed installation db error messaging during db creation

10-Aug-2006 Louis Landry
 + Added error stack for error handling and error renderer for JDocument
 # Unable to change component in menu type
 # Strings from component xml file not translated

09-Aug-2006 Johan Janssens
 ^ Seperated logic and presentation in site modules

08-Aug-2006 Louis Landry
 + Added filter library for input and output filtering

08-Aug-2006 Enno Klasing
 + [topic,83203] : Added Banner ordering based on Josselin's initial patch

08-Aug-2006 Andrew Eddie
 + Added 'exclude' attribute to filelist parameter tag
 + Added 'exclude' attribute to folderlist parameter tag
 ^ Reconfigured mod_search to support selectable module templates

07-Aug-2006 Andrew Eddie
 # Fixed Zend Hash Del Key Or Index Vulnerability
 ^ globals.php now forces emulation to register_globals = off
 # Fixed JUtility::spoofKey method to ensure the hash is a string

06-Aug-2006 Enno Klasing
 # Fixed [artf5526] : Install fails: "Fatal error: Class 'JArray' not found" with makeDB()
 # Fixed [artf5531] : Missing "global $mainframe;" in com_users/admin.users.php

05-Aug-2006 Johan Janssens
 - Removed JApplication::getLanguage, use JFactory::getLanguage instead

04-Aug-2006 Johan Janssens
 - Deprecated JApplication::getUser, use JFactory::getUser instead
 - Removed JApplication::getDBO, use JFactory::getDBO instead

04-Aug-2006 Enno Klasing
 # Fixed [artf5509] : com_frontpage xhtml compliance
 ^ Deprecated mosObjectToArray, use JArrayHelper::fromObject instead

03-Aug-2006 Enno Klasing
 # Added various missing language strings

03-Aug-2006 Andrew Eddie
 # Fixed bug in JPath::check
 ^ JPath::check returns a clean path
 # [artf5279] Framwork model.php getController() Reference
 ^ Removed database argument in JModel constructor

31-Jul-2006 David Gal
 ^ More fixes to RTL in admin helped by Mati Kochen

31-Jul-2006 Alex Kempkens
 # [artf5414] Errors on every link to content item, fixed poll other not found

30-Jul-2006 Wilco Jansen
 + Added error number handling in JTable class.

30-Jul-2006 David Gal
 ^ Fixes to RTL in admin
 + Added RTL to media manager dtree menu

29-Jul-2006 Enno Klasing
 ^ Deprecated josSpoofCheck, use JUtility::spoofCheck instead
 ^ Deprecated josSpoofValue, use JUtility::spoofKey instead
 + Added JUtility::spoofCheck to POST forms [WIP, com_content still missing]

26-jul-2006 Johan Janssens
 ^ Deprecared mosBindArraytoObject, use JObject->bind instead
 + Added JDatabase->loadAssoc, to fetch a result row as an associative array
 ^ Renamed connector package to client
 ^ Moved environment classes into their own package

25-Jul-2006 Johan Janssens
 ^ Refactored JMenu class to remove application state coupling
 ^ Moved getEditor function from JApplication to JFactory

24-Jul-2006 Johan Janssens
 ^ Decoupled user object from application class
 ^ Refactored JSession class to improved session handling

24-Jul-2006 Alex Kempkens
 # Fixed [artf5321] Hardcoded strings, custom menus and other static texts
 + Added missing language files

23-Jul-2006 Johan Janssens
 + Added custom module to backend and cleanedup functionality.

23-Jul-2006 Alex Kempkens
 # Fixed wrong class and method names in mod_stats

23-Jul-2006 Enno Klasing
 # Fixed [artf5361] : Legacy menu active on homepage

22-Jul-2006 David Gal
 ^ Replaced most instances of 'content item' to 'article' in code, comments and language files

22-Jul-2006 Johan Janssens
 ^ Replaced mosArrayToInts by JArrayHelper::toIntegers
 ^ Replaced mosGetParam by JArrayHelper::getValue

21-Jul-2006 Enno Klasing
 # Fixed [artf5360] : Cannot delete user

20-Jul-2006 Louis Landry
 # Fixed bug in com_banners
 # Fixed bug in mod_mainmenu and split menus
 + Added JArrayHelper static class in utilities package
 ^ Moved input filtering on request variables to the correct place

20-Jul-2006 Johan Janssens
 ^ Refactored frontend mod_login
 + Improved login error reporting on the frontend
 ^ Replaced mosHash by JUtility::getHash
 ^ Deprecated mosBackTrace, use JError->getBackTrace instead

19-Jul-2006 Johan Janssens
 + Added static JUtility static as container for utility functions
 ^ Implemented JError store to track last error message
 # Fixed login error reporting on both front and backend
 ^ Replaced josMail by JUtility::sendMail
 ^ Replaced josSendAdminMail by JUtility::sendAdminMail

19-Jul-2006 Enno Klasing
 # Fixed [artf4441] : Missing translation in default en_GB
 # Fixed problems with josMail() when using SMTP Mailer

18-Jul-2006 Marko Schmuck
 ^ updated installation language file for German latest version of the GTT Team

18-Jul-2006 Enno Klasing
 # Fixed [artf3839] : patTemplate Tabs

17-Jul-2006 Alex Kempkens
 + [task2536] com_registration -> remember me changed. Frontend texts added to language files
 # [artf3899] Missing definitions in backend language files - added which I found missing
 ^ updated installation language file for German

16-Jul-2006 Wilco Jansen
 ^ Added JTable::addIncludePath enabling 3rd party components to make use of the JTable::getInstance method

16-Jul-2006 David Gal
 ^ Moved session creation and language setting prior to plugin loading index.php (admin and site)
 ^ Refactored setLanguage - removed from JApplication and created specific methods in JSite and JAdministrator
 # Fixed JRequest::getVar type casting

16-Jul-2006 Alex Kempkens
 ^ [task2379] refactoring of mod_whosonline
 # Corrected the missing '!' in the template
 ^ [task2378] refactored of mod_syndicate
 ^ [task2378] refactored of mod_stats
 ^ [task2375] refactored of mod_search
 ^ [task2374] refactored of mod_related_items
 ^ [task2373] refactored of mod_random_image
 ^ [task2372] refactored of mod_poll

16-Jul-2006 Andy Miller
 ^ Updated some menu icons to match new manager icons

16-Jul-2006 Sam Moffatt
 # Fixed up minor authentication issues

15-Jul-2006 Enno Klasing
 ^ Relocated plugin language files to Administrator

14-Jul-2006 Andy Miller
 ^ Changed overlib css style
 ^ Updated publish/expired/pending 16x16 icons for improved clarity
 ^ Began reworking 'manager' icons

13-Jul-2006 Louis Landry
 ^ All core database queries changed to use the limit/offset arguments to JDatabase::setQuery instead of hardcoded SQL

13-Jul-2006 Enno Klasing
 ^ Naming conventions for plugin language files have changed to include the folder name of the plugin
 # Removed double definitions of language strings (en-GB.com_plugins.ini), added missing language strings
 # Labels without a description (tooltip) for parameters were not translated
 # Fixed [artf4985] : Translate 'Login' in administrator login

12-Jul-2006 Andrew Eddie
 ^ Deprecated mosBackTrace, use JError instead
 ^ Filtering and casting parts of JRequest::getVar broken into functions

11-Jul-2006 Enno Klasing
 # Fixed [artf5246] : Hardcoded strings (could not be localized)
 # Fixed [artf3900] : Usertype not translated in listings

10-Jul-2006 Louis Landry
 # Backward Compatability issue: define $database and $my as globals -- still deprecated
 ^ JDatabase::__destruct added to make sure db connections are closed on all page loads

10-Jul-2006 Andrew Eddie
 ^ JTable::load sets the internal error message on a fail

10-Jul-2006 Andrew Eddie
 # Fixed problem in JTable::isCheckedOut where checked_out doesn't exist

09-Jul-2006 Louis Landry
 # Fixed different admin menu behavior if entering com_config from control panel or main menu
 # Fixed notice in JApplicationHelper::getPath()

09-Jul-2006 Mat???
 ^ Installer, database password input field changed to type: password
 ^ Installer, admin password input field changed to type: password
 ^ Installer, admin password have to be confirmed
 - Installer, admin password removed from the installation finish screen

06-Jul-2006 Johan Janssens
 # Fixed [artf5210] : New categories have no entry for component
 # Fixed [artf5176] : JInstallerModule->uninstall returns wrong error message
 # Fixed [artf5088] : admin template/wrong class in com_messages
 # Fixed [artf5087] : Error deleting private message
 # Fixed [artf5087] : Error deleting private message
 # Fixed [artf4939] : Can't reach to help server
 # Fixed [artf4815] : Feedcreator 1.7.3 with sticky encoding ISO-8859-1
 # Fixed [artf4755] : Installation language rev. 3580
 # Fixed [artf4640] : Error trying to authenticate using LDAP
 # Fixed [artf4213] : JFactory::_createMailer() possibly instantiates wrong class...

04-Jul-2006 Samuel Moffatt
 ^ Changed instances of sefRelToAbs to josUrl in pagination class

03-Jul-2006 Louis Landry
 # [artf5152] : JApplication->getHead calls a non existant method
 # [artf4537] : upgrade10to15.sql - wrong field reference (build 3336)

01-Jul-2006 Johan Janssens
 - Removed unique email registration setting

01-Jul-2006 Louis Landry
 + Added JLog class for logging actions/events

30-Jun-2006 Johan Janssens
 ^ [task2569] : Revisit the ADODB compatibility

30-Jun-2006 Emir Sakic
 # Fixed task notices in sefurlbot
 # Fixed weblinks ordering

29-Jun-2006 David Gal
 ^ Modified the migration to convert imported menu table to the new menu system
 # Fixed PDF display
 ^ Updated our modified TCPDF library to changes made in latest release of the library

27-Jun-2006 Alex Kempkens
 ^ [task2640] : Beta 1 - refactor of global vars
 ^ [task2358] : Refactoring of com_poll finished

26-Jun-2006 Alex Kempkens
 ^ [task2638] : Refactoring of $my to $user, changed in all core files

23-Jun-2006 Louis Landry
 ^ POC for hardened form handling -- weblinks submission

223-Jun-2006 Johan Janssens
 + Added com_cache to easily manage and clean the cache throught the backend

21-Jun-2006 Johan Janssens
 # Updated phpxmlrpc library to version 2.0 stable
 # Updated cache_lite library to version 1.7.2
 # Fixed [task2517] : Updated core libraries

20-Jun-2006 Johan Janssens
 # Fixed [task2548] : Base tag issues

14-Jun-2006 Johan Janssens
 # Feature request [artf1718] : Selecting wysiwyg editor in components

13-Jun-2006 Johan Janssens
 # Feature request [artf4683] : Extended Allowable filetypes in the media manager
 # Feature request [artf4065] : Include an option in global config to set allowable filetypes for uploading
 # Feature request [artf1519] : Store post data on session expiry
 # Feature request [artf1536] : Move category / Bulk move content

13-Jun-2006 Alex Kempkens
 # [task2596] : Beta 1 - Renaming $database -> $db, finished all area
 + some ini files which have been missing, no new tags involved
 # Fixed : ordering of modules didn't worked with the arrows

13-Jun-2006 Louis Landry
 + Added target to banners module params

09-Jun-2006 Louis Landry
 ^ Allow overlib to be styled via CSS classes
 # Fixed [artf4828] : incorrect logging of queries.
 # Fixed [artf4904] : Single quote in config values not saved correctly (PHP Format)

09-Jun-2006 Johan Janssens
 ^ Implemented [task2506] : config component calls help

08-Jun-2006 Andrew Eddie
 ^ Allowed pluggable controller for contact component (so alternative/non-standard routing is possible)
 + Added custom error page for contact component
 + Added getError and setError to JController
 + Added injection filtering to JMail methods

08-Jun-2006 Andy Miller
 ^ Changed khepri Administrator template to use images rather than nifty corners
 ^ Changed Installation to use images rather than nifty corners
 # Fixed Installation to work in IE
 # Fixed Installation to fit in 800x600 browsers

08-Jun-2006 Andrew Eddie
 ^ Refactoring of com_contact frontend into MVC architecture
 + Moded params for banned*stuff and session check to component configuration

07-Jun-2006 Louis Landry
 ^ {readmore} changed to HR tag with specific id attribute
 ^ Refactor of menu system to allow third party extensibility

05-Jun-2006 David Gal
 ^ Changed name of helpsites xml file to correct version

03-Jun-2006 Andrew Eddie
 + Added publish_up, publish_down,tags, params field to banners table
 + Added table to track banner impressions and clicks
 + Added metadata field to content table

01-Jun-2006 Rey Gigataras
 + Ability to set usertype of New User Registration via frontend

01-Jun-2006 Andrew Eddie
 + Added window.open attributes to mod_mainmenu

31-May-2006 Johan Janssens
 - Removed cache_path configuration setting
 + Added debug_lang configuration setting

31-May-2006 David Gal
 ^ Changed link in final installation step to point to new languages page on help site
 ^ Added an access check to allow/block admin language choice by user in front end

30-May-2006 David Gal
 ^ Changed content search plugin to look for uncategorised content instead of static content

30-May-2006 Andrew Eddie
 + Added generic Mail To Friend component, com_mailto
 + Added JRequest::getURI method

29-May-2006 Louis Landry
 ^ Added check to JObserver attach method ot make sure an observer can't be attached more than once

29-May-2006 Andrew Eddie
 + Added category support to banners component
 + Added ordering and sticky fields to banners to support better ordering
 + Added description field to banners for general private notes
 ^ Revised banners module
 + Added demo banners to sample data in a text adverts style

26-May-2006 Alex Kempkens
 ! Refactoring of frontend extentions
 ^ Removed config references to global vars and refactored them to getCfg calls
 ^ Removed mosNotAuth calls

25-May-2006 Andrew Eddie
 + Added JComponentHelper (joomla.application.extensions.component)

25-May-2006 Mateusz Krzeszowiec
 ^ mod_login now does not enforce redirect links to be /index.php?[...]

24-May-2006 Louis Landry
 ^ Moved JTree library to the common package
 ^ Modified admin menu to use JTree
 ^ Refactor of media manager
 # Fixed [artf4579] : geshi WON'T work

24-May-2006 Johan Janssens
 + Added jdoc:empty function to JDocumentHTML

24-May-2006 Andrew Eddie
 ^ Refactoring of menu type manager
 ^ Widenned jos_modules::position field to 50 chars
 + Added mvcrt field to jos_modules table
 + Added all core components to jos_components table in preparation for configuration support
 + Added functionality to com_config to handle the configuration of any component via parameters
 + Added basic configuration POC to com_media

23-May-2006 Johan Janssens
 + Added AJAX driven upload functionality to image manager
 - Removed old administrator template
 ^ Changed release codename to 'Khepri'

22-May-2006 Alex Kempkens
 # Fixed [[artf4240]] : Registration confirmation email broken: password
 ! [task2359] : Refactoring completed
 ^ [task2519] : getInstance refactored in order to make sure only one instance is created

22-May-2006 Andrew Eddie
 ^ mosAdminMenus::menuItem refactored to JMenuHelper::menuItem
 ^ mosAdminMenus::menutypes refactored JModelMenu method
 + Added table for menu types
 ^ Refactored menu type manager
 ^ Delete menu uses new popup technique

19-May-2006 David Gal
 + Added automated content migration facility in installation

18-May-2006 Johan Janssens
 # Fixed [[artf4497]] : moofx sliders leave controls visible (Mac OS)
 - Removed image add/edit functionality from content edit page
 + Implemented modal image manager for easy inserting of images into the editor
 ^ Implemented modal popup for previewing content items

16-May-2006 Andrew Eddie
 + Added template filter to module manager list

15-May-2006 Andrew Eddie
 ^ JSimpleXML will return false it the xml file is empty and not attempt to parse it
 ^ Added template_name field to menu table

14-May-2006 Johan Janssens
 ^ Moved Cache_Lite package to pear library folder

12-May-2006 Louis Landry
 ^ Refactored mod_mainmenu -- new options allows for great flexibility using unordered lists
 + Added JTree and JNode to utilities package -- Abstract tree implementation

12-May-2006 Alex Kempkens
 ^ Updated German installation language

12-May-2006 David Gal
 + Added confirm for no sample data in installation.

11-May-2006 Johan Janssens
 ^ Refactored JDocumentHTML class to use an adapter pattern and added buffering improvements
 + Added template and page caching configuration settings

10-May-2006 Johan Janssens
 ^ Feature request [artf1819] : Duplicate queries
 ^ Feature request [artf3705] : Sorting of all tables in admin area
 ^ Feature request [artf1479] : Content Items Manager add filter by field Date, Publish etc
 ^ Feature request [artf1992] : Allow sorting in Content Items Manager
 ^ Feature request [artf2008] : Contacts Sorting Improvement
 ^ Feature request [artf3884] : mod_sections.php missing css class
 ^ Feature request [artf3170] : Apply button in CSS and HTML built-in editors
 ^ Feature request [artf2271] : Make it possible to send own headers
 ^ Feature request [artf1721] : More advanced editor-xtd triggers
 ^ Feature request [artf2691] : Weblinks: Auto alphabetize

10-May-2006 Samuel Moffatt
 # Fixed issue with login directly after activation causing error, now redirects to index.php

08-May-2006 Johan Janssens
 ^ Moved ZLib output compressing into JDocument class

07-May-2006 Samuel Moffatt
 # Changed languages to language in component and modules installer

06-May-2006 Johan Janssens
 ^ Refactored JDocument class to use an adapter pattern
 ^ Renamed JDocumentRSS class to JDocumentFeed
 ^ Implemented RSS 2.0 and Atom 1.0 document renderers
 ^ Restructured Document package to better reflect different doc types
 + Added JDate class to easily handle RFC 822, ISO 8601 and UNIX date timestamps

01-May-2006 Johan Janssens
 # Fixed [artf4480] : Menu ordering
 # Fixed [artf4526] : PDF button don't works
 # Fixed [artf4036] : JDocumentHTML constructor incorrectly calls parent constructor.
 # Fixed [artf3287] : Syndicate module not viewable by default
 # Fixed [artf4405] : [patch] Print, PDF and email buttons aren't accessible

01-May-2006 David Gal
 ^ Changed the syntax of sql queries tags in component installer xml file

01-May-2006 Arno Zijlstra
 ^ Changed back buttons to cancel in template css and html editor

01-May-2006 Andrew Eddie
 + Added Relative image url suppot to TinyMCE

01-May-2006 Andy Miller
 ^ Added default styles for the frontend/admin xtd-buttons

28-Apr-2006 Johan Janssens
 # Fixed [artf4238] : Empty JS brackets in backend don't render correctly

27-Apr-2006 Johan Janssens
 + Implemented hybrid javascript modal popup library

27-Apr-2006 Alex Kempkens
 + added mosHTML::formatMessage for generic message output

26-Apr-2006 Louis Landry
 ^ Refactored editors-xtd plugins and separated rendering from editor

26-Apr-2006 Johan Janssens
 ^ Refactored JDocumentRSS class to use an adapter pattern

26-Apr-2006 David Gal
 + Added RTL display option for newsfeeds component.
   Can display RTL feed in LTR site and vice versa
 ^ Changed name of search ignore file to [langTag].ignore.php and move it to language folder
 # Fixed com_search to remove words to ignore from multiple word search terms of type 'all'

25-Apr-2006 Andy Miller
 + Added template param to turn off rounded corners in new admin template

24-Apr-2006 Johan Janssens
 ^ Refactored JDocument package to use an adapter pattern
 ^ Syndication module fetches syndication link from page head

24-Apr-2006 Andy Miller
 ^ Reworked UI for Global Configuration
 ^ Administrator Modules Manager has been reworked for new design
 ^ Cleaned up login CSS
 ^ Reworked UI for User Manager

24-Apr-2006 David Gal
 + Added language class (lite) to jajax.php for localisation of server side jajax routines

22-Apr-2006 Alex Kempkens
 ^ Cleanup/Refactor com_registration
   - adapted new user handling as well and changed the standard registration procedure to it
 ^ Corrected getInstace of user objects with id's
 + Language tag for Registration Errors

21-Apr-2006 Johan Janssens
 - Removed syndicate component
 ^ Moved feed settings to configuration
 ^ Remorked syndication module to support JDocumentRSS
 + Added live bookmark support to frontpage component
 ^ Set new admin template as default

20-Apr-2006 Johan Janssens
 ^ Moved content feed handling into content component
 ^ Moved contact feed handling into contact component
 ^ Moved weblink feed handling into weblink component
 - Removed syndicate plugins

20-Apr-2006 David Gal
 ^ Moved installation sample data sql file to sql folder - no longer part of language packs

20-Apr-2006 Louis Landry
 + setVar method to JRequest
 # Fix error in JCache::remove method causing problems with auto state storage
 ^ Installation and temporary files are handled in the tmp/ folder -- not media/
 + Added preview link to mod_status
 + Added JPagination link list method

20-Apr-2006 David Gal
 + Moved loading of sample data in installation to MainConfig step. Implemented with xajax
 + Added possibility of uploading and executing sql scripts during installation
   from MainConfig step. Good for localised sample data or data migration/restore

19-Apr-2006 Johan Janssens
 + Added JDocumentPDF format for PDF output rendering
 + Added JDocumentRAW format for RAW output rendering
 + Added JDocumentRSS format for RSS output rendering
 ^ Refactored JDocument and com_content to use output types
 ^ Deprecated administrator/includes/template.php, file removed
 ^ Made index.php only entry point for JSite
 ^ Made index.php only entry point for JAdministrator
 ! Set minimum system requirements to PHP version to 4.3.0
 ^ Moved frontpage feed handling into frontpage component
 # Fixed [artf3911] : Pear include in lite.php problem in safe mode
 # Fixed [artf4330] : PEAR being included twice

16-Apr-2006 Johan Janssens
 ^ Preview content now works on the fly
 ^ Refactored JEditor API
 ^ Moved site content item editing to modal popup
 - Removed TinyMCE print, save and preview plugins
 - Removed administrator/mod_components
 - Removed 'Link To Menu' edit content tab

15-Apr-2006 Louis Landry
 # Fixed small bugs with JFolder and JFTP -- Thanks Beat --
 # Fixed some translation strings in admin

13-Apr-2006 Louis Landry
 # Fixed [artf3574] : com_weblinks description field
 # Fixed [artf3902] : small problem with content.php when no frontpage items are to be shown
 # Fixed [artf4251] : JPath::check function incorrectly checks for presence of ..
 # Fixed [artf4044] : com_newsfeeds could generate invalid html
 # Fixed [artf3896] : geshi - call to undefined function

11-Apr-2006 Johan Janssens
 + Added JPaneSliders class, for creating moofx driven sliding panes
 ^ Updated moofx library to 1.2.3
 ^ Switched article edit view to using JPaneSliders

10-Apr-2006 Johan Janssens
 ^ Deprecated mosTabs, use JPane instead

09-Apr-2006 Louis Landry
 + Store user state on administrator auto-logout so that when the user logs in again, the
   state is restored
 ^ com_content now uses JMessage
 # [artf4250] : FTP directory listing returns double entries (with fix proposal)

09-Apr-2006 Andy Miller
 # Added some padding/js trickery to stop nifty corners from jumping around
 + Forward progress on the admin template - tweaked style on menu

08-Apr-2006 Johan Janssens
 + Implemented hybrid javascript menu in new admin template

07-Apr-2006 David Gal
 + Applied utf-8 aware string functions (JString class) to all extensions
   All of the php code should now be utf-8 aware.

05-Apr-2006 Alex Kempkens
 ^ Cleanup/Refactor com_search (frontend) incl. all related plugins

03-Apr-2006 David Gal
 ^ Cleanup/Refactor com_user (admin)

02-Apr-2006 Johan Janssens
 ^ Changed file header copyright information
 ^ Changed version information from 1.5 to 1.5
 # Fixed [artf4208] : JError::isError()
 # Fixed [artf4137] : JDocumentHelper::implodeAttribs...
 # Fixed [artf4120] : JREQUEST_ALLOWHTML and JREQUEST_ALLOWRAW mixed up in JRequest::getVar

31-Mar-2006 David Gal
 + Integrated new RSS parsing library - MagpieRSS (adds conversion to utf-8 from all encodings)
 ^ Cleanup/Refactor com_newsfeeds (site)

30-Mar-2006 Louis Landry
 ^ Merged com_typedcontent into com_content
 # Fixed JRequest::getVar integer type unable to accept negative integers
 ^ Cleanup/Refactor mod_archive
 ^ Cleanup/Refactor mod_footer
 ^ Cleanup/Refactor mod_sections
 ^ Cleanup/Refactor com_login

29-Mar-2006 David Gal
 ^ Cleanup/Refactor com_newsfeeds (admin)

27-Mar-2006 David Gal
 ^ Changed the phputf8 library to the newer release of the library

27-Mar-2006 Andrew Eddie
 # Fixed mega-inefficient query in the poll results display
 ^ Cleaned up multiple table nestings in poll results page
 ^ Minor refactoring to frontend polls component

27-Mar-2006 David Gal
 # Fixed language uninstall bug

25-Mar-2006 David Gal
 ^ Separated install xml files and metadata xml files for languages
 # Fixed some language intall problems

23-Mar-2006 Johan Janssens
 - Removed DOMIT! XML-RPC library
 ^ Implemented JSimpleXML instead of DOMit for parsing extension xml files
 ^ In component navigation in configuration (removed tabs)

21-Mar-2006 Johan Janssens
 + Added JSimpleXML class to utilities package

21-Mar-2005 Louis Landry
 ^ Renamed JModel to JTable and added to the database package
 ^ Use josRedirect instead of mosRedirect in codebase
 ^ Moved deprecated methods out of JTable class and getPublicProperties() into JObject

20-Mar-2005 Louis Landry
 ^ Use JRequest::getVar instead of mosGetParam in codebase

20-Mar-2005 Johan Janssens
 # Fixed [artf3938] : addHeadLink does not properly format output when additional attributes are specified.
 - Removed legacy usertypes db table

19-Mar-2005 Louis Landry
 + CSS driven full admin menu module

17-Mar-2005 Johan Janssens
 ! Overall preformance improvements
 ^ Feature request [artf1796] : Admin: Items -> sort by pressing on tableheader

17-Mar-2005 Andrew Eddie
 ^ Upgraded mod_banners to allow for multiple banners to be shown
 ^ For review: In component navigation for com_banners
 ^ For review: In component navigation for com_templates
 ^ For review: Code snippets in template HTML editor

16-Mar-2005 Johan Janssens
 - Removed administrator/includes/toolbar.html.php
 - Moved parameter package into presentation package

16-Mar-2005 Louis Landry
 + JMenu class to hold menu item information
 ^ Implemented caching in com_content
 ^ Implemented caching in mod_newsflash
 ^ Various performance improvements

15-Mar-2005 Louis Landry
 + JMessage class to utility package
 ^ com_content restructuring

15-Mar-2005 Andrew Eddie
 + Added webpage and mobile fields to contacts table
 ^ Widen several narrow fields in contacts table to allow for more characters
 ^ Contact position, address and phone number edit fields changes to textareas
   to allow for multi-line input
 + New toolbar buttons in contact edit form: Save and New, Save To Copy

14-Mar-2006 David Gal
 + Added backward compatibility to $mosConfig_lang such that en-GB returns 'english'
 + Added new tag <backwardLang> to language metadata xml files

13-Mar-2006 Johan Janssens
 + Added phpxmlrpc library to replace DOMit XML-RPC
 + Added backend login module
 ^ Authentication API and plugin handling cleanup

13-Mar-2006 David Gal
 ^ Changed configuration var $lang to $lang_site and made required modifications

13-Mar-2006 Louis Landry
 ^ JButton cleanup and consolidation

09-Mar-2006 Johan Janssens
 - Removed backbutton configuration setting
 # Fixed : [artf3786] : Template - names
 # Fixed : [artf3842] : [patch] JPATH_SITE should be JPATH_CONFIGURATION
 # Fixed : [artf3850] : Remove hspace attribute from mosimage.php
 + Changed userExists to getUserId in JModelUser

08-Mar-2006 Johan Janssens
 ^ Moved presentation classes into their own package
 ^ Moved mail classes into utilities package

08-Mar-2006 Louis Landry
 + Proper HTML Error page rendering for JError
 # Fixed [artf3646] : Lowercase definition in language file
 # Fixed [artf3452] : array may be passed uninitialised
 # Fixed [artf3557] : Bug in go2(), mistake in mosCommonHTML::Images()
 ^ Editor unification and com_content cleanup in administrator client
 + Editor button for {readmore} tag

06-Mar-2006 Johan Janssens
 ^ Reorganised the administrator menu structure

05-Mar-2006 Johan Janssens
 - Removed pagetitles and meta_pagetitle configuration settings

04-Mar-2006 Louis Landry
 # Fixed [artf3431] : Error when mod_breadcrumbs is published

26-Feb-2006 Johan Janssens
 + Added Blogger API XML-RPC plugin

25-Feb-2006 David Gal
 ^ Changed xStandard to output utf-8 content instead of NCR codes
 + Implemented converstion to utf-8 of locale formated date when Windows is the host OS
 + Added new metadata tag <winCodePage> in language xml files to support above conversion

24-Feb-2006 David Gal
 + Added RTL support to new installation program UI

23-Feb-2006 Johan Janssens
 ^ Renamed mossef content plugin to sef
 ^ Renamed moscode content plugin to code
 ^ Renamed mosemailcloak content plugin to emailcloak
 ^ Renamed mosloadposition content plugin to loadmodule
 ^ Renamed mospagebreak content plugin to pagebreak
 ^ Renamed mosvote content plugin to vote
 ^ Renamed mosimage.btn editor-xtd plugin to image
 ^ Renamed mospage.btn editor-xtd plugin to pagebreak
 ^ Fixed language detection
 # Fixed [artf3624] : Content priview error with {mosImage}
 # Fixed [artf3552] : Typo in mosCommonHTML::menuLinksContent
 # Fixed [artf3344] : Install errors due to empty browser language setting
 # Fixed [artf2792] : Web installer language choice

23-Feb-2006 Alex Kempkens
 + Added language parameter to content.xml

22-Feb-2006 Johan Janssens
 ^ Upgraded TinyMCE Compressor [1.0.7]
 ^ Upgraded TinyMCE [2.0.3]
 ^ Renamed mosimage content plugin to image

21-Feb-2006 Johan Janssens
 + Added client_id field to session table
 ^ Added client column to mod_logged and improved forced log out functionality

20-Feb-2006 Andrew Eddie
 # Fixed filelist param - would always show list entries related to images for default and do not use

17-Feb-2005 Andy Miller
 + Added new installer Look and Feel
 + Added new login Look and Feel
 + Work started on new Admin Template
 + Created new set of icons for new Admin Template

16-Feb-2006 Rey Gigataras
 + Allow ability to siwtch off emailcloaking for specific items via {mosemailcloak=off} tag

16-Feb-2006 Louis Landry
 # Fixed [artf3475] : relative include paths break installation on some systems
 # Fixed infinite recursion problem with some JError errors

16-Feb-2006 Johan Janssens
 # Fixed [artf3454] : using statistics the main toolbar in admin breaks
 ^ Plugin naming cleanup

16-Feb-2006 Samuel Moffatt
 + Added GMail authentication plugin

15-Feb-2006 Louis Landry
 # Fixed JFile::upload src path issue causing an inability to upload components on some systems
 # Fixed Installation autofind FTP path issue on windows machines wehre paths case don't match
 ^ On installation paths are now auto-chmodded by the installation script
 ^ FTP Port now configurable option for connecting to ftp the ftp server

14-Feb-2006 Johan Janssens
 + Added XStandard Lite 1.7 plugin
 ^ Renamed JDocument placeholder funtion to include

13-Feb-2006 Louis Landry
 # Fixed [artf3481] : Changes in uri.php - Corrects MS problem in Installation
 # Fixed [artf3498] : Bugs in uri.php - HTTPS detection, URI handling is not correct on Microsoft IIS environment
 # Fixed [artf3383] : Component installation languagefiles are not copied
 # Fixed [artf3368] : $url not set in mosAdminMenus::ImageCheckAdmin and administrator-dir handling is wrong

11-Feb-2006 Louis Landry
 # Fixed [artf3478] : Error in SQL Script

11-Feb-2006 David Gal
 ^ Modified JString to load after pre-installation check (phputf8 will crash on wrong settings)
 + Added local mbstring environmental settings in htaccess.txt ready for uncommenting if needed

08-Feb-2005 Louis Landry
 # Fixed [artf3432] : Administrator toolbar items do not contain port number

-------------------- 1.5.0 Alpha 2 Released -- [06-Feb-2006 00:00 UTC] ------------------

04-Feb-2005 Johan Janssens
 # Fixed [artf3368] : $url not set in mosAdminMenus::ImageCheckAdmin and administrator-dir handling is wrong

03-Feb 2006 Rey Gigataras
 ^ Modified admin Content/Static Content edit pages to better use of screen realestate
 + Add `100` to list dropdown select

02-Feb 2006 Louis Landry
 ^ Changed core components to use new JUser->authorize method instead of acl_check()

02-Feb 2006 Rey Gigataras
 # Fixed [topic,34959.0.html] : Weblinks error
 + Labels added to params output
 ^ Moved `Page Hits Statistics` to `Content` submenu

01-Feb 2006 Louis Landry
 * Itemid script injection - Thanks Mathijs de Jong
 ^ Framework file catagorization cleanup
 ^ JACL class renamed to JAuthorization
 ^ JApplication::getUser now uses the JUser class: global $my deprecated

01-Feb-2006 Rey Gigataras
 ^ Registration component output correctly separated into .html.php

01-Feb-2006 Johan Janssens
 - Removed mod_templatechooser
 ^ Changed bot language file prefix to plg

01-Feb-2006 Emir Sakic
 # Fixed admin menubar hover href issue

01-Feb-2006 Andrew Eddie
 # Fixed change of JModel::publish_array to JModel::publish (since 1.0.3)
 # Fixed bug in JTree where parent_id of 0 not correctly handled if children array is out of order
 # Added missing getAffectedRows methods to database classes

31-Jan-2006 Rey Gigataras
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
 # Fixed [artf3306] : locale - time offset in configuration - very minor
 # Fixed [artf3255] : unable to edit _system css files
 # Fixed XAJAX problem on installation (PHP as CGI on apache)
 # Fixed custom help site per user not working

29-Jan-2006 Johan Janssens
 ^ Moved mail classes into mail library package
 # Fixed [artf3285] : White page on Your Details and Check-In My Items
 # Fixed [artf3263] : Unable to make new message in private messaging
 # Fixed [artf3271] : Category image not visible (path incorrect)
 # Fixed [artf3282] : Sample image is missing

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
 # Fixed: [artf3197] : component install creates wrong directory permission
 # Fixed: [artf2736] : Incorrect language js escape
 # Fixed: [artf2911] : 1661: Admin menus inconsistent
 # Fixed: [artf3193] : Template editor escapes on save... continually

23-Jan-2006 Johan Janssens
 ^ Feature request [artf2781] : change $mosConfig_live_site to permit server aliasing
 # Fixed [artf1938] : Help site server choice per admin user

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
 # Fixed [artf3140] : component install creates wrong directory permission
 # Fixed [artf3123] : Bad help addressing
 ^ Implemented phase one of component disable/enable functionality (GUI) WIP

18-Jan-2006 Johan Janssens
 # Fixed [artf2172] : database settings not retained in installer

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
 # Fixed [artf2143] : Altered radio buttons to checkboxes for installers

13-Jan-2006 Johan Janssens
 # Fixed [artf2514] : Cannot preview template positions

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
 # Fixed [artf2941]: Image Upload and Media Manager issues
 # Fixed [artf1863] : Function delete_file and delete_folder not check str
 # Fixed [artf2424] : Help Server Select list default problem
 # Fixed [artf2948] : 1700: SEF broken
 # Fixed [artf1747] : No clean-up in event of component installation failure
 ^ Feature request [artf1728] : upload component from server
 ^ Feature request [artf2017] : popups/uploadimage.php not using directory

07-Jan-2006 Louis Landry
 + Added JPagination class
 ^ Deprecated mosPageNav class, use JPagination instead
 # Fixed [artf2917] : Rev#1665 -- Forced log-out on clicking "Upload" in content

06-Jan-2006 Johan Janssens
 ^ Implemented adpater pattern in JModel class
 ^ Updated geshi to version 1.0.7.5, moved to the libraries

06-Jan-2006 Louis Landry
 ^ Mambots refactored to Plugins
 ^ Interaction with editors is now controlled by JEditor
 # Fixed [artf2926] : SVN 1669 file not renamed
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
 # Fixed [artf2807] : Missing file - Legacy plugins
 + Added JMail class

30-Dec-2005 Johan Janssens
 + Added JURI and JRequest classes - contributed by Louis Landry
 + Implemented AJAX functionality in the installation - contributed by Louis Landry
 - Removed administrator/mod_pathway
 + Template rendering completely overhaulted by new JDocument interface

27-Dec-2005 Johan Janssens
 # Fixed [artf2742] : Backend generates just plain text output in IE or Opera
 # Fixed [artf2739] : mambot edit-save error
 # Fixed [artf2729] : Same content displayed twice on FrontPage
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
 ^ Deprecated mosParameters, use JParameters instead
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
 + Added padding and separator styles to multi column layout

21-Dec-2005 Johan Janssens
 + Added JTemplate Zlib outputfilter for transparent gzip compression
 + Added JPluginModel and JInstallerPlugin classes - contributed by Louis Landry

21-Dec-2005 Andy Miller
 + Added editor_content.css for MilkyWay template
 + changed admin acccent color from red to green to differentiate 1.0 and 1.5

21-Dec-2005 Levis Bisson
 + Added and wrapped tinymce language module file for parameters in the backend

20-Dec-2005 Emir Sakic
 # Fixed [artf2432] : Apostrophe in paths isn't escaped properly

20-Dec-2005 Levis Bisson
 ^ Changed the translation text Mambots to Plugins
 # Reworked "load language function" for translating Modules and Plugins
 # Fixed path for site or admin modules in the backend

20-Dec-2005 Johan Janssens
 # Fixed [artf2606] : JApplication::getBasePath interface changed from Joomla 1.0.4
 ^ Reworked installer to use an adapter pattern

19-Dec-2005 Johan Janssens
 ^ Refined mbstring installation checks - contributed by David Gal
 ^ Renamed Pathway module to Breadcrumbs - contributed by Louis Landry
 ^ Minor fixes in FTP library that should solve response code problems experienced on
   some mac ftp servers - contributed by Louis Landry
 # Fixed [artf2655] : factory.php - xml_domit_lite_parser.php

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
 ^ Feature request [artf1063] : Safemode patch for Joomla
 ^ Feature request [artf1507] : FTP installer

12-Dec-2005 Johan Janssens
 ^ Fixed smaller file system problems - contributed by Louis Landry
 + Added mbstring and dbcollaction information to system info - contributed by David Gal
 # Fixed [artf2485] : Impossible to install component/module/mambot/templates

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
 # Fixed [artf2517] : "Cancel" the editing of content after "apply" not possible

10-Dec-2005 Samuel Moffatt
 ^ Disabled php_value in htaccess file, caused 500 Internal Server Errors
 + JRegistry Core Complete (INI Format and Database Storage Engines)

09-Dec-2005 Emir Sakic
 # Fixed [artf2324] : SEF for components assumes option is always first part of query
 # Fixed [artf1955] : Search results bug
 + Added a solution for url-type menu highlighting

09-Dec-2005 Johan Janssens
 + Added support for FTP to the installation - Contributed by Louis Landry
 # Fixed [artf2495] : Cant save user details from FE.
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
 # Fixed [artf2369] : $mosConfig_lang & $mosConfig_lang_administrator pb
 + Added 'Site if offline' message to mosMainBody
 + Added error.php system template
 + Added login box to offline system template
 - Removed login and logout message functionality

29-Nov-2005 Johan Janssens
 # Fixed [artf2361] : Fatal error: Call to a member function triggerEvent()
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
 # Fixed [artf2317] : Installation language file
 # Fixed [artf2319] : Spelling error

26-Nov-2005 Emir Sakic
 + Added mambots/system to chmod check array

26-Nov-2005 Johan Janssens
 ^ Changed help server to dropdown in config
 ^ Changed language prefix to eng_GB (accoording to ISO639-2 and ISO 3166)
 ^ Changed language names to English(GB)
 # Fixed [artf2285] : Installation fails

24-Nov-2005 Emir Sakic
 # Fixed [artf2225] : Email / Print redirects to homepage
 # Fixed [artf1705] : Not same URL for same item : duplicate content

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
 # Fixed [artf2240] : URL encoding entire frontend?
 # Fixed [artf2222] : ampReplace in content.html.php
 # Fixed wrong class call
 + Versioncheck for new_link parameter for mysql_connect.

22-Nov-2005 Johan Janssens
 # Fixed [artf2232] : Installation failure

21-Nov-2005 Marko Schmuck
 # Fixed files.php wrong default value

21-Nov-2005 Johan Janssens
 # Fixed [artf2216] : Extensions Installer
 # Fixed [artf2206] : Registered user only is permitted as manager in the backend

21-Nov-2005 Levis Bisson
 ^ Changed concatenated translation $msg string to sprintf()
 ^ Changed concatenated translation .' '. and ." ". string to sprintf()
 # Fixed [artf2103] : Who's online works partly
 # Fixed [artf2215] : smtp mail -> PHP fatal

20-Nov-2005 Johan Janssens
 # Fixed [artf2196] : Error saving content from back-end
 # Fixed [artf2207] : Remember me option -> PHP fatal

20-Nov-2005 Levis Bisson
 # Fixed Artifact [artf1967] : displays with an escaped apostrophe in both title and TOC.
 # Fixed Artifact [artf2194] : mod_fullmenu - 2 little mistakes

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
 - Reverted [artf2139] : admin menu xhtml
 # Fixed [artf2170] : logged.php does not show logged in people
 # Fixed [artf2175] : Admin main page vanishes when changing list length
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
 # Fixed [artf2122] : Typo in mosGetOS function
 + Added new DS define to shortcut DIRECTORT_SEPERATOR
 + Added new mosHTML::Link, Image and Script function

14-Nov-2005 Andy Miller
 ^ Reimplemented installation with new 'dark' theme

14-Nov-2005 Johan Janssens
 # Fixed [artf2102] : Cpanel: logged.php works displays incomplete info.
 # Fixed [artf2034] : patTemplate - page.html, et al: wrong namespace
 + UTF-8 modifications to the installation (contributed by David Gal)
 ^ Changed all instances of $_LANG to JText
 - Deprecated mosProfiler, use JProfiler instead

14-Nov-2005 Emir Sakic
 + Added support for SEF without mod_rewrite as mambot parameter

14-Nov-2005 Arno Zijlstra
 # Fixed typo in libraries/joomla/factory.php

13-Nov-2005 Johan Janssens
 ^ Renamed mosConfig_mbf_content to mosConfig_multilingual_support
 # Fixed [artf2081] : Contact us: You are not authorized to view this resource.
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
 # Fixed [artf2018] : Admin Menu strings missing
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
 # Fixed [artf2002] : Can't access Site Mambots
 # Fixed [artf2003] : Fatal errors - typos in backend

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
 # Fixed [artf1949] : Typo in back-end com_config.ini
 # Fixed [artf1866] : Alpha1: Content categories don't show

02-Nov-2005 Andrew Eddie
 ^ Reworked ACL ACO's to better align with future requirements

02-Nov-2005 Arno Zijlstra
 ^ Changed footer module
 # Fixed : version include path in joomla installer

02-Nov-2005 Johan Janssens
 + Added XML-RPC support
 # Fixed [artf1918] : Edit News Feeds gives error
 # Fixed [artf1841] : Problem with E-Mail / Print Icon Links

01-Nov-2005 Arno Zijlstra
 + Added footer module english language file

01-Nov-2005 Johan Janssens
 ^ Moved includes/version.php to libraries/joomla/version.php
 # Fixed [artf1901] : english.com_templates.ini
 + Added [artf1895] : Footer as as module

31-Oct-2005 Johan Janssens
 # Fixed : [artf1883] : DESCMENUGROUP twice in english.com_menus.ini
 # Fixed : [artf1891] : When trying to register a new user get Fatal error.

30-Oct-2005 Rey Gigataras
 ^ Upgraded TinyMCE Compressor [1.02]
 ^ Upgraded TinyMCE [2.0 RC4]

30-Oct-2005 Johan Janssens
 # Fixed [artf1878] : english.com_config.ini missing Berlin
 ^ Moved editor/editor.php to libraries/joomla/editor.php
 - Removed editor folder

30-Oct-2005 Levis Bisson
 + Added the new frontend language files (structure)

28-Oct-2005 Samuel Moffatt
 + Library Support Added
 + Added getUserList() and userExists($username) functions to mosUser
 ^ LDAP userbot modified (class moved to libraries)

28-Oct-2005 Johan Janssens
 ^ Changed [artf1719] : Don't run initeditor from template

27-Oct-2005 Marko Schmuck
 # Fixed [artf1805] : Time Offset problem

27-Oct-2005 Johan Janssens
 # Fixed [artf1826] : Typo's in language files
 # Fixed [artf1820] : Call to undefined function: mosmainbody()
 # Fixed [artf1825] : Can't delete uploaded pic from media manager
 # Fixed [artf1818] : Error at "Edit Your Details"
 ^ Moved backtemplate head handling into new mosShowHead_Admin();

27-Oct-2005 Robin Muilwijk
 # Fixed [artf1824], fatal error in Private messaging, backend


-------------------- 1.5.0 Alpha Released [26-Oct-2005] ------------------------


26-Oct-2005 Samuel Moffatt
 # Fixed user login where only the first user bot would be checked.
 # Fixed bug where a new database object with the same username, password and host but different database name would kill Joomla!

26-Oct-2005 Levis Bisson
 # Fixed Artifact [artf1713] : Hardcoded text in searchbot
 # Fixed selectlist finishing by an Hypen

25-Oct-2005 Johan Janssens
 # Fixed [artf1724] : Back end language is not being selected
 # Fixed [artf1784] : Back end language selected on each user not working

25-Oct-2005 Emir Sakic
 # Fixed a bug with live_site appended prefix in SEF
 # $mosConfig_mbf_content missing in mosConfig class
 + Added handle buttons to filter box in content admin managers

23-Oct-2005 Johan Janssens
 # Fixed [artf1684] : Media manager broken
 # Fixed [artf1742] : Can't login in front-end, wrong link
 ^ Artifact [artf1413] : My Settings Page: editor selection option

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
 ^ Artifact [artf1206] : Don't show Database Password in admin area
 ^ Artifact [artf1301] : Expand content title lengths
 ^ Artifact [artf1282] : Easier sorting of static content in creating menu links
 ^ Artifact [artf1162] : Remove hardcoding of <<, <, > and >> in pageNavigation.php

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

