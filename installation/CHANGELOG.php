<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;
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
$ -> Language fix or change
+ -> Addition
^ -> Change
- -> Removed
! -> Note

27-May-2009 Rob Schley
 # Fixed checks against gid in com_content. Frontend editing should work now!
 # Fixed checks against gid in com_weblinks submit form.
 - Removed the gid field from the user form.
 # Fixed JElement. Changed it back to a concrete class until other areas are changed to not use it as such.
 # Fixed the group based filtering in com_content admin interface.
 # Fixed part of the component config screen. Still not quite correct but at least it saves now.
 # Fixed the recipients drop down in com_messages administrator interface.
 # Fixed the authors drop down in com_content administrator interface.
 ^ Cleaned up JMenuSite.
 ^ Reworked the JAccess::getAuthorisedUsergroups() method.
 # Fixed some global configuration issues.
 + Added a default access setting to global configuration.

27-May-2009 Andrew Eddie
 - Dropped PEAR package and JArchive::create
 ^ Deprecated JHtmlList::accesslevel, use JHtmlList::accessLevels instead
 ^ Corrected JHtmlGrid::access to use new values (still needs work - not scalable)
 - Deprecated passing hidemainmenu through Toolbar buttons (should be done in the view)
 ! HTML package soft-converted to PHP 5
 ^ Refactored backend com_weblinks; added access control field.

26-May-2009 Andrew Eddie
 + Added new ACL schema
 - Removed jos_core_acl tables
 - Removed jos_groups; adjusted joins in queries
 - Upgraded JFactory
 - Removed phpgacl libraries
 - Removed JAuthorization (replaced with JAcl)
 + Added exclusion support for modules (don't show on page)
 - Removed ADODB compatibility methods from JDatabase
 + Added chaining support to JQuery

25-May-2009 Louis Landry
 + Added JSON registry format.
 ^ Changed default registry format to JSON -- dynamically converts from INI.
 _ Removed unnecessary constructors from plugin classes
 - Removed php4 and 5.0 compatability files
 - Removed JTemplate and pattemplate
 + Added JAccess and supporting libraries.
 ^ Updated JTableUser to support new ACL
 + Added JTableAsset, JTableUsergroup
 + Added Joomla namespace to core.js for Joomla native JavaScript methods.
 ! Installation application is under construction.

25-May-2009 Rob Schley
 - Removed DOMIT from the libraries. Deprecated JFactory::getXMLParser('dom');
 + Added the onBeforeRender plugin event. Refactored some of the event handling logic for applications.
 ^ Ported over Anthony Ferrara's changes to the plugin and event system for better performance.
 - Removed XStandard
 ^ Cleaned up the site index and application files.
 ^ Changed JClass references to JObject.
 # Fixed a fatal error in JForm.
 + Added JController::getInstance() to fetch a controller instance.
 + Added JModelList, JModelItem, and JModelForm.
 + Added JQuery.
 + Added com_users.

22-May-2009 Louis Landry
 - Removed PDF support.
 ^ Moved the openid library package into the plugins/authentication directory.

22-May-2009 Rob Schley
 - Removed phputf8 from the libraries.
 ^ Moved the geshi library package into the plugins/content directory.
 - Removed a ton of legacy code.
 - Removed a ton of deprecated code.
 # Fixed an issue with the Media Manager constantly reloading the page with MooTools 1.2.
 + Added JForm libraries package.
 ^ Removed references to behavior.mootools.
 ^ Fixed the admin menu to work with MooTools 1.2.
 ^ Merged in changes to JHtmlBehavior for the JavaScript framework.
 ^ Updated JS from for Mootools 1.2.

13-May-2009 Ian MacLennan
 # [#13898] There is no translated description for component/module/plugin/etc. after installation
 # [#15417] db->updateObject function outdated
 # [#15727] Module cache, id, Itemid
 # [#16314] PHPDoc Comment for JFactory getDocument method Incorrect
 # [#16349] Revert 16122 and Replace sr-ME language files

09-May-2009 Kevin Devine
 # [#15909] RSS 2.0 feeds try to turn mailto: links into absolute urls
 # [#16211] Atom Link in RSS feed causes validation problems
 # [#16007] JFile::getName() loses first character

05-May-2009 Ian MacLennan
 # [#15541] Article Catagory Layout incorrectly sorted
 # [#15699] Global Configuration Undefined Variable warning
 # [#15740] At login use of task=register instead of view=register
 # [#15853] Search results pagination broken when two or more search terms, SEF enabled
 # [#15912] Error in KEPRI Template, css for icon 'article'
 # [#15963] The align attribute in mod_mainmenu helper causes problems with some templates

24-Apr-2009 Kevin Devine
 # [#15446] Atom feed does not validate for Contact Category and Weblinks Category Layouts
 # [#13890] < Prev and Next > links do not follow SEF rules (duplicate content)
 # [#15691] Need better control of who can upload files
 # [#16112] Change in Montengrin Language ISO Code
 # [#15551] Inconsistent prefix for index.php in com_users #2
 # [#15913] Change in installation version display (patch included)
 # [#15701] Joomla 1.5.10 breaks package installation due to new installer PHP code

-------------------- 1.5.10 Stable Release [27-March-2009] ------------------

27-Mar-2009 Anthony Ferrara
 # Fixed a few issues with the language install packs (double &lt; characters, and a missing file)

27-Mar-2009 Wilco Jansen
 - Removed mk-MK language pack in installer
 + Added be-BY, et-EE, gu-IN, mn-MN and ur-PK language pack in installer
 ^ Updated bg-BG installer language pack
 ^ Updated CREDITS.php with translators information and other contributors
 ^ Changed helpsite reference matching installation language file changes
^! Moderate Priority: A series of XSS and CSRF faults exist in the administrator application. Affected administrator components include com_admin, com_media, com_search. Both com_admin and com_search contain XSS vulnerabilities, and com_media contains 2 CSRF vulnerabilities.
^! Low Priority: A XSS vulnerability exists in the category view of com_content.
 ^ Updated version tags in translation manifest files

25-Mar-2009 Wilco Jansen
 ^ Activated installation check and updated version files

11-Mar-2009 Ian MacLennan
 # [#14515] Atom feed does not validate

9-Mar-2009 Wilco Jansen
 # [#15215] Inconsistent prefix for index.php in com_users

8-Mar-2009 Wilco Jansen
 # [#15300] Missing translation in pagebreak plugin
 # [#15107] Save order enabled and creating random results when active sorting column isn't ordering
 # [#14818] filesystem file move doesn't return false when not read- or writable
 # [#14827] Page break does not work correctly when tables are included in the page sections. The <div></div> pairs are uneven.
 # [#15137] sef plugin does not convert data attribute
 # [#9701]  Installation of Extensions fails when they include zip files
 # [#15044] JArchiveZip::_extractNative() fails to discover zip_open() failure
 # [#14525] pagebreak doesn't work correct if using JCE
 # [#14558] Pagebreak Plugin Does Not Keep Track of Active Page
 # [#14947] Status Module Icon RTL issue
 # [#15266] Bad URL after redirect when Register to Read More in Section, Category Menu Items
 # [#15362] Not valid XHTML output in com_content and com_contact
 # [#15354] com_media loads css files from the backend
 # [#15375] Missing language string
 # [#15376] Login redirect no longer returns you to your current page if no redirect specified
 # [#15233] Search doesn't work with international characters when SEF is enabled
 # [#14514] parameter element id of type folderlist and filelist are not correct
 # [#13285] Wrong tag-IDs used by openid.js
 # [#12119] Changing the module's 'Position' drop-down doesn't change the 'Order' drop-down.
 # [#15359] spambots are still able to see registration page even if registration is turned off
 # [#14071] menu image alignment
 # [#14875] JFolder::folders returns unnecessary warning
 # [#14705] Web link Router incorrectly grabs wrong category item
 # [#15143] Ampersand in Site Name breaks Position field in vCard
 # [#14022] Faulty error handling in JFactory::getXMLParser
 # [#14228] Article alias field missing from in category views
 # [#14381] Incorrect checked out date format in the back-end
 # [#15282] Undefined index HTTP_USER_AGENT in behavior.php
 ! Big thanks to the Joomla Bug Squad for a weekend of hard work!

1-Mar-2009 Ian MacLennan
 # [#12137] Cache Saving module doesn't clear the module's cache data
 # [#13111] Inconsistent encoding behaviour - "&amp;" stays as "&amp;" in articles but is converted to "&" in modules
 # [#13999] When installing a new module, its title is not translated
 # [#14059] Case sensitive image extensions
 # [#14286] Template JA Purity: Link all the text in an article if category is shown
 # [#14565] Omitted UTF-8 modifier in preg_replace function (com_search)
 # [#14758] Article HTML filtering doesn't work if only one Filter group is selected
 # [#14795] JInstallerComponent::_rollback_menu() - Error when getting DB Connector
 # [#14859] untranslated message installing language files for component
 # [#14896] Changes of menu items aren't applied immediately if caching is enabled
 # [#14945] Site Title over Joomla's Logo RTL issue
 # [#15006] Setting Tooltip Offset Breaks Tooltip
 # [#15047] installer.php parseMedia pointing to wrong dir.
 # [#15124] Filter Function on Archived Article Doesn't Work
 # [#15217] custom <installfile> isn't upgraded on component installation
 # [#15237] Loadposition Deletes Dollar signs
 # [#14660] Short open tag in admin.categories.html.php

19-Feb-2009 Wilco Jansen
 # [#14004] Can't sort categories by "order" column in reverse order

15-Feb-2009 Kevin Devine
 # [#15007] Bad Tooltip Help on Section, Category, and Article Alias
 # [#14710] User Details not translated
 # [#14720] •Untranslated strings in com_contact with patch
 # [#14944] Copy Menu Items
 # [#11798] Missing l10n in JApplicationHelper::parseXMLInstallFile()

07-Feb-2009 Kevin Devine
 # [#14865] Directory Persmissions doesn't list correct tmp or log directories
 # [#14767] Menu alias not respecting "active" setting
 # [#14857] Cache Plugin Can Cache Incorrect Data]
 # [#14948] Little tag error in mod_feed

29-Jan-2009 Kevin Devine
 # [#14510] Section List drill down to Category List: Filter doesn't work if global content filters set
 # [#14491] JString::rtrim method incorrect
 # [#14496] Pagebreak outputs incompatible <BR/> elements (XHTML)
 # [#14571] Wrong date in "last updated" in section blog when using beez
 # [#14468] user name word translation not correct in register form.
 # [#14439] Wrong authorEmail in Rhuk Milkyway
 # [#13973] SEF Background Image Fix Breaks Some Javascript ***

-------------------- 1.5.9 Stable Release [9-January-2009] ------------------

09-Jan-2009 Ian MacLennan
 # [#14495] Issue 14398 fix doesn't work with SEF off

09-Jan-2009 Wilco Jansen
 ^ Updated the CREDITS.php file with new list of translators, and some contributers
 ^ Changed  /installation/template/tmpl/finish.html updated link to community site
 ^ Updated language packs to 1.5.9 (thanks JM for the huge job of coordinating this)
 ^ Activated installation check
 ^ Implemented security fixes
 ! Thanks to all who provided the patches and worked hard on testing and documenting this release

06-Jan-2009 Ian MacLennan
 # [#14414] string is unnecessary
 # [#14433] Fix for openid transition issues
 # [#14434] Typo in Cache Manager

05-Jan-2009 Kevin Devine
 # [#14415] Rhuk Milkyway: Some strings unnecessary
 # [#14389] String missing in en-GB.com_installer.ini
 # [#10680] Blank page if vcard is not enabled in the contact parameters, but selected
 # [#13942] The 'modules' view in com_installer does not display author email and url
 # [#14070] Article archive pagination
 # [#14339] JString bug for strspn()
 # [#13949] robots & author metadata lost on copy
 # [#12812] 1.5.7. uri.php changes break backend urls if $live_site=Http (note uppercase H)
 # [#14394] Poll error message strange

05-Jan-2009 Sam Moffatt
 - Removed extraneous com_statistics language file

04-Jan-2009 Ian MacLennan
 # [#14408] Weird characters in LICENSES.php
 # [#14395] e-mail a friend shows language string not the translation
 # [#14374] One english remaining string in mod_search
 # [#11871] vCard displays excess spaces
 # [#14412] 404 if menu access is set to Public and Contact item to Registered
 # [#14402] In Category List, user cannot see what is being filtered
 # [#14391] en-GB.com_statistics.ini should be deleted
 # [#14410] Outdated link in the installer language file
 # [#14317] Cache space not freed
 # [#12577] Small change in components/com_banners/models/banner.php

03-Jan-2009 Ian MacLennan
 # [#14392] Register to read more should redirect to article, not front page.
 # [#14390] Hits Filter in Category List Uses Like Instead of Greater Than or Equal
 # [#14313] short php notation in beez as committed in 12798 creates problems on Windows hosting
 # [#14360] Spacers JAPurity Translatable
 # [#14171] No image
 # [#14106] Changing the access for plugins on the plugins list does not work in 1.5.8
 # [#13811] Wrong license for PHPMailer in CREDITS.php
 # [#13517] CSS errors in rhuk_milkyway/css/template_rtl.css
 # [#12023] Aditional Help server is deleted when helper servers are reset
 # [#11874] New modules cannot be added when there are no modules in the table for a given client
 # [#10840] Cache:filter on view table category doesn't work if cache is enabled
 # [#14227] Fix for old dev.joomla.org links
 # [#14398] Category list loses column sort when you navigate to new page

01-Jan-2009 Wilco Jansen
 ! Revert CHANGELOG.php, COPYRIGHT.php, CREDITS.php, INSTALL.php, LICENSE.php, LICENSES.php to webroot

01-Jan-2009 Ian MacLennan
 # [#12217] Current OpenID used by joomla does not work with Yahoo - OpenID 2.0 protocol is required

30-Dec-2008 Ian MacLennan
 # [#12746] Replace PEAR with a GPL alternative - updated Archive_Tar to relicensed BSD version.

30-Dec-2008 Wilco Jansen
 - Removed CHANGELOG.php, COPYRIGHT.php, CREDITS.php, INSTALL.php, LICENSE.php, LICENSES.php from webroot
 ^ Moved informational php files from previous line to text versions in install/information
 ! See discussion at http://mail.google.com/mail/#inbox/11e86e6661059074

29-Dec-2008 Ian MacLennan
 # [#14308] Spacer Values Not Translatable
 # [#14196] Non registered users can print full pdf of registered article
 # [#13490] Fulltext search for Uncategorized and Archived articles
 # [#11235] RTL feeds PARAM not saved in db + rtl feeds not correctly displayed in Milkyway as well as in Beez/solution included

12-Dec-2008 Kevin Devine
 # [#12868] Contact form 1.5.7 not valid XHTML 1.0 Transitional (+solution)

05-Dec-2008 Kevin Devine
 # [#13909] strpos error on editing alias menu item
 # [#13901] Editor deletes content when default filter is applied: UTF-8 compatibility not enforced with JInputFilter.

26-Nov-2008 Ian MacLennan
 # [#13860] Calling JDatabase::Query() or anything invoking JDatabase::Query() more than once causes SQL error
 # [#12989] 404 - Contact not found - with dropdown in contact view

24-Nov-2008 Ian MacLennan
 # [#13859] Bad file reference in the "Beez" template
 # [#13694] Archive Module count Parameter doesnt work//needs tool tip correction

23-Nov-2008 Ian MacLennan
 # [#13794] Space between meta keywords gone after saving article
 # [#13634] Beez override differences in titles
 # [#13570] Missing h1 text-align in rhuk_milkyway/css/template_rtl.css
 # [#13499] • Cope with ini files wrongly encoded utf8 with bom - patch attached
 # [#13549] typo in sample_data.sql
 # [#13761] Media Manager Javascript error: Object doesn't support this property or method [IE only]
 # [#13700] Contact image changes for Beez override
 # [#13514] Untranslated Strings in en-GB.ini after SVN 11236
 # [#12939] JFolder::delete bug when folder contain symbolic links on folders
 # [#12841] Toolbar & Button Mediamanager
 # [#12730] 'page'-string in plugins/content/pagebreak.php not properly externalized

21-Nov-2008 Ian MacLennan
 # [#13770] Remove default filter for super admin users, fix filter whitelist problem
 # [#13666] Untranslated Strings in admin/mod_feed
 # [#13662] Legacy Plugin - Login Timeout - Refresh
 # [#13633] Tooltip language string in com_config
 # [#13632] Wrong date Last Updated in Beez
 # [#13626] Untranslated strings in com_contact
 # [#13617] JMenu getMenu() doc error
 # [#13611] Inconsistency in login/logout redirection page of mod_login
 # [#13608] Untranslated Strings in com_weblinks
 # [#13584] Beez template override for com_search doesn't echo error message
 # [#13574] JInstallerHelper class function description mistakes
 # [#13542] 'Change Contact Details' link loads wrong page.
 # [#13505] onPrepareContent fix for non com_content Components
 # [#12925] ja_purity default status for the right modules collapsible function does not work
 # [#12887] CSS and XHTML valid error in template JA_Purity///link to css validator broken
 # [#12247] query_batch change a sql in a wrong way
 # [#12115] Cache: plgSystemCache plugin doesn't respect site/page language
 # [#13616] Changes at help screens

18-Nov-2008 Jui-Yu Tsai
 # [#13673] Front end article submission auto-populates "finish publishing" date with same date as "start publishing"

14-Nov-2008 Jui-Yu Tsai
 # [#13045] Contact category incorrect links

10-Nov-2008 Anthony Ferrara
 # Removed Install Check

-------------------- 1.5.8 Stable Release [10-November-2008] ------------------

10-Nov-2008 Anthony Ferrara
 # Added security fixes

10-Nov-2008 Wilco Jansen
 # Changed version tags of default language file to 1.5.8 (Wohnaiki)
 # Change of version file

07-Nov-2008 Sam Moffatt
 # [#13590] New installation languages to get into 1.5.8

01-Nov-2008 Ian MacLennan
 # [#13470] Missing languagestrings in mod_breadcrumbs.ini
 # [#13440] Later versions of cURL break GMail plugin
 # [#13432] Description error
 # [#13412] typo in en-GB.mod_latestnews.ini
 # [#13388] Admin modules xml does not uninstall ini files
 # [#13373] Defaut config change to cope with flash10 issue
 # [#12854] emailcloak whitespace

30-Oct-2008 Kevin Devine
 # [#13326] Beez 'Your Details' page
 # [#12696] **SEF plugin does not convert object param and onmouseover/onmouseout rollovers
 # [#13405] Further Changes to Welcome to Joomla! Module

29-Oct-2008 Sam Moffatt
 # [#13387] Incorrect regexp in JTableUser matches too many fields

28-Oct-2008 Kevin Devine
 # [#13313] Archive is not getdate - 1.5.7

25-Oct-2008 Ian MacLennan
 # [#12920] **Perform isInternal checking on referer values in User Controller
 # [#10954] **Start Publishing is automatically changed upon editing an article when time zone is not set to UTC 00:00
 # [#12798] **Beez Html Folder
 # [#12372] **Secure password is not allowed when editing account details


25-Oct-2008 Ian MacLennan
 # [#13323] **New Welcome to Joomla module and Security Newsfeed module in CPANEL
 # [#12885] **Newsfeed Module Cache is broken
 # [#13165] please select an item message does not append task to javascript pop up
 # [#11798] Missing l10n in JApplicationHelper::parseXMLInstallFile() - patch 2 committed only
 # [#11807] mosMenuCheck missing return statement
 # [#13297] Missing languagestrings in com_menus.ini
 # [#13265] **Remove illegal characters in Article Keywords
 # [#11759] **Bug in menu system when changing menu link type

25-Oct-2008 Sam Moffatt
 # [#13264] **Untranslated Strings in en-GB.tpl_ja_purity.ini
 # [#13262] Untranslated Strings in Unknown file
 # [#13253] Untranslated Strings in en-GB.ini
 # [#13242] print icon on layout "blog category" as first page
 # [#13233] reset button on article selection for menu item doesn't work
 # [#13223] Same language string in Section layout of a menu item**
 # [#13217] **Error in the increase of hits count when editing article in front end
 # [#12878] error language string in mod_feed.ini
 # [#12542] **Inconsistent showing of default records in un-install screens (templates/language)



19-Oct-2008 Ian MacLennan
 # [#12697] **More sample data changes, including UTF-8 Fixes
 # [#12724] **Content Router appends ID to item and is different from the menu (revert 11561)
 # [#12859] **Backend: Disabled Admin Menu = javascript error
 # [#12864] Issue with JMenu and large sites
 # [#12950] **Beez template: The password reset doesn't work at all
 # [#13198] *** Temporary users cannot log out

15-Oct-2008 Kevin Devine
 # [#13076] **Feed module target attribute not validating
 # [#13064] **Problem with mod_related_items Keyword Matching
 # [#12550] **nameQuote() mixed order in strpos
 # [#12625] ** default_form.php in com_search has onClick on button tag which fails XHTML 1.0 Transitional validation

13-Oct-2008 Ian MacLennan
 # [#13164] JFolder::files and JFolder::folders don't function properly when using integral search depth
 # [#13152] Component Directories to be completely deleted upon bogus entry into jos_components
 # [#13151] Wrong wording in language file
 # [#13017] Install language with pdf fonts with both admin and site bug (patch included)
 # [#13015] Frontpage blog wrong number of links
 # [#12942] A tag of category link is not closed in Section blog view
 # [#12602] mod_stats shows wrong time
 # [#12533] Missing method in JRecordSet
 # [#12424] Correct or Remove Show_noauth parameter from sections module
 # [#11707] In category blog layout, edit icon not displayed for user who canEdit if show title is 'No'
 # [#11261] Cache: Undefined variable: rows in /administrator/components/com_cache/cache.class.php on line 114

10-Oct-2008 Kevin Devine
 # [#12722] STRIPOS

06-Oct-2008 Ian MacLennan
 # [#12843] Text 'New ordering saved' cannot be translated in weblinks
 # [#12734] Missing language string JA Purity
 # [#12031] File Not Found or Compoent Not Found When SEARCH Button is Pressed
 # [#12011] E-mail content pop-up window too short in Beez

02-Oct-2008 Ian MacLennan
 # [#12855] Misspelling of category in components/com_content/views/category/view.feed.php

25-Sep-2008 Ian MacLennan
 # [#12778] Remove commercial newsfeeds from joomla installation packages
 # [#12731] Wrong frontpage article after upgrade to 1.5.7
 # [#11521] ****Image does not display in Contacts when an image directory is configured
 # [#12338] URL: mod_login loses ItemID on redirect

22-Sep-2008 Ian MacLennan
 # [10254] Remove brackets around date/time in "Last Updated"
 # [12630] Plural/singular issue for Section list

09-Sep-2008 Anthony Ferrara
 ^ Disabled Installation Check

-------------------- 1.5.7 Stable Release [9-September-2008] ------------------

09-Sep-2008 Wilco Jansen
 # Security fixes, thanks JSST!
 # Changed version tags of default language file to 1.5.7
 # Change of version file

05-Sep-2008 Wilco Jansen
 # Enabled installation check
 # [12543] pagebreak plugin: undefined variable full
 # [12663] Feeds in com_content don't show up more than once when caching is enabled
 # [12519] Clean up Outdated Sample Content
 # [12480] When a guest uses a registered article view url a 403 or 404  is thrown

31-Aug-2008 Wilco Jansen
 # [12039] Cannot override contact list length
 # [12481] When saving or cancellig a frontend edit you are redirected to a blank page instead of the article
 # [12536] Warnings for Path to Image Folder and Path to Media Folder

30-Aug-2008 Wilco Jansen
 # [10175] JRoute::_() and Application Redirect() Causing CGI Error IIS 6
 # [10691] Section/Category Blog fills top->bottom instead of left->right
 # [10943] Error of menu display according to access right
 # [10953] Time zone need to be changed
 # [11330] Box Width Parameter in mod_search Has No Effect
 # [11621] Warning: strpos() [function.strpos]: Empty delimiter
 # [11870] show_noauth problem  in 2 module helpers
 # [12071] SEO: index and follow meta tag in print view
 # [12165] Pagination in com_categories does not respect sectionfilter
 # [12167] Show Search Results" Option in Search Component Not Working
 # [12204] ja_purity template - site logo text goes under header background
 # [12229] Article Order Drop-Down List on Front End Shows Archived and Trashed Articles
 # [12259] Help Key Reference Update for Modules: New Screen
 # [12276] Selection of name/username doesnt work in mod_login
 # [12336] Terms impossible to translate in  admin.newsfeeds.php
 # [12394] Incorrect colspan in admin mod_latest
 # [12425] When a guest tries to view unauthorized content, redirect should be to login not register
 # [12426] Polls and Search use the wrong view - IIS 7
 # [12432] Category search plugin not working
 # [12438] <BR/> tags do not pass html validation
 ! There where a dozen mismatches in the language files, so not all <br /> tags have been replaced,
   new language files for 1.5.7 need to be validated on this issue.
 # [12442] JA Purity SVN changes can break backwards compatibility with existing sites
 # [12462] Menu item is still locked after closing it
 # [12492] Preview an edited artcle does not use template editor.css
 # [12055] Archive intro text is cut to 255 characters - causes formatting problems
 # [12457] Alias is copied incorrectly, when copying an article
 # [12460] $row->getError() all over the shop where $row is not initialized
 # [12194] URL: Incorrect SEF URLs for outgoing recommandation e-mails

29-Aug-2008 Charl van Niekerk
 # [10458] Pagebreak in article in blog layout does not work - limitstart double usage (Tim, Arno)

28-Aug-2008 Andrew Eddie
 # [12110] Beez Template - Newsflash - Article URL not provided when "Title Linkable" is Yes
 # [12033] Menu Separator shows as link instead of plain text when using Legacy Menu Formats
 # [12261] textarea parameter type cannot handle more than one line of data

28-Aug-2008 Charl van Niekerk
 # [11763] RSS feed produces incorrect publish date (Hannes)

27-Aug-2008 Toby Patterson
 # [9343] Profiling J1.5 framework (Dalibor, Hannes)
 # [11018] TMLSelect makes all items selected when using not numeric keys and selected item is 0 (Alessandro)
 # [11255] JMail class ignores JConfig.sendmail path for sendmail (Ernie, Jens)
 # [11535] Coding error in metadata handling of com_content controller.php (John)
 # [12101] Cache: JCacheStorageFile::gc flawed logic in cache expiry (Geraint)
 # [12146] SEO: User/Developer frontend: $document->setMetadata creates duplicate meta tags. (Paul, Mickael)
 # [12382] XMLrpc client id is to high (Emil)
 # [12461] Cache: can't Clean Cache File with Cache Manager when change Cache Handler (Akarawuth)

26-Aug-2008 Charl van Niekerk
 # [9824] alt tags missing for some img tags (Gergo Erdosi)

25-Aug-2008 Toby Patterson
 # [10265] & not replaced with &amp; in the external links (Denis, Hannes)
 # [10384] Single quote in title is escaped twice when editing an article in frontend (Arnault, Bill)
 # [11115] queryBatch does not log queries in debugmode (Ian)
 # [12441] Street Address is not shown in contacts (Eduardo, JBS)

24-Aug-2008 Sam Moffatt
 # [11970] ja_purity email and print button files misnamed and not used

23-Aug-2008 Wilco Jansen
 # [11327] Base path showed in media manager is missing slashes
 # [11544] JSite::getParams() doesn't work as expected
 # [11561] Section blog resulting breadcrumbs issue
 # [12080] System generated RSS feeds not rendering correctly for external URLs
 # [12118] Latest version check &help.j.org at Joomla! Help
 # [12187] Ja_purity default article layout does not display Edit icon for authors
 # [12252] Outdated Links in Welcome to Joomla! and Newsfeed Fixes
 # [12268] Multiple issues with Top menu in JA_Purity
 # [12399] Copying Newsflash Module in Sample Data results in 500 error and duplicate key for menu table
 # [12353] More aritcle links in section blog not working

22-Aug-2008 Toby Patterson
 # [#10965] Not all instances of module are deleted when module is uninstalled (Andrzej and Sam)
 # [#11561] Patch: Beez Contact Image, typo in attribute value (Rene and Elin)

19-Aug-2008 Toby Patterson
 # [#12010] Remove confusing error message about language files for extension installations (thanks Amy && Sam)

13-August-2008 Anthony Ferrara
 ^ Remove install check

-------------------- 1.5.6 Stable Release [12-August-2008] ------------------

05-Aug-2008 Toby Patterson
 # [#10906] Error in JURI::buildQuery if using "param[key]=value" GET parameter

-------------------- 1.5.5 Stable Release [27-July-2008] ---------------------

26-July-2008 Anthony Ferrara
 # [#11973] Section Layout chooses existing Category Blog for drill down using SEF URLs
 # [#11737] Archive and SEO

22-July-2008 Anthony Ferrara
 # [#11682] component login error with SEF
 # [#11888] Archive Article Error With Finish Publishing Date
 # [#11849] Various problems with com_content router
 # [#11875] Item user acces overridden in newsflash module
 # [#11744] searching for article-title doesn't work

21-July-2008 Anthony Ferrara
 # [#11718] Pagebreak plugin still does not work
 # [#11844] SEF Plugin breaks Google Webmaster Tools JS

21-July-2008 Sam Moffatt
 # [#11818] JA_Purity : CSS style not correctly apply to menu in Hornav position
 # [#11698] Issue with & in Menu Manager Unique Name
 # [#10662] Error not warning when there is a submit menu item with the wrong permissions
 # [#11895] Wrong overlib for login
 # [#11892] Determining Read more Tag
 # [#11890] Installing upgrade module results in duplicate module entry
 # [#11873] Upgrading components deletes old component entry
 # [#11838] Clean up of some Todos
 # [#11820] javascript file dtree is corrupt
 # [#11717] Double Titles with 1.5.4 upgrade
 # [#11409] Category List view does not have a parameter in XML for number of items
 # [#10869] newsflash description bad: "random article"?
 # [#8889] BEEZ - search ONLY fieldset alignment

17-July-2008 Mati Kochen
 # [#10823] mosmsg is ignored - added support in legacy plugin (thanks Ian for code suggestions)

16-July-2008 Alan Langford
 # [#11846] Allow "collapse all" on all instances of JPane

15-July-2008 Anthony Ferrara
 # [#11839] Contact item, setting the bad word param stops all mail
 # [#11731] Wrong languagestring in tpl_ja_purity
 # [#11808] Sample data - Promo Books banner incorrect link
 # [#11817] JA-Purity Login module using incorrect code for token
 # [#11676] fix for Newsflash "read more" links in beez template incorrect
 # [#11730] Missing language string in com_content.ini
 # [#11760] Another missing language string in com_content.ini
 # [#11597] Search filter on Private Messaging Doesn't Find any Messages
 # [#11716] Banner using Flash/SWF wrong dimensions
 # [#11806] Notice in article selection for article layout menu items
 # [#11789] forgot your user name not displayed in other languages
 # [#11602] Language file is missing for administrator module mod_feed

8-July-2008 Anthony Ferrara
 # Removal of install check

-------------------- 1.5.4 Stable Release [7-July-2008] ---------------------

6-July-2008 Wilco Jansen
 # Rollback of language file in joomla_backward.sql
 # Included latest language files in installer for 1.5.4

5-July-2008 Andrew Eddie
 # [#11075] Minor errors in ACL libraries (reopened)

4-July-2008 Wilco Jansen
 ^ Change default template back to rhuk_milkyway (all help docs are based upon this template)

4-July-2008 Anthony Ferrara
 # Rollback of language file change (accidental removal of 3 strings)

3-July-2008 Ian MacLennan
 # [#8369] Issues with Page Title and Menu Item Layouts
 # [#10766] table align=right breaks rendering in IE and Opera
 # [#11646] Corrects in spelling and grammar for en-GB site
 # [#11659] Sorting of modules in New module screen is wrong

3-July-2008 Anthony Ferrara
 # [#11647] Corrections (spelling and grammar) to en-GB administrator files (Thanks Ron!)
 # [#11648] Corrections to en-GB installer (Thanks Ron!)
 # [#11618] acl check incorrect in com_users for block user and email events
 # [#11609] Default Article Layout should not have width or colspan="2"
 # [#9234] Article Layout menu type does not restrict Categories by Section in Article listbox
 # [#11639] OpenID Javascript throws errors causes conflicts with Mootools functions
 # [#11627] OnBefore/AfterContentSave Triggers

2-July-2008 Anthony Ferrara
 # [#11643] Cannot save menu item for Submit New Article (Thanks Jens!)
 # [#11638] SVN Rev.10473 breaks path on JS includes (Thanks Jens!)
 # [#11636] [t,297432] htaccess Security Issue
 # [#11635] [Security BUG 1.5.3] User redirect spam (Thanks Ian!)

30-June-2008 Andrew Eddie
 # [11637] Fix htmlentities in com_modules

28-June 2008 Anthony Ferrara
 # [#11583] Updated help screen key refs from doc team  (Thanks Chris Davenport)
 ! Patches provided during the second Pizza Bug and Fun event (28/29 june)

28/29-June 2008 Wilco jansen
 # [9027] Search system use keyword "Search..." if the inputbox empty
 # [10166] Untranslated String in Calendar tool
 # [11249] Set unpublished menu-item as default
 # [11407] error of time display module pool
 # [11464] Contact router broken on SEF and no Itemid
 # [11562] Help screen key reference for Category Manager needs to be variable
 # [11600] JURI::root does not honour live_site setting
 # [11632] Registration redirects upon successful submission of form back to blank registration form
 # [11633] The css for messages in  in milkyway  is incorrect
 ! Patches provided during the second Pizza Bug and Fun event (28/29 june)

27-June-2008 Andrew Eddie
 ^ [11601] Improvement to System Debug plugin
 # [10842] [patch] mod_latest doesn't use JHtml::_('date')
 # [11610] Date in popular module in administrator not GMT

26-June-2008 Wilco Jansen
 # [11190] Unable to delete files with illegal characters
 # [11571] Old cache file can't be deleted when cache is turned off
 # [11580] Missing translation for timezone Venezuela
 # [11463] Dates for votes on poll is not gmt
 # [11598] JFactory::getUser() error when specifying user who doesn't exist
 # [11470] registered content doesn't show up in a public view when show unauthorized links is set to true in a section view
 # [11584] Sort "add module" list in alpha order by column; fix HTML

25-June-2008 Sam Moffatt
 # [11079] Joomla! LDAP Library doesn't support altering details in LDAP

19/20-June-2008 Wilco Jansen
 # [9729] Category Manager - Uncategorized
 # [9901] Patch - com_weblinks Categories view revised tmpl/default.php
 # [10291] Empty URL Parameters
 # [10273] HTML entities in changelog break help display in back-end
 # [10280] New Time Zone in Venezuela
 # [10380] Category description textarea not HTML-quoted
 # [10532] Filter issues in installation and missing or invalid language strings
 # [10877] Error: time connection remains a UTC
 # [10881] "Article order" missing in language file
 # [10944] josSpoofCheck not passing $alternate to josSpoofValue correctly
 # [11326] "validName" popup when omitting user name, password or database name in setup wizard
 # [11444] [Patch] "What is OpenID?" not displayed
 # [11487] Unable to insert image directly after upload without flash uploader
 # [11516] Typo in en-GB.mod_mainmenu.ini
 ! Patches provided during the second Pizza Bug and Fun event

19-June-2008 Wilco Jansen
 # [11225] Expired cache files lead to component not found error message
 ! Patch provided by Anthony. Also unit tests have been created for the caching layer...yay!

15-June-2008 Wilco Jansen
 # [9991] Inconsistent use of nameQuote()
 # [11426] Banners component not able to handle flash banners

8-June-2008 Ian MacLennan
 # [10363] E_NOLOGIN_BLOCKED visible in error/warning

6-June-2008 Wilco Jansen
 # [9806] Menu instance seems to be overriden
 # [11338] Linked category title in JA_Purity category blog causes text to be linked
 # [10873] Frontend Does Not Respect Global List Length
 # [11333] Various weblinks fixes, and a little on category manager
 # [10834] "Umbrella" issue for several mod_newsfeed issues
 # [11354] Typo in gmail authentication plugin

3-June-2008 Sam Moffatt
 ^ prop-set all js, ini and css files to LF line ending style and cleaned up mixed line ending styles

1-June-2008 Sam Moffatt
 ^ prop-set all php files to LF line ending style
 ^ Cleaned up a lot of files with mixed line ending styles
 # [#8957] ampersand in Site name shows up as &amp; in Administrator tool

31-May-2008 Wilco Jansen
 # [10864] PHP session.auto_start leads to broken installation and warning messages
 # [11329] If you have a login menu item, Forgot Password and Forgot Username links don't work
 # [10107] Notice: Trying to get property of non-object in section.php on line 449
 # [10126] External link in breadcrumb
 # [10376] Article editing from FrontPage changes the article alias
 # [11331] Breadcrumbs: redundant parameter, and not respecting default values for parameters
 # [11158] Menu link to unpublished menu item causes fatal error
 # [11191] Missing mandatory check for several menu item types
 # [11075] Minor errors in ACL libraries
 # [11054] Logged in user can view registration form
 # [11311] JA Purity - templateDetails.xml, params - language
 # [8512]  HTML in content items matches search words in search component/plugin (AKA the real big issue with search in Joomla!)
 # [11302] Typos in tpl_ja_purity admin

29-May-2008 Wilco Jansen
 # [10942] Menu does not rebuild sublevel on copy/move (causes menu ordering issues)
 # [10037] timeoffset correction in toISO8601 method of JDate Class
 # [11189] Install site and admin languages with one package

21-May-2008 Anthony Ferrara
 # Fix for fatal error introduced by [#10397] fix.  Supports pass by reference too (so [#10397] is fixed as well)

19-May-2008 Anthony Ferrara
 # [#11111] More robust checking of menu type layouts for JS validation (Thanks Jens!)

18-May-2008 Wilco Jansen
 # [9349] JApplicationHelper::getPath requires strict naming conventions which may be deprecated
 # [10255] Adding Additonal Security to Joomla's File Caching
 # [10397] _processBuildRules and processParseRules  not passing by reference
 # [10793] Two small bugs in com_messages
 # [10949] Cannot Add News Feeds Category List Layout in 1.5.3

14-May-2008 Ian MacLennan
 ^ Updated key reference for help system from screen.menus.type to screen.menus.edit

10-May-2008 Wilco Jansen
 # [9986] Extension installer Install from Directory field should come with the site path prefilled by default.
 # [10412] XHTML validation fails when using category name as a link on frontpage
 # [10811] Category link is not closed on front page in BEEZ template - default_item.php
 # [10498] [PATCH] folders not copied correctly, using <media> in XML
 # [11055] [Security] Crafted URL can disclose absolute path
 # [10226] mod_login has a slightly wrong description for login/logout redirection
 # [10669] Breadcrumbs module always produces last item in pathway

09-May-2008 Andrew Eddie
 + Added ja_purity template by JoomlArt

07-May-2008 Sam Moffatt
 # [10923] Backend accepts any password for custom Super Administrator when LDAP enabled

24-Apr-2008 Mati Kochen
 # Fix for the Legacy-Marker - missing parse
 # Removed the special treatment for RTL in Pagination

-------------------- 1.5.3 Stable Release [22-April-2008] ---------------------

19-Apr-2008 Anthony Ferrara
 # [#10009] Search Function yields warning
 # [#10150] Installation minimum password length doesn't work
 # [#10725] Installation not xhtml compliant
 # [#10739] Spelling error in com_installer.ini
 # [#10092] Switcher hides nested divs
 # Fix for fatal error related to [#10638]

19-Apr-2008 Andrew Eddie
 ! Trailing white-space cleanup
 # [#9725] JFilterInput Infinite Loop

18-Apr-2008 Ian MacLennan
 # [#10732] Help screen updates for Menu Manager

18-Apr-2008 Sam Moffatt
 # [#10724] Custom user groups fail to display
 # [#10707] update link to forum in Sample content
 # [#10638] mod_newsflash renders article separator after last article

17-Apr-2008 Anthony Ferrara
 # [#9858] Flash Uploader not loading properly
 # [#10511] Print button showing Array Print Array
 # [#9775] Cache directory not writable causes warning
 # [#10588] QueryBatch executing empty queries
 # [#10675] Code Cleanup
 # [#10702] JURI::clean fix (not properly stripping out /'s) - Thanks Alex Stylianos
 # [#10308] Installer rejects valid DB names
 # [#10323] Wrong param count for class_exists in TCPDF

14-Apr-2008 Mati Kochen
 + Offline validation
 + Legacy-Marker - a marker to show (admin) extensions requiring Legacy-Mode ON

13-Apr-2008 Sam Moffatt
 # [#10639] mod_newsflash renders bad "read more" link text
 # [#10574] Problem with template rhuk_milkyway in white color variation.
 # [#10540] com_login not w3c valid
 # [#10539] Contacts string repeat twice in com_contacts language file
 # [#10510] /templates/beez/com_content/section/default.php
 # [#10302] Milky Way and Beez lack editor.css files
 # [#9984] Plugin parameters with pipes still not working perfectly
 # [#10402] Mainmenu Module issues
 # [#9977] Search module changing '-' to ':' in keywords
 # [#10097] Various XHTML fixes

10-Apr-2008 Anthony Ferrara
 # [#10508] Caching pathway and breadcrumbs fix
 # [#10329] Debug fails with version of Zend Optimizer

10-Apr-2008 Mati Kochen
 # [#10299] Added 'Use Global' as default value to weblink.xml

09-Apr-2008 Mati Kochen
 # [#10253] Better PDF coding

09-Apr-2008 Mati Kochen
 # [#10297] Fixed RTL in Offline message

04-Apr-2008 Toby Patterson
 # Fixed [#10307] "Select Article" breaks on change category refresh (Thanks Michael)

03-Apr-2008 Toby Patterson
 # Fixed [#10197] component install error fails to reference left over folder in administrator/components folder
 # Fixed [#10200] jdoc:include type="module" not usable
 # Fixed [#10012] $task is not properly passed to extensions
 # Fixed [#10345] emailcloak is not removed if the article does not contain @

29-Mar-2008 Ian MacLennan
 # Fixed [#9335] Extra/Random table class (sectionentrytable0)

29-Mar-2008 Sam Moffatt
 ! Removed old TODO notice in installer

28-Mar-2008 Wilco Jansen
 # Fixed [9118] Uncaught Error message in Extension Manager when uninstalling deleted component
 ! Thanks Ian for the patch

26-Mar-2008 Toby Patterson
 # Fixed [9015] No .blank class in system general.css

-------------------- 1.5.2 Stable Release [22-March-2008] ---------------------

22-Mar-2008 Sam Moffatt
 $ Added ko-KR installation language files

21-Mar-2008 Sam Moffatt
 $ Added lt-LT, pl-PL and ca-ES installation language files

20-Mar-2008 Ian MacLennan
 $ Added bn-IN and th-TH installation language files

20-Mar-2008 Andrew Eddie
 # Fixed double-quoting bug in gacl_api::del_object

15-Mar-2008 Ian MacLennan
 # [#9816] Fixed openid toggle link doesn't appear on component.  Also fixes duplicate ids for com and mod.
 # [#9816] Fixed username cannot contain + or - characters
 # [#9816] Fixed css resulting from first patch above

15-Mar-2008 Sam Moffatt
 ^ Updated language XML files version to 1.5.2 and date to 2008-03-15 (pour JM)

12-Mar-2008 Ian MacLennan
 # [#10156] Param for disabling the Flash Uploader

11-Mar-2008 Anthony Ferrara
 # [#10077] Edit links for frontpage layout broken when not default menu item.

11-Mar-2008 Wilco Jansen
 # [10129] front-end message when article submitted not translated

10-Mar-2008 Wilco Jansen
 # [9971] Default parameter (global configuration) not stored in table
 # [9976] Invalid behavior after switching list length
 # [10112] Strings and tips added for 10019 editing options
 # [10124] Notice layout in milkyway is not right due to missing some css
 # [10071] Email alert for private message is confusing

09-Mar-2008 Mati Kochen
 # [#10083] Upgraded TCPDF Library to v2.6
 # [#10102] Removed unneeded IF clause for ICONV usage

08-Mar-2008 Andrew Eddie
 # [#10103] Additional Content Filtering

07-Mar-2008 Ian MacLennan
 # [#9808] JHtmlSelect::Options dies if empty array passed
 # [#10027] When bulit a menu with catalog list which catalog has no articles, error comes out when click this menu
 # [#10055] Administrator login not possible due to unmasked querys.

07-Mar-2008 Andrew Eddie
 # [#10032] JView::get() does not defer properly to JObject::get()
 # [#9641] Extra <ul /> added by mod_mainmenu in access restricted menus
 # [#10047] Size correction for some parameters pop-ups (patch)
 ^ Massmail BCC checkbox checked by default

05-Mar-2008 Ian MacLennan
 # [#9817] TableUser has sendEmail set to 1 by default instead of 0, while JUser has it set to 0 by default

04-Mar-2008 Anthony Ferrara
 # [#9964] lost password sends a bad link when Joomla is in a directory (Thanks Tomasz Dobrzynski)
 # [#10011] 2 Bugs in com_newsfeed
 # [#9828] Broken Links to blog items
 # [#8679] Incorrect anchors in pagination for admin template

01-Mar-2008 Alan Langford
 ^ Conditional load of JLoader to support unit test.
 + Add jexit() global exit function, also for unit test.
 ^ Replace all non-environment calls to die() and exit() with jexit() (except external libs).
 ^ Make die message on no _JEXEC defined consistent throughout.

29-Feb-2008 Toby Patterson
 # [#8775] Administration Toolbar translation issues

29-Feb-2008 Anthony Ferrara
 # Error Log Library overwriting $date var (fatal error)
 # [#9673] Media Manager + Global paths issues
 # [#9978] Alias URLs don't work when SEF enabled
 * Sanitization of image and media paths in global config
 # Fix for date in com_messages (Thanks Jens)

28-Feb-2008 Anthony Ferrara
 + JFactory::getDate
 + Support for locale based JDate override (for support of non-gregorian calendars)
 ^ Changed all calls from $date = new JDate() to $date = &JFactory::getDate();
 ^ JDate now does the translations on its own (it does not rely on setlocale()) for thread safe function.
 $ Added support for xx-XX.date.php in frontend language directories (to be used for non-gregorian calendars).
 ! all instances of JDate should now be retrieved via JFactory::getDate(); (to allow for overrides)
 # Notice with JTable::isCheckedOut when called statically
 # [#9832] [#9696] Invalid Itemid causes router to choke
 # [#7860] Cache Callback ID not reliable if callback is object
 # [#9715] Development info cached (also fixes tpl=1 case)
 # [#9421] Fix for INI parsing with | in the content
 $ [#9848] DESCNEWITEMSFIRST & LAST added to many places.
 # [#9377] Easier translation and localization
 # Upgrade TCPDF to 2.2.002 (Removes GD, libjpeg and libpng dependancies)
 # [#9968] Fix for router using default menu item vars if non-sef url passed when sef is enabled
 # [#9288] Title not escaped in link for section blog view

28-Feb-2008 Wilco Jansen
 # [9946] Page title issue for contents

28-Feb-2008 Sam Moffatt
 ^ Changed incorrect and misleading text in LDAP Authentication plugin

28-Feb-2008 Ian MacLennan
 # [#9402] Alternative read more
 # [#9909] Newsflash Module returns incorrect SEF URL
 # [#9847] JTable::isCheckedOut() can throw an undefined method error
 # [#9912] Error in sample data
 $ [#9967] 2 missing strings in admin
 # [#7960] JFilterInput

27-Feb-2008 Ian MacLennan
 # [#9648] Cache folder disapearing with legacy mode enabled
 # [#9805] bad url element for content pdf links]

26-Feb-2008 Ian MacLennan
 # [#9845] com_users Login form does not offer OpenId login option
 # [#9844] created date on openid created users is invalid
 # [#8676] OpenID related untranslated strings [js]

26-Feb-2008 Hannes Papenberg
 # [#9916] Saving Article Layout menu does not work

25-Feb-2008 Ian MacLennan
 # [#9932] Typo in file
 # [#9907] Code cleanup com_weblinks, <button> element improperly closed

25-Feb-2008Mati Kochen
 ^ [#9857] Updated TCPDF Library to support RTL - Thanks JM.

23-Feb-2008 Ian MacLennan
 # [#9778] Breadcrumb includes separators
 # [#9513] Search module in rhuk_milkyway - IE6
 # [#8547] Com_media: Unable to delete files with spaces
 # [#9862] Remember me can display confusing error message.


22-Feb-2008 Anthony Ferrara
 # Fix parse_str &amp; issues
 # [#9867] �Hardcoded strings + some errors (Thanks JM)

21-Feb-2008 Ian MacLennan
 # [#9840] •Hard coded string missing translation
 # [#9579] Contact Send-Email Form Routing to Wrong Address
 # [#9739] sefRelToAbs('http://localhost/index.php?option=com_content&view=frontpage&Itemid=1') returns wrong URL

20-Feb-2008 Ian MacLennan
 # [#9807] Notice error in lib/j/html/html/list.php, sign of bigger problem (thanks Jens)

19-Feb-2008 Anthony Ferrara
 # [#9534] Tooltips hidden behind some tabs
 # [#8800] Changing order of articles
 # [#9708] Styling of loadmodule plugin fix.
 # [#9710] mod_feed htmlentities issues.
 # [#9758] Frontend error message for checked out content partially translated

16-Feb-2008 Ian MacLennan
 # [9635] mod_random_image doesn't work as advertised
 # [8230] missing error handler on jfactory getxmlparser

15-Feb-2008 Ian MacLennan
 # [#8684] Errors not correctly trapped on login

14-Feb-2008 Ian MacLennan
 # [#9655] Cannot have more than 1 mootools tree on a page

13-Feb-2008 Ian MacLennan
 # [#9263] Bug in com_search: incorrect highliting of multiple search words
 # [#8738] Backend Login Problems--error message not shown when frontend or blocked user attempts login
 # [#9630] Language strings missing
 # [#9636] mod_banners cannot validate as XHTML 1.0 Strict
 # [#9289] reference to wrapper url produces errors when no modules are loaded
 # [#9719] JDate->toISO8601 suggestion/correction

12-Feb-2008 Ian MacLennan
 # [#9695] Invalid Token message received when trying to authenticate with OpenID
 # [#9006] Incorrect delete section message
 # [#9253] Incorrect caching time of the feed XML in mod_feed
 # [#9490] Fatal error: Call to a member function name() helper.php
 # [#8808] PDF from an article - "contributed by" isof "written by"
 # [#9555] Poll Manager poll's title sorting broken

12-Feb-2008 Anthony Ferrara
 # [#9697] Khepri has type="module" instead of type="modules" for Admin Submenu (Thanks Jens)

11-Feb-2008 Andrew Eddie
 $ Fixed string for XML-RPC server tip (default is no) in com_config.ini

10-Feb-2008 Ian MacLennan
 # Fixed [9371] h3 Title not translated at install step4 and 5
 # Fixed [9697] Khepri has type="module" instead of type="modules" for Admin Submenu

10-Feb-2008 Anthony Ferrara
 # Fixed issue with notice populating $live_site on upgrade from 1.5.0

10-Feb-2008 Sam Moffatt
 # Fixed [#9381] Misnamed variable errors in migration

09-Feb-2008 Ian MacLennan
 # Fixed [8602] Cookie error message in installation process
 # Fixed [9458] Email on new article - "from" is missing
 # Fixed [8368] Template preview shows only used module positions
 # Fixed [9434] Sample data: Two Resource Modules
 # Fixed [9690] Version number in administrator backend shows 1.5.0
 # Fixed [9312] Pre-installation Check wrongly recommends Display Errors ON
 # Fixed [9408] Articles don't change if you change a category to another section


-------------------- 1.5.1 Stable Release [8-February-2008] ---------------------

05-Feb-2008 Anthony Ferrara
 # Fixed [9552] Added missing DOMMIT files
 # Fixed [9620] When trying to login, the site returns 'Invalid Token'
 # Added live_site parameter to config, and JURI::base override (fixes SEF and proxy issues)

05-Feb-2008 Ian MacLennan
 # Fixed [9512] Removed superfluous references to JUser
 # Fixed [9596] Incorrect language string in Beez
 # Fixed [9257] Fixed comments in index.php and administrator/index.php
 # Fixed [9399] XMLRPC Blogger more_text tag problem
 * Fixed [9406] XMLRPC Blogger API

05-Feb-2008 Andrew Eddie
 # Turned XML-RPC server off by default

04-Feb-2008 Wilco Jansen
 # Fixed [9111] error.php contains a relative url to Home Page (Thanks Jens)
 # Fixed [9516] Links in archive module don't work with SEF (Thanks Jens)
 # Fixed [9211] Installation always falling back to joomla_backwards.sql (Thanks Jens)

01-Feb-2008 Ian MacLennan
 # Fixed [#9320] Problem with allowing HTML in requests [patch] (Thanks Jens)

01-Feb-2008 Anthony Ferrara
 * Fixed remote execution vulnerability in phpmailer
 # [#6730] batchQuery() Bug: Broken splitting function
 # [#8776] Mass Email BCC option (Thanks JM)

30-Jan-2008 Anthony Ferrara
 # Fixed htaccess instructions (refering to a second section that was removed)
 # [topic,257873] Fixed possible notice with com_content router
 # [#9518] When creating menu item for a poll, you cannot select poll (Thanks Ian MacLennan)
 # [#9383] Search for contacts generates bad links (Thanks Jens-Christian Skibakk)
 # [#9426] PopUp Url link broken

29-Jan-2008 Ian MacLennan
 # Fixed [#9342] Poll goes 404 after voting - fixed redirect URL.

28-Jan-2008 Anthony Ferrara
 # Fixed memcache session driver config param loading (changed it to work like cache driver)
 # [#9225] Typo in joomla_backwards.sql (Thanks Jens-Christian Skibakk)
 # [#8823] Modules don't show up when eAccelerator is enabled (Thanks Dalibor Karlovic)

28-Jan-2008 Robin Muilwijk
 # Fixed [#9472] Session not cleared properly
 # Fixed [#9291] Error in call method
 # Fixed [#9251] Additional double quote in weblink's template
 # Fixed [#8173] Problem with preg_quote in function utf8_ireplace

27-Jan-2008 Wilco Jansen
 ^ Remove the installation check
 # [9401] Help in backend showind 404 [Patch], thanks Jens-Christian Skibakk for the patch
 # [9412] publish_down is initialized to 1970 in some environments, thanks Kevin for the patch

-------------------- 1.5.0 Stable Release [21-January-2008] ---------------------

21-Jan-2008 Rob Schley
 ^ Updated COPYRIGHT.php to reference the new, consolidated CREDITS.php
 + Added LICENSES.php which will hold full text versions of other licenses.

17-Jan-2008 Anthony Ferrara
 + [8987] [8986] Added 3 Language strings to com_users and com_installer's language files (Thanks JM)
 # [9285] Administrators not being able to edit their own profile or change password

16-Jan-2008 Anthony Ferrara
 # Fixed session issues with Invalid Token randomly appearing
 # Fixed [9255] Error with Pagination and SEF (Thanks Jenscski)

15-Jan-2008 Wilco Jansen
 + Added language af-ZA and ar-DZ

15-Jan-2008 Andrew Eddie
 ^ Encapsulated public/non-public token logic into JUtility::getToken

14-Jan-2008 Wilco Jansen
 # Fixed [8874] Apostrophes transformed in html entities for page titles
 # Fixed [8673] Wrong encoding for "login redirection url" in user login parameters
 ^ Changed fa-IR langiage pack
 + Added tr-TR langiage pack
 ! Patch for 8874 and 8673 provided by Kevin Devine

14-Jan-2008 Andrew Eddie
 # Fixed inconsistend SQL in backward compat file (#__core_acl_aro_sections.section_id renamed to #__core_acl_aro_sections.id)

13-Jan-2008 Anthony Ferrara
 * [8739] Block user issues in administrator fix
 * [topic,252372] Security fix in com_users
 # [9126] [8702] Fixes for imagepath problems in categories:w
 # Fixed language issues
 # Added default alias for all items in core

12-Jan-2008 Wilco Jansen
 # Fixed [9194] No _JEXEC check in bigdump causes information disclosure if called directly

12-Jan-2008 Ian MacLennan
 # Fixed SEF issue for com_newsfeeds.
 # Removed incorrect line endings from some language files.
 # Fixed issue with page cache caching tokens.

11-Jan-2008 Ian MacLennan
 # Fixed SEF issue for com_poll, com_wrapper and com_search

11-Jan-2008 Wilco Jansen
 # Fixed [9032] cannot upload image
 # Fixed [9161] Media Manager - uploads doesn't work with flash tool
 ! Patch provided by Kevin Devine, thanks Kevin!
 ^ Changes language files for hr-HR, lt-LT, ro-RO, ru-RU
 + Added language files for eu-ES, hi-IN

11-Jan-2008 Ian MacLennan
 # Fixed bug in search where small words were not being filtered out properly
 # Fixed problem in search with regex using too many resources (related to above)
 # Fixed [#8404] Incorrect highlighting of search terms (as a byproduct)

10-Jan-2008 Sam Moffatt
 # Fixed error in backlink migration plugin
 # Fixed error with category/section search in front end
 # Fixed error with weblink search in back end
 # Fixed error with Legacy SEF incorrectly returning 404 page not found error

09-Jan-2008 Andy Miller
 # Fixed issues with pillmenu in both LTR and RTL directions

09-Jan-2008 Ian MacLennan
 # Fixed issue with incorrect building of section links in content router

07-Jan-2008 Johan Janssens
 # Fixed issue with JApplication::route wrongly assuming no route was found if no request variables are
   being returned and throwing a 404.

07-Jan-2008 Andrew Eddie
 # Changed form tokens to display different public and logged in values

05-Jan-2008 Rob Schley
 # Refactored routers for com_contact, com_weblinks, com_polls, and com_newsfeeds to be more reliable
   at finding configurations and to prevent duplicate content URL issues.

05-Jan-2008 Louis Landry
 # Fixed [#8228] Empty categories don't display when the show empty category parameter is selected (proposed solution)
 # Fixed [#8301] Memory consumption problems in com_search
 # Fixed [#8432] Mod_polls Validation: JS Unterminated String Literal--problems with quote marks in alias
 # Fixed [#8532] alias fields on menus and com_pool is not correctly sanitized can break links when sef on and cause other errors

05-Jan-2008 Charl van Niekerk
 # Fixed pagination in backend com_weblinks (similar issue as [#8718])
 # Fixed division by zero in com_weblinks frontend and backend if limit = 0

05-Jan-2008 Anthony Ferrara
 # [#8663] File path issues in media manager for IE6 and IE7 (Thanks Jens-Christian Skibakk)
 # [#8452] Mediamanager in IE6 shows one item in each row (Thanks Michal Sobkowiak)
 ^ Fix for pt-PT installation translation file error (from Translation team)

05-Jan-2008 Mati Kochen
 + Added missing POLL string
 - Removed unnecessary "
 ^ fixed locales again
 # [topic,249218] notice when showing subtree with no active parent (thanks trevornorth)

05-Jan-2008 Wilco Jansen
 ^ Updated the installer language files (thanks Ole for providing, thanks translators for creating these files)
 # Fixed [9019] Content of entryfield 'Style' of 'Image' -> 'Appearances' are not saved in Article Editor (Thanks Bruce Scherzinger)
 ! Make sure to save the plugin properties once of the tinymce editor!

05-Jan-2008 Andrew Eddie
 * SECURITY - Hardened escaping of user supplied text strings used in LIKE queries
 ^ Added extra arguments to JDatabase::Quote and JDatabase::getEscaped to facilitate hardening queries
 # Fixed [#8988] Legacy commonhtml.php bug
 # Fixed missing token in offline page

04-Jan-2008 Charl van Niekerk
 # Fixed pagination in backend com_content (similar issue as [#8718])

04-Jan-2008 Louis Landry
 # Fixed JDate issue with server offsets and daylight savings time as well as GMT output

04-Jan-2008 Jui-Yu Tsai
 # Fixed com_messages manager reset filter

04-Jan-2008 Mati Kochen
 ^ [topic,249292] Minor Typos in Sample Data
 # [topic,249199] Added 404 if no Route was found

04-Jan-2008 Alan Langford
 ^ Removed conditionals in loader.php, to revisit after upcoming release.

03-Jan-2008 Jui-Yu Tsai
 # Fixed [#8615][topic,240577] mod_newsflash "Read more..." parameter issue
 # Fixed [topic,248718] com_search gives an error under Beez template
 # Fixed [topic,248716] Author and date in beez template

03-Jan-2008 Anthony Ferrara
 # Fixed untranslated string in timezones (Thanks Ercan �zkaya)

03-Jan-2008 Andrew Eddie
 # Added JHtml::_('form.token') and JRequest::checkToken to assist in preventing CSRF exploits

03-Jan-2008 Alan Langford
 ^ Added conditionals to JLoader, __autoload(), jimport() to aid unit testing.

02-Jan-2008 Mati Kochen
 ^ Added UTF locales to en_GB.xml (admin/installation/site)

02-Jan-2008 Andrew Eddie
 # Fixed CSRF exploits in com_installer

02-Jan-2008 Toby Patterson
 # Fixed problem with JDocumentRendererAtom encoding links resulting in invalid urls (& to &amp;)

02-Jan-2008 Robin Muilwijk
 # Fixed [#8969] Mod_sections missing parameter + patch
 # Fixed [#8828] htaccess does not include rewrite for .htm

02-Jan-2008 Sam Moffatt
 # Fixed radio button selection in com_installer
 ^ Removed administration/media tag from module installer

01-Jan-2008 Chris Davenport
 ^ Local help files replaced by dummy files containing links to online help.

01-Jan-2008 Johan Janssens
 ^ Changed JHtml::_() to support variable prefixes, type can now be prefix.class.function

01-Jan-2008 Wilco Jansen
 ^ Added also front-end language defaulting, see also #8307

01-Jan-2008 Mati Kochen
 # [#8750] Fixed Base URL sent by reminder mail

01-Jan-2008 Sam Moffatt
 ! Welcome to 2008, a great new year for Joomla!
 ^ Updates to the installation system to better handle some situations
 ^ Renamed a variable in the Joomla authentication plugin to make more sense
 # Fixes to prevent against uninitialised variable access in various locations

31-Dec-2007 Mati Kochen
 ^ [topic,247978] Added More Articles string, with corresponding fixes in files
 # [#8935] wrong comparisson for categories

31-Dec-2007 Charl van Niekerk
 # Fixed [#8516] xmlrpc throws errors when using third party blog/content entry tools
 ^ Changed mod_breadcrumbs individual module include to "breadcrumb" position include in rhuk_milkyway and beez
 ^ Renamed "breadcrumbs" position to "breadcrumb" in rhuk_milkyway

31-Dec-2007 Johan Janssens
 + Added scope variable to JApplication

30-Dec-2007 Wilco Jansen
 # Fixed [8307] Local distribs can't define default admin language

30-Dec-2007 Charl van Niekerk
 # Fixed [#8718] Frontend com_weblinks pagination error

30-Dec-2007 Mati Kochen
 # [#8568] Applied proposed fixes
 # [#8797] Added string to com_installer
 # [#7549] type of uninstall not translated
 # [#8901] changed copyright to 2008

30-Dec-2007 Anthony Ferrara
 ^ [#8901] Update copyright date needed in all trunk files
 # [#8736] 'limit' form field ignored in com_search
 ^ Added Istanbul to the timezone listings (Thanks Ercan �zkaya)

29-Dec-2007 Andy Miller
 # Fixed issue with admin login button with Safari

29-Dec-2007 Hannes Papenberg
 # [#8688] fixed pagination in com_categories

29-Dec-2007 Johan Janssens
 + Added transliterate function to JLanguage
 ^ JFilterOutput::stringURLSafe now calls JLanguage::transliterate

29-Dec-2007 Anthony Ferrara
 # [#8690] javascript popup: url not found (images directory incorrect)

29-Dec-2007 Mati Kochen
 ^ change width from 1000px to 960px (khepri)
 # [#8873] added BROWSE string
 # [#8867] fixed (Today) string
 # [#8576] added UNINSTALLLANGPUBLISHEDALREADY to com_installer with the correct call

28-Dec-2007 Hannes Papenberg
 # Fixed [#8229] If Intro Text is set to hide and no Fulltext is available, Intro Text is used as the fulltext

27-Dec-2007 Wilco Jansen
 ! Forgotten to credit Zinho for supplying us with information about the csrf exploit that was fixed
   during PBF weekend. Thanks Zinho for you issue report.

27-Dec-2007 Chris Davenport
 ^ Removed/renamed redundant local help screens.

26-Dec-2007 Nur Aini Rakhmawati
# Fixed [#6111] New button act as Edit when multiply select in Menu Item Manager
# Fixed [t,223403] Warning menu manager standardization for cancel button

25-Dec-2007 Nur Aini Rakhmawati
 # Fixed [#8557] language typo and ordering languange list (Thanks to Ole Bang Ottosen)

24-Dec-2007 Anthony Ferrara
 # Fixed [#8754] issue with SEF plugin rewriting raw anchors (Thanks Jens-Christian Skibakk)

24-Dec-2007 Jui-Yu Tsai
 # Fixed [#8568] language typo

23-Dec-2007 Rob Schley
 # Fixed JRegistryFormatINI::objectToString() method to build proper arrays again. Thanks Ian for testing.
 # Fixed view cache handler not storing module buffer.
 # Fixed JDocumentHTML::getBuffer() so that you can access the entire document buffer.

23-Dec-2007 Nur Aini Rakhmawati
 # Fixed [#8168] Removed Redundant code in Published Section. Thanks Alaattin Kahramanlar

22-Dec-2007 Johan Janssens
 + Added $params parameter to JEditor::display function. This allows to programaticaly set or override
   the editor plugin parameters.

22-Dec-2007 Andrew Eddie
 ^ Moved article edit icon into the print|pdf|email area
 + Added type property to JAuthenticationResponse which is set to the successful authenication method
 ^ Split diff.sql into steps for RC's

21-Dec-2007 Mati Kochen
 ^ [topic,245507] Better Styling with double classes & easier RTL

21-Dec-2007 Anthony Ferrara
 # [#8678] [#8675] [#8648] [topic,245507] Fixed min-width CSS issue forcing scrollbars

21-Dec-2007 Andrew Eddie
 # Fixed [topic,245313] Fatal error in Menu Manager when editing an item
 ! Lots of cosmetic commits (remove trailing ?> tags at EOF, white space, etc)

20-Dec-2007 Jui-Yu Tsai
 # [topic,245322] fixed missing "s" at string for more than one unit

20-Dec-2007 Mickael Maison
 # [#7617] Untranslated error message during authentication

20-Dec-2007 Mati Kochen
 ^ [topic,244583] added $rows = $this->items, and replaced all instaces
 ^ [topic,244213] added limitation to the return pagination only when there is one
 ^ [topic,244895] added missing content display
 ^ [topic,245291] refactor more links to use ContentHelperRoute

20-Dec-2007 Ian MacLennan
 # Fixed Topic 245155 Category Content Filter missing default parameter values in model

20-Dec-2007 Sam Moffatt
 # [#8444] Testing migration script on install - Scripts not executing (added display of current max PHP upload)
 # [#8517] com_installer: Installing from nonexisting URL generates technical error message
 ! SERVER_CONNECT_FAILED language added to com_installer
 ! MAXIMUM UPLOAD SIZE and UPLOADFILESIZE added to installation language
 # [#8628] Extension installer fails to remove media files (proposed solution)
 # [#8573] Google stuff still present in com_search

20-Dec-2007 Andrew Eddie
 # Fixed [t,243324] PHP 4 incompatible syntax in ContentModelArchive::_getList
 # Fixed extra <span> in Content Archive items layout
 # Fixed [#8667] bug in JDate

19-Dec-2007 Ian MacLennan
 # Fixed Content Router swallows up layout (checks to see if it matches Itemid)

19-Dec-2007 Ian MacLennan
 # Fixed topic 244449 XMLRPC Search plugin doesn't work with weblinks search plugin published

-------------------- 1.5.0 Release Candidate 4 Released [19-December-2007] ---------------------
=======

24-Apr-2008 Mati Kochen
 # Fix for the Legacy-Marker - missing parse
 # Removed the special treatment for RTL in Pagination

-------------------- 1.5.3 Stable Release [22-April-2008] ---------------------

19-Apr-2008 Anthony Ferrara
 # [#10009] Search Function yields warning
 # [#10150] Installation minimum password length doesn't work
 # [#10725] Installation not xhtml compliant
 # [#10739] Spelling error in com_installer.ini
 # [#10092] Switcher hides nested divs
 # Fix for fatal error related to [#10638]

19-Apr-2008 Andrew Eddie
 ! Trailing white-space cleanup
 # [#9725] JFilterInput Infinite Loop

18-Apr-2008 Ian MacLennan
 # [#10732] Help screen updates for Menu Manager

18-Apr-2008 Sam Moffatt
 # [#10724] Custom user groups fail to display
 # [#10707] update link to forum in Sample content
 # [#10638] mod_newsflash renders article separator after last article

17-Apr-2008 Anthony Ferrara
 # [#9858] Flash Uploader not loading properly
 # [#10511] Print button showing Array Print Array
 # [#9775] Cache directory not writable causes warning
 # [#10588] QueryBatch executing empty queries
 # [#10675] Code Cleanup
 # [#10702] JURI::clean fix (not properly stripping out /'s) - Thanks Alex Stylianos
 # [#10308] Installer rejects valid DB names
 # [#10323] Wrong param count for class_exists in TCPDF

14-Apr-2008 Mati Kochen
 + Offline validation
 + Legacy-Marker - a marker to show (admin) extensions requiring Legacy-Mode ON

13-Apr-2008 Sam Moffatt
 # [#10639] mod_newsflash renders bad "read more" link text
 # [#10574] Problem with template rhuk_milkyway in white color variation.
 # [#10540] com_login not w3c valid
 # [#10539] Contacts string repeat twice in com_contacts language file
 # [#10510] /templates/beez/com_content/section/default.php
 # [#10302] Milky Way and Beez lack editor.css files
 # [#9984] Plugin parameters with pipes still not working perfectly
 # [#10402] Mainmenu Module issues
 # [#9977] Search module changing '-' to ':' in keywords
 # [#10097] Various XHTML fixes

10-Apr-2008 Anthony Ferrara
 # [#10508] Caching pathway and breadcrumbs fix
 # [#10329] Debug fails with version of Zend Optimizer

10-Apr-2008 Mati Kochen
 # [#10299] Added 'Use Global' as default value to weblink.xml

09-Apr-2008 Mati Kochen
 # [#10253] Better PDF coding

09-Apr-2008 Mati Kochen
 # [#10297] Fixed RTL in Offline message

04-Apr-2008 Toby Patterson
 # Fixed [#10307] "Select Article" breaks on change category refresh (Thanks Michael)

03-Apr-2008 Toby Patterson
 # Fixed [#10197] component install error fails to reference left over folder in administrator/components folder
 # Fixed [#10200] jdoc:include type="module" not usable
 # Fixed [#10012] $task is not properly passed to extensions
 # Fixed [#10345] emailcloak is not removed if the article does not contain @

29-Mar-2008 Ian MacLennan
 # Fixed [#9335] Extra/Random table class (sectionentrytable0)

29-Mar-2008 Sam Moffatt
 ! Removed old TODO notice in installer

28-Mar-2008 Wilco Jansen
 # Fixed [9118] Uncaught Error message in Extension Manager when uninstalling deleted component
 ! Thanks Ian for the patch

26-Mar-2008 Toby Patterson
 # Fixed [9015] No .blank class in system general.css

-------------------- 1.5.2 Stable Release [22-March-2008] ---------------------

22-Mar-2008 Sam Moffatt
 $ Added ko-KR installation language files

21-Mar-2008 Sam Moffatt
 $ Added lt-LT, pl-PL and ca-ES installation language files

20-Mar-2008 Ian MacLennan
 $ Added bn-IN and th-TH installation language files

20-Mar-2008 Andrew Eddie
 # Fixed double-quoting bug in gacl_api::del_object

15-Mar-2008 Ian MacLennan
 # [#9816] Fixed openid toggle link doesn't appear on component.  Also fixes duplicate ids for com and mod.
 # [#9816] Fixed username cannot contain + or - characters
 # [#9816] Fixed css resulting from first patch above

15-Mar-2008 Sam Moffatt
 ^ Updated language XML files version to 1.5.2 and date to 2008-03-15 (pour JM)

12-Mar-2008 Ian MacLennan
 # [#10156] Param for disabling the Flash Uploader

11-Mar-2008 Anthony Ferrara
 # [#10077] Edit links for frontpage layout broken when not default menu item.

11-Mar-2008 Wilco Jansen
 # [10129] front-end message when article submitted not translated

10-Mar-2008 Wilco Jansen
 # [9971] Default parameter (global configuration) not stored in table
 # [9976] Invalid behavior after switching list length
 # [10112] Strings and tips added for 10019 editing options
 # [10124] Notice layout in milkyway is not right due to missing some css
 # [10071] Email alert for private message is confusing

09-Mar-2008 Mati Kochen
 # [#10083] Upgraded TCPDF Library to v2.6
 # [#10102] Removed unneeded IF clause for ICONV usage

08-Mar-2008 Andrew Eddie
 # [#10103] Additional Content Filtering

07-Mar-2008 Ian MacLennan
 # [#9808] JHtmlSelect::Options dies if empty array passed
 # [#10027] When bulit a menu with catalog list which catalog has no articles, error comes out when click this menu
 # [#10055] Administrator login not possible due to unmasked querys.

07-Mar-2008 Andrew Eddie
 # [#10032] JView::get() does not defer properly to JObject::get()
 # [#9641] Extra <ul /> added by mod_mainmenu in access restricted menus
 # [#10047] Size correction for some parameters pop-ups (patch)
 ^ Massmail BCC checkbox checked by default

05-Mar-2008 Ian MacLennan
 # [#9817] TableUser has sendEmail set to 1 by default instead of 0, while JUser has it set to 0 by default

04-Mar-2008 Anthony Ferrara
 # [#9964] lost password sends a bad link when Joomla is in a directory (Thanks Tomasz Dobrzynski)
 # [#10011] 2 Bugs in com_newsfeed
 # [#9828] Broken Links to blog items
 # [#8679] Incorrect anchors in pagination for admin template

01-Mar-2008 Alan Langford
 ^ Conditional load of JLoader to support unit test.
 + Add jexit() global exit function, also for unit test.
 ^ Replace all non-environment calls to die() and exit() with jexit() (except external libs).
 ^ Make die message on no _JEXEC defined consistent throughout.

29-Feb-2008 Toby Patterson
 # [#8775] Administration Toolbar translation issues

29-Feb-2008 Anthony Ferrara
 # Error Log Library overwriting $date var (fatal error)
 # [#9673] Media Manager + Global paths issues
 # [#9978] Alias URLs don't work when SEF enabled
 * Sanitization of image and media paths in global config
 # Fix for date in com_messages (Thanks Jens)

28-Feb-2008 Anthony Ferrara
 + JFactory::getDate
 + Support for locale based JDate override (for support of non-gregorian calendars)
 ^ Changed all calls from $date = new JDate() to $date = &JFactory::getDate();
 ^ JDate now does the translations on its own (it does not rely on setlocale()) for thread safe function.
 $ Added support for xx-XX.date.php in frontend language directories (to be used for non-gregorian calendars).
 ! all instances of JDate should now be retrieved via JFactory::getDate(); (to allow for overrides)
 # Notice with JTable::isCheckedOut when called statically
 # [#9832] [#9696] Invalid Itemid causes router to choke
 # [#7860] Cache Callback ID not reliable if callback is object
 # [#9715] Development info cached (also fixes tpl=1 case)
 # [#9421] Fix for INI parsing with | in the content
 $ [#9848] DESCNEWITEMSFIRST & LAST added to many places.
 # [#9377] Easier translation and localization
 # Upgrade TCPDF to 2.2.002 (Removes GD, libjpeg and libpng dependancies)
 # [#9968] Fix for router using default menu item vars if non-sef url passed when sef is enabled
 # [#9288] Title not escaped in link for section blog view

28-Feb-2008 Wilco Jansen
 # [9946] Page title issue for contents

28-Feb-2008 Sam Moffatt
 ^ Changed incorrect and misleading text in LDAP Authentication plugin

28-Feb-2008 Ian MacLennan
 # [#9402] Alternative read more
 # [#9909] Newsflash Module returns incorrect SEF URL
 # [#9847] JTable::isCheckedOut() can throw an undefined method error
 # [#9912] Error in sample data
 $ [#9967] 2 missing strings in admin
 # [#7960] JFilterInput

27-Feb-2008 Ian MacLennan
 # [#9648] Cache folder disapearing with legacy mode enabled
 # [#9805] bad url element for content pdf links]

26-Feb-2008 Ian MacLennan
 # [#9845] com_users Login form does not offer OpenId login option
 # [#9844] created date on openid created users is invalid
 # [#8676] OpenID related untranslated strings [js]

26-Feb-2008 Hannes Papenberg
 # [#9916] Saving Article Layout menu does not work

25-Feb-2008 Ian MacLennan
 # [#9932] Typo in file
 # [#9907] Code cleanup com_weblinks, <button> element improperly closed

25-Feb-2008Mati Kochen
 ^ [#9857] Updated TCPDF Library to support RTL - Thanks JM.

23-Feb-2008 Ian MacLennan
 # [#9778] Breadcrumb includes separators
 # [#9513] Search module in rhuk_milkyway - IE6
 # [#8547] Com_media: Unable to delete files with spaces
 # [#9862] Remember me can display confusing error message.


22-Feb-2008 Anthony Ferrara
 # Fix parse_str &amp; issues
 # [#9867] �Hardcoded strings + some errors (Thanks JM)

21-Feb-2008 Ian MacLennan
 # [#9840] •Hard coded string missing translation
 # [#9579] Contact Send-Email Form Routing to Wrong Address
 # [#9739] sefRelToAbs('http://localhost/index.php?option=com_content&view=frontpage&Itemid=1') returns wrong URL

20-Feb-2008 Ian MacLennan
 # [#9807] Notice error in lib/j/html/html/list.php, sign of bigger problem (thanks Jens)

19-Feb-2008 Anthony Ferrara
 # [#9534] Tooltips hidden behind some tabs
 # [#8800] Changing order of articles
 # [#9708] Styling of loadmodule plugin fix.
 # [#9710] mod_feed htmlentities issues.
 # [#9758] Frontend error message for checked out content partially translated

16-Feb-2008 Ian MacLennan
 # [9635] mod_random_image doesn't work as advertised
 # [8230] missing error handler on jfactory getxmlparser

15-Feb-2008 Ian MacLennan
 # [#8684] Errors not correctly trapped on login

14-Feb-2008 Ian MacLennan
 # [#9655] Cannot have more than 1 mootools tree on a page

13-Feb-2008 Ian MacLennan
 # [#9263] Bug in com_search: incorrect highliting of multiple search words
 # [#8738] Backend Login Problems--error message not shown when frontend or blocked user attempts login
 # [#9630] Language strings missing
 # [#9636] mod_banners cannot validate as XHTML 1.0 Strict
 # [#9289] reference to wrapper url produces errors when no modules are loaded
 # [#9719] JDate->toISO8601 suggestion/correction

12-Feb-2008 Ian MacLennan
 # [#9695] Invalid Token message received when trying to authenticate with OpenID
 # [#9006] Incorrect delete section message
 # [#9253] Incorrect caching time of the feed XML in mod_feed
 # [#9490] Fatal error: Call to a member function name() helper.php
 # [#8808] PDF from an article - "contributed by" isof "written by"
 # [#9555] Poll Manager poll's title sorting broken

12-Feb-2008 Anthony Ferrara
 # [#9697] Khepri has type="module" instead of type="modules" for Admin Submenu (Thanks Jens)

11-Feb-2008 Andrew Eddie
 $ Fixed string for XML-RPC server tip (default is no) in com_config.ini

10-Feb-2008 Ian MacLennan
 # Fixed [9371] h3 Title not translated at install step4 and 5
 # Fixed [9697] Khepri has type="module" instead of type="modules" for Admin Submenu

10-Feb-2008 Anthony Ferrara
 # Fixed issue with notice populating $live_site on upgrade from 1.5.0

10-Feb-2008 Sam Moffatt
 # Fixed [#9381] Misnamed variable errors in migration

09-Feb-2008 Ian MacLennan
 # Fixed [8602] Cookie error message in installation process
 # Fixed [9458] Email on new article - "from" is missing
 # Fixed [8368] Template preview shows only used module positions
 # Fixed [9434] Sample data: Two Resource Modules
 # Fixed [9690] Version number in administrator backend shows 1.5.0
 # Fixed [9312] Pre-installation Check wrongly recommends Display Errors ON
 # Fixed [9408] Articles don't change if you change a category to another section


-------------------- 1.5.1 Stable Release [8-February-2008] ---------------------

05-Feb-2008 Anthony Ferrara
 # Fixed [9552] Added missing DOMMIT files
 # Fixed [9620] When trying to login, the site returns 'Invalid Token'
 # Added live_site parameter to config, and JURI::base override (fixes SEF and proxy issues)

05-Feb-2008 Ian MacLennan
 # Fixed [9512] Removed superfluous references to JUser
 # Fixed [9596] Incorrect language string in Beez
 # Fixed [9257] Fixed comments in index.php and administrator/index.php
 # Fixed [9399] XMLRPC Blogger more_text tag problem
 * Fixed [9406] XMLRPC Blogger API

05-Feb-2008 Andrew Eddie
 # Turned XML-RPC server off by default

04-Feb-2008 Wilco Jansen
 # Fixed [9111] error.php contains a relative url to Home Page (Thanks Jens)
 # Fixed [9516] Links in archive module don't work with SEF (Thanks Jens)
 # Fixed [9211] Installation always falling back to joomla_backwards.sql (Thanks Jens)

01-Feb-2008 Ian MacLennan
 # Fixed [#9320] Problem with allowing HTML in requests [patch] (Thanks Jens)

01-Feb-2008 Anthony Ferrara
 * Fixed remote execution vulnerability in phpmailer
 # [#6730] batchQuery() Bug: Broken splitting function
 # [#8776] Mass Email BCC option (Thanks JM)

30-Jan-2008 Anthony Ferrara
 # Fixed htaccess instructions (refering to a second section that was removed)
 # [topic,257873] Fixed possible notice with com_content router
 # [#9518] When creating menu item for a poll, you cannot select poll (Thanks Ian MacLennan)
 # [#9383] Search for contacts generates bad links (Thanks Jens-Christian Skibakk)
 # [#9426] PopUp Url link broken

29-Jan-2008 Ian MacLennan
 # Fixed [#9342] Poll goes 404 after voting - fixed redirect URL.

28-Jan-2008 Anthony Ferrara
 # Fixed memcache session driver config param loading (changed it to work like cache driver)
 # [#9225] Typo in joomla_backwards.sql (Thanks Jens-Christian Skibakk)
 # [#8823] Modules don't show up when eAccelerator is enabled (Thanks Dalibor Karlovic)

28-Jan-2008 Robin Muilwijk
 # Fixed [#9472] Session not cleared properly
 # Fixed [#9291] Error in call method
 # Fixed [#9251] Additional double quote in weblink's template
 # Fixed [#8173] Problem with preg_quote in function utf8_ireplace

27-Jan-2008 Wilco Jansen
 ^ Remove the installation check
 # [9401] Help in backend showind 404 [Patch], thanks Jens-Christian Skibakk for the patch
 # [9412] publish_down is initialized to 1970 in some environments, thanks Kevin for the patch

-------------------- 1.5.0 Stable Release [21-January-2008] ---------------------

21-Jan-2008 Rob Schley
 ^ Updated COPYRIGHT.php to reference the new, consolidated CREDITS.php
 + Added LICENSES.php which will hold full text versions of other licenses.

17-Jan-2008 Anthony Ferrara
 + [8987] [8986] Added 3 Language strings to com_users and com_installer's language files (Thanks JM)
 # [9285] Administrators not being able to edit their own profile or change password

16-Jan-2008 Anthony Ferrara
 # Fixed session issues with Invalid Token randomly appearing
 # Fixed [9255] Error with Pagination and SEF (Thanks Jenscski)

15-Jan-2008 Wilco Jansen
 + Added language af-ZA and ar-DZ

15-Jan-2008 Andrew Eddie
 ^ Encapsulated public/non-public token logic into JUtility::getToken

14-Jan-2008 Wilco Jansen
 # Fixed [8874] Apostrophes transformed in html entities for page titles
 # Fixed [8673] Wrong encoding for "login redirection url" in user login parameters
 ^ Changed fa-IR langiage pack
 + Added tr-TR langiage pack
 ! Patch for 8874 and 8673 provided by Kevin Devine

14-Jan-2008 Andrew Eddie
 # Fixed inconsistend SQL in backward compat file (#__core_acl_aro_sections.section_id renamed to #__core_acl_aro_sections.id)

13-Jan-2008 Anthony Ferrara
 * [8739] Block user issues in administrator fix
 * [topic,252372] Security fix in com_users
 # [9126] [8702] Fixes for imagepath problems in categories:w
 # Fixed language issues
 # Added default alias for all items in core

12-Jan-2008 Wilco Jansen
 # Fixed [9194] No _JEXEC check in bigdump causes information disclosure if called directly

12-Jan-2008 Ian MacLennan
 # Fixed SEF issue for com_newsfeeds.
 # Removed incorrect line endings from some language files.
 # Fixed issue with page cache caching tokens.

11-Jan-2008 Ian MacLennan
 # Fixed SEF issue for com_poll, com_wrapper and com_search

11-Jan-2008 Wilco Jansen
 # Fixed [9032] cannot upload image
 # Fixed [9161] Media Manager - uploads doesn't work with flash tool
 ! Patch provided by Kevin Devine, thanks Kevin!
 ^ Changes language files for hr-HR, lt-LT, ro-RO, ru-RU
 + Added language files for eu-ES, hi-IN

11-Jan-2008 Ian MacLennan
 # Fixed bug in search where small words were not being filtered out properly
 # Fixed problem in search with regex using too many resources (related to above)
 # Fixed [#8404] Incorrect highlighting of search terms (as a byproduct)

10-Jan-2008 Sam Moffatt
 # Fixed error in backlink migration plugin
 # Fixed error with category/section search in front end
 # Fixed error with weblink search in back end
 # Fixed error with Legacy SEF incorrectly returning 404 page not found error

09-Jan-2008 Andy Miller
 # Fixed issues with pillmenu in both LTR and RTL directions

09-Jan-2008 Ian MacLennan
 # Fixed issue with incorrect building of section links in content router

07-Jan-2008 Johan Janssens
 # Fixed issue with JApplication::route wrongly assuming no route was found if no request variables are
   being returned and throwing a 404.

07-Jan-2008 Andrew Eddie
 # Changed form tokens to display different public and logged in values

05-Jan-2008 Rob Schley
 # Refactored routers for com_contact, com_weblinks, com_polls, and com_newsfeeds to be more reliable
   at finding configurations and to prevent duplicate content URL issues.

05-Jan-2008 Louis Landry
 # Fixed [#8228] Empty categories don't display when the show empty category parameter is selected (proposed solution)
 # Fixed [#8301] Memory consumption problems in com_search
 # Fixed [#8432] Mod_polls Validation: JS Unterminated String Literal--problems with quote marks in alias
 # Fixed [#8532] alias fields on menus and com_pool is not correctly sanitized can break links when sef on and cause other errors

05-Jan-2008 Charl van Niekerk
 # Fixed pagination in backend com_weblinks (similar issue as [#8718])
 # Fixed division by zero in com_weblinks frontend and backend if limit = 0

05-Jan-2008 Anthony Ferrara
 # [#8663] File path issues in media manager for IE6 and IE7 (Thanks Jens-Christian Skibakk)
 # [#8452] Mediamanager in IE6 shows one item in each row (Thanks Michal Sobkowiak)
 ^ Fix for pt-PT installation translation file error (from Translation team)

05-Jan-2008 Mati Kochen
 + Added missing POLL string
 - Removed unnecessary "
 ^ fixed locales again
 # [topic,249218] notice when showing subtree with no active parent (thanks trevornorth)

05-Jan-2008 Wilco Jansen
 ^ Updated the installer language files (thanks Ole for providing, thanks translators for creating these files)
 # Fixed [9019] Content of entryfield 'Style' of 'Image' -> 'Appearances' are not saved in Article Editor (Thanks Bruce Scherzinger)
 ! Make sure to save the plugin properties once of the tinymce editor!

05-Jan-2008 Andrew Eddie
 * SECURITY - Hardened escaping of user supplied text strings used in LIKE queries
 ^ Added extra arguments to JDatabase::Quote and JDatabase::getEscaped to facilitate hardening queries
 # Fixed [#8988] Legacy commonhtml.php bug
 # Fixed missing token in offline page

04-Jan-2008 Charl van Niekerk
 # Fixed pagination in backend com_content (similar issue as [#8718])

04-Jan-2008 Louis Landry
 # Fixed JDate issue with server offsets and daylight savings time as well as GMT output

04-Jan-2008 Jui-Yu Tsai
 # Fixed com_messages manager reset filter

04-Jan-2008 Mati Kochen
 ^ [topic,249292] Minor Typos in Sample Data
 # [topic,249199] Added 404 if no Route was found

04-Jan-2008 Alan Langford
 ^ Removed conditionals in loader.php, to revisit after upcoming release.

03-Jan-2008 Jui-Yu Tsai
 # Fixed [#8615][topic,240577] mod_newsflash "Read more..." parameter issue
 # Fixed [topic,248718] com_search gives an error under Beez template
 # Fixed [topic,248716] Author and date in beez template

03-Jan-2008 Anthony Ferrara
 # Fixed untranslated string in timezones (Thanks Ercan �zkaya)

03-Jan-2008 Andrew Eddie
 # Added JHtml::_('form.token') and JRequest::checkToken to assist in preventing CSRF exploits

03-Jan-2008 Alan Langford
 ^ Added conditionals to JLoader, __autoload(), jimport() to aid unit testing.

02-Jan-2008 Mati Kochen
 ^ Added UTF locales to en_GB.xml (admin/installation/site)

02-Jan-2008 Andrew Eddie
 # Fixed CSRF exploits in com_installer

02-Jan-2008 Toby Patterson
 # Fixed problem with JDocumentRendererAtom encoding links resulting in invalid urls (& to &amp;)

02-Jan-2008 Robin Muilwijk
 # Fixed [#8969] Mod_sections missing parameter + patch
 # Fixed [#8828] htaccess does not include rewrite for .htm

02-Jan-2008 Sam Moffatt
 # Fixed radio button selection in com_installer
 ^ Removed administration/media tag from module installer

01-Jan-2008 Chris Davenport
 ^ Local help files replaced by dummy files containing links to online help.

01-Jan-2008 Johan Janssens
 ^ Changed JHtml::_() to support variable prefixes, type can now be prefix.class.function

01-Jan-2008 Wilco Jansen
 ^ Added also front-end language defaulting, see also #8307

01-Jan-2008 Mati Kochen
 # [#8750] Fixed Base URL sent by reminder mail

01-Jan-2008 Sam Moffatt
 ! Welcome to 2008, a great new year for Joomla!
 ^ Updates to the installation system to better handle some situations
 ^ Renamed a variable in the Joomla authentication plugin to make more sense
 # Fixes to prevent against uninitialised variable access in various locations

31-Dec-2007 Mati Kochen
 ^ [topic,247978] Added More Articles string, with corresponding fixes in files
 # [#8935] wrong comparisson for categories

31-Dec-2007 Charl van Niekerk
 # Fixed [#8516] xmlrpc throws errors when using third party blog/content entry tools
 ^ Changed mod_breadcrumbs individual module include to "breadcrumb" position include in rhuk_milkyway and beez
 ^ Renamed "breadcrumbs" position to "breadcrumb" in rhuk_milkyway

31-Dec-2007 Johan Janssens
 + Added scope variable to JApplication

30-Dec-2007 Wilco Jansen
 # Fixed [8307] Local distribs can't define default admin language

30-Dec-2007 Charl van Niekerk
 # Fixed [#8718] Frontend com_weblinks pagination error

30-Dec-2007 Mati Kochen
 # [#8568] Applied proposed fixes
 # [#8797] Added string to com_installer
 # [#7549] type of uninstall not translated
 # [#8901] changed copyright to 2008

30-Dec-2007 Anthony Ferrara
 ^ [#8901] Update copyright date needed in all trunk files
 # [#8736] 'limit' form field ignored in com_search
 ^ Added Istanbul to the timezone listings (Thanks Ercan �zkaya)

29-Dec-2007 Andy Miller
 # Fixed issue with admin login button with Safari

29-Dec-2007 Hannes Papenberg
 # [#8688] fixed pagination in com_categories

29-Dec-2007 Johan Janssens
 + Added transliterate function to JLanguage
 ^ JFilterOutput::stringURLSafe now calls JLanguage::transliterate

29-Dec-2007 Anthony Ferrara
 # [#8690] javascript popup: url not found (images directory incorrect)

29-Dec-2007 Mati Kochen
 ^ change width from 1000px to 960px (khepri)
 # [#8873] added BROWSE string
 # [#8867] fixed (Today) string
 # [#8576] added UNINSTALLLANGPUBLISHEDALREADY to com_installer with the correct call

28-Dec-2007 Hannes Papenberg
 # Fixed [#8229] If Intro Text is set to hide and no Fulltext is available, Intro Text is used as the fulltext

27-Dec-2007 Wilco Jansen
 ! Forgotten to credit Zinho for supplying us with information about the csrf exploit that was fixed
   during PBF weekend. Thanks Zinho for you issue report.

27-Dec-2007 Chris Davenport
 ^ Removed/renamed redundant local help screens.

26-Dec-2007 Nur Aini Rakhmawati
# Fixed [#6111] New button act as Edit when multiply select in Menu Item Manager
# Fixed [t,223403] Warning menu manager standardization for cancel button

25-Dec-2007 Nur Aini Rakhmawati
 # Fixed [#8557] language typo and ordering languange list (Thanks to Ole Bang Ottosen)

24-Dec-2007 Anthony Ferrara
 # Fixed [#8754] issue with SEF plugin rewriting raw anchors (Thanks Jens-Christian Skibakk)

24-Dec-2007 Jui-Yu Tsai
 # Fixed [#8568] language typo

23-Dec-2007 Rob Schley
 # Fixed JRegistryFormatINI::objectToString() method to build proper arrays again. Thanks Ian for testing.
 # Fixed view cache handler not storing module buffer.
 # Fixed JDocumentHTML::getBuffer() so that you can access the entire document buffer.

23-Dec-2007 Nur Aini Rakhmawati
 # Fixed [#8168] Removed Redundant code in Published Section. Thanks Alaattin Kahramanlar

22-Dec-2007 Johan Janssens
 + Added $params parameter to JEditor::display function. This allows to programaticaly set or override
   the editor plugin parameters.

22-Dec-2007 Andrew Eddie
 ^ Moved article edit icon into the print|pdf|email area
 + Added type property to JAuthenticationResponse which is set to the successful authenication method
 ^ Split diff.sql into steps for RC's

21-Dec-2007 Mati Kochen
 ^ [topic,245507] Better Styling with double classes & easier RTL

21-Dec-2007 Anthony Ferrara
 # [#8678] [#8675] [#8648] [topic,245507] Fixed min-width CSS issue forcing scrollbars

21-Dec-2007 Andrew Eddie
 # Fixed [topic,245313] Fatal error in Menu Manager when editing an item
 ! Lots of cosmetic commits (remove trailing ?> tags at EOF, white space, etc)

20-Dec-2007 Jui-Yu Tsai
 # [topic,245322] fixed missing "s" at string for more than one unit

20-Dec-2007 Mickael Maison
 # [#7617] Untranslated error message during authentication

20-Dec-2007 Mati Kochen
 ^ [topic,244583] added $rows = $this->items, and replaced all instaces
 ^ [topic,244213] added limitation to the return pagination only when there is one
 ^ [topic,244895] added missing content display
 ^ [topic,245291] refactor more links to use ContentHelperRoute

20-Dec-2007 Ian MacLennan
 # Fixed Topic 245155 Category Content Filter missing default parameter values in model

20-Dec-2007 Sam Moffatt
 # [#8444] Testing migration script on install - Scripts not executing (added display of current max PHP upload)
 # [#8517] com_installer: Installing from nonexisting URL generates technical error message
 ! SERVER_CONNECT_FAILED language added to com_installer
 ! MAXIMUM UPLOAD SIZE and UPLOADFILESIZE added to installation language
 # [#8628] Extension installer fails to remove media files (proposed solution)
 # [#8573] Google stuff still present in com_search

20-Dec-2007 Andrew Eddie
 # Fixed [t,243324] PHP 4 incompatible syntax in ContentModelArchive::_getList
 # Fixed extra <span> in Content Archive items layout
 # Fixed [#8667] bug in JDate

19-Dec-2007 Ian MacLennan
 # Fixed Content Router swallows up layout (checks to see if it matches Itemid)

19-Dec-2007 Ian MacLennan
 # Fixed topic 244449 XMLRPC Search plugin doesn't work with weblinks search plugin published

-------------------- 1.5.0 Release Candidate 4 Released [19-December-2007] ---------------------
