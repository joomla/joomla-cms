<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// TODO: Wack this into a language file when this gets merged
if (JDEBUG)
{
	JError::raiseWarning(100, "JLog has moved to jimport('joomla.log.log'), please update your code.");
	JError::raiseWarning(100, "JLog has changed its behaviour; please update your code.");
}
require_once JPATH_LIBRARIES . '/joomla/log/log.php';
