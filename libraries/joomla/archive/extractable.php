<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Archive
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Archieve class interface
 *
 * @package     Joomla.Platform
 * @subpackage  Archive
 * @since       12.1
 */
interface JArchiveExtractable
{
	/**
	 * Extract a compressed file to a given path
	 *
	 * @param   string  $archive      Path to archive to extract
	 * @param   string  $destination  Path to extract archive to
	 * @param   array   $options      Extraction options [may be unused]
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   12.1
	 */
	public function extract($archive, $destination, array $options = array());

	/**
	 * Tests whether this adapter can unpack files on this computer.
	 *
	 * @return  boolean  True if supported
	 *
	 * @since   12.1
	 */
	public static function isSupported();
}
