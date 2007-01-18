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
if ($mainframe->isAdmin()) {
	if (!$user->authorize( 'com_media', 'manage' )) {
		$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
	}
} else {
	if (!$user->authorize( 'com_media', 'popup' )) {
		$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
	}
}

$cid = JRequest::getVar( 'cid', array (0), 'post', 'array');
if (!is_array($cid)) {
	$cid = array (0);
}

$folder = JRequest::getVar( 'folder', '');
if (is_int(strpos($folder, "..")) && $folder != '') {
	$mainframe->redirect("index.php?option=com_media&folder=".$folder, JText::_('NO HACKING PLEASE'));
}

define('COM_MEDIA_BASE', JPATH_SITE.DS.'images'.DS.'stories');
define('COM_MEDIA_BASEURL', ($mainframe->isAdmin()) ? $mainframe->getSiteURL().'images/stories' : JURI::base().'images/stories');

require_once( JPATH_COMPONENT.DS.'helpers'.DS.'media.php' );

$task = JRequest::getVar( 'task', '');
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

	case 'deletefolder' :
		MediaController::deleteFolder($folder);
		MediaController::showMedia();
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
		JResponse::setHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate' );
		JResponse::setHeader( 'Cache-Control', 'post-check=0, pre-check=0', false );	// HTTP/1.1
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
			$folder 	= str_replace(DS, "/", $folder);
			$nodes[] 	= $folder;
		}
		$tree = MediaController::_buildFolderTree($nodes);

		MediaViews::showMedia($tree);
	}

	/**
	 * Build imagelist
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function listMedia()
	{
		JResponse::setHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate' );
		JResponse::setHeader( 'Cache-Control', 'post-check=0, pre-check=0', false );	// HTTP/1.1
		// Load the admin HTML view
		require_once (JApplicationHelper::getPath('admin_html'));

		// Get current path from request
		$current = JRequest::getVar( 'cFolder' );

		// Initialize variables
		$basePath 	= COM_MEDIA_BASE.DS.$current;
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
						$fileDetails['file'] = JPath::clean($basePath.DS.$file, false);
						$fileDetails['imgInfo'] = $imageInfo;
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$images[] = $fileDetails;
					} else {
						// Not a known image file so we will call it a document
						$fileDetails['name'] = $file;
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$fileDetails['file'] = JPath::clean($basePath.DS.$file, false);
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
		$directory = JRequest::getVar( 'directory', '' );

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

		$lang = & JFactory::getLanguage();
		$lang->load('', JPATH_ADMINISTRATOR);
		$lang->load(JRequest::getVar( 'option' ), JPATH_ADMINISTRATOR);

		$mainframe->setPageTitle(JText::_('Insert Image'));

		// Load the admin popup view
		require_once (dirname(__FILE__).DS.'admin.media.popup.php');

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$imgFolders = JFolder::folders(COM_MEDIA_BASE, '.', true, true);

		// Build the array of select options for the folder list
		$folders[] = JHTMLSelect::option("/");
		foreach ($imgFolders as $folder) {
			$folder 	= str_replace(COM_MEDIA_BASE, "", $folder);
			$folder 	= str_replace(DS, "/", $folder);
			$folders[] 	= JHTMLSelect::option($folder);
		}

		// Sort the folder list array
		if (is_array($folders)) {
			sort($folders);
		}

		// Create the drop-down folder select list
		$folderSelect = JHTMLSelect::genericList($folders, 'folderlist', "class=\"inputbox\" size=\"1\" onchange=\"document.imagemanager.setFolder(this.options[this.selectedIndex].value)\" ", 'value', 'text', $listFolder);

		//attach stylesheet to document
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		$doc =& JFactory::getDocument();
		$doc->addStyleSheet('components/com_media/assets/popup-imagemanager.css');
		$doc->addScript('components/com_media/assets/popup-imagemanager.js');
		$doc->addScript( $url. 'includes/js/moofx/moo.fx.js' );
		$doc->addScript( $url. 'includes/js/moofx/moo.fx.pack.js' );

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
						$fileDetails['file'] = JPath::clean($basePath.DS.$file, false);
						$fileDetails['imgInfo'] = $imageInfo;
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$images[$file] = $fileDetails;
					} else {
						// Not a known image file so we will call it a document
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$fileDetails['file'] = JPath::clean($basePath.DS.$file, false);
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
		$dirPath 	= JRequest::getVar( 'dirPath', '' );
		$err		= null;

		JRequest::setVar('cFolder', $dirPath);

		if (isset ($file) && is_array($file) && isset ($dirPath)) {

			if (file_exists(COM_MEDIA_BASE.$dirPath.DS.$file['name'])) {
				MediaController::showUpload(JText::_('Upload FAILED.File allready exists'));
				return;
			}

			if (!MediaHelper::canUpload( $file, $err )) {
				MediaController::showUpload(JText::_($err));
				return;
			}

			if (!JFile::upload($file['tmp_name'], COM_MEDIA_BASE.$dirPath.DS.strtolower($file['name']))) {
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

		$files 			= JRequest::getVar( 'uploads', array(), 'files', 'array' );
		$dirPath 		= JRequest::getVar( 'dirpath', '' );
		$err			= null;
		$file['size']	= 0;
		JRequest::setVar('cFolder', $dirPath);
		jimport('joomla.filesystem.file');

		if (is_array($files) && isset ($dirPath)) {
			for ($i=0;$i<count($files['name']);$i++) {
				if (file_exists(COM_MEDIA_BASE.$dirPath.DS.$files['name'][$i])) {
					return false;
				}
				$file['name'] = $files['name'][$i];
				$file['size'] += (int)$files['size'][$i];
				if (!MediaHelper::canUpload( $file, $err )) {
					$mainframe->redirect("index.php?option=com_media&amp;cFolder=".$dirPath, JText::_($err));
					return;
				}
				if (!JFile::upload($files['tmp_name'][$i], COM_MEDIA_BASE.$dirPath.DS.strtolower($files['name'][$i]))) {
					$mainframe->redirect("index.php?option=com_media&amp;cFolder=".$dirPath, JText::_('Upload FAILED'));
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

		$folderName = JRequest::getVar( 'foldername', '');
		$dirPath 	= JRequest::getVar( 'dirpath', '' );
		if ($dirPath == '/') {
			$dirPath == '';
		}
		JRequest::setVar('cFolder', $dirPath);

		if (strlen($folderName) > 0) {
			if (eregi("[^0-9a-zA-Z_]", $folderName)) {
				$mainframe->redirect("index.php?option=com_media&amp;cFolder=".$dirPath, JText::_('WARNDIRNAME'));
			}
			$folder = COM_MEDIA_BASE.$dirPath.DS.$folderName;
			if (!is_dir($folder) && !is_file($folder))
			{
				jimport('joomla.filesystem.*');
				$folder = JPath::clean($folder);
				JFolder::create($folder);
				JFile::write($folder."index.html", "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>");
			}
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

		$paths	= JRequest::getVar( 'rm', array(), '', 'array' );
		$ret	= false;
		if (count($paths)) {
			foreach ($paths as $path)
			{
				$fullPath = JPath::clean(COM_MEDIA_BASE.$current.DS.$path, false);
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
						echo '<font color="red">'.JText::_('Unable to delete:').$fullPath.' '.JText::_('Not Empty!').'</font>';
					}
				}
			}
		}
		return !$ret;
	}

	/**
	 * Deletes a file
	 *
	 * @param string $listFolder The image directory to delete a file from
	 * @since 1.5
	 */
	function deleteFile($listdir)
	{
		jimport('joomla.filesystem.file');

		$delFile = JRequest::getVar( 'delFile' );
		$fullPath = COM_MEDIA_BASE.$listdir.DS.$delFile;

		return JFile::delete($fullPath);
	}

	/**
	 * Delete a folder
	 *
	 * @param string $listdir The image directory to delete a folder from
	 * @since 1.5
	 */
	function deleteFolder($listdir)
	{
		jimport('joomla.filesystem.folder');

		$canDelete = true;
		$delFolder = JRequest::getVar( 'delFolder' );
		$delFolder = COM_MEDIA_BASE.DS.$listdir.DS.$delFolder;

		$files = JFolder::files($delFolder, '.', true);

		foreach ($files as $file) {
			if ($file != 'index.html') {
				$canDelete = false;
			}
		}

		if ($canDelete) {
			$ret = JFolder::delete($delFolder);
		} else {
			echo '<font color="red">'.JText::_('Unable to delete: not empty!').'</font>';
		}
		return $ret;
	}

	function _buildFolderTree($list)
	{
		// id, parent, name, url, title, target
		$nodes = array();
		$i = 1;
		$nodes[''] = array ('id' => "0", 'pid' => -1, 'name' => JText::_('Images Folder'), 'url' => 'index.php?option=com_media&task=list&tmpl=component&cFolder=/', 'title' => '/', 'target' => 'folderframe');
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
				$nodes[$item] = array ('id' => $i, 'pid' => $pid, 'name' => basename($item), 'url' => 'index.php?option=com_media&task=list&tmpl=component&cFolder='.$item, 'title' => $item, 'target' => 'folderframe');
				$i++;
			}
		}
		return $nodes;
	}
}
?>