<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache\Controller;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\CacheController;

/**
 * Joomla! Cache view type object
 *
 * @since  1.7.0
 */
class ViewController extends CacheController
{
	/**
	 * Get the cached view data
	 *
	 * @param   object   $view        The view object to cache output for
	 * @param   string   $method      The method name of the view method to cache output for
	 * @param   mixed    $id          The cache data ID
	 * @param   boolean  $wrkarounds  True to enable workarounds.
	 *
	 * @return  boolean  True if the cache is hit (false else)
	 *
	 * @since   1.7.0
	 */
	public function get($view, $method = 'display', $id = false, $wrkarounds = true)
	{
		// If an id is not given generate it from the request
		if (!$id)
		{
			$id = $this->_makeId($view, $method);
		}

		$data = $this->cache->get($id);

		$locktest = (object) array('locked' => null, 'locklooped' => null);

		if ($data === false)
		{
			$locktest = $this->cache->lock($id);

			/*
			 * If the loop is completed and returned true it means the lock has been set.
			 * If looped is true try to get the cached data again; it could exist now.
			 */
			if ($locktest->locked === true && $locktest->locklooped === true)
			{
				$data = $this->cache->get($id);
			}

			// False means that locking is either turned off or maxtime has been exceeded. Execute the view.
		}

		if ($data !== false)
		{
			if ($locktest->locked === true)
			{
				$this->cache->unlock($id);
			}

			$data = unserialize(trim($data));

			if ($wrkarounds)
			{
				echo Cache::getWorkarounds($data);
			}
			else
			{
				// No workarounds, so all data is stored in one piece
				echo $data;
			}

			return true;
		}

		// No hit so we have to execute the view
		if (!method_exists($view, $method))
		{
			return false;
		}

		if ($locktest->locked === false && $locktest->locklooped === true)
		{
			// We can not store data because another process is in the middle of saving
			$view->$method();

			return false;
		}

		// Capture and echo output
		ob_start();
		ob_implicit_flush(false);
		$view->$method();
		$data = ob_get_clean();
		echo $data;

		/*
		 * For a view we have a special case.  We need to cache not only the output from the view, but the state
		 * of the document head after the view has been rendered.  This will allow us to properly cache any attached
		 * scripts or stylesheets or links or any other modifications that the view has made to the document object
		 */
		if ($wrkarounds)
		{
			$data = Cache::setWorkarounds($data);
		}

		// Store the cache data
		$this->cache->store(serialize($data), $id);

		if ($locktest->locked === true)
		{
			$this->cache->unlock($id);
		}

		return false;
	}

	/**
	 * Generate a view cache ID.
	 *
	 * @param   object  $view    The view object to cache output for
	 * @param   string  $method  The method name to cache for the view object
	 *
	 * @return  string  MD5 Hash
	 *
	 * @since   1.7.0
	 */
	protected function _makeId($view, $method)
	{
		return md5(serialize(array(Cache::makeId(), get_class($view), $method)));
	}
}
