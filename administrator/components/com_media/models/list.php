<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once __DIR__ . '/file.php';
require_once __DIR__ . '/files.php';

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
	 * @param   string $property Optional parameter name
	 * @param   mixed  $default  Optional default value
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
			$input  = JFactory::getApplication()->input;
			$folder = $input->get('folder', '', 'path');
			$this->setState('folder', $folder);

			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			$set = true;
		}

		return parent::getState($property, $default);
	}

	/**
	 * Build browsable list
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

		$list = array('folders' => array(), 'docs' => array(), 'images' => array(), 'videos' => array());

		// Determine current path from the current state
		$basePath = $this->getCurrentFolder();
		$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE . '/');

		if (!file_exists($basePath))
		{
			return $list;
		}

		$fileList   = JFolder::files($basePath);
		$folderList = JFolder::folders($basePath);

		// Iterate over the files if they exist
		if ($fileList !== false)
		{
			foreach ($fileList as $file)
			{
				if (!$this->isFileBrowsable($basePath . '/' . $file))
				{
					continue;
				}

				$fileModel = $this->getFileModel();
				$fileModel->loadByPath($basePath . '/' . $file);

				$tmp = new JObject;
				$tmp->setProperties($fileModel->getFileProperties());

				$group          = $tmp->get('group');
				$list[$group][] = $tmp;
			}
		}

		// Iterate over the folders if they exist
		if ($folderList !== false)
		{
			foreach ($folderList as $folder)
			{
				$tmp                = new JObject;
				$tmp->name          = basename($folder);
				$tmp->path          = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $folder));
				$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
				$count              = MediaHelper::countFiles($tmp->path);
				$tmp->files         = $count[0];
				$tmp->folders       = $count[1];

				$list['folders'][] = $tmp;
			}
		}

		return $list;
	}

	/**
	 * Check whether this file is browsable in the Media Manager
	 *
	 * @param $file
	 *
	 * @return bool
	 */
	protected function isFileBrowsable($file)
	{
		$relativeFile = basename($file);

		if (!is_file($file))
		{
			return false;
		}

		if (substr($relativeFile, 0, 1) == '.')
		{
			return false;
		}

		if (strtolower($relativeFile) == 'index.html')
		{
			return false;
		}

		return true;
	}

	/**
	 * Return the current folder
	 *
	 * @return string
	 */
	public function getCurrentFolder()
	{
		$current = (string) $this->getState('folder');
		$currentFolder  = COM_MEDIA_BASE . ((strlen($current) > 0) ? '/' . $current : '');

		return $currentFolder;
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

	/**
	 * Return th file model
	 *
	 * @return MediaModelFile
	 */
	public function getFileModel()
	{
		return new MediaModelFile;
	}
}
