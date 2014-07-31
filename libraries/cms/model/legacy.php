<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.component.model');

/**
 * Alias to JModel for forward compatability.
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       2.5.5
 */
class JModelLegacy extends JModel
{
	/**
	 * Add a directory where JModel should search for models. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   mixed   $path    A path or array[sting] of paths to search.
	 * @param   string  $prefix  A prefix for models.
	 *
	 * @return  array  An array with directory elements. If prefix is equal to '', all directories are returned.
	 *
	 * @since   2.5.5
	 */
	public static function addIncludePath($path = '', $prefix = '')
	{
		return parent::addIncludePath($path, $prefix);
	}
}
