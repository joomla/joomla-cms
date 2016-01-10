<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JArchive
 *
 * @package     Joomla.Platform
 * @subpackage  Archive
 * @since       3.4
 */
class JArchiveWrapperArchive
{
	/**
	 * Helper wrapper method for extract
	 *
	 * @param   string  $archivename  The name of the archive file
	 * @param   string  $extractdir   Directory to unpack into
	 *
	 * @return  boolean  True for success
	 *
	 * @see     JArchive::extract()
	 * @since   3.4
	 * @throws InvalidArgumentException
	 */
	public function extract($archivename, $extractdir)
	{
		return JArchive::extract($archivename, $extractdir);
	}

	/**
	 * Helper wrapper method for getAdapter
	 *
	 * @param   string  $type  The type of adapter (bzip2|gzip|tar|zip).
	 *
	 * @return  JArchiveExtractable  Adapter for the requested type
	 *
	 * @see     JUserHelper::getAdapter()
	 * @since   3.4
	 */
	public function getAdapter($type)
	{
		return JArchive::getAdapter($type);
	}
}
