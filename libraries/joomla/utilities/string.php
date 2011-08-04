<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Utilities
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

// TODO: Wack this into a language file when this gets merged
if (JDEBUG)
{
	JError::raiseWarning(100, "JString has moved to jimport('joomla.string.string'), please update your code.");
}
require_once (JPATH_LIBRARIES . '/joomla/string/string.php');


