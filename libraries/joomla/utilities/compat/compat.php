<?php
/**
* @version		$Id:compat.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Compatibility
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Load compatibility files
 *
 * @package		Joomla.Framework
 * @subpackage	Compatibility
 * @since		1.5
 */
if (version_compare( phpversion(), '5.0' ) < 0) {
	jimport('joomla.utilities.compat.php50x' );
}