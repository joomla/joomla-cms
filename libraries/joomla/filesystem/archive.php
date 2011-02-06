<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 */

defined('JPATH_PLATFORM') or die;

/**
 * An Archive handling class
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @since		1.5
 */
class JArchive
{
	/**
	 * @param	string	$archivename	The name of the archive file
	 * @param	string	$extractdir		Directory to unpack into
	 *
	 * @return	boolean	True for success
	 * @since	1.5
	 */
	public static function extract($archivename, $extractdir)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$untar = false;
		$result = false;
		$ext = JFile::getExt(strtolower($archivename));

		// check if a tar is embedded...gzip/bzip2 can just be plain files!
		if (JFile::getExt(JFile::stripExt(strtolower($archivename))) == 'tar') {
			$untar = true;
		}

		switch ($ext)
		{
			case 'zip':
				$adapter = JArchive::getAdapter('zip');

				if ($adapter) {
					$result = $adapter->extract($archivename, $extractdir);
				}
				break;

			case 'tar':
				$adapter = JArchive::getAdapter('tar');

				if ($adapter) {
					$result = $adapter->extract($archivename, $extractdir);
				}
				break;

			case 'tgz':
				$untar = true;	// This format is a tarball gzip'd

			case 'gz':	// This may just be an individual file (e.g. sql script)
			case 'gzip':
				$adapter = JArchive::getAdapter('gzip');

				if ($adapter) {
					$config		= JFactory::getConfig();
					$tmpfname	= $config->get('tmp_path').DS.uniqid('gzip');
					$gzresult	= $adapter->extract($archivename, $tmpfname);

					if (JError::isError($gzresult)) {
						@unlink($tmpfname);

						return false;
					}

					if ($untar) {
						// Try to untar the file
						$tadapter = JArchive::getAdapter('tar');

						if ($tadapter) {
							$result = $tadapter->extract($tmpfname, $extractdir);
						}
					}
					else {
						$path = JPath::clean($extractdir);
						JFolder::create($path);
						$result = JFile::copy($tmpfname,$path.DS.JFile::stripExt(JFile::getName(strtolower($archivename))),null,1);
					}

					@unlink($tmpfname);
				}
				break;

			case 'tbz2' :
				$untar = true; // This format is a tarball bzip2'd

			case 'bz2'  :	// This may just be an individual file (e.g. sql script)
			case 'bzip2':
				$adapter = JArchive::getAdapter('bzip2');

				if ($adapter) {
					$config		= JFactory::getConfig();
					$tmpfname	= $config->get('tmp_path').DS.uniqid('bzip2');
					$bzresult	= $adapter->extract($archivename, $tmpfname);

					if (JError::isError($bzresult)) {
						@unlink($tmpfname);
						return false;
					}

					if ($untar) {
						// Try to untar the file
						$tadapter = JArchive::getAdapter('tar');

						if ($tadapter) {
							$result = $tadapter->extract($tmpfname, $extractdir);
						}
					}
					else {
						$path = JPath::clean($extractdir);
						JFolder::create($path);
						$result = JFile::copy($tmpfname,$path.DS.JFile::stripExt(JFile::getName(strtolower($archivename))),null,1);
					}

					@unlink($tmpfname);
				}
				break;

			default:
				JError::raiseWarning(10, JText::_('JLIB_FILESYSTEM_UNKNOWNARCHIVETYPE'));
				return false;
				break;
		}

		if (! $result || JError::isError($result)) {
			return false;
		}

		return true;
	}

	/**
	 * @param	string	$type
	 *
	 * @return	JObject
	 * @since	1.5
	 */
	public static function getAdapter($type)
	{
		static $adapters;

		if (!isset($adapters)) {
			$adapters = array();
		}

		if (!isset($adapters[$type])) {
			// Try to load the adapter object
			$class = 'JArchive'.ucfirst($type);

			if (!class_exists($class)) {
				$path = dirname(__FILE__).DS.'archive'.DS.strtolower($type).'.php';
				if (file_exists($path)) {
					require_once $path;
				}
				else {
					JError::raiseError(500,JText::_('JLIB_FILESYSTEM_UNABLE_TO_LOAD_ARCHIVE'));
				}
			}

			$adapters[$type] = new $class();
		}

		return $adapters[$type];
	}
}