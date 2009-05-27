<?php
/**
 * @version		$Id: page.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Joomla! Cache page type object
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCachePage extends JCache
{
	/**
	 * Get the cached page data
	 *
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @return	boolean	True if the cache is hit (false else)
	 * @since	1.5
	 */
	function get($id=false, $group='page')
	{
		// Initialize variables
		$data = false;

		// If an id is not given generate it from the request
		if ($id == false) {
			$id = $this->_makeId();
		}


		// If the etag matches the page id ... sent a no change header and exit : utilize browser cache
		if (!headers_sent() && isset($_SERVER['HTTP_IF_NONE_MATCH'])){
			$etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
			if ($etag == $id) {
				$browserCache = isset($this->_options['browsercache']) ? $this->_options['browsercache'] : false;
				if ($browserCache) {
					$this->_noChange();
				}
			}
		}

		// We got a cache hit... set the etag header and echo the page data
		$data = parent::get($id, $group);
		if ($data !== false) {
			$this->_setEtag($id);
			return $data;
		}

		// Set id and group placeholders
		$this->_id		= $id;
		$this->_group	= $group;
		return false;
	}

	/**
	 * Stop the cache buffer and store the cached data
	 *
	 * @access	public
	 * @return	boolean	True if cache stored
	 * @since	1.5
	 */
	function store()
	{
		// Get page data from JResponse body
		$data = JResponse::getBody();

		// Get id and group and reset them placeholders
		$id		= $this->_id;
		$group	= $this->_group;
		$this->_id		= null;
		$this->_group	= null;

		// Only attempt to store if page data exists
		if ($data) {
			return parent::store($data, $id, $group);
		}
		return false;
	}

	/**
	 * Generate a page cache id
	 * @todo	Discuss whether this should be coupled to a data hash or a request hash ... perhaps hashed with a serialized request
	 *
	 * @access	private
	 * @return	string	MD5 Hash : page cache id
	 * @since	1.5
	 */
	function _makeId()
	{
		return md5(JRequest::getURI());
	}

	/**
	 * There is no change in page data so send a not modified header and die gracefully
	 *
	 * @access	private
	 * @return	void
	 * @since	1.5
	 */
	function _noChange()
	{
		global $mainframe;

		// Send not modified header and exit gracefully
		header('HTTP/1.x 304 Not Modified', true);
		$mainframe->close();
	}

	/**
	 * Set the ETag header in the response
	 *
	 * @access	private
	 * @return	void
	 * @since	1.5
	 */
	function _setEtag($etag)
	{
		JResponse::setHeader('ETag', $etag, true);
	}
}
