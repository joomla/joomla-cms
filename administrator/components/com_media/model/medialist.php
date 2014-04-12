<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Media Component MediaList Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.3
 */
class MediaModelMedialist extends ConfigModelForm
{
	public function getState($property = null, $default = null)
	{
		static $set;

		if (!$set)
		{
			$input  = JFactory::getApplication()->input;
			$folder = $input->get('folder', '', 'path');
			$this->state->set('folder', $folder);

			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->state->set('parent', $parent);
			$set = true;
		}

		if(!$property)
		{
			
			return parent::getState();
		}
		else
		{
			
			return parent::getState()->get($property, $default);
		}

	}

	public function getImages()
	{
		$list = $this->getList();

		return $list['images'];
	}

	public function getFolders()
	{
		$list = $this->getList();

		return $list['folders'];
	}

	public function getDocuments()
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
	public function getList()
	{
		$mediaHelper = new JHelperMedia;

		static $list;

		// Only process the list once per request
		if (is_array($list))
		{
			return $list;
		}

		// Get current path from request
		$current = $this->getState('folder');

		// If undefined, set to empty
		if ($current == 'undefined')
		{
			$current = '';
		}

		if (strlen($current) > 0)
		{
			$basePath = COM_MEDIA_BASE.'/'.$current;
		}
		else
		{
			$basePath = COM_MEDIA_BASE;
		}

		$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE.'/');

		$images		= array ();
		$folders	= array ();
		$docs		= array ();

		$fileList = false;
		$folderList = false;
		if (file_exists($basePath))
		{
			// Get the list of files and folders from the given folder
			$fileList	= JFolder::files($basePath);
			$folderList = JFolder::folders($basePath);
		}

		// Iterate over the files if they exist
		if ($fileList !== false)
		{
			foreach ($fileList as $file)
			{
				if (is_file($basePath.'/'.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html')
				{
					$tmp = new JObject;
					$tmp->name = $file;
					$tmp->title = $file;
					$tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $file));
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
						case 'ico':
							$info = @getimagesize($tmp->path);
							$tmp->width		= @$info[0];
							$tmp->height	= @$info[1];
							$tmp->type		= @$info[2];
							$tmp->mime		= @$info['mime'];

							if (($info[0] > 120) || ($info[1] > 120))
							{
								$dimensions = $mediaHelper->imageResize($info[0], $info[1], 120);
								$tmp->width_120 = $dimensions[0];
								$tmp->height_120 = $dimensions[1];
							}
							else {
								$tmp->width_120 = $tmp->width;
								$tmp->height_120 = $tmp->height;
							}

							if (($info[0] > 16) || ($info[1] > 16))
							{
								$dimensions = $mediaHelper->imageResize($info[0], $info[1], 16);
								$tmp->width_16 = $dimensions[0];
								$tmp->height_16 = $dimensions[1];
							}
							else {
								$tmp->width_16 = $tmp->width;
								$tmp->height_16 = $tmp->height;
							}

							$images[] = $tmp;
							break;

						// Non-image document
						default:
							$tmp->icon_32 = "media/mime-icon-32/".$ext.".png";
							$tmp->icon_16 = "media/mime-icon-16/".$ext.".png";
							$docs[] = $tmp;
							break;
					}
					
					// Get image id from #__ucm_content table
					$url = str_replace('/', '\\', $tmp->path);
					
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					
					$query 	-> select($db->quoteName('core_content_id'))
							-> from($db->quoteName('#__ucm_content'))
							-> where($db->quoteName('core_urls') . ' = '. $db->quote($url));

					$db->setQuery($query);

					$result = $db->loadObject();

					if($result != null)
					{
						$tmp->id = $result->core_content_id;

					}
					else
					{
						// Logic to add image to #__ucm_content and get core_content_id
						$newfile = array();
						$newfile['name'] = $tmp->name;
						$newfile['type'] = $tmp->type;
						$newfile['filepath'] = $url;
						$newfile['size'] = $tmp->size;
						
						// Using create controller to create a new record
						$createController = new MediaControllerMediaCreate();
						$input = JFactory::getApplication()->input;
						$input->set('file', $newfile);
						
						$createController->execute();
						
						// Get core_content_id of newly created record
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);
							
						$query 	-> select($db->quoteName('core_content_id'))
								-> from($db->quoteName('#__ucm_content'))
								-> where($db->quoteName('core_urls') . ' = '. $db->quote($url));
						
						$db->setQuery($query);
						
						$result = $db->loadObject();
						
						$tmp->core_content_id = $result->core_content_id;
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
				$count = $mediaHelper->countFiles($tmp->path);
				$tmp->files = $count[0];
				$tmp->folders = $count[1];

				$folders[] = $tmp;
			}
		}

		$list = array('folders' => $folders, 'docs' => $docs, 'images' => $images);

		return $list;
	}

	public function getForm($data = array(), $loadData = true)
	{
		return;
	}
}
