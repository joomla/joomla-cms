<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use \Joomla\CMS\Helper\MediaHelper;

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Media Component List Model
 *
 * @since  1.5
 */
class MediaModelList extends JModelLegacy
{
	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
	 *
	 * @since   1.5
	 */
	public function getState($property = null, $default = null)
	{
		static $set;

		if (!$set)
		{
			$input  = Factory::getApplication()->input;
			$folder = $input->get('folder', '', 'path');
			$this->setState('folder', $folder);

			$parent = str_replace("\\", '/', dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			$set = true;
		}

		return parent::getState($property, $default);
	}

	/**
	 * Get the images on the current folder
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function getImages()
	{
		$list = $this->getList();

		return $list['images'];
	}

	/**
	 * Get the folders on the current folder
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function getFolders()
	{
		$list = $this->getList();

		return $list['folders'];
	}

	/**
	 * Get the documents on the current folder
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function getDocuments()
	{
		$list = $this->getList();

		return $list['docs'];
	}

	/**
	 * Build imagelist
	 *
	 * @return  array
	 *
	 * @since 1.5
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
		$current = (string) $this->getState('folder');

		$basePath  = COM_MEDIA_BASE . ((strlen($current) > 0) ? '/' . $current : '');
		$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE . '/');

		// Reset base path
		if (strpos(realpath($basePath), JPath::clean(realpath(COM_MEDIA_BASE))) !== 0)
		{
			$basePath = COM_MEDIA_BASE;
		}

		$images  = array ();
		$folders = array ();
		$docs    = array ();
		$videos  = array ();

		$fileList   = false;
		$folderList = false;

		if (file_exists($basePath))
		{
			// Get the list of files and folders from the given folder
			$fileList   = Folder::files($basePath);
			$folderList = Folder::folders($basePath);
		}

		$mediaHelper = new MediaHelper;

		// Iterate over the files if they exist
		if ($fileList !== false)
		{
			$tmpBaseObject = new JObject;

			foreach ($fileList as $file)
			{
				if (is_file($basePath . '/' . $file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html')
				{
					$tmp = clone $tmpBaseObject;
					$tmp->name = $file;
					$tmp->title = $file;
					$tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $file));
					$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
					$tmp->size = filesize($tmp->path);

					$ext = strtolower(File::getExt($file));

					// TODO THe .xcf extension comes from Joomla 1.0 but it's no longer valid in modern browsers...
					$imageExtensions = array_merge(
						$mediaHelper->getImageExtensions(),
						array('jpg', 'png', 'gif', 'xcf', 'odg', 'bmp', 'jpeg', 'ico')
					);

					// Image extension?
					if (in_array($ext, $imageExtensions))
					{
						if ($ext === 'svg')
						{
							// SVG images are not supported by getimagesize() so we have to fake it.
							$info = array(60, 60, 'svg', 'image/svg+xml');
						}
						else
						{
							// Everything else should be supported by getimagesize() – or you'll  get a file type icon.
							$info = @getimagesize($tmp->path);
						}

						$tmp->width  = @$info[0];
						$tmp->height = @$info[1];
						$tmp->type   = @$info[2];
						$tmp->mime   = @$info['mime'];

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
					}
					// Video extension?
					elseif ($ext == 'mp4')
					{
						$tmp->icon_32 = 'media/mime-icon-32/' . $ext . '.png';
						$tmp->icon_16 = 'media/mime-icon-16/' . $ext . '.png';
						$videos[] = $tmp;
					}
					// Non-image extension?
					else
					{
						$tmp->icon_32 = 'media/mime-icon-32/' . $ext . '.png';
						$tmp->icon_16 = 'media/mime-icon-16/' . $ext . '.png';
						$docs[] = $tmp;
					}
				}
			}
		}

		// Iterate over the folders if they exist
		if ($folderList !== false)
		{
			$tmpBaseObject = new JObject;

			foreach ($folderList as $folder)
			{
				$tmp = clone $tmpBaseObject;
				$tmp->name = basename($folder);
				$tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $folder));
				$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
				$count = $mediaHelper->countFiles($tmp->path);
				$tmp->files = $count[0];
				$tmp->folders = $count[1];

				$folders[] = $tmp;
			}
		}

		$list = array('folders' => $folders, 'docs' => $docs, 'images' => $images, 'videos' => $videos);

		return $list;
	}

	/**
	 * Get the videos on the current folder
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getVideos()
	{
		$list = $this->getList();

		return $list['videos'];
	}
}
