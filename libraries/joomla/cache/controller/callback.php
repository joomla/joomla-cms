<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * @param   callable  $callback    Callback or string shorthand for a callback
	 * @param   array     $args        Callback arguments
	 * @param   mixed     $id          Cache ID
	 * @param   boolean   $wrkarounds  True to use wrkarounds
	 * @param   array     $woptions    Workaround options
	 *
	 * @return  mixed  Result of the callback
	 *
	 * @since   11.1
	 */
	public function get($callback, $args = array(), $id = false, $wrkarounds = false, $woptions = array())
	{
		if (!$id)
		{
			// Generate an ID
			$id = $this->_makeId($callback, $args);
		}

		$data = $this->cache->get($id);

		$locktest             = new stdClass;
		$locktest->locked     = null;
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
			$cached                = unserialize(trim($data));
			$coptions['mergehead'] = isset($woptions['mergehead']) ? $woptions['mergehead'] : 0;
			$output                = ($wrkarounds == false) ? $cached['output'] : JCache::getWorkarounds($cached['output'], $coptions);
			$result                = $cached['result'];

			if ($locktest->locked == true)
			{
				$this->cache->unlock($id);
			}
		}
		else
		{
			if (!is_array($args))
			{
				$referenceArgs = !empty($args) ? array(&$args) : array();
			}
			else
			{
				$referenceArgs = &$args;
			}

			if ($locktest->locked == false)
			{
				$locktest = $this->cache->lock($id);
			}

			if (isset($woptions['modulemode']) && $woptions['modulemode'] == 1)
			{
				$document = JFactory::getDocument();
				$coptions['modulemode'] = 1;
				if (method_exists($document, 'getHeadData'))
				{
					$coptions['headerbefore'] = $document->getHeadData();
				}
			}
			else
			{
				$coptions['modulemode'] = 0;
			}

			ob_start();
			ob_implicit_flush(false);

			$result = call_user_func_array($callback, $referenceArgs);
			$output = ob_get_clean();

			$coptions['nopathway'] = isset($woptions['nopathway']) ? $woptions['nopathway'] : 1;
			$coptions['nohead']    = isset($woptions['nohead']) ? $woptions['nohead'] : 1;
			$coptions['nomodules'] = isset($woptions['nomodules']) ? $woptions['nomodules'] : 1;

			$cached = array(
				'output' => ($wrkarounds == false) ? $output : JCache::setWorkarounds($output, $coptions),
				'result' => $result,
			);

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

		return md5(serialize(array($callback, $args)));
	}
}
