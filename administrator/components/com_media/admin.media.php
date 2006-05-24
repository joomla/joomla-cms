<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Media
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Make sure the user is authorized to view this page
$user = & $mainframe->getUser();
if ($mainframe->isAdmin()) {
	if (!$user->authorize( 'com_media', 'manage' )) {
		josRedirect('index.php', JText::_('ALERTNOTAUTH'));
	}
} else {
	if (!$user->authorize( 'com_media', 'popup' )) {
		josRedirect('index.php', JText::_('ALERTNOTAUTH'));
	}
}

$cid = JRequest::getVar( 'cid', array (0), 'post', 'array');
if (!is_array($cid)) {
	$cid = array (0);
}

$folder = JRequest::getVar( 'folder', '');
if (is_int(strpos($folder, "..")) && $folder != '') {
	josRedirect("index2.php?option=com_media&folder=".$folder, JText::_('NO HACKING PLEASE'));
}

define( 'JPATH_COM_MEDIA', dirname( __FILE__ ));
define('COM_MEDIA_BASE', JPATH_SITE.DS.'images');
define('COM_MEDIA_BASEURL', ($mainframe->isAdmin()) ? $mainframe->getSiteURL().'images' : $mainframe->getBaseURL().'images');

require_once( JPATH_COM_MEDIA . '/helper.php' );

$task = JRequest::getVar( 'task', '');
switch ($task) {

	case 'upload' :
		JMediaController::upload();
		break;

	case 'uploadBatch' :
		JMediaController::batchUpload();
		JMediaController::showMedia();
		break;

	case 'newdir' :
		$dirPath = JRequest::getVar( 'dirPath', '');
		JMediaController::createFolder($dirPath);
		JMediaController::showMedia();
		break;

	case 'delete' :
		JMediaController::deleteFile($folder);
		JMediaController::listMedia();
		break;

	case 'deletefolder' :
		JMediaController::deleteFolder($folder);
		JMediaController::listMedia();
		break;

	case 'list' :
		JMediaController::listMedia();
		break;

	case 'cancel' :
		josRedirect('index.php');
		break;

		// popup directory creation interface for use by components
	case 'popupDirectory' :
		JMediaController::showFolder();
		break;

		// popup upload interface for use by components
	case 'popupUpload' :
		JMediaController::showUpload();
		break;

		// popup upload interface for use by components
	case 'imgManager' :
		JMediaController::imgManager(COM_MEDIA_BASE);
		break;

	case 'imgManagerList' :
		JMediaController::imgManagerList($folder);
		break;

	default :
		JMediaController::showMedia();
		break;
}

/**
 * Media Manager Controller
 *
 * @static
 * @package Joomla
 * @subpackage Media
 * @since 1.5
 */
class JMediaController
{
	/**
	 * Show media manager
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function showMedia($base = null)
	{
		// Load the admin HTML view
		require_once (JApplicationHelper::getPath('admin_html'));

		// Get some paths from the request
		if (empty($base)) {
			$base = COM_MEDIA_BASE;
		}
		$current = JRequest::getVar( 'cFolder' );

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$imgFolders = JFolder::folders($base, '.', true, true);

		// Build the array of select options for the folder list
		$folders[] = mosHTML::makeOption("/");
		foreach ($imgFolders as $folder) {
			$folder 	= str_replace($base, "", $folder);
			$folder 	= str_replace(DS, "/", $folder);
			$nodes[] 	= $folder;
			$folders[] 	= mosHTML::makeOption($folder);
		}
		$tree = JMediaController::_buildFolderTree($nodes);

		// Sort the folder list array
		if (is_array($folders)) {
			sort($folders);
		}

		// Create the drop-down folder select list
		$folderSelect = mosHTML::selectList($folders, 'dirPath', "class=\"inputbox\" size=\"1\" onchange=\"goUpDir()\" ", 'value', 'text', $current);

		JMediaViews::showMedia($folderSelect, $current, $tree);
	}

	/**
	 * Build imagelist
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function listMedia()
	{
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
					if (JMediaHelper::isImage($file)) {
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
			JMediaViews::listMedia($current, $folders, $docs, $images);
		} else {
			JMediaViews::listError();
		}
	}
	
	/**
	 * Upload popup
	 *
	 * @since 1.5
	 */
	function showUpload($msg = '')
	{
		global $mainframe;
		
		$dirPath = JRequest::getVar( 'dirPath', '/' );
		
		// Load the admin popup view
		require_once (dirname(__FILE__).DS.'admin.media.popup.php');
		
		//attach stylesheet to document
		$doc = & $mainframe->getDocument();
		$doc->addStyleSheet('components/com_media/includes/popup-imageupload.css');
		$doc->addScript('components/com_media/includes/popup-imageupload.js');
		
		JMediaViews::popupUpload($dirPath, $msg);
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
		
		JMediaViews::popupDirectory(COM_MEDIA_BASEURL);
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
		
		$mainframe->setPageTitle(JText::_('Insert Image'));

		// Load the admin popup view
		require_once (dirname(__FILE__).DS.'admin.media.popup.php');

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$imgFolders = JFolder::folders(COM_MEDIA_BASE, '.', true, true);

		// Build the array of select options for the folder list
		$folders[] = mosHTML::makeOption("/");
		foreach ($imgFolders as $folder) {
			$folder 	= str_replace(COM_MEDIA_BASE, "", $folder);
			$folder 	= str_replace(DS, "/", $folder);
			$folders[] 	= mosHTML::makeOption($folder);
		}

		// Sort the folder list array
		if (is_array($folders)) {
			sort($folders);
		}

		// Create the drop-down folder select list
		$folderSelect = mosHTML::selectList($folders, 'folderlist', "class=\"inputbox\" size=\"1\" onchange=\"document.imagemanager.setFolder(this.options[this.selectedIndex].value)\" ", 'value', 'text', $listFolder);

		//attach stylesheet to document
		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : $mainframe->getBaseURL();
		
		$doc = & $mainframe->getDocument();
		$doc->addStyleSheet('components/com_media/includes/popup-imagemanager.css');
		$doc->addScript('components/com_media/includes/popup-imagemanager.js');
		$doc->addScript( $url. 'includes/js/moofx/moo.fx.js' );
		$doc->addScript( $url. 'includes/js/moofx/moo.fx.pack.js' );

		JMediaViews::imgManager($folderSelect, null);
	}

	function imgManagerList($listFolder)
	{
		global $mainframe;
		
		// Load the admin popup view
		require_once (dirname(__FILE__).DS.'admin.media.popup.php');

		// Initialize variables
		$basePath 	= COM_MEDIA_BASE.DS.$listFolder;
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
					if (JMediaHelper::isImage($file)) {
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
		$doc = & $mainframe->getDocument();
		$doc->addStyleSheet('components/com_media/includes/popup-imagelist.css');

		// If there are no errors then lets list the media
		if ($folderList !== false && $fileList !== false) {
			JMediaViews::imgManagerList($listFolder, $folders, $images);
		} else {
			JMediaViews::listError();
		}
	}

	/**
	 * Upload a file
	 *
	 * @since 1.5
	 */
	function upload()
	{
		global $mainframe, $clearUploads;

		$file 		= JRequest::getVar( 'upload', '', 'files', 'array' );
		$dirPath 	= JRequest::getVar( 'dirPath', '' );
		$juri 		= $mainframe->getURI();
		
		if (isset ($file) && is_array($file) && isset ($dirPath)) {
			$dirPathPost = $dirPath;
			$destDir = COM_MEDIA_BASE.$dirPathPost.DS;

			if (file_exists($destDir.$file['name'])) {
				JMediaController::showUpload(JText::_('Upload FAILED.File allready exists'));
				return;
			}

			if (!JMediaHelper::canUpload( $file, $err )) {
				JMediaController::showUpload(JText::_($err));
				return;
			}

			if (!JFile::upload($file['tmp_name'], $destDir.strtolower($file['name']))) {
				JMediaController::showUpload(JText::_('Upload FAILED'));
				return;
				
			} else {
				JMediaController::showUpload(JText::_('Upload complete'));
				return;
			}

			$clearUploads = true;
		}
	}

	function batchUpload()
	{
		$files 		= JRequest::getVar( 'uploads', array(), 'files', 'array' );
		$dirPath 	= JRequest::getVar( 'dirpath', '' );

		jimport('joomla.filesystem.file');

		if (is_array($files) && isset ($dirPath)) {
			for ($i=0;count($files['name']);$i++) {
				$dirPathPost = $dirPath;
				$destDir = COM_MEDIA_BASE.$dirPathPost.DS;
	
				if (file_exists($destDir.$files['name'][$i])) {
					return false;
				}

				$format	= JFile::getExt($files['name'][$i]);
				$allowable 	= array ('bmp', 'csv', 'doc', 'epg', 'gif', 'ico', 'jpg', 'odg', 'odp', 'ods', 'odt', 'pdf', 'png', 'ppt', 'swf', 'txt', 'xcf', 'xls');
				if (in_array($format, $allowable)) {
					$noMatch = true;
				} else {
					$noMatch = false;
				}

				if (!$noMatch) {
					josRedirect("index.php?option=com_media", JText::_('This file type is not supported'));
				}

				if (!JFile::upload($files['tmp_name'][$i], $destDir.strtolower($files['name'][$i]))) {
					josRedirect("index.php?option=com_media", JText::_('Upload FAILED'));
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
	function createFolder($path)
	{
		$folderName = JRequest::getVar( 'foldername', '', 'post' );
		$dirPath 	= JRequest::getVar( 'dirpath', '' );

		if (strlen($folderName) > 0) {
			if (eregi("[^0-9a-zA-Z_]", $folderName)) {
				josRedirect("index2.php?option=com_media&listdir=".$dirPath, JText::_('WARNDIRNAME'));
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
		$delFolder = COM_MEDIA_BASE.$listdir.$delFolder;

		$files = JFolder::files($delFolder, '.', true);

		foreach ($files as $file) {
			if ($file != 'index.html') {
				$canDelete = false;
			}
		}

		if ($canDelete) {
			JFolder::delete($delFolder);
		} else {
			echo '<font color="red">'.JText::_('Unable to delete: not empty!').'</font>';
		}
	}

	function _buildFolderTree($list)
	{
		// id, parent, name, url, title, target
		$nodes = array();
		$i = 1;
		$nodes[''] = array ('id' => "0", 'pid' => -1, 'name' => 'Images Folder', 'url' => 'index3.php?option=com_media&amp;task=list&amp;cFolder=/', 'title' => 'Images Folder', 'target' => 'imgManager');
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
				$nodes[$item] = array ('id' => $i, 'pid' => $pid, 'name' => basename($item), 'url' => 'index3.php?option=com_media&amp;task=list&amp;cFolder='.$item, 'title' => $item, 'target' => 'imgManager');
				$i++;
			}
		}
		return $nodes;
	}
}
?>