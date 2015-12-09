<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Component Folders Model
 */
class MediaModelFolders extends JModelLegacy
{
	/**
	 * Lists the folders in a parent folder
	 *
	 * @var array
	 */
	protected $folders = array();

	/**
	 * Folder to search for files
	 *
	 * @var string
	 */
	protected $currentFolder = '';

	/**
	 * Get the current folder
	 *
	 * @return string
	 */
	public function getCurrentFolder()
	{
		return $this->currentFolder;
	}

	/**
	 * Set the current folder
	 *
	 * @param $folder
	 */
	public function setCurrentFolder($currentFolder)
	{
		$this->currentFolder = $currentFolder;

		return $this;
	}

	/**
	 * Build browsable list of files
	 *
	 * @return  array
	 */
	public function getFolders()
	{
		if (!empty($this->folders))
		{
			return $this->folders;
		}

		$currentFolder = $this->getCurrentFolder();

		if (!file_exists($currentFolder))
		{
			return $this->folders;
		}

		$folderList = JFolder::folders($currentFolder);
		$mediaHelper = new JHelperMedia;

		// Iterate over the folders if they exist
		if ($folderList !== false)
		{
			foreach ($folderList as $folder)
			{
				$tmp                = new JObject;
				$tmp->name          = basename($folder);
				$tmp->path          = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($currentFolder . '/' . $folder));
				$tmp->path_relative = str_replace($currentFolder, '', $tmp->path);
				$tmp->count         = $mediaHelper->countFiles($tmp->path);
				$tmp->files         = $tmp->count[0];
				$tmp->folders       = $tmp->count[1];

				$this->folders[] = $tmp;
			}
		}

		return $this->folders;
	}

	/**
	 * Return the file model
	 *
	 * @return MediaModelFolder
	 */
	protected function getFolderModel()
	{
		return new MediaModelFolder;
	}
}