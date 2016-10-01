<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Cache storage helper functions.
 *
 * @since  11.1
 */
class JCacheStorageHelper
{
	/**
	 * Cache data group
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $group = '';

	/**
	 * Cached item size
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $size = 0;

	/**
	 * Counter
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $count = 0;

	/**
	 * Constructor
	 *
	 * @param   string  $group  The cache data group
	 *
	 * @since   11.1
	 */
	public function __construct($group)
	{
		$this->group = $group;
	}

	/**
	 * Increase cache items count.
	 *
	 * @param   string  $size  Cached item size
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function updateSize($size)
	{
		$this->size = number_format((float) $this->size + (float) $size, 2, JText::_('DECIMALS_SEPARATOR'), JText::_('THOUSANDS_SEPARATOR'));
		$this->count++;
	}
}
