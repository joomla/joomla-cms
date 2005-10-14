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


--------------------

14-Oct-2005 Johan Janssens
 + Added userbot group
 + Added Joomla, LDAP and example userbots
 + Added onUserLogin and onUserLogout triggers
 + Added backend language chooser on login page

13-Oct-2005 Andy Miller
 # Fixed artf1504 : rhuk_solarflare_ii Template | Menus with " not displaying correctly

13-Oct-2005 Rey Gigataras
 # Fixed duplicated module creation in install
 # Fixed XHTML issue in rss feed module
 # Fixed XHTML issue in com_search
 # Fixed artf1550 : Properly SEFify com_registration links
 # Fixed artf1533 : rhuk_solarflare_ii 2.2 active_menu
 # Fixed artf1354 : Can't create new user
 # Fixed artf1433 : Images in Templates
 # Fixed artf1531 : RSS Feed showing wrong livesite URL

12-Oct-2005 Marko Schmuck
 * Fixed securitybug in admin.content.html.php when 2 logged in and try to edit the same content [ Low Level Security Bug ]

12-Oct-2005 Andy Miller
 + Added advanced SSL support plus new mosLink() method for 3pd's to use

12-Oct-2005 Johan Janssens
 # Fixed artf1266 : gzip compression conflict
 # Fixed artf1453 : Weblink item missing approved parameter
 # Fixed artf1452 : Error deleting Language file
 # Fixed artf1373 : Pagination error

12-Oct-2005 Rey Gigataras
 # Fixed bug in Global Config param `Time Offset`
 # Fixed artf1414 : Missing images in HTML_toolbar
 # Fixed artf1513 : PDF format does not work at version 1.0.2

11-Oct-2005 Rey Gigataras
 * Fixed Search Component flooding, by limiting searching to between 3 and 20 characters [ Low Level Security Bug in 1.0.x ]
 ^ Blog - Content Category Archive will no longer show dropdown selector when coming from Archive Module
 # Fixed artf1470 : Archives not working in the front end
 # Fixed artf1495 : Frontend Archive blog display
 # Fixed artf1364 : TinyMCE loads wrong template styles
 # Fixed artf1494 : Template fault in offline preview
 # Fixed artf1497 : mosemailcloak adds trailing space
 # Fixed artf1493 : mod_whosonline.php

09-Oct-2005 Rey Gigataras
 * Fixed SQL injection bug in content submission [ Medium Level Security Bug in 1.0.x ]
 * Fixed artf1405 : Joomla shows Items to unauthorized users [ Low Level Security Bug in 1.0.2 ]
 # Fixed artf1454 : After update email_cloacking bot is always on
 # Fixed artf1447 : Bug in mosloadposition mambot
 # Fixed artf1483 : SEF default .htaccess file settings are too lax
 # Fixed artf1480 : Administrator type user can loggof Super Adminstrator
 # Fixed artf1422 : PDF Icon is set to on when it should be off
 # Fixed artf1476 : Error at "number of Trashed Items" in sections
 # Fixed artf1415 : Wrong image in editList() function of mosToolBar class

08-Oct-2005 Johan Janssens
 # Fixed artf1384 : tinyMCE doesnt save converted entities

07-Oct-2005 Andy Miller
 # Fixed tabpane css font issue

07-Oct-205 Andy Stewart
 # Fixed artf1382 : Added installation check to ensure "//" is not generated via PHP_SELF
 # Fixed artf1439 : Used correct ErrorMsg function and updated javascript redirect to remove POSTDATA message
 # Fixed artf1400 : Added a check of $other within com_categories to skip section exists check if set to "other"

07-Oct-2005 Johan Janssens
 # Fixed artf1421 : unneeded file includes\domit\testing_domit.php
 # Fixed artf1439 : Used correct ErrorMsg function and updated javascript redirect to remove POSTDATA message
 # Fixed artf1400 : Added a check of $other within com_categories to skip section exists check if set to "other"

05-Oct-2005 Robin Muilwijk
 # Fixed artf1366 : Typo in admin, Adding a new menu item - Blog Content Category

----- Branched from Joomla 1.0 on 2 october 2005 -----

2. Copyright and disclaimer
---------------------------
This application is opensource software released under the GPL.  Please
see source code and the LICENSE file
