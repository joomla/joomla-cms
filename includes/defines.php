<?php
/**
 * @version		$Id: defines.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
* Joomla! Application define
 */

//Global definitions
//Joomla framework path definitions
$parts = explode(DS, JPATH_BASE);

//Defines
define('JPATH_ROOT',			implode(DS, $parts));

define('JPATH_SITE',			JPATH_ROOT);
define('JPATH_CONFIGURATION', 	JPATH_ROOT);
define('JPATH_ADMINISTRATOR', 	JPATH_ROOT.DS.'administrator');
define('JPATH_XMLRPC', 		JPATH_ROOT.DS.'xmlrpc');
define('JPATH_LIBRARIES',	 	JPATH_ROOT.DS.'libraries');
define('JPATH_PLUGINS',		JPATH_ROOT.DS.'plugins'  );
define('JPATH_INSTALLATION',	JPATH_ROOT.DS.'installation');
define('JPATH_THEMES'	   ,	JPATH_BASE.DS.'templates');
define('JPATH_CACHE',			JPATH_BASE.DS.'cache');