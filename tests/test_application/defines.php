<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_BASE') or die;

/**
 * Joomla! Application define.
 */

//Global definitions.
//Joomla framework path definitions.
$parts = explode(DS, JPATH_BASE);
array_pop($parts);
array_pop($parts);

//Defines.
define('JPATH_ROOT',			JPATH_BASE);
define('JPATH_CONFIGURATION',	JPATH_ROOT);
define('JPATH_PLATFORM',		implode(DS, $parts).'/libraries');
define('JPATH_PLUGINS',			JPATH_ROOT.DS.'plugins');
define('JPATH_THEMES',			JPATH_BASE.DS.'templates');
define('JPATH_CACHE',			JPATH_BASE.DS.'cache');
define('JPATH_MANIFESTS',		JPATH_BASE.DS.'manifests');

