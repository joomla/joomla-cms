<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Massmail
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// ensure user has access to this function
if (!$acl->acl_check('com_media', 'manage', 'users', $my->usertype)) {
	josRedirect('index2.php', JText :: _('ALERTNOTAUTH'));
}

// Load the admin HTML view
require_once (JApplicationHelper :: getPath('admin_html'));

$cid = mosGetParam($_POST, 'cid', array (0));
if (!is_array($cid)) {
	$cid = array (0);
}

$task	 = JRequest::getVar( 'task', '');
$listdir = JRequest::getVar( 'listdir', '');
$dirPath = JRequest::getVar( 'dirPath', '');

if (is_int(strpos($listdir, "..")) && $listdir != '') {
	josRedirect("index2.php?option=com_media&listdir=".$listDir, JText :: _('NO HACKING PLEASE'));
}

define('COM_MEDIA_BASE', JPATH_SITE.DS.'images');
define('COM_MEDIA_BASEURL', JURL_SITE.'/images');

switch ($task) {

	case 'upload' :
		JMediaController :: upload();
		break;

	case 'newdir' :
		JMediaController :: createFolder($dirPath);
		JMediaController :: showMedia($dirPath);
		break;

	case 'delete' :
		JMediaController :: deleteFile($listdir);
		JMediaController :: showMedia($listdir);
		break;

	case 'deletefolder' :
		JMediaController :: deleteFolder($listdir);
		JMediaController :: showMedia($listdir);
		break;

	case 'list' :
		JMediaController :: listMedia($listdir);
		break;

	case 'cancel' :
		mosRedirect('index2.php');
		break;

		// popup directory creation interface for use by components
	case 'popupDirectory' :
		JMediaViews :: popupDirectory(COM_MEDIA_BASEURL);
		break;

		// popup upload interface for use by components
	case 'popupUpload' :
		JMediaViews :: popupUpload(COM_MEDIA_BASE);
		break;

	default :
		JMediaController :: showMedia($listdir);
		break;
}

/**
 * Media Manager Controller 
 * 
 * @static
 * @package Joomla
 * @subpackage Media
 * @since 1.1
 */
class JMediaController {

	/**
	 * Show media manager
	 * 
	 * @param string $listFolder The image directory to display
	 * @since 1.1
	 */
	function showMedia($listFolder) {
		/*
		 * Get the list of folders 
		 */
		$imgFolders = JFolder :: folders(COM_MEDIA_BASE, '.', true, true);

		/*
		 * Build the array of select options for the folder list
		 */
		$folders[] = mosHTML :: makeOption("/");
		foreach ($imgFolders as $folder) {
			$folder = str_replace(COM_MEDIA_BASE, "", $folder);
			$folder = str_replace(DS, "/", $folder);
			$folders[] = mosHTML :: makeOption($folder);
		}

		/*
		 * Sort the folder list array
		 */
		if (is_array($folders)) {
			sort($folders);
		}

		/*
		 * Create the drop-down folder select list
		 */
		$folderSelect = mosHTML :: selectList($folders, 'dirPath', "class=\"inputbox\" size=\"1\" onchange=\"goUpDir()\" ", 'value', 'text', $listFolder);

		JMediaViews :: showMedia($folderSelect, $listFolder);
	}

	/**
	 * Build imagelist
	 * 
	 * @param string $listFolder The image directory to display
	 * @since 1.1
	 */
	function listMedia($listFolder) {
		/*
		 * Initialize variables
		 */
		$basePath = COM_MEDIA_BASE.DS.$listFolder;
		$images = array ();
		$folders = array ();
		$docs = array ();
		$imageTypes = 'xcf|odg|gif|jpg|png|bmp';

		/*
		 * Get the list of files and folders from the given folder
		 */
		$fileList = JFolder :: files($basePath);
		$folderList = JFolder :: folders($basePath);

		/*
		 * Iterate over the files if they exist
		 */
		if ($fileList !== false) {
			foreach ($fileList as $file) {

				if (is_file($basePath.DS.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {
					if (eregi($imageTypes, $file)) {
						$imageInfo = @ getimagesize($basePath.DS.$file);
						$fileDetails['file'] = $basePath.DS.$file;
						$fileDetails['imgInfo'] = $imageInfo;
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$images[$file] = $fileDetails;
					} else {
						/*
						 * Not a known image file, so we will call it a document
						 */
						$fileDetails['size'] = filesize($basePath.DS.$file);
						$fileDetails['file'] = $basePath.DS.$file;
						$docs[$file] = $fileDetails;
					}
				}
			}
		}

		/*
		 * Iterate over the folders if they exist
		 */
		if ($folderList !== false) {
			foreach ($folderList as $folder) {
				$folders[$folder] = $folder;
			}
		}

		/*
		 * If there are no errors then lets list the media
		 */
		if ($folderList !== false && $fileList !== false) {
			JMediaViews :: listMedia($listFolder, $folders, $docs, $images);
		} else {
			JMediaViews :: listError();
		}
	}

	/**
	 * Upload a file
	 * 
	 * @since 1.1
	 */
	function upload() {
		global $clearUploads;

		$file 		= JRequest::getVar( 'upload', '', 'file' );
		$dirPath 	= JRequest::getVar( 'dirPath', '' );
		
		if (isset ($file) && is_array($file) && isset ($dirPath)) {
			$dirPathPost = $dirPath;
			$destDir = COM_MEDIA_BASE.$dirPathPost.DS;

			if (file_exists($destDir.$file['name'])) {
				josRedirect("index3.php?option=com_media&task=popupUpload&listdir=".$dirPath, JText :: _('Upload FAILED.File allready exists'));
			}

			$format = JFile :: getExt($file['name']);

			$allowable = array ('bmp', 'csv', 'doc', 'epg', 'gif', 'ico', 'jpg', 'odg', 'odp', 'ods', 'odt', 'pdf', 'png', 'ppt', 'swf', 'txt', 'xcf', 'xls');
			if (in_array($format, $allowable)) {
				$noMatch = true;
			} else {
				$noMatch = false;
			}

			if (!$noMatch) {
				josRedirect("index3.php?option=com_media&task=popupUpload&listdir=".$dirPath, JText :: _('This file type is not supported'));
			}

			if (!JFile :: upload($file['tmp_name'], $destDir.strtolower($file['name']))) {
				josRedirect("index3.php?option=com_media&task=popupUpload&listdir=".$dirPath, JText :: _('Upload FAILED'));
			} else {
				josRedirect("index3.php?option=com_media&task=popupUpload&listdir=".$dirPath, JText :: _('Upload complete'));
			}

			$clearUploads = true;
		}
	}

	/**
	 * Create a folder
	 * 
	 * @param string $path Path of the folder to create
	 * @since 1.1
	 */
	function createFolder($path) {
		$folderName = mosGetParam($_POST, 'foldername', '');

		if (strlen($folderName) > 0) {
			if (eregi("[^0-9a-zA-Z_]", $folderName)) {
				mosRedirect("index2.php?option=com_media&listdir=".$_POST['dirPath'], JText :: _('WARNDIRNAME'));
			}
			$folder = COM_MEDIA_BASE.$path.DS.$folderName;
			if (!is_dir($folder) && !is_file($folder)) {
				$folder = JPath :: clean($folder);
				JFolder :: create($folder);
				JFile :: write($folder."index.html", "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>");
			}
		}
	}

	/**
	 * Deletes a file
	 * 
	 * @param string $listFolder The image directory to delete a file from
	 * @since 1.1
	 */
	function deleteFile($listdir) {
		$delFile = JRequest::getVar( 'delFile' );
		$fullPath = COM_MEDIA_BASE.$listdir.DS.$delFile;

		return JFile :: delete($fullPath);
	}

	/**
	 * Delete a folder
	 * 
	 * @param string $listdir The image directory to delete a folder from
	 * @since 1.1
	 */
	function deleteFolder($listdir) {
		$canDelete = true;
		$delFolder = JRequest::getVar( 'delFolder' );
		$delFolder = COM_MEDIA_BASE.$listdir.$delFolder;

		$files = JFolder :: files($delFolder, '.', true);

		foreach ($files as $file) {
			if ($file != 'index.html') {
				$canDelete = false;
			}
		}

		if ($canDelete) {
			JFolder :: delete($delFolder);
		} else {
			echo '<font color="red">'.JText :: _('Unable to delete: not empty!').'</font>';
		}
	}
}
?>