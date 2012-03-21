<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

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
	 * Extract an archive file to a directory.
	 *
	 * @param   string  $archivename  The name of the archive file
	 * @param   string  $extractdir   Directory to unpack into
	 *
	 * @return  boolean  True for success
	 *
	 * @since   11.1
	 */
	public static function extract($archivename, $extractdir)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

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
						$result = JFile::copy($tmpfname, $path . '/' . JFile::stripExt(JFile::getName(strtolower($archivename))), null, 1);
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
						$result = JFile::copy($tmpfname, $path . '/' . JFile::stripExt(JFile::getName(strtolower($archivename))), null, 1);
					}

					@unlink($tmpfname);
				}
				break;

			default:
				JLog::add(JText::_('JLIB_FILESYSTEM_UNKNOWNARCHIVETYPE'), JLog::WARNING, 'jerror');
				return false;
				break;
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
	 * @return  object  JArchiveExtractable
	 *
	 * @since   11.1
	 * @throws  Exception
	 */
	public static function getAdapter($type)
	{
		static $adapters;

		if (!isset($adapters))
		{
			$adapters = array();
		}

		if (!isset($adapters[$type]))
		{
			// Try to load the adapter object
			$class = 'JArchive' . ucfirst($type);

			if (!class_exists($class))
			{
				throw new Exception(JText::_('JLIB_FILESYSTEM_UNABLE_TO_LOAD_ARCHIVE'), 500);
			}

			$adapters[$type] = new $class;
		}

		return $adapters[$type];
	}
}
