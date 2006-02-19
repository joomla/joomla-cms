<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('cache.Lite');

/**
 * Abstract class for caching handlers
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.1
 */
class JCache extends Cache_Lite
{
	var $_defaultGroup = 'JCache';
	var $_validateCache = false;

	/**
	* Constructor
	*
	* $options is an assoc. To have a look at availables options,
	* see the constructor of the Cache_Lite class in 'Cache_Lite.php'
	*
	* Comparing to Cache_Lite constructor, there is another option :
	* $options = array(
	*	 (...) see Cache_Lite constructor
	*	 'defaultGroup' => default cache group for function caching (string)
	* );
	*
	* @param array $options options
	* @access public
	*/
	function JCache($options = array (NULL))
	{
		$this->_construct($options);
	}

	/**
	 * Constructor
	 *
	 * @param array $options options
	 * @access protected
	 */
	function _construct($options)
	{
		global $mainframe;

		/*
		 * Set default group
		 */
		if (isset ($options['defaultGroup']))
		{
			$this->_defaultGroup = $options['defaultGroup'];
		}

		/*
		 * Build the cache directory if it exists
		 */
		$baseDir = $mainframe->getCfg('cachepath');
		if (!empty($baseDir))
		{
			$this->_cacheDir = JPath :: clean($baseDir);
		}
		
		/*
		 * Add the application specific subdirectory for cache paths
		 */
		$this->_cacheDir .= ($mainframe->getClient()) ? 'administrator'.DS : 'site'.DS;

		/*
		 * Create cache directory if not present
		 */
		if (!JFolder::exists($this->_cacheDir))
		{
			JFolder::create($this->_cacheDir);
		}
		
		$this->Cache_Lite($options);
	}

	/**
	 * Returns a reference to the global Cache object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param handler $handler The cache handler to instantiate
	 * @param array $options options
	 * @return database A database object
	 * @since 1.1
	 */
	function & getInstance($handler = 'Function', $options)
	{
		static $instances;

		if (!isset ($instances))
		{
			$instances = array ();
		}

		$signature = serialize(array ($options));

		if (empty ($instances[$signature]))
		{
			jimport('joomla.cache.adapters.'.$handler);
			$adapter = 'JCache'.$handler;
			$instances[$signature] = new $adapter ($options);
		}

		return $instances[$signature];
	}

	/**
	 * Enable/disbale caching, if caching is enabled
	 *
	 * @param boolean $enable If true enable caching.
	 * @access public
	 */
	function setCaching($enable)
	{
		if ($this->_caching)
		{
			$this->_caching = $enable;
		}
		return $this->_caching;
	}

	/**
	 * Enable/disbale cache validation
	 *
	 * @param boolean $validateCache If true enable cache validation.
	 * @access public
	 */
	function setCacheValidation($validateCache)
	{
		$this->_validateCache = $validateCache;
	}

	/**
	 * Make a control key with the string containing datas
	 *
	 * @param string $data data
	 * @param string $controlType type of control 'md5', 'crc32' or 'strlen'
	 * @return string control key
	 * @access public
	 */
	function generateId($data, $controlType = 'md5')
	{
		return $this->_hash($data, $controlType);
	}

	/**
	 * Cleans the cache
	 */
	function cleanCache($group = false, $mode = 'ingroup')
	{
		global $mainframe;

		if ($mainframe->getCfg('caching'))
		{
			$cache = & JCache :: getCache($group);
			$cache->clean($group, $mode);

			/*
			 * Build the cache directory
			 */
			$baseDir = $mainframe->getCfg('cachepath');
			$baseDir .= ($mainframe->getClient()) ? DS.'administrator'.DS : DS.'site'.DS;
			$path = JPath :: clean($baseDir);
			$files = JFolder :: files($path, '.xml');
			foreach ($files as $file)
			{
				$file = $path.$file;
				unlink($file);
			}
		}
	}

	/**
	 * Deprecated, use JFactory createCache instead
	 * @since 1.1
	 */
	function & getCache($group = '')
	{
		return JFactory :: getCache($group);
	}
}
?>