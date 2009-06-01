<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Joomla! Cache output type object
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheOutput extends JCache
{
	/**
	 * Start the cache
	 *
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True if the cache is hit (false else)
	 * @since	1.5
	 */
	function start($id, $group=null)
	{
		// If we have data in cache use that...
		$data = $this->get($id, $group);
		if ($data !== false) {
			echo $data;
			return true;
		} else {
			// Nothing in cache... lets start the output buffer and start collecting data for next time.
			ob_start();
			ob_implicit_flush(false);
			// Set id and group placeholders
			$this->_id		= $id;
			$this->_group	= $group;
			return false;
		}
	}

	/**
	 * Stop the cache buffer and store the cached data
	 *
	 * @access	public
	 * @return	boolean	True if cache stored
	 * @since	1.5
	 */
	function end()
	{
		// Get data from output buffer and echo it
		$data = ob_get_contents();
		ob_end_clean();
		echo $data;

		// Get id and group and reset them placeholders
		$id		= $this->_id;
		$group	= $this->_group;
		$this->_id		= null;
		$this->_group	= null;

		// Get the storage handler and store the cached data
		$this->store($data, $id, $group);
	}
}
