<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_maps
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_maps
 *
 * @package     Joomla.Site
 * @subpackage  mod_maps
 * @since       3.2
 */
class ModMapsHelper
{
	/*
	 * @since  3.2
	 */
	public static function getSize(&$size)
	{
		return (strpos($size, '%') === false ? $size . 'px' : $size);
	}
}
