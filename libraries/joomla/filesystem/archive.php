<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	FileSystem
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
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
 * @subpackage		FileSystem
 * @since		1.5
 */
class JArchive
{
	/**
	 * @param string The name of the archive file
	 * @param string Directory to unpack into
	 * $return boolean for success
	 */
	function extract( $archivename, $extractdir)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$ext = JFile::getExt(strtolower($archivename));
		switch ($ext)
		{
			case 'zip':
				$adapter =& JArchive::getAdapter('zip');
				if ($adapter) {
					$result = $adapter->extract($archivename, $extractdir);
				}
				break;
			case 'tar':
				$adapter =& JArchive::getAdapter('tar');
				if ($adapter) {
					$result = $adapter->extract($archivename, $extractdir);
				}
				break;
			case 'gz';
			case 'tgz';
			case 'gzip';
				$adapter =& JArchive::getAdapter('gzip');
				if ($adapter) {
					$config =& JFactory::getConfig();
					$tmpfname = $config->getValue('config.tmp_path').DS.uniqid('gzip');
					$gzresult = $adapter->extract($archivename, $tmpfname);
					if (JError::isError($gzresult)) {
						@unlink($tmpfname);
						return false;
					}
					// Try to untar the file
					$tadapter =& JArchive::getAdapter('tar');
					if ($tadapter) {
						$result = $tadapter->extract($tmpfname, $extractdir);
					}
					@unlink($tmpfname);
				}
				break;
			case 'bz2';
			case 'tbz2';
			case 'bzip2';
				$adapter =& JArchive::getAdapter('bzip2');
				if ($adapter) {
					$config =& JFactory::getConfig();
					$tmpfname = $config->getValue('config.tmp_path').DS.uniqid('bzip2');
					$bzresult = $adapter->extract($archivename, $tmpfname);
					if (JError::isError($bzresult)) {
						@unlink($tmpfname);
						return false;
					}
					// Try to untar the file
					$tadapter =& JArchive::getAdapter('tar');
					if ($tadapter) {
						$result = $tadapter->extract($tmpfname, $extractdir);
					}
					@unlink($tmpfname);
				}
				break;
			default:
				JError::raiseWarning(10, JText::_('UNKNOWNARCHIVETYPE'));
				return false;
				break;
		}

		if (JError::isError($result)) {
			return false;
		}
		return true;
	}

	function &getAdapter($type)
	{
		static $adapters;

		if (!isset($adapters)) {
			$adapters = array();
		}

		if (!isset($adapters[$type])) {
			// Try to load the adapter object
			jimport('joomla.filesystem.archive.'.strtolower($type));
			$class = 'JArchive'.ucfirst($type);
			if (!class_exists($class)) {
				$false = false;
				return $false;
			}
			$adapters[$type] = new $class();
		}
		return $adapters[$type];
	}
}