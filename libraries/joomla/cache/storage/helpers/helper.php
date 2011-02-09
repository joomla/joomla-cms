<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Cache
 */

defined('JPATH_PLATFORM') or die;

/**
 * Cache storage helper functions.
 *
 * @static
 * @package		Joomla.Platform
 * @subpackage	Cache
 * @since		1.6
 */
class JCacheStorageHelper
{
	/**
	 * @since	1.6
	 */
	public $group = '';

	/**
	 * @since	1.6
	 */
	public $size = 0;

	/**
	 * @since	1.6
	 */
	public $count = 0;

	/**
	 * Constructor
	 *
	 * @param	array	$options	options
	 */
	public function __construct($group)
	{
		$this->group = $group;
	}

	/**
	 * Increase cache items count.
	 *
	 * @param	string	$size	Cached item size
	 * @param	string	$group	The cache data group
	 * @since	1.6
	 */
	public function updateSize($size)
	{
		$this->size = number_format($this->size + $size, 2);
		$this->count++;
	}
}