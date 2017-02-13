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
 * Joomla! Cache callback type object
 *
 * @since  11.1
 */
class JCacheControllerCallback extends JCacheController
{
	/**
	 * Executes a cacheable callback if not found in cache else returns cached output and result
	 *
	 * Since arguments to this function are read with func_get_args you can pass any number of arguments to this method
	 * as long as the first argument passed is the callback definition.
	 *
	 * The callback definition can be in several forms:
	 * - Standard PHP Callback array see <https://secure.php.net/callback> [recommended]
	 * - Function name as a string eg. 'foo' for function foo()
	 * - Static method name as a string eg. 'MyClass::myMethod' for method myMethod() of class MyClass
	 *
	 * @return  mixed  Result of the callback
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public function call()
	{
		// Get callback and arguments
		$args     = func_get_args();
		$callback = array_shift($args);

		return $this->get($callback, $args);
	}

	/**
	 * Executes a cacheable callback if not found in cache else returns cached output and result
	 *
	 * @param   mixed    $callback    Callback or string shorthand for a callback
	 * @param   array    $args        Callback arguments
	 * @param   mixed    $id          Cache ID
	 * @param   boolean  $wrkarounds  True to use wrkarounds
	 * @param   array    $woptions    Workaround options
	 *
	 * @return  mixed  Result of the callback
	 *
	 * @since   11.1
	 */
	public function get($callback, $args = array(), $id = false, $wrkarounds = false, $woptions = array())
	{
		// Normalize callback
		if (is_array($callback) || is_callable($callback))
		{
			// We have a standard php callback array -- do nothing
		}
		elseif (strstr($callback, '::'))
		{
			// This is shorthand for a static method callback classname::methodname
			list ($class, $method) = explode('::', $callback);
			$callback = array(trim($class), trim($method));
		}
		elseif (strstr($callback, '->'))
		{
			/*
			 * This is a really not so smart way of doing this... we provide this for backward compatability but this
			 * WILL! disappear in a future version.  If you are using this syntax change your code to use the standard
			 * PHP callback array syntax: <https://secure.php.net/callback>
			 *
			 * We have to use some silly global notation to pull it off and this is very unreliable
			 */
			list ($object_123456789, $method) = explode('->', $callback);
			global $$object_123456789;
			$callback = array($$object_123456789, $method);
		}

		if (!$id)
		{
			// Generate an ID
			$id = $this->_makeId($callback, $args);
		}

		$data = $this->cache->get($id);

		$locktest = (object) array('locked' => null, 'locklooped' => null);

		if ($data === false)
		{
			$locktest = $this->cache->lock($id);

			// If locklooped is true try to get the cached data again; it could exist now.
			if ($locktest->locked === true && $locktest->locklooped === true)
			{
				$data = $this->cache->get($id);
			}
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
				echo JCache::getWorkarounds(
					$data['output'],
					array('mergehead' => isset($woptions['mergehead']) ? $woptions['mergehead'] : 0)
				);
			}
			else
			{
				echo $data['output'];
			}

			return $data['result'];
		}

		if (!is_array($args))
		{
			$referenceArgs = !empty($args) ? array(&$args) : array();
		}
		else
		{
			$referenceArgs = &$args;
		}

		if ($locktest->locked === false && $locktest->locklooped === true)
		{
			// We can not store data because another process is in the middle of saving
			return call_user_func_array($callback, $referenceArgs);
		}

		$coptions = array();

		if (isset($woptions['modulemode']) && $woptions['modulemode'] == 1)
		{
			$document = JFactory::getDocument();

			if (method_exists($document, 'getHeadData'))
			{
				$coptions['headerbefore'] = $document->getHeadData();
			}

			$coptions['modulemode'] = 1;
		}
		else
		{
			$coptions['modulemode'] = 0;
		}

		$coptions['nopathway'] = isset($woptions['nopathway']) ? $woptions['nopathway'] : 1;
		$coptions['nohead']    = isset($woptions['nohead'])    ? $woptions['nohead'] : 1;
		$coptions['nomodules'] = isset($woptions['nomodules']) ? $woptions['nomodules'] : 1;

		ob_start();
		ob_implicit_flush(false);

		$result = call_user_func_array($callback, $referenceArgs);
		$output = ob_get_clean();

		$data = array('result' => $result);

		if ($wrkarounds)
		{
			$data['output'] = JCache::setWorkarounds($output, $coptions);
		}
		else
		{
			$data['output'] = $output;
		}

		// Store the cache data
		$this->cache->store(serialize($data), $id);

		if ($locktest->locked === true)
		{
			$this->cache->unlock($id);
		}

		echo $output;

		return $result;
	}

	/**
	 * Generate a callback cache ID
	 *
	 * @param   callback  $callback  Callback to cache
	 * @param   array     $args      Arguments to the callback method to cache
	 *
	 * @return  string  MD5 Hash
	 *
	 * @since   11.1
	 */
	protected function _makeId($callback, $args)
	{
		if (is_array($callback) && is_object($callback[0]))
		{
			$vars        = get_object_vars($callback[0]);
			$vars[]      = strtolower(get_class($callback[0]));
			$callback[0] = $vars;
		}

		// A Closure can't be serialized, so to generate the ID we'll need to get its hash
		if (is_a($callback, 'closure'))
		{
			$hash = spl_object_hash($callback);

			return md5($hash . serialize($args));
		}

		return md5(serialize(array($callback, $args)));
	}
}
