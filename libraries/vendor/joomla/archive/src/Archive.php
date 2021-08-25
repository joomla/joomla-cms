<?php
/**
 * Part of the Joomla Framework Archive Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Archive;

use Joomla\Archive\Exception\UnknownArchiveException;
use Joomla\Archive\Exception\UnsupportedArchiveException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/**
 * An Archive handling class
 *
 * @since  1.0
 */
class Archive
{
	/**
	 * The array of instantiated archive adapters.
	 *
	 * @var    ExtractableInterface[]
	 * @since  1.0
	 */
	protected $adapters = array();

	/**
	 * Holds the options array.
	 *
	 * @var    array|\ArrayAccess
	 * @since  1.0
	 */
	public $options = array();

	/**
	 * Create a new Archive object.
	 *
	 * @param   array|\ArrayAccess  $options  An array of options
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function __construct($options = array())
	{
		if (!\is_array($options) && !($options instanceof \ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		// Make sure we have a tmp directory.
		isset($options['tmp_path']) || $options['tmp_path'] = realpath(sys_get_temp_dir());

		$this->options = $options;
	}

	/**
	 * Extract an archive file to a directory.
	 *
	 * @param   string  $archivename  The name of the archive file
	 * @param   string  $extractdir   Directory to unpack into
	 *
	 * @return  boolean  True for success
	 *
	 * @since   1.0
	 * @throws  UnknownArchiveException if the archive type is not supported
	 */
	public function extract($archivename, $extractdir)
	{
		$ext      = pathinfo($archivename, \PATHINFO_EXTENSION);
		$path     = pathinfo($archivename, \PATHINFO_DIRNAME);
		$filename = pathinfo($archivename, \PATHINFO_FILENAME);

		switch (strtolower($ext))
		{
			case 'zip':
				$result = $this->getAdapter('zip')->extract($archivename, $extractdir);

				break;

			case 'tar':
				$result = $this->getAdapter('tar')->extract($archivename, $extractdir);

				break;

			case 'tgz':
			case 'gz':
			case 'gzip':
				// This may just be an individual file (e.g. sql script)
				$tmpfname = $this->options['tmp_path'] . '/' . uniqid('gzip');

				try
				{
					$this->getAdapter('gzip')->extract($archivename, $tmpfname);
				}
				catch (\RuntimeException $exception)
				{
					@unlink($tmpfname);

					return false;
				}

				if ($ext === 'tgz' || stripos($filename, '.tar') !== false)
				{
					$result = $this->getAdapter('tar')->extract($tmpfname, $extractdir);
				}
				else
				{
					Folder::create($extractdir);
					$result = File::copy($tmpfname, $extractdir . '/' . $filename, null, 0);
				}

				@unlink($tmpfname);

				break;

			case 'tbz2':
			case 'bz2':
			case 'bzip2':
				// This may just be an individual file (e.g. sql script)
				$tmpfname = $this->options['tmp_path'] . '/' . uniqid('bzip2');

				try
				{
					$this->getAdapter('bzip2')->extract($archivename, $tmpfname);
				}
				catch (\RuntimeException $exception)
				{
					@unlink($tmpfname);

					return false;
				}

				if ($ext === 'tbz2' || stripos($filename, '.tar') !== false)
				{
					$result = $this->getAdapter('tar')->extract($tmpfname, $extractdir);
				}
				else
				{
					Folder::create($extractdir);
					$result = File::copy($tmpfname, $extractdir . '/' . $filename, null, 0);
				}

				@unlink($tmpfname);

				break;

			default:
				throw new UnknownArchiveException(sprintf('Unknown archive type: %s', $ext));
		}

		return $result;
	}

	/**
	 * Method to override the provided adapter with your own implementation.
	 *
	 * @param   string   $type      Name of the adapter to set.
	 * @param   string   $class     FQCN of your class which implements ExtractableInterface.
	 * @param   boolean  $override  True to force override the adapter type.
	 *
	 * @return  Archive  This object for chaining.
	 *
	 * @since   1.0
	 * @throws  UnsupportedArchiveException if the adapter type is not supported
	 */
	public function setAdapter($type, $class, $override = true)
	{
		if ($override || !isset($this->adapters[$type]))
		{
			$error = !\is_object($class) && !class_exists($class)
					? 'Archive adapter "%s" (class "%s") not found.'
					: '';

			$error = $error == '' && !($class instanceof ExtractableInterface)
					? 'The provided adapter "%s" (class "%s") must implement Joomla\\Archive\\ExtractableInterface'
					: $error;

			$error = $error == '' && !$class::isSupported()
					? 'Archive adapter "%s" (class "%s") not supported.'
					: $error;

			if ($error != '')
			{
				throw new UnsupportedArchiveException(
					sprintf($error, $type, $class)
				);
			}

			$this->adapters[$type] = new $class($this->options);
		}

		return $this;
	}

	/**
	 * Get a file compression adapter.
	 *
	 * @param   string  $type  The type of adapter (bzip2|gzip|tar|zip).
	 *
	 * @return  ExtractableInterface  Adapter for the requested type
	 *
	 * @since   1.0
	 * @throws  UnsupportedArchiveException
	 */
	public function getAdapter($type)
	{
		$type = strtolower($type);

		if (!isset($this->adapters[$type]))
		{
			// Try to load the adapter object
			/** @var ExtractableInterface $class */
			$class = 'Joomla\\Archive\\' . ucfirst($type);

			if (!class_exists($class) || !$class::isSupported())
			{
				throw new UnsupportedArchiveException(
					sprintf(
						'Archive adapter "%s" (class "%s") not found or supported.',
						$type,
						$class
					)
				);
			}

			$this->adapters[$type] = new $class($this->options);
		}

		return $this->adapters[$type];
	}
}
