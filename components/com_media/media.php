<?php
/**
 * @version $Id: admin.media.php 3382 2006-05-05 00:30:32Z webImagery $
 * @package Joomla
 * @subpackage Massmail
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

// Load the admin HTML view
require_once (JApplicationHelper::getPath('admin_html'));

$task	 = JRequest::getVar( 'task', '');
$listdir = JRequest::getVar( 'listdir', '');
$dirPath = JRequest::getVar( 'dirPath', '');

if (is_int(strpos($listdir, "..")) && $listdir != '') {
	josRedirect("administrator/index2.php?option=com_media&listdir=".$listDir, JText::_('NO HACKING PLEASE'));
}

define('COM_MEDIA_BASE', JPATH_SITE.DS.'images');
define('COM_MEDIA_BASEURL', $mainframe->getBaseURL().'images');

switch ($task) {

	case 'upload' :
		JMediaController::upload();
		break;

	case 'newdir' :
		JMediaController::createFolder($dirPath);
		JMediaController::showMedia($dirPath);
		break;

	case 'delete' :
		JMediaController::deleteFile($listdir);
		JMediaController::showMedia($listdir);
		break;

	case 'deletefolder' :
		JMediaController::deleteFolder($listdir);
		JMediaController::showMedia($listdir);
		break;

	case 'list' :
		JMediaController::listMedia($listdir);
		break;

		// popup directory creation interface for use by components
	case 'popupDirectory' :
		JMediaViews::popupDirectory(COM_MEDIA_BASEURL);
		break;

		// popup upload interface for use by components
	case 'popupUpload' :
		JMediaViews::popupUpload(COM_MEDIA_BASE);
		break;

	default :
		JMediaController::popupImgManager(COM_MEDIA_BASE);
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
	 * Image Manager Popup
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function popupImgManager($listFolder)
	{
		global $mainframe;

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
		$folderSelect = mosHTML::selectList($folders, 'dirPath', "class=\"inputbox\" size=\"1\" onchange=\"goUpDir()\" ", 'value', 'text', $listFolder);

		$doc = & $mainframe->getDocument();
		
		$doc->addStyleSheet('administrator/components/com_media/includes/manager.css');
		$doc->addScript('administrator/components/com_media/includes/manager.js');

		JMediaViews::popupImgManager($folderSelect, null);
	}
}
?>
