<?php
/**
 * @version $Id$
 * @package Joomla.Framework
 * @subpackage FileSystem
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * An Archive handling class
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage 	FileSystem
 * @since		1.5
 */
class JArchive {
	/**
	 * @param string The name of the archive including path and one of the following
	 *			   extenstions: .gz, .tar., .zip
	 * @param array of path to the files that must be added to the archive
	 * $return boolean for success
	 */
	function create($archive, $files )
	{
		jimport('pear.File.Archive');

		// $files is an array of path to the files that must be added to the archive

		$r = File_Archive::extract(
			$files,
			File_Archive::toArchive($archive, File_Archive::toOutput())
		);

		if (PEAR::isError($r)) {
			return false;
		}
		return true;
	}

	/**
	 * @param string The name of the archive file
	 * @param string Directory to unpack into
	 * $return boolean for success
	 */
	function extract( $archivename, $extractdir)
	{
		jimport( 'pear.File.Archive' );

		$r = File_Archive::extract( File_Archive::read($archivename.'/'), File_Archive::toFiles($extractdir) );

		if (PEAR::isError($r)) {
			return false;
		}
		return true;
	}
}
?>
