<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Storage;

defined('JPATH_PLATFORM') or die;

/**
 * Cache storage helper functions.
 *
 * @since  1.7.0
 */
class CacheStorageHelper
{
	/**
	 * Cache data group
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $group = '';

	/**
	 * Cached item size
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $size = 0;

	/**
	 * Counter
	 *
	 * @var    integer
	 * @since  1.7.0
	 */
	public $count = 0;

	/**
	 * Constructor
	 *
	 * @param   string  $group  The cache data group
	 *
	 * @since   1.7.0
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
	 * @since   1.7.0
	 */
	public function updateSize($size)
	{
		$this->size += $size;
		$this->count++;
	}
}
