<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Cache;

defined('JPATH_PLATFORM') or die;

use Joomla\Application\Web\WebClient;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\String\StringHelper;

/**
 * Joomla! Cache base object
 *
 * @since  11.1
 */
class Cache
{
	/**
	 * Storage handler
	 *
	 * @var    CacheStorage[]
	 * @since  11.1
	 */
	public static $_handler = array();

	/**
	 * Cache options
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_options;

	/**
	 * Constructor
	 *
	 * @param   array  $options  Cache options
	 *
	 * @since   11.1
	 */
	public function __construct($options)
	{
		$conf = \JFactory::getConfig();

		$this->_options = array(
			'cachebase'    => $conf->get('cache_path', JPATH_CACHE),
			'lifetime'     => (int) $conf->get('cachetime'),
			'language'     => $conf->get('language', 'en-GB'),
			'storage'      => $conf->get('cache_handler', ''),
			'defaultgroup' => 'default',
			'locking'      => true,
			'locktime'     => 15,
			'checkTime'    => true,
			'caching'      => ($conf->get('caching') >= 1) ? true : false,
		);

		// Overwrite default options with given options
		foreach ($options as $option => $value)
		{
			if (isset($options[$option]) && $options[$option] !== '')
			{
				$this->_options[$option] = $options[$option];
			}
		}

		if (empty($this->_options['storage']))
		{
			$this->setCaching(false);
		}
	}

	/**
	 * Returns a reference to a cache adapter object, always creating it
	 *
	 * @param   string  $type     The cache object type to instantiate
	 * @param   array   $options  The array of options
	 *
	 * @return  CacheController
	 *
	 * @since   11.1
	 */
	public static function getInstance($type = 'output', $options = array())
	{
		return CacheController::getInstance($type, $options);
	}

	/**
	 * Get the storage handlers
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public static function getStores()
	{
		$handlers = array();

		// Get an iterator and loop trough the driver classes.
		$iterator = new \DirectoryIterator(__DIR__ . '/Storage');

		/** @type  $file  \DirectoryIterator */
		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();

			// Only load for php files.
			if (!$file->isFile() || $file->getExtension() != 'php' || $fileName == 'CacheStorageHelper.php')
			{
				continue;
			}

			// Derive the class name from the type.
			$class = str_ireplace('.php', '', __NAMESPACE__ . '\\Storage\\' . ucfirst(trim($fileName)));

			// If the class doesn't exist we have nothing left to do but look at the next type. We did our best.
			if (!class_exists($class))
			{
				continue;
			}

			// Sweet!  Our class exists, so now we just need to know if it passes its test method.
			if ($class::isSupported())
			{
				// Connector names should not have file extensions.
				$handler = str_ireplace('Storage.php', '', $fileName);
				$handler = str_ireplace('.php', '', $handler);
				$handlers[] = strtolower($handler);
			}
		}

		return $handlers;
	}

	/**
	 * Set caching enabled state
	 *
	 * @param   boolean  $enabled  True to enable caching
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setCaching($enabled)
	{
		$this->_options['caching'] = $enabled;
	}

	/**
	 * Get caching state
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function getCaching()
	{
		return $this->_options['caching'];
	}

	/**
	 * Set cache lifetime
	 *
	 * @param   integer  $lt  Cache lifetime
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function setLifeTime($lt)
	{
		$this->_options['lifetime'] = $lt;
	}

	/**
	 * Check if the cache contains data stored by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public function contains($id, $group = null)
	{
		if (!$this->getCaching())
		{
			return false;
		}

		// Get the default group
		$group = $group ?: $this->_options['defaultgroup'];

		return $this->_getStorage()->contains($id, $group);
	}

	/**
	 * Get cached data by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  mixed  Boolean false on failure or a cached data object
	 *
	 * @since   11.1
	 */
	public function get($id, $group = null)
	{
		if (!$this->getCaching())
		{
			return false;
		}

		// Get the default group
		$group = $group ?: $this->_options['defaultgroup'];

		return $this->_getStorage()->get($id, $group, $this->_options['checkTime']);
	}

	/**
	 * Get a list of all cached data
	 *
	 * @return  mixed  Boolean false on failure or an object with a list of cache groups and data
	 *
	 * @since   11.1
	 */
	public function getAll()
	{
		if (!$this->getCaching())
		{
			return false;
		}

		return $this->_getStorage()->getAll();
	}

	/**
	 * Store the cached data by ID and group
	 *
	 * @param   mixed   $data   The data to store
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function store($data, $id, $group = null)
	{
		if (!$this->getCaching())
		{
			return false;
		}

		// Get the default group
		$group = $group ?: $this->_options['defaultgroup'];

		// Get the storage and store the cached data
		return $this->_getStorage()->store($id, $group, $data);
	}

	/**
	 * Remove a cached data entry by ID and group
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function remove($id, $group = null)
	{
		// Get the default group
		$group = $group ?: $this->_options['defaultgroup'];

		try
		{
			return $this->_getStorage()->remove($id, $group);
		}
		catch (CacheExceptionInterface $e)
		{
			if (!$this->getCaching())
			{
				return false;
			}

			throw $e;
		}
	}

	/**
	 * Clean cache for a group given a mode.
	 *
	 * group mode    : cleans all cache in the group
	 * notgroup mode : cleans all cache not in the group
	 *
	 * @param   string  $group  The cache data group
	 * @param   string  $mode   The mode for cleaning cache [group|notgroup]
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   11.1
	 */
	public function clean($group = null, $mode = 'group')
	{
		// Get the default group
		$group = $group ?: $this->_options['defaultgroup'];

		try
		{
			return $this->_getStorage()->clean($group, $mode);
		}
		catch (CacheExceptionInterface $e)
		{
			if (!$this->getCaching())
			{
				return false;
			}

			throw $e;
		}
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function gc()
	{
		try
		{
			return $this->_getStorage()->gc();
		}
		catch (CacheExceptionInterface $e)
		{
			if (!$this->getCaching())
			{
				return false;
			}

			throw $e;
		}
	}

	/**
	 * Set lock flag on cached item
	 *
	 * @param   string  $id        The cache data ID
	 * @param   string  $group     The cache data group
	 * @param   string  $locktime  The default locktime for locking the cache.
	 *
	 * @return  \stdClass  Object with properties of lock and locklooped
	 *
	 * @since   11.1
	 */
	public function lock($id, $group = null, $locktime = null)
	{
		$returning = new \stdClass;
		$returning->locklooped = false;

		if (!$this->getCaching())
		{
			$returning->locked = false;

			return $returning;
		}

		// Get the default group
		$group = $group ?: $this->_options['defaultgroup'];

		// Get the default locktime
		$locktime = $locktime ?: $this->_options['locktime'];

		/*
		 * Allow storage handlers to perform locking on their own
		 * NOTE drivers with lock need also unlock or unlocking will fail because of false $id
		 */
		$handler = $this->_getStorage();

		if ($this->_options['locking'] == true)
		{
			$locked = $handler->lock($id, $group, $locktime);

			if ($locked !== false)
			{
				return $locked;
			}
		}

		// Fallback
		$curentlifetime = $this->_options['lifetime'];

		// Set lifetime to locktime for storing in children
		$this->_options['lifetime'] = $locktime;

		$looptime = $locktime * 10;
		$id2      = $id . '_lock';

		if ($this->_options['locking'] == true)
		{
			$data_lock = $handler->get($id2, $group, $this->_options['checkTime']);
		}
		else
		{
			$data_lock         = false;
			$returning->locked = false;
		}

		if ($data_lock !== false)
		{
			$lock_counter = 0;

			// Loop until you find that the lock has been released. That implies that data get from other thread has finished
			while ($data_lock !== false)
			{
				if ($lock_counter > $looptime)
				{
					$returning->locked = false;
					$returning->locklooped = true;
					break;
				}

				usleep(100);
				$data_lock = $handler->get($id2, $group, $this->_options['checkTime']);
				$lock_counter++;
			}
		}

		if ($this->_options['locking'] == true)
		{
			$returning->locked = $handler->store($id2, $group, 1);
		}

		// Revert lifetime to previous one
		$this->_options['lifetime'] = $curentlifetime;

		return $returning;
	}

	/**
	 * Unset lock flag on cached item
	 *
	 * @param   string  $id     The cache data ID
	 * @param   string  $group  The cache data group
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function unlock($id, $group = null)
	{
		if (!$this->getCaching())
		{
			return false;
		}

		// Get the default group
		$group = $group ?: $this->_options['defaultgroup'];

		// Allow handlers to perform unlocking on their own
		$handler = $this->_getStorage();

		$unlocked = $handler->unlock($id, $group);

		if ($unlocked !== false)
		{
			return $unlocked;
		}

		// Fallback
		return $handler->remove($id . '_lock', $group);
	}

	/**
	 * Get the cache storage handler
	 *
	 * @return  CacheStorage
	 *
	 * @since   11.1
	 */
	public function &_getStorage()
	{
		$hash = md5(serialize($this->_options));

		if (isset(self::$_handler[$hash]))
		{
			return self::$_handler[$hash];
		}

		self::$_handler[$hash] = CacheStorage::getInstance($this->_options['storage'], $this->_options);

		return self::$_handler[$hash];
	}

	/**
	 * Perform workarounds on retrieved cached data
	 *
	 * @param   string  $data     Cached data
	 * @param   array   $options  Array of options
	 *
	 * @return  string  Body of cached data
	 *
	 * @since   11.1
	 */
	public static function getWorkarounds($data, $options = array())
	{
		$app      = \JFactory::getApplication();
		$document = \JFactory::getDocument();
		$body     = null;

		// Get the document head out of the cache.
		if (isset($options['mergehead']) && $options['mergehead'] == 1 && isset($data['head']) && !empty($data['head'])
			&& method_exists($document, 'mergeHeadData'))
		{
			$document->mergeHeadData($data['head']);
		}
		elseif (isset($data['head']) && method_exists($document, 'setHeadData'))
		{
			$document->setHeadData($data['head']);
		}

		// Get the document MIME encoding out of the cache
		if (isset($data['mime_encoding']))
		{
			$document->setMimeEncoding($data['mime_encoding'], true);
		}

		// If the pathway buffer is set in the cache data, get it.
		if (isset($data['pathway']) && is_array($data['pathway']))
		{
			// Push the pathway data into the pathway object.
			$app->getPathway()->setPathway($data['pathway']);
		}

		// @todo check if the following is needed, seems like it should be in page cache
		// If a module buffer is set in the cache data, get it.
		if (isset($data['module']) && is_array($data['module']))
		{
			// Iterate through the module positions and push them into the document buffer.
			foreach ($data['module'] as $name => $contents)
			{
				$document->setBuffer($contents, 'module', $name);
			}
		}

		// Set cached headers.
		if (isset($data['headers']) && $data['headers'])
		{
			foreach ($data['headers'] as $header)
			{
				$app->setHeader($header['name'], $header['value']);
			}
		}

		// The following code searches for a token in the cached page and replaces it with the proper token.
		if (isset($data['body']))
		{
			$token       = \JSession::getFormToken();
			$search      = '#<input type="hidden" name="[0-9a-f]{32}" value="1" />#';
			$replacement = '<input type="hidden" name="' . $token . '" value="1" />';

			$data['body'] = preg_replace($search, $replacement, $data['body']);
			$body         = $data['body'];
		}

		// Get the document body out of the cache.
		return $body;
	}

	/**
	 * Create workarounds for data to be cached
	 *
	 * @param   string  $data     Cached data
	 * @param   array   $options  Array of options
	 *
	 * @return  string  Data to be cached
	 *
	 * @since   11.1
	 */
	public static function setWorkarounds($data, $options = array())
	{
		$loptions = array(
			'nopathway'  => 0,
			'nohead'     => 0,
			'nomodules'  => 0,
			'modulemode' => 0,
		);

		if (isset($options['nopathway']))
		{
			$loptions['nopathway'] = $options['nopathway'];
		}

		if (isset($options['nohead']))
		{
			$loptions['nohead'] = $options['nohead'];
		}

		if (isset($options['nomodules']))
		{
			$loptions['nomodules'] = $options['nomodules'];
		}

		if (isset($options['modulemode']))
		{
			$loptions['modulemode'] = $options['modulemode'];
		}

		$app      = \JFactory::getApplication();
		$document = \JFactory::getDocument();

		if ($loptions['nomodules'] != 1)
		{
			// Get the modules buffer before component execution.
			$buffer1 = $document->getBuffer();

			if (!is_array($buffer1))
			{
				$buffer1 = array();
			}

			// Make sure the module buffer is an array.
			if (!isset($buffer1['module']) || !is_array($buffer1['module']))
			{
				$buffer1['module'] = array();
			}
		}

		// View body data
		$cached['body'] = $data;

		// Document head data
		if ($loptions['nohead'] != 1 && method_exists($document, 'getHeadData'))
		{
			if ($loptions['modulemode'] == 1)
			{
				$headnow = $document->getHeadData();
				$unset   = array('title', 'description', 'link', 'links', 'metaTags');

				foreach ($unset as $un)
				{
					unset($headnow[$un]);
					unset($options['headerbefore'][$un]);
				}

				$cached['head'] = array();

				// Only store what this module has added
				foreach ($headnow as $now => $value)
				{
					if (isset($options['headerbefore'][$now]))
					{
						// We have to serialize the content of the arrays because the may contain other arrays which is a notice in PHP 5.4 and newer
						$nowvalue    = array_map('serialize', $headnow[$now]);
						$beforevalue = array_map('serialize', $options['headerbefore'][$now]);

						$newvalue = array_diff_assoc($nowvalue, $beforevalue);
						$newvalue = array_map('unserialize', $newvalue);

						// Special treatment for script and style declarations.
						if (($now == 'script' || $now == 'style') && is_array($newvalue) && is_array($options['headerbefore'][$now]))
						{
							foreach ($newvalue as $type => $currentScriptStr)
							{
								if (isset($options['headerbefore'][$now][strtolower($type)]))
								{
									$oldScriptStr = $options['headerbefore'][$now][strtolower($type)];

									if ($oldScriptStr != $currentScriptStr)
									{
										// Save only the appended declaration.
										$newvalue[strtolower($type)] = StringHelper::substr($currentScriptStr, StringHelper::strlen($oldScriptStr));
									}
								}
							}
						}
					}
					else
					{
						$newvalue = $headnow[$now];
					}

					if (!empty($newvalue))
					{
						$cached['head'][$now] = $newvalue;
					}
				}
			}
			else
			{
				$cached['head'] = $document->getHeadData();
			}
		}

		// Document MIME encoding
		$cached['mime_encoding'] = $document->getMimeEncoding();

		// Pathway data
		if ($app->isClient('site') && $loptions['nopathway'] != 1)
		{
			$cached['pathway'] = is_array($data) && isset($data['pathway']) ? $data['pathway'] : $app->getPathway()->getPathway();
		}

		if ($loptions['nomodules'] != 1)
		{
			// @todo Check if the following is needed, seems like it should be in page cache
			// Get the module buffer after component execution.
			$buffer2 = $document->getBuffer();

			if (!is_array($buffer2))
			{
				$buffer2 = array();
			}

			// Make sure the module buffer is an array.
			if (!isset($buffer2['module']) || !is_array($buffer2['module']))
			{
				$buffer2['module'] = array();
			}

			// Compare the second module buffer against the first buffer.
			$cached['module'] = array_diff_assoc($buffer2['module'], $buffer1['module']);
		}

		// Headers data
		if (isset($options['headers']) && $options['headers'])
		{
			$cached['headers'] = $app->getHeaders();
		}

		return $cached;
	}

	/**
	 * Create a safe ID for cached data from URL parameters
	 *
	 * @return  string  MD5 encoded cache ID
	 *
	 * @since   11.1
	 */
	public static function makeId()
	{
		$app = \JFactory::getApplication();

		$registeredurlparams = new \stdClass;

		// Get url parameters set by plugins
		if (!empty($app->registeredurlparams))
		{
			$registeredurlparams = $app->registeredurlparams;
		}

		// Platform defaults
		$defaulturlparams = array(
			'format' => 'WORD',
			'option' => 'WORD',
			'view'   => 'WORD',
			'layout' => 'WORD',
			'tpl'    => 'CMD',
			'id'     => 'INT',
		);

		// Use platform defaults if parameter doesn't already exist.
		foreach ($defaulturlparams as $param => $type)
		{
			if (!property_exists($registeredurlparams, $param))
			{
				$registeredurlparams->$param = $type;
			}
		}

		$safeuriaddon = new \stdClass;

		foreach ($registeredurlparams as $key => $value)
		{
			$safeuriaddon->$key = $app->input->get($key, null, $value);
		}

		return md5(serialize($safeuriaddon));
	}

	/**
	 * Set a prefix cache key if device calls for separate caching
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	public static function getPlatformPrefix()
	{
		// No prefix when Global Config is set to no platfom specific prefix
		if (!\JFactory::getConfig()->get('cache_platformprefix', '0'))
		{
			return '';
		}

		$webclient = new WebClient;

		if ($webclient->mobile)
		{
			return 'M-';
		}

		return '';
	}

	/**
	 * Add a directory where Cache should search for handlers. You may either pass a string or an array of directories.
	 *
	 * @param   array|string  $path  A path to search.
	 *
	 * @return  array   An array with directory elements
	 *
	 * @since   11.1
	 */
	public static function addIncludePath($path = '')
	{
		static $paths;

		if (!isset($paths))
		{
			$paths = array();
		}

		if (!empty($path) && !in_array($path, $paths))
		{
			\JLoader::import('joomla.filesystem.path');
			array_unshift($paths, \JPath::clean($path));
		}

		return $paths;
	}
}
