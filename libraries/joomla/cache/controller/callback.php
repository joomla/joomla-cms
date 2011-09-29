<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

jimport('joomla.cache.controller');

/**
 * Joomla! Cache callback type object
 *
 * @package     Joomla.Platform
 * @subpackage  Cache
 * @since       11.1
 */
class JCacheControllerCallback extends JCacheController
{
	/**
	 * Executes a cacheable callback if not found in cache else returns cached output and result
	 *
	 * Since arguments to this function are read with func_get_args you can pass any number of
	 * arguments to this method
	 * as long as the first argument passed is the callback definition.
	 *
	 * The callback definition can be in several forms:
	 * - Standard PHP Callback array see <http://php.net/callback> [recommended]
	 * - Function name as a string eg. 'foo' for function foo()
	 * - Static method name as a string eg. 'MyClass::myMethod' for method myMethod() of class MyClass
	 *
	 * @return  mixed  Result of the callback
	 *
	 * @since   11.1
	 */
	public function call()
	{
		// Get callback and arguments
		$args = func_get_args();
		$callback = array_shift($args);

		return $this->get($callback, $args);
	}

	/**
	 * Executes a cacheable callback if not found in cache else returns cached output and result
	 *
	 * @param   mixed    $callback    Callback or string shorthand for a callback
	 * @param   array    $args        Callback arguments
	 * @param   string   $id          Cache id
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
		if (is_array($callback))
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
			 * PHP callback array syntax: <http://php.net/callback>
			 *
			 * We have to use some silly global notation to pull it off and this is very unreliable
			 */
			list ($object_123456789, $method) = explode('->', $callback);
			global $$object_123456789;
			$callback = array($$object_123456789, $method);
		}
		else
		{
			// We have just a standard function -- do nothing
		}

		if (!$id)
		{
			// Generate an ID
			$id = $this->_makeId($callback, $args);
		}

		$data = false;
		$data = $this->cache->get($id);

		$locktest = new stdClass;
		$locktest->locked = null;
		$locktest->locklooped = null;

		if ($data === false)
		{
			$locktest = $this->cache->lock($id);
			if ($locktest->locked == true && $locktest->locklooped == true)
			{
				$data = $this->cache->get($id);
			}
		}

		$coptions = array();

		if ($data !== false)
		{

			$cached = unserialize(trim($data));
			$coptions['mergehead'] = isset($woptions['mergehead']) ? $woptions['mergehead'] : 0;
			$output = ($wrkarounds == false) ? $cached['output'] : JCache::getWorkarounds($cached['output'], $coptions);
			$result = $cached['result'];
			if ($locktest->locked == true)
			{
				$this->cache->unlock($id);
			}

		}
		else
		{

			if (!is_array($args))
			{
				$Args = !empty($args) ? array(&$args) : array();
			}
			else
			{
				$Args = &$args;
			}

			if ($locktest->locked == false)
			{
				$locktest = $this->cache->lock($id);
			}

			if (isset($woptions['modulemode']))
			{
				$document = JFactory::getDocument();
				$coptions['modulemode'] = $woptions['modulemode'];
				$coptions['headerbefore'] = $document->getHeadData();
			}
			else
			{
				$coptions['modulemode'] = 0;
			}

			ob_start();
			ob_implicit_flush(false);

			$result = call_user_func_array($callback, $Args);
			$output = ob_get_contents();

			ob_end_clean();

			$cached = array();

			$coptions['nopathway'] = isset($woptions['nopathway']) ? $woptions['nopathway'] : 1;
			$coptions['nohead'] = isset($woptions['nohead']) ? $woptions['nohead'] : 1;
			$coptions['nomodules'] = isset($woptions['nomodules']) ? $woptions['nomodules'] : 1;

			$cached['output'] = ($wrkarounds == false) ? $output : JCache::setWorkarounds($output, $coptions);
			$cached['result'] = $result;

			// Store the cache data
			$this->cache->store(serialize($cached), $id);
			if ($locktest->locked == true)
			{
				$this->cache->unlock($id);
			}
		}

		echo $output;
		return $result;
	}

	/**
	 * Generate a callback cache id
	 *
	 * @param   callback  $callback  Callback to cache
	 * @param   array     $args      Arguments to the callback method to cache
	 *
	 * @return  string  MD5 Hash : function cache id
	 *
	 * @since   11.1
	 */
	protected function _makeId($callback, $args)
	{
		if (is_array($callback) && is_object($callback[0]))
		{
			$vars = get_object_vars($callback[0]);
			$vars[] = strtolower(get_class($callback[0]));
			$callback[0] = $vars;
		}

		return md5(serialize(array($callback, $args)));
	}
}
