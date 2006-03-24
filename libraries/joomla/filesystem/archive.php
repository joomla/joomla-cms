<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

if (!defined('DS')) {
	/** string Shortcut for the DIRECTORY_SEPERATOR define */
	define('DS', DIRECTORY_SEPERATOR);
}

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
	 * @param string The name of the archive
	 * @param mixed The name of a single file or an array of files
	 * @param string The compression for the archive
	 * @param string Path to add within the archive
	 * @param string Path to remove within the archive
	 * @param boolean Automatically append the extension for the archive
	 * @param boolean Remove for source files
	 */
	function create($archive, $files, $compress = 'tar', $addPath = '', $removePath = '', $autoExt = false, $cleanUp = false) {

		jimport('archive.Tar');

		if (is_string($files)) {
			$files = array ($files);
		}
		if ($autoExt) {
			$archive .= '.'.$compress;
		}

		$tar = new Archive_Tar($archive, $compress);
		$tar->setErrorHandling(PEAR_ERROR_PRINT);
		$tar->createModify($files, $addPath, $removePath);

		if ($cleanUp) {
			JFile::delete($files);
		}
		return $tar;
	}
}
?>