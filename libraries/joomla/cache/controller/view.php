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
 * Joomla! Cache view type object
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.6
 */
class JCacheControllerView extends JCacheController
{
	/**
	 * Get the cached view data
	 *
	 * @param	object	$view	The view object to cache output for
	 * @param	string	$method	The method name of the view method to cache output for
	 * @param	string	$group	The cache data group
	 * @param	string	$id		The cache data id
	 * @return	boolean	True if the cache is hit (false else)
	 * @since	1.6
	 */
	public function get(&$view, $method, $id=false, $wrkarounds=true)
	{
		// If an id is not given generate it from the request
		if ($id == false) {
			$id = $this->_makeId($view, $method);
		}

		$data = false;
		$data = $this->cache->get($id);

		$locktest = new stdClass;
		$locktest->locked = null;
		$locktest->locklooped = null;

		if ($data === false) {
			$locktest = $this->cache->lock($id,null);
			// if the loop is completed and returned true the means the lock has been set
			// if looped is true try to get the cached data again; it could exist now
			if ($locktest->locked == true && $locktest->locklooped == true) {
				$data = $this->cache->get($id);
			}
			// false means that locking is either turned off or maxtime has been exceeeded, execute the view
		}

		if ($data !== false) {
			$data		= unserialize($data);

			if ($wrkarounds === true) {
				echo JCache::getWorkarounds($data);
			}

			else {  // no workarounds, all data is stored in one piece
				echo (isset($data)) ? $data : null;
			}

			if ($locktest->locked == true) {
				$this->cache->unlock($id);
			}

			return true;
		}

		/*
		 * No hit so we have to execute the view
		 */
		if (method_exists($view, $method)) {
			// if previous lock failed try again
			if ($locktest->locked == false) {
				$locktest = $this->cache->lock($id,null);
			}

			// Capture and echo output
			ob_start();
			ob_implicit_flush(false);
			$view->$method();
			$data = ob_get_contents();
			ob_end_clean();
			echo $data;

			/*
			 * For a view we have a special case.  We need to cache not only the output from the view, but the state
			 * of the document head after the view has been rendered.  This will allow us to properly cache any attached
			 * scripts or stylesheets or links or any other modifications that the view has made to the document object
			 */
			$cached = array();

			$cached = $wrkarounds == true ? JCache::setWorkarounds($data) : $data;

			// Store the cache data
			$this->cache->store(serialize($cached), $id);

			if ($locktest->locked == true) {
				$this->cache->unlock($id);
			}
		}
		return false;
	}

	/**
	 * Generate a view cache id.
	 *
	 * @param	object	$view	The view object to cache output for
	 * @param	string	$method	The method name to cache for the view object
	 * @return	string	MD5 Hash : view cache id
	 * @since	1.6
	 */
	private function _makeId(&$view, $method)
	{
		return md5(serialize(array(JCache::makeId(), get_class($view), $method)));
	}
}