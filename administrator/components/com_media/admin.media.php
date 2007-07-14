<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Make sure the user is authorized to view this page
$user = & JFactory::getUser();

if ($mainframe->isAdmin())
{
	if (!$user->authorize( 'com_media', 'manage' )) {
		$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
	}
	define('COM_MEDIA_BASE', JPATH_SITE.DS.'images');
	define('COM_MEDIA_BASEURL', $mainframe->getSiteURL().'images');
}
else
{
	if (!$user->authorize( 'com_media', 'popup' )) {
		$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
	}
	define('COM_MEDIA_BASE', JPATH_SITE.DS.'images'.DS.'stories');
	define('COM_MEDIA_BASEURL', JURI::base().'images/stories');
}

$folder			= JRequest::getVar('folder', '', '', 'string'); //Jinx : path filter doesn't work
$folderCheck	= JRequest::getVar('folder', null, '', 'string', JREQUEST_ALLOWRAW);
if (($folderCheck !== null) && ($folder !== $folderCheck)) {
	JError::raiseWarning(403, JText::_('WARNDIRNAME'));
}

require_once( JPATH_COMPONENT.DS.'helpers'.DS.'media.php' );

$task = JRequest::getCmd('task');
switch ($task) {

	case 'upload' :
		MediaController::upload();
		break;

	case 'uploadbatch' :
		MediaController::batchUpload();
		MediaController::showMedia();
		break;

	case 'createfolder' :
		MediaController::createFolder();
		MediaController::showMedia();
		break;

	case 'delete' :
		MediaController::delete($folder);
		MediaController::listMedia();
		break;

	case 'list' :
		MediaController::listMedia();
		break;

		// popup directory creation interface for use by components
	case 'popupDirectory' :
		MediaController::showFolder();
		break;

		// popup upload interface for use by components
	case 'popupUpload' :
		MediaController::showUpload();
		break;

		// popup upload interface for use by components
	case 'imgManager' :
		MediaController::imgManager(COM_MEDIA_BASE);
		break;

	case 'imgManagerList' :
		MediaController::imgManagerList($folder);
		break;

	default :
		MediaController::showMedia();
		break;
}

/**
 * Media Manager Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Media
 * @since 1.5
 */
class MediaController
{
	/**
	 * Show media manager
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function showMedia($base = null)
	{
		// Do not allow cache
		JResponse::allowCache(false);

		// Load the admin HTML view
		require_once (JApplicationHelper::getPath('admin_html'));

		// Get some paths from the request
		if (empty($base)) {
			$base = COM_MEDIA_BASE;
		}

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$imgFolders = JFolder::folders($base, '.', true, true);

		$nodes = array();
		foreach ($imgFolders as $folder) {
			$folder 	= str_replace($base, "", $folder);
			$folder		= (strpos($folder, DS) === 0) ? substr($folder, 1) : $folder ;
			$folder 	= str_replace(DS, "/", $folder);
			$nodes[] 	= $folder;
		}
		$tree = MediaController::_buildFolderTree($nodes);

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		jimport('joomla.client.helper');
		$ftp = !JClientHelper::hasCredentials('ftp');

		MediaViews::showMedia($tree, $ftp);
	}

	/**
	 * Build imagelist
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function listMedia()
	{
		// Do not allow cache
		JResponse::allowCache(false);

		// Load the admin HTML view
		require_once (JApplicationHelper::getPath('admin_html'));

		// Get current path from request
		$current = JRequest::getVar( 'folder', '', '', 'path' );

		// If undefined, set to empty
		if ($current == 'undefined')
		{
			$current = '';
		}

		// Initialize variables
		if (strlen($current) > 1) {
			$basePath = COM_MEDIA_BASE.DS.$current;
		} else {
			$basePath = COM_MEDIA_BASE;
		}
		$images 	= array ();
		$folders 	= array ();
		$docs 		= array ();

		// Get the list of files and folders from the given folder
		jimport('joomla.filesystem.folder');
		$fileList 	= JFolder::files($basePath);
		$folderList = JFolder::folders($basePath);

		// Iterate over the files if they exist
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.DS.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {
					if (MediaHelper::isImage($file)) {
						$imageInfo = @ getimagesize($basePath.DS.$file);
						$fileDetails['name'] = $file;
						$fileDetails['file'] = JPath::clean($basePath.DS.$file);
						$fileDetails['imgInfo'] = $imageInfo;
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$images[] = $fileDetails;
					} else {
						// Not a known image file so we will call it a document
						$fileDetails['name'] = $file;
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$fileDetails['file'] = JPath::clean($basePath.DS.$file);
						$docs[] = $fileDetails;
					}
				}
			}
		}

		// Iterate over the folders if they exist
		if ($folderList !== false) {
			foreach ($folderList as $folder) {
				$folders[$folder] = $folder;
			}
		}

		// If there are no errors then lets list the media
		if ($folderList !== false && $fileList !== false) {
			MediaViews::listMedia($current, $folders, $docs, $images);
		} else {
			MediaViews::listError();
		}
	}

	/**
	 * Upload popup
	 *
	 * @since 1.5
	 */
	function showUpload($msg = '')
	{
		$directory = JRequest::getVar( 'directory', '', '', 'path' );

		// Load the admin popup view
		require_once (dirname(__FILE__).DS.'admin.media.popup.php');

		//attach stylesheet to document
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet('components/com_media/assets/popup-imageupload.css');
		$doc->addScript('components/com_media/assets/popup-imageupload.js');

		MediaViews::popupUpload($directory, $msg);
	}

	/**
	 * Upload popup
	 *
	 * @since 1.5
	 */
	function showFolder()
	{
		global $mainframe;

		// Load the admin popup view
		require_once (dirname(__FILE__).DS.'admin.media.popup.php');

		MediaViews::popupDirectory(COM_MEDIA_BASEURL);
	}

	/**
	 * Image Manager Popup
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function imgManager($listFolder)
	{
		global $mainframe;

		$document =& JFactory::getDocument();

		$lang = & JFactory::getLanguage();
		$lang->load('', JPATH_ADMINISTRATOR);
		$lang->load(JRequest::getCmd( 'option' ), JPATH_ADMINISTRATOR);

		$document->setTitle(JText::_('Insert Image'));

		// Load the admin popup view
		require_once (dirname(__FILE__).DS.'admin.media.popup.php');

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$imgFolders = JFolder::folders(COM_MEDIA_BASE, '.', true, true);

		// Build the array of select options for the folder list
		$folders[] = JHTML::_('select.option', "","/");
		foreach ($imgFolders as $folder) {
			$folder 	= str_replace(COM_MEDIA_BASE, "", $folder);
			$folder 	= str_replace(DS, "/", $folder);
			$folders[] 	= JHTML::_('select.option', $folder);
		}

		// Sort the folder list array
		if (is_array($folders)) {
			sort($folders);
		}

		// Create the drop-down folder select list
		$folderSelect = JHTML::_('select.genericlist',  $folders, 'folderlist', "class=\"inputbox\" size=\"1\" onchange=\"document.imagemanager = new JImageManager(); document.imagemanager.setFolder(this.options[this.selectedIndex].value)\" ", 'value', 'text', $listFolder);

		//attach stylesheet to document
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$doc =& JFactory::getDocument();
		$doc->addStyleSheet('components/com_media/assets/popup-imagemanager.css');
		$doc->addScript('components/com_media/assets/popup-imagemanager.js');

		MediaViews::imgManager($folderSelect, null);
	}

	function imgManagerList($listFolder)
	{
		global $mainframe;

		// Load the admin popup view
		require_once (dirname(__FILE__).DS.'admin.media.popup.php');

		// Initialize variables
		$basePath 	= COM_MEDIA_BASE.$listFolder;
		$images 	= array ();
		$folders 	= array ();
		$docs 		= array ();

		// Get the list of files and folders from the given folder
		jimport('joomla.filesystem.folder');
		$fileList 	= JFolder::files($basePath);
		$folderList = JFolder::folders($basePath);

		// Iterate over the files if they exist
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.DS.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {
					if (MediaHelper::isImage($file)) {
						$imageInfo = @ getimagesize($basePath.DS.$file);
						$fileDetails['file'] = JPath::clean($basePath.DS.$file);
						$fileDetails['imgInfo'] = $imageInfo;
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$images[$file] = $fileDetails;
					} else {
						// Not a known image file so we will call it a document
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$fileDetails['file'] = JPath::clean($basePath.DS.$file);
						$docs[$file] = $fileDetails;
					}
				}
			}
		}

		// Iterate over the folders if they exist
		if ($folderList !== false) {
			foreach ($folderList as $folder) {
				$folders[$folder] = $folder;
			}
		}

		//attach stylesheet to document
		$doc = & JFactory::getDocument();
		$doc->addStyleSheet('components/com_media/assets/popup-imagelist.css');

		// If there are no errors then lets list the media
		if ($folderList !== false && $fileList !== false) {
			MediaViews::imgManagerList($listFolder, $folders, $images);
		} else {
			MediaViews::listError();
		}
	}

	/**
	 * Upload a file
	 *
	 * @since 1.5
	 */
	function upload()
	{
		global $mainframe;

		$file 		= JRequest::getVar( 'upload', '', 'files', 'array' );
		$folder		= JRequest::getVar( 'dirpath', '', '', 'path' );
		$err		= null;

		JRequest::setVar('folder', $folder);

		if (isset($file['name'])) {
			$filepath = JPath::clean(COM_MEDIA_BASE.DS.$folder.DS.strtolower($file['name']));

			if (!MediaHelper::canUpload( $file, $err )) {
				MediaController::showUpload(JText::_($err));
				return;
			}

			jimport('joomla.filesystem.file');
			if (JFile::exists($filepath)) {
				MediaController::showUpload(JText::_('Upload FAILED. File already exists'));
				return;
			}

			if (!JFile::upload($file['tmp_name'], $filepath)) {
				MediaController::showUpload(JText::_('Upload FAILED'));
				return;
			} else {
				MediaController::showUpload(JText::_('Upload complete'));
				return;
			}
		}
	}

	function batchUpload()
	{
		global $mainframe;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$files 			= JRequest::getVar( 'uploads', array(), 'files', 'array' );
		$folder			= JRequest::getVar( 'dirpath', '', '', 'path' );
		$err			= null;
		$file['size']	= 0;
		JRequest::setVar('folder', $folder);
		jimport('joomla.filesystem.file');

		if (is_array(@$files['name'])) {
			for ($i=0;$i<count($files['name']);$i++) {
				$filepath = JPath::clean(COM_MEDIA_BASE.DS.$folder.DS.strtolower($files['name'][$i]));
				$file['name'] = $files['name'][$i];
				$file['size'] += (int)$files['size'][$i];
				if (!MediaHelper::canUpload( $file, $err )) {
					$mainframe->redirect('index.php?option=com_media&folder='.$folder, JText::_($err));
				}
				if (JFile::exists($filepath)) {
					$mainframe->redirect('index.php?option=com_media&folder='.$folder, JText::_('Upload FAILED. File already exists'));
				}
				if (!JFile::upload($files['tmp_name'][$i], $filepath)) {
					$mainframe->redirect('index.php?option=com_media&folder='.$folder, JText::_('Upload FAILED'));
				}
			}
		}
	}

	/**
	 * Create a folder
	 *
	 * @param string $path Path of the folder to create
	 * @since 1.5
	 */
	function createFolder()
	{
		global $mainframe;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$folder			= JRequest::getCmd( 'foldername', '');
		$folderCheck	= JRequest::getVar( 'foldername', null, '', 'string', JREQUEST_ALLOWRAW);
		$parent			= JRequest::getVar( 'dirpath', '', '', 'path' );

		JRequest::setVar('folder', $parent);

		if (($folderCheck !== null) && ($folder !== $folderCheck)) {
			$mainframe->redirect('index.php?option=com_media&folder='.$parent, JText::_('WARNDIRNAME'));
		}

		if (strlen($folder) > 0) {
			$path = JPath::clean(COM_MEDIA_BASE.DS.$parent.DS.$folder);
			if (!is_dir($path) && !is_file($path))
			{
				jimport('joomla.filesystem.*');
				JFolder::create($path);
				JFile::write($path.DS."index.html", "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>");
			}
			JRequest::setVar('folder', ($parent) ? $parent.'/'.$folder : $folder);
		}
	}

	/**
	 * Deletes paths from the current path
	 *
	 * @param string $listFolder The image directory to delete a file from
	 * @since 1.5
	 */
	function delete($current)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$paths	= JRequest::getVar( 'rm', array(), '', 'array' );
		$ret	= false;
		if (count($paths)) {
			foreach ($paths as $path)
			{
				if ($path !== JInputFilter::clean($path, 'cmd')) {
					echo '<font color="red">'.JText::_('Unable to delete:').htmlspecialchars($path).' '.JText::_('WARNFILENAME').'</font><br />';
					continue;
				}

				$fullPath = JPath::clean(COM_MEDIA_BASE.DS.$current.DS.$path);
				if (is_file($fullPath)) {
					$ret |= !JFile::delete($fullPath);
				} else if (is_dir($fullPath)) {
					$files = JFolder::files($fullPath, '.', true);
					$canDelete = true;
					foreach ($files as $file) {
						if ($file != 'index.html') {
							$canDelete = false;
						}
					}
					if ($canDelete) {
						$ret |= !JFolder::delete($fullPath);
					} else {
						echo '<font color="red">'.JText::_('Unable to delete:').$fullPath.' '.JText::_('Not Empty!').'</font><br />';
					}
				}
			}
		}
		return !$ret;
	}

	function _buildFolderTree($list)
	{
		// id, parent, name, url, title, target
		$nodes = array();
		$i = 1;
		$nodes[''] = array ('id' => "0", 'pid' => -1, 'name' => JText::_('Images Folder'), 'url' => 'index.php?option=com_media&task=list&tmpl=component&folder=', 'title' => '/', 'target' => 'folderframe');
		if (is_array($list) && count($list)) {
			foreach ($list as $item) {
				// Try to find parent
				$pivot = strrpos($item, '/');
				$parent = substr($item, 0, $pivot);
				if (isset($nodes[$parent])) {
					$pid = $nodes[$parent]['id'];
				} else {
					$pid = -1;
				}
				$nodes[$item] = array ('id' => $i, 'pid' => $pid, 'name' => basename($item), 'url' => 'index.php?option=com_media&task=list&tmpl=component&folder='.$item, 'title' => $item, 'target' => 'folderframe');
				$i++;
			}
		}
		return $nodes;
	}
}
?>