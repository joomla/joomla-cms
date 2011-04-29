<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Cache page type object
 *
 * @package		Joomla.Platform
 * @subpackage	Cache
 * @since		11.1
 */
class JCacheControllerPage extends JCacheController
{
	/**
	 * ID property for the cache page object.
	 *
	 * @var		integer
	 * @since	11.1
	 */
	protected $_id;

	/**
	 * Cache group
	 *
	 * @var		string
	 * @since	11.1
	 */
	protected $_group;

	/**
	 * Cache lock test
	 *
	 * @var		object
	 * @since	11.1
	 */
	protected $_locktest = null;

	/**
	 * Get the cached page data
	 *
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 *
	 * @return	boolean	True if the cache is hit (false else)
	 * @since	11.1
	 */
	public function get($id=false, $group='page', $wrkarounds=true)
	{
		// Initialise variables.
		$data = false;

		// If an id is not given generate it from the request
		if ($id == false) {
			$id = $this->_makeId();
		}

		// If the etag matches the page id ... set a no change header and exit : utilize browser cache
		if (!headers_sent() && isset($_SERVER['HTTP_IF_NONE_MATCH'])){
			$etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
			if ($etag == $id) {
				$browserCache = isset($this->options['browsercache']) ? $this->options['browsercache'] : false;
				if ($browserCache) {
					$this->_noChange();
				}
			}
		}

		// We got a cache hit... set the etag header and echo the page data
		$data = $this->cache->get($id, $group);

		$this->_locktest = new stdClass;
		$this->_locktest->locked = null;
		$this->_locktest->locklooped = null;

		if ($data === false) {
			$this->_locktest = $this->cache->lock($id, $group);
			if ($this->_locktest->locked == true && $this->_locktest->locklooped == true) {
				$data = $this->cache->get($id, $group);
			}
		}

		if ($data !== false) {
			$data = unserialize(trim($data));
			if ($wrkarounds === true) {
				$data = JCache::getWorkarounds($data);
			}

			$this->_setEtag($id);
			if ($this->_locktest->locked == true) {
				$this->cache->unlock($id, $group);
			}
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
	 * @return	boolean	True if cache stored
	 * @since	11.1
	 */
	public function store($wrkarounds=true)
	{
		// Get page data from JResponse body
		$data = JResponse::getBody();

		// Get id and group and reset the placeholders
		$id		= $this->_id;
		$group	= $this->_group;
		$this->_id		= null;
		$this->_group	= null;

		// Only attempt to store if page data exists
		if ($data) {
			$data = $wrkarounds==false ? $data : JCache::setWorkarounds($data);

			if ($this->_locktest->locked == false) {
				$this->_locktest = $this->cache->lock($id, $group);
			}

			$sucess = $this->cache->store(serialize($data), $id, $group);

			if ($this->_locktest->locked == true) {
				$this->cache->unlock($id, $group);
			}

			return $sucess;
		}
		return false;
	}

	/**
	 * Generate a page cache id
	 *
	 * @todo	TODO: Discuss whether this should be coupled to a data hash or a request
	 * 			hash ... perhaps hashed with a serialized request
	 *
	 * @return	string	MD5 Hash : page cache id
	 * @since	11.1
	 */
	protected function _makeId()
	{
		//return md5(JRequest::getURI());
		return JCache::makeId();
	}

	/**
	 * There is no change in page data so send a not modified header and die gracefully
	 *
	 * @return	void
	 * @since	11.1
	 */
	protected function _noChange()
	{
		$app = JFactory::getApplication();

		// Send not modified header and exit gracefully
		header('HTTP/1.x 304 Not Modified', true);
		$app->close();
	}

	/**
	 * Set the ETag header in the response
	 *
	 * @return	void
	 * @since	11.1
	 */
	protected function _setEtag($etag)
	{
		JResponse::setHeader('ETag', $etag, true);
	}
}