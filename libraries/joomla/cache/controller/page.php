<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Cache page type object
 *
 * @since  11.1
 */
class JCacheControllerPage extends JCacheController
{
	/**
	 * ID property for the cache page object.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	protected $_id;

	/**
	 * Cache group
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_group;

	/**
	 * Cache lock test
	 *
	 * @var    stdClass
	 * @since  11.1
	 */
	protected $_locktest = null;

	/**
	 * Get the cached page data
	 *
	 * @param   boolean  $id     The cache data ID
	 * @param   string   $group  The cache data group
	 *
	 * @return  mixed  Boolean false on no result, cached object otherwise
	 *
	 * @since   11.1
	 */
	public function get($id = false, $group = 'page')
	{
		// If an id is not given, generate it from the request
		if (!$id)
		{
			$id = $this->_makeId();
		}

		// If the etag matches the page id ... set a no change header and exit : utilize browser cache
		if (!headers_sent() && isset($_SERVER['HTTP_IF_NONE_MATCH']))
		{
			$etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);

			if ($etag == $id)
			{
				$browserCache = isset($this->options['browsercache']) ? $this->options['browsercache'] : false;

				if ($browserCache)
				{
					$this->_noChange();
				}
			}
		}

		// We got a cache hit... set the etag header and echo the page data
		$data = $this->cache->get($id, $group, true);

		$this->_locktest = (object) array('locked' => null, 'locklooped' => null);

		if ($data === false)
		{
			$this->_locktest = $this->cache->lock($id, $group);

			// If locklooped is true try to get the cached data again; it could exist now.
			if ($this->_locktest->locked === true && $this->_locktest->locklooped === true)
			{
				$data = $this->cache->get($id, $group, true);
			}
		}

		if ($data !== false)
		{
			if ($this->_locktest->locked === true)
			{
				$this->cache->unlock($id, $group);
			}

			$data = JCache::getWorkarounds($data);

			$this->_setEtag($id);

			return $data;
		}

		// Set ID and group placeholders
		$this->_id    = $id;
		$this->_group = $group;

		return false;
	}

	/**
	 * Stop the cache buffer and store the cached data
	 *
	 * @param   mixed    $data        The data to store
	 * @param   string   $id          The cache data ID
	 * @param   string   $group       The cache data group
	 * @param   boolean  $wrkarounds  True to use wrkarounds
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function store($data, $id, $group = null, $wrkarounds = true)
	{
		if ($this->_locktest->locked === false && $this->_locktest->locklooped === true)
		{
			// We can not store data because another process is in the middle of saving
			return false;
		}

		// Get page data from the application object
		if (!$data)
		{
			$data = JFactory::getApplication()->getBody();

			// Only attempt to store if page data exists.
			if (!$data)
			{
				return false;
			}
		}

		// Get id and group and reset the placeholders
		if (!$id)
		{
			$id = $this->_id;
		}

		if (!$group)
		{
			$group = $this->_group;
		}

		if ($wrkarounds)
		{
			$data = JCache::setWorkarounds(
				$data,
				array(
					'nopathway' => 1,
					'nohead'    => 1,
					'nomodules' => 1,
					'headers'   => true,
				)
			);
		}

		$result = $this->cache->store($data, $id, $group, true);

		if ($this->_locktest->locked === true)
		{
			$this->cache->unlock($id, $group);
		}

		return $result;
	}

	/**
	 * Generate a page cache id
	 *
	 * @return  string  MD5 Hash
	 *
	 * @since   11.1
	 * @todo    Discuss whether this should be coupled to a data hash or a request hash ... perhaps hashed with a serialized request
	 */
	protected function _makeId()
	{
		return JCache::makeId();
	}

	/**
	 * There is no change in page data so send an unmodified header and die gracefully
	 *
	 * @return  void
	 *
	 * @since   11.1
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
	 * @param   string  $etag  The entity tag (etag) to set
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function _setEtag($etag)
	{
		JFactory::getApplication()->setHeader('ETag', $etag, true);
	}
}
