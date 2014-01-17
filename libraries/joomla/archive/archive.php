<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * An Archive handling class
 *
 * @package     Joomla.Platform
 * @subpackage  Archive
 * @since       11.1
 */
class JArchive
{
	/**
	 * @var    array  The array of instantiated archive adapters.
	 * @since  12.1
	 */
	protected static $adapters = array();

	/**
	 * Extract an archive file to a directory.
	 *
	 * @param   string  $archivename  The name of the archive file
	 * @param   string  $extractdir   Directory to unpack into
	 *
	 * @return  boolean  True for success
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public static function extract($archivename, $extractdir)
	{
		$untar = false;
		$result = false;
		$ext = JFile::getExt(strtolower($archivename));

		// Check if a tar is embedded...gzip/bzip2 can just be plain files!
		if (JFile::getExt(JFile::stripExt(strtolower($archivename))) == 'tar')
		{
			$untar = true;
		}

		switch ($ext)
		{
			case 'zip':
				$adapter = self::getAdapter('zip');

				if ($adapter)
				{
					$result = $adapter->extract($archivename, $extractdir);
				}
				break;

			case 'tar':
				$adapter = self::getAdapter('tar');

				if ($adapter)
				{
					$result = $adapter->extract($archivename, $extractdir);
				}
				break;

			case 'tgz':
				// This format is a tarball gzip'd
				$untar = true;

			case 'gz':
			case 'gzip':
				// This may just be an individual file (e.g. sql script)
				$adapter = self::getAdapter('gzip');

				if ($adapter)
				{
					$config = JFactory::getConfig();
					$tmpfname = $config->get('tmp_path') . '/' . uniqid('gzip');
					$gzresult = $adapter->extract($archivename, $tmpfname);

					if ($gzresult instanceof Exception)
					{
						@unlink($tmpfname);

						return false;
					}

					if ($untar)
					{
						// Try to untar the file
						$tadapter = self::getAdapter('tar');

						if ($tadapter)
						{
							$result = $tadapter->extract($tmpfname, $extractdir);
						}
					}
					else
					{
						$path = JPath::clean($extractdir);
						JFolder::create($path);
						$result = JFile::copy($tmpfname, $path . '/' . JFile::stripExt(basename(strtolower($archivename))), null, 1);
					}

					@unlink($tmpfname);
				}
				break;

			case 'tbz2':
				// This format is a tarball bzip2'd
				$untar = true;

			case 'bz2':
			case 'bzip2':
				// This may just be an individual file (e.g. sql script)
				$adapter = self::getAdapter('bzip2');

				if ($adapter)
				{
					$config = JFactory::getConfig();
					$tmpfname = $config->get('tmp_path') . '/' . uniqid('bzip2');
					$bzresult = $adapter->extract($archivename, $tmpfname);

					if ($bzresult instanceof Exception)
					{
						@unlink($tmpfname);

						return false;
					}

					if ($untar)
					{
						// Try to untar the file
						$tadapter = self::getAdapter('tar');

						if ($tadapter)
						{
							$result = $tadapter->extract($tmpfname, $extractdir);
						}
					}
					else
					{
						$path = JPath::clean($extractdir);
						JFolder::create($path);
						$result = JFile::copy($tmpfname, $path . '/' . JFile::stripExt(basename(strtolower($archivename))), null, 1);
					}

					@unlink($tmpfname);
				}
				break;

			default:
				throw new InvalidArgumentException('Unknown Archive Type');
		}

		if (!$result || $result instanceof Exception)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get a file compression adapter.
	 *
	 * @param   string  $type  The type of adapter (bzip2|gzip|tar|zip).
	 *
	 * @return  JArchiveExtractable  Adapter for the requested type
	 *
	 * @since   11.1
	 * @throws  UnexpectedValueException
	 */
	public static function getAdapter($type)
	{
		if (!isset(self::$adapters[$type]))
		{
			// Try to load the adapter object
			$class = 'JArchive' . ucfirst($type);

			if (!class_exists($class))
			{
				throw new UnexpectedValueException('Unable to load archive', 500);
			}

			self::$adapters[$type] = new $class;
		}

		return self::$adapters[$type];
	}
}
