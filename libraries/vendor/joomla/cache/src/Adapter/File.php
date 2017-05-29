<?php
/**
 * Part of the Joomla Framework Cache Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cache\Adapter;

use Joomla\Cache\AbstractCacheItemPool;
use Joomla\Cache\Exception\InvalidArgumentException;
use Joomla\Cache\Exception\RuntimeException;
use Joomla\Cache\Item\HasExpirationDateInterface;
use Joomla\Cache\Item\Item;
use Psr\Cache\CacheItemInterface;

/**
 * Filesystem cache driver for the Joomla Framework.
 *
 * Supported options:
 * - file.locking (boolean) :
 * - file.path              : The path for cache files.
 *
 * @since  1.0
 */
class File extends AbstractCacheItemPool
{
	/**
	 * Constructor.
	 *
	 * @param   mixed  $options  An options array, or an object that implements \ArrayAccess
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function __construct($options = [])
	{
		if (!isset($options['file.locking']))
		{
			$options['file.locking'] = true;
		}

		if (!isset($options['file.path']))
		{
			throw new InvalidArgumentException('The file.path option must be set.');
		}

		$this->checkFilePath($options['file.path']);

		parent::__construct($options);
	}

	/**
	 * This will wipe out the entire cache's keys
	 *
	 * @return  boolean  True if the pool was successfully cleared. False if there was an error.
	 *
	 * @since   1.0
	 */
	public function clear()
	{
		$filePath = $this->options['file.path'];
		$this->checkFilePath($filePath);

		$iterator = new \RegexIterator(
			new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($filePath)
			),
			'/\.data$/i'
		);

		/* @var  \RecursiveDirectoryIterator  $file */
		foreach ($iterator as $file)
		{
			if ($file->isFile())
			{
				@unlink($file->getRealPath());
			}
		}

		return true;
	}

	/**
	 * Returns a Cache Item representing the specified key.
	 *
	 * @param   string  $key  The key for which to return the corresponding Cache Item.
	 *
	 * @return  CacheItemInterface  The corresponding Cache Item.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  RuntimeException
	 */
	public function getItem($key)
	{
		if (!$this->hasItem($key))
		{
			return new Item($key);
		}

		$resource = @fopen($this->fetchStreamUri($key), 'rb');

		if (!$resource)
		{
			throw new RuntimeException(sprintf('Unable to fetch cache entry for %s.  Connot open the resource.', $key));
		}

		// If locking is enabled get a shared lock for reading on the resource.
		if ($this->options['file.locking'] && !flock($resource, LOCK_SH))
		{
			throw new RuntimeException(sprintf('Unable to fetch cache entry for %s.  Connot obtain a lock.', $key));
		}

		$data = stream_get_contents($resource);

		// If locking is enabled release the lock on the resource.
		if ($this->options['file.locking'] && !flock($resource, LOCK_UN))
		{
			throw new RuntimeException(sprintf('Unable to fetch cache entry for %s.  Connot release the lock.', $key));
		}

		fclose($resource);

		$item = new Item($key);
		$information = unserialize($data);

		// If the cached data has expired remove it and return.
		if ($information[1] !== null && time() > $information[1])
		{
			if (!$this->deleteItem($key))
			{
				throw new RuntimeException(sprintf('Unable to clean expired cache entry for %s.', $key), null);
			}

			return $item;
		}

		$item->set($information[0]);

		return $item;
	}

	/**
	 * Removes the item from the pool.
	 *
	 * @param   string  $key  The key to delete.
	 *
	 * @return  boolean  True if the item was successfully removed. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function deleteItem($key)
	{
		if ($this->hasItem($key))
		{
			return (bool) @unlink($this->fetchStreamUri($key));
		}

		// If the item doesn't exist, no error
		return true;
	}

	/**
	 * Persists a cache item immediately.
	 *
	 * @param   CacheItemInterface  $item  The cache item to save.
	 *
	 * @return  boolean  True if the item was successfully persisted. False if there was an error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save(CacheItemInterface $item)
	{
		$fileName = $this->fetchStreamUri($item->getKey());
		$filePath = pathinfo($fileName, PATHINFO_DIRNAME);

		if (!is_dir($filePath))
		{
			mkdir($filePath, 0770, true);
		}

		if ($item instanceof HasExpirationDateInterface)
		{
			$contents = serialize(array($item->get(), time() + $this->convertItemExpiryToSeconds($item)));
		}
		else
		{
			$contents = serialize(array($item->get(), null));
		}

		$success = (bool) file_put_contents(
			$fileName,
			$contents,
			($this->options['file.locking'] ? LOCK_EX : null)
		);

		return $success;
	}

	/**
	 * Confirms if the cache contains specified cache item.
	 *
	 * @param   string  $key  The key for which to check existence.
	 *
	 * @return  boolean  True if item exists in the cache, false otherwise.
	 *
	 * @since   1.0
	 */
	public function hasItem($key)
	{
		return is_file($this->fetchStreamUri($key));
	}

	/**
	 * Test to see if the CacheItemPoolInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Check that the file path is a directory and writable.
	 *
	 * @param   string  $filePath  A file path.
	 *
	 * @return  boolean  The method will always return true, if it returns.
	 *
	 * @since   1.0
	 * @throws  RuntimeException if the file path is invalid.
	 */
	private function checkFilePath($filePath)
	{
		if (!is_dir($filePath))
		{
			throw new RuntimeException(sprintf('The base cache path `%s` does not exist.', $filePath));
		}
		elseif (!is_writable($filePath))
		{
			throw new RuntimeException(sprintf('The base cache path `%s` is not writable.', $filePath));
		}

		return true;
	}

	/**
	 * Get the full stream URI for the cache entry.
	 *
	 * @param   string  $key  The storage entry identifier.
	 *
	 * @return  string  The full stream URI for the cache entry.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException if the cache path is invalid.
	 */
	private function fetchStreamUri($key)
	{
		$filePath = $this->options['file.path'];
		$this->checkFilePath($filePath);

		return sprintf(
			'%s/~%s/%s.data',
			$filePath,
			substr(hash('md5', $key), 0, 4),
			hash('sha1', $key)
		);
	}
}
