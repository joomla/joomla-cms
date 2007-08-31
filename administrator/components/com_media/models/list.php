<?php
/**
 * @version		$Id: weblink.php 8117 2007-07-20 13:37:22Z friesengeist $
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Media Component List Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Media
 * @since 1.5
 */
class MediaModelList extends JModel
{

	function getState($property = null)
	{
		static $set;

		if (!$set) {
			$folder = JRequest::getVar( 'folder', '', '', 'path' );
			$this->setState('folder', $folder);

			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			$set = true;
		}
		return parent::getState($property);
	}

	function getImages()
	{
		$list = $this->getList();
		return $list['images'];
	}

	function getFolders()
	{
		$list = $this->getList();
		return $list['folders'];
	}

	function getDocuments()
	{
		$list = $this->getList();
		return $list['docs'];
	}

	/**
	 * Build imagelist
	 *
	 * @param string $listFolder The image directory to display
	 * @since 1.5
	 */
	function getList()
	{
		static $list;

		// Only process the list once per request
		if (is_array($list)) {
			return $list;
		}

		// Get current path from request
		$current = $this->getState('folder');

		// If undefined, set to empty
		if ($current == 'undefined') {
			$current = '';
		}

		// Initialize variables
		if (strlen($current) > 0) {
			$basePath = COM_MEDIA_BASE.DS.$current;
		} else {
			$basePath = COM_MEDIA_BASE;
		}
		$mediaBase = str_replace(DS, '/', COM_MEDIA_BASE.'/');

		$images 	= array ();
		$folders 	= array ();
		$docs 		= array ();

		// Get the list of files and folders from the given folder
		$fileList 	= JFolder::files($basePath);
		$folderList = JFolder::folders($basePath);

		// Iterate over the files if they exist
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.DS.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {
					$tmp = new JObject();
					$tmp->name = $file;
					$tmp->path = str_replace(DS, '/', JPath::clean($basePath.DS.$file));
					$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
					$tmp->size = filesize($tmp->path);

					$ext = strtolower(JFile::getExt($file));
					switch ($ext)
					{
						// Image
						case 'jpg':
						case 'png':
						case 'gif':
						case 'xcf':
						case 'odg':
						case 'bmp':
						case 'jpeg':
							$info = @getimagesize($tmp->path);
							$tmp->width		= @$info[0];
							$tmp->height	= @$info[1];
							$tmp->type		= @$info[2];
							$tmp->mime		= @$info['mime'];

							$filesize		= MediaHelper::parseSize($tmp->size);

							if (($info[0] > 70) || ($info[1] > 70)) {
								$dimensions = MediaHelper::imageResize($info[0], $info[1], 70);
								$tmp->width_80 = $dimensions[0];
								$tmp->height_80 = $dimensions[1];
							} else {
								$tmp->width_80 = $tmp->width;
								$tmp->height_80 = $tmp->height;
							}

							if (($info[0] > 16) || ($info[1] > 16)) {
								$dimensions = MediaHelper::imageResize($info[0], $info[1], 16);
								$tmp->width_16 = $dimensions[0];
								$tmp->height_16 = $dimensions[1];
							} else {
								$tmp->width_16 = $tmp->width;
								$tmp->height_16 = $tmp->height;
							}
							$images[] = $tmp;
							break;
						// Non-image document
						default:
							$iconfile_32 = JPATH_ADMINISTRATOR.DS."components".DS."com_media".DS."images".DS."mime-icon-32".DS.$ext.".png";
							if (file_exists($iconfile_32)) {
								$tmp->icon_32 = "components/com_media/images/mime-icon-32/".$ext.".png";
							} else {
								$tmp->icon_32 = "components/com_media/images/con_info.png";
							}
							$iconfile_16 = JPATH_ADMINISTRATOR.DS."components".DS."com_media".DS."images".DS."mime-icon-16".DS.$ext.".png";
							if (file_exists($iconfile_16)) {
								$tmp->icon_16 = "components/com_media/images/mime-icon-16/".$ext.".png";
							} else {
								$tmp->icon_16 = "components/com_media/images/con_info.png";
							}
							$docs[] = $tmp;
							break;
					}
				}
			}
		}

		// Iterate over the folders if they exist
		if ($folderList !== false) {
			foreach ($folderList as $folder) {
				$tmp = new JObject();
				$tmp->name = basename($folder);
				$tmp->path = str_replace(DS, '/', JPath::clean($basePath.DS.$folder));
				$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
				$count = MediaHelper::countFiles($tmp->path);
				$tmp->files = $count[0];
				$tmp->folders = $count[1];

				$folders[] = $tmp;
			}
		}

		$list = array('folders' => $folders, 'docs' => $docs, 'images' => $images);

		return $list;
	}
}
