<?php

/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Cache storage helper functions
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.6
 */

class JCacheStorageHelper
{

	public $group = '';
	public $size = 0;
	public $count = 0;

	/**
	 * Increase cache items count
	 * @param	string	$size		Cached item size
	 * @param	string	$group		The cache data group
	 * @since	1.6
	 */
	public function updateSize($size,$group)
	{	
		$this->group = $group;
		$this->size = number_format($this->size + $size, 2);
		$this->count++;
	}
	
}
