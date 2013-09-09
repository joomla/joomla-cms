<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Media Component List Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaModelMediaList extends JModelDatabase
{
	/**
	 * Get State
	 *
	 * @since 3.2
	 */
	public function getState($property = null, $default = null)
	{
		static $set;

		if (!$set)
		{
			$input = JFactory::getApplication()->input;
			$state = $this->state;
			$folder = $input->get('folder', '', 'path');
			$state->set('folder', $folder);
			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$state->set('parent', $parent);
			$set = true;
		}

		return $this->state;
	}

	/**
	 * Get Images
	 *
	 *
	 * @since 3.2
	 */
	public function getImages()
	{
		$list = $this->getList();

		return $list['images'];
	}

	/**
	 * Get List
	 *
	 *
	 * @since 3.2
	 */
	public function getFolders()
	{
		$list = $this->getList();

		return $list['folders'];
	}

	/**
	 * Get Documents
	 *
	 *
	 * @since 3.2
	 */
	public function getDocuments()
	{
		$list = $this->getList();

		return $list['docs'];
	}

	/**
	 * Get List
	 *
	 *
	 * @since 3.2
	 */
	public function getList()
	{
		static $list;

		// Only process the list once per request
		if (is_array($list))
		{
			return $list;
		}

		// Get current path from request
		$current = $this->state->get('folder');

		// If undefined, set to empty
		if ($current == 'undefined')
		{
			$current = '';
		}

		if (strlen($current) > 0)
		{
			$basePath = COM_MEDIA_BASE . '/' . $current;
		}
		else
		{
			$basePath = COM_MEDIA_BASE;
		}

		$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE . '/');

		$images = array();
		$folders = array();
		$docs = array();

		$fileList = false;
		$folderList = false;

		if (file_exists($basePath))
		{
			// Get the list of files and folders from the given folder
			$fileList = $this->getFiles($current);
			$folderList = JFolder::folders($basePath);
		}

		// Iterate over the files if they exist
		if ($fileList !== false)
		{
			foreach ($fileList as $fileObject)
			{
				$file = $fileObject->file;

				if (is_file($basePath . '/' . $file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html')
				{
					$tmp = new JObject;
					$tmp->name = $file;
					$tmp->title = $file;
					$tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $file));
					$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
					$tmp->size = filesize($tmp->path);
					$tmp->checkedOut = $fileObject->checkedOut;

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
						case 'ico':
							$info = @getimagesize($tmp->path);
							$tmp->width = @$info[0];
							$tmp->height = @$info[1];
							$tmp->type = @$info[2];
							$tmp->mime = @$info['mime'];

							if (($info[0] > 60) || ($info[1] > 60))
							{
								$dimensions = MediaHelper::imageResize($info[0], $info[1], 60);
								$tmp->width_60 = $dimensions[0];
								$tmp->height_60 = $dimensions[1];
							}
							else
							{
								$tmp->width_60 = $tmp->width;
								$tmp->height_60 = $tmp->height;
							}

							if (($info[0] > 16) || ($info[1] > 16))
							{
								$dimensions = MediaHelper::imageResize($info[0], $info[1], 16);
								$tmp->width_16 = $dimensions[0];
								$tmp->height_16 = $dimensions[1];
							}
							else
							{
								$tmp->width_16 = $tmp->width;
								$tmp->height_16 = $tmp->height;
							}

							$images[] = $tmp;
							break;

						// Non-image document
						default:
							$tmp->icon_32 = "media/mime-icon-32/" . $ext . ".png";
							$tmp->icon_16 = "media/mime-icon-16/" . $ext . ".png";
							$docs[] = $tmp;
							break;
					}
				}
			}
		}

		// Iterate over the folders if they exist
		if ($folderList !== false)
		{
			foreach ($folderList as $folder)
			{
				$tmp = new JObject;
				$tmp->name = basename($folder);
				$tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $folder));
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

	/**
	 * Get Files
	 *
	 *
	 * @since 3.2
	 */
	public function getFiles($basePath)
	{
		$input = JFactory::getApplication()->input;
		$search = $input->get('search', '', 'STRING');
		$input->set('filter.search', $search);
		$category = $input->get('category', 0, 'INT');
		$access = $input->get('access', 0, 'INT');
		$ordering = $input->get('ordering', '', 'STRING');
		$direction = $input->get('direction', '', 'STRING');

		$basePath = $this->fixPath($basePath);
		$db = $this->db;
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$columns = array('urls', 'checked_out');
		$regex = $basePath == '' ? '^[^\/:*?"<>|]+.[a-zA-Z0-9]+$': '^' . $basePath . '\\\\' . '[^\\\\/:*?"<>|]+.[a-zA-Z0-9]+$';
		$query	->select($columns)
				->from("#__ucm_media")
				->where('urls regexp' . $this->db->quote($regex))
				->where('alias like' . $this->db->quote('%' . $search . '%'));

		if ($category != 0)
		{
			$query->where('catid = ' . $this->db->quote($category));
		}

		if ($access != 0)
		{
			$query->where('access = ' . $this->db->quote($access));
		}

		if ($ordering == '')
		{
			$ordering = 'alias';
		}

		$query->order($db->quoteName($ordering) . ' ' . $direction);

		$db->setQuery($query);
		$results = $db->loadObjectList();
		$temp = array();

		foreach ($results as $result)
		{
			$t = pathinfo($result->urls);
			$tempObject = new JObject;
			$tempObject->file = $t['basename'];
			$tempObject->checkedOut = $this->isCheckedOut((int) $result->checked_out);
			array_push($temp, $tempObject);
		}

		return $temp;
	}

	/**
	 * Fix the editing path
	 *
	 *
	 * @since 3.2
	 */
	public function fixPath($path = null){
		$path = str_replace('/', '\\', $path);
		$path = str_replace('\\', '\\\\', $path);
		$path = trim($path, "\\");

		return $path;
	}

	/**
	 * Check if current media is checked out
	 *
	 *
	 * @since 3.2
	 */
	public function isCheckedOut($id){
		$user = JFactory::getUser();

		return ($id == 0) || ($user->id == $id) ? '<i class="icon-publish"></i>' : '<i class="icon-lock"></i>';
	}
}
