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
 * @subpackage	FileSystem
 * @since		1.5
 */
class JArchive
{
	/**
	 * @param	string	The name of the archive file
	 * @param	string	Directory to unpack into
	 * @return	boolean	True for success
	 */
	function extract( $archivename, $extractdir)
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
			case 'tgz'  :
				$untar = true;	// This format is a tarball gzip'd
			case 'gz'   :	// This may just be an individual file (e.g. sql script)
			case 'gzip' : 
				$adapter =& JArchive::getAdapter('gzip');
				if ($adapter)
				{
					$config =& JFactory::getConfig();
					$tmpfname = $config->getValue('config.tmp_path').DS.uniqid('gzip');
					$gzresult = $adapter->extract($archivename, $tmpfname);
					if (JError::isError($gzresult))
					{
						@unlink($tmpfname);
						return false;
					}
					if($untar)
					{
						// Try to untar the file
						$tadapter =& JArchive::getAdapter('tar');
						if ($tadapter) {
							$result = $tadapter->extract($tmpfname, $extractdir);
						}
					}
					else
					{
						$path = JPath::clean($extractdir);
						JFolder::create($path);
						$result = JFile::copy($tmpfname,$path.DS.JFile::stripExt(JFile::getName(strtolower($archivename))));
					}
					@unlink($tmpfname);
				}
				break;
			case 'tbz2' :
				$untar = true; // This format is a tarball bzip2'd
			case 'bz2'  :	// This may just be an individual file (e.g. sql script)
			case 'bzip2':
				$adapter =& JArchive::getAdapter('bzip2');
				if ($adapter)
				{
					$config =& JFactory::getConfig();
					$tmpfname = $config->getValue('config.tmp_path').DS.uniqid('bzip2');
					$bzresult = $adapter->extract($archivename, $tmpfname);
					if (JError::isError($bzresult))
					{
						@unlink($tmpfname);
						return false;
					}
					if ($untar)
					{
						// Try to untar the file
						$tadapter =& JArchive::getAdapter('tar');
						if ($tadapter) {
							$result = $tadapter->extract($tmpfname, $extractdir);
						}
					}
					else
					{
						$path = JPath::clean($extractdir);
						JFolder::create($path);
						$result = JFile::copy($tmpfname,$path.DS.JFile::stripExt(JFile::getName(strtolower($archivename))));
					}
					@unlink($tmpfname);
				}
				break;
			default:
				JError::raiseWarning(10, JText::_('UNKNOWNARCHIVETYPE'));
				return false;
				break;
		}

		if (! $result || JError::isError($result)) {
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

		if (!isset($adapters[$type]))
		{
			// Try to load the adapter object
			$class = 'JArchive'.ucfirst($type);
			
			if (!class_exists($class))
			{
				$path = dirname(__FILE__).DS.'archive'.DS.strtolower($type).'.php';
				if (file_exists($path)) {
					require_once($path);
				} else {
					JError::raiseError(500,JText::_('Unable to load archive'));
				}
			}
			
			$adapters[$type] = new $class();
		}
		return $adapters[$type];
	}

	/**
	 * @param	string	The name of the archive
	 * @param	mixed	The name of a single file or an array of files
	 * @param	string	The compression for the archive
	 * @param	string	Path to add within the archive
	 * @param	string	Path to remove within the archive
	 * @param	boolean	Automatically append the extension for the archive
	 * @param	boolean	Remove for source files
	 */
	function create($archive, $files, $compress = 'tar', $addPath = '', $removePath = '', $autoExt = false, $cleanUp = false)
	{
		jimport( 'archive.tar' );

		if (is_string($files)) {
			$files = array ($files);
		}
		if ($autoExt) {
			$archive .= '.'.$compress;
		}

		$tar = new Archive_Tar( $archive, $compress );
		$tar->setErrorHandling(PEAR_ERROR_PRINT);
		$tar->createModify( $files, $addPath, $removePath );

		if ($cleanUp) {
			JFile::delete( $files );
		}
		return $tar;
	}
}