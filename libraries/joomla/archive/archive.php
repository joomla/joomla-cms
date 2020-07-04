<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

use Joomla\Archive\Archive;

/**
 * An Archive handling class
 *
 * @since       1.5
 * @deprecated  4.0 use the Joomla\Archive\Archive class instead
 */
class JArchive
{
	/**
	 * The array of instantiated archive adapters.
	 *
	 * @var    JArchiveExtractable[]
	 * @since  3.0.0
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
	 * @since   1.5
	 * @throws  InvalidArgumentException
	 * @deprecated  4.0 use the Joomla\Archive\Archive class instead
	 */
	public static function extract($archivename, $extractdir)
	{
		// The archive instance
		$archive = new Archive(array('tmp_path' => JFactory::getConfig()->get('tmp_path')));

		// Extract the archive
		return $archive->extract($archivename, $extractdir);
	}

	/**
	 * Get a file compression adapter.
	 *
	 * @param   string  $type  The type of adapter (bzip2|gzip|tar|zip).
	 *
	 * @return  JArchiveExtractable  Adapter for the requested type
	 *
	 * @since   1.5
	 * @throws  UnexpectedValueException
	 * @deprecated  4.0 use the Joomla\Archive\Archive class instead
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
